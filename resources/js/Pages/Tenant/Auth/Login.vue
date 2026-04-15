<template>
  <AuthLayout>
    <!-- Lado direito: ilustração de oficina -->
    <template #branded>
      <AuthBrandedPanel custom-class="bg-slate-50 dark:bg-[#0d1117]">
        <div class="flex h-full flex-col items-center justify-center gap-6 px-4 py-8">

          <!-- Ilustração: mecânico trabalhando no motor com capô aberto -->
          <img
            src="@assets/media/images/login.png"
            alt="Mecânico trabalhando no motor"
            class="w-full max-w-md object-contain rounded-2xl dark:mix-blend-lighten"
          />

          <!-- Texto -->
          <div class="text-center">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Easy Oficina</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Gestão operacional da sua oficina em um só lugar</p>
          </div>

          <!-- Ícones de funcionalidades -->
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
          </div>

        </div>
      </AuthBrandedPanel>
    </template>

    <!-- Login Form -->
    <form
      id="tenant_login_form"
      @submit.prevent="handleSubmit"
    >
      <div class="flex flex-col gap-3.5">
        <!-- Logo no topo do formulário -->
        <div class="mb-2 flex flex-col items-center gap-3">
          <img :src="logoLight" alt="Easy Oficina" class="h-16 object-contain dark:hidden" />
          <img :src="logoDark" alt="Easy Oficina" class="h-16 object-contain hidden dark:block" />
        </div>

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

        <!-- Password Input -->
        <div class="flex flex-col gap-1">
          <div class="flex items-center justify-between">
            <label
              for="password"
              class="kt-form-label font-normal text-mono"
            >
              Senha
            </label>
          </div>
          <PasswordInput
            id="password"
            v-model="form.password"
            placeholder="Digite sua senha"
            required
            :aria-invalid="form.errors.password ? 'true' : 'false'"
          />
          <span
            v-if="form.errors.password"
            class="text-xs text-destructive"
          >
            {{ form.errors.password }}
          </span>
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between">
          <Checkbox
            id="remember"
            v-model="form.remember"
            size="sm"
          >
            Lembrar-me
          </Checkbox>
          <Link
            href="/forgot-password"
            class="kt-link kt-link-underline text-sm text-primary hover:text-primary/90"
          >
            Esqueceu sua senha?
          </Link>
        </div>

        <!-- Submit Button -->
        <Button
          type="submit"
          variant="primary"
          :disabled="form.processing"
          class="flex grow justify-center"
        >
          {{ form.processing ? 'Entrando...' : 'Entrar' }}
        </Button>
      </div>
    </form>
  </AuthLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import AuthBrandedPanel from '@/Components/Auth/AuthBrandedPanel.vue';
import Button from '@/Components/UI/Button.vue';
import Input from '@/Components/UI/Input.vue';
import Checkbox from '@/Components/UI/Checkbox.vue';
import PasswordInput from '@/Components/Auth/PasswordInput.vue';
import logoLight from '@assets/media/images/logo_light.png';
import logoDark from '@assets/media/images/logo_dark.png';

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const handleSubmit = () => {
  form.post('/login', {
    onFinish: () => {
      if (Object.keys(form.errors).length > 0) {
        form.password = '';
      }
    },
  });
};
</script>
