<template>
  <teleport to="body">
    <Transition name="drawer">
      <div
        v-if="open"
        class="fixed inset-0 z-50 flex items-center justify-end"
        style="background: rgba(0, 0, 0, 0.5);"
      >
        <div class="w-full max-w-105 h-full bg-background border-l border-border shadow-xl flex flex-col">
          <div class="flex items-center justify-between px-5 py-4 border-b border-border">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
              Ajuste de Estoque
            </h2>
            <button class="kt-btn kt-btn-sm kt-btn-icon" @click="$emit('close')">
              <i class="ki-filled ki-cross"></i>
            </button>
          </div>

          <div class="flex-1 overflow-y-auto p-5">
            <div v-if="product" class="space-y-6">
              <div class="stock-summary-card" :class="stockStatusClass">
                <div class="flex items-center justify-between mb-3">
                  <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                    {{ product.name }}
                  </h3>
                  <div class="stock-badge" :class="stockStatusClass">
                    <i class="ki-filled" :class="stockIcon"></i>
                    {{ stockStatusLabel }}
                  </div>
                </div>
                <div class="stock-info">
                  <div>
                    <p class="stock-info-label">Saldo Atual</p>
                    <p class="stock-info-value" :class="stockBalanceClass">
                      {{ currentBalance }}
                    </p>
                  </div>
                  <div>
                    <p class="stock-info-label">Categoria</p>
                    <p class="stock-info-value">{{ getCategoryLabel(product.category) }}</p>
                  </div>
                  <div>
                    <p class="stock-info-label">Unidade</p>
                    <p class="stock-info-value">{{ getUnitLabel(product.unit) }}</p>
                  </div>
                </div>
              </div>

              <form @submit.prevent="submitHandler" class="space-y-4">
                <FormField name="movement_type" label="Tipo de Movimentação" v-slot="{ field, errors }">
                  <div class="flex gap-3">
                    <label
                      class="movement-type-option"
                      :class="{ 'movement-type-option-selected': field.value === 'IN' }"
                    >
                      <input
                        type="radio"
                        v-bind="field"
                        value="IN"
                        class="sr-only"
                      />
                      <div class="movement-type-content movement-type-in">
                        <i class="ki-filled ki-arrow-down text-xl"></i>
                        <span class="font-medium">Entrada</span>
                      </div>
                    </label>
                    <label
                      class="movement-type-option"
                      :class="{ 'movement-type-option-selected': field.value === 'OUT' }"
                    >
                      <input
                        type="radio"
                        v-bind="field"
                        value="OUT"
                        class="sr-only"
                      />
                      <div class="movement-type-content movement-type-out">
                        <i class="ki-filled ki-arrow-up text-xl"></i>
                        <span class="font-medium">Saída</span>
                      </div>
                    </label>
                  </div>
                  <FormError :errors="errors" />
                </FormField>

                <FormField name="quantity" label="Quantidade" v-slot="{ field, errors }">
                  <input
                    v-bind="field"
                    type="number"
                    min="1"
                    step="1"
                    class="kt-input w-full"
                    placeholder="Digite a quantidade"
                  />
                  <FormError :errors="errors" />
                </FormField>

                <FormField name="reason" label="Motivo" v-slot="{ field, errors }">
                  <select v-bind="field" class="kt-select w-full">
                    <option value="">Selecione o motivo</option>
                    <option
                      v-for="reason in movementReasons"
                      :key="reason.value"
                      :value="reason.value"
                    >
                      {{ reason.label }}
                    </option>
                  </select>
                  <FormError :errors="errors" />
                </FormField>

                <FormField name="notes" label="Observações (Opcional)" v-slot="{ field, errors }">
                  <textarea
                    v-bind="field"
                    class="kt-input w-full"
                    rows="4"
                    maxlength="500"
                    placeholder="Digite observações sobre esta movimentação..."
                  ></textarea>
                  <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    {{ field.value?.length || 0 }}/500 caracteres
                  </div>
                  <FormError :errors="errors" />
                </FormField>

                <div class="flex justify-end gap-2 pt-4 border-t border-border">
                  <button type="button" class="kt-btn kt-btn-ghost" @click="$emit('close')">
                    Cancelar
                  </button>
                  <button type="submit" class="kt-btn kt-btn-primary">
                    Confirmar Ajuste
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </teleport>
</template>

<script setup>
import { useForm } from 'vee-validate';
import { ref, computed, watch } from 'vue';
import * as yup from 'yup';
import FormField from './FormField.vue';
import FormError from './FormError.vue';
import { getCategoryLabel, getUnitLabel } from '@/Data/productData';
import { movementReasons } from '@/Data/stockData';

