import axios from 'axios';

function toAccountFormData(accountData) {
    const formData = new FormData();

    formData.append('name', accountData.name ?? '');
    formData.append('remove_avatar', accountData.remove_avatar ? '1' : '0');

    if (accountData.password) {
        formData.append('password', accountData.password);
    }

    if (accountData.password_confirmation) {
        formData.append('password_confirmation', accountData.password_confirmation);
    }

    if (accountData.avatar instanceof File) {
        formData.append('avatar', accountData.avatar);
    }

    return formData;
}

/**
 * Atualiza a conta do usuario autenticado.
 * @param {Object} accountData
 * @returns {Promise<{success: boolean, data?: Object, error?: any}>}
 */
export async function updateAccount(accountData) {
    try {
        const payload = toAccountFormData(accountData);
        const { data } = await axios.post('/account', payload, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });

        return { success: true, data };
    } catch (error) {
        return { success: false, error };
    }
}
