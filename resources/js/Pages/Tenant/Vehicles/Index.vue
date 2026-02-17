<template>
  <TenantLayout title="Veículos" :breadcrumbs="breadcrumbs">
    <div class="kt-container-fixed w-full py-4 px-2">
      <StatsContainer>
        <StatsCard
          v-for="(stat, index) in stats"
          :key="index"
          :icon="stat.icon"
          :title="stat.title"
          :value="stat.value"
          :subtitle="stat.subtitle"
          :trend="stat.trend"
          :color="stat.color"
        />
      </StatsContainer>

      <DataGrid
        :columns="columns"
        :items="items"
        :total="total"
        :page="page"
        :perPage="perPage"
        @update:page="onPage"
        @sort="onSort"
        @search="onSearch"
      >
        <template #actions>
          <div class="flex items-center gap-2">
            <ExportButton @export="handleExport" />
            <button class="kt-btn kt-btn-primary" @click="onNew">Novo Veículo</button>
          </div>
        </template>

        <template #filters>
          <FilterDropdown
            :activeCount="activeFiltersCount"
            @clear="onClearFilters"
          >
            <div class="filter-item">
              <label class="filter-label">
                Tipo de Veículo
              </label>
              <select
                v-model="filters.vehicle_type"
                class="kt-select"
              >
                <option value="">Todos</option>
                <option value="car">Carro</option>
                <option value="motorcycle">Moto</option>
              </select>
            </div>

            <div class="filter-item relative">
              <label class="filter-label">
                Cliente
              </label>
              <input
                v-model="clientSearch"
                type="text"
                class="kt-input"
                placeholder="Buscar cliente..."
                @input="onClientSearchInput"
                @focus="showClientDropdown = true"
                @blur="onClientBlur"
              />
              <div
                v-if="showClientDropdown && (loadingClients || filteredClients.length > 0)"
                class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg max-h-60 overflow-auto"
              >
                <template v-if="loadingClients">
                  <div
                    v-for="i in 3"
                    :key="'skeleton-' + i"
                    class="px-4 py-3 animate-pulse"
                  >
                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4 mb-2"></div>
                    <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
                  </div>
                </template>
                <template v-else>
                  <div
                    v-for="client in filteredClients"
                    :key="client.id"
                    class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer"
                    @mousedown.prevent="selectClient(client)"
                  >
                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ client.name }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                      {{ client.document_number || client.email }}
                    </div>
                  </div>
                </template>
              </div>
            </div>
          </FilterDropdown>
        </template>
        <template #cell-vehicle_type="{ row }">
          <div class="flex items-center gap-2">
            <i
              v-if="row.vehicle_type === 'car'"
              class="ki-filled ki-car text-blue-600 text-xl"
              title="Carro"
            ></i>
            <svg
              v-else
              class="w-5 h-5 fill-current text-orange-600"
              title="Moto"
              viewBox="0 0 467.161 467.161"
              xmlns="http://www.w3.org/2000/svg"
            >
              <g>
                <path d="M391.083,210.102c-10.262,0-20.05,2.054-28.991,5.754l-4.204-7.886c10.289-4.373,21.533-6.766,33.195-6.766 c14.523,0,28.863,3.744,41.472,10.826c3.608,2.028,8.183,0.746,10.212-2.866c2.028-3.611,0.745-8.184-2.866-10.212 c-14.846-8.339-31.727-12.748-48.817-12.748c-14.184,0-27.84,3.01-40.273,8.49l-7.419-13.917c0.697,0.047,1.398,0.08,2.107,0.08 h17.363c4.143,0,7.5-3.358,7.5-7.5v-48.49c0-4.142-3.357-7.5-7.5-7.5h-17.363c-11.763,0-22.005,6.624-27.203,16.335l-13.238-24.832 c-1.303-2.445-3.848-3.972-6.618-3.972H263.11c-4.143,0-7.5,3.358-7.5,7.5s3.357,7.5,7.5,7.5h30.828l12.748,23.913 c-1.641-1.205-3.383-2.306-5.232-3.278c-11.296-5.936-24.958-6.282-36.543-0.927l-23.357,10.8 c-8.455,3.909-15.074,10.358-19.271,18.091c-6.716-7.44-16.062-12.511-26.711-13.715L81.73,141.913h0 c-5.808-0.657-11.636,1.2-15.994,5.092c-4.357,3.893-6.857,9.477-6.857,15.32v32.943c-1.436,0.62-2.85,1.281-4.235,1.995 c-25.867,13.334-44.631,37.128-51.48,65.28c-0.979,4.025,1.49,8.081,5.515,9.061c0.596,0.145,1.192,0.214,1.779,0.214 c3.379,0,6.447-2.3,7.282-5.729c5.821-23.925,21.777-44.152,43.778-55.493c7.854-4.049,16.868-6.189,26.068-6.189h10.355h120.262 v21.75h-71.313c-12.899-10.042-29.069-16.052-46.575-16.052c-39.079,0-71.561,29.868-75.634,67.969H7.5c-4.142,0-7.5,3.358-7.5,7.5 v24.938c0,4.142,3.358,7.5,7.5,7.5h23.716c12.314,26.667,39.442,44.25,69.1,44.25s56.786-17.583,69.1-44.25H221.1 c16.36,0,29.842-12.538,31.354-28.509c13.915-5.003,25.737-15.026,32.921-28.55l35.532-69.341 c2.005-5.34,2.823-10.867,2.584-16.278l14.085,26.421c-18.799,11.922-33.568,30.048-41.125,52.147 c-1.34,3.919,0.751,8.183,4.67,9.523c0.804,0.275,1.622,0.405,2.428,0.405c3.118,0,6.03-1.96,7.096-5.075 c6.297-18.414,18.478-33.591,34.006-43.729l4.212,7.902c-20.399,13.661-33.859,36.912-33.859,63.252 c0,41.95,34.129,76.078,76.078,76.078s76.078-34.128,76.078-76.078S433.032,210.102,391.083,210.102z M329.664,148.199 c0-8.73,7.104-15.833,15.834-15.833h9.863v33.49h-9.863c-8.73,0-15.834-7.103-15.834-15.833V148.199z M247.848,164.021l23.357-10.8 c7.379-3.411,16.077-3.19,23.271,0.59c11.715,6.155,17.041,20.139,12.388,32.528l-1.152,3.069h-73.178 c-0.101-0.567-0.216-1.129-0.339-1.688C232.504,177.704,238.391,168.394,247.848,164.021z M97.942,189.408H87.586H73.88v-27.083 c0-2.159,1.159-3.516,1.85-4.134c0.691-0.617,2.174-1.616,4.315-1.374h0l113.841,12.869c11.285,1.276,20.311,9.268,23.278,19.721 H97.942z M175.95,278.075c-1.466-13.71-6.618-26.346-14.425-36.917h30.533l19.21,36.917H175.95z M100.316,225.106 c30.824,0,56.523,23.15,60.514,52.969H39.802C43.793,248.257,69.491,225.106,100.316,225.106z M100.316,347.263 c-21.4,0-41.16-11.401-52.12-29.25h9.511c10.045,13.46,25.669,21.391,42.609,21.391c16.939,0,32.564-7.931,42.609-21.391h9.511 C141.477,335.862,121.716,347.263,100.316,347.263z M79.176,318.013h42.28c-6.162,4.108-13.462,6.391-21.14,6.391 C92.638,324.403,85.338,322.121,79.176,318.013z M236.239,293.075c-2.542,5.843-8.371,9.938-15.139,9.938H15v-9.938H236.239z M272.129,253.917c-7.917,14.901-23.317,24.159-40.191,24.159h-3.76l-19.21-36.917h16.737c4.142,0,7.5-3.358,7.5-7.5v-29.25h65.227 L272.129,253.917z M391.083,347.258c-33.679,0-61.078-27.399-61.078-61.078c0-20.614,10.275-38.862,25.962-49.928l3.726,6.989 c-13.222,9.692-21.828,25.326-21.828,42.939c0,29.345,23.874,53.219,53.219,53.219s53.219-23.874,53.219-53.219 c0-29.345-23.874-53.219-53.219-53.219c-6.378,0-12.496,1.13-18.168,3.197l-3.723-6.984c6.801-2.621,14.178-4.073,21.891-4.073 c33.679,0,61.078,27.4,61.078,61.079S424.761,347.258,391.083,347.258z M375.128,272.196c1.35,2.531,3.943,3.973,6.625,3.973 c1.19,0,2.397-0.284,3.521-0.883c3.655-1.948,5.038-6.491,3.09-10.146l-8.292-15.555c3.489-1.052,7.183-1.624,11.01-1.624 c21.074,0,38.219,17.145,38.219,38.219c0,21.074-17.145,38.219-38.219,38.219s-38.219-17.145-38.219-38.219 c0-11.88,5.45-22.511,13.979-29.526L375.128,272.196z"></path>
                <path d="M384.153,289.049c2.018,4.77,8.537,6.235,12.23,2.43c2.896-2.815,2.893-7.783,0-10.6c-2.452-2.526-6.564-2.878-9.47-0.94 C383.954,281.913,382.844,285.772,384.153,289.049C384.343,289.499,383.973,288.599,384.153,289.049z"></path>
              </g>
            </svg>
          </div>
        </template>
        <template #cell-client="{ row }">
          <span>{{ row.client?.name || '-' }}</span>
        </template>
        <template #cell-actions="{ row }">
          <div class="text-end flex gap-2 justify-end">
            <button class="kt-btn kt-btn-sm kt-btn-ghost" @click="onEdit(row.id)" title="Editar">
              <i class="ki-filled ki-pencil text-gray-800 dark:text-gray-200"></i>
            </button>
            <button class="kt-btn kt-btn-sm text-white" @click="onDelete(row.id)" title="Deletar">
              <i class="ki-filled ki-trash"></i>
            </button>
          </div>
        </template>
      </DataGrid>
    </div>
  </TenantLayout>
  <DrawerVeiculo
    :open="drawerOpen"
    :isEdit="drawerEdit"
    :vehicle="drawerVehicle"
    @close="onDrawerClose"
    @submit="onDrawerSubmit"
  />
  <ConfirmModal ref="confirmModal" />
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import DataGrid from '../../../Shared/Components/DataGrid.vue';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import StatsContainer from '@/Shared/Components/StatsContainer.vue';
import StatsCard from '@/Shared/Components/StatsCard.vue';
import ExportButton from '@/Shared/Components/ExportButton.vue';
import FilterDropdown from '@/Shared/Components/FilterDropdown.vue';
import { fetchVehicles, createVehicle, updateVehicle, deleteVehicle, fetchVehicleStats } from '@/services/vehicleService.js';
import { fetchClients } from '@/services/clientService.js';
import DrawerVeiculo from '../../../Shared/Components/DrawerVeiculo.vue';
import ConfirmModal from '../../../Shared/Components/ConfirmModal.vue';
import { useMasks } from '@/Composables/useMasks.js';
import { useToast } from '@/Shared/composables/useToast.js';
import { useVehicleFilters } from '@/Composables/useVehicleFilters.js';
import { useStats } from '@/Composables/useStats.js';
import { useExportCSV } from '@/Composables/useExportCSV.js';

