import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import DrawerVeiculo from '@/Shared/Components/DrawerVeiculo.vue';

// Track mutable values so tests can change vehicle_type
let mockValues = { vehicle_type: 'car', license_plate: 'ABC-1234' };

vi.mock('vee-validate', () => ({
    useForm: () => ({
        handleSubmit: (cb) => async () => cb({ ...mockValues }),
        setValues: vi.fn(),
        resetForm: vi.fn(),
        setFieldValue: vi.fn((field, val) => { mockValues[field] = val; }),
        values: mockValues,
    }),
}));

vi.mock('@/Composables/useMasks', () => ({
    useMasks: () => ({
        licensePlateMask: vi.fn((v) => v),
        unmaskLicensePlate: vi.fn((v) => v?.replace(/-/g, '') ?? ''),
    }),
}));

vi.mock('@/services/clientService', () => ({
    fetchClients: vi.fn().mockResolvedValue({ items: [] }),
}));

vi.mock('@/services/vehicleService', () => ({
    checkVehiclePlate: vi.fn().mockResolvedValue({ success: true, data: { exists: false } }),
    transferVehicleOwnership: vi.fn(),
}));

vi.mock('@/Shared/composables/useToast', () => ({
    useToast: () => ({
        success: vi.fn(),
        error: vi.fn(),
        info: vi.fn(),
    }),
}));

describe('DrawerVeiculo.vue', () => {
    beforeEach(() => {
        mockValues = { vehicle_type: 'car', license_plate: 'ABC-1234' };
    });

    const createWrapper = (props = {}) =>
        mount(DrawerVeiculo, {
            props: { open: true, isEdit: false, vehicle: null, ...props },
            global: {
                stubs: {
                    teleport: true,
                    Transition: { template: '<slot />' },
                    ConfirmModal: {
                        template: '<div />',
                        methods: { open: vi.fn().mockResolvedValue(false) },
                    },
                    FormField: {
                        template: '<div><label>{{ label }}</label><slot :field="{ value: \'\' }" :errors="[]" /></div>',
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

    it('shows "Novo Veículo" title in create mode', () => {
        const wrapper = createWrapper({ isEdit: false });
        expect(wrapper.text()).toContain('Novo Veículo');
    });

    it('shows "Editar Veículo" title in edit mode', () => {
        const wrapper = createWrapper({ isEdit: true });
        expect(wrapper.text()).toContain('Editar Veículo');
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

    it('emits "submit" with unmasked license plate on form submit', async () => {
        const wrapper = createWrapper();
        await wrapper.find('form').trigger('submit');
        expect(wrapper.emitted('submit')).toBeTruthy();
        const payload = wrapper.emitted('submit')[0][0];
        // unmaskLicensePlate mock removes hyphens
        expect(payload.license_plate).toBe('ABC1234');
    });

    it('does not show cilindrada field for car type', () => {
        mockValues.vehicle_type = 'car';
        const wrapper = createWrapper();
        expect(wrapper.text()).not.toContain('Cilindrada');
    });

    it('shows cilindrada field for motorcycle type', () => {
        mockValues.vehicle_type = 'motorcycle';
        const wrapper = createWrapper();
        expect(wrapper.text()).toContain('Cilindrada');
    });
});
