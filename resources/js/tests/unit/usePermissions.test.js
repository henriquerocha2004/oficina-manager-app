import { describe, it, expect, vi } from 'vitest';

// Mock must be declared before importing the composable
const mockPage = { props: { auth: { user: { role: null } } } };

vi.mock('@inertiajs/vue3', () => ({
    usePage: () => mockPage,
}));

import { usePermissions } from '@/Composables/usePermissions.js';

function withRole(role) {
    mockPage.props.auth.user.role = role;
}

describe('usePermissions', () => {

    describe('role-based computed properties', () => {
        it('isAdministrator is true when role is administrator', () => {
            withRole('administrator');
            const { isAdministrator } = usePermissions();
            expect(isAdministrator.value).toBe(true);
        });

        it('isAdministrator is false when role is reception', () => {
            withRole('reception');
            const { isAdministrator } = usePermissions();
            expect(isAdministrator.value).toBe(false);
        });

        it('isReception is true when role is reception', () => {
            withRole('reception');
            const { isReception } = usePermissions();
            expect(isReception.value).toBe(true);
        });

        it('isReception is false when role is administrator', () => {
            withRole('administrator');
            const { isReception } = usePermissions();
            expect(isReception.value).toBe(false);
        });

        it('isMechanic is true when role is mechanic', () => {
            withRole('mechanic');
            const { isMechanic } = usePermissions();
            expect(isMechanic.value).toBe(true);
        });

        it('isMechanic is false when role is administrator', () => {
            withRole('administrator');
            const { isMechanic } = usePermissions();
            expect(isMechanic.value).toBe(false);
        });

        it('all role flags are false when user has no role', () => {
            mockPage.props.auth.user.role = null;
            const { isAdministrator, isReception, isMechanic } = usePermissions();
            expect(isAdministrator.value).toBe(false);
            expect(isReception.value).toBe(false);
            expect(isMechanic.value).toBe(false);
        });
    });

    describe('hasRole', () => {
        it('returns true when user has one of the given roles', () => {
            withRole('reception');
            const { hasRole } = usePermissions();
            expect(hasRole('administrator', 'reception')).toBe(true);
        });

        it('returns false when user does not have any of the given roles', () => {
            withRole('mechanic');
            const { hasRole } = usePermissions();
            expect(hasRole('administrator', 'reception')).toBe(false);
        });
    });

    describe('canCreateServiceOrder', () => {
        it('returns true for administrator', () => {
            withRole('administrator');
            const { canCreateServiceOrder } = usePermissions();
            expect(canCreateServiceOrder()).toBe(true);
        });

        it('returns true for reception', () => {
            withRole('reception');
            const { canCreateServiceOrder } = usePermissions();
            expect(canCreateServiceOrder()).toBe(true);
        });

        it('returns false for mechanic', () => {
            withRole('mechanic');
            const { canCreateServiceOrder } = usePermissions();
            expect(canCreateServiceOrder()).toBe(false);
        });
    });

    describe('canManageUsers', () => {
        it('returns true for administrator', () => {
            withRole('administrator');
            const { canManageUsers } = usePermissions();
            expect(canManageUsers()).toBe(true);
        });

        it('returns false for reception', () => {
            withRole('reception');
            const { canManageUsers } = usePermissions();
            expect(canManageUsers()).toBe(false);
        });

        it('returns false for mechanic', () => {
            withRole('mechanic');
            const { canManageUsers } = usePermissions();
            expect(canManageUsers()).toBe(false);
        });
    });

    describe('canAccessBackoffice', () => {
        it('returns true for administrator', () => {
            withRole('administrator');
            const { canAccessBackoffice } = usePermissions();
            expect(canAccessBackoffice()).toBe(true);
        });

        it('returns true for reception', () => {
            withRole('reception');
            const { canAccessBackoffice } = usePermissions();
            expect(canAccessBackoffice()).toBe(true);
        });

        it('returns false for mechanic', () => {
            withRole('mechanic');
            const { canAccessBackoffice } = usePermissions();
            expect(canAccessBackoffice()).toBe(false);
        });
    });
});
