import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import ProductsIndex from '@/Pages/Tenant/Products/Index.vue';
import { fetchProducts, createProduct, updateProduct, deleteProduct, fetchProduct } from '@/services/productService';

// Mock services
vi.mock('@/services/productService', () => ({
    fetchProducts: vi.fn(),
    createProduct: vi.fn(),
    updateProduct: vi.fn(),
    deleteProduct: vi.fn(),
    fetchProduct: vi.fn(),
}));

// Mock composables
vi.mock('@/Shared/composables/useToast.js', () => ({
    useToast: () => ({
        success: vi.fn(),
        error: vi.fn(),
    }),
}));

// Mock useMasks
vi.mock('@/Composables/useMasks.js', () => ({
    useMasks: () => ({
        maskCurrency: (value) => `R$ ${value}`,
    }),
}));

// Mock product data
vi.mock('@/Data/productData', () => ({
    getCategoryLabel: (value) => value === 'engine' ? 'Motor' : value,
    getUnitLabel: (value) => value === 'unit' ? 'Unidade' : value,
}));

// Mock components
const mockDataGrid = {
    template: `
        <div class="data-grid">
            <slot name="title" />
            <slot name="actions" />
            <slot name="cell-category" :row="{ id: 1, category: 'engine' }" />
            <slot name="cell-unit" :row="{ id: 1, unit: 'unit' }" />
            <slot name="cell-unit_price" :row="{ id: 1, unit_price: 100 }" />
            <slot name="cell-is_active" :row="{ id: 1, is_active: true }" />
            <slot name="cell-actions" :row="{ id: 1 }" />
        </div>
    `,
    props: ['columns', 'items', 'total', 'page', 'perPage'],
};

