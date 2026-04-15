import axios from 'axios';

export async function fetchTenants({ page = 1, perPage = 10, search = '', sortKey = '', sortDir = 'asc', filters = {} } = {}) {
    const params = { page, per_page: perPage, search };
    if (sortKey) {
        params.sort_field = sortKey;
        params.sort_direction = sortDir;
    }
    if (filters.status) {
        params['filters[status]'] = filters.status;
    }
    const { data } = await axios.get('/admin/tenants/search', { params });
    const tenants = data.data.tenants;
    return {
        items: tenants.data,
        total: tenants.total,
        page: tenants.current_page,
        perPage: tenants.per_page,
    };
}

export async function createTenant(tenantData) {
    try {
        const { data } = await axios.post('/admin/tenants', tenantData);
        return { success: true, data };
    } catch (error) {
        return { success: false, error };
    }
}

export async function updateTenant(id, tenantData) {
    try {
        const { data } = await axios.put(`/admin/tenants/${id}`, tenantData);
        return { success: true, data };
    } catch (error) {
        return { success: false, error };
    }
}

export async function deleteTenant(id) {
    try {
        const { data } = await axios.delete(`/admin/tenants/${id}`);
        return { success: true, data };
    } catch (error) {
        return { success: false, error };
    }
}

export async function findTenant(id) {
    try {
        const { data } = await axios.get(`/admin/tenants/${id}`);
        return { success: true, data: data.data.tenant };
    } catch (error) {
        return { success: false, error };
    }
}
