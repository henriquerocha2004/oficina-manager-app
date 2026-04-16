import axios from 'axios';

export async function fetchClients({ page = 1, perPage = 10, search = '', sortKey = '', sortDir = 'asc' } = {}) {
    const params = { page, per_page: perPage, search };
    if (sortKey) {
        params.sort_field = sortKey;
        params.sort_direction = sortDir;
    }
    const { data } = await axios.get('/admin/clients/search', { params });
    const clients = data.data.clients;
    return {
        items: clients.data,
        total: clients.total,
        page: clients.current_page,
        perPage: clients.per_page,
    };
}

export async function createClient(clientData) {
    try {
        const { data } = await axios.post('/admin/clients', clientData);
        return { success: true, data };
    } catch (error) {
        return { success: false, error };
    }
}

export async function updateClient(id, clientData) {
    try {
        const { data } = await axios.put(`/admin/clients/${id}`, clientData);
        return { success: true, data };
    } catch (error) {
        return { success: false, error };
    }
}

export async function deleteClient(id) {
    try {
        const { data } = await axios.delete(`/admin/clients/${id}`);
        return { success: true, data };
    } catch (error) {
        return { success: false, error };
    }
}

export async function findClient(id) {
    try {
        const { data } = await axios.get(`/admin/clients/${id}`);
        return { success: true, data: data.data.client };
    } catch (error) {
        return { success: false, error };
    }
}
