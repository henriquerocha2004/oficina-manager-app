<template>
  <div class="service-order-filters">
    <FilterDropdown :activeCount="activeFiltersCount" @clear="onClearFilters">
      <div class="filters-grid">

        <div class="filter-item">
          <label class="filter-label">De</label>
          <KtDatePickerInput
            v-model="filters.dateFrom"
            :max="filters.dateTo"
            aria-label="Selecionar data inicial"
            trigger-label="Selecionar data inicial"
            @change="applyFiltersIfDateRangeComplete"
          />
        </div>

        <div class="filter-item">
          <label class="filter-label">Até</label>
          <KtDatePickerInput
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

        <div class="filter-item">
          <label class="filter-label">Status</label>
          <select v-model="filters.status" class="kt-select" @change="applyFilters">
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
import { computed } from 'vue';
import FilterDropdown from '@/Shared/Components/FilterDropdown.vue';
import KtDatePickerInput from '@/Shared/Components/KtDatePickerInput.vue';
import { useServiceOrderFilters } from '@/Composables/useServiceOrderFilters.js';
import { ServiceOrderStatusLabels } from '@/Data/serviceOrderStatuses.js';

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
