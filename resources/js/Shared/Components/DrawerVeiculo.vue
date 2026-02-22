<template>
  <teleport to="body">
    <Transition name="drawer">
      <div
        v-if="open"
        class="fixed inset-0 z-50 flex items-center justify-end"
        style="background: rgba(0, 0, 0, 0.5);"
      >
        <div class="w-full max-w-105 h-full bg-background border-l border-border shadow-xl flex flex-col">
          <!-- Header -->
          <div class="flex items-center justify-between px-5 py-4 border-b border-border">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
              {{ isEdit ? 'Editar Veículo' : 'Novo Veículo' }}
            </h2>
            <button class="kt-btn kt-btn-sm kt-btn-icon" @click="$emit('close')">
              <i class="ki-filled ki-cross"></i>
            </button>
          </div>

          <!-- Form -->
          <form
            class="flex flex-col gap-4 p-5 flex-1 overflow-y-auto"
            @submit.prevent="submitHandler"
          >
            <!-- Cliente (Autocomplete) -->
            <FormField name="client_id" label="Cliente" v-slot="{ field, errors }">
              <div class="relative">
                <input
                  v-model="clientSearch"
                  type="text"
                  class="kt-input w-full"
                  placeholder="Buscar cliente..."
                  @input="onClientSearchInput"
                  @focus="showClientDropdown = true"
                  @blur="onClientBlur"
                />
                <div
                  v-if="showClientDropdown && filteredClients.length > 0"
                  class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg max-h-60 overflow-auto"
                >
                  <div
                    v-for="client in filteredClients"
                    :key="client.id"
                    class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer"
                    @mousedown.prevent="selectClient(client)"
                  >
                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ client.name }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                      {{ client.document_number || client.email }}
                    </div>
                  </div>
                </div>
              </div>
              <FormError :errors="errors" />
            </FormField>

            <!-- Placa -->
            <FormField name="license_plate" label="Placa" v-slot="{ field, errors }">
              <div class="relative">
                <input
                  v-bind="field"
                  class="kt-input w-full"
                  placeholder="ABC-1234 ou ABC-1D23"
                  maxlength="8"
                  @input="applyMask('license_plate', licensePlateMask, $event)"
                />
                <span
                  v-if="checkingPlate"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-gray-500"
                >
                  Verificando...
                </span>
              </div>
              <FormError :errors="errors" />
            </FormField>

            <!-- Tipo de Veículo -->
            <FormField name="vehicle_type" label="Tipo de Veículo" v-slot="{ field, errors }">
              <div class="flex gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                  <input
                    type="radio"
                    :checked="field.value === 'car'"
                    @change="setFieldValue('vehicle_type', 'car')"
                    class="kt-radio"
                  />
                  <span class="text-gray-900 dark:text-gray-100">Carro</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                  <input
                    type="radio"
                    :checked="field.value === 'motorcycle'"
                    @change="setFieldValue('vehicle_type', 'motorcycle')"
                    class="kt-radio"
                  />
                  <span class="text-gray-900 dark:text-gray-100">Moto</span>
                </label>
              </div>
              <FormError :errors="errors" />
            </FormField>

            <!-- Marca -->
            <FormField name="brand" label="Marca" v-slot="{ field, errors }">
              <input v-bind="field" class="kt-input w-full" placeholder="Ex: Toyota, Honda" />
              <FormError :errors="errors" />
            </FormField>

            <!-- Modelo -->
            <FormField name="model" label="Modelo" v-slot="{ field, errors }">
              <input v-bind="field" class="kt-input w-full" placeholder="Ex: Corolla, Civic" />
              <FormError :errors="errors" />
            </FormField>

            <!-- Ano -->
            <FormField name="year" label="Ano" v-slot="{ field, errors }">
              <input
                v-bind="field"
                type="number"
                class="kt-input w-full"
                placeholder="Ex: 2024"
                :min="1886"
                :max="currentYear + 1"
              />
              <FormError :errors="errors" />
            </FormField>

            <!-- Cor -->
            <FormField name="color" label="Cor" v-slot="{ field, errors }">
              <input v-bind="field" class="kt-input w-full" placeholder="Ex: Preto, Prata" />
              <FormError :errors="errors" />
            </FormField>

            <!-- Chassi (VIN) -->
            <FormField name="vin" label="Chassi (VIN)" v-slot="{ field, errors }">
              <input v-bind="field" class="kt-input w-full" placeholder="Número do chassi" />
              <FormError :errors="errors" />
            </FormField>

            <!-- Combustível -->
            <FormField name="fuel" label="Combustível" v-slot="{ field, errors }">
              <select v-bind="field" class="kt-select w-full">
                <option value="">Selecione</option>
                <option value="alcohol">Álcool</option>
                <option value="gasoline">Gasolina</option>
                <option value="diesel">Diesel</option>
              </select>
              <FormError :errors="errors" />
            </FormField>

            <!-- Transmissão -->
            <FormField name="transmission" label="Transmissão" v-slot="{ field, errors }">
              <select v-bind="field" class="kt-select w-full">
                <option value="">Selecione</option>
                <option value="manual">Manual</option>
                <option value="automatic">Automática</option>
              </select>
              <FormError :errors="errors" />
            </FormField>

            <!-- Quilometragem -->
            <FormField name="mileage" label="Quilometragem" v-slot="{ field, errors }">
              <input
                v-bind="field"
                type="number"
                class="kt-input w-full"
                placeholder="Ex: 50000"
                min="0"
              />
              <FormError :errors="errors" />
            </FormField>

            <!-- Cilindrada (apenas para motos) -->
            <FormField
              v-if="values.vehicle_type === 'motorcycle'"
              name="cilinder_capacity"
              label="Cilindrada"
              v-slot="{ field, errors }"
            >
              <input v-bind="field" class="kt-input w-full" placeholder="Ex: 150cc, 300cc" />
              <FormError :errors="errors" />
            </FormField>

            <!-- Observações -->
            <FormField name="observations" label="Observações" v-slot="{ field, errors }">
              <textarea
                v-bind="field"
                class="kt-input w-full h-40"
                rows="6"
                placeholder="Observações sobre o veículo"
              />
              <FormError :errors="errors" />
            </FormField>

            <div class="flex justify-end gap-2 mt-4">
              <button type="button" class="kt-btn kt-btn-ghost" @click="$emit('close')">
                Cancelar
              </button>
              <button type="submit" class="kt-btn kt-btn-primary">
                Salvar
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    <!-- Modal de Confirmação de Transferência -->
    <ConfirmModal ref="confirmModal" modal-id="transfer_ownership_modal" />
  </teleport>
