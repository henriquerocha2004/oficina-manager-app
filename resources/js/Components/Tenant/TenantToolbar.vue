<template>
  <div class="toolbar py-3 px-5 lg:px-10 border-b border-gray-200 dark:border-gray-800 min-h-[56px] flex items-center">
    <div class="flex items-center justify-between gap-4">
      <!-- Título e Breadcrumbs -->
      <div class="flex flex-col gap-1">
        <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
          {{ title }}
        </h1>
        <nav v-if="breadcrumbs && breadcrumbs.length > 0" class="breadcrumb">
          <ol class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
            <li 
              v-for="(crumb, index) in breadcrumbs" 
              :key="index"
              class="flex items-center gap-2"
            >
              <Link
                v-if="crumb.url"
                :href="crumb.url"
                class="hover:text-primary transition-colors"
              >
                {{ crumb.label }}
              </Link>
              <span v-else class="text-gray-900 dark:text-gray-100">
                {{ crumb.label }}
              </span>
              <i 
                v-if="index < breadcrumbs.length - 1"
                class="ki-outline ki-right text-xs"
              ></i>
            </li>
          </ol>
        </nav>
      </div>

      <!-- Slot para ações personalizadas -->
      <div v-if="$slots.actions" class="flex items-center gap-2">
        <slot name="actions" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  title: {
    type: String,
    required: true,
  },
  breadcrumbs: {
    type: Array,
    default: () => [],
  },
});
</script>
