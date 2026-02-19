import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import DrawerAjusteEstoque from '@/Shared/Components/DrawerAjusteEstoque.vue';

vi.mock('vee-validate', () => ({
    useForm: () => ({
        handleSubmit: (cb) => () => cb({
            movement_type: 'IN',
            quantity: 10,
            reason: 'purchase',
            notes: 'Test notes',
        }),
        resetForm: vi.fn(),
        setFieldValue: vi.fn(),
    }),
}));

vi.mock('@/Data/productData', () => ({
    getCategoryLabel: (value) => value === 'engine' ? 'Motor' : value,
    getUnitLabel: (value) => value === 'unit' ? 'Unidade' : value,
}));

vi.mock('@/Data/stockData', () => ({
    movementReasons: [
        { value: 'purchase', label: 'Compra' },
        { value: 'sale', label: 'Venda' },
        { value: 'adjustment', label: 'Ajuste' },
    ],
}));

describe('DrawerAjusteEstoque.vue', () => {
    let wrapper;

    const mockProductWithStock = {
        id: '01HXQ5Y9XXXXXXXXXXXXXXXXXXX',
        name: 'Filtro de Óleo',
        category: 'engine',
        unit: 'unit',
        unit_price: 25.50,
        is_active: true,
        stock_movements: [
            {
                id: '01HXQ5Y9ZZZZZZZZZZZZZZZZZZZ',
                movement_type: 'IN',
                quantity: 50,
                balance_after: 50,
                reason: 'purchase',
                created_at: '2024-02-17T10:30:00.000000Z',
            },
        ],
    };

    const mockProductWithoutStock = {
        id: '01HXQ5Y9XXXXXXXXXXXXXXXXXXX',
        name: 'Filtro de Óleo',
        category: 'engine',
        unit: 'unit',
        unit_price: 25.50,
        is_active: true,
        stock_movements: [],
    };

    const createWrapper = (props = {}) => {
        return mount(DrawerAjusteEstoque, {
            props: {
                open: true,
                product: mockProductWithStock,
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
        expect(wrapper.find('.fixed').exists()).toBe(true);
    });

    it('should display correct title', () => {
        expect(wrapper.text()).toContain('Ajuste de Estoque');
    });

    it('should calculate current balance correctly', () => {
        expect(wrapper.vm.currentBalance).toBe(50);
    });

    it('should show positive stock status when balance is greater than 0', () => {
        expect(wrapper.text()).toContain('Em Estoque');
        expect(wrapper.vm.stockStatusClass).toBe('stock-status-positive');
    });

    it('should show negative stock status when balance is 0', () => {
        wrapper = createWrapper({ product: mockProductWithoutStock });
        expect(wrapper.text()).toContain('Sem Estoque');
        expect(wrapper.vm.stockStatusClass).toBe('stock-status-negative');
    });

    it('should display product name and category', () => {
        expect(wrapper.text()).toContain('Filtro de Óleo');
        expect(wrapper.text()).toContain('Motor');
        expect(wrapper.text()).toContain('Unidade');
    });

    it('should display current balance', () => {
        expect(wrapper.text()).toContain('50');
    });

    it('should emit close event when close button is clicked', async () => {
        const closeButton = wrapper.find('.kt-btn-icon');
        await closeButton.trigger('click');
        expect(wrapper.emitted('close')).toBeTruthy();
    });

    it('should emit close event when cancel button is clicked', async () => {
        const cancelButton = wrapper.find('.kt-btn-ghost');
        await cancelButton.trigger('click');
        expect(wrapper.emitted('close')).toBeTruthy();
    });

    it('should emit submit event with form data when submitted', async () => {
        const form = wrapper.find('form');
        await form.trigger('submit');
        expect(wrapper.emitted('submit')).toBeTruthy();
        expect(wrapper.emitted('submit')[0]).toEqual([{
            movement_type: 'IN',
            quantity: 10,
            reason: 'purchase',
            notes: 'Test notes',
        }]);
    });

    it('should not render when open is false', () => {
        wrapper = createWrapper({ open: false });
        expect(wrapper.find('.fixed').exists()).toBe(false);
    });

    it('should calculate balance as 0 when no movements', () => {
        wrapper = createWrapper({ product: mockProductWithoutStock });
        expect(wrapper.vm.currentBalance).toBe(0);
    });
});
