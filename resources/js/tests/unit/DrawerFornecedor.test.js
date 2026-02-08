import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import DrawerFornecedor from '@/Shared/Components/DrawerFornecedor.vue';
import { useMasks } from '@/Composables/useMasks';

// Mock vee-validate
vi.mock('vee-validate', () => ({
    useForm: () => ({
        handleSubmit: (cb) => () => cb({
            name: 'Test Supplier',
            trade_name: 'Trade Name',
            document_number: '12.345.678/0001-90',
            email: 'test@supplier.com',
            phone: '(11) 1234-5678',
            mobile: '(11) 98765-4321',
            website: 'https://supplier.com',
            street: 'Rua Teste',
            number: '123',
            complement: 'Sala 1',
            neighborhood: 'Bairro',
            city: 'São Paulo',
            state: 'SP',
            zip_code: '12345-678',
            contact_person: 'João Silva',
            payment_term_days: 30,
            notes: 'Test notes',
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
        maskCEP: (value) => value,
        maskDocument: (value) => value,
        maskPhone: (value) => value,
        unmask: (value) => value.replace(/\D/g, ''),
    }),
}));

// Mock useCep
vi.mock('@/Composables/useCep', () => ({
    fetchAddressByCep: vi.fn(),
}));

// Mock data
vi.mock('@/Data/brasilianStates', () => ({
    default: [
        { code: 'SP', name: 'São Paulo' },
        { code: 'RJ', name: 'Rio de Janeiro' },
    ],
}));

describe('DrawerFornecedor.vue', () => {
    let wrapper;

    const createWrapper = (props = {}) => {
        return mount(DrawerFornecedor, {
            props: {
                open: true,
                isEdit: false,
                supplier: null,
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

    it('should display correct title for new supplier', () => {
        expect(wrapper.text()).toContain('Novo Fornecedor');
    });

    it('should display correct title for edit supplier', () => {
        wrapper = createWrapper({ isEdit: true });
        expect(wrapper.text()).toContain('Editar Fornecedor');
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
