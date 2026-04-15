<template>
  <AdminLayout title="Tenants" :breadcrumbs="breadcrumbs">
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
        <template #filters>
          <div class="flex items-center gap-2">
            <select v-model="statusFilter" class="kt-select" @change="onStatusFilter">
              <option value="">Todos os status</option>
              <option value="active">Ativo</option>
              <option value="inactive">Inativo</option>
              <option value="trial">Trial</option>
            </select>
          </div>
        </template>

        <template #actions>
          <button class="kt-btn kt-btn-primary" @click="onNew">Novo Tenant</button>
        </template>

        <template #cell-status="{ row }">
          <span :class="statusClass(row.status)">
            {{ statusLabel(row.status) }}
          </span>
        </template>

        <template #cell-client="{ row }">
          {{ row.client?.name ?? '—' }}
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

  <DrawerTenantAdmin
    :open="drawerOpen"
    :isEdit="drawerEdit"
    :tenant="drawerTenant"
    :clients="clientsList"
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
import DrawerTenantAdmin from '@/Shared/Components/DrawerTenantAdmin.vue';
import { fetchTenants, createTenant, updateTenant, deleteTenant } from '@/services/admin/tenantService.js';
import { fetchClients } from '@/services/admin/clientService.js';
import { useToast } from '@/Shared/composables/useToast.js';

const toast = useToast();

const breadcrumbs = [{ label: 'Tenants' }];

const page = ref(1);
const perPage = ref(10);
const total = ref(0);
const items = ref([]);
const search = ref('');
const sortKey = ref(null);
const sortDir = ref('asc');
const statusFilter = ref('');
const drawerOpen = ref(false);
const drawerEdit = ref(false);
const drawerTenant = ref(null);
const clientsList = ref([]);
const confirmModal = ref(null);

const columns = [
  { key: 'name', label: 'Nome', sortable: true },
  { key: 'domain', label: 'Domínio', sortable: true },
  { key: 'email', label: 'Email', sortable: false },
  { key: 'status', label: 'Status', sortable: true },
  { key: 'client', label: 'Cliente', sortable: false },
  { key: 'actions', label: 'Ações', sortable: false },
];

const load = async () => {
  const res = await fetchTenants({
    page: page.value,
    perPage: perPage.value,
    search: search.value,
    sortKey: sortKey.value,
    sortDir: sortDir.value,
    filters: { status: statusFilter.value },
  });
  items.value = res.items;
  total.value = res.total;
};

const loadClients = async () => {
  const res = await fetchClients({ perPage: 100 });
  clientsList.value = res.items;
};

onMounted(() => {
  load();
  loadClients();
});

function onPage(p) { page.value = p; load(); }
function onSearch(q) { search.value = q; page.value = 1; load(); }
function onSort({ key, dir }) { sortKey.value = key; sortDir.value = dir; page.value = 1; load(); }
function onStatusFilter() { page.value = 1; load(); }

function statusLabel(status) {
  const labels = { active: 'Ativo', inactive: 'Inativo', trial: 'Trial' };
  return labels[status] ?? status;
}

function statusClass(status) {
  const classes = {
    active: 'kt-badge kt-badge-success',
    inactive: 'kt-badge kt-badge-danger',
    trial: 'kt-badge kt-badge-warning',
  };
  return classes[status] ?? 'kt-badge';
}

function onNew() {
  drawerEdit.value = false;
  drawerTenant.value = null;
  drawerOpen.value = true;
}

function onEdit(row) {
  drawerEdit.value = true;
  drawerTenant.value = { ...row };
  drawerOpen.value = true;
}

async function onDrawerSubmit(data) {
  const result = drawerEdit.value
    ? await updateTenant(drawerTenant.value.id, data)
    : await createTenant(data);

  if (!result.success) {
    toast.error('Erro ao ' + (drawerEdit.value ? 'atualizar' : 'criar') + ' tenant: ' + (result.error.response?.data?.message || result.error.message));
    return;
  }

  toast.success('Tenant ' + (drawerEdit.value ? 'atualizado' : 'criado') + ' com sucesso!');
  drawerOpen.value = false;
  await load();
}

function onDelete(id) {
  confirmModal.value.open({
    title: 'Deletar Tenant',
    message: 'Tem certeza que deseja deletar este tenant?',
  }).then(async (confirmed) => {
    if (!confirmed) return;
    const result = await deleteTenant(id);
    if (!result.success) {
      toast.error('Erro ao deletar tenant: ' + (result.error.response?.data?.message || result.error.message));
      return;
    }
    toast.success('Tenant deletado com sucesso!');
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
