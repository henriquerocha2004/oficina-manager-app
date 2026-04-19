<template>
  <div id="sidebar_header" class="sidebar-header px-5 py-4 border-b border-border">
    <!-- Logo e Brand -->
    <div class="flex items-center justify-between gap-2">
      <div class="flex items-center gap-2">
        <img
          :src="logo"
          :class="isCustomLogo ? 'h-8 w-8 object-contain rounded' : 'h-8 w-8'"
          :alt="brandName"
        />
        <span v-if="!collapsed" class="text-lg font-semibold text-gray-100">
          {{ brandName }}
        </span>
      </div>
      

    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import logoUrl from '@assets/media/app/mini-logo-circle-success.svg';

const props = defineProps({
  logo: {
    type: String,
    default: undefined,
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

const isCustomLogo = computed(() => !!props.logo && !props.logo.endsWith('.svg'));
const logo = computed(() => props.logo || logoUrl);
</script>
