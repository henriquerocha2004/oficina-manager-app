<template>
  <TenantLayout title="Produtos" :breadcrumbs="breadcrumbs">
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
        :items="filteredItems"
        :total="filteredTotal"
        :page="page"
        :perPage="perPage"
        @update:page="onPage"
        @sort="onSort"
        @search="onSearch"
      >
        <template #filters>
          <FilterDropdown
            :activeCount="activeFiltersCount"
            @clear="clearAllFilters"
          >
            <div class="filter-item">
              <label class="filter-label">Categoria</label>
              <select
                v-model="filters.category"
                class="kt-select"
                @change="applyFilters"
              >
                <option value="">Todas</option>
                <option
                  v-for="cat in productCategories"
                  :key="cat.value"
                  :value="cat.value"
                >
                  {{ cat.label }}
                </option>
              </select>
            </div>

            <div class="filter-item">
              <label class="filter-label">Status</label>
              <select
                v-model="filters.status"
                class="kt-select"
                @change="applyFilters"
              >
                <option value="all">Todos</option>
                <option value="active">Ativos</option>
                <option value="inactive">Inativos</option>
              </select>
            </div>

            <div class="filter-item">
              <label class="filter-label">Preço Mín</label>
              <input
                v-model.number="filters.priceMin"
                type="number"
                placeholder="0,00"
                class="kt-input"
                min="0"
                step="0.01"
                @input="applyFilters"
              />
            </div>

            <div class="filter-item">
              <label class="filter-label">Preço Máx</label>
              <input
                v-model.number="filters.priceMax"
                type="number"
                placeholder="9999,99"
                class="kt-input"
                min="0"
                step="0.01"
                @input="applyFilters"
              />
            </div>
          </FilterDropdown>
        </template>

        <template #actions>
          <div class="flex items-center gap-2">
            <ExportButton @export="handleExport" />
            <button class="kt-btn kt-btn-primary" @click="onNew">Novo Produto</button>
          </div>
        </template>
        <template #cell-category="{ row }">
          {{ getCategoryLabel(row.category) }}
        </template>
        <template #cell-unit="{ row }">
          {{ getUnitLabel(row.unit) }}
        </template>
        <template #cell-unit_price="{ row }">
          {{ formatCurrency(row.unit_price) }}
        </template>
        <template #cell-is_active="{ row }">
          <span
            :class="[
              'px-2 py-1 text-xs rounded-full',
              row.is_active
                ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
            ]"
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
  <DrawerProduto
    :open="drawerOpen"
    :isEdit="drawerEdit"
    :product="drawerProduct"
    @close="onDrawerClose"
    @submit="onDrawerSubmit"
    @supplier-updated="onSupplierUpdated"
  />
  <ConfirmModal ref="confirmModal" />
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import DataGrid from '../../../Shared/Components/DataGrid.vue';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import StatsContainer from '@/Shared/Components/StatsContainer.vue';
import StatsCard from '@/Shared/Components/StatsCard.vue';
import FilterDropdown from '@/Shared/Components/FilterDropdown.vue';
import ExportButton from '@/Shared/Components/ExportButton.vue';
import { fetchProducts, fetchProductStats, createProduct, updateProduct, deleteProduct, fetchProduct } from '@/services/productService.js';
import DrawerProduto from '../../../Shared/Components/DrawerProduto.vue';
import ConfirmModal from '../../../Shared/Components/ConfirmModal.vue';
import { useMasks } from '@/Composables/useMasks.js';
import { useToast } from '@/Shared/composables/useToast.js';
import { useStats } from '@/Composables/useStats.js';
import { useExportCSV } from '@/Composables/useExportCSV.js';
import { useProductFilters } from '@/Composables/useProductFilters.js';
import { getCategoryLabel, getUnitLabel, productCategories } from '@/Data/productData';

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
const drawerProduct = ref(null);
const confirmModal = ref(null);
const statsFromApi = ref(null);

const breadcrumbs = [ { label: 'Produtos' } ];

const { maskCurrency } = useMasks();
const { exportToCSV } = useExportCSV();
const { filters, saveToStorage, loadFromStorage, clearFilters, activeFiltersCount } = useProductFilters();
const { stats } = useStats(items, 'products', statsFromApi);
const filteredItems = computed(() => {
    let result = items.value;
    if (filters.category) {
        result = result.filter(item => item.category === filters.category);
    }

    if (filters.status && filters.status !== 'all') {
        const isActive = filters.status === 'active';
        result = result.filter(item => item.is_active === isActive);
    }

    if (filters.priceMin) {
        result = result.filter(item => parseFloat(item.unit_price) >= parseFloat(filters.priceMin));
    }

    if (filters.priceMax) {
        result = result.filter(item => parseFloat(item.unit_price) <= parseFloat(filters.priceMax));
    }

    return result;
});

