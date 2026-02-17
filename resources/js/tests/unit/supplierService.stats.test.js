vi.mock('axios');

import { describe, it, expect, vi, beforeEach } from 'vitest';
import axios from 'axios';
import { fetchSupplierStats } from '@/services/supplierService';

const mockedAxios = vi.mocked(axios);

describe('supplierService - fetchSupplierStats', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('should fetch supplier stats successfully', async () => {
        // Arrange
        const mockResponse = {
            data: {
                data: {
                    stats: {
                        total: 25,
                        last_90_days: 5,
                        previous_90_days: 3,
                        active_suppliers: 20,
                        active_percentage: 80.0,
                        growth: 2,
                        growth_percentage: 66.7,
                        top_state: 'SP',
                        top_state_count: 10,
                        top_state_percentage: 40.0,
                    }
                }
            }
        };
        mockedAxios.get.mockResolvedValue(mockResponse);

        // Act
        const result = await fetchSupplierStats();

        // Assert
        expect(mockedAxios.get).toHaveBeenCalledWith('/suppliers/stats');
        expect(result).toEqual({
            success: true,
            data: {
                total: 25,
                last_90_days: 5,
                previous_90_days: 3,
                active_suppliers: 20,
                active_percentage: 80.0,
                growth: 2,
                growth_percentage: 66.7,
                top_state: 'SP',
                top_state_count: 10,
                top_state_percentage: 40.0,
            }
        });
    });

    it('should handle fetch stats error', async () => {
        // Arrange
        const error = new Error('Network error');
        mockedAxios.get.mockRejectedValue(error);

        // Act
        const result = await fetchSupplierStats();

        // Assert
        expect(result).toEqual({ success: false, error });
    });
});
