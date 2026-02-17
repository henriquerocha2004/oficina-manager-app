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
              {{ isEdit ? 'Editar Fornecedor' : 'Novo Fornecedor' }}
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
            <!-- Razão Social -->
            <FormField name="name" label="Razão Social" v-slot="{ field, errors }">
              <input v-bind="field" class="kt-input w-full" placeholder="Razão Social" />
              <FormError :errors="errors" />
            </FormField>

            <!-- Nome Fantasia -->
            <FormField name="trade_name" label="Nome Fantasia" v-slot="{ field, errors }">
              <input v-bind="field" class="kt-input w-full" placeholder="Nome Fantasia (opcional)" />
              <FormError :errors="errors" />
            </FormField>

            <!-- CNPJ -->
            <FormField name="document_number" label="CNPJ" v-slot="{ field, errors }">
              <input 
                v-bind="field"
                class="kt-input w-full"
                placeholder="00.000.000/0000-00"
                :maxlength="18"
                @input="applyMask('document_number', maskDocument, $event)"
              />
              <FormError :errors="errors" />
            </FormField>

            <!-- Email -->
            <FormField name="email" label="Email" v-slot="{ field, errors }">
              <input v-bind="field" class="kt-input w-full" placeholder="email@exemplo.com" />
              <FormError :errors="errors" />
            </FormField>

            <!-- Telefone -->
            <FormField name="phone" label="Telefone" v-slot="{ field, errors }">
              <input 
                v-bind="field"
                class="kt-input w-full"
                placeholder="(00) 0000-0000"
                :maxlength="phoneLenghtLimit"
                @input="applyMask('phone', maskPhone, $event)"
              />
              <FormError :errors="errors" />
            </FormField>

            <!-- Celular -->
            <FormField name="mobile" label="Celular" v-slot="{ field, errors }">
              <input 
                v-bind="field"
                class="kt-input w-full"
                placeholder="(00) 00000-0000"
                :maxlength="mobileLenghtLimit"
                @input="applyMask('mobile', maskPhone, $event)"
              />
              <FormError :errors="errors" />
            </FormField>

            <!-- Website -->
            <FormField name="website" label="Website" v-slot="{ field, errors }">
              <input v-bind="field" class="kt-input w-full" placeholder="https://exemplo.com.br" />
              <FormError :errors="errors" />
            </FormField>

            <!-- Pessoa de Contato -->
            <FormField name="contact_person" label="Pessoa de Contato" v-slot="{ field, errors }">
              <input v-bind="field" class="kt-input w-full" placeholder="Nome do contato" />
              <FormError :errors="errors" />
            </FormField>

            <!-- Prazo de Pagamento -->
            <FormField name="payment_term_days" label="Prazo de Pagamento (dias)" v-slot="{ field, errors }">
              <input
                v-bind="field"
                type="number"
                class="kt-input w-full"
                placeholder="Ex: 30"
                min="0"
                max="365"
              />
              <FormError :errors="errors" />
            </FormField>

            <div class="flex flex-col gap-2">
              <!-- CEP -->
              <FormField name="zip_code" label="CEP" v-slot="{ field, errors }">
                <input
                  v-bind="field"
                  class="kt-input w-full"
                  placeholder="00000-000"
                  @blur="onCepBlur(field.value)"
                  :maxlength="9"
                  @input="applyMask('zip_code', maskCEP, $event)"
                />
                <FormError :errors="errors" />
              </FormField>

              <!-- Endereço -->
              <FormField name="street" label="Endereço" v-slot="{ field, errors }">
                <input v-bind="field" class="kt-input w-full" placeholder="Rua, Avenida..." />
                <FormError :errors="errors" />
              </FormField>

              <!-- Número -->
              <FormField name="number" label="Número" v-slot="{ field, errors }">
                <input v-bind="field" class="kt-input w-full" placeholder="Número" />
                <FormError :errors="errors" />
              </FormField>

              <!-- Complemento -->
              <FormField name="complement" label="Complemento" v-slot="{ field, errors }">
                <input v-bind="field" class="kt-input w-full" placeholder="Sala, Bloco..." />
                <FormError :errors="errors" />
              </FormField>

              <!-- Bairro -->
              <FormField name="neighborhood" label="Bairro" v-slot="{ field, errors }">
                <input v-bind="field" class="kt-input w-full" placeholder="Bairro" />
                <FormError :errors="errors" />
              </FormField>

              <!-- Cidade -->
              <FormField name="city" label="Cidade" v-slot="{ field, errors }">
                <input v-bind="field" class="kt-input w-full" placeholder="Cidade" />
                <FormError :errors="errors" />
              </FormField>

              <!-- Estado -->
              <FormField name="state" label="Estado" v-slot="{ field, errors }">
                <select v-bind="field" class="kt-select w-full">
                  <option value="">Selecione</option>
                  <option v-for="s in brazilianStates" :key="s.code" :value="s.code">{{ s.name }}</option>
                </select>
                <FormError :errors="errors" />
              </FormField>
            </div>

            <!-- Observações -->
            <FormField name="notes" label="Observações" v-slot="{ field, errors }">
              <textarea
                v-bind="field"
                class="kt-input w-full h-40"
                rows="6"
                placeholder="Observações sobre o fornecedor"
              />
              <FormError :errors="errors" />
            </FormField>

            <!-- Status Ativo -->
            <FormField name="is_active" label="Status" v-slot="{ field, errors }">
              <div class="flex items-center gap-2">
                <input
                  type="checkbox"
                  :checked="field.value"
                  @change="setFieldValue('is_active', $event.target.checked)"
                  class="kt-checkbox"
                />
                <span class="text-gray-900 dark:text-gray-100">Fornecedor ativo</span>
              </div>
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
  </teleport>
