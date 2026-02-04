<template>
  <TenantLayout title="Clientes" :breadcrumbs="breadcrumbs">
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
import { ref, onMounted } from 'vue';
import DataGrid from '../../../Shared/Components/DataGrid.vue';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { fetchClients, createClient, updateClient, deleteClient } from '../../../services/clientService';
import DrawerCliente from '../../../Shared/Components/DrawerCliente.vue';
import ConfirmModal from '../../../Shared/Components/ConfirmModal.vue';
import { useMasks } from '../../../Composables/useMasks.js';
import { useToast } from '../../../Shared/composables/useToast.js';

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

// Removido acesso global

const breadcrumbs = [ { label: 'Clientes' } ];

const { unmask } = useMasks();

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

onMounted(load);

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
    // Error toast
    toast.error('Erro ao ' + (drawerEdit.value ? 'atualizar' : 'criar') + ' cliente: ' + (result.error.response?.data?.message || result.error.message));
    return;
  }
  // Success toast
  toast.success('Cliente ' + (drawerEdit.value ? 'atualizado' : 'criado') + ' com sucesso!');
  // Reload grid
  load();
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
      load();
    }
  });
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