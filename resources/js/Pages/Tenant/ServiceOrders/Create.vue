<template>
  <TenantLayout title="Nova Ordem de Serviço" :breadcrumbs="breadcrumbs">
    <div class="kt-container-fixed w-full py-4 px-2">
      <div class="card bg-white dark:bg-card border border-border rounded-lg">
        <div class="card-header px-6 py-4 border-b border-neutral-300 dark:border-white/20">
          <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
            Nova Ordem de Serviço
          </h2>
        </div>
        <div class="p-6">
          <p class="text-gray-600 dark:text-gray-400">
            Funcionalidade em desenvolvimento...
          </p>
        </div>
        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-neutral-300 dark:border-white/20">
          <button class="kt-btn kt-btn-ghost" @click="goBack">
            Cancelar
          </button>
          <button class="kt-btn kt-btn-primary">
            Criar OS
          </button>
        </div>
      </div>
    </div>
  </TenantLayout>
</template>

<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';

const breadcrumbs = computed(() => [
  { label: 'Ordens de Serviço', href: '/service-orders' },
  { label: 'Nova OS' },
]);

function goBack() {
  router.get('/service-orders');
}
</script>

<style>
.kt-container-fixed {
  box-sizing: border-box;
  width: 100%;
  max-width: 100%;
  margin: 0 auto;
}

@media (min-width: 640px) {
  .kt-container-fixed { max-width: 640px; }
}
@media (min-width: 768px) {
  .kt-container-fixed { max-width: 768px; }
}
@media (min-width: 1024px) {
  .kt-container-fixed { max-width: 1400px; }
}
@media (min-width: 1280px) {
  .kt-container-fixed { max-width: 1600px; }
}
</style>