const toast = useToast();
const { exportToCSV } = useExportCSV();

const page = ref(1);
const perPage = ref(6);
const total = ref(0);
const items = ref([]);
const search = ref('');
const sortKey = ref(null);
const sortDir = ref('asc');
const drawerOpen = ref(false);
const drawerEdit = ref(false);
const drawerVehicle = ref(null);
const confirmModal = ref(null);
const loading = ref(false);
const statsFromApi = ref(null);

// Filtros
const { filters, saveToStorage, loadFromStorage, clearFilters, activeFiltersCount } = useVehicleFilters();
const clientSearch = ref('');

const { stats } = useStats(items, 'vehicles', statsFromApi);
const showClientDropdown = ref(false);
const filteredClients = ref([]);
const loadingClients = ref(false);
const debounceTimer = ref(null);

const breadcrumbs = [{ label: 'Veículos' }];

const { unmaskLicensePlate } = useMasks();

const loadStats = async () => {
  const result = await fetchVehicleStats();
  if (result.success) {
    statsFromApi.value = result.data;
  } else {
    toast.error('Erro ao carregar estatísticas.');
  }
};

const load = async () => {
  loading.value = true;
  try {
    const res = await fetchVehicles({
      page: page.value,
      perPage: perPage.value,
      search: search.value,
      sortKey: sortKey.value,
      sortDir: sortDir.value,
      filters: {
        vehicle_type: filters.vehicle_type || undefined,
        client_id: filters.client_id || undefined,
      }
    });
    items.value = res.items;
    total.value = res.total;
  } finally {
    loading.value = false;
  }
};