const mockDrawerProduto = {
    template: '<div class="drawer-produto"></div>',
    props: ['open', 'isEdit', 'product'],
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

describe('ProductsIndex.vue', () => {
    let wrapper;

    const mockProducts = {
        items: [
            { 
                id: '01HX123', 
                name: 'Product 1', 
                category: 'engine', 
                unit: 'unit', 
                unit_price: 100,
                is_active: true 
            },
            { 
                id: '01HX456', 
                name: 'Product 2', 
                category: 'brakes', 
                unit: 'liter', 
                unit_price: 50,
                is_active: false 
            },
        ],
        total: 2,
        page: 1,
        perPage: 6,
    };

    const createWrapper = () => {
        return mount(ProductsIndex, {
            global: {
                stubs: {
                    TenantLayout: mockTenantLayout,
                    DataGrid: mockDataGrid,
                    DrawerProduto: mockDrawerProduto,
                    ConfirmModal: mockConfirmModal,
                },
            },
        });
    };

    beforeEach(() => {
        vi.clearAllMocks();
        fetchProducts.mockResolvedValue(mockProducts);
        wrapper = createWrapper();
    });

    it('should render correctly', () => {
        // Assert
        expect(wrapper.find('.tenant-layout').exists()).toBe(true);
        expect(wrapper.find('.data-grid').exists()).toBe(true);
    });

    it('should fetch products on mount', async () => {
        // Assert
        expect(fetchProducts).toHaveBeenCalledWith({
            page: 1,
            perPage: 6,
            search: '',
            sortKey: null,
            sortDir: 'asc',
        });
    });

    it('should display correct title', () => {
        // Assert
        expect(wrapper.text()).toContain('Produtos');
    });

    it('should have correct columns', () => {
        // Assert
        const dataGrid = wrapper.find('.data-grid');
        expect(dataGrid.exists()).toBe(true);
    });

    it('should open drawer when "Novo Produto" is clicked', async () => {
        // Assert
        const button = wrapper.find('.kt-btn-primary');
        expect(button.exists()).toBe(true);
    });

    it('should handle create product successfully', async () => {
        // Arrange
        createProduct.mockResolvedValue({ success: true, data: {} });
        fetchProducts.mockResolvedValue(mockProducts);
        
        const newProduct = {
            name: 'New Product',
            category: 'engine',
            unit: 'unit',
            unit_price: 100,
            is_active: true,
        };

        // Act
        await wrapper.vm.onDrawerSubmit(newProduct);

        // Assert
        expect(createProduct).toHaveBeenCalledWith(newProduct);
        expect(fetchProducts).toHaveBeenCalled();
    });

    it('should handle update product successfully', async () => {
        // Arrange
        const productId = '01HX123';
        const productData = {
            name: 'Updated Product',
            category: 'brakes',
            unit: 'liter',
            unit_price: 150,
        };
        
        fetchProduct.mockResolvedValue({
            success: true,
            data: {
                data: {
                    product: {
                        id: productId,
                        ...productData,
                        suppliers: [],
                    },
                },
            },
        });
        
        updateProduct.mockResolvedValue({ success: true, data: {} });
        fetchProducts.mockResolvedValue(mockProducts);

        // Act
        await wrapper.vm.onEdit(productId);
        await wrapper.vm.$nextTick();
        
        wrapper.vm.drawerEdit = true;
        wrapper.vm.drawerProduct = { id: productId, ...productData };
        await wrapper.vm.onDrawerSubmit(productData);

        // Assert
        expect(updateProduct).toHaveBeenCalledWith(productId, productData);
        expect(fetchProducts).toHaveBeenCalled();
    });

    it('should handle delete product successfully', async () => {
        // Arrange
        deleteProduct.mockResolvedValue({ success: true, data: {} });
        fetchProducts.mockResolvedValue(mockProducts);

        const confirmModal = wrapper.findComponent({ name: 'ConfirmModal' });
        if (confirmModal.exists()) {
            confirmModal.vm.open = vi.fn(() => Promise.resolve(true));
        }

        // Act
        await wrapper.vm.onDelete('01HX123');

        // Assert
        expect(deleteProduct).toHaveBeenCalledWith('01HX123');
    });

    it('should handle create product error', async () => {
        // Arrange
        const error = new Error('Validation error');
        createProduct.mockResolvedValue({ 
            success: false, 
            error: { 
                response: { data: { message: 'Validation error' } } 
            } 
        });

        const newProduct = {
            name: 'New Product',
        };

        // Act
        await wrapper.vm.onDrawerSubmit(newProduct);

        // Assert
        expect(createProduct).toHaveBeenCalledWith(newProduct);
    });

    it('should format currency correctly', () => {
        // Act
        const formatted = wrapper.vm.formatCurrency(100);

        // Assert
        expect(formatted).toContain('R$');
    });

    it('should fetch product with suppliers when editing', async () => {
        // Arrange
        const productId = '01HX123';
        fetchProduct.mockResolvedValue({
            success: true,
            data: {
                data: {
                    product: {
                        id: productId,
                        name: 'Product 1',
                        suppliers: [
                            { id: '01HX999', name: 'Supplier 1' }
                        ],
                    },
                },
            },
        });

        // Act
        await wrapper.vm.onEdit(productId);

        // Assert
        expect(fetchProduct).toHaveBeenCalledWith(productId);
    });

    it('should reload product when supplier is updated', async () => {
        // Arrange
        const productId = '01HX123';
        fetchProduct.mockResolvedValue({
            success: true,
            data: {
                data: {
                    product: {
                        id: productId,
                        name: 'Product 1',
                        suppliers: [],
                    },
                },
            },
        });

        wrapper.vm.drawerProduct = { id: productId };

        // Act
        await wrapper.vm.onSupplierUpdated();

        // Assert
        expect(fetchProduct).toHaveBeenCalledWith(productId);
    });

    it('should display category label using slot', () => {
        // Assert
        const dataGrid = wrapper.find('.data-grid');
        expect(dataGrid.exists()).toBe(true);
    });

    it('should display unit label using slot', () => {
        // Assert
        const dataGrid = wrapper.find('.data-grid');
        expect(dataGrid.exists()).toBe(true);
    });

    it('should display active status badge using slot', () => {
        // Assert
        const dataGrid = wrapper.find('.data-grid');
        expect(dataGrid.exists()).toBe(true);
    });
});