</template>

<script setup>
import { useForm } from 'vee-validate';
import { computed, watch, ref } from 'vue';
import * as yup from 'yup';
import FormField from './FormField.vue';
import FormError from './FormError.vue';
import ConfirmModal from './ConfirmModal.vue';
import { useMasks } from '@/Composables/useMasks';
import { fetchClients } from '@/services/clientService';
import { checkVehiclePlate, transferVehicleOwnership } from '@/services/vehicleService';
import { useToast } from '@/Shared/composables/useToast';

const { licensePlateMask, unmaskLicensePlate } = useMasks();
const toast = useToast();

const props = defineProps({
  open: Boolean,
  isEdit: Boolean,
  vehicle: Object,
});

const emit = defineEmits(['close', 'submit', 'refresh']);

const currentYear = new Date().getFullYear();

// Cliente autocomplete
const clientSearch = ref('');
const showClientDropdown = ref(false);
const filteredClients = ref([]);
const selectedClient = ref(null);

// Verificação de placa
const checkingPlate = ref(false);
const plateExists = ref(false);
const plateOwnerInfo = ref(null);
const transferConfirmed = ref(false); // Armazena se usuário confirmou transferência
let plateCheckTimeout = null;

// Modal de confirmação
const confirmModal = ref(null);

const applyMask = (fieldName, maskFn, event) => {
  const rawValue = event.target.value;
  const maskedValue = maskFn(rawValue);
  setFieldValue(fieldName, maskedValue);
};

