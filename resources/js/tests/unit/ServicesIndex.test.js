import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import Index from '@/Pages/Tenant/Services/Index.vue';
import { fetchServices, createService, updateService, deleteService } from '@/services/serviceService';
import { useToast } from '@/Shared/composables/useToast';

// Mock the services
vi.mock('@/services/serviceService');
vi.mock('@/Shared/composables/useToast');
vi.mock('@/Composables/useServiceFilters');

// Mock Inertia
vi.mock('@inertiajs/vue3', () => ({
    usePage: () => ({
        props: {
            auth: { user: { name: 'Test User' } },
            flash: {},
        },
    }),
}));

// Mock components
vi.mock('../../../resources/js/Shared/Components/DataGrid.vue', () => ({
    default: {
        name: 'DataGrid',
        template: '<div data-testid="data-grid"><slot name="title"/><slot name="actions"/><slot name="cell-actions" :row="{id:1}"/></div>',
        props: ['columns', 'items', 'total', 'page', 'perPage'],
        emits: ['update:page', 'sort', 'search'],
    },
}));

vi.mock('../../../resources/js/Shared/Components/DrawerServico.vue', () => ({
    default: {
        name: 'DrawerServico',
        template: '<div data-testid="drawer-servico"><slot/></div>',
        props: ['open', 'isEdit', 'service'],
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

describe('Services Index.vue', () => {
    let wrapper;
    let mockFetchServices;
    let mockCreateService;
    let mockUpdateService;
    let mockDeleteService;
    let mockToast;

    beforeEach(() => {
        mockFetchServices = vi.mocked(fetchServices);
        mockCreateService = vi.mocked(createService);
        mockUpdateService = vi.mocked(updateService);
        mockDeleteService = vi.mocked(deleteService);
        mockToast = { success: vi.fn(), error: vi.fn() };
        useToast.mockReturnValue(mockToast);

        mockFetchServices.mockResolvedValue({
            items: [{ id: 1, name: 'Troca de óleo', category: 'maintenance', base_price: 150.00, is_active: true }],
            total: 1,
            page: 1,
            perPage: 6,
        });

        wrapper = mount(Index, {
            global: {
                stubs: ['DataGrid', 'DrawerServico', 'ConfirmModal', 'TenantLayout'],
            },
        });
    });

    it('should load services on mount', async () => {
        await wrapper.vm.$nextTick();
        expect(mockFetchServices).toHaveBeenCalledWith({
            page: 1,
            perPage: 6,
            search: '',
            sortKey: null,
            sortDir: 'asc',
            filters: {
                category: undefined,
            }
        });
        expect(wrapper.vm.items).toEqual([{ id: 1, name: 'Troca de óleo', category: 'maintenance', base_price: 150.00, is_active: true }]);
    });

    it('should open drawer for edit', async () => {
        wrapper.vm.items = [{ id: 1, name: 'Troca de óleo', category: 'maintenance' }];
        await wrapper.vm.$nextTick();
        wrapper.vm.onEdit(1);
        expect(wrapper.vm.drawerOpen).toBe(true);
        expect(wrapper.vm.drawerEdit).toBe(true);
        expect(wrapper.vm.drawerService).toEqual({ id: 1, name: 'Troca de óleo', category: 'maintenance' });
    });

    it('should create service successfully', async () => {
        mockCreateService.mockResolvedValue({ success: true, data: { id: 2 } });
        const data = { name: 'Novo Serviço', category: 'repair', base_price: 200.00 };

        await wrapper.vm.onDrawerSubmit(data);

        expect(mockCreateService).toHaveBeenCalledWith(data);
        expect(mockToast.success).toHaveBeenCalledWith('Serviço criado com sucesso!');
        expect(wrapper.vm.drawerOpen).toBe(false);
        expect(mockFetchServices).toHaveBeenCalled();
    });

    it('should handle create service error', async () => {
        const error = new Error('Create failed');
        mockCreateService.mockResolvedValue({ success: false, error });

        const data = { name: 'Novo Serviço' };
        await wrapper.vm.onDrawerSubmit(data);

        expect(mockToast.error).toHaveBeenCalledWith('Erro ao criar serviço: Create failed');
    });

    it('should update service successfully', async () => {
        wrapper.vm.drawerEdit = true;
        wrapper.vm.drawerService = { id: 1 };
        mockUpdateService.mockResolvedValue({ success: true, data: { id: 1 } });

        const data = { name: 'Serviço Atualizado', category: 'repair', base_price: 250.00 };
        await wrapper.vm.onDrawerSubmit(data);

        expect(mockUpdateService).toHaveBeenCalledWith(1, data);
        expect(mockToast.success).toHaveBeenCalledWith('Serviço atualizado com sucesso!');
    });
});