async function onClientSearchInput() {
  if (debounceTimer.value) {
    clearTimeout(debounceTimer.value);
  }

  debounceTimer.value = setTimeout(async () => {
    if (clientSearch.value.length < 2) {
      filteredClients.value = [];
      return;
    }

    loadingClients.value = true;
    try {
      const result = await fetchClients({
        search: clientSearch.value,
        perPage: 10,
        page: 1,
      });
      filteredClients.value = result.items;
      showClientDropdown.value = true;
    } catch (error) {
      console.error('Erro ao buscar clientes:', error);
      filteredClients.value = [];
    } finally {
      loadingClients.value = false;
    }
  }, 300);
}

function selectClient(client) {
  filters.client_id = client.id;
  filters.clientName = client.name;
  clientSearch.value = client.name;
  showClientDropdown.value = false;
}

function onClientBlur() {
  setTimeout(() => {
    showClientDropdown.value = false;
  }, 200);
}

function onClearFilters() {
  clearFilters();
  clientSearch.value = '';
  page.value = 1;
  load();
}

function handleExport() {
    const columns = [
        { key: 'brand', label: 'Marca' },
        { key: 'model', label: 'Modelo' },
        { key: 'year', label: 'Ano' },
        { key: 'color', label: 'Cor' },
        { key: 'license_plate', label: 'Placa' },
        { key: 'vehicle_type', label: 'Tipo' },
        { key: 'client_name', label: 'Cliente' },
    ];

    // Exporta os dados
    const dataToExport = items.value.map(item => ({
        ...item,
        vehicle_type: item.vehicle_type === 'car' ? 'Carro' : 'Moto',
        client_name: item.client?.name || '-',
    }));

    exportToCSV(dataToExport, columns, 'veiculos');
}

