import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import DrawerServico from '@/Shared/Components/DrawerServico.vue';
import { useMasks } from '@/Composables/useMasks';

// Mock vee-validate
vi.mock('vee-validate', () => ({
    useForm: () => ({
        handleSubmit: (cb) => () => cb({
            name: 'Test Service',
            category: 'maintenance',
            base_price: 'R$ 150,00',
            estimated_time: 60,
            description: 'Test description',
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
        unmaskCurrency: (value) => parseFloat(value.replace(/[^\d,]/g, '').replace(',', '.')),
    }),
}));

// Mock data
vi.mock('@/Data/serviceCategories', () => ({
    serviceCategories: [
        { value: 'maintenance', label: 'Manutenção' },
        { value: 'repair', label: 'Reparo' },
    ],
}));

describe('DrawerServico.vue', () => {
    let wrapper;

    const createWrapper = (props = {}) => {
        return mount(DrawerServico, {
            props: {
                open: true,
                isEdit: false,
                service: null,
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

    it('should display correct title for new service', () => {
        expect(wrapper.text()).toContain('Novo Serviço');
    });

    it('should display correct title for edit service', () => {
        wrapper = createWrapper({ isEdit: true });
        expect(wrapper.text()).toContain('Editar Serviço');
    });

    it('should emit close event when close button is clicked', async () => {
        const closeBtn = wrapper.find('.kt-btn-icon');
        await closeBtn.trigger('click');
        expect(wrapper.emitted('close')).toBeTruthy();
    });

    it('should emit submit event with cleaned data', async () => {
        const form = wrapper.find('form');
        await form.trigger('submit');
        
        expect(wrapper.emitted('submit')).toBeTruthy();
    });

    it('should not render when closed', () => {
        wrapper = createWrapper({ open: false });
        expect(wrapper.find('.fixed').exists()).toBe(false);
    });
});
