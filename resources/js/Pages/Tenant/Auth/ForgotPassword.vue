<template>
  <AuthLayout>
    <template #branded>
      <AuthBrandedPanel custom-class="bg-slate-50 dark:bg-[#0d1117]">
        <div class="flex h-full flex-col items-center justify-center gap-6 px-4 py-8">

          <img
            src="@assets/media/images/login.png"
            alt="Mecânico trabalhando no motor"
            class="w-full max-w-md object-contain rounded-2xl dark:mix-blend-lighten"
          />

          <div class="text-center">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Easy Oficina</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Gestão completa da sua oficina em um só lugar</p>
          </div>

          <div class="flex items-center gap-8 text-center">
            <div class="flex flex-col items-center gap-2">
              <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 dark:bg-white/5">
                <svg class="h-5 w-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
              </div>
              <span class="text-xs text-gray-500 dark:text-gray-400">Ordens de<br/>Serviço</span>
            </div>
            <div class="flex flex-col items-center gap-2">
              <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 dark:bg-white/5">
                <svg class="h-5 w-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>
                </svg>
              </div>
              <span class="text-xs text-gray-500 dark:text-gray-400">Clientes</span>
            </div>
            <div class="flex flex-col items-center gap-2">
              <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 dark:bg-white/5">
                <svg class="h-5 w-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                </svg>
              </div>
              <span class="text-xs text-gray-500 dark:text-gray-400">Estoque</span>
            </div>
          </div>

        </div>
      </AuthBrandedPanel>
    </template>

    <!-- Forgot Password Form -->
    <form
      id="tenant_forgot_password_form"
      @submit.prevent="handleSubmit"
    >
      <div class="flex flex-col gap-4">
        <!-- Logo -->
        <div class="mb-2 flex flex-col items-center gap-3">
          <img :src="logoLight" alt="Easy Oficina" class="h-16 object-contain dark:hidden" />
          <img :src="logoDark" alt="Easy Oficina" class="h-16 object-contain hidden dark:block" />
          <div class="text-center">
            <h2 class="text-xl font-bold text-foreground">Esqueceu sua senha?</h2>
            <p class="text-sm text-muted-foreground">Digite seu email para receber o link de recuperação</p>
          </div>
        </div>

        <!-- Success Message -->
        <div
          v-if="submitted"
          class="rounded-lg border border-primary/20 bg-primary/10 p-4 text-sm text-foreground"
        >
          <div class="flex items-start gap-2">
            <svg class="h-5 w-5 flex-shrink-0 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
              <p class="font-medium">Email enviado com sucesso!</p>
              <p class="mt-1 text-muted-foreground">
                Verifique sua caixa de entrada e spam. O link expira em 60 minutos.
              </p>
            </div>
          </div>
        </div>

        <!-- Email Input -->
        <div class="flex flex-col gap-1">
          <label
            for="email"
            class="kt-form-label font-normal text-mono"
          >
            Email
          </label>
          <Input
            id="email"
            v-model="form.email"
            type="email"
            placeholder="seu@email.com"
            required
            :aria-invalid="form.errors.email ? 'true' : 'false'"
          />
          <span
            v-if="form.errors.email"
            class="text-xs text-destructive"
          >
            {{ form.errors.email }}
          </span>
        </div>

        <!-- Submit Button -->
        <Button
          type="submit"
          variant="primary"
          :disabled="form.processing"
          class="flex grow justify-center"
        >
          {{ form.processing ? 'Enviando...' : 'Enviar Link de Recuperação' }}
        </Button>

        <!-- Back to Login -->
        <div class="text-center">
          <Link
            href="/"
            class="kt-link kt-link-underline text-sm text-muted-foreground hover:text-primary"
          >
            <span class="flex items-center justify-center gap-1">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
              Voltar para o login
            </span>
          </Link>
        </div>
      </div>
    </form>
  </AuthLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import AuthBrandedPanel from '@/Components/Auth/AuthBrandedPanel.vue';
import Button from '@/Components/UI/Button.vue';
import Input from '@/Components/UI/Input.vue';
import logoLight from '@assets/media/images/logo_light.png';
import logoDark from '@assets/media/images/logo_dark.png';

const submitted = ref(false);

const form = useForm({
  email: '',
});

const handleSubmit = () => {
  form.post('/forgot-password', {
    onSuccess: () => {
      submitted.value = true;
    },
  });
};
</script>