watch(
  [() => filters.vehicle_type, () => filters.client_id],
  () => {
    saveToStorage();
    page.value = 1;
    load();
  }
);

onMounted(() => {
  Object.assign(filters, loadFromStorage());
  if (filters.clientName) {
    clientSearch.value = filters.clientName;
  }
  loadStats();
  load();
});

function onPage(p) {
  page.value = p;
  load();
}

function onSearch(q) {
  search.value = q;
  page.value = 1;
  load();
}

function onSort({ key, dir }) {
  sortKey.value = key;
  sortDir.value = dir;
  page.value = 1;
  load();
}

function onNew() {
  drawerEdit.value = false;
  drawerVehicle.value = null;
  drawerOpen.value = true;
}

function onEdit(id) {
  const vehicle = items.value.find(i => i.id === id);
  if (vehicle) {
    drawerEdit.value = true;
    drawerVehicle.value = { ...vehicle };
    drawerOpen.value = true;
  }
}

function onDrawerClose() {
  drawerOpen.value = false;
}

async function onDrawerSubmit(data) {
  const unmaskedData = {
    ...data,
    license_plate: unmaskLicensePlate(data.license_plate),
  };

  const result = drawerEdit.value
    ? await updateVehicle(drawerVehicle.value.id, unmaskedData)
    : await createVehicle(unmaskedData);

  if (!result.success) {
    toast.error('Erro ao ' + (drawerEdit.value ? 'atualizar' : 'criar') + ' veículo: ' + (result.error.response?.data?.message || result.error.message));
    return;
  }

  toast.success('Veículo ' + (drawerEdit.value ? 'atualizado' : 'criado') + ' com sucesso!');

  await load();
  drawerOpen.value = false;
}

function onDelete(id) {
  confirmModal.value.open({
    title: 'Deletar Veículo',
    message: 'Tem certeza que deseja deletar este veículo?'
  }).then(async (confirmed) => {
    if (confirmed) {
      const result = await deleteVehicle(id);
      if (!result.success) {
        toast.error('Erro ao deletar veículo: ' + (result.error.response?.data?.message || result.error.message));
        return;
      }
      toast.success('Veículo deletado com sucesso!');
      await load();
    }
  });
}

const columns = [
  { key: 'vehicle_type', label: 'Tipo', sortable: false },
  { key: 'license_plate', label: 'Placa', sortable: true },
  { key: 'brand', label: 'Marca', sortable: true },
  { key: 'model', label: 'Modelo', sortable: true },
  { key: 'year', label: 'Ano', sortable: true },
  { key: 'client', label: 'Cliente', sortable: false },
  { key: 'actions', label: 'Ações', sortable: false }
];
</script>

<style>
@media (max-width: 640px) {
  html, body {
    font-size: 1.1rem !important;
    zoom: 1 !important;
  }
}
</style>

<style>
/* Container ocupa toda largura no mobile */
.kt-container-fixed {
  box-sizing: border-box;
  width: 100%;
  max-width: 100%;
  margin: 0 auto;
}
@media (min-width: 640px) {
  .kt-container-fixed { max-width: 640px; }
}
@media (min-width: 768px) {
  .kt-container-fixed { max-width: 768px; }
}
@media (min-width: 1024px) {
  .kt-container-fixed { max-width: 1400px; }
}
@media (min-width: 1280px) {
  .kt-container-fixed { max-width: 1600px; }
}
@media (max-width: 640px) {
  html, body {
    font-size: 1.1rem !important;
    zoom: 1 !important;
  }
}
</style>

<style scoped>
.filter-item {
    display: flex;
    flex-direction: column;
    gap: 0.375rem;
}

.filter-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
}

.dark .filter-label {
    color: #cbd5e1;
}
</style>
