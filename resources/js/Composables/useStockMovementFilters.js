import { reactive, computed } from 'vue';

const STORAGE_KEY = 'stock-movement-filters';

/**
 * Composable para gerenciar filtros de movimentações de estoque com persistência em sessionStorage
 * @returns {Object} - Objeto contendo filtros, funções de gerenciamento e contagem de filtros ativos
 */
export function useStockMovementFilters() {
    const filters = reactive({
        product_id: '',
        productName: '',
        movement_type: '',
        reason: '',
        date_from: '',
        date_to: '',
    });

    /**
     * Salva os filtros no sessionStorage
     */
    function saveToStorage() {
        try {
            sessionStorage.setItem(STORAGE_KEY, JSON.stringify(filters));
        } catch (error) {
            console.error('Erro ao salvar filtros no sessionStorage:', error);
        }
    }

    /**
     * Carrega os filtros do sessionStorage
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
     * Limpa todos os filtros e remove do sessionStorage
     */
    function clearFilters() {
        filters.product_id = '';
        filters.productName = '';
        filters.movement_type = '';
        filters.reason = '';
        filters.date_from = '';
        filters.date_to = '';
        
        try {
            sessionStorage.removeItem(STORAGE_KEY);
        } catch (error) {
            console.error('Erro ao remover filtros do sessionStorage:', error);
        }
    }

    /**
     * Conta quantos filtros estão ativos (não vazios)
     */
    const activeFiltersCount = computed(() => {
        let count = 0;
        if (filters.product_id) count++;
        if (filters.movement_type) count++;
        if (filters.reason) count++;
        if (filters.date_from) count++;
        if (filters.date_to) count++;
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
