<template>
  <div 
    :key="columnKey" 
    class="kanban-column flex flex-col bg-white dark:bg-card border border-border rounded-lg h-full min-h-[300px]" 
    :class="{ 'column-disabled': isDisabled }"
    :data-status="status"
  >
    <div class="p-3 border-b border-border shrink-0">
      <div class="flex items-center justify-between">
        <h3 class="font-semibold text-foreground">
          {{ title }}
        </h3>
        <span class="badge badge-secondary">
          {{ localItems.length }}
        </span>
      </div>
    </div>

    <div class="column-body flex-1 p-2 overflow-y-auto" :data-status="status">
      <draggable
        v-model="localItems"
        group="service-orders"
        item-key="id"
        class="space-y-2 min-h-[100px]"
        ghost-class="ghost-card"
        drag-class="drag-card"
        :animation="200"
        :move="checkMove"
        @change="handleChange"
        @start="onDragStart"
        @end="onDragEnd"
      >
        <template #item="{ element }">
          <ServiceOrderCard
            :service-order="element"
            @click="$emit('card-click', element)"
          />
        </template>
      </draggable>

      <div v-if="localItems.length === 0" class="flex items-center justify-center min-h-[100px] text-muted-foreground text-sm">
        Nenhuma OS
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
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
  keyMap: {
    type: Object,
    default: () => ({}),
  },
  canReceive: {
    type: Function,
    required: true,
  },
  checkRequiresData: {
    type: Function,
    required: true,
  },
  draggingItem: {
    type: Object,
    default: null,
  },
});

const emit = defineEmits(['card-click', 'change', 'drag-start', 'drag-end', 'check-transition']);

const localItems = ref([...props.items]);
const isUpdating = ref(false);

const isDisabled = computed(() => {
  if (!props.draggingItem) return false;
  const fromStatus = props.draggingItem.status;
  if (fromStatus === props.status) return false;
  return !props.canReceive(fromStatus, props.status);
});

const columnKey = computed(() => props.keyMap[props.status] || 'default');

watch(() => props.items, (newVal) => {
  if (!isUpdating.value) {
    localItems.value = [...newVal];
  }
}, { deep: true });

function handleChange(event) {
  if (event.added) {
    emit('change', {
      type: 'added',
      serviceOrder: event.added.element,
      newStatus: props.status,
    });
  }
}

function canReceive(to, from, dragEl, event) {
  if (!event?.relatedContext?.element) {
    return false;
  }
  const fromStatus = event.relatedContext.element.status;
  const toStatus = props.status;
  
  if (fromStatus === toStatus) {
    return true;
  }
  
  return props.canReceive(fromStatus, toStatus);
}

function checkMove(evt) {
  const fromStatus = evt.draggedContext?.element?.status;
  const toStatus = props.status;
  
  if (fromStatus === toStatus) {
    return true;
  }
  
  if (!props.canReceive(fromStatus, toStatus)) {
    return false;
  }
  
  if (props.checkRequiresData(fromStatus, toStatus)) {
    return false;
  }
  
  return true;
}

function onDragStart(evt) {
  const item = evt.item.__draggable_context?.element;
  emit('drag-start', item);
}

function onDragEnd() {
  emit('drag-end');
}

function syncFromParent(newItems) {
  isUpdating.value = true;
  localItems.value = [...newItems];
  setTimeout(() => {
    isUpdating.value = false;
  }, 100);
}

defineExpose({ syncFromParent });
</script>

<style scoped>
.column-disabled {
  opacity: 0.5;
  pointer-events: none;
}

.ghost-card {
  opacity: 0.5;
  background: var(--color-muted);
  border: 2px dashed var(--color-border);
}

.drag-card {
  transform: rotate(3deg);
}
</style>
