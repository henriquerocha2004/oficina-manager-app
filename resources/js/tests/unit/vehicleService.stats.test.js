import { describe, it, expect, vi, beforeEach } from 'vitest';
import axios from 'axios';
import { fetchVehicleStats } from '@/services/vehicleService.js';

vi.mock('axios');

describe('vehicleService - stats', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('should fetch vehicle statistics successfully', async () => {
        // Arrange
        const mockStats = {
            total: 100,
            last_90_days: 20,
            previous_90_days: 15,
            growth: 5,
            growth_percentage: 33.3,
            top_brand: 'Toyota',
            top_brand_count: 30,
            top_brand_percentage: 30.0,
        };

        const mockResponse = {
            data: {
                message: 'Vehicle statistics fetched successfully.',
                data: {
                    stats: mockStats,
                },
            },
        };

        vi.mocked(axios.get).mockResolvedValue(mockResponse);

        // Act
        const result = await fetchVehicleStats();

        // Assert
        expect(axios.get).toHaveBeenCalledWith('/vehicles/stats');
        expect(result.success).toBe(true);
        expect(result.data).toEqual(mockStats);
    });

    it('should handle errors when fetching stats fails', async () => {
        // Arrange
        const mockError = new Error('Network error');
        vi.mocked(axios.get).mockRejectedValue(mockError);

        // Act
        const result = await fetchVehicleStats();

        // Assert
        expect(axios.get).toHaveBeenCalledWith('/vehicles/stats');
        expect(result.success).toBe(false);
        expect(result.error).toBe(mockError);
    });
});