/* ---------------------------
 * Validation schema
 * --------------------------- */
const schema = yup.object({
  client_id: yup.string().required('Cliente é obrigatório'),
  license_plate: yup
    .string()
    .required('Placa é obrigatória')
    .matches(
      /^[A-Z]{3}-?\d{1}[A-Z0-9]{1}\d{2}$/,
      'Placa inválida. Use formato ABC-1234 ou ABC-1D23'
    ),
  brand: yup.string().required('Marca é obrigatória').min(3, 'Marca deve ter no mínimo 3 caracteres'),
  model: yup.string().required('Modelo é obrigatório').min(1, 'Modelo é obrigatório'),
  year: yup
    .number()
    .required('Ano é obrigatório')
    .min(1886, 'Ano inválido')
    .max(currentYear + 1, `Ano não pode ser maior que ${currentYear + 1}`),
  vehicle_type: yup.string().oneOf(['car', 'motorcycle'], 'Tipo de veículo inválido').default('car'),
  color: yup.string().nullable(),
  vin: yup.string().nullable(),
  fuel: yup.string().oneOf(['alcohol', 'gasoline', 'diesel', ''], 'Combustível inválido').nullable(),
  transmission: yup.string().oneOf(['manual', 'automatic', ''], 'Transmissão inválida').nullable(),
  mileage: yup.number().nullable().min(0, 'Quilometragem não pode ser negativa'),
  cilinder_capacity: yup.string().nullable(),
  observations: yup.string().nullable(),
});

/* ---------------------------
 * Form controller
 * --------------------------- */
const {
  handleSubmit,
  setValues,
  resetForm,
  setFieldValue,
  values,
} = useForm({
  validationSchema: schema,
  initialValues: computed(() => props.vehicle || {
    client_id: '',
    license_plate: '',
    brand: '',
    model: '',
    year: currentYear,
    vehicle_type: 'car',
    color: '',
    vin: '',
    fuel: '',
    transmission: '',
    mileage: null,
    cilinder_capacity: '',
    observations: '',
  }),
});

/* ---------------------------
 * Cliente autocomplete logic
 * --------------------------- */
async function onClientSearchInput() {
  if (clientSearch.value.length < 2) {
    filteredClients.value = [];
    return;
  }

  try {
    const result = await fetchClients({
      search: clientSearch.value,
      perPage: 10,
      page: 1,
    });
    filteredClients.value = result.items;
    showClientDropdown.value = true;
  } catch (error) {
    console.error('Erro ao buscar clientes:', error);
    filteredClients.value = [];
  }
}

function selectClient(client) {
  selectedClient.value = client;
  clientSearch.value = client.name;
  setFieldValue('client_id', client.id);
  showClientDropdown.value = false;
}

function onClientBlur() {
  setTimeout(() => {
    showClientDropdown.value = false;
  }, 200);
}

/* ---------------------------
 * Verificação de placa
 * --------------------------- */
