import { reactive, computed } from 'vue';

const STORAGE_KEY = 'product-filters';

/**
 * Composable para gerenciar filtros de produtos com persistência em sessionStorage.
 * @returns {Object} - Objeto contendo filtros, funções de gerenciamento e contagem de filtros ativos
 */
export function useProductFilters() {
    const filters = reactive({
        category: '',
        status: 'all', // all, active, inactive
        priceMin: '',
        priceMax: '',
    });

    /**
     * Salva os filtros no sessionStorage.
     */
    function saveToStorage() {
        try {
            sessionStorage.setItem(STORAGE_KEY, JSON.stringify(filters));
        } catch (error) {
            console.error('Erro ao salvar filtros no sessionStorage:', error);
        }
    }

    /**
     * Carrega os filtros do sessionStorage.
     * @returns {Object} - Objeto com os filtros salvos ou objeto vazio
     */
    function loadFromStorage() {
        try {
            const saved = sessionStorage.getItem(STORAGE_KEY);
            return saved ? JSON.parse(saved) : {};
        } catch (error) {
            console.error('Erro ao carregar filtros do sessionStorage:', error);
            return {};
        }
    }

    /**
     * Limpa todos os filtros e remove do sessionStorage.
     */
    function clearFilters() {
        filters.category = '';
        filters.status = 'all';
        filters.priceMin = '';
        filters.priceMax = '';

        try {
            sessionStorage.removeItem(STORAGE_KEY);
        } catch (error) {
            console.error('Erro ao remover filtros do sessionStorage:', error);
        }
    }

    /**
     * Conta quantos filtros estão ativos (não vazios).
     */
    const activeFiltersCount = computed(() => {
        let count = 0;
        if (filters.category) count++;
        if (filters.status && filters.status !== 'all') count++;
        if (filters.priceMin) count++;
        if (filters.priceMax) count++;
        return count;
    });

    return {
        filters,
        saveToStorage,
        loadFromStorage,
        clearFilters,
        activeFiltersCount,
    };
}
