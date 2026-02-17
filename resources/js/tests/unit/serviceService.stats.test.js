import { describe, it, expect, vi, beforeEach } from 'vitest';
import axios from 'axios';
import { fetchServiceStats } from '@/services/serviceService.js';

vi.mock('axios');

describe('serviceService - stats', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('should fetch service statistics successfully', async () => {
        // Arrange
        const mockStats = {
            total: 50,
            active_services: 45,
            average_price: 150.50,
            last_90_days: 10,
            previous_90_days: 8,
            growth: 2,
            growth_percentage: 25.0,
        };

        const mockResponse = {
            data: {
                message: 'Service statistics fetched successfully.',
                data: {
                    stats: mockStats,
                },
            },
        };

        vi.mocked(axios.get).mockResolvedValue(mockResponse);

        // Act
        const result = await fetchServiceStats();

        // Assert
        expect(axios.get).toHaveBeenCalledWith('/services/stats');
        expect(result.success).toBe(true);
        expect(result.data).toEqual(mockStats);
    });

    it('should handle errors when fetching stats fails', async () => {
        // Arrange
        const mockError = new Error('Network error');
        vi.mocked(axios.get).mockRejectedValue(mockError);

        // Act
        const result = await fetchServiceStats();

        // Assert
        expect(axios.get).toHaveBeenCalledWith('/services/stats');
        expect(result.success).toBe(false);
        expect(result.error).toBe(mockError);
    });
});
