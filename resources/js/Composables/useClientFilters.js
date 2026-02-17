import { reactive, computed } from 'vue';

const STORAGE_KEY = 'client-filters';

/**
 * Composable para gerenciar filtros de clientes com persistência em sessionStorage.
 * @returns {Object} - Objeto contendo filtros, funções de gerenciamento e contagem de filtros ativos
 */
export function useClientFilters() {
    const filters = reactive({
        state: '',
        city: '',
        type: 'all', // all, pf (pessoa física), pj (pessoa jurídica)
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
        filters.state = '';
        filters.city = '';
        filters.type = 'all';

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
        if (filters.state) count++;
        if (filters.city) count++;
        if (filters.type && filters.type !== 'all') count++;
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
