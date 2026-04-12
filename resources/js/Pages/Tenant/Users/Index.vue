<template>
  <TenantLayout title="Usuarios" :breadcrumbs="breadcrumbs">
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
          <div class="flex items-center gap-2">
            <button class="kt-btn kt-btn-primary" @click="onNew">Novo Usuario</button>
          </div>
        </template>

        <template #cell-avatar="{ row }">
          <div class="flex items-center">
            <img
              :src="row.avatar || blankAvatar"
              :alt="row.name"
              class="w-10 h-10 rounded-full object-cover border border-border"
            />
          </div>
        </template>

        <template #cell-is_active="{ row }">
          <span
            :class="row.is_active
              ? 'px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
              : 'px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'"
          >
            {{ row.is_active ? 'Ativo' : 'Inativo' }}
          </span>
        </template>

        <template #cell-role="{ row }">
          <span :class="getUserRoleBadgeClass(row.role)">
            {{ getUserRoleLabel(row.role) }}
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

  <DrawerUsuario
    :open="drawerOpen"
    :isEdit="drawerEdit"
    :user="drawerUser"
    @close="onDrawerClose"
    @submit="onDrawerSubmit"
  />

  <ConfirmModal ref="confirmModal" />
</template>

<script setup>
import { ref, onMounted } from 'vue';
import DataGrid from '../../../Shared/Components/DataGrid.vue';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import DrawerUsuario from '@/Shared/Components/DrawerUsuario.vue';
import ConfirmModal from '@/Shared/Components/ConfirmModal.vue';
import { fetchUsers, createUser, updateUser, deleteUser } from '@/services/userService.js';
import { useToast } from '@/Shared/composables/useToast.js';
import blankAvatar from '@assets/media/avatars/blank.png';
import { getUserRoleBadgeClass, getUserRoleLabel } from '@/Data/userRoles.js';

const toast = useToast();

const page = ref(1);
const perPage = ref(10);
const total = ref(0);
const items = ref([]);
const search = ref('');
const sortKey = ref(null);
const sortDir = ref('asc');

const drawerOpen = ref(false);
const drawerEdit = ref(false);
const drawerUser = ref(null);
const confirmModal = ref(null);

const breadcrumbs = [{ label: 'Usuarios' }];

const columns = [
  { key: 'avatar', label: 'Foto', sortable: false },
  { key: 'name', label: 'Nome', sortable: true },
  { key: 'email', label: 'Email', sortable: true },
  { key: 'role', label: 'Perfil', sortable: true },
  { key: 'is_active', label: 'Status', sortable: true },
  { key: 'actions', label: 'Acoes', sortable: false },
];

const load = async () => {
  const res = await fetchUsers({
    page: page.value,
    perPage: perPage.value,
    search: search.value,
    sortKey: sortKey.value,
    sortDir: sortDir.value,
  });

  items.value = res.items;
  total.value = res.total;
};

onMounted(() => {
  load();
});

function onPage(newPage) {
  page.value = newPage;
  load();
}

function onSearch(query) {
  search.value = query;
  page.value = 1;
  load();
}

function onSort({ key, dir }) {
  sortKey.value = key;
  sortDir.value = dir;
  page.value = 1;
  load();
}

function onNew() {
  drawerEdit.value = false;
  drawerUser.value = null;
  drawerOpen.value = true;
}

function onEdit(id) {
  const user = items.value.find((item) => item.id === id);

  if (user) {
    drawerEdit.value = true;
    drawerUser.value = { ...user };
    drawerOpen.value = true;
  }
}

function onDrawerClose() {
  drawerOpen.value = false;
}

async function onDrawerSubmit(data) {
  const result = drawerEdit.value
    ? await updateUser(drawerUser.value.id, data)
    : await createUser(data);

  if (!result.success) {
    toast.error('Erro ao salvar usuario: ' + (result.error.response?.data?.message || result.error.message));
    return;
  }

  toast.success('Usuario salvo com sucesso.');
  drawerOpen.value = false;
  await load();
}

function onDelete(id) {
  confirmModal.value.open({
    title: 'Deletar Usuario',
    message: 'Tem certeza que deseja deletar este usuario?',
  }).then(async (confirmed) => {
    if (!confirmed) {
      return;
    }

    const result = await deleteUser(id);

    if (!result.success) {
      toast.error('Erro ao deletar usuario: ' + (result.error.response?.data?.message || result.error.message));
      return;
    }

    toast.success('Usuario deletado com sucesso.');
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
</style>
