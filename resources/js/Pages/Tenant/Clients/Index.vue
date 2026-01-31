<template>
  <TenantLayout title="Clientes" :breadcrumbs="breadcrumbs">
    <div class="kt-container-fixed py-6">
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
          <h1 class="text-lg font-semibold dark:text-white">Clientes</h1>
          <p class="text-sm text-secondary-foreground">Lista de clientes cadastrados</p>
        </template>
        <template #actions>
          <div class="flex items-center gap-2">
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
</template>

<script setup>
import { ref, onMounted } from 'vue';
import DataGrid from '../../../Shared/Components/DataGrid.vue';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { fetchClientsMock } from '../../../mock/clients';

const page = ref(1);
const perPage = ref(6);
const total = ref(0);
const items = ref([]);
const search = ref('');
const sortKey = ref(null);
const sortDir = ref('asc');

const breadcrumbs = [ { label: 'Clientes' } ];

const load = async () => {
  const res = await fetchClientsMock({ page: page.value, perPage: perPage.value, search: search.value, sortKey: sortKey.value, sortDir: sortDir.value });
  items.value = res.items;
  total.value = res.total;
};

onMounted(load);

function onPage(p) { page.value = p; load(); }
function onSearch(q) { search.value = q; page.value = 1; load(); }
function onSort({ key, dir }) { sortKey.value = key; sortDir.value = dir; page.value = 1; load(); }

function onNew() { alert('Abrir formulário novo cliente (não implementado)'); }

function onEdit(id) { alert('Editar cliente ' + id + ' (front-only)'); }
function onDelete(id) {
  if (!confirm('Confirma exclusão do cliente #' + id + '?')) return;
  // For mock: remove from list by calling a simple filter on mock data source (not persisted)
  // Use the exposed function (not exported) - fallback: reload and simulate deletion by removing locally
  items.value = items.value.filter(i => i.id !== id);
  total.value = Math.max(0, total.value - 1);
}

// Columns definition: last column uses isHtml + slotHtml to render action buttons with onclick -> global handlers
const columns = [
  { key: 'name', label: 'Nome', sortable: true },
  { key: 'email', label: 'Email', sortable: true },
  { key: 'city', label: 'Cidade', sortable: true },
  { key: 'state', label: 'Estado', sortable: true },
  { key: 'actions', label: 'Ações', sortable: false }
];

</script>

<style scoped>
.kt-container-fixed { max-width: 1100px; margin: 0 auto; }
</style>