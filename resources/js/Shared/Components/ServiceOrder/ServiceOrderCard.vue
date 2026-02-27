<template>
  <div 
    class="service-order-card bg-gray-100 dark:bg-[#080808] rounded-lg p-3 cursor-pointer hover:shadow-md transition-shadow"
    @click="$emit('click')"
  >
    <div class="flex items-start justify-between mb-2">
      <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">
        {{ serviceOrder.code }}
      </span>
    </div>

    <div class="mb-2">
      <div class="text-sm font-medium text-gray-700 dark:text-gray-300 truncate">
        {{ serviceOrder.client.name }}
      </div>
    </div>

    <div class="mb-3">
      <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
        <i class="ki-filled ki-car text-sm"></i>
        <span class="truncate">
          {{ serviceOrder.vehicle.brand }} {{ serviceOrder.vehicle.model }}
        </span>
      </div>
      <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
        <i class="ki-filled ki-id text-sm"></i>
        <span>{{ serviceOrder.vehicle.plate }}</span>
      </div>
    </div>

    <div class="flex items-center justify-between pt-2 border-t border-gray-200 dark:border-gray-800">
      <div class="text-xs text-gray-500 dark:text-gray-400">
        {{ formattedEntryDate }}
      </div>
      <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">
        {{ formattedTotal }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  serviceOrder: {
    type: Object,
    required: true,
  },
});

defineEmits(['click']);

const formattedEntryDate = computed(() => {
  const date = new Date(props.serviceOrder.entry_date);
  return date.toLocaleDateString('pt-BR');
});

const formattedTotal = computed(() => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  }).format(props.serviceOrder.total);
});
</script>

<style scoped>
.service-order-card {
  touch-action: none;
}
</style>
