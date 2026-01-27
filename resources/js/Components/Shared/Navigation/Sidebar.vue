<template>
  <div 
    class="fixed top-0 left-0 bottom-0 w-67.5 z-105 flex flex-col transition-transform duration-300 ease-in-out bg-[#080808]
           before:content-[''] before:fixed before:inset-0 before:bg-black/50 before:opacity-0 before:pointer-events-none before:transition-opacity before:duration-300
           lg:z-105
           max-lg:z-110"
    :class="{ 
      '-translate-x-full max-lg:before:opacity-100 max-lg:before:pointer-events-auto': !isSidebarOpen,
      'lg:translate-x-0 lg:w-20': !isSidebarOpen && isSidebarOpen === false
    }"
  >
    <slot />
  </div>
</template>

<script setup>
defineProps({
  isSidebarOpen: {
    type: Boolean,
    default: true,
  },
});
</script>
