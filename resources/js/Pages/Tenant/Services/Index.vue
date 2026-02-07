<template>
  <TenantLayout title="Serviços" :breadcrumbs="breadcrumbs">
    <div class="kt-container-fixed w-full py-4 px-2">
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
        <template #title>
          <h1 class="text-lg font-semibold dark:text-white">Serviços</h1>
          <p class="text-sm text-secondary-foreground">Lista de serviços cadastrados</p>
        </template>
        
        <template #actions>
          <div class="flex items-center gap-2">
            <button class="kt-btn kt-btn-primary" @click="onNew">Novo Serviço</button>
          </div>
        </template>
        
        <!-- Filtros -->
        <template #filters>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <!-- Filtro Categoria -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Categoria
              </label>    
              <select
                v-model="filters.category"
                class="kt-input w-full pr-10"
              >
                <option value="">Todas</option>
                <option v-for="cat in categories" :key="cat.value" :value="cat.value">
                  {{ cat.label }}
                </option>
              </select>
            </div>

            <!-- Botão Limpar Filtros -->
            <div class="flex items-center gap-2">
              <button
                v-if="activeFiltersCount > 0"
                class="kt-btn kt-btn-ghost"
                @click="onClearFilters"
              >
                Limpar Filtros
              </button>
              <span
                v-if="activeFiltersCount > 0"
                class="inline-flex items-center justify-center px-2 py-1 text-xs font-semibold rounded bg-primary/20 text-primary"
              >
                {{ activeFiltersCount }}
              </span>
            </div>
          </div>
        </template>

        <!-- Custom cells -->
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
import { fetchServices, createService, updateService, deleteService } from '../../../services/serviceService';
import DrawerServico from '../../../Shared/Components/DrawerServico.vue';
import ConfirmModal from '../../../Shared/Components/ConfirmModal.vue';
import { useToast } from '../../../Shared/composables/useToast.js';
import { useServiceFilters } from '../../../Composables/useServiceFilters.js';
import { serviceCategories, getCategoryLabel } from '../../../Data/serviceCategories.js';

const toast = useToast();

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

// Filtros
const { filters, saveToStorage, loadFromStorage, clearFilters, activeFiltersCount } = useServiceFilters();
const categories = serviceCategories;

const breadcrumbs = [{ label: 'Serviços' }];

/**
 * Formata número como moeda brasileira
 */
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

// Watchers para salvar filtros e recarregar
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
  // Create or update
  const result = drawerEdit.value
    ? await updateService(drawerService.value.id, data)
    : await createService(data);

  if (!result.success) {
    // Error toast
    toast.error('Erro ao ' + (drawerEdit.value ? 'atualizar' : 'criar') + ' serviço: ' + (result.error.response?.data?.message || result.error.message));
    return;
  }

  // Success toast
  toast.success('Serviço ' + (drawerEdit.value ? 'atualizado' : 'criado') + ' com sucesso!');

  // Reload grid
  load();

  // Close drawer
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

// Columns definition
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
  .kt-container-fixed { max-width: 1100px; }
}
@media (max-width: 640px) {
  html, body {
    font-size: 1.1rem !important;
    zoom: 1 !important;
  }
}
</style>
