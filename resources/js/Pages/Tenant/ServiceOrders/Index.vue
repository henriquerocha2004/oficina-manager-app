<template>
  <TenantLayout title="Ordens de Serviço" :breadcrumbs="breadcrumbs">
    <div class="kt-container-fixed w-full h-full py-4 px-2 flex flex-col">
      <div class="flex items-center justify-end gap-3 mb-4 shrink-0">
        <div v-if="!isMobile" class="view-toggle-switch">
          <button
            class="view-toggle-option"
            :class="{ 'active': viewMode === 'kanban' }"
            @click="onViewModeChange('kanban')"
          >
            <i class="ki-filled ki-element-11 text-sm"></i>
          </button>
          <button
            class="view-toggle-option"
            :class="{ 'active': viewMode === 'grid' }"
            @click="onViewModeChange('grid')"
          >
            <i class="ki-filled ki-row-vertical text-sm"></i>
          </button>
        </div>
        <button v-if="canCreateServiceOrder()" class="kt-btn" @click="onNew">
          <i class="ki-filled ki-plus text-sm mr-1"></i>
          Nova OS
        </button>
      </div>

      <div class="flex-1 min-h-0">
        <Transition name="fade-slide" mode="out-in">
          <KanbanBoard v-if="viewMode === 'kanban' && !isMobile" ref="kanbanRef" />
          <ServiceOrderGridView v-else />
        </Transition>
      </div>
    </div>

    <CreateServiceOrderModal ref="createModalRef" @created="onOrderCreated" />
  </TenantLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import KanbanBoard from '@/Shared/Components/ServiceOrder/KanbanBoard.vue';
import ServiceOrderGridView from '@/Shared/Components/ServiceOrder/ServiceOrderGridView.vue';
import CreateServiceOrderModal from '@/Shared/Components/ServiceOrder/CreateServiceOrderModal.vue';
import { usePermissions } from '@/Composables/usePermissions.js';

const { canCreateServiceOrder } = usePermissions();

const VIEW_MODE_KEY = 'service-orders-view-mode';

const windowWidth = ref(typeof window !== 'undefined' ? window.innerWidth : 1024);
const createModalRef = ref(null);
const kanbanRef = ref(null);

const isMobile = computed(() => windowWidth.value < 1024);

const viewMode = ref(
  isMobile.value ? 'grid' : (localStorage.getItem(VIEW_MODE_KEY) ?? 'kanban')
);

function onViewModeChange(mode) {
  viewMode.value = mode;
  localStorage.setItem(VIEW_MODE_KEY, mode);
}

function updateWidth() {
  windowWidth.value = window.innerWidth;
  if (isMobile.value) {
    viewMode.value = 'grid';
  }
}

onMounted(() => {
  window.addEventListener('resize', updateWidth);
  updateWidth();
});

onUnmounted(() => {
  window.removeEventListener('resize', updateWidth);
});

const breadcrumbs = [
  { label: 'Ordens de Serviço' },
];

function onNew() {
  createModalRef.value?.open();
}

function onOrderCreated(serviceOrder) {
  router.visit(`/service-orders/${serviceOrder.id}/detail`);
}
</script>

<style>
.kt-container-fixed {
  box-sizing: border-box;
  width: 100%;
  max-width: 100%;
  margin: 0 auto;
}

.view-toggle-switch {
  display: flex;
  position: relative;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 4px;
  gap: 4px;
}

.dark .view-toggle-switch {
  background: #1a1a1a;
  border-color: #1f1f1f;
}

.view-toggle-option {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 28px;
  border-radius: 6px;
  border: none;
  background: transparent;
  color: #6b7280;
  cursor: pointer;
  transition: all 0.2s ease;
  position: relative;
  z-index: 1;
}

.view-toggle-option:hover {
  color: #ff5722;
}

.view-toggle-option.active {
  color: white;
}

.dark .view-toggle-option.active {
  color: white;
}

.view-toggle-switch::before {
  content: '';
  position: absolute;
  top: 4px;
  left: 4px;
  width: 32px;
  height: 28px;
  background: #ff5722;
  border-radius: 6px;
  transition: transform 0.2s ease;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.dark .view-toggle-switch::before {
  background: #ff5722;
}

.view-toggle-option.active ~ .view-toggle-option::before,
.view-toggle-switch:has(.view-toggle-option:last-child.active)::before {
  transform: translateX(36px);
}

.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.fade-slide-enter-from {
  opacity: 0;
  transform: translateY(10px);
}

.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-10px);
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