async function checkPlate(licensePlate) {
  if (!licensePlate || licensePlate.length < 7) {
    plateExists.value = false;
    plateOwnerInfo.value = null;
    transferConfirmed.value = false;
    return;
  }

  // Remove máscara para enviar ao backend
  const cleanPlate = unmaskLicensePlate(licensePlate);
  
  checkingPlate.value = true;
  const result = await checkVehiclePlate(cleanPlate);
  checkingPlate.value = false;

  if (result.success && result.data.exists) {
    plateExists.value = true;
    plateOwnerInfo.value = result.data;
    
    // Verifica se o proprietário é diferente do cliente selecionado
    if (plateOwnerInfo.value.current_owner_id && 
        plateOwnerInfo.value.current_owner_id !== values.client_id &&
        selectedClient.value) {
      
      // Abre o modal imediatamente
      const confirmed = await confirmModal.value.open({
        title: 'Transferência de Propriedade',
        message: `Esta placa já está cadastrada para o cliente "${plateOwnerInfo.value.current_owner_name}". 
                  Deseja transferir a propriedade do veículo para "${selectedClient.value?.name}"?`
      });
      
      if (confirmed) {
        // Usuário confirmou, executa a transferência imediatamente
        const newClientId = values.client_id || selectedClient.value?.id;
        
        if (!newClientId) {
          toast.error('Erro: Cliente não foi selecionado corretamente.');
          return;
        }
        
        const transferResult = await transferVehicleOwnership(
          plateOwnerInfo.value.vehicle_id,
          newClientId
        );
        
        if (transferResult.success) {
          toast.success('Propriedade do veículo transferida com sucesso!');
          // Emite evento de refresh para atualizar o grid
          emit('refresh');
          // Fecha o drawer
          emit('close');
        } else {
          toast.error('Erro ao transferir propriedade do veículo.');
        }
      } else {
        // Usuário cancelou
        transferConfirmed.value = false;
        toast.info('Transferência cancelada. A placa foi limpa.');
        // Limpa a placa e reseta os estados
        plateExists.value = false;
        plateOwnerInfo.value = null;
        setFieldValue('license_plate', '');
      }
    }
  } else {
    plateExists.value = false;
    plateOwnerInfo.value = null;
    transferConfirmed.value = false;
  }
}

// Watch para verificar placa quando digitada (com debounce)
// APENAS no modo de criação
watch(
  () => values.license_plate,
  (newPlate, oldPlate) => {
    // NÃO verifica placa no modo de edição
    if (props.isEdit) {
      return;
    }
    
    // Limpa timeout anterior
    if (plateCheckTimeout) {
      clearTimeout(plateCheckTimeout);
    }

    // Reseta estados quando a placa é modificada
    if (newPlate !== oldPlate) {
      plateExists.value = false;
      plateOwnerInfo.value = null;
      transferConfirmed.value = false;
    }

    // Verifica placa após 800ms de inatividade
    plateCheckTimeout = setTimeout(() => {
      checkPlate(newPlate);
    }, 800);
  }
);

/* ---------------------------
 * Sync edit / create
 * --------------------------- */
watch(
  () => props.vehicle,
  (val) => {
    if (val) {
      setValues(val);
      if (val.current_owner?.client) {
        selectedClient.value = val.current_owner.client;
        clientSearch.value = val.current_owner.client.name;
        // Define o client_id no formulário
        setFieldValue('client_id', val.current_owner.client.id);
      }
    } else {
      resetForm();
      clientSearch.value = '';
      selectedClient.value = null;
    }
  }
);

/* ---------------------------
 * Submit (clean & safe)
 * --------------------------- */
const submitHandler = handleSubmit(async (values) => {
  console.log('=== DEBUG SUBMIT ===');
  console.log('Submetendo formulário...');
  
  const submitData = {
    ...values,
    license_plate: unmaskLicensePlate(values.license_plate),
  };
  emit('submit', submitData);
});
</script>

<style scoped>
.drawer-enter-active,
.drawer-leave-active {
  transition: opacity 0.3s ease;
}

.drawer-enter-active > div,
.drawer-leave-active > div {
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.drawer-enter-from,
.drawer-leave-to {
  opacity: 0;
}

.drawer-enter-from > div,
.drawer-leave-to > div {
  transform: translateX(100%);
}

.drawer-enter-to,
.drawer-leave-from {
  opacity: 1;
}

.drawer-enter-to > div,
.drawer-leave-from > div {
  transform: translateX(0);
}
</style>
