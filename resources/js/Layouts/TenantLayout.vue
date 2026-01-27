<template>
  <BaseAuthenticatedLayout :sidebar-open="isSidebarOpen">
    <!-- Mobile Header -->
    <template #mobile-header>
      <TenantMobileHeader
        brand-name="Oficina Manager"
        @toggle-sidebar="toggleSidebar"
      />
    </template>

    <!-- Sidebar -->
    <template #sidebar>
      <Sidebar :is-sidebar-open="isSidebarOpen">
        <SidebarHeader
          brand-name="Oficina Manager"
          :collapsed="!isSidebarOpen"
          :show-search="true"
          :show-add-new="true"
          @open-search="openSearchModal"
          @add-new="handleAddNew"
        />
        
        <SidebarMenu
          :menu-items="tenantMenu"
          :collapsed="!isSidebarOpen"
          @toggle-accordion="toggleAccordion"
        />
        
        <SidebarFooter
          :collapsed="!isSidebarOpen"
          profile-route="tenant.settings.account"
          settings-route="tenant.settings.profile"
          logout-route="tenant.logout"
          :notifications-count="notificationsCount"
          @open-notifications="openNotificationsDrawer"
        />
      </Sidebar>
    </template>

    <!-- Toolbar -->
    <template #toolbar>
      <TenantToolbar
        :title="title"
        :breadcrumbs="breadcrumbs"
      >
        <template #actions>
          <slot name="toolbar-actions" />
        </template>
      </TenantToolbar>
    </template>

    <!-- Main Content (slot padrão) -->
    <slot />

    <!-- Footer -->
    <template #footer>
      <TenantFooter />
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
import { ref, provide } from 'vue';
import { router } from '@inertiajs/vue3';
import BaseAuthenticatedLayout from './BaseAuthenticatedLayout.vue';
import Sidebar from '@/Components/Shared/Navigation/Sidebar.vue';
import SidebarHeader from '@/Components/Shared/Navigation/SidebarHeader.vue';
import SidebarMenu from '@/Components/Shared/Navigation/SidebarMenu.vue';
import SidebarFooter from '@/Components/Shared/Navigation/SidebarFooter.vue';
import TenantMobileHeader from '@/Components/Tenant/TenantMobileHeader.vue';
import TenantToolbar from '@/Components/Tenant/TenantToolbar.vue';
import TenantFooter from '@/Components/Tenant/TenantFooter.vue';
import NotificationsDrawer from '@/Components/Shared/NotificationsDrawer.vue';
import SearchModal from '@/Components/Shared/SearchModal.vue';
import { tenantMenu } from '@/config/tenantMenu.js';
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
} = useSidebar('tenant');

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

function handleAddNew() {
  // Navegar para criar nova ordem de serviço (ação padrão do botão "Add New")
  router.visit(route('tenant.service-orders.create'));
}
</script>
