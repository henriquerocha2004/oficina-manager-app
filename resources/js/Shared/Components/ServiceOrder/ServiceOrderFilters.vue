<template>
  <div class="service-order-filters">
    <FilterDropdown :activeCount="activeFiltersCount" @clear="clearFilters">
      <div class="filter-item">
        <label class="filter-label">Período</label>
        <div class="flex gap-2">
          <input
            v-model="filters.dateFrom"
            type="date"
            class="kt-input"
            @change="applyFilters"
          />
          <input
            v-model="filters.dateTo"
            type="date"
            class="kt-input"
            @change="applyFilters"
          />
        </div>
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
        <label class="filter-label">Status</label>
        <select v-model="filters.status" class="kt-select" @change="applyFilters">
          <option value="">Todos</option>
          <option v-for="(label, value) in statusOptions" :key="value" :value="value">
            {{ label }}
          </option>
        </select>
      </div>
    </FilterDropdown>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import FilterDropdown from '@/Shared/Components/FilterDropdown.vue';
import { useServiceOrderFilters } from '@/Composables/useServiceOrderFilters.js';
import { ServiceOrderStatusLabels, KanbanStatuses } from '@/Data/serviceOrderStatuses.js';

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

let debounceTimer = null;
function applyFiltersDebounced() {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    applyFilters();
  }, 300);
}

initFromStorage();
</script>

<style scoped>
.filter-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.filter-label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
}

.dark .filter-label {
  color: #d1d5db;
}
</style>
