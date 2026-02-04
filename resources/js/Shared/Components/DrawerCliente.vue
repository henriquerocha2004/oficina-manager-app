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
              {{ isEdit ? 'Editar Cliente' : 'Novo Cliente' }}
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
            <FormField name="name" label="Nome" v-slot="{ field, errors }">
              <input v-bind="field" class="kt-input w-full" placeholder="Nome completo" />
              <FormError :errors="errors" />
            </FormField>

            <FormField name="email" label="Email" v-slot="{ field, errors }">
              <input v-bind="field" class="kt-input w-full" placeholder="Email" />
              <FormError :errors="errors" />
            </FormField>

            <FormField name="document_number" label="CPF/CNPJ" v-slot="{ field, errors }">
              <input 
                v-bind="field"
                class="kt-input w-full"
                placeholder="CPF ou CNPJ"
                :maxlength="docLimit"
                @input="applyMask('document_number', maskDocument, $event)"
              />
              <FormError :errors="errors" />
            </FormField>

            <FormField name="phone" label="Telefone" v-slot="{ field, errors }">
              <input 
                v-bind="field"
                class="kt-input w-full"
                placeholder="Telefone"
                :maxlength="phoneLenghtLimit"
                @input="applyMask('phone', maskPhone, $event)"
              />
              <FormError :errors="errors" />
            </FormField>

            <div class="flex flex-col gap-2">
              <FormField name="zip_code" label="CEP" v-slot="{ field, errors }">
                <input
                  v-bind="field"
                  class="kt-input w-full"
                  placeholder="CEP"
                  @blur="onCepBlur(field.value)"
                  :maxlength="9"
                  @input="applyMask('zip_code', maskCEP, $event)"
                />
                <FormError :errors="errors" />
              </FormField>

              <FormField name="street" label="Endereço" v-slot="{ field, errors }">
                <input v-bind="field" class="kt-input w-full" placeholder="Rua, número" />
                <FormError :errors="errors" />
              </FormField>

              <FormField name="city" label="Cidade" v-slot="{ field, errors }">
                <input v-bind="field" class="kt-input w-full" placeholder="Cidade" />
                <FormError :errors="errors" />
              </FormField>

              <FormField name="state" label="Estado" v-slot="{ field, errors }">
                <select v-bind="field" class="kt-input w-full">
                  <option value="">Selecione</option>
                  <option v-for="s in brazilianStates" :key="s.code" :value="s.code">{{ s.name }}</option>
                </select>
                <FormError :errors="errors" />
              </FormField>
            </div>

            <FormField name="observations" label="Observações" v-slot="{ field, errors }">
              <textarea
                v-bind="field"
                class="kt-input w-full h-40"
                rows="6"
                placeholder="Observações"
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
  </teleport>
</template>

<script setup>
import { useForm, Form } from 'vee-validate';
import { computed, watch } from 'vue';
import * as yup from 'yup';
import FormField from './FormField.vue';
import FormError from './FormError.vue';
import { fetchAddressByCep } from '@/Composables/useCep';
import { useMasks } from '@/Composables/useMasks';
import brazilianStates from '@/Data/brasilianStates';

const { maskCEP, maskDocument, unmask, getDocMaxLength, getPhoneMaxLength, maskPhone } = useMasks();
const props = defineProps({
  open: Boolean,
  isEdit: Boolean,
  client: Object,
});

const emit = defineEmits(['close', 'submit']);
const docLimit = computed(() => {
  const currentVal = values.document_number || '';
  return getDocMaxLength(currentVal);
});

const phoneLenghtLimit = computed(() => {
  const currentVal = values.phone || '';
  return getPhoneMaxLength(currentVal);
})

const applyMask = (fieldName, maskFn, event) => {
  const rawValue = event.target.value;
  const maskedValue = maskFn(rawValue);
  setFieldValue(fieldName, maskedValue);
};

/* ---------------------------
 * Validation schema
 * --------------------------- */
const schema = yup.object({
  name: yup.string().required('Nome é obrigatório'),
  email: yup.string().email('Email inválido').required('Email é obrigatório'),
  document_number: yup.string().required('Documento é obrigatório'),
  phone: yup.string().required('Telefone é obrigatório'),
  zip_code: yup.string().nullable(),
  street: yup.string().nullable(),
  city: yup.string().nullable(),
  state: yup.string().nullable(),
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
  initialValues: computed(() => props.client || {
    name: '',
    email: '',
    document_number: '',
    phone: '',
    zip_code: '',
    street: '',
    city: '',
    state: '',
    observations: '',
  }),
});

/* ---------------------------
 * Sync edit / create
 * --------------------------- */
watch(
  () => props.client,
  (val) => {
    if (val) setValues(val);
    else resetForm();
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
  console.log('Endereço encontrado:', addr);
  if (addr.zip_code) setFieldValue('zip_code', values.zip_code);
  if (addr.street) setFieldValue('street', addr.street);
  if (addr.city) setFieldValue('city', addr.city);
  if (addr.state) setFieldValue('state', addr.state);
}

/* ---------------------------
 * Submit (clean & safe)
 * --------------------------- */
const submitHandler = handleSubmit((values) => {
  console.log('Submitting client:', values);
  emit('submit', values);
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
