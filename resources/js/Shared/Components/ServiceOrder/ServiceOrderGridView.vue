<template>
  <div class="service-order-grid-view lg:h-full flex flex-col">
    <StatsContainer class="shrink-0">
      <StatsCard
        v-for="stat in stats"
        :key="stat.key"
        :icon="stat.icon"
        :title="stat.title"
        :value="stat.value"
        :color="stat.color"
      />
    </StatsContainer>

    <div class="mt-4 lg:flex-1 lg:min-h-0">
      <ServiceOrderGrid />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import StatsContainer from '@/Shared/Components/StatsContainer.vue';
import StatsCard from '@/Shared/Components/StatsCard.vue';
import ServiceOrderGrid from './ServiceOrderGrid.vue';
import { fetchServiceOrderStats } from '@/services/serviceOrderService.js';

const stats = ref([
  { key: 'total',            title: 'Total',        value: 0, icon: 'ki-filled ki-copy',       color: 'blue'   },
  { key: 'draft',            title: 'Rascunho',     value: 0, icon: 'ki-filled ki-note',        color: 'gray'   },
  { key: 'waiting_approval', title: 'Aguardando',   value: 0, icon: 'ki-filled ki-time',        color: 'yellow' },
  { key: 'in_progress',      title: 'Em Progresso', value: 0, icon: 'ki-filled ki-setting-2',   color: 'orange' },
  { key: 'completed',        title: 'Concluídas',   value: 0, icon: 'ki-filled ki-double-check', color: 'green'  },
]);

async function loadStats() {
  const result = await fetchServiceOrderStats();
  if (result.success) {
    const data = result.data;
    const s = data.by_status || {};
    stats.value = [
      { key: 'total',            title: 'Total',        value: data.total ?? 0,           icon: 'ki-filled ki-copy',        color: 'blue'   },
      { key: 'draft',            title: 'Rascunho',     value: s.draft ?? 0,              icon: 'ki-filled ki-note',        color: 'gray'   },
      { key: 'waiting_approval', title: 'Aguardando',   value: s.waiting_approval ?? 0,   icon: 'ki-filled ki-time',        color: 'yellow' },
      { key: 'in_progress',      title: 'Em Progresso', value: s.in_progress ?? 0,        icon: 'ki-filled ki-setting-2',   color: 'orange' },
      { key: 'completed',        title: 'Concluídas',   value: s.completed ?? 0,          icon: 'ki-filled ki-double-check', color: 'green'  },
    ];
  }
}

onMounted(() => {
  loadStats();
});
</script>
