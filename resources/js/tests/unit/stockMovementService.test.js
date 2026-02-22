import { describe, it, expect, vi, beforeEach } from 'vitest';
import axios from 'axios';
import { fetchStockMovements, searchProducts } from '@/services/stockMovementService.js';

vi.mock('axios');

describe('stockMovementService', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    describe('fetchStockMovements', () => {
        it('should fetch stock movements successfully', async () => {
            // Arrange
            const mockMovements = {
                current_page: 1,
                data: [
                    {
                        id: '1',
                        product_id: 'p1',
                        movement_type: 'IN',
                        type: 'manual',
                        quantity: 10,
                        balance_after: 50,
                        reason: 'purchase',
                        user_id: 'u1',
                        created_at: '2026-02-19T10:00:00.000Z',
                    },
                ],
                total: 1,
                per_page: 10,
            };

            const mockResponse = {
                data: {
                    data: {
                        movements: mockMovements,
                    },
                },
            };

            vi.mocked(axios.get).mockResolvedValue(mockResponse);

            // Act
            const result = await fetchStockMovements({
                page: 1,
                perPage: 10,
                search: '',
                sortKey: 'created_at',
                sortDir: 'desc',
            });

            // Assert
            expect(axios.get).toHaveBeenCalledWith('/stock/movements/search', {
                params: {
                    page: 1,
                    per_page: 10,
                    search: '',
                    sort_field: 'created_at',
                    sort_direction: 'desc',
                },
            });
            expect(result.items).toEqual(mockMovements.data);
            expect(result.total).toBe(1);
            expect(result.page).toBe(1);
            expect(result.perPage).toBe(10);
        });

        it('should include filters in request when provided', async () => {
            // Arrange
            const mockMovements = {
                current_page: 1,
                data: [],
                total: 0,
                per_page: 10,
            };

            const mockResponse = {
                data: {
                    data: {
                        movements: mockMovements,
                    },
                },
            };

            vi.mocked(axios.get).mockResolvedValue(mockResponse);

            // Act
            await fetchStockMovements({
                page: 1,
                perPage: 10,
                filters: {
                    product_id: 'p1',
                    movement_type: 'IN',
                    reason: 'purchase',
                    date_from: '2026-01-01',
                    date_to: '2026-01-31',
                },
            });

            // Assert
            expect(axios.get).toHaveBeenCalledWith('/stock/movements/search', {
                params: {
                    page: 1,
                    per_page: 10,
                    search: '',
                    filters: {
                        product_id: 'p1',
                        movement_type: 'IN',
                        reason: 'purchase',
                        date_from: '2026-01-01',
                        date_to: '2026-01-31',
                    },
                },
            });
        });

        it('should exclude empty filter values from request', async () => {
            // Arrange
            const mockMovements = {
                current_page: 1,
                data: [],
                total: 0,
                per_page: 10,
            };

            const mockResponse = {
                data: {
                    data: {
                        movements: mockMovements,
                    },
                },
            };

            vi.mocked(axios.get).mockResolvedValue(mockResponse);

            // Act
            await fetchStockMovements({
                page: 1,
                perPage: 10,
                filters: {
                    product_id: 'p1',
                    movement_type: '',
                    reason: null,
                },
            });

            // Assert
            expect(axios.get).toHaveBeenCalledWith('/stock/movements/search', {
                params: {
                    page: 1,
                    per_page: 10,
                    search: '',
                    filters: {
                        product_id: 'p1',
                    },
                },
            });
        });
    });

    describe('searchProducts', () => {
        it('should search products successfully', async () => {
            // Arrange
            const mockProducts = {
                current_page: 1,
                data: [
                    { id: 'p1', name: 'Product 1', price: 10.0 },
                    { id: 'p2', name: 'Product 2', price: 20.0 },
                ],
                total: 2,
                per_page: 10,
            };

            const mockResponse = {
                data: {
                    data: {
                        products: mockProducts,
                    },
                },
            };

            vi.mocked(axios.get).mockResolvedValue(mockResponse);

            // Act
            const result = await searchProducts('Product');

            // Assert
            expect(axios.get).toHaveBeenCalledWith('/products/search', {
                params: {
                    search: 'Product',
                    per_page: 10,
                },
            });
            expect(result.success).toBe(true);
            expect(result.data).toEqual([
                { id: 'p1', name: 'Product 1' },
                { id: 'p2', name: 'Product 2' },
            ]);
        });

        it('should handle errors when searching products fails', async () => {
            // Arrange
            const mockError = new Error('Network error');
            vi.mocked(axios.get).mockRejectedValue(mockError);

            // Act
            const result = await searchProducts('Product');

            // Assert
            expect(axios.get).toHaveBeenCalledWith('/products/search', {
                params: {
                    search: 'Product',
                    per_page: 10,
                },
            });
            expect(result.success).toBe(false);
            expect(result.error).toBe(mockError);
        });
    });
});