const filteredTotal = computed(() => filteredItems.value.length);

const load = async () => {
  const res = await fetchProducts({
    page: page.value,
    perPage: perPage.value,
    search: search.value,
    sortKey: sortKey.value,
    sortDir: sortDir.value
  });
  items.value = res.items;
  total.value = res.total;
};

const loadStats = async () => {
    const result = await fetchProductStats();
    if (result.success) {
        statsFromApi.value = result.data;
    }
};

onMounted(() => {
    const saved = loadFromStorage();
    if (saved.category) filters.category = saved.category;
    if (saved.status) filters.status = saved.status;
    if (saved.priceMin) filters.priceMin = saved.priceMin;
    if (saved.priceMax) filters.priceMax = saved.priceMax;
    load();
    loadStats();
});

watch(() => filters.category, () => saveToStorage());
watch(() => filters.status, () => saveToStorage());
watch(() => filters.priceMin, () => saveToStorage());
watch(() => filters.priceMax, () => saveToStorage());

function applyFilters() {
    page.value = 1;
}

function clearAllFilters() {
    clearFilters();
    page.value = 1;
}

function handleExport() {
    const columns = [
        { key: 'name', label: 'Nome' },
        { key: 'category', label: 'Categoria' },
        { key: 'unit', label: 'Unidade' },
        { key: 'unit_price', label: 'Preço Unitário' },
        { key: 'suggested_price', label: 'Preço Sugerido' },
        { key: 'is_active', label: 'Status' },
    ];

    const dataToExport = filteredItems.value.map(item => ({
        ...item,
        category: getCategoryLabel(item.category),
        unit: getUnitLabel(item.unit),
        unit_price: formatCurrency(item.unit_price),
        suggested_price: item.suggested_price ? formatCurrency(item.suggested_price) : '',
        is_active: item.is_active ? 'Ativo' : 'Inativo',
    }));

    exportToCSV(dataToExport, columns, 'produtos');
}

function onPage(p) { page.value = p; load(); }
function onSearch(q) { search.value = q; page.value = 1; load(); }
function onSort({ key, dir }) { sortKey.value = key; sortDir.value = dir; page.value = 1; load(); }

function formatCurrency(value) {
  if (!value) return 'R$ 0,00';
  return maskCurrency(String(value * 100));
}

function onNew() {
  drawerEdit.value = false;
  drawerProduct.value = null;
  drawerOpen.value = true;
}

async function onEdit(id) {
  const result = await fetchProduct(id);
  if (result.success && result.data && result.data.data) {
    drawerEdit.value = true;
    drawerProduct.value = result.data.data.product;
    drawerOpen.value = true;
    return;
  }

  toast.error('Erro ao buscar produto: ' + (result.error?.response?.data?.message || result.error?.message || 'Erro desconhecido'));
}

function onDrawerClose() {
  drawerOpen.value = false;
}

async function onDrawerSubmit(data) {
  const result = drawerEdit.value
    ? await updateProduct(drawerProduct.value.id, data)
    : await createProduct(data);

  if (!result.success) {
    toast.error('Erro ao ' + (drawerEdit.value ? 'atualizar' : 'criar') + ' produto: ' + (result.error.response?.data?.message || result.error.message));
    return;
  }

  toast.success('Produto ' + (drawerEdit.value ? 'atualizado' : 'criado') + ' com sucesso!');
  await load();

  drawerOpen.value = false;
}

async function onSupplierUpdated() {
  if (drawerProduct.value && drawerProduct.value.id) {
    const result = await fetchProduct(drawerProduct.value.id);
    if (result.success && result.data && result.data.data) {
      drawerProduct.value = result.data.data.product;
    }
  }
}

function onDelete(id) {
  confirmModal.value.open({
    title: 'Deletar Produto',
    message: 'Tem certeza que deseja deletar este produto?'
  }).then(async (confirmed) => {
    if (confirmed) {
      const result = await deleteProduct(id);
      if (!result.success) {
        toast.error('Erro ao deletar produto: ' + (result.error.response?.data?.message || result.error.message));
        return;
      }
      toast.success('Produto deletado com sucesso!');
      await load();
    }
  });
}

const columns = [
  { key: 'name', label: 'Nome', sortable: true },
  { key: 'category', label: 'Categoria', sortable: true },
  { key: 'unit', label: 'Unidade', sortable: false },
  { key: 'unit_price', label: 'Preço Unit.', sortable: true },
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
