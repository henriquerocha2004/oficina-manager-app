<template>
  <div id="sidebar_header" class="sidebar-header px-5 py-4 border-b border-border">
    <!-- Logo e Brand -->
    <div class="flex items-center justify-between gap-2">
      <div class="flex items-center gap-2">
        <img 
          :src="logo" 
          class="h-8 w-8" 
          :alt="brandName"
        />
        <span v-if="!collapsed" class="text-lg font-semibold text-gray-100">
          {{ brandName }}
        </span>
      </div>
      
      <!-- Botões do Header -->
      <div v-if="!collapsed" class="flex items-center gap-1">
        <button
          v-if="showSearch"
          type="button"
          @click="$emit('open-search')"
          class="inline-flex items-center justify-center h-8 w-8 px-0 rounded-lg bg-primary text-white transition-colors"
          title="Buscar"
        >
          <i class="ki-outline ki-magnifier"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import logoUrl from '@assets/media/app/mini-logo-circle-success.svg';

const props = defineProps({
  logo: {
    type: String,
    default: logoUrl,
  },
  brandName: {
    type: String,
    required: true,
  },
  collapsed: {
    type: Boolean,
    default: false,
  },
  showSearch: {
    type: Boolean,
    default: true,
  },
  showAddNew: {
    type: Boolean,
    default: false,
  },
});

defineEmits(['open-search', 'add-new']);
</script>
