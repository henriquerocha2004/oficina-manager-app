<template>
  <AdminLayout title="Clientes" :breadcrumbs="breadcrumbs">
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
        <template #actions>
          <button class="kt-btn kt-btn-primary" @click="onNew">Novo Cliente</button>
        </template>
        <template #cell-actions="{ row }">
          <div class="text-end flex gap-2 justify-end">
            <button class="kt-btn kt-btn-sm kt-btn-ghost" @click="onEdit(row)" title="Editar">
              <i class="ki-filled ki-pencil text-gray-800 dark:text-gray-200"></i>
            </button>
            <button class="kt-btn kt-btn-sm text-white" @click="onDelete(row.id)" title="Deletar">
              <i class="ki-filled ki-trash"></i>
            </button>
          </div>
        </template>
      </DataGrid>
    </div>
  </AdminLayout>

  <DrawerClientAdmin
    :open="drawerOpen"
    :isEdit="drawerEdit"
    :client="drawerClient"
    @close="drawerOpen = false"
    @submit="onDrawerSubmit"
  />
  <ConfirmModal ref="confirmModal" />
</template>

<script setup>
import { ref, onMounted } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataGrid from '@/Shared/Components/DataGrid.vue';
import ConfirmModal from '@/Shared/Components/ConfirmModal.vue';
import DrawerClientAdmin from '@/Shared/Components/DrawerClientAdmin.vue';
import { fetchClients, createClient, updateClient, deleteClient } from '@/services/admin/clientService.js';
import { useToast } from '@/Shared/composables/useToast.js';
import { useMasks } from '@/Composables/useMasks.js';

const toast = useToast();
const { unmask } = useMasks();

const breadcrumbs = [{ label: 'Clientes' }];

const page = ref(1);
const perPage = ref(10);
const total = ref(0);
const items = ref([]);
const search = ref('');
const sortKey = ref(null);
const sortDir = ref('asc');
const drawerOpen = ref(false);
const drawerEdit = ref(false);
const drawerClient = ref(null);
const confirmModal = ref(null);

const columns = [
  { key: 'name', label: 'Nome', sortable: true },
  { key: 'email', label: 'Email', sortable: true },
  { key: 'document', label: 'CNPJ/CPF', sortable: false },
  { key: 'phone', label: 'Telefone', sortable: false },
  { key: 'actions', label: 'Ações', sortable: false },
];

const load = async () => {
  const res = await fetchClients({
    page: page.value,
    perPage: perPage.value,
    search: search.value,
    sortKey: sortKey.value,
    sortDir: sortDir.value,
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

function onEdit(row) {
  drawerEdit.value = true;
  drawerClient.value = { ...row };
  drawerOpen.value = true;
}

async function onDrawerSubmit(data) {
  const payload = {
    ...data,
    document: unmask(data.document),
    phone: unmask(data.phone),
    zip_code: unmask(data.zip_code),
  };

  const result = drawerEdit.value
    ? await updateClient(drawerClient.value.id, payload)
    : await createClient(payload);

  if (!result.success) {
    toast.error('Erro ao ' + (drawerEdit.value ? 'atualizar' : 'criar') + ' cliente: ' + (result.error.response?.data?.message || result.error.message));
    return;
  }

  toast.success('Cliente ' + (drawerEdit.value ? 'atualizado' : 'criado') + ' com sucesso!');
  drawerOpen.value = false;
  await load();
}

function onDelete(id) {
  confirmModal.value.open({
    title: 'Deletar Cliente',
    message: 'Tem certeza que deseja deletar este cliente?',
  }).then(async (confirmed) => {
    if (!confirmed) return;
    const result = await deleteClient(id);
    if (!result.success) {
      toast.error('Erro ao deletar cliente: ' + (result.error.response?.data?.message || result.error.message));
      return;
    }
    toast.success('Cliente deletado com sucesso!');
    await load();
  });
}
</script>

<style>
.kt-container-fixed {
  box-sizing: border-box;
  width: 100%;
  max-width: 100%;
  margin: 0 auto;
}
@media (min-width: 1024px) {
  .kt-container-fixed { max-width: 1400px; }
}
</style>
