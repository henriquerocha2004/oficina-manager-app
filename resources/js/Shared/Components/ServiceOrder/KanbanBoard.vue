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
        :items="columns[status] || []"
        @card-click="onCardClick"
        @change="onStatusChange"
      />
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import KanbanColumn from './KanbanColumn.vue';
import { useServiceOrderKanban } from '@/Composables/useServiceOrderKanban.js';
import { KanbanStatuses, KanbanColumnLabels } from '@/Data/serviceOrderStatuses.js';

const {
  columns,
  loading,
  error,
  days,
  setDays,
  load,
  changeStatus,
} = useServiceOrderKanban();

const kanbanStatuses = KanbanStatuses;
const columnTitles = KanbanColumnLabels;

function onDaysChange(event) {
  setDays(parseInt(event.target.value, 10));
}

function onCardClick(serviceOrder) {
  router.get('/service-orders/' + serviceOrder.id);
}

async function onStatusChange({ serviceOrder, newStatus }) {
  await changeStatus(serviceOrder.id, newStatus);
}

onMounted(() => {
  load();
});
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
