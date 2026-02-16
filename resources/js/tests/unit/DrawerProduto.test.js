import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import DrawerProduto from '@/Shared/Components/DrawerProduto.vue';
import { useMasks } from '@/Composables/useMasks';

// Mock vee-validate
vi.mock('vee-validate', () => ({
    useForm: () => ({
        handleSubmit: (cb) => () => cb({
            name: 'Test Product',
            description: 'Test Description',
            category: 'engine',
            unit: 'unit',
            unit_price: 'R$ 100,00',
            suggested_price: 'R$ 150,00',
            is_active: true,
        }),
        setValues: vi.fn(),
        resetForm: vi.fn(),
        setFieldValue: vi.fn(),
        values: {},
    }),
}));

// Mock composables
vi.mock('@/Composables/useMasks', () => ({
    useMasks: () => ({
        maskCurrency: (value) => `R$ ${value}`,
        unmaskCurrency: (value) => parseFloat(value.replace(/[^\d,]/g, '').replace(',', '.')) || 0,
    }),
}));

// Mock toast
vi.mock('@/Shared/composables/useToast', () => ({
    useToast: () => ({
        success: vi.fn(),
        error: vi.fn(),
    }),
}));

// Mock product data
vi.mock('@/Data/productData', () => ({
    productCategories: [
        { value: 'engine', label: 'Motor' },
        { value: 'brakes', label: 'Freios' },
    ],
    productUnits: [
        { value: 'unit', label: 'Unidade' },
        { value: 'liter', label: 'Litro' },
    ],
}));

// Mock services
vi.mock('@/services/supplierService', () => ({
    fetchSuppliers: vi.fn().mockResolvedValue({
        items: [
            { id: '01HX123', name: 'Supplier 1' },
            { id: '01HX456', name: 'Supplier 2' },
        ],
    }),
}));

vi.mock('@/services/productService', () => ({
    attachSupplier: vi.fn().mockResolvedValue({
        success: true,
        data: {
            data: {
                product: {
                    id: '01HX789',
                    suppliers: [{ id: '01HX123', name: 'Supplier 1', pivot: { unit_price: 50, delivery_time: 7 } }],
                },
            },
        },
    }),
    updateProductSupplier: vi.fn().mockResolvedValue({
        success: true,
        data: {
            data: {
                product: {
                    id: '01HX789',
                    suppliers: [{ id: '01HX123', name: 'Supplier 1', pivot: { unit_price: 60, delivery_time: 5 } }],
                },
            },
        },
    }),
    detachSupplier: vi.fn().mockResolvedValue({
        success: true,
        data: {
            data: {
                product: {
                    id: '01HX789',
                    suppliers: [],
                },
            },
        },
    }),
}));

describe('DrawerProduto.vue', () => {
    let wrapper;

    const createWrapper = (props = {}) => {
        return mount(DrawerProduto, {
            props: {
                open: true,
                isEdit: false,
                product: null,
                ...props,
            },
            global: {
                stubs: {
                    teleport: true,
                    FormField: {
                        template: '<div><slot :field="{ value: modelValue }" :errors="[]" /></div>',
                        props: ['name', 'label', 'modelValue'],
                    },
                    FormError: { template: '<div></div>' },
                },
            },
        });
    };

    beforeEach(() => {
        wrapper = createWrapper();
    });

    it('should render drawer when open', () => {
        // Assert
        expect(wrapper.find('.fixed').exists()).toBe(true);
    });

    it('should display correct title for new product', () => {
        // Assert
        expect(wrapper.text()).toContain('Novo Produto');
    });

    it('should display correct title for edit product', () => {
        // Arrange
        wrapper = createWrapper({ isEdit: true });
        
        // Assert
        expect(wrapper.text()).toContain('Editar Produto');
    });

    it('should emit close event when close button is clicked', async () => {
        // Arrange
        const closeButton = wrapper.find('.kt-btn-icon');
        
        // Act
        await closeButton.trigger('click');
        
        // Assert
        expect(wrapper.emitted('close')).toBeTruthy();
    });

    it('should emit submit event when form is submitted', async () => {
        // Arrange
        const form = wrapper.find('form');
        
        // Act
        await form.trigger('submit.prevent');
        
        // Assert
        expect(wrapper.emitted('submit')).toBeTruthy();
    });

    it('should show tabs only in edit mode', () => {
        // Arrange - new product
        wrapper = createWrapper({ isEdit: false });
        
        // Assert
        expect(wrapper.find('[data-kt-tabs]').exists()).toBe(false);
        
        // Arrange - edit product
        wrapper = createWrapper({ 
            isEdit: true,
            product: {
                id: '01HX789',
                name: 'Test Product',
                suppliers: [],
            }
        });
        
        // Assert
        expect(wrapper.find('[data-kt-tabs]').exists()).toBe(true);
    });

    it('should display supplier tab only in edit mode', () => {
        // Arrange - new product
        wrapper = createWrapper({ isEdit: false });
        
        // Assert
        expect(wrapper.text()).not.toContain('Fornecedores');
        
        // Arrange - edit product
        wrapper = createWrapper({ 
            isEdit: true,
            product: {
                id: '01HX789',
                name: 'Test Product',
                suppliers: [],
            }
        });
        
        // Assert
        expect(wrapper.text()).toContain('Fornecedores');
    });

    it('should display product suppliers list in edit mode', async () => {
        // Arrange
        wrapper = createWrapper({ 
            isEdit: true,
            product: {
                id: '01HX789',
                name: 'Test Product',
                suppliers: [
                    {
                        id: '01HX123',
                        name: 'Supplier 1',
                        pivot: {
                            unit_price: 50,
                            delivery_time: 7,
                        },
                    },
                ],
            }
        });
        
        await wrapper.vm.$nextTick();
        
        // Assert
        // Verifica se o componente renderiza a aba de fornecedores
        expect(wrapper.find('#tab_product_suppliers').exists()).toBe(true);
    });

    it('should display empty state when no suppliers', () => {
        // Arrange
        wrapper = createWrapper({ 
            isEdit: true,
            product: {
                id: '01HX789',
                name: 'Test Product',
                suppliers: [],
            }
        });
        
        // Assert
        expect(wrapper.text()).toContain('Nenhum fornecedor vinculado');
    });

    it('should render category select with options', () => {
        // Assert
        const categorySelect = wrapper.find('select');
        expect(categorySelect.exists()).toBe(true);
    });

    it('should render unit select with options', () => {
        // Assert
        const selects = wrapper.findAll('select');
        expect(selects.length).toBeGreaterThan(0);
    });

    it('should have checkbox for is_active field', () => {
        // Assert
        const checkbox = wrapper.find('input[type="checkbox"]');
        expect(checkbox.exists()).toBe(true);
    });
});
