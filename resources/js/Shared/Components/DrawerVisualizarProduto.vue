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
              Visualizar Produto
            </h2>
            <button class="kt-btn kt-btn-sm kt-btn-icon" @click="$emit('close')">
              <i class="ki-filled ki-cross"></i>
            </button>
          </div>

          <div class="kt-tabs kt-tabs-line px-5 border-b border-border" data-kt-tabs="true">
            <button 
              class="kt-tab-toggle py-3 active" 
              data-kt-tab-toggle="#tab_view_product_data"
            >
              Dados
            </button>
            <button 
              class="kt-tab-toggle py-3" 
              data-kt-tab-toggle="#tab_view_product_suppliers"
            >
              Fornecedores
            </button>
            <button 
              class="kt-tab-toggle py-3" 
              data-kt-tab-toggle="#tab_view_product_movements"
            >
              Movimentações
            </button>
          </div>

          <div class="flex-1 overflow-y-auto">
            <div 
              id="tab_view_product_data" 
              class="h-full p-5"
            >
              <div v-if="product" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                  <div class="field-view">
                    <label class="field-label">Nome</label>
                    <p class="field-value">{{ product.name }}</p>
                  </div>

                  <div class="field-view">
                    <label class="field-label">Categoria</label>
                    <p class="field-value">{{ getCategoryLabel(product.category) }}</p>
                  </div>

                  <div class="field-view">
                    <label class="field-label">Unidade</label>
                    <p class="field-value">{{ getUnitLabel(product.unit) }}</p>
                  </div>

                  <div class="field-view">
                    <label class="field-label">Preço Unitário</label>
                    <p class="field-value">{{ formatCurrency(product.unit_price) }}</p>
                  </div>

                  <div class="field-view">
                    <label class="field-label">Preço Sugerido</label>
                    <p class="field-value">
                      {{ product.suggested_price ? formatCurrency(product.suggested_price) : 'Não definido' }}
                    </p>
                  </div>

                  <div class="field-view">
                    <label class="field-label">Status</label>
                    <p class="field-value">
                      <span
                        :class="[
                          'px-2 py-1 text-xs rounded-full',
                          product.is_active
                            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                            : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                        ]"
                      >
                        {{ product.is_active ? 'Ativo' : 'Inativo' }}
                      </span>
                    </p>
                  </div>
                </div>

                <div v-if="product.description" class="field-view">
                  <label class="field-label">Descrição</label>
                  <p class="field-value">{{ product.description }}</p>
                </div>

                <div v-if="product.sku" class="field-view">
                  <label class="field-label">SKU</label>
                  <p class="field-value">{{ product.sku }}</p>
                </div>

                <div v-if="product.barcode" class="field-view">
                  <label class="field-label">Código de Barras</label>
                  <p class="field-value">{{ product.barcode }}</p>
                </div>

                <div v-if="product.manufacturer" class="field-view">
                  <label class="field-label">Fabricante</label>
                  <p class="field-value">{{ product.manufacturer }}</p>
                </div>

                <div v-if="product.min_stock_level" class="field-view">
                  <label class="field-label">Nível Mínimo de Estoque</label>
                  <p class="field-value">{{ product.min_stock_level }}</p>
                </div>
              </div>
            </div>

            <div 
              id="tab_view_product_suppliers" 
              class="hidden h-full p-5"
            >
              <div v-if="product && product.suppliers && product.suppliers.length > 0" class="space-y-4">
                <ProductSupplierCard
                  v-for="supplier in product.suppliers"
                  :key="supplier.id"
                  :supplier="supplier"
                  :format-currency="formatCurrency"
                  :readonly="true"
                />
              </div>
              <div v-else class="flex items-center justify-center h-full">
                <p class="text-gray-500 dark:text-gray-400">Nenhum fornecedor vinculado</p>
              </div>
            </div>

            <div 
              id="tab_view_product_movements" 
              class="hidden h-full p-5"
            >
              <div v-if="product && product.stock_movements && product.stock_movements.length > 0" class="space-y-4">
                <div
                  v-for="movement in product.stock_movements"
                  :key="movement.id"
                  class="movement-card"
                >
                  <div class="movement-icon-wrapper" :class="getMovementColorClass(movement.movement_type)">
                    <i :class="['ki-filled', getMovementTypeIcon(movement.movement_type)]"></i>
                  </div>
                  <div class="movement-content">
                    <div class="flex items-center justify-between mb-2">
                      <h4 class="movement-title">
                        {{ getMovementTypeLabel(movement.movement_type) }}
                      </h4>
                      <span class="movement-date">
                        {{ formatDate(movement.created_at) }}
                      </span>
                    </div>
                    <div class="movement-details">
                      <div class="movement-detail-item">
                        <span class="movement-detail-label">Quantidade:</span>
                        <span class="movement-detail-value">{{ movement.quantity }}</span>
                      </div>
                      <div class="movement-detail-item">
                        <span class="movement-detail-label">Motivo:</span>
                        <span class="movement-detail-value">{{ getMovementReasonLabel(movement.reason) }}</span>
                      </div>
                      <div class="movement-detail-item">
                        <span class="movement-detail-label">Saldo após:</span>
                        <span class="movement-detail-value font-semibold">{{ movement.balance_after }}</span>
                      </div>
                    </div>
                    <div v-if="movement.notes" class="movement-notes">
                      <span class="movement-detail-label">Observações:</span>
                      <p class="text-sm text-gray-600 dark:text-gray-300">{{ movement.notes }}</p>
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="flex items-center justify-center h-full">
                <p class="text-gray-500 dark:text-gray-400">Nenhuma movimentação registrada</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </teleport>