</template>

<script setup>
import { useForm } from 'vee-validate';
import { computed, watch } from 'vue';
import * as yup from 'yup';
import FormField from './FormField.vue';
import FormError from './FormError.vue';
import { fetchAddressByCep } from '@/Composables/useCep';
import { useMasks } from '@/Composables/useMasks';
import brazilianStates from '@/Data/brasilianStates';

const { maskCEP, maskDocument, maskPhone, unmask } = useMasks();

const props = defineProps({
  open: Boolean,
  isEdit: Boolean,
  supplier: Object,
});

const emit = defineEmits(['close', 'submit']);

const phoneLenghtLimit = computed(() => {
  const currentVal = values.phone || '';
  const clean = currentVal.replace(/\D/g, '');
  return clean.length >= 11 ? 15 : 16;
});

const mobileLenghtLimit = computed(() => {
  const currentVal = values.mobile || '';
  const clean = currentVal.replace(/\D/g, '');
  return clean.length >= 11 ? 15 : 16;
});

const applyMask = (fieldName, maskFn, event) => {
  const rawValue = event.target.value;
  const maskedValue = maskFn(rawValue);
  setFieldValue(fieldName, maskedValue);
};

/* ---------------------------
 * Validation schema
 * --------------------------- */
const schema = yup.object({
  name: yup.string().required('Razão Social é obrigatória').min(3, 'Razão Social deve ter no mínimo 3 caracteres').max(255, 'Razão Social deve ter no máximo 255 caracteres'),
  trade_name: yup.string().nullable().max(255, 'Nome Fantasia deve ter no máximo 255 caracteres'),
  document_number: yup.string().required('CNPJ é obrigatório'),
  email: yup.string().email('Email inválido').nullable().max(255, 'Email deve ter no máximo 255 caracteres'),
  phone: yup.string().nullable().min(14, 'Telefone deve ter no mínimo 10 dígitos').max(20, 'Telefone deve ter no máximo 20 caracteres'),
  mobile: yup.string().nullable().min(14, 'Celular deve ter no mínimo 10 dígitos').max(20, 'Celular deve ter no máximo 20 caracteres'),
  website: yup.string().url('Website deve ser uma URL válida').nullable().max(255, 'Website deve ter no máximo 255 caracteres'),
  street: yup.string().nullable().max(255, 'Endereço deve ter no máximo 255 caracteres'),
  number: yup.string().nullable().max(20, 'Número deve ter no máximo 20 caracteres'),
  complement: yup.string().nullable().max(100, 'Complemento deve ter no máximo 100 caracteres'),
  neighborhood: yup.string().nullable().max(100, 'Bairro deve ter no máximo 100 caracteres'),
  city: yup.string().nullable().max(100, 'Cidade deve ter no máximo 100 caracteres'),
  state: yup.string().nullable().length(2, 'Estado deve ter 2 caracteres'),
  zip_code: yup.string().nullable(),
  contact_person: yup.string().nullable().max(255, 'Pessoa de Contato deve ter no máximo 255 caracteres'),
  payment_term_days: yup.number().nullable().min(0, 'Prazo não pode ser negativo').max(365, 'Prazo máximo é 365 dias'),
  notes: yup.string().nullable().max(2000, 'Observações deve ter no máximo 2000 caracteres'),
  is_active: yup.boolean().nullable(),
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
  initialValues: computed(() => props.supplier || {
    name: '',
    trade_name: '',
    document_number: '',
    email: '',
    phone: '',
    mobile: '',
    website: '',
    street: '',
    number: '',
    complement: '',
    neighborhood: '',
    city: '',
    state: '',
    zip_code: '',
    contact_person: '',
    payment_term_days: null,
    notes: '',
    is_active: true,
  }),
});

/* ---------------------------
 * Sync edit / create
 * --------------------------- */
watch(
  () => props.supplier,
  (val) => {
    if (val) {
      // Apply masks to data coming from backend
      const maskedData = {
        ...val,
        document_number: val.document_number ? maskDocument(val.document_number) : '',
        phone: val.phone ? maskPhone(val.phone) : '',
        mobile: val.mobile ? maskPhone(val.mobile) : '',
        zip_code: val.zip_code ? maskCEP(val.zip_code) : '',
      };
      setValues(maskedData);
    } else {
      resetForm();
    }
  }
);

watch(() => values.zip_code, async (val) => {
  if (!val) return;
  const cleanCep = val.replace(/\D/g, '');
  if (cleanCep.length !== 8) return;

  await onCepBlur(cleanCep);
});

/* ---------------------------
 * CEP lookup
 * --------------------------- */
async function onCepBlur(value) {
  if (!value) return;

  const addr = await fetchAddressByCep(value);
  if (!addr) return;
  if (addr.zip_code) setFieldValue('zip_code', values.zip_code);
  if (addr.street) setFieldValue('street', addr.street);
  if (addr.neighborhood) setFieldValue('neighborhood', addr.neighborhood);
  if (addr.city) setFieldValue('city', addr.city);
  if (addr.state) setFieldValue('state', addr.state);
}

/* ---------------------------
 * Submit (clean & safe)
 * --------------------------- */
const submitHandler = handleSubmit((values) => {
  const unmaskedData = {
    ...values,
    document_number: unmask(values.document_number),
    phone: unmask(values.phone),
    mobile: unmask(values.mobile),
    zip_code: unmask(values.zip_code),
  };
  emit('submit', unmaskedData);
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
