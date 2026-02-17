<template>
  <TenantLayout title="Clientes" :breadcrumbs="breadcrumbs">
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
              <label class="filter-label">Tipo</label>
              <select
                v-model="filters.type"
                class="kt-select"
                @change="applyFilters"
              >
                <option value="all">Todos</option>
                <option value="pf">Pessoa Física</option>
                <option value="pj">Pessoa Jurídica</option>
              </select>
            </div>
          </FilterDropdown>
        </template>

        <template #actions>
          <div class="flex items-center gap-2">
            <ExportButton @export="handleExport" />
            <button class="kt-btn kt-btn-primary" @click="onNew">Novo Cliente</button>
          </div>
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
  <DrawerCliente
    :open="drawerOpen"
    :isEdit="drawerEdit"
    :client="drawerClient"
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
import { fetchClients, createClient, updateClient, deleteClient } from '../../../services/clientService';
import DrawerCliente from '../../../Shared/Components/DrawerCliente.vue';
import ConfirmModal from '../../../Shared/Components/ConfirmModal.vue';
import FilterDropdown from '@/Shared/Components/FilterDropdown.vue';
import { useMasks } from '@/Composables/useMasks.js';
import { useToast } from '@/Shared/composables/useToast.js';
import { useStats } from '@/Composables/useStats.js';
import { useExportCSV } from '@/Composables/useExportCSV.js';
import { useClientFilters } from '@/Composables/useClientFilters.js';
import { brazilianStates } from '@/Data/brazilianStates.js';

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
const drawerClient = ref(null);
const confirmModal = ref(null);

const breadcrumbs = [ { label: 'Clientes' } ];

const { unmask } = useMasks();
const { exportToCSV } = useExportCSV();
const { filters, saveToStorage, loadFromStorage, clearFilters, activeFiltersCount } = useClientFilters();
const { stats } = useStats(items, 'clients');

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

    if (filters.type && filters.type !== 'all') {
        if (filters.type === 'pf') {
            result = result.filter(item => item.document_number && item.document_number.length === 11);
        }

        if (filters.type === 'pj') {
            result = result.filter(item => item.document_number && item.document_number.length === 14);
        }
    }

    return result;
});

const filteredTotal = computed(() => filteredItems.value.length);

const load = async () => {
  const res = await fetchClients({
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
    if (saved.type) filters.type = saved.type;
    load();
});

watch(() => filters.state, () => saveToStorage());
watch(() => filters.city, () => saveToStorage());
watch(() => filters.type, () => saveToStorage());

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
        { key: 'email', label: 'Email' },
        { key: 'phone', label: 'Telefone' },
        { key: 'document_number', label: 'CPF/CNPJ' },
        { key: 'city', label: 'Cidade' },
        { key: 'state', label: 'Estado' },
        { key: 'zip_code', label: 'CEP' },
    ];

    exportToCSV(filteredItems.value, columns, 'clientes');
}

function onPage(p) { page.value = p; load(); }
function onSearch(q) { search.value = q; page.value = 1; load(); }
function onSort({ key, dir }) { sortKey.value = key; sortDir.value = dir; page.value = 1; load(); }

function onNew() {
  drawerEdit.value = false;
  drawerClient.value = null;
  drawerOpen.value = true;
}

function onEdit(id) {
  const client = items.value.find(i => i.id === id);
  if (client) {
    drawerEdit.value = true;
    drawerClient.value = { ...client };
    drawerOpen.value = true;
  }
}

function onDrawerClose() {
  drawerOpen.value = false;
}

async function onDrawerSubmit(data) {
  // Unmask the data
  const unmaskedData = {
    ...data,
    document: unmask(data.document),
    phone: unmask(data.phone),
    zip_code: unmask(data.zip_code),
  };
  // Create or update
  const result = drawerEdit.value
    ? await updateClient(drawerClient.value.id, unmaskedData)
    : await createClient(unmaskedData);
  if (!result.success) {
    toast.error('Erro ao ' + (drawerEdit.value ? 'atualizar' : 'criar') + ' cliente: ' + (result.error.response?.data?.message || result.error.message));
    return;
  }
  toast.success('Cliente ' + (drawerEdit.value ? 'atualizado' : 'criado') + ' com sucesso!');
  await load();
  // Close drawer
  drawerOpen.value = false;
}

function onDelete(id) {
  confirmModal.value.open({
    title: 'Deletar Cliente',
    message: 'Tem certeza que deseja deletar este cliente?'
  }).then(async (confirmed) => {
    if (confirmed) {
      const result = await deleteClient(id);
      if (!result.success) {
        toast.error('Erro ao deletar cliente: ' + (result.error.response?.data?.message || result.error.message));
        return;
      }
      toast.success('Cliente deletado com sucesso!');
      await load();
    }
  });
}

const columns = [
  { key: 'name', label: 'Nome', sortable: true },
  { key: 'email', label: 'Email', sortable: true },
  { key: 'city', label: 'Cidade', sortable: true },
  { key: 'state', label: 'Estado', sortable: true },
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

/* Dark mode */
.dark .filter-label {
    color: #cbd5e1;
}
</style>
