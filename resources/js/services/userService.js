import axios from 'axios';

function toUserFormData(userData, withMethodSpoof = false) {
    const formData = new FormData();

    formData.append('name', userData.name ?? '');
    formData.append('email', userData.email ?? '');
    formData.append('role', userData.role ?? '');
    formData.append('is_active', userData.is_active ? '1' : '0');
    formData.append('remove_avatar', userData.remove_avatar ? '1' : '0');

    if (userData.password) {
        formData.append('password', userData.password);
    }

    if (userData.password_confirmation) {
        formData.append('password_confirmation', userData.password_confirmation);
    }

    if (userData.avatar instanceof File) {
        formData.append('avatar', userData.avatar);
    }

    if (withMethodSpoof) {
        formData.append('_method', 'PUT');
    }

    return formData;
}

/**
 * Busca usuarios com paginacao, ordenacao e busca textual.
 * @param {Object} params
 * @param {number} params.page
 * @param {number} params.perPage
 * @param {string} params.search
 * @param {string} params.sortKey
 * @param {string} params.sortDir
 * @returns {Promise<{items: Array, total: number, page: number, perPage: number}>}
 */
export async function fetchUsers({ page = 1, perPage = 10, search = '', sortKey = '', sortDir = 'asc' } = {}) {
    const params = {
        page,
        per_page: perPage,
        search,
    };

    if (sortKey) {
        params.sort_field = sortKey;
        params.sort_direction = sortDir;
    }

    const { data } = await axios.get('/users/search', { params });
    const users = data.data.users;

    return {
        items: users.data,
        total: users.total,
        page: users.current_page,
        perPage: users.per_page,
    };
}

/**
 * Cria um novo usuario.
 * @param {Object} userData
 * @returns {Promise<{success: boolean, data?: Object, error?: any}>}
 */
export async function createUser(userData) {
    try {
        const payload = toUserFormData(userData);
        const { data } = await axios.post('/users', payload, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });

        return { success: true, data };
    } catch (error) {
        return { success: false, error };
    }
}

/**
 * Atualiza um usuario existente.
 * @param {number|string} id
 * @param {Object} userData
 * @returns {Promise<{success: boolean, data?: Object, error?: any}>}
 */
export async function updateUser(id, userData) {
    try {
        const payload = toUserFormData(userData, true);
        const { data } = await axios.post(`/users/${id}`, payload, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });

        return { success: true, data };
    } catch (error) {
        return { success: false, error };
    }
}

/**
 * Remove um usuario (soft delete).
 * @param {number|string} id
 * @returns {Promise<{success: boolean, data?: Object, error?: any}>}
 */
export async function deleteUser(id) {
    try {
        const { data } = await axios.delete(`/users/${id}`);
        return { success: true, data };
    } catch (error) {
        return { success: false, error };
    }
}
