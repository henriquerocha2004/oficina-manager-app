import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import DrawerVisualizarProduto from '@/Shared/Components/DrawerVisualizarProduto.vue';

vi.mock('@/Composables/useMasks', () => ({
    useMasks: () => ({
        maskCurrency: (value) => `R$ ${value}`,
    }),
}));

vi.mock('@/Data/productData', () => ({
    getCategoryLabel: (value) => value === 'engine' ? 'Motor' : value,
    getUnitLabel: (value) => value === 'unit' ? 'Unidade' : value,
}));

vi.mock('@/Data/stockData', () => ({
    getMovementTypeLabel: (value) => value === 'IN' ? 'Entrada' : 'Saída',
    getMovementReasonLabel: (value) => value === 'purchase' ? 'Compra' : value,
    getMovementTypeIcon: (value) => value === 'IN' ? 'ki-arrow-down' : 'ki-arrow-up',
    getMovementTypeColor: (value) => value === 'IN' ? 'green' : 'red',
}));

describe('DrawerVisualizarProduto.vue', () => {
    let wrapper;

    const mockProduct = {
        id: '01HXQ5Y9XXXXXXXXXXXXXXXXXXX',
        name: 'Filtro de Óleo',
        category: 'engine',
        unit: 'unit',
        unit_price: 25.50,
        suggested_price: 35.00,
        is_active: true,
        description: 'Filtro de óleo para motor',
        sku: 'FO-001',
        barcode: '7891234567890',
        manufacturer: 'Fabricante XYZ',
        min_stock_level: 10,
        suppliers: [
            {
                id: '01HXQ5Y9YYYYYYYYYYYYYYYYYY',
                name: 'Fornecedor ABC',
                pivot: {
                    supplier_sku: 'ABC-FO-001',
                    cost_price: 20.00,
                    lead_time_days: 5,
                    min_order_quantity: 10,
                    is_preferred: true,
                    notes: 'Fornecedor preferencial',
                },
            },
        ],
        stock_movements: [
            {
                id: '01HXQ5Y9ZZZZZZZZZZZZZZZZZZZ',
                movement_type: 'IN',
                quantity: 50,
                reason: 'purchase',
                balance_after: 50,
                notes: 'Compra inicial',
                created_at: '2024-02-17T10:30:00.000000Z',
            },
        ],
    };

    const createWrapper = (props = {}) => {
        return mount(DrawerVisualizarProduto, {
            props: {
                open: true,
                product: mockProduct,
                ...props,
            },
            global: {
                stubs: {
                    teleport: true,
                    ProductSupplierCard: {
                        template: '<div class="supplier-card-mock">{{ supplier.name }}</div>',
                        props: ['supplier', 'formatCurrency', 'readonly'],
                    },
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
        expect(wrapper.text()).toContain('Visualizar Produto');
    });

    it('should render tabs', () => {
        const tabs = wrapper.findAll('.kt-tab-toggle');
        expect(tabs.length).toBe(3);
        expect(tabs[0].text()).toContain('Dados');
        expect(tabs[1].text()).toContain('Fornecedores');
        expect(tabs[2].text()).toContain('Movimentações');
    });

    it('should display product data correctly', () => {
        const dataTab = wrapper.find('#tab_view_product_data');
        expect(dataTab.text()).toContain('Filtro de Óleo');
        expect(dataTab.text()).toContain('Motor');
        expect(dataTab.text()).toContain('Unidade');
        expect(dataTab.text()).toContain('Ativo');
    });

    it('should display suppliers list', () => {
        const suppliersTab = wrapper.find('#tab_view_product_suppliers');
        expect(suppliersTab.exists()).toBe(true);
    });

    it('should display stock movements timeline', () => {
        const movementsTab = wrapper.find('#tab_view_product_movements');
        expect(movementsTab.exists()).toBe(true);
        expect(movementsTab.text()).toContain('Entrada');
        expect(movementsTab.text()).toContain('50');
        expect(movementsTab.text()).toContain('Compra');
    });

    it('should emit close event when close button is clicked', async () => {
        const closeButton = wrapper.find('.kt-btn-icon');
        await closeButton.trigger('click');
        expect(wrapper.emitted('close')).toBeTruthy();
    });

    it('should not render when open is false', () => {
        wrapper = createWrapper({ open: false });
        expect(wrapper.find('.fixed').exists()).toBe(false);
    });

    it('should show empty message when no suppliers', () => {
        const productWithoutSuppliers = { ...mockProduct, suppliers: [] };
        wrapper = createWrapper({ product: productWithoutSuppliers });
        const suppliersTab = wrapper.find('#tab_view_product_suppliers');
        expect(suppliersTab.text()).toContain('Nenhum fornecedor vinculado');
    });

    it('should show empty message when no movements', () => {
        const productWithoutMovements = { ...mockProduct, stock_movements: [] };
        wrapper = createWrapper({ product: productWithoutMovements });
        const movementsTab = wrapper.find('#tab_view_product_movements');
        expect(movementsTab.text()).toContain('Nenhuma movimentação registrada');
    });
});
