<template>
  <AdminLayout
    title="Dashboard Administrativo"
    :breadcrumbs="breadcrumbs"
  >
    <!-- Container Principal -->
    <div class="grid gap-5 lg:gap-7.5 border-t border-border pt-5">
      <!-- Grid Row -->
      <div class="grid lg:grid-cols-3 gap-5 lg:gap-7.5">
        <!-- Card 1 -->
        <div class="card bg-white dark:bg-card">
          <div class="card-body">
            <i class="ki-filled ki-shield-tick text-3xl text-primary mb-2"></i>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-foreground mb-1">
              Painel Administrativo
            </h3>
            <p class="text-sm text-gray-600 dark:text-muted-foreground">
              Gerencie tenants e assinaturas do sistema
            </p>
          </div>
        </div>

        <!-- Card 2 -->
        <div class="card bg-white dark:bg-card">
          <div class="card-body">
            <i class="ki-filled ki-rocket text-3xl text-success mb-2"></i>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-foreground mb-1">
              Dashboard em Construção
            </h3>
            <p class="text-sm text-gray-600 dark:text-muted-foreground">
              O conteúdo do dashboard será implementado em breve
            </p>
          </div>
        </div>

        <!-- Card 3 -->
        <div class="card bg-white dark:bg-card">
          <div class="card-body">
            <i class="ki-filled ki-chart-simple text-3xl text-warning mb-2"></i>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-foreground mb-1">
              Relatórios
            </h3>
            <p class="text-sm text-gray-600 dark:text-muted-foreground">
              Visualize métricas e indicadores do sistema
            </p>
          </div>
        </div>
      </div>

      <!-- Área de Conteúdo Principal -->
      <div class="card bg-white dark:bg-card">
        <div class="card-header">
          <h3 class="card-title text-2xl font-bold">Bem-vindo ao Painel Administrativo</h3>
        </div>
        <div class="card-body">
          <div class="flex flex-col items-center justify-center py-10">
            <i class="ki-filled ki-security-user text-6xl text-gray-400 mb-4"></i>
            <h4 class="text-xl font-semibold text-gray-900 dark:text-foreground mb-2">
              Sistema de Gestão Multi-tenant
            </h4>
            <p class="text-gray-600 dark:text-muted-foreground text-center max-w-md">
              Utilize o menu lateral para gerenciar tenants, usuários e configurações do sistema.
            </p>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';

const breadcrumbs = [
  { label: 'Dashboard' },
];
</script>
