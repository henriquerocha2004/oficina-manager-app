<template>
  <TenantLayout :title="`Histórico — ${vehicleTitle}`" :breadcrumbs="breadcrumbs">
    <div class="kt-container-fixed w-full py-4 px-2 flex flex-col gap-4">

      <!-- Header -->
      <div class="flex items-center gap-3">
        <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" @click="router.visit('/vehicles')" title="Voltar">
          <i class="ki-filled ki-arrow-left text-gray-600 dark:text-gray-400"></i>
        </button>
        <i class="ki-filled ki-car text-gray-400 text-xl"></i>
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ vehicleTitle }}</h1>
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-semibold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 tracking-widest">
          {{ vehicle.license_plate }}
        </span>
      </div>

      <!-- Propriedades do veículo -->
      <div class="card bg-white dark:bg-card border border-border rounded-xl px-6 py-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
          <div>
            <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Proprietário</p>
            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ ownerName }}</p>
          </div>
          <div>
            <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Ano</p>
            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ vehicle.year ?? '—' }}</p>
          </div>
          <div>
            <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Quilometragem</p>
            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ vehicle.mileage ? `${Number(vehicle.mileage).toLocaleString('pt-BR')} km` : '—' }}</p>
          </div>
          <div>
            <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Cor</p>
            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ vehicle.color ?? '—' }}</p>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <StatsContainer>
        <StatsCard
          icon="ki-filled ki-wrench"
          title="Total de OS"
          :value="stats.total_orders"
          color="blue"
        />
        <StatsCard
          icon="ki-filled ki-calendar"
          title="Última Visita"
          :value="lastVisitFormatted"
          color="gray"
        />
        <StatsCard
          icon="ki-filled ki-warning-2"
          title="Problemas Recorrentes"
          :value="stats.recurring_problems"
          color="yellow"
        />
      </StatsContainer>

      <!-- Tabela de histórico -->
      <div class="flex flex-col gap-2">
        <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">Histórico de Serviços</h2>
        <DataGrid
          :columns="columns"
          :items="orders"
          :total="total"
          :page="page"
          :perPage="perPage"
          searchPlaceholder="Buscar OS..."
          @update:page="onPage"
          @sort="onSort"
          @search="onSearch"
        >
          <template #cell-order_number="{ row }">
            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ row.order_number }}</span>
          </template>

          <template #cell-date="{ row }">
            <span class="text-orange-500 dark:text-orange-400">{{ formatDate(row.created_at) }}</span>
          </template>

          <template #cell-description="{ row }">
            <span class="text-gray-700 dark:text-gray-300">{{ row.reported_problem || '—' }}</span>
          </template>

          <template #cell-status="{ row }">
            <span :class="['inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full font-medium', ServiceOrderStatusColors[row.status]]">
              <i :class="[ServiceOrderStatusIcons[row.status], 'text-xs']"></i>
              {{ ServiceOrderStatusLabels[row.status] }}
            </span>
          </template>

          <template #cell-total="{ row }">
            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ formatCurrency(row.total ?? 0) }}</span>
          </template>

          <template #cell-actions="{ row }">
            <div class="flex justify-end">
              <button
                class="kt-btn kt-btn-sm kt-btn-ghost"
                title="Ver OS"
                @click="router.visit(`/service-orders/${row.id}/detail`)"
              >
                <i class="ki-filled ki-eye text-gray-600 dark:text-gray-400"></i>
              </button>
            </div>
          </template>
        </DataGrid>
      </div>

    </div>
  </TenantLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import DataGrid from '@/Shared/Components/DataGrid.vue';
import StatsContainer from '@/Shared/Components/StatsContainer.vue';
import StatsCard from '@/Shared/Components/StatsCard.vue';
import { fetchVehicleServiceOrders } from '@/services/vehicleService.js';
import {
  ServiceOrderStatusLabels,
  ServiceOrderStatusColors,
  ServiceOrderStatusIcons,
} from '@/Data/serviceOrderStatuses.js';

const props = defineProps({
  vehicle: { type: Object, required: true },
  stats: { type: Object, required: true },
});

const orders = ref([]);
const total = ref(0);
const page = ref(1);
const perPage = ref(10);
const search = ref('');
const sortKey = ref('created_at');
const sortDir = ref('desc');

const columns = [
  { key: 'order_number', label: 'OS #', sortable: true },
  { key: 'date', label: 'Data', sortable: true },
  { key: 'description', label: 'Descrição', sortable: false },
  { key: 'status', label: 'Status', sortable: true },
  { key: 'total', label: 'Total', sortable: true },
  { key: 'actions', label: '', sortable: false },
];

const vehicleTitle = computed(() => {
  const parts = [props.vehicle.brand, props.vehicle.model, props.vehicle.year].filter(Boolean);
  return parts.join(' ');
});

const ownerName = computed(() => props.vehicle.current_owner?.client?.name ?? '—');

const breadcrumbs = computed(() => [
  { label: 'Veículos', href: '/vehicles' },
  { label: vehicleTitle.value },
]);

const lastVisitFormatted = computed(() => {
  if (!props.stats.last_visit) return '—';
  return formatDate(props.stats.last_visit);
});

function formatDate(value) {
  if (!value) return '—';
  return new Date(value).toLocaleDateString('pt-BR', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  });
}

function formatCurrency(value) {
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
}

async function loadOrders() {
  const result = await fetchVehicleServiceOrders(props.vehicle.id, {
    page: page.value,
    perPage: perPage.value,
    search: search.value,
    sortKey: sortKey.value,
    sortDir: sortDir.value,
  });
  orders.value = result.items;
  total.value = result.total;
}

function onPage(p) {
  page.value = p;
  loadOrders();
}

function onSort({ key, dir }) {
  sortKey.value = key;
  sortDir.value = dir;
  page.value = 1;
  loadOrders();
}

function onSearch(val) {
  search.value = val;
  page.value = 1;
  loadOrders();
}

onMounted(loadOrders);
</script>