</template>

<script setup>
import { watch, nextTick } from 'vue';
import ProductSupplierCard from './Product/ProductSupplierCard.vue';
import { getCategoryLabel, getUnitLabel } from '@/Data/productData';
import { getMovementTypeLabel, getMovementReasonLabel, getMovementTypeIcon, getMovementTypeColor } from '@/Data/stockData';
import { useMasks } from '@/Composables/useMasks';

const { maskCurrency } = useMasks();

const props = defineProps({
  open: Boolean,
  product: Object,
});

defineEmits(['close']);

const initializeTabs = () => {
  nextTick(() => {
    const tabsElement = document.querySelector('[data-kt-tabs="true"]');
    if (tabsElement && window.KTTabs) {
      window.KTTabs.getOrCreateInstance(tabsElement);
    }
  });
};

watch(() => props.open, (isOpen) => {
  if (isOpen) {
    initializeTabs();
  }
});

function formatCurrency(value) {
  if (!value) return 'R$ 0,00';
  return maskCurrency(String(value));
}

function formatDate(dateString) {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}

function getMovementColorClass(type) {
  const color = getMovementTypeColor(type);
  if (color === 'green') {
    return 'movement-icon-green';
  }
  if (color === 'red') {
    return 'movement-icon-red';
  }
  return 'movement-icon-gray';
}
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

:deep(.kt-tab-toggle.active) {
  color: rgb(234 88 12) !important;
  border-bottom-color: rgb(234 88 12) !important;
}

:deep(.dark .kt-tab-toggle.active) {
  color: rgb(251 146 60) !important;
  border-bottom-color: rgb(251 146 60) !important;
}

:deep(.kt-tab-toggle:hover) {
  color: rgb(234 88 12) !important;
}

:deep(.dark .kt-tab-toggle:hover) {
  color: rgb(251 146 60) !important;
}

.field-view {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.field-label {
  font-size: 0.75rem;
  font-weight: 500;
  text-transform: uppercase;
  color: #6b7280;
}

.dark .field-label {
  color: #9ca3af;
}

.field-value {
  font-size: 0.875rem;
  color: #111827;
  word-wrap: break-word;
}

.dark .field-value {
  color: #f3f4f6;
}

.movement-card {
  display: flex;
  gap: 1rem;
  padding: 1rem;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  transition: all 0.2s;
}

.dark .movement-card {
  background: #1f2937;
  border-color: #374151;
}

.movement-card:hover {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.movement-icon-wrapper {
  flex-shrink: 0;
  width: 2.5rem;
  height: 2.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 9999px;
  font-size: 1.25rem;
}

.movement-icon-green {
  background: #d1fae5;
  color: #065f46;
}

.dark .movement-icon-green {
  background: #064e3b;
  color: #6ee7b7;
}

.movement-icon-red {
  background: #fee2e2;
  color: #991b1b;
}

.dark .movement-icon-red {
  background: #7f1d1d;
  color: #fca5a5;
}

.movement-icon-gray {
  background: #e5e7eb;
  color: #4b5563;
}

.dark .movement-icon-gray {
  background: #374151;
  color: #9ca3af;
}

.movement-content {
  flex: 1;
}

.movement-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: #111827;
}

.dark .movement-title {
  color: #f9fafb;
}

.movement-date {
  font-size: 0.75rem;
  color: #6b7280;
}

.dark .movement-date {
  color: #9ca3af;
}

.movement-details {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.75rem;
  margin-top: 0.5rem;
}

.movement-detail-item {
  display: flex;
  flex-direction: column;
  gap: 0.125rem;
}

.movement-detail-label {
  font-size: 0.75rem;
  color: #6b7280;
}

.dark .movement-detail-label {
  color: #9ca3af;
}

.movement-detail-value {
  font-size: 0.875rem;
  color: #111827;
}

.dark .movement-detail-value {
  color: #f3f4f6;
}

.movement-notes {
  margin-top: 0.75rem;
  padding-top: 0.75rem;
  border-top: 1px solid #e5e7eb;
}

.dark .movement-notes {
  border-top-color: #374151;
}
</style>
