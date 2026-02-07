import axios from 'axios';

/**
 * Busca veículos do endpoint backend com paginação, ordenação e busca.
 * @param {Object} params
 * @param {number} params.page
 * @param {number} params.perPage
 * @param {string} params.search
 * @param {string} params.sortKey
 * @param {string} params.sortDir
 * @returns {Promise<{items: Array, total: number, page: number, perPage: number}>}
 */
export async function fetchVehicles({ page = 1, perPage = 10, search = '', sortKey = '', sortDir = 'asc', filters = {} } = {}) {
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

    const { data } = await axios.get('/vehicles/search', { params });
    // Ajuste para o formato do backend Laravel
    const vehicles = data.data.vehicles;
    return {
        items: vehicles.data,
        total: vehicles.total,
        page: vehicles.current_page,
        perPage: vehicles.per_page,
    };
}

/**
 * Cria um novo veículo no backend.
 * @param {Object} vehicleData
 * @returns {Promise<{success: boolean, data?: Object, error?: any}>}
 */
export async function createVehicle(vehicleData) {
    try {
        const { data } = await axios.post('/vehicles', vehicleData);
        return { success: true, data };
    } catch (error) {
        return { success: false, error };
    }
}

/**
 * Atualiza um veículo existente no backend.
 * @param {number|string} id
 * @param {Object} vehicleData
 * @returns {Promise<{success: boolean, data?: Object, error?: any}>}
 */
export async function updateVehicle(id, vehicleData) {
    try {
        const { data } = await axios.put(`/vehicles/${id}`, vehicleData);
        return { success: true, data };
    } catch (error) {
        return { success: false, error };
    }
}

/**
 * Deleta um veículo no backend.
 * @param {number|string} id
 * @returns {Promise<{success: boolean, data?: Object, error?: any}>}
 */
export async function deleteVehicle(id) {
    try {
        const { data } = await axios.delete(`/vehicles/${id}`);
        return { success: true, data };
    } catch (error) {
        return { success: false, error };
    }
}
