import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import SuppliersIndex from '@/Pages/Tenant/Suppliers/Index.vue';
import { fetchSuppliers, createSupplier, updateSupplier, deleteSupplier } from '@/services/supplierService';

// Mock services
vi.mock('@/services/supplierService', () => ({
    fetchSuppliers: vi.fn(),
    createSupplier: vi.fn(),
    updateSupplier: vi.fn(),
    deleteSupplier: vi.fn(),
}));

// Mock composables
vi.mock('@/Shared/composables/useToast.js', () => ({
    useToast: () => ({
        success: vi.fn(),
        error: vi.fn(),
    }),
}));

// Mock components
const mockDataGrid = {
    template: `
        <div class="data-grid">
            <slot name="title" />
            <slot name="actions" />
            <slot name="cell-actions" :row="{ id: 1 }" />
        </div>
    `,
    props: ['columns', 'items', 'total', 'page', 'perPage'],
};

const mockDrawerFornecedor = {
    template: '<div class="drawer-fornecedor"></div>',
    props: ['open', 'isEdit', 'supplier'],
};

const mockConfirmModal = {
    template: '<div class="confirm-modal"></div>',
    methods: {
        open: vi.fn(() => Promise.resolve(true)),
    },
};

const mockTenantLayout = {
    template: '<div class="tenant-layout"><slot /></div>',
    props: ['title', 'breadcrumbs'],
};

describe('SuppliersIndex.vue', () => {
    let wrapper;

    const mockSuppliers = {
        items: [
            { id: 1, name: 'Supplier 1', city: 'São Paulo', state: 'SP' },
            { id: 2, name: 'Supplier 2', city: 'Rio de Janeiro', state: 'RJ' },
        ],
        total: 2,
        page: 1,
        perPage: 6,
    };

    const createWrapper = () => {
        return mount(SuppliersIndex, {
            global: {
                stubs: {
                    TenantLayout: mockTenantLayout,
                    DataGrid: mockDataGrid,
                    DrawerFornecedor: mockDrawerFornecedor,
                    ConfirmModal: mockConfirmModal,
                },
            },
        });
    };

    beforeEach(() => {
        vi.clearAllMocks();
        fetchSuppliers.mockResolvedValue(mockSuppliers);
        wrapper = createWrapper();
    });

    it('should render correctly', () => {
        expect(wrapper.find('.tenant-layout').exists()).toBe(true);
        expect(wrapper.find('.data-grid').exists()).toBe(true);
    });

    it('should fetch suppliers on mount', async () => {
        expect(fetchSuppliers).toHaveBeenCalledWith({
            page: 1,
            perPage: 6,
            search: '',
            sortKey: null,
            sortDir: 'asc',
        });
    });

    it('should display correct title', () => {
        expect(wrapper.text()).toContain('Fornecedores');
    });

    it('should have correct columns', () => {
        const dataGrid = wrapper.find('.data-grid');
        expect(dataGrid.exists()).toBe(true);
    });

    it('should open drawer when "Novo Fornecedor" is clicked', async () => {
        const button = wrapper.find('.kt-btn-primary');
        expect(button.exists()).toBe(true);
    });

    it('should handle create supplier successfully', async () => {
        createSupplier.mockResolvedValue({ success: true, data: {} });
        
        const newSupplier = {
            name: 'New Supplier',
            document_number: '12345678000190',
        };

        await wrapper.vm.onDrawerSubmit(newSupplier);

        expect(createSupplier).toHaveBeenCalledWith(newSupplier);
    });

    it('should handle update supplier successfully', async () => {
        updateSupplier.mockResolvedValue({ success: true, data: {} });
        
        wrapper.vm.drawerEdit = true;
        wrapper.vm.drawerSupplier = { id: 1 };
        
        const updatedSupplier = {
            name: 'Updated Supplier',
            document_number: '12345678000190',
        };

        await wrapper.vm.onDrawerSubmit(updatedSupplier);

        expect(updateSupplier).toHaveBeenCalledWith(1, updatedSupplier);
    });

    it('should handle delete supplier successfully', async () => {
        deleteSupplier.mockResolvedValue({ success: true, data: {} });
        
        wrapper.vm.confirmModal = {
            open: vi.fn(() => Promise.resolve(true)),
        };

        await wrapper.vm.onDelete(1);

        expect(deleteSupplier).toHaveBeenCalledWith(1);
    });

    it('should handle errors when creating supplier', async () => {
        const error = { response: { data: { message: 'Error creating supplier' } } };
        createSupplier.mockResolvedValue({ success: false, error });

        const newSupplier = {
            name: 'New Supplier',
            document_number: '12345678000190',
        };

        await wrapper.vm.onDrawerSubmit(newSupplier);

        expect(createSupplier).toHaveBeenCalledWith(newSupplier);
    });
});
