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
              <input v-bind="field" class="kt-input w-full" placeholder="Nome da empresa" />
              <FormError :errors="errors" />
            </FormField>

            <FormField name="email" label="Email" v-slot="{ field, errors }">
              <input v-bind="field" class="kt-input w-full" placeholder="Email" type="email" />
              <FormError :errors="errors" />
            </FormField>

            <FormField name="document" label="CNPJ / CPF" v-slot="{ field, errors }">
              <input
                v-bind="field"
                class="kt-input w-full"
                placeholder="CNPJ ou CPF"
                :maxlength="docLimit"
                @input="applyMask('document', maskDocument, $event)"
              />
              <FormError :errors="errors" />
            </FormField>

            <FormField name="phone" label="Telefone" v-slot="{ field, errors }">
              <input
                v-bind="field"
                class="kt-input w-full"
                placeholder="Telefone"
                :maxlength="phoneLimit"
                @input="applyMask('phone', maskPhone, $event)"
              />
              <FormError :errors="errors" />
            </FormField>

            <FormField name="zip_code" label="CEP" v-slot="{ field, errors }">
              <input
                v-bind="field"
                class="kt-input w-full"
                placeholder="CEP"
                :maxlength="9"
                @input="applyMask('zip_code', maskCEP, $event)"
                @blur="onCepBlur(field.value)"
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
              <select v-bind="field" class="kt-select w-full">
                <option value="">Selecione</option>
                <option v-for="s in brazilianStates" :key="s.value" :value="s.value">{{ s.label }}</option>
              </select>
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
import { computed, watch } from 'vue';
import { useForm } from 'vee-validate';
import * as yup from 'yup';
import FormField from './FormField.vue';
import FormError from './FormError.vue';
import { useMasks } from '@/Composables/useMasks';
import { fetchAddressByCep } from '@/Composables/useCep';
import { brazilianStates } from '@/Data/brazilianStates.js';

const { maskCEP, maskDocument, maskPhone, getDocMaxLength, getPhoneMaxLength } = useMasks();

const props = defineProps({
  open: Boolean,
  isEdit: Boolean,
  client: Object,
});

const emit = defineEmits(['close', 'submit']);

const docLimit = computed(() => getDocMaxLength(values.document || ''));
const phoneLimit = computed(() => getPhoneMaxLength(values.phone || ''));

const applyMask = (fieldName, maskFn, event) => {
  setFieldValue(fieldName, maskFn(event.target.value));
};

const schema = yup.object({
  name: yup.string().required('Nome é obrigatório'),
  email: yup.string().email('Email inválido').required('Email é obrigatório'),
  document: yup.string().required('Documento é obrigatório'),
  phone: yup.string().nullable(),
  zip_code: yup.string().nullable(),
  street: yup.string().nullable(),
  city: yup.string().nullable(),
  state: yup.string().nullable(),
});

const { handleSubmit, setValues, resetForm, setFieldValue, values } = useForm({
  validationSchema: schema,
  initialValues: {
    name: '',
    email: '',
    document: '',
    phone: '',
    zip_code: '',
    street: '',
    city: '',
    state: '',
  },
});

watch(
  () => props.client,
  (val) => {
    if (val) setValues(val);
    else resetForm();
  }
);

async function onCepBlur(value) {
  if (!value) return;
  const addr = await fetchAddressByCep(value);
  if (!addr) return;
  if (addr.street) setFieldValue('street', addr.street);
  if (addr.city) setFieldValue('city', addr.city);
  if (addr.state) setFieldValue('state', addr.state);
}

const submitHandler = handleSubmit((values) => {
  emit('submit', values);
});
</script>

<style scoped>
.drawer-enter-active > div,
.drawer-leave-active > div {
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.drawer-enter-from > div,
.drawer-leave-to > div {
  transform: translateX(100%);
}

.drawer-enter-to > div,
.drawer-leave-from > div {
  transform: translateX(0);
}
</style>
