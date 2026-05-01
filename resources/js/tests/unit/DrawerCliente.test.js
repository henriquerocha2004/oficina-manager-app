import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import DrawerCliente from '@/Shared/Components/DrawerCliente.vue';

// Mock vee-validate — submitHandler calls cb with form values
vi.mock('vee-validate', () => ({
    useForm: () => ({
        handleSubmit: (cb) => () => cb({
            name: 'João Silva',
            email: 'joao@test.com',
            document_number: '123.456.789-01',
            phone: '(11) 98765-4321',
            zip_code: '01310-100',
            street: 'Rua das Flores, 42',
            city: 'São Paulo',
            state: 'SP',
            observations: '',
        }),
        setValues: vi.fn(),
        resetForm: vi.fn(),
        setFieldValue: vi.fn(),
        values: {
            document_number: '',
            phone: '',
        },
    }),
}));

vi.mock('@/Composables/useMasks', () => ({
    useMasks: () => ({
        maskCEP: vi.fn((v) => v),
        maskDocument: vi.fn((v) => v),
        maskPhone: vi.fn((v) => v),
        getDocMaxLength: vi.fn(() => 14),
        getPhoneMaxLength: vi.fn(() => 15),
    }),
}));

vi.mock('@/Composables/useCep', () => ({
    fetchAddressByCep: vi.fn().mockResolvedValue(null),
}));

vi.mock('@/Data/brasilianStates', () => ({
    default: [
        { code: 'SP', name: 'São Paulo' },
        { code: 'RJ', name: 'Rio de Janeiro' },
    ],
}));

describe('DrawerCliente.vue', () => {
    const createWrapper = (props = {}) =>
        mount(DrawerCliente, {
            props: { open: true, isEdit: false, client: null, ...props },
            global: {
                stubs: {
                    teleport: true,
                    Transition: { template: '<slot />' },
                    FormField: {
                        template: '<div><slot :field="{ value: \'\' }" :errors="[]" /></div>',
                        props: ['name', 'label'],
                    },
                    FormError: { template: '<div />' },
                },
            },
        });

    it('renders the drawer when open', () => {
        const wrapper = createWrapper();
        expect(wrapper.find('.fixed').exists()).toBe(true);
    });

    it('does not render when closed', () => {
        const wrapper = createWrapper({ open: false });
        expect(wrapper.find('.fixed').exists()).toBe(false);
    });

    it('shows "Novo Cliente" title in create mode', () => {
        const wrapper = createWrapper({ isEdit: false });
        expect(wrapper.text()).toContain('Novo Cliente');
    });

    it('shows "Editar Cliente" title in edit mode', () => {
        const wrapper = createWrapper({ isEdit: true });
        expect(wrapper.text()).toContain('Editar Cliente');
    });

    it('emits "close" when the X button is clicked', async () => {
        const wrapper = createWrapper();
        await wrapper.find('.kt-btn-icon').trigger('click');
        expect(wrapper.emitted('close')).toBeTruthy();
    });

    it('emits "close" when Cancelar button is clicked', async () => {
        const wrapper = createWrapper();
        const cancelBtn = wrapper.findAll('button').find((b) => b.text() === 'Cancelar');
        await cancelBtn.trigger('click');
        expect(wrapper.emitted('close')).toBeTruthy();
    });

    it('emits "submit" with form values on form submit', async () => {
        const wrapper = createWrapper();
        await wrapper.find('form').trigger('submit');
        expect(wrapper.emitted('submit')).toBeTruthy();
        const payload = wrapper.emitted('submit')[0][0];
        expect(payload.name).toBe('João Silva');
        expect(payload.email).toBe('joao@test.com');
    });
});
