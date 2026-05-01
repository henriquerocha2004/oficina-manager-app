import axios from 'axios';

/**
 * Busca movimentações de estoque do endpoint backend com paginação, ordenação e filtros.
 * @param {Object} params
 * @param {number} params.page
 * @param {number} params.perPage
 * @param {string} params.search
 * @param {string} params.sortKey
 * @param {string} params.sortDir
 * @param {Object} params.filters
 * @param {string} params.filters.product_id - ID do produto
 * @param {string} params.filters.movement_type - Tipo de movimento (IN/OUT)
 * @param {string} params.filters.reason - Motivo da movimentação
 * @param {string} params.filters.date_from - Data início (YYYY-MM-DD)
 * @param {string} params.filters.date_to - Data fim (YYYY-MM-DD)
 * @returns {Promise<{items: Array, total: number, page: number, perPage: number}>}
 */
export async function fetchStockMovements({ page = 1, perPage = 10, search = '', sortKey = '', sortDir = 'asc', filters = {} } = {}) {
    const params = {
        page,
        per_page: perPage,
        search,
    };

    // Só adiciona sort_field e sort_direction se sortKey tiver valor
    if (sortKey) {
        params.sort_field = sortKey;
        params.sort_direction = sortDir;
    }

    // Adiciona filtros apenas se houver valores
    const filtersToSend = Object.entries(filters).reduce((acc, [key, value]) => {
        if (value) acc[key] = value;
        return acc;
    }, {});

    if (Object.keys(filtersToSend).length > 0) {
        params.filters = filtersToSend;
    }

    const { data } = await axios.get('/stock/movements/search', { params });
    // Ajuste para o formato do backend Laravel
    const movements = data.data.movements;
    return {
        items: movements.data,
        total: movements.total,
        page: movements.current_page,
        perPage: movements.per_page,
    };
}

/**
 * Busca um produto por nome (autocomplete).
 * @param {string} search - Nome do produto a buscar
 * @returns {Promise<{success: boolean, data?: Array, error?: any}>}
 */
export async function searchProducts(search) {
    try {
        const { data } = await axios.get('/products/search', {
            params: {
                search,
                per_page: 10,
            },
        });
        const products = data.data.products;
        return {
            success: true,
            data: products.data.map(p => ({ id: p.id, name: p.name })),
        };
    } catch (error) {
        return { success: false, error };
    }
}

/**
 * Busca estatísticas de movimentações de estoque
 * @returns {Promise<Object>}
 */
export async function fetchStockMovementStats() {
    try {
        const { data } = await axios.get('/stock/movements/stats');
        return { success: true, data: data.data };
    } catch (error) {
        console.error('Error fetching stock movement stats:', error);
        return { success: false, error };
    }
}
