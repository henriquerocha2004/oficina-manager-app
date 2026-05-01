import { describe, it, expect, vi, beforeEach } from 'vitest';

const mockPage = { props: {}, component: '', url: '/' };

vi.mock('@inertiajs/vue3', () => ({
    usePage: () => mockPage,
}));

import { route, routeCurrent, useRoute } from '@/Composables/useRoute.js';

describe('useRoute', () => {

    describe('route()', () => {

        // ── Static tenant routes ─────────────────────────────────────────

        it('returns /dashboard for tenant.dashboard', () => {
            expect(route('tenant.dashboard')).toBe('/dashboard');
        });

        it('returns /clients for clients.index', () => {
            expect(route('clients.index')).toBe('/clients');
        });

        it('returns /service-orders for service-orders.index', () => {
            expect(route('service-orders.index')).toBe('/service-orders');
        });

        it('returns /faq for faq.index', () => {
            expect(route('faq.index')).toBe('/faq');
        });

        it('returns /settings for settings.index', () => {
            expect(route('settings.index')).toBe('/settings');
        });

        // ── Parameterized tenant routes ──────────────────────────────────

        it('returns /clients/:id for clients.show', () => {
            expect(route('clients.show', 'abc-123')).toBe('/clients/abc-123');
        });

        it('returns /vehicles/:id for vehicles.show', () => {
            expect(route('vehicles.show', 'xyz-456')).toBe('/vehicles/xyz-456');
        });

        it('returns /service-orders/:id for service-orders.show', () => {
            expect(route('service-orders.show', 'order-001')).toBe('/service-orders/order-001');
        });

        it('returns /users/:id/edit for users.edit', () => {
            expect(route('users.edit', 'user-789')).toBe('/users/user-789/edit');
        });

        // ── Admin routes ─────────────────────────────────────────────────

        it('returns /admin/dashboard for admin.dashboard', () => {
            expect(route('admin.dashboard')).toBe('/admin/dashboard');
        });

        it('returns /admin/tenants/:id for admin.tenants.show', () => {
            expect(route('admin.tenants.show', 'tenant-1')).toBe('/admin/tenants/tenant-1');
        });

        // ── Error handling ───────────────────────────────────────────────

        it('returns "/" and logs error for unknown route', () => {
            const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {});

            const result = route('non.existent.route');

            expect(result).toBe('/');
            expect(consoleSpy).toHaveBeenCalledWith(
                expect.stringContaining('non.existent.route')
            );

            consoleSpy.mockRestore();
        });
    });

    describe('routeCurrent()', () => {

        beforeEach(() => {
            mockPage.component = '';
            mockPage.url = '/';
        });

        it('returns true for admin.dashboard when component matches', () => {
            mockPage.component = 'Admin/Dashboard';
            expect(routeCurrent('admin.dashboard')).toBe(true);
        });

        it('returns true for tenant.dashboard when component matches', () => {
            mockPage.component = 'Tenant/Dashboard';
            expect(routeCurrent('tenant.dashboard')).toBe(true);
        });

        it('returns true for clients.index when component matches', () => {
            mockPage.component = 'Tenant/Clients/Index';
            expect(routeCurrent('clients.index')).toBe(true);
        });

        it('returns true for service-orders.index on Show component (highlight sidebar)', () => {
            mockPage.component = 'Tenant/ServiceOrders/Show';
            expect(routeCurrent('service-orders.index')).toBe(true);
        });

        it('returns true for service-orders.show when component is Show', () => {
            mockPage.component = 'Tenant/ServiceOrders/Show';
            expect(routeCurrent('service-orders.show')).toBe(true);
        });

        it('returns true for faq.index when component matches', () => {
            mockPage.component = 'Tenant/Faq/Index';
            expect(routeCurrent('faq.index')).toBe(true);
        });

        it('returns true for vehicles.index on History component', () => {
            mockPage.component = 'Tenant/Vehicles/History';
            expect(routeCurrent('vehicles.index')).toBe(true);
        });

        it('returns false when component does not match the route', () => {
            mockPage.component = 'Tenant/Clients/Index';
            mockPage.url = '/clients';
            expect(routeCurrent('service-orders.index')).toBe(false);
        });

        it('falls back to URL match when no component rule matches', () => {
            mockPage.component = 'SomeOtherComponent';
            mockPage.url = '/settings';
            expect(routeCurrent('settings.index')).toBe(true);
        });

        it('returns false when URL does not match and no component rule applies', () => {
            mockPage.component = 'SomeOtherComponent';
            mockPage.url = '/different-page';
            expect(routeCurrent('clients.index')).toBe(false);
        });
    });

    describe('useRoute composable', () => {
        it('exposes route and current functions', () => {
            const { route: r, current } = useRoute();
            expect(typeof r).toBe('function');
            expect(typeof current).toBe('function');
        });

        it('route function works correctly via composable', () => {
            const { route: r } = useRoute();
            expect(r('clients.index')).toBe('/clients');
        });
    });
});
