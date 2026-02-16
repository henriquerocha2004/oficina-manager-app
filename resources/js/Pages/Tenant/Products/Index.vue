<template>
  <TenantLayout title="Produtos" :breadcrumbs="breadcrumbs">
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
          <h1 class="text-lg font-semibold dark:text-white">Produtos</h1>
          <p class="text-sm text-secondary-foreground">Lista de produtos cadastrados</p>
        </template>
        <template #actions>
          <div class="flex items-center gap-2">
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
import { ref, onMounted } from 'vue';
import DataGrid from '../../../Shared/Components/DataGrid.vue';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { fetchProducts, createProduct, updateProduct, deleteProduct, fetchProduct } from '@/services/productService.js';
import DrawerProduto from '../../../Shared/Components/DrawerProduto.vue';
import ConfirmModal from '../../../Shared/Components/ConfirmModal.vue';
import { useMasks } from '@/Composables/useMasks.js';
import { useToast } from '@/Shared/composables/useToast.js';
import { getCategoryLabel, getUnitLabel } from '@/Data/productData';

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

const breadcrumbs = [ { label: 'Produtos' } ];

const { maskCurrency } = useMasks();

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

onMounted(load);

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
  // Busca o produto completo com fornecedores
  const result = await fetchProduct(id);
  if (result.success && result.data && result.data.data) {
    drawerEdit.value = true;
    drawerProduct.value = result.data.data.product;
    drawerOpen.value = true;
  } else {
    toast.error('Erro ao buscar produto: ' + (result.error?.response?.data?.message || result.error?.message || 'Erro desconhecido'));
  }
}

function onDrawerClose() {
  drawerOpen.value = false;
}

async function onDrawerSubmit(data) {
  // Create or update
  const result = drawerEdit.value
    ? await updateProduct(drawerProduct.value.id, data)
    : await createProduct(data);

  if (!result.success) {
    // Error toast
    toast.error('Erro ao ' + (drawerEdit.value ? 'atualizar' : 'criar') + ' produto: ' + (result.error.response?.data?.message || result.error.message));
    return;
  }

  // Success toast
  toast.success('Produto ' + (drawerEdit.value ? 'atualizado' : 'criado') + ' com sucesso!');

  // Reload grid
  await load();

  // Close drawer
  drawerOpen.value = false;
}

async function onSupplierUpdated() {
  // Recarrega o produto atual para atualizar a lista de fornecedores
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

// Columns definition
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
