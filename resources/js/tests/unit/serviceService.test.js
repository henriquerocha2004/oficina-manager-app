vi.mock('axios');

import { describe, it, expect, vi, beforeEach } from 'vitest';
import axios from 'axios';
import { fetchServices, createService, updateService, deleteService } from '@/services/serviceService';

const mockedAxios = vi.mocked(axios);

describe('serviceService', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    describe('fetchServices', () => {
        it('should fetch services successfully', async () => {
            const mockResponse = {
                data: {
                    data: {
                        services: {
                            data: [{ id: 1, name: 'Troca de óleo', category: 'maintenance', base_price: 150.00 }],
                            total: 1,
                            current_page: 1,
                            per_page: 10,
                        }
                    }
                }
            };
            mockedAxios.get.mockResolvedValue(mockResponse);

            const result = await fetchServices({ page: 1, perPage: 10 });

            expect(mockedAxios.get).toHaveBeenCalledWith('/services/search', {
                params: {
                    page: 1,
                    per_page: 10,
                    search: '',
                }
            });
            expect(result).toEqual({
                items: [{ id: 1, name: 'Troca de óleo', category: 'maintenance', base_price: 150.00 }],
                total: 1,
                page: 1,
                perPage: 10,
            });
        });

        it('should fetch services with filters', async () => {
            const mockResponse = {
                data: {
                    data: {
                        services: {
                            data: [{ id: 1, name: 'Troca de óleo', category: 'maintenance' }],
                            total: 1,
                            current_page: 1,
                            per_page: 10,
                        }
                    }
                }
            };
            mockedAxios.get.mockResolvedValue(mockResponse);

            await fetchServices({ 
                page: 1, 
                perPage: 10, 
                filters: { category: 'maintenance' } 
            });

            expect(mockedAxios.get).toHaveBeenCalledWith('/services/search', {
                params: {
                    page: 1,
                    per_page: 10,
                    search: '',
                    filters: { category: 'maintenance' }
                }
            });
        });

        it('should handle fetch error', async () => {
            mockedAxios.get.mockRejectedValue(new Error('Network error'));

            await expect(fetchServices()).rejects.toThrow('Network error');
        });
    });

    describe('createService', () => {
        it('should create service successfully', async () => {
            const serviceData = { 
                name: 'Novo Serviço', 
                category: 'repair',
                base_price: 200.00,
                is_active: true
            };
            const mockResponse = { data: { id: 1, ...serviceData } };
            mockedAxios.post.mockResolvedValue(mockResponse);

            const result = await createService(serviceData);

            expect(mockedAxios.post).toHaveBeenCalledWith('/services', serviceData);
            expect(result).toEqual({ success: true, data: { id: 1, ...serviceData } });
        });

        it('should handle create error', async () => {
            const serviceData = { name: 'Novo Serviço' };
            const error = new Error('Validation error');
            mockedAxios.post.mockRejectedValue(error);

            const result = await createService(serviceData);

            expect(result).toEqual({ success: false, error });
        });
    });

    describe('updateService', () => {
        it('should update service successfully', async () => {
            const id = 1;
            const serviceData = { name: 'Serviço Atualizado', base_price: 250.00 };
            const mockResponse = { data: { id, ...serviceData } };
            mockedAxios.put.mockResolvedValue(mockResponse);

            const result = await updateService(id, serviceData);

            expect(mockedAxios.put).toHaveBeenCalledWith('/services/1', serviceData);
            expect(result).toEqual({ success: true, data: { id, ...serviceData } });
        });

        it('should handle update error', async () => {
            const id = 1;
            const serviceData = { name: 'Serviço Atualizado' };
            const error = new Error('Update error');
            mockedAxios.put.mockRejectedValue(error);

            const result = await updateService(id, serviceData);

            expect(result).toEqual({ success: false, error });
        });
    });

    describe('deleteService', () => {
        it('should delete service successfully', async () => {
            const id = 1;
            const mockResponse = { data: {} };
            mockedAxios.delete.mockResolvedValue(mockResponse);

            const result = await deleteService(id);

            expect(mockedAxios.delete).toHaveBeenCalledWith('/services/1');
            expect(result).toEqual({ success: true, data: {} });
        });

        it('should handle delete error', async () => {
            const id = 1;
            const error = new Error('Delete error');
            mockedAxios.delete.mockRejectedValue(error);

            const result = await deleteService(id);

            expect(result).toEqual({ success: false, error });
        });
    });
});
