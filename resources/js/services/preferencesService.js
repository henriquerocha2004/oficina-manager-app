import axios from 'axios';

/**
 * Salva uma ou mais preferências do usuário autenticado no backend.
 * Operação silenciosa — falhas não propagam erro ao chamador.
 *
 * Para expandir: adicione novos campos ao endpoint PATCH /account/preferences
 * e à validação no UserController::updatePreferences.
 *
 * @param {Object} preferences - ex: { theme: 'dark' } ou { so_view_mode: 'kanban' }
 */
export async function savePreferences(preferences) {
    try {
        await axios.patch('/account/preferences', preferences);
    } catch {
        // Falha silenciosa — localStorage já mantém o valor localmente
    }
}
