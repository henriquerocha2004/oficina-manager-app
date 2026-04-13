import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

/**
 * Composable de permissões por perfil de usuário.
 *
 * Espelha as regras definidas em config/tenant_permissions.php.
 * Use para esconder/mostrar elementos da UI conforme o perfil do usuário autenticado.
 *
 * Para expandir: adicione novos helpers aqui conforme novas regras forem criadas no backend.
 */
export function usePermissions() {
    const page = usePage();

    const role = computed(() => page.props.auth?.user?.role ?? null);

    const isAdministrator = computed(() => role.value === 'administrator');
    const isReception     = computed(() => role.value === 'reception');
    const isMechanic      = computed(() => role.value === 'mechanic');

    /**
     * Verifica se o usuário possui um dos perfis informados.
     * @param {...string} roles
     * @returns {boolean}
     */
    function hasRole(...roles) {
        return roles.includes(role.value);
    }

    /** Pode criar ou deletar Ordens de Serviço */
    function canCreateServiceOrder() {
        return hasRole('administrator', 'reception');
    }

    /** Pode gerenciar usuários (CRUD) */
    function canManageUsers() {
        return hasRole('administrator');
    }

    /** Pode acessar módulos de clientes, veículos, serviços, etc. */
    function canAccessBackoffice() {
        return hasRole('administrator', 'reception');
    }

    return {
        role,
        isAdministrator,
        isReception,
        isMechanic,
        hasRole,
        canCreateServiceOrder,
        canManageUsers,
        canAccessBackoffice,
    };
}
