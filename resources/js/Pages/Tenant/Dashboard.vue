<template>
  <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
    <!-- Header -->
    <header class="bg-white dark:bg-gray-800 shadow">
      <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between">
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Dashboard
          </h1>
          <form
            method="POST"
            @submit.prevent="logout"
          >
            <button
              type="submit"
              class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
            >
              Sair
            </button>
          </form>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <div class="rounded-lg border-4 border-dashed border-gray-200 p-6 dark:border-gray-700">
          <h2 class="mb-4 text-2xl font-bold text-gray-900 dark:text-white">
            Bem-vindo, {{ $page.props.auth.user?.name }}!
          </h2>
          <p class="text-gray-600 dark:text-gray-400">
            Gerencie sua oficina de forma eficiente.
          </p>

          <!-- Quick Stats -->
          <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
              <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Clientes Ativos
              </h3>
              <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                --
              </p>
            </div>
            <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
              <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Serviços do Mês
              </h3>
              <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                --
              </p>
            </div>
            <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
              <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Faturamento
              </h3>
              <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                R$ --
              </p>
            </div>
            <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
              <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Agendamentos Hoje
              </h3>
              <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                --
              </p>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const logoutForm = useForm({});

const logout = () => {
  logoutForm.post('/logout');
};
</script>
