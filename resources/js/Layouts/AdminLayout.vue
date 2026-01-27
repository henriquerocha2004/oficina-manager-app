<template>
  <BaseAuthenticatedLayout :sidebar-open="sidebarOpen">
    <!-- Mobile Header -->
    <template #mobile-header>
      <AdminMobileHeader
        brand-name="Admin Panel"
        @toggle-sidebar="toggleSidebar"
      />
    </template>

    <!-- Sidebar -->
    <template #sidebar>
      <Sidebar :is-sidebar-open="isSidebarOpen">
        <SidebarHeader
          brand-name="Admin Panel"
          :collapsed="!isSidebarOpen"
          :show-search="true"
          :show-add-new="false"
          @open-search="openSearchModal"
        />
        
        <SidebarMenu
          :menu-items="adminMenu"
          :collapsed="!isSidebarOpen"
          @toggle-accordion="toggleAccordion"
        />
        
        <SidebarFooter
          :collapsed="!isSidebarOpen"
          profile-route="admin.settings.profile"
          settings-route="admin.settings.general"
          logout-route="admin.logout"
          :notifications-count="notificationsCount"
          @open-notifications="openNotificationsDrawer"
        />
      </Sidebar>
    </template>

    <!-- Toolbar -->
    <template #toolbar>
      <AdminToolbar
        :title="title"
        :breadcrumbs="breadcrumbs"
      >
        <template #actions>
          <slot name="toolbar-actions" />
        </template>
      </AdminToolbar>
    </template>

    <!-- Main Content (slot padrão) -->
    <slot />

    <!-- Footer -->
    <template #footer>
      <AdminFooter />
    </template>

    <!-- Notifications Drawer -->
    <template #notifications-drawer>
      <NotificationsDrawer
        :is-open="isNotificationsDrawerOpen"
        @close="isNotificationsDrawerOpen = false"
      />
    </template>

    <!-- Search Modal -->
    <template #search-modal>
      <SearchModal
        :is-open="isSearchModalOpen"
        @close="isSearchModalOpen = false"
      />
    </template>
  </BaseAuthenticatedLayout>
</template>

<script setup>
import { onMounted, onUnmounted, computed, ref, provide } from 'vue';
// Forçar update do layout ao voltar para desktop
onMounted(() => {
  window.addEventListener('resize', handleDesktopResize);
});

onUnmounted(() => {
  window.removeEventListener('resize', handleDesktopResize);
});

function handleDesktopResize() {
  if (window.innerWidth >= 1024 && !isSidebarOpen.value) {
    isSidebarOpen.value = true;
  }
}
import BaseAuthenticatedLayout from './BaseAuthenticatedLayout.vue';
import Sidebar from '@/Components/Shared/Navigation/Sidebar.vue';
import SidebarHeader from '@/Components/Shared/Navigation/SidebarHeader.vue';
import SidebarMenu from '@/Components/Shared/Navigation/SidebarMenu.vue';
import SidebarFooter from '@/Components/Shared/Navigation/SidebarFooter.vue';
import AdminMobileHeader from '@/Components/Admin/AdminMobileHeader.vue';
import AdminToolbar from '@/Components/Admin/AdminToolbar.vue';
import AdminFooter from '@/Components/Admin/AdminFooter.vue';
import NotificationsDrawer from '@/Components/Shared/NotificationsDrawer.vue';
import SearchModal from '@/Components/Shared/SearchModal.vue';
import { adminMenu } from '@/config/adminMenu.js';
import { useSidebar } from '@/Composables/useSidebar.js';

const props = defineProps({
  title: {
    type: String,
    default: 'Dashboard',
  },
  breadcrumbs: {
    type: Array,
    default: () => [],
  },
});

// Estado da sidebar
const {
  isSidebarOpen,
  toggleSidebar,
  toggleAccordion,
  isAccordionOpen,
  closeSidebarOnMobile,
} = useSidebar('admin');

// Computed para garantir reatividade ao layout
const sidebarOpen = computed(() => isSidebarOpen.value);

// Prover função isAccordionOpen para componentes filhos
provide('isAccordionOpen', isAccordionOpen);

// Notificações
const notificationsCount = ref(0);
const isNotificationsDrawerOpen = ref(false);
const isSearchModalOpen = ref(false);

function openNotificationsDrawer() {
  isNotificationsDrawerOpen.value = true;
}

function openSearchModal() {
  isSearchModalOpen.value = true;
}
</script>
