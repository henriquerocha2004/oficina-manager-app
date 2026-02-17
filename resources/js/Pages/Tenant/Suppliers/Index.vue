<template>
  <TenantLayout title="Fornecedores" :breadcrumbs="breadcrumbs">
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
              <label class="filter-label">Estado</label>
              <select
                v-model="filters.state"
                class="kt-select"
                @change="applyFilters"
              >
                <option value="">Todos</option>
                <option
                  v-for="state in brazilianStates"
                  :key="state.value"
                  :value="state.value"
                >
                  {{ state.label }}
                </option>
              </select>
            </div>

            <div class="filter-item">
              <label class="filter-label">Cidade</label>
              <input
                v-model="filters.city"
                type="text"
                placeholder="Digite a cidade"
                class="kt-input"
                @input="applyFilters"
              />
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
          </FilterDropdown>
        </template>

        <template #actions>
          <div class="flex items-center gap-2">
            <ExportButton @export="handleExport" />
            <button class="kt-btn kt-btn-primary" @click="onNew">Novo Fornecedor</button>
          </div>
        </template>
        <template #cell-document_number="{ row }">
          {{ formatCNPJ(row.document_number) }}
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
  <DrawerFornecedor
    :open="drawerOpen"
    :isEdit="drawerEdit"
    :supplier="drawerSupplier"
    @close="onDrawerClose"
    @submit="onDrawerSubmit"
  />
  <ConfirmModal ref="confirmModal" />
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import DataGrid from '../../../Shared/Components/DataGrid.vue';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import StatsContainer from '@/Shared/Components/StatsContainer.vue';
import StatsCard from '@/Shared/Components/StatsCard.vue';
import ExportButton from '@/Shared/Components/ExportButton.vue';
import FilterDropdown from '@/Shared/Components/FilterDropdown.vue';
import { fetchSuppliers, createSupplier, updateSupplier, deleteSupplier } from '@/services/supplierService.js';
import DrawerFornecedor from '../../../Shared/Components/DrawerFornecedor.vue';
import ConfirmModal from '../../../Shared/Components/ConfirmModal.vue';
import { useToast } from '@/Shared/composables/useToast.js';
import { useMasks } from '@/Composables/useMasks.js';
import { useStats } from '@/Composables/useStats.js';
import { useExportCSV } from '@/Composables/useExportCSV.js';
import { useSupplierFilters } from '@/Composables/useSupplierFilters.js';
import { brazilianStates } from '@/Data/brazilianStates.js';

const toast = useToast();
const { maskDocument } = useMasks();
const { exportToCSV } = useExportCSV();
const { filters, saveToStorage, loadFromStorage, clearFilters, activeFiltersCount } = useSupplierFilters();

const page = ref(1);
const perPage = ref(6);
const total = ref(0);
const items = ref([]);
const search = ref('');
const sortKey = ref(null);
const sortDir = ref('asc');
const drawerOpen = ref(false);
const drawerEdit = ref(false);
const drawerSupplier = ref(null);
const confirmModal = ref(null);

const breadcrumbs = [ { label: 'Fornecedores' } ];

const { stats } = useStats(items, 'suppliers');

const filteredItems = computed(() => {
    let result = items.value;
    if (filters.state) {
        result = result.filter(item => item.state === filters.state);
    }

    if (filters.city) {
        result = result.filter(item =>
            item.city && item.city.toLowerCase().includes(filters.city.toLowerCase())
        );
    }

    if (filters.status && filters.status !== 'all') {
        const isActive = filters.status === 'active';
        result = result.filter(item => item.is_active === isActive);
    }

    return result;
});

const filteredTotal = computed(() => filteredItems.value.length);

const load = async () => {
  const res = await fetchSuppliers({
    page: page.value,
    perPage: perPage.value,
    search: search.value,
    sortKey: sortKey.value,
    sortDir: sortDir.value
  });
  items.value = res.items;
  total.value = res.total;
};

onMounted(() => {
    const saved = loadFromStorage();
    if (saved.state) filters.state = saved.state;
    if (saved.city) filters.city = saved.city;
    if (saved.status) filters.status = saved.status;
    load();
});

watch(() => filters.state, () => saveToStorage());
watch(() => filters.city, () => saveToStorage());
watch(() => filters.status, () => saveToStorage());

function applyFilters() {
    page.value = 1;
}

function clearAllFilters() {
    clearFilters();
    page.value = 1;
}

function handleExport() {
    const columns = [
        { key: 'name', label: 'Razão Social' },
        { key: 'trade_name', label: 'Nome Fantasia' },
        { key: 'document_number', label: 'CNPJ' },
        { key: 'contact_person', label: 'Contato' },
        { key: 'phone', label: 'Telefone' },
        { key: 'email', label: 'Email' },
        { key: 'city', label: 'Cidade' },
        { key: 'state', label: 'UF' },
        { key: 'is_active', label: 'Status' },
    ];

    const dataToExport = filteredItems.value.map(item => ({
        ...item,
        document_number: formatCNPJ(item.document_number),
        is_active: item.is_active ? 'Ativo' : 'Inativo',
    }));

    exportToCSV(dataToExport, columns, 'fornecedores');
}

function onPage(p) { page.value = p; load(); }
function onSearch(q) { search.value = q; page.value = 1; load(); }
function onSort({ key, dir }) { sortKey.value = key; sortDir.value = dir; page.value = 1; load(); }

function onNew() {
  drawerEdit.value = false;
  drawerSupplier.value = null;
  drawerOpen.value = true;
}

function onEdit(id) {
  const supplier = items.value.find(i => i.id === id);
  if (supplier) {
    drawerEdit.value = true;
    drawerSupplier.value = { ...supplier };
    drawerOpen.value = true;
  }
}

function onDrawerClose() {
  drawerOpen.value = false;
}

async function onDrawerSubmit(data) {
  const result = drawerEdit.value
    ? await updateSupplier(drawerSupplier.value.id, data)
    : await createSupplier(data);
  if (!result.success) {
    toast.error('Erro ao ' + (drawerEdit.value ? 'atualizar' : 'criar') + ' fornecedor: ' + (result.error.response?.data?.message || result.error.message));
    return;
  }
  toast.success('Fornecedor ' + (drawerEdit.value ? 'atualizado' : 'criado') + ' com sucesso!');
  load();
  drawerOpen.value = false;
}

function onDelete(id) {
  confirmModal.value.open({
    title: 'Deletar Fornecedor',
    message: 'Tem certeza que deseja deletar este fornecedor?'
  }).then(async (confirmed) => {
    if (confirmed) {
      const result = await deleteSupplier(id);
      if (!result.success) {
        toast.error('Erro ao deletar fornecedor: ' + (result.error.response?.data?.message || result.error.message));
        return;
      }
      toast.success('Fornecedor deletado com sucesso!');
      await load();
    }
  });
}

function formatCNPJ(cnpj) {
  if (!cnpj) return '';
  return maskDocument(cnpj);
}

const columns = [
  { key: 'name', label: 'Razão Social', sortable: true },
  { key: 'trade_name', label: 'Nome Fantasia', sortable: true },
  { key: 'document_number', label: 'CNPJ', sortable: true },
  { key: 'contact_person', label: 'Contato', sortable: true },
  { key: 'city', label: 'Cidade', sortable: true },
  { key: 'state', label: 'UF', sortable: true },
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
