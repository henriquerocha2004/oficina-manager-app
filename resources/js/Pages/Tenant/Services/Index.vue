<template>
  <TenantLayout title="Serviços" :breadcrumbs="breadcrumbs">
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
            <button class="kt-btn kt-btn-primary" @click="onNew">Novo Serviço</button>
          </div>
        </template>

        <template #filters>
          <FilterDropdown
            :activeCount="activeFiltersCount"
            @clear="onClearFilters"
          >
            <div class="filter-item">
              <label class="filter-label">
                Categoria
              </label>
              <select
                v-model="filters.category"
                class="kt-select"
              >
                <option value="">Todas</option>
                <option v-for="cat in categories" :key="cat.value" :value="cat.value">
                  {{ cat.label }}
                </option>
              </select>
            </div>
          </FilterDropdown>
        </template>

        <template #cell-category="{ row }">
          <span>{{ getCategoryLabel(row.category) }}</span>
        </template>

        <template #cell-base_price="{ row }">
          <span class="font-medium">{{ formatCurrency(row.base_price) }}</span>
        </template>

        <template #cell-estimated_time="{ row }">
          <span>{{ row.estimated_time ? row.estimated_time + ' min' : '-' }}</span>
        </template>

        <template #cell-is_active="{ row }">
          <span
            :class="row.is_active
              ? 'px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
              : 'px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'"
          >
            {{ row.is_active ? 'Ativo' : 'Inativo' }}
          </span>
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

  <DrawerServico
    :open="drawerOpen"
    :isEdit="drawerEdit"
    :service="drawerService"
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
import { fetchServices, createService, updateService, deleteService } from '@/services/serviceService.js';
import DrawerServico from '../../../Shared/Components/DrawerServico.vue';
import ConfirmModal from '../../../Shared/Components/ConfirmModal.vue';
import { useToast } from '@/Shared/composables/useToast.js';
import { useServiceFilters } from '@/Composables/useServiceFilters.js';
import { useStats } from '@/Composables/useStats.js';
import { useExportCSV } from '@/Composables/useExportCSV.js';
import { serviceCategories, getCategoryLabel } from '@/Data/serviceCategories.js';

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
const drawerService = ref(null);
const confirmModal = ref(null);
const loading = ref(false);

const { filters, saveToStorage, loadFromStorage, clearFilters, activeFiltersCount } = useServiceFilters();
const categories = serviceCategories;
const { stats } = useStats(items, 'services');

const breadcrumbs = [{ label: 'Serviços' }];
const formatCurrency = (value) => {
  if (!value) return 'R$ 0,00';
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  }).format(value);
};

const load = async () => {
  loading.value = true;
  try {
    const res = await fetchServices({
      page: page.value,
      perPage: perPage.value,
      search: search.value,
      sortKey: sortKey.value,
      sortDir: sortDir.value,
      filters: {
        category: filters.category || undefined,
      }
    });
    items.value = res.items;
    total.value = res.total;
  } catch (error) {
    toast.error('Erro ao carregar serviços: ' + (error.response?.data?.message || error.message));
  } finally {
    loading.value = false;
  }
};

function onClearFilters() {
  clearFilters();
  page.value = 1;
  load();
}

function handleExport() {
    const columns = [
        { key: 'name', label: 'Nome' },
        { key: 'category', label: 'Categoria' },
        { key: 'base_price', label: 'Preço Base' },
        { key: 'estimated_time', label: 'Tempo Estimado (min)' },
        { key: 'description', label: 'Descrição' },
        { key: 'is_active', label: 'Status' },
    ];

    const dataToExport = items.value.map(item => ({
        ...item,
        category: getCategoryLabel(item.category),
        base_price: formatCurrency(item.base_price),
        is_active: item.is_active ? 'Ativo' : 'Inativo',
    }));

    exportToCSV(dataToExport, columns, 'servicos');
}

watch(
  () => filters.category,
  () => {
    saveToStorage();
    page.value = 1;
    load();
  }
);

onMounted(() => {
  Object.assign(filters, loadFromStorage());
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
  drawerService.value = null;
  drawerOpen.value = true;
}

function onEdit(id) {
  const service = items.value.find(i => i.id === id);
  if (service) {
    drawerEdit.value = true;
    drawerService.value = { ...service };
    drawerOpen.value = true;
  }
}

function onDrawerClose() {
  drawerOpen.value = false;
}

async function onDrawerSubmit(data) {
  const result = drawerEdit.value
    ? await updateService(drawerService.value.id, data)
    : await createService(data);

  if (!result.success) {
    toast.error('Erro ao ' + (drawerEdit.value ? 'atualizar' : 'criar') + ' serviço: ' + (result.error.response?.data?.message || result.error.message));
    return;
  }

  toast.success('Serviço ' + (drawerEdit.value ? 'atualizado' : 'criado') + ' com sucesso!');

  await load();
  drawerOpen.value = false;
}

function onDelete(id) {
  confirmModal.value.open({
    title: 'Deletar Serviço',
    message: 'Tem certeza que deseja deletar este serviço?'
  }).then(async (confirmed) => {
    if (confirmed) {
      const result = await deleteService(id);
      if (!result.success) {
        toast.error('Erro ao deletar serviço: ' + (result.error.response?.data?.message || result.error.message));
        return;
      }
      toast.success('Serviço deletado com sucesso!');
      load();
    }
  });
}

const columns = [
  { key: 'name', label: 'Nome', sortable: true },
  { key: 'category', label: 'Categoria', sortable: true },
  { key: 'base_price', label: 'Preço Base', sortable: true },
  { key: 'estimated_time', label: 'Tempo Est.', sortable: true },
  { key: 'is_active', label: 'Status', sortable: false },
  { key: 'actions', label: 'Ações', sortable: false }
];
</script>

<style>
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
