vi.mock('axios');

import { describe, it, expect, vi, beforeEach } from 'vitest';
import axios from 'axios';
import { fetchUsers, createUser, updateUser, deleteUser } from '@/services/userService';

const mockedAxios = vi.mocked(axios);

const makeUserResponse = (overrides = {}) => ({
    id: 'u-1',
    name: 'João Silva',
    email: 'joao@test.com',
    role: 'reception',
    is_active: true,
    ...overrides,
});

describe('userService', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    // ─── fetchUsers ────────────────────────────────────────────────────────

    describe('fetchUsers', () => {
        const mockResponse = (users = []) => ({
            data: {
                data: {
                    users: {
                        data: users,
                        total: users.length,
                        current_page: 1,
                        per_page: 10,
                    },
                },
            },
        });

        it('calls /users/search with default params', async () => {
            mockedAxios.get.mockResolvedValue(mockResponse());

            await fetchUsers();

            expect(mockedAxios.get).toHaveBeenCalledWith('/users/search', {
                params: { page: 1, per_page: 10, search: '' },
            });
        });

        it('includes sort params when sortKey is provided', async () => {
            mockedAxios.get.mockResolvedValue(mockResponse());

            await fetchUsers({ sortKey: 'name', sortDir: 'asc' });

            expect(mockedAxios.get).toHaveBeenCalledWith('/users/search', {
                params: expect.objectContaining({ sort_field: 'name', sort_direction: 'asc' }),
            });
        });

        it('does not include sort params when sortKey is empty', async () => {
            mockedAxios.get.mockResolvedValue(mockResponse());

            await fetchUsers({ sortKey: '' });

            const callParams = mockedAxios.get.mock.calls[0][1].params;
            expect(callParams.sort_field).toBeUndefined();
        });

        it('returns paginated structure', async () => {
            mockedAxios.get.mockResolvedValue(mockResponse([makeUserResponse()]));

            const result = await fetchUsers({ page: 1, perPage: 10 });

            expect(result.items).toHaveLength(1);
            expect(result.total).toBe(1);
            expect(result.page).toBe(1);
            expect(result.perPage).toBe(10);
        });

        it('passes search term in params', async () => {
            mockedAxios.get.mockResolvedValue(mockResponse());

            await fetchUsers({ search: 'João' });

            expect(mockedAxios.get).toHaveBeenCalledWith('/users/search', {
                params: expect.objectContaining({ search: 'João' }),
            });
        });
    });

    // ─── createUser ────────────────────────────────────────────────────────

    describe('createUser', () => {
        it('posts FormData to /users as multipart', async () => {
            const mockData = { data: { user: makeUserResponse() } };
            mockedAxios.post.mockResolvedValue({ data: mockData });

            const result = await createUser({
                name: 'João Silva',
                email: 'joao@test.com',
                role: 'reception',
                is_active: true,
                remove_avatar: false,
            });

            expect(mockedAxios.post).toHaveBeenCalledWith(
                '/users',
                expect.any(FormData),
                expect.objectContaining({ headers: { 'Content-Type': 'multipart/form-data' } })
            );
            expect(result.success).toBe(true);
        });

        it('includes password when provided', async () => {
            mockedAxios.post.mockResolvedValue({ data: {} });

            await createUser({
                name: 'Test',
                email: 'test@test.com',
                role: 'mechanic',
                is_active: true,
                remove_avatar: false,
                password: 'secret123',
                password_confirmation: 'secret123',
            });

            const formData = mockedAxios.post.mock.calls[0][1];
            expect(formData.get('password')).toBe('secret123');
            expect(formData.get('password_confirmation')).toBe('secret123');
        });

        it('does not include password when not provided', async () => {
            mockedAxios.post.mockResolvedValue({ data: {} });

            await createUser({ name: 'Test', email: 'test@test.com', role: 'mechanic', is_active: true, remove_avatar: false });

            const formData = mockedAxios.post.mock.calls[0][1];
            expect(formData.get('password')).toBeNull();
        });

        it('returns success:false on error', async () => {
            const error = new Error('Conflict');
            mockedAxios.post.mockRejectedValue(error);

            const result = await createUser({ name: 'Test', email: 'dup@test.com', role: 'reception', is_active: true, remove_avatar: false });

            expect(result.success).toBe(false);
            expect(result.error).toBe(error);
        });
    });

    // ─── updateUser ────────────────────────────────────────────────────────

    describe('updateUser', () => {
        it('posts FormData with _method=PUT to /users/:id', async () => {
            mockedAxios.post.mockResolvedValue({ data: {} });

            await updateUser('u-1', {
                name: 'João Atualizado',
                email: 'joao@test.com',
                role: 'administrator',
                is_active: true,
                remove_avatar: false,
            });

            expect(mockedAxios.post).toHaveBeenCalledWith(
                '/users/u-1',
                expect.any(FormData),
                expect.objectContaining({ headers: { 'Content-Type': 'multipart/form-data' } })
            );

            const formData = mockedAxios.post.mock.calls[0][1];
            expect(formData.get('_method')).toBe('PUT');
            expect(formData.get('name')).toBe('João Atualizado');
        });

        it('includes avatar file when provided', async () => {
            mockedAxios.post.mockResolvedValue({ data: {} });

            const avatarFile = new File(['img'], 'avatar.jpg', { type: 'image/jpeg' });

            await updateUser('u-1', {
                name: 'Test',
                email: 'test@test.com',
                role: 'mechanic',
                is_active: true,
                remove_avatar: false,
                avatar: avatarFile,
            });

            const formData = mockedAxios.post.mock.calls[0][1];
            expect(formData.get('avatar')).toBe(avatarFile);
        });

        it('does not append avatar when not a File instance', async () => {
            mockedAxios.post.mockResolvedValue({ data: {} });

            await updateUser('u-1', {
                name: 'Test',
                email: 'test@test.com',
                role: 'mechanic',
                is_active: true,
                remove_avatar: false,
                avatar: '/path/to/existing.jpg',
            });

            const formData = mockedAxios.post.mock.calls[0][1];
            expect(formData.get('avatar')).toBeNull();
        });

        it('returns success:false on error', async () => {
            const error = new Error('Not found');
            mockedAxios.post.mockRejectedValue(error);

            const result = await updateUser('missing', { name: 'Test', email: 't@t.com', role: 'mechanic', is_active: true, remove_avatar: false });

            expect(result.success).toBe(false);
        });
    });

    // ─── deleteUser ────────────────────────────────────────────────────────

    describe('deleteUser', () => {
        it('calls DELETE /users/:id', async () => {
            mockedAxios.delete.mockResolvedValue({ data: {} });

            const result = await deleteUser('u-1');

            expect(mockedAxios.delete).toHaveBeenCalledWith('/users/u-1');
            expect(result.success).toBe(true);
        });

        it('returns success:false on error', async () => {
            mockedAxios.delete.mockRejectedValue(new Error('Not found'));

            const result = await deleteUser('missing');

            expect(result.success).toBe(false);
        });
    });
});
