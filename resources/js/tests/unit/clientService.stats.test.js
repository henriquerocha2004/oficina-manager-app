vi.mock('axios');

import { describe, it, expect, vi, beforeEach } from 'vitest';
import axios from 'axios';
import { fetchClientStats } from '@/services/clientService';

const mockedAxios = vi.mocked(axios);

describe('clientService - fetchClientStats', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('should fetch client stats successfully', async () => {
        // Arrange
        const mockResponse = {
            data: {
                data: {
                    stats: {
                        total: 150,
                        last_90_days: 12,
                        previous_90_days: 8,
                        growth: 4,
                        growth_percentage: 50.0,
                        top_city: 'São Paulo',
                        top_city_count: 45,
                        top_city_percentage: 30.0,
                    }
                }
            }
        };
        mockedAxios.get.mockResolvedValue(mockResponse);

        // Act
        const result = await fetchClientStats();

        // Assert
        expect(mockedAxios.get).toHaveBeenCalledWith('/clients/stats');
        expect(result).toEqual({
            success: true,
            data: {
                total: 150,
                last_90_days: 12,
                previous_90_days: 8,
                growth: 4,
                growth_percentage: 50.0,
                top_city: 'São Paulo',
                top_city_count: 45,
                top_city_percentage: 30.0,
            }
        });
    });

    it('should handle fetch stats error', async () => {
        // Arrange
        const error = new Error('Network error');
        mockedAxios.get.mockRejectedValue(error);

        // Act
        const result = await fetchClientStats();

        // Assert
        expect(result).toEqual({ success: false, error });
    });
});
