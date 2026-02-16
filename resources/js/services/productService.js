import axios from 'axios';

/**
 * Busca produtos do endpoint backend com paginação, ordenação e busca.
 * @param {Object} params
 * @param {number} params.page
 * @param {number} params.perPage
 * @param {string} params.search
 * @param {string} params.sortKey
 * @param {string} params.sortDir
 * @returns {Promise<{items: Array, total: number, page: number, perPage: number}>}
 */
export async function fetchProducts({ page = 1, perPage = 10, search = '', sortKey = '', sortDir = 'asc' } = {}) {
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

    const { data } = await axios.get('/products/search', { params });
    // Ajuste para o formato do backend Laravel
    const products = data.data.products;
    return {
        items: products.data,
        total: products.total,
        page: products.current_page,
        perPage: products.per_page,
    };
}

/**
 * Cria um novo produto no backend.
 * @param {Object} productData
 * @returns {Promise<{success: boolean, data?: Object, error?: any}>}
 */
export async function createProduct(productData) {
    try {
        const { data } = await axios.post('/products', productData);
        return { success: true, data };
    } catch (error) {
        return { success: false, error };
    }
}

/**
 * Atualiza um produto existente no backend.
 * @param {number|string} id
 * @param {Object} productData
 * @returns {Promise<{success: boolean, data?: Object, error?: any}>}
 */
export async function updateProduct(id, productData) {
    try {
        const { data } = await axios.put(`/products/${id}`, productData);
        return { success: true, data };
    } catch (error) {
        return { success: false, error };
    }
}

/**
 * Deleta um produto no backend.
 * @param {number|string} id
 * @returns {Promise<{success: boolean, data?: Object, error?: any}>}
 */
export async function deleteProduct(id) {
    try {
        const { data } = await axios.delete(`/products/${id}`);
        return { success: true, data };
    } catch (error) {
        return { success: false, error };
    }
}

/**
 * Busca um produto específico por ID.
 * @param {number|string} id
 * @returns {Promise<{success: boolean, data?: Object, error?: any}>}
 */
export async function fetchProduct(id) {
    try {
        const { data } = await axios.get(`/products/${id}`);
        return { success: true, data };
    } catch (error) {
        return { success: false, error };
    }
}

/**
 * Vincula um fornecedor a um produto.
 * @param {number|string} productId
 * @param {Object} supplierData - { supplier_id, unit_price, delivery_time }
 * @returns {Promise<{success: boolean, data?: Object, error?: any}>}
 */
export async function attachSupplier(productId, supplierData) {
    try {
        const { data } = await axios.post(`/products/${productId}/suppliers`, supplierData);
        return { success: true, data };
    } catch (error) {
        return { success: false, error };
    }
}

/**
 * Atualiza o vínculo entre produto e fornecedor.
 * @param {number|string} productId
 * @param {number|string} supplierId
 * @param {Object} supplierData - { unit_price, delivery_time }
 * @returns {Promise<{success: boolean, data?: Object, error?: any}>}
 */
export async function updateProductSupplier(productId, supplierId, supplierData) {
    try {
        const { data } = await axios.put(`/products/${productId}/suppliers/${supplierId}`, supplierData);
        return { success: true, data };
    } catch (error) {
        return { success: false, error };
    }
}

/**
 * Remove o vínculo entre produto e fornecedor.
 * @param {number|string} productId
 * @param {number|string} supplierId
 * @returns {Promise<{success: boolean, data?: Object, error?: any}>}
 */
export async function detachSupplier(productId, supplierId) {
    try {
        const { data } = await axios.delete(`/products/${productId}/suppliers/${supplierId}`);
        return { success: true, data };
    } catch (error) {
        return { success: false, error };
    }
}
