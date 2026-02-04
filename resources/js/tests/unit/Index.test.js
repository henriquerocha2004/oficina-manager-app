import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import { createApp } from 'vue';
import Index from '@/Pages/Tenant/Clients/Index.vue';
import { fetchClients, createClient, updateClient, deleteClient } from '@/services/clientService';
import { useToast } from '@/Shared/composables/useToast';
import { useMasks } from '@/Composables/useMasks';

// Mock the services
vi.mock('@/services/clientService');
vi.mock('@/Shared/composables/useToast');
vi.mock('@/Composables/useMasks');

// Mock Inertia
vi.mock('@inertiajs/vue3', () => ({
    usePage: () => ({
        props: {
            auth: { user: { name: 'Test User' } },
            flash: {},
        },
    }),
}));

// Mock DataGrid and other components
vi.mock('../../../resources/js/Shared/Components/DataGrid.vue', () => ({
    default: {
        name: 'DataGrid',
        template: '<div data-testid="data-grid"><slot name="title"/><slot name="actions"/><slot name="cell-actions" :row="{id:1}"/></div>',
        props: ['columns', 'items', 'total', 'page', 'perPage'],
        emits: ['update:page', 'sort', 'search'],
    },
}));

vi.mock('../../../resources/js/Shared/Components/DrawerCliente.vue', () => ({
    default: {
        name: 'DrawerCliente',
        template: '<div data-testid="drawer-cliente"><slot/></div>',
        props: ['open', 'isEdit', 'client'],
        emits: ['close', 'submit'],
    },
}));

vi.mock('../../../resources/js/Shared/Components/ConfirmModal.vue', () => ({
    default: {
        name: 'ConfirmModal',
        template: '<div data-testid="confirm-modal"><slot/></div>',
        methods: {
            open: vi.fn(),
        },
    },
}));

vi.mock('@/Layouts/TenantLayout.vue', () => ({
    default: {
        name: 'TenantLayout',
        template: '<div><slot name="default"/></div>',
        props: ['title', 'breadcrumbs'],
    },
}));

describe('Index.vue', () => {
    let wrapper;
    let mockFetchClients;
    let mockCreateClient;
    let mockUpdateClient;
    let mockDeleteClient;
    let mockToast;
    let mockUnmask;

    beforeEach(() => {
        mockFetchClients = vi.mocked(fetchClients);
        mockCreateClient = vi.mocked(createClient);
        mockUpdateClient = vi.mocked(updateClient);
        mockDeleteClient = vi.mocked(deleteClient);
        mockToast = { success: vi.fn(), error: vi.fn() };
        useToast.mockReturnValue(mockToast);
        mockUnmask = vi.fn((value) => value);
        useMasks.mockReturnValue({ unmask: mockUnmask });

        mockFetchClients.mockResolvedValue({
            items: [{ id: 1, name: 'Client 1' }],
            total: 1,
            page: 1,
            perPage: 6,
        });

        wrapper = mount(Index, {
            global: {
                stubs: ['DataGrid', 'DrawerCliente', 'ConfirmModal', 'TenantLayout'],
            },
        });
    });

    it('should load clients on mount', async () => {
        await wrapper.vm.$nextTick();
        expect(mockFetchClients).toHaveBeenCalledWith({
            page: 1,
            perPage: 6,
            search: '',
            sortKey: null,
            sortDir: 'asc',
        });
        expect(wrapper.vm.items).toEqual([{ id: 1, name: 'Client 1' }]);
    });

    it('should open drawer for edit', async () => {
        wrapper.vm.items = [{ id: 1, name: 'Client 1' }];
        await wrapper.vm.$nextTick();
        wrapper.vm.onEdit(1);
        expect(wrapper.vm.drawerOpen).toBe(true);
        expect(wrapper.vm.drawerEdit).toBe(true);
        expect(wrapper.vm.drawerClient).toEqual({ id: 1, name: 'Client 1' });
    });

    it('should create client successfully', async () => {
        mockCreateClient.mockResolvedValue({ success: true, data: { id: 2 } });
        const data = { name: 'New Client', document: '123', phone: '456', zip_code: '789' };

        await wrapper.vm.onDrawerSubmit(data);

        expect(mockUnmask).toHaveBeenCalledTimes(3);
        expect(mockCreateClient).toHaveBeenCalledWith({
            name: 'New Client',
            document: '123',
            phone: '456',
            zip_code: '789',
        });
        expect(mockToast.success).toHaveBeenCalledWith('Cliente criado com sucesso!');
        expect(wrapper.vm.drawerOpen).toBe(false);
        expect(mockFetchClients).toHaveBeenCalled();
    });

    it('should handle create client error', async () => {
        const error = new Error('Create failed');
        mockCreateClient.mockResolvedValue({ success: false, error });

        const data = { name: 'New Client' };
        await wrapper.vm.onDrawerSubmit(data);

        expect(mockToast.error).toHaveBeenCalledWith('Erro ao criar cliente: Create failed');
    });

    it('should update client successfully', async () => {
        wrapper.vm.drawerEdit = true;
        wrapper.vm.drawerClient = { id: 1 };
        mockUpdateClient.mockResolvedValue({ success: true, data: { id: 1 } });

        const data = { name: 'Updated Client', document: '123', phone: '456', zip_code: '789' };
        await wrapper.vm.onDrawerSubmit(data);

        expect(mockUpdateClient).toHaveBeenCalledWith(1, {
            name: 'Updated Client',
            document: '123',
            phone: '456',
            zip_code: '789',
        });
        expect(mockToast.success).toHaveBeenCalledWith('Cliente atualizado com sucesso!');
    });
});