import axios from 'axios';

/**
 * Faz upload da logomarca do tenant.
 * @param {File} file
 * @returns {Promise<{success: boolean, data?: Object, error?: any}>}
 */
export async function uploadLogo(file) {
    try {
        const formData = new FormData();
        formData.append('logo', file);
        const { data } = await axios.post('/settings/logo', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        return { success: true, data: data.data };
    } catch (error) {
        return { success: false, error };
    }
}

/**
 * Remove a logomarca do tenant.
 * @returns {Promise<{success: boolean, data?: Object, error?: any}>}
 */
export async function removeLogo() {
    try {
        const { data } = await axios.delete('/settings/logo');
        return { success: true, data: data.data };
    } catch (error) {
        return { success: false, error };
    }
}
