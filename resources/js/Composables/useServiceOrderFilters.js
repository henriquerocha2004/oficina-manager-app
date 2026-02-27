import { reactive, computed } from 'vue';

const STORAGE_KEY = 'service-order-filters';

export function useServiceOrderFilters() {
    const filters = reactive({
        dateFrom: '',
        dateTo: '',
        client: '',
        plate: '',
        status: '',
    });

    function saveToStorage() {
        try {
            sessionStorage.setItem(STORAGE_KEY, JSON.stringify(filters));
        } catch (error) {
            console.error('Error saving filters to sessionStorage:', error);
        }
    }

    function loadFromStorage() {
        try {
            const saved = sessionStorage.getItem(STORAGE_KEY);
            return saved ? JSON.parse(saved) : {};
        } catch (error) {
            console.error('Error loading filters from sessionStorage:', error);
            return {};
        }
    }

    function clearFilters() {
        filters.dateFrom = '';
        filters.dateTo = '';
        filters.client = '';
        filters.plate = '';
        filters.status = '';
        
        try {
            sessionStorage.removeItem(STORAGE_KEY);
        } catch (error) {
            console.error('Error removing filters from sessionStorage:', error);
        }
    }

    const activeFiltersCount = computed(() => {
        let count = 0;
        if (filters.dateFrom) count++;
        if (filters.dateTo) count++;
        if (filters.client) count++;
        if (filters.plate) count++;
        if (filters.status) count++;
        return count;
    });

    const hasActiveFilters = computed(() => activeFiltersCount.value > 0);

    function initFromStorage() {
        const saved = loadFromStorage();
        if (saved.dateFrom) filters.dateFrom = saved.dateFrom;
        if (saved.dateTo) filters.dateTo = saved.dateTo;
        if (saved.client) filters.client = saved.client;
        if (saved.plate) filters.plate = saved.plate;
        if (saved.status) filters.status = saved.status;
    }

    return {
        filters,
        saveToStorage,
        loadFromStorage,
        clearFilters,
        activeFiltersCount,
        hasActiveFilters,
        initFromStorage,
    };
}
