vi.mock('axios');

import { describe, it, expect, vi, beforeEach } from 'vitest';
import axios from 'axios';
import { fetchSuppliers, createSupplier, updateSupplier, deleteSupplier } from '@/services/supplierService';

const mockedAxios = vi.mocked(axios);

describe('supplierService', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    describe('fetchSuppliers', () => {
        it('should fetch suppliers successfully', async () => {
            const mockResponse = {
                data: {
                    data: {
                        suppliers: {
                            data: [{ id: 1, name: 'Supplier 1' }],
                            total: 1,
                            current_page: 1,
                            per_page: 10,
                        }
                    }
                }
            };
            mockedAxios.get.mockResolvedValue(mockResponse);

            const result = await fetchSuppliers({ page: 1, perPage: 10 });

            expect(mockedAxios.get).toHaveBeenCalledWith('/suppliers/search', {
                params: {
                    page: 1,
                    per_page: 10,
                    search: '',
                }
            });
            expect(result).toEqual({
                items: [{ id: 1, name: 'Supplier 1' }],
                total: 1,
                page: 1,
                perPage: 10,
            });
        });

        it('should handle fetch error', async () => {
            mockedAxios.get.mockRejectedValue(new Error('Network error'));

            await expect(fetchSuppliers()).rejects.toThrow('Network error');
        });
    });

    describe('createSupplier', () => {
        it('should create supplier successfully', async () => {
            const supplierData = { name: 'New Supplier' };
            const mockResponse = { data: { id: 1, ...supplierData } };
            mockedAxios.post.mockResolvedValue(mockResponse);

            const result = await createSupplier(supplierData);

            expect(mockedAxios.post).toHaveBeenCalledWith('/suppliers', supplierData);
            expect(result).toEqual({ success: true, data: { id: 1, ...supplierData } });
        });

        it('should handle create error', async () => {
            const supplierData = { name: 'New Supplier' };
            const error = new Error('Validation error');
            mockedAxios.post.mockRejectedValue(error);

            const result = await createSupplier(supplierData);

            expect(result).toEqual({ success: false, error });
        });
    });

    describe('updateSupplier', () => {
        it('should update supplier successfully', async () => {
            const id = 1;
            const supplierData = { name: 'Updated Supplier' };
            const mockResponse = { data: { id, ...supplierData } };
            mockedAxios.put.mockResolvedValue(mockResponse);

            const result = await updateSupplier(id, supplierData);

            expect(mockedAxios.put).toHaveBeenCalledWith('/suppliers/1', supplierData);
            expect(result).toEqual({ success: true, data: { id, ...supplierData } });
        });

        it('should handle update error', async () => {
            const id = 1;
            const supplierData = { name: 'Updated Supplier' };
            const error = new Error('Update error');
            mockedAxios.put.mockRejectedValue(error);

            const result = await updateSupplier(id, supplierData);

            expect(result).toEqual({ success: false, error });
        });
    });

    describe('deleteSupplier', () => {
        it('should delete supplier successfully', async () => {
            const id = 1;
            const mockResponse = { data: {} };
            mockedAxios.delete.mockResolvedValue(mockResponse);

            const result = await deleteSupplier(id);

            expect(mockedAxios.delete).toHaveBeenCalledWith('/suppliers/1');
            expect(result).toEqual({ success: true, data: {} });
        });

        it('should handle delete error', async () => {
            const id = 1;
            const error = new Error('Delete error');
            mockedAxios.delete.mockRejectedValue(error);

            const result = await deleteSupplier(id);

            expect(result).toEqual({ success: false, error });
        });
    });
});
