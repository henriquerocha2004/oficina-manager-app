<template>
  <div class="kanban-board flex flex-col h-full">
    <div class="flex items-center justify-end gap-4 mb-4 ml-auto shrink-0" style="width: 280px;">
      <label class="text-sm font-medium text-secondary-foreground whitespace-nowrap">
        Período:
      </label>
      <select
        :value="days"
        class="kt-select flex-1"
        @change="onDaysChange"
      >
        <option :value="30">Últimos 30 dias</option>
        <option :value="60">Últimos 60 dias</option>
        <option :value="90">Últimos 90 dias</option>
      </select>
    </div>

    <div v-if="loading" class="flex items-center justify-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
    </div>

    <div v-else-if="error" class="flex items-center justify-center py-12">
      <div class="text-center">
        <p class="text-destructive mb-2">Erro ao carregar ordens de serviço</p>
        <button class="kt-btn" @click="load">
          Tentar novamente
        </button>
      </div>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 flex-1" style="min-height: 0;">
      <KanbanColumn
        v-for="status in kanbanStatuses"
        :key="status"
        :status="status"
        :title="columnTitles[status]"
        :items="columns[status].value"
        :key-map="statusColumnKey"
        :can-receive="canTransition"
        :check-requires-data="checkRequiresData"
        :dragging-item="draggingItem"
        @card-click="onCardClick"
        @change="onStatusChange"
        @drag-start="onDragStart"
        @drag-end="onDragEnd"
      />
    </div>

    <ServiceOrderTransitionModal ref="transitionModal" @cancel="onTransitionCancel" />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import KanbanColumn from './KanbanColumn.vue';
import ServiceOrderTransitionModal from './ServiceOrderTransitionModal.vue';
import { useServiceOrderKanban } from '@/Composables/useServiceOrderKanban.js';
import { KanbanStatuses, KanbanColumnLabels } from '@/Data/serviceOrderStatuses.js';
import { getTransitionLabel, canTransition, requiresData } from '@/Composables/useServiceOrderTransitions.js';
import { fetchServiceOrderItems } from '@/services/serviceOrderService.js';

const {
  columns,
  loading,
  error,
  days,
  setDays,
  load,
  changeStatus,
  changeStatusWithData,
  checkTransition,
  findCurrentStatus,
  removeFromCurrentColumn,
  addToColumn,
} = useServiceOrderKanban();

function checkRequiresData(fromStatus, toStatus) {
  return requiresData(fromStatus, toStatus).length > 0;
}

const kanbanStatuses = KanbanStatuses;
const columnTitles = KanbanColumnLabels;

const transitionModal = ref(null);
const pendingTransition = ref(null);
const statusColumnKey = reactive({});
const draggingItem = ref(null);

function onDragStart(item) {
  draggingItem.value = item;
}

function onDragEnd() {
  draggingItem.value = null;
}

function forceRerenderColumn(status) {
  statusColumnKey[status] = (statusColumnKey[status] || 0) + 1;
}

function onDaysChange(event) {
  setDays(parseInt(event.target.value, 10));
}

function onCardClick(serviceOrder) {
  router.visit('/service-orders/' + serviceOrder.id + '/detail');
}

async function onStatusChange({ serviceOrder, newStatus }) {
  const checkResult = checkTransition(serviceOrder.id, newStatus);
  
  if (!checkResult.allowed) {
    window.showToast?.({
      message: checkResult.error,
      icon: '<i class="ki-filled ki-cross-circle text-red-500 text-xl"></i>'
    });
    await load();
    return;
  }

  if (checkResult.needsData) {
    const title = getTransitionLabel(serviceOrder.status, newStatus);

    let itemsForModal = [];
    if (serviceOrder.status === 'in_progress') {
      const result = await fetchServiceOrderItems(serviceOrder.id);
      if (result.success) {
        itemsForModal = result.data;
      }
    }

    pendingTransition.value = {
      serviceOrderId: serviceOrder.id,
      currentStatus: serviceOrder.status,
      newStatus: newStatus,
      title,
      needsDiagnosis: checkResult.needsDiagnosis,
      needsItems: checkResult.needsItems,
      initialDiagnosis: serviceOrder.diagnosis,
      initialItems: itemsForModal,
      serviceOrder: { ...serviceOrder },
      toStatus: newStatus
    };

    const result = await transitionModal.value.open({
      fromStatus: serviceOrder.status,
      toStatus: newStatus,
      title,
      needsDiagnosis: checkResult.needsDiagnosis,
      needsItems: checkResult.needsItems,
      initialDiagnosis: serviceOrder.diagnosis,
      initialItems: itemsForModal
    });

    if (result) {
      await onTransitionConfirm(result);
    }
    return;
  }

  try {
    await changeStatus(serviceOrder.id, newStatus);
    window.showToast?.({
      message: 'Status atualizado com sucesso',
      icon: '<i class="ki-filled ki-check-circle text-green-500 text-xl"></i>'
    });
  } catch (err) {
    window.showToast?.({
      message: err.message || 'Erro ao atualizar status',
      icon: '<i class="ki-filled ki-cross-circle text-red-500 text-xl"></i>'
    });
  }
}

async function onTransitionConfirm(data) {
  if (!pendingTransition.value) {
    return;
  }

  const { serviceOrderId, currentStatus, newStatus } = pendingTransition.value;

  try {
    const updatedOrder = await changeStatusWithData(serviceOrderId, currentStatus, newStatus, data);
    addToColumn(serviceOrderId, newStatus);
    if (updatedOrder) {
      const col = columns[newStatus].value;
      const idx = col.findIndex(so => String(so.id) === String(serviceOrderId));
      if (idx !== -1) {
        col[idx] = updatedOrder;
      }
    }
    window.showToast?.({
      message: 'Status atualizado com sucesso',
      icon: '<i class="ki-filled ki-check-circle text-green-500 text-xl"></i>'
    });
  } catch (err) {
    window.showToast?.({
      message: err.message || 'Erro ao atualizar status',
      icon: '<i class="ki-filled ki-cross-circle text-red-500 text-xl"></i>'
    });
    await load();
  } finally {
    pendingTransition.value = null;
  }
}

function onTransitionCancel() {
  pendingTransition.value = null;
  load();
}

onMounted(() => {
  load();
});

function addDraftOrder(serviceOrder) {
  columns['draft'].value.unshift(serviceOrder);
}

defineExpose({ addDraftOrder });
</script>

<style scoped>
.kt-select {
  padding: 0.5rem 0.75rem;
  border: 1px solid var(--color-border);
  border-radius: var(--radius);
  background-color: var(--color-card);
  color: var(--color-foreground);
}

.dark .kt-select {
  background-color: var(--color-card);
  color: var(--color-foreground);
}
</style>