const props = defineProps({
  open: Boolean,
  product: Object,
});

const emit = defineEmits(['close', 'submit']);

const schema = yup.object({
  movement_type: yup.string().required('Tipo de movimentação é obrigatório').oneOf(['IN', 'OUT']),
  quantity: yup.number().required('Quantidade é obrigatória').min(1, 'Quantidade deve ser no mínimo 1').integer('Quantidade deve ser um número inteiro'),
  reason: yup.string().required('Motivo é obrigatório').oneOf(
    movementReasons.map(r => r.value),
    'Motivo selecionado é inválido'
  ),
  notes: yup.string().nullable().max(500, 'Observações não podem ter mais de 500 caracteres'),
});

const { handleSubmit, resetForm, setFieldValue } = useForm({
  validationSchema: schema,
  initialValues: {
    movement_type: 'IN',
    quantity: null,
    reason: '',
    notes: '',
  },
});

const currentBalance = computed(() => {
  if (!props.product || !props.product.stock_movements || props.product.stock_movements.length === 0) {
    return 0;
  }
  const lastMovement = props.product.stock_movements[0];
  return lastMovement.balance_after || 0;
});

const stockStatusClass = computed(() => {
  if (currentBalance.value > 0) {
    return 'stock-status-positive';
  }
  return 'stock-status-negative';
});

const stockBalanceClass = computed(() => {
  if (currentBalance.value > 0) {
    return 'text-green-700 dark:text-green-400';
  }
  return 'text-red-700 dark:text-red-400';
});

const stockIcon = computed(() => {
  if (currentBalance.value > 0) {
    return 'ki-check-circle';
  }
  return 'ki-cross-circle';
});

const stockStatusLabel = computed(() => {
  if (currentBalance.value > 0) {
    return 'Em Estoque';
  }
  return 'Sem Estoque';
});

watch(() => props.open, (isOpen) => {
  if (isOpen) {
    resetForm();
    setFieldValue('movement_type', 'IN');
  }
});

const submitHandler = handleSubmit((values) => {
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

.stock-summary-card {
  padding: 1.25rem;
  border-radius: 0.5rem;
  border: 2px solid;
  transition: all 0.3s;
}

.stock-status-positive {
  background: rgba(16, 185, 129, 0.05);
  border-color: #10b981;
}

.dark .stock-status-positive {
  background: rgba(16, 185, 129, 0.1);
  border-color: #34d399;
}

.stock-status-negative {
  background: rgba(239, 68, 68, 0.05);
  border-color: #ef4444;
}

.dark .stock-status-negative {
  background: rgba(239, 68, 68, 0.1);
  border-color: #f87171;
}

.stock-badge {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.375rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
}

.stock-badge.stock-status-positive {
  background: #10b981;
  color: white;
}

.stock-badge.stock-status-negative {
  background: #ef4444;
  color: white;
}

.stock-info {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
}

.stock-info-label {
  font-size: 0.75rem;
  color: #6b7280;
  margin-bottom: 0.25rem;
}

.dark .stock-info-label {
  color: #9ca3af;
}

.stock-info-value {
  font-size: 1.125rem;
  font-weight: 600;
  color: #111827;
}

.dark .stock-info-value {
  color: #f9fafb;
}

.movement-type-option {
  flex: 1;
  cursor: pointer;
}

.movement-type-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  padding: 1rem;
  border: 2px solid #e5e7eb;
  border-radius: 0.5rem;
  transition: all 0.2s;
  color: #111827;
}

.dark .movement-type-content {
  border-color: #374151;
  color: #f9fafb;
}

.movement-type-content:hover {
  border-color: #d1d5db;
}

.dark .movement-type-content:hover {
  border-color: #4b5563;
}

.movement-type-option-selected .movement-type-content {
  border-width: 2px;
}

.movement-type-option-selected .movement-type-in {
  background: rgba(16, 185, 129, 0.1);
  border-color: #10b981;
  color: #065f46;
}

.dark .movement-type-option-selected .movement-type-in {
  background: rgba(16, 185, 129, 0.15);
  border-color: #34d399;
  color: #6ee7b7;
}

.movement-type-option-selected .movement-type-out {
  background: rgba(239, 68, 68, 0.1);
  border-color: #ef4444;
  color: #991b1b;
}

.dark .movement-type-option-selected .movement-type-out {
  background: rgba(239, 68, 68, 0.15);
  border-color: #f87171;
  color: #fca5a5;
}
</style>
