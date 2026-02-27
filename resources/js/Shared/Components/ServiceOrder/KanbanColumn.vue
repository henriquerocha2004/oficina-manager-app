<template>
  <div class="kanban-column flex flex-col bg-white dark:bg-card border border-border rounded-lg h-full min-h-[300px]">
    <div class="p-3 border-b border-border shrink-0">
      <div class="flex items-center justify-between">
        <h3 class="font-semibold text-foreground">
          {{ title }}
        </h3>
        <span class="badge badge-secondary">
          {{ items.length }}
        </span>
      </div>
    </div>

    <div class="column-body flex-1 p-2 overflow-y-auto">
      <draggable
        v-model="localItems"
        :group="{ name: 'service-orders' }"
        item-key="id"
        class="space-y-2 min-h-[100px]"
        ghost-class="ghost-card"
        drag-class="drag-card"
        :animation="200"
        @change="handleChange"
      >
        <template #item="{ element }">
          <ServiceOrderCard
            :service-order="element"
            @click="$emit('card-click', element)"
          />
        </template>
      </draggable>

      <div v-if="items.length === 0" class="flex items-center justify-center h-full min-h-[200px] text-muted-foreground text-sm">
        Nenhuma OS
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import draggable from 'vuedraggable';
import ServiceOrderCard from './ServiceOrderCard.vue';

const props = defineProps({
  status: {
    type: String,
    required: true,
  },
  title: {
    type: String,
    required: true,
  },
  items: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(['card-click', 'change']);

const localItems = computed({
  get: () => props.items,
  set: (value) => {
    // Não faz nada - a modificação é tratada pelo vuedraggable
  }
});

function handleChange(event) {
  if (event.added) {
    emit('change', {
      type: 'added',
      serviceOrder: event.added.element,
      newStatus: props.status,
    });
  }
}
</script>

<style scoped>
.ghost-card {
  opacity: 0.5;
  background: var(--color-muted);
  border: 2px dashed var(--color-border);
}

.drag-card {
  transform: rotate(3deg);
}
</style>
