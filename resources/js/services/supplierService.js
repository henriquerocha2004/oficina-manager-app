import axios from 'axios';

/**
 * Busca fornecedores do endpoint backend com paginação, ordenação e busca.
 * @param {Object} params
 * @param {number} params.page
 * @param {number} params.perPage
 * @param {string} params.search
 * @param {string} params.sortKey
 * @param {string} params.sortDir
 * @returns {Promise<{items: Array, total: number, page: number, perPage: number}>}
 */
export async function fetchSuppliers({ page = 1, perPage = 10, search = '', sortKey = '', sortDir = 'asc' } = {}) {
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

    const { data } = await axios.get('/suppliers/search', { params });
    // Ajuste para o formato do backend Laravel
    const suppliers = data.data.suppliers;
    return {
        items: suppliers.data,
        total: suppliers.total,
        page: suppliers.current_page,
        perPage: suppliers.per_page,
    };
}

/**
 * Cria um novo fornecedor no backend.
 * @param {Object} supplierData
 * @returns {Promise<{success: boolean, data?: Object, error?: any}>}
 */
export async function createSupplier(supplierData) {
    try {
        const { data } = await axios.post('/suppliers', supplierData);
        return { success: true, data };
    } catch (error) {
        return { success: false, error };
    }
}

/**
 * Atualiza um fornecedor existente no backend.
 * @param {number|string} id
 * @param {Object} supplierData
 * @returns {Promise<{success: boolean, data?: Object, error?: any}>}
 */
export async function updateSupplier(id, supplierData) {
    try {
        const { data } = await axios.put(`/suppliers/${id}`, supplierData);
        return { success: true, data };
    } catch (error) {
        return { success: false, error };
    }
}

/**
 * Deleta um fornecedor no backend.
 * @param {number|string} id
 * @returns {Promise<{success: boolean, data?: Object, error?: any}>}
 */
export async function deleteSupplier(id) {
    try {
        const { data } = await axios.delete(`/suppliers/${id}`);
        return { success: true, data };
    } catch (error) {
        return { success: false, error };
    }
}

/**
 * Busca estatísticas de fornecedores do endpoint backend.
 * @returns {Promise<{success: boolean, data?: Object, error?: any}>}
 */
export async function fetchSupplierStats() {
    try {
        const { data } = await axios.get('/suppliers/stats');
        return { success: true, data: data.data.stats };
    } catch (error) {
        return { success: false, error };
    }
}
