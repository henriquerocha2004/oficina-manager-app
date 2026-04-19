<template>
  <div class="service-order-grid lg:h-full">
    <DataGrid
      :columns="columns"
      :items="items"
      :total="total"
      :page="page"
      :per-page="perPage"
      search-placeholder="Buscar OS..."
      @update:page="onPage"
      @sort="onSort"
      @search="onSearch"
    >
      <template #filters>
        <ServiceOrderFilters @filter-change="onFilterChange" />
      </template>

      <template #cell-code="{ row }">
        <span class="font-semibold text-gray-900 dark:text-gray-100">
          {{ row.code }}
        </span>
      </template>

      <template #cell-client="{ row }">
        <div>
          <div class="font-medium text-gray-900 dark:text-gray-100">
            {{ row.client.name }}
          </div>
          <div class="text-xs text-gray-500 dark:text-gray-400">
            {{ row.client.phone }}
          </div>
        </div>
      </template>

      <template #cell-vehicle="{ row }">
        <div>
          <div class="font-medium text-gray-900 dark:text-gray-100">
            {{ row.vehicle.brand }} {{ row.vehicle.model }}
          </div>
          <div class="text-xs text-gray-500 dark:text-gray-400">
            {{ row.vehicle.plate }}
          </div>
        </div>
      </template>

      <template #cell-status="{ row }">
        <span
          :class="[
            'inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full font-medium',
            getStatusColor(row.status)
          ]"
        >
          <i :class="[getStatusIcon(row.status), 'text-xs']"></i>
          {{ getStatusLabel(row.status) }}
        </span>
      </template>

      <template #cell-entry_date="{ row }">
        {{ formatDate(row.entry_date) }}
      </template>

      <template #cell-total="{ row }">
        <span class="font-semibold text-gray-900 dark:text-gray-100">
          {{ formatCurrency(row.total) }}
        </span>
      </template>

      <template #cell-actions="{ row }">
        <div class="flex gap-2 justify-end">
          <button
            class="kt-btn kt-btn-sm kt-btn-ghost"
            title="Visualizar"
            @click="router.visit(`/service-orders/${row.id}/detail`)"
          >
            <i class="ki-filled ki-eye text-gray-600 dark:text-gray-400"></i>
          </button>
        </div>
      </template>
    </DataGrid>
  </div>
</template>

<script setup>
import { onMounted, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import DataGrid from '@/Shared/Components/DataGrid.vue';
import ServiceOrderFilters from './ServiceOrderFilters.vue';
import { useServiceOrderGrid } from '@/Composables/useServiceOrderGrid.js';
import { useServiceOrderFilters } from '@/Composables/useServiceOrderFilters.js';
import { ServiceOrderStatusLabels, ServiceOrderStatusColors, ServiceOrderStatusIcons } from '@/Data/serviceOrderStatuses.js';

const {
  items,
  loading,
  error,
  page,
  perPage,
  total,
  search,
  sortKey,
  sortDir,
  load,
  setPage,
  setSearch,
  setSort,
} = useServiceOrderGrid();

const { filters, initFromStorage } = useServiceOrderFilters();

const columns = [
  { key: 'code', label: 'OS', sortable: true },
  { key: 'client', label: 'Cliente', sortable: true },
  { key: 'vehicle', label: 'Veículo', sortable: true },
  { key: 'status', label: 'Status', sortable: true },
  { key: 'entry_date', label: 'Entrada', sortable: true },
  { key: 'total', label: 'Total', sortable: true },
  { key: 'actions', label: 'Ações', sortable: false },
];

function getStatusLabel(status) { return ServiceOrderStatusLabels[status] || status; }
function getStatusColor(status) { return ServiceOrderStatusColors[status] || 'bg-gray-100 text-gray-800'; }
function getStatusIcon(status)  { return ServiceOrderStatusIcons[status]  || 'ki-filled ki-information'; }

function formatDate(date) {
  return new Date(date).toLocaleDateString('pt-BR');
}

function formatCurrency(value) {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  }).format(value);
}

function onPage(p) {
  setPage(p);
  load(filters);
}

function onSort({ key, dir }) {
  const sortMap = {
    client: 'client_name',
    vehicle: 'vehicle_plate',
    total: 'total',
  };
  setSort(sortMap[key] || key, dir);
  load(filters);
}

function onSearch(q) {
  setSearch(q);
  load(filters);
}

function onFilterChange(newFilters) {
  load(newFilters);
}

initFromStorage();

onMounted(() => {
  load(filters);
});
</script>
