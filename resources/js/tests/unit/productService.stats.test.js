vi.mock('axios');

import { describe, it, expect, vi, beforeEach } from 'vitest';
import axios from 'axios';
import { fetchProductStats } from '@/services/productService';

const mockedAxios = vi.mocked(axios);

describe('productService - fetchProductStats', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('should fetch product stats successfully', async () => {
        // Arrange
        const mockResponse = {
            data: {
                data: {
                    stats: {
                        total: 100,
                        last_90_days: 15,
                        previous_90_days: 10,
                        active_products: 85,
                        active_percentage: 85.0,
                        total_value: 45000.50,
                        growth: 5,
                        growth_percentage: 50.0,
                        top_category: 'engine',
                        top_category_count: 30,
                        top_category_percentage: 30.0,
                    }
                }
            }
        };
        mockedAxios.get.mockResolvedValue(mockResponse);

        // Act
        const result = await fetchProductStats();

        // Assert
        expect(mockedAxios.get).toHaveBeenCalledWith('/products/stats');
        expect(result).toEqual({
            success: true,
            data: {
                total: 100,
                last_90_days: 15,
                previous_90_days: 10,
                active_products: 85,
                active_percentage: 85.0,
                total_value: 45000.50,
                growth: 5,
                growth_percentage: 50.0,
                top_category: 'engine',
                top_category_count: 30,
                top_category_percentage: 30.0,
            }
        });
    });

    it('should handle fetch stats error', async () => {
        // Arrange
        const error = new Error('Network error');
        mockedAxios.get.mockRejectedValue(error);

        // Act
        const result = await fetchProductStats();

        // Assert
        expect(result).toEqual({ success: false, error });
    });
});
