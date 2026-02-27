<template>
  <div class="service-order-grid-view">
    <StatsContainer>
      <StatsCard
        v-for="stat in stats"
        :key="stat.key"
        :icon="stat.icon"
        :title="stat.title"
        :value="stat.value"
        :color="stat.color"
      />
    </StatsContainer>

    <div class="mt-4">
      <ServiceOrderGrid
        @new="$emit('new')"
        @view="$emit('view', $event)"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import StatsContainer from '@/Shared/Components/StatsContainer.vue';
import StatsCard from '@/Shared/Components/StatsCard.vue';
import ServiceOrderGrid from './ServiceOrderGrid.vue';
import { fetchServiceOrderStats } from '@/services/serviceOrderService.js';

defineEmits(['new', 'view']);

const stats = ref([
  { key: 'total', title: 'Total', value: 0, icon: 'ki-copy', color: 'blue' },
  { key: 'draft', title: 'Rascunho', value: 0, icon: 'ki-note', color: 'gray' },
  { key: 'waiting_approval', title: 'Aguardando', value: 0, icon: 'ki-time', color: 'yellow' },
  { key: 'in_progress', title: 'Em Progresso', value: 0, icon: 'ki-gear', color: 'orange' },
  { key: 'completed', title: 'Concluídas', value: 0, icon: 'ki-check', color: 'green' },
]);

async function loadStats() {
  const result = await fetchServiceOrderStats();
  if (result.success) {
    const data = result.data;
    stats.value = [
      { key: 'total', title: 'Total', value: data.total || 0, icon: 'ki-copy', color: 'blue' },
      { key: 'draft', title: 'Rascunho', value: data.draft || 0, icon: 'ki-note', color: 'gray' },
      { key: 'waiting_approval', title: 'Aguardando', value: data.waiting_approval || 0, icon: 'ki-time', color: 'yellow' },
      { key: 'in_progress', title: 'Em Progresso', value: data.in_progress || 0, icon: 'ki-gear', color: 'orange' },
      { key: 'completed', title: 'Concluídas', value: data.completed || 0, icon: 'ki-check', color: 'green' },
    ];
  }
}

onMounted(() => {
  loadStats();
});
</script>
