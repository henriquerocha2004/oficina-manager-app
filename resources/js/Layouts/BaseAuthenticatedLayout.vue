<template>
  <div class="flex grow">
    <!-- Mobile Header -->
    <slot name="mobile-header" />

    <!-- Sidebar -->
    <slot name="sidebar" />

    <!-- Main Wrapper -->
    <div 
      class="main-wrapper flex flex-col grow bg-background dark:bg-background"
      :style="mainWrapperStyle"
    >
      <!-- Toolbar -->
      <slot name="toolbar" />

      <!-- Main Content -->
      <main class="flex-1 p-5 lg:p-10">
        <slot />
      </main>

      <!-- Footer -->
      <slot name="footer" />
    </div>

    <!-- Overlays -->
    <slot name="notifications-drawer" />
    <slot name="search-modal" />
  </div>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  sidebarOpen: {
    type: Boolean,
    default: true,
  },
});

const windowWidth = ref(typeof window !== 'undefined' ? window.innerWidth : 1024);

function updateWindowWidth() {
  windowWidth.value = window.innerWidth;
}

onMounted(() => {
  window.addEventListener('resize', updateWindowWidth);
});
onUnmounted(() => {
  window.removeEventListener('resize', updateWindowWidth);
});

// Ajustar margin-left do main wrapper baseado no estado da sidebar e largura da janela
const mainWrapperStyle = computed(() => {
  if (windowWidth.value >= 1024) {
    return {
      marginLeft: props.sidebarOpen ? '270px' : '80px',
      transition: 'margin-left 0.3s ease',
    };
  }
  return {};
});
</script>

<style>
/* CSS Variables do Metronic */
:root {
  --header-height: 60px;
  --sidebar-width: 270px;
}

/* Main wrapper base styles */
.main-wrapper {
  min-height: 100vh;
}

/* Mobile: adicionar padding-top para compensar header fixo */
@media (max-width: 1023px) {
  .main-wrapper {
    padding-top: var(--header-height);
  }
}

/* Desktop: remover padding-top */
@media (min-width: 1024px) {
  .main-wrapper {
    padding-top: 0;
  }
}
</style>
