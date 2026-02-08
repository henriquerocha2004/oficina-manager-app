<template>
  <TenantLayout title="Fornecedores" :breadcrumbs="breadcrumbs">
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
          <h1 class="text-lg font-semibold dark:text-white">Fornecedores</h1>
          <p class="text-sm text-secondary-foreground">Lista de fornecedores cadastrados</p>
        </template>
        <template #actions>
          <div class="flex items-center gap-2">
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
import { ref, onMounted } from 'vue';
import DataGrid from '../../../Shared/Components/DataGrid.vue';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { fetchSuppliers, createSupplier, updateSupplier, deleteSupplier } from '../../../services/supplierService';
import DrawerFornecedor from '../../../Shared/Components/DrawerFornecedor.vue';
import ConfirmModal from '../../../Shared/Components/ConfirmModal.vue';
import { useToast } from '../../../Shared/composables/useToast.js';
import { useMasks } from '../../../Composables/useMasks.js';

const toast = useToast();
const { maskDocument } = useMasks();

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

onMounted(load);

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
      load();
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
