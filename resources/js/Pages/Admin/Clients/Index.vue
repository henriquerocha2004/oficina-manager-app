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
    @update-tenant="onTenantUpdate"
    @create-tenant="onTenantCreate"
  />
  <ConfirmModal ref="confirmModal" />
</template>

<script setup>
import { onMounted, ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataGrid from '@/Shared/Components/DataGrid.vue';
import ConfirmModal from '@/Shared/Components/ConfirmModal.vue';
import DrawerClientAdmin from '@/Shared/Components/DrawerClientAdmin.vue';
import { fetchClients, createClient, updateClient, deleteClient, findClient } from '@/services/admin/clientService.js';
import { createTenant, updateTenant } from '@/services/admin/tenantService.js';
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

const normalizeCreateStatus = (payload) => {
  if (payload.tenant_is_trial) {
    return 'trial';
  }

  return payload.tenant_is_active ? 'active' : 'inactive';
};

const buildCreatePayload = (payload) => ({
  name: payload.name,
  email: payload.email,
  document: unmask(payload.document),
  phone: unmask(payload.phone),
  zip_code: unmask(payload.zip_code),
  street: payload.street,
  city: payload.city,
  state: payload.state,
  domain: payload.domain,
  status: normalizeCreateStatus(payload),
  trial_until: payload.tenant_is_trial ? payload.trial_until : null,
});

const buildUpdatePayload = (payload) => ({
  name: payload.name,
  email: payload.email,
  document: unmask(payload.document),
  phone: unmask(payload.phone),
  zip_code: unmask(payload.zip_code),
  street: payload.street,
  city: payload.city,
  state: payload.state,
});

const buildTenantPayload = (payload) => ({
  ...payload,
  document: unmask(payload.document),
  trial_until: payload.status === 'trial' ? payload.trial_until : null,
});

const load = async () => {
  const response = await fetchClients({
    page: page.value,
    perPage: perPage.value,
    search: search.value,
    sortKey: sortKey.value,
    sortDir: sortDir.value,
  });

  items.value = response.items;
  total.value = response.total;
};

const refreshSelectedClient = async (clientId) => {
  const response = await findClient(clientId);

  if (!response.success) {
    toast.error('Erro ao carregar cliente: ' + (response.error.response?.data?.message || response.error.message));
    return false;
  }

  drawerClient.value = response.data;
  return true;
};

onMounted(load);

function onPage(newPage) { page.value = newPage; load(); }
function onSearch(query) { search.value = query; page.value = 1; load(); }
function onSort({ key, dir }) { sortKey.value = key; sortDir.value = dir; page.value = 1; load(); }

function onNew() {
  drawerEdit.value = false;
  drawerClient.value = null;
  drawerOpen.value = true;
}

async function onEdit(row) {
  drawerEdit.value = true;
  drawerClient.value = { ...row, tenants: [] };
  drawerOpen.value = true;
  await refreshSelectedClient(row.id);
}

async function onDrawerSubmit(data) {
  const result = drawerEdit.value
    ? await updateClient(drawerClient.value.id, buildUpdatePayload(data))
    : await createClient(buildCreatePayload(data));

  if (!result.success) {
    toast.error('Erro ao ' + (drawerEdit.value ? 'atualizar' : 'criar') + ' cliente: ' + (result.error.response?.data?.message || result.error.message));
    return;
  }

  toast.success('Cliente ' + (drawerEdit.value ? 'atualizado' : 'criado') + ' com sucesso!');
  drawerOpen.value = false;
  await load();
}

async function onTenantCreate(data) {
  const result = await createTenant(buildTenantPayload(data));

  if (!result.success) {
    toast.error('Erro ao criar tenant: ' + (result.error.response?.data?.message || result.error.message));
    return;
  }

  toast.success('Tenant criado com sucesso!');
  await load();

  if (drawerClient.value?.id) {
    await refreshSelectedClient(drawerClient.value.id);
  }
}

async function onTenantUpdate(data) {
  const result = await updateTenant(data.id, buildTenantPayload(data));

  if (!result.success) {
    toast.error('Erro ao atualizar tenant: ' + (result.error.response?.data?.message || result.error.message));

    if (drawerClient.value?.id) {
      await refreshSelectedClient(drawerClient.value.id);
    }

    return;
  }

  toast.success('Tenant atualizado com sucesso!');
  await load();

  if (drawerClient.value?.id) {
    await refreshSelectedClient(drawerClient.value.id);
  }
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
