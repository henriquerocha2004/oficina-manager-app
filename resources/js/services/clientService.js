import axios from 'axios';

/**
 * Busca clientes do endpoint backend com paginação, ordenação e busca.
 * @param {Object} params
 * @param {number} params.page
 * @param {number} params.perPage
 * @param {string} params.search
 * @param {string} params.sortKey
 * @param {string} params.sortDir
 * @returns {Promise<{items: Array, total: number, page: number, perPage: number}>}
 */
export async function fetchClients({ page = 1, perPage = 10, search = '', sortKey = '', sortDir = 'asc' } = {}) {
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

    const { data } = await axios.get('/clients/search', { params });
    // Ajuste para o formato do backend Laravel
    const clients = data.data.clients;
    return {
        items: clients.data,
        total: clients.total,
        page: clients.current_page,
        perPage: clients.per_page,
    };
}

/**
 * Cria um novo cliente no backend.
 * @param {Object} clientData
 * @returns {Promise<{success: boolean, data?: Object, error?: any}>}
 */
export async function createClient(clientData) {
    try {
        const { data } = await axios.post('/clients', clientData);
        return { success: true, data };
    } catch (error) {
        return { success: false, error };
    }
}

/**
 * Atualiza um cliente existente no backend.
 * @param {number|string} id
 * @param {Object} clientData
 * @returns {Promise<{success: boolean, data?: Object, error?: any}>}
 */
export async function updateClient(id, clientData) {
    try {
        const { data } = await axios.put(`/clients/${id}`, clientData);
        return { success: true, data };
    } catch (error) {
        return { success: false, error };
    }
}

/**
 * Deleta um cliente no backend.
 * @param {number|string} id
 * @returns {Promise<{success: boolean, data?: Object, error?: any}>}
 */
export async function deleteClient(id) {
    try {
        const { data } = await axios.delete(`/clients/${id}`);
        return { success: true, data };
    } catch (error) {
        return { success: false, error };
    }
}

/**
 * Busca estatísticas de clientes do backend.
 * @returns {Promise<{success: boolean, data?: Object, error?: any}>}
 */
export async function fetchClientStats() {
    try {
        const { data } = await axios.get('/clients/stats');
        return { success: true, data: data.data.stats };
    } catch (error) {
        return { success: false, error };
    }
}
