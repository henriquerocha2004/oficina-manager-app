<template>
  <div 
    id="sidebar_menu" 
    class="sidebar-menu flex-1 overflow-y-auto px-3 py-4"
  >
    <!-- Menu Principal -->
    <div class="flex flex-col gap-2">
      <SidebarMenuItem
        v-for="(item, index) in menuItems"
        :key="index"
        :item="item"
        :collapsed="collapsed"
        @toggle-accordion="$emit('toggle-accordion', $event)"
        @item-click="$emit('item-click', $event)"
      />
    </div>
  </div>
</template>

<script setup>
import SidebarMenuItem from './SidebarMenuItem.vue';

defineProps({
  menuItems: {
    type: Array,
    required: true,
  },
  collapsed: {
    type: Boolean,
    default: false,
  },
});

defineEmits(['toggle-accordion', 'item-click']);
</script>
