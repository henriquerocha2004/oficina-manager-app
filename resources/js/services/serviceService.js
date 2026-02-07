import axios from 'axios';

/**
 * Busca serviços do endpoint backend com paginação, ordenação, busca e filtros.
 * @param {Object} params
 * @param {number} params.page
 * @param {number} params.perPage
 * @param {string} params.search
 * @param {string} params.sortKey
 * @param {string} params.sortDir
 * @param {Object} params.filters
 * @returns {Promise<{items: Array, total: number, page: number, perPage: number}>}
 */
export async function fetchServices({ 
    page = 1, 
    perPage = 10, 
    search = '', 
    sortKey = '', 
    sortDir = 'asc', 
    filters = {} 
} = {}) {
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

    const { data } = await axios.get('/services/search', { params });
    // Ajuste para o formato do backend Laravel
    const services = data.data.services;
    return {
        items: services.data,
        total: services.total,
        page: services.current_page,
        perPage: services.per_page,
    };
}

/**
 * Cria um novo serviço no backend.
 * @param {Object} serviceData
 * @returns {Promise<{success: boolean, data?: Object, error?: any}>}
 */
export async function createService(serviceData) {
    try {
        const { data } = await axios.post('/services', serviceData);
        return { success: true, data };
    } catch (error) {
        return { success: false, error };
    }
}

/**
 * Atualiza um serviço existente no backend.
 * @param {number|string} id
 * @param {Object} serviceData
 * @returns {Promise<{success: boolean, data?: Object, error?: any}>}
 */
export async function updateService(id, serviceData) {
    try {
        const { data } = await axios.put(`/services/${id}`, serviceData);
        return { success: true, data };
    } catch (error) {
        return { success: false, error };
    }
}

/**
 * Deleta um serviço no backend.
 * @param {number|string} id
 * @returns {Promise<{success: boolean, data?: Object, error?: any}>}
 */
export async function deleteService(id) {
    try {
        const { data } = await axios.delete(`/services/${id}`);
        return { success: true, data };
    } catch (error) {
        return { success: false, error };
    }
}
