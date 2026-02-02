vi.mock('axios');

import { describe, it, expect, vi, beforeEach } from 'vitest';
import axios from 'axios';
import { fetchClients, createClient, updateClient, deleteClient } from '@/services/clientService';

const mockedAxios = vi.mocked(axios);

describe('clientService', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    describe('fetchClients', () => {
        it('should fetch clients successfully', async () => {
            const mockResponse = {
                data: {
                    data: {
                        clients: {
                            data: [{ id: 1, name: 'Client 1' }],
                            total: 1,
                            current_page: 1,
                            per_page: 10,
                        }
                    }
                }
            };
            mockedAxios.get.mockResolvedValue(mockResponse);

            const result = await fetchClients({ page: 1, perPage: 10 });

            expect(mockedAxios.get).toHaveBeenCalledWith('/clients/search', {
                params: {
                    page: 1,
                    per_page: 10,
                    search: '',
                    sort_field: '',
                    sort_direction: 'asc',
                }
            });
            expect(result).toEqual({
                items: [{ id: 1, name: 'Client 1' }],
                total: 1,
                page: 1,
                perPage: 10,
            });
        });

        it('should handle fetch error', async () => {
            mockedAxios.get.mockRejectedValue(new Error('Network error'));

            await expect(fetchClients()).rejects.toThrow('Network error');
        });
    });

    describe('createClient', () => {
        it('should create client successfully', async () => {
            const clientData = { name: 'New Client' };
            const mockResponse = { data: { id: 1, ...clientData } };
            mockedAxios.post.mockResolvedValue(mockResponse);

            const result = await createClient(clientData);

            expect(mockedAxios.post).toHaveBeenCalledWith('/clients', clientData);
            expect(result).toEqual({ success: true, data: { id: 1, ...clientData } });
        });

        it('should handle create error', async () => {
            const clientData = { name: 'New Client' };
            const error = new Error('Validation error');
            mockedAxios.post.mockRejectedValue(error);

            const result = await createClient(clientData);

            expect(result).toEqual({ success: false, error });
        });
    });

    describe('updateClient', () => {
        it('should update client successfully', async () => {
            const id = 1;
            const clientData = { name: 'Updated Client' };
            const mockResponse = { data: { id, ...clientData } };
            mockedAxios.put.mockResolvedValue(mockResponse);

            const result = await updateClient(id, clientData);

            expect(mockedAxios.put).toHaveBeenCalledWith('/clients/1', clientData);
            expect(result).toEqual({ success: true, data: { id, ...clientData } });
        });

        it('should handle update error', async () => {
            const id = 1;
            const clientData = { name: 'Updated Client' };
            const error = new Error('Update error');
            mockedAxios.put.mockRejectedValue(error);

            const result = await updateClient(id, clientData);

            expect(result).toEqual({ success: false, error });
        });
    });

    describe('deleteClient', () => {
        it('should delete client successfully', async () => {
            const id = 1;
            const mockResponse = { data: {} };
            mockedAxios.delete.mockResolvedValue(mockResponse);

            const result = await deleteClient(id);

            expect(mockedAxios.delete).toHaveBeenCalledWith('/clients/1');
            expect(result).toEqual({ success: true, data: {} });
        });

        it('should handle delete error', async () => {
            const id = 1;
            const error = new Error('Delete error');
            mockedAxios.delete.mockRejectedValue(error);

            const result = await deleteClient(id);

            expect(result).toEqual({ success: false, error });
        });
    });
});