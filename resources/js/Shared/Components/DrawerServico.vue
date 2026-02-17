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
              {{ isEdit ? 'Editar Serviço' : 'Novo Serviço' }}
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
            <!-- Nome -->
            <FormField name="name" label="Nome do Serviço" v-slot="{ field, errors }">
              <input v-bind="field" class="kt-input w-full" placeholder="Ex: Troca de óleo" />
              <FormError :errors="errors" />
            </FormField>

            <!-- Categoria -->
            <FormField name="category" label="Categoria" v-slot="{ field, errors }">
              <select v-bind="field" class="kt-select w-full">
                <option value="">Selecione uma categoria</option>
                <option v-for="cat in categories" :key="cat.value" :value="cat.value">
                  {{ cat.label }}
                </option>
              </select>
              <FormError :errors="errors" />
            </FormField>

            <!-- Preço Base -->
            <FormField name="base_price" label="Preço Base" v-slot="{ field, errors }">
              <input
                :value="field.value"
                class="kt-input w-full"
                placeholder="R$ 0,00"
                @input="applyMask('base_price', maskCurrency, $event)"
              />
              <FormError :errors="errors" />
            </FormField>

            <!-- Tempo Estimado -->
            <FormField name="estimated_time" label="Tempo Estimado (minutos)" v-slot="{ field, errors }">
              <input
                type="number"
                v-bind="field"
                class="kt-input w-full"
                placeholder="60"
                min="1"
                max="9999"
              />
              <FormError :errors="errors" />
            </FormField>

            <!-- Descrição -->
            <FormField name="description" label="Descrição" v-slot="{ field, errors }">
              <textarea
                v-bind="field"
                class="kt-input w-full h-32"
                rows="6"
                placeholder="Descrição detalhada do serviço..."
              />
              <FormError :errors="errors" />
            </FormField>

            <!-- Serviço Ativo -->
            <FormField name="is_active" label="Status" v-slot="{ field, errors }">
              <label class="flex items-center gap-2 cursor-pointer">
                <input
                  type="checkbox"
                  :checked="field.value"
                  @change="setFieldValue('is_active', $event.target.checked)"
                  class="kt-checkbox"
                />
                <span class="text-gray-900 dark:text-gray-100">Serviço ativo</span>
              </label>
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
import { useMasks } from '@/Composables/useMasks';
import { serviceCategories } from '@/Data/serviceCategories';

const { maskCurrency, unmaskCurrency } = useMasks();

const props = defineProps({
  open: Boolean,
  isEdit: Boolean,
  service: Object,
});

const emit = defineEmits(['close', 'submit']);

const categories = serviceCategories;

/* ---------------------------
 * Validation schema
 * --------------------------- */
const schema = yup.object({
  name: yup.string()
    .required('Nome é obrigatório')
    .min(3, 'Mínimo 3 caracteres')
    .max(255, 'Máximo 255 caracteres'),
  
  description: yup.string()
    .nullable()
    .max(1000, 'Máximo 1000 caracteres'),
  
  base_price: yup.string()
    .required('Preço base é obrigatório')
    .test('min-price', 'Preço deve ser maior que zero', (value) => {
      if (!value) return false;
      const numValue = unmaskCurrency(value);
      return numValue > 0;
    })
    .test('max-price', 'Preço muito alto', (value) => {
      if (!value) return true;
      const numValue = unmaskCurrency(value);
      return numValue <= 999999.99;
    }),
  
  category: yup.string()
    .required('Categoria é obrigatória')
    .oneOf(['maintenance', 'repair', 'diagnostic', 'painting', 'alignment', 'other'], 'Categoria inválida'),
  
  estimated_time: yup.number()
    .nullable()
    .integer('Deve ser um número inteiro')
    .min(1, 'Mínimo 1 minuto')
    .max(9999, 'Máximo 9999 minutos'),
  
  is_active: yup.boolean().default(true),
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
  initialValues: computed(() => {
    if (props.service) {
      return {
        ...props.service,
        base_price: props.service.base_price ? maskCurrency(String(props.service.base_price * 100)) : '',
      };
    }
    return {
      name: '',
      description: '',
      base_price: '',
      category: '',
      estimated_time: null,
      is_active: true,
    };
  }),
});

const applyMask = (fieldName, maskFn, event) => {
  const rawValue = event.target.value;
  const maskedValue = maskFn(rawValue);
  setFieldValue(fieldName, maskedValue);
};

/* ---------------------------
 * Sync edit / create
 * --------------------------- */
watch(
  () => props.service,
  (val) => {
    if (val) {
      setValues({
        ...val,
        base_price: val.base_price ? maskCurrency(String(val.base_price * 100)) : '',
      });
    } else {
      resetForm();
    }
  }
);

/* ---------------------------
 * Submit (clean & safe)
 * --------------------------- */
const submitHandler = handleSubmit((values) => {
  console.log('Submitting service:', values);
  
  // Converte base_price de string mascarada para número
  const cleanedValues = {
    ...values,
    base_price: unmaskCurrency(values.base_price),
  };
  
  emit('submit', cleanedValues);
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
