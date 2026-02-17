<template>
  <footer class="fixed bottom-0 left-0 w-full z-30 border-t border-border bg-background/95 backdrop-blur supports-backdrop-filter:bg-background/80 font-sans">
    <div class="flex flex-col md:flex-row items-center justify-between gap-4 px-5 py-3 min-h-14">
      <div class="text-xs text-muted-foreground">
        {{ currentYear }} &copy; {{ appName }}
      </div>
      <div class="flex items-center gap-4 md:justify-end w-full md:w-auto">
        <a
          v-for="link in links"
          :key="link.label"
          :href="link.url"
          class="text-xs text-muted-foreground hover:text-primary transition-colors font-medium"
          :target="link.external ? '_blank' : '_self'"
          :rel="link.external ? 'noopener noreferrer' : ''"
        >
          {{ link.label }}
        </a>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  appName: {
    type: String,
    default: 'Oficina Manager',
  },
  links: {
    type: Array,
    default: () => [
      { label: 'Ajuda', url: '#', external: false },
      { label: 'Suporte', url: '#', external: false },
      { label: 'Política de Privacidade', url: '#', external: false },
    ],
  },
});

const currentYear = computed(() => new Date().getFullYear());
</script>
