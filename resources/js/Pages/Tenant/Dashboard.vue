<template>
  <TenantLayout
    title="Dashboard"
    :breadcrumbs="breadcrumbs"
  >
    <!-- Container Principal -->
    <div class="grid gap-5 lg:gap-7.5 border-t border-border pt-5">
      <!-- Grid Row -->
      <div class="grid lg:grid-cols-3 gap-5 lg:gap-7.5">
        <!-- Card 1 -->
        <div class="card bg-white dark:bg-card">
          <div class="card-body">
            <i class="ki-filled ki-rocket text-3xl text-primary mb-2"></i>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-foreground mb-1">
              Dashboard em Construção
            </h3>
            <p class="text-sm text-gray-600 dark:text-muted-foreground">
              O conteúdo do dashboard será implementado em breve
            </p>
          </div>
        </div>

        <!-- Card 2 -->
        <div class="card bg-white dark:bg-card">
          <div class="card-body">
            <i class="ki-filled ki-chart-line-up text-3xl text-success mb-2"></i>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-foreground mb-1">
              Estatísticas
            </h3>
            <p class="text-sm text-gray-600 dark:text-muted-foreground">
              Visualize métricas e indicadores importantes
            </p>
          </div>
        </div>

        <!-- Card 3 -->
        <div class="card bg-white dark:bg-card">
          <div class="card-body">
            <i class="ki-filled ki-notification-2 text-3xl text-warning mb-2"></i>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-foreground mb-1">
              Notificações
            </h3>
            <p class="text-sm text-gray-600 dark:text-muted-foreground">
              Acompanhe atualizações e alertas
            </p>
          </div>
        </div>
      </div>

      <!-- Área de Conteúdo Principal -->
      <div class="card bg-white dark:bg-card">
        <div class="card-header">
          <h3 class="card-title text-2xl font-bold">Bem-vindo ao Sistema</h3>
        </div>
        <div class="card-body">
          <div class="flex flex-col items-center justify-center py-10">
            <i class="ki-filled ki-abstract-41 text-6xl text-gray-400 mb-4"></i>
            <h4 class="text-xl font-semibold text-gray-900 dark:text-foreground mb-2">
              Sistema de Gestão de Oficinas
            </h4>
            <p class="text-gray-600 dark:text-muted-foreground text-center max-w-md">
              Utilize o menu lateral para navegar pelas funcionalidades disponíveis.
            </p>
          </div>
        </div>
      </div>
    </div>
  </TenantLayout>
</template>

<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';

const breadcrumbs = [
  { label: 'Dashboard' },
];
</script>
