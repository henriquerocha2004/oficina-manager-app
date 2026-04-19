<template>
  <div class="service-order-filters">
    <FilterDropdown :activeCount="activeFiltersCount" @clear="onClearFilters">
      <div class="filters-grid">

        <div class="filter-item">
          <label class="filter-label">De</label>
          <input v-if="isMobile"
            v-model="filters.dateFrom"
            type="date"
            class="kt-input"
            @change="applyFiltersIfDateRangeComplete"
          />
          <KtDatePickerInput v-else
            v-model="filters.dateFrom"
            :max="filters.dateTo"
            aria-label="Selecionar data inicial"
            trigger-label="Selecionar data inicial"
            @change="applyFiltersIfDateRangeComplete"
          />
        </div>

        <div class="filter-item">
          <label class="filter-label">Até</label>
          <input v-if="isMobile"
            v-model="filters.dateTo"
            type="date"
            class="kt-input"
            @change="applyFiltersIfDateRangeComplete"
          />
          <KtDatePickerInput v-else
            v-model="filters.dateTo"
            :min="filters.dateFrom"
            aria-label="Selecionar data final"
            trigger-label="Selecionar data final"
            @change="applyFiltersIfDateRangeComplete"
          />
        </div>

        <div class="filter-item">
          <label class="filter-label">Cliente</label>
          <input
            v-model="filters.client"
            type="text"
            placeholder="Nome do cliente"
            class="kt-input"
            @input="applyFiltersDebounced"
          />
        </div>

        <div class="filter-item">
          <label class="filter-label">Placa</label>
          <input
            v-model="filters.plate"
            type="text"
            placeholder="ABC-1234"
            class="kt-input"
            @input="applyFiltersDebounced"
          />
        </div>

        <div class="filter-item">
          <label class="filter-label">Nº OS</label>
          <input
            v-model="filters.orderNumber"
            type="text"
            placeholder="Ex: 75926"
            class="kt-input"
            @input="applyFiltersDebounced"
          />
        </div>

        <div class="filter-item" :class="{ 'status-full': isMobile }">
          <label class="filter-label">Status</label>
          <div v-if="isMobile" class="status-chips">
            <button
              class="status-chip"
              :class="{ active: filters.status === '' }"
              @click="selectStatus('')"
            >Todos</button>
            <button
              v-for="(label, value) in statusOptions"
              :key="value"
              class="status-chip"
              :class="{ active: filters.status === value }"
              @click="selectStatus(value)"
            >{{ label }}</button>
          </div>
          <select v-else v-model="filters.status" class="kt-select" @change="applyFilters">
            <option value="">Todos</option>
            <option v-for="(label, value) in statusOptions" :key="value" :value="value">
              {{ label }}
            </option>
          </select>
        </div>

      </div>
    </FilterDropdown>
  </div>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import FilterDropdown from '@/Shared/Components/FilterDropdown.vue';
import KtDatePickerInput from '@/Shared/Components/KtDatePickerInput.vue';
import { useServiceOrderFilters } from '@/Composables/useServiceOrderFilters.js';
import { ServiceOrderStatusLabels } from '@/Data/serviceOrderStatuses.js';

const windowWidth = ref(typeof window !== 'undefined' ? window.innerWidth : 1024);
const isMobile = computed(() => windowWidth.value < 768);
function onResize() { windowWidth.value = window.innerWidth; }
onMounted(() => window.addEventListener('resize', onResize));
onUnmounted(() => window.removeEventListener('resize', onResize));

const emit = defineEmits(['filter-change']);

const { filters, clearFilters, activeFiltersCount, initFromStorage, saveToStorage } = useServiceOrderFilters();

const statusOptions = computed(() => {
  const options = {};
  Object.entries(ServiceOrderStatusLabels).forEach(([value, label]) => {
    options[value] = label;
  });
  return options;
});

function applyFilters() {
  saveToStorage();
  emit('filter-change', { ...filters });
}

function applyFiltersIfDateRangeComplete() {
  if (filters.dateFrom && filters.dateTo) {
    applyFilters();
  }
}

function onClearFilters() {
  clearFilters();
  emit('filter-change', { ...filters });
}

function selectStatus(value) {
  filters.status = value;
  applyFilters();
}

let debounceTimer = null;
function applyFiltersDebounced() {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(applyFilters, 300);
}

initFromStorage();
</script>

<style scoped>
.filters-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.875rem;
  width: 100%;
}

@media (max-width: 640px) {
  .filters-grid {
    grid-template-columns: 1fr 1fr;
  }
}

.status-full {
  grid-column: 1 / -1;
}

.status-chips {
  display: flex;
  gap: 0.375rem;
  flex-wrap: wrap;
}

.status-chip {
  flex: 0 0 auto;
  padding: 0.375rem 0.625rem;
  font-size: 0.75rem;
  font-weight: 500;
  color: #6b7280;
  background: transparent;
  border: 1.5px solid #d1d5db;
  border-radius: 2rem;
  cursor: pointer;
  transition: all 0.15s;
  white-space: nowrap;
}

.status-chip.active {
  color: #fff;
  background: #f97316;
  border-color: #f97316;
}

.dark .status-chip {
  color: #94a3b8;
  border-color: #334155;
}

.dark .status-chip.active {
  color: #fff;
  background: #f97316;
  border-color: #f97316;
}

.filter-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.filter-label {
  font-size: 0.8125rem;
  font-weight: 500;
  color: #374151;
}

.dark .filter-label {
  color: #d1d5db;
}
</style>
