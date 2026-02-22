import { describe, it, expect, beforeEach, vi } from 'vitest';
import { useStockMovementFilters } from '@/Composables/useStockMovementFilters.js';

describe('useStockMovementFilters', () => {
    beforeEach(() => {
        sessionStorage.clear();
        vi.clearAllMocks();
    });

    it('should initialize filters with default values', () => {
        // Act
        const { filters } = useStockMovementFilters();

        // Assert
        expect(filters.product_id).toBe('');
        expect(filters.productName).toBe('');
        expect(filters.movement_type).toBe('');
        expect(filters.reason).toBe('');
        expect(filters.date_from).toBe('');
        expect(filters.date_to).toBe('');
    });

    it('should save filters to sessionStorage', () => {
        // Arrange
        const { filters, saveToStorage } = useStockMovementFilters();
        filters.product_id = 'p1';
        filters.productName = 'Product 1';
        filters.movement_type = 'IN';
        filters.reason = 'purchase';
        filters.date_from = '2026-01-01';
        filters.date_to = '2026-01-31';

        // Act
        saveToStorage();

        // Assert
        const saved = JSON.parse(sessionStorage.getItem('stock-movement-filters'));
        expect(saved.product_id).toBe('p1');
        expect(saved.productName).toBe('Product 1');
        expect(saved.movement_type).toBe('IN');
        expect(saved.reason).toBe('purchase');
        expect(saved.date_from).toBe('2026-01-01');
        expect(saved.date_to).toBe('2026-01-31');
    });

    it('should load filters from sessionStorage', () => {
        // Arrange
        const savedFilters = {
            product_id: 'p1',
            productName: 'Product 1',
            movement_type: 'IN',
            reason: 'purchase',
            date_from: '2026-01-01',
            date_to: '2026-01-31',
        };
        sessionStorage.setItem('stock-movement-filters', JSON.stringify(savedFilters));

        // Act
        const { loadFromStorage } = useStockMovementFilters();
        const loaded = loadFromStorage();

        // Assert
        expect(loaded.product_id).toBe('p1');
        expect(loaded.productName).toBe('Product 1');
        expect(loaded.movement_type).toBe('IN');
        expect(loaded.reason).toBe('purchase');
        expect(loaded.date_from).toBe('2026-01-01');
        expect(loaded.date_to).toBe('2026-01-31');
    });

    it('should return empty object when loading from empty sessionStorage', () => {
        // Act
        const { loadFromStorage } = useStockMovementFilters();
        const loaded = loadFromStorage();

        // Assert
        expect(loaded).toEqual({});
    });

    it('should clear all filters and remove from sessionStorage', () => {
        // Arrange
        const { filters, saveToStorage, clearFilters } = useStockMovementFilters();
        filters.product_id = 'p1';
        filters.productName = 'Product 1';
        filters.movement_type = 'IN';
        filters.reason = 'purchase';
        filters.date_from = '2026-01-01';
        filters.date_to = '2026-01-31';
        saveToStorage();

        // Act
        clearFilters();

        // Assert
        expect(filters.product_id).toBe('');
        expect(filters.productName).toBe('');
        expect(filters.movement_type).toBe('');
        expect(filters.reason).toBe('');
        expect(filters.date_from).toBe('');
        expect(filters.date_to).toBe('');
        expect(sessionStorage.getItem('stock-movement-filters')).toBeNull();
    });

    it('should count active filters correctly', () => {
        // Arrange
        const { filters, activeFiltersCount } = useStockMovementFilters();

        // Act & Assert - no filters
        expect(activeFiltersCount.value).toBe(0);

        // Act & Assert - one filter
        filters.product_id = 'p1';
        expect(activeFiltersCount.value).toBe(1);

        // Act & Assert - two filters
        filters.movement_type = 'IN';
        expect(activeFiltersCount.value).toBe(2);

        // Act & Assert - all filters
        filters.reason = 'purchase';
        filters.date_from = '2026-01-01';
        filters.date_to = '2026-01-31';
        expect(activeFiltersCount.value).toBe(5);
    });

    it('should not count empty string filters', () => {
        // Arrange
        const { filters, activeFiltersCount } = useStockMovementFilters();
        filters.product_id = '';
        filters.movement_type = '';
        filters.reason = '';
        filters.date_from = '';
        filters.date_to = '';

        // Act & Assert
        expect(activeFiltersCount.value).toBe(0);
    });

    it('should handle sessionStorage errors gracefully when saving', () => {
        // Arrange
        const { filters, saveToStorage } = useStockMovementFilters();
        filters.product_id = 'p1';
        
        const consoleErrorSpy = vi.spyOn(console, 'error').mockImplementation(() => {});
        vi.spyOn(Storage.prototype, 'setItem').mockImplementation(() => {
            throw new Error('Storage full');
        });

        // Act
        saveToStorage();

        // Assert
        expect(consoleErrorSpy).toHaveBeenCalledWith(
            'Erro ao salvar filtros no sessionStorage:',
            expect.any(Error)
        );

        consoleErrorSpy.mockRestore();
    });

    it('should handle sessionStorage errors gracefully when loading', () => {
        // Arrange
        const consoleErrorSpy = vi.spyOn(console, 'error').mockImplementation(() => {});
        vi.spyOn(Storage.prototype, 'getItem').mockImplementation(() => {
            throw new Error('Storage error');
        });

        // Act
        const { loadFromStorage } = useStockMovementFilters();
        const loaded = loadFromStorage();

        // Assert
        expect(loaded).toEqual({});
        expect(consoleErrorSpy).toHaveBeenCalledWith(
            'Erro ao carregar filtros do sessionStorage:',
            expect.any(Error)
        );

        consoleErrorSpy.mockRestore();
    });

    it('should handle sessionStorage errors gracefully when clearing', () => {
        // Arrange
        const { clearFilters } = useStockMovementFilters();
        
        const consoleErrorSpy = vi.spyOn(console, 'error').mockImplementation(() => {});
        vi.spyOn(Storage.prototype, 'removeItem').mockImplementation(() => {
            throw new Error('Storage error');
        });

        // Act
        clearFilters();

        // Assert
        expect(consoleErrorSpy).toHaveBeenCalledWith(
            'Erro ao remover filtros do sessionStorage:',
            expect.any(Error)
        );

        consoleErrorSpy.mockRestore();
    });
});
