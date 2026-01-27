<template>
  <div 
    class="header lg:hidden flex items-center fixed z-10 top-0 left-0 right-0 h-[60px] px-4 bg-gray-900 border-b border-gray-800"
  >
    <!-- Logo -->
    <div class="flex items-center gap-2 flex-1">
      <img 
        :src="logo" 
        class="h-8 w-8" 
        :alt="brandName"
      />
      <span class="text-lg font-semibold text-gray-100">
        {{ brandName }}
      </span>
    </div>

    <!-- Menu Toggle -->
    <button
      type="button"
      class="inline-flex items-center justify-center rounded-lg font-medium transition-colors w-10 h-10 px-0 bg-gray-700 text-gray-200 hover:bg-gray-600"
      @click="$emit('toggle-sidebar')"
      aria-label="Toggle Menu"
    >
      <i class="ki-outline ki-menu text-xl"></i>
    </button>
  </div>
</template>

<script setup>
import logoUrl from '@assets/media/app/mini-logo-circle-success.svg';

defineProps({
  logo: {
    type: String,
    default: logoUrl,
  },
  brandName: {
    type: String,
    required: true,
  },
});

defineEmits(['toggle-sidebar']);
</script>
