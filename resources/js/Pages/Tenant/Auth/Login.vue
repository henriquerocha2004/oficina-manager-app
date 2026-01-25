<template>
  <AuthLayout>
    <!-- Customizar lado direito via slot "branded" -->
    <template #branded>
      <AuthBrandedPanel
        :overlay="true"
        overlay-color="rgba(0, 0, 0, 0.7)"
      >
        <!-- Logo -->
        <div class="mb-8">
          <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-primary">
              <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            </div>
            <div>
              <h1 class="text-2xl font-bold text-white">Auto Mecânica Pro</h1>
              <p class="text-sm text-gray-300">Portal de Acesso Seguro</p>
            </div>
          </div>
        </div>

        <!-- Título e Subtítulo -->
        <div class="mb-12">
          <h2 class="mb-3 text-4xl font-bold text-white">
            Excelência em Serviços<br />Automotivos
          </h2>
          <p class="text-lg text-gray-300">
            Tecnologia e expertise para cuidar do seu veículo
          </p>
        </div>

        <!-- Estatísticas -->
        <div class="grid grid-cols-3 gap-8">
          <div class="text-center">
            <div class="mb-2 text-4xl font-bold text-primary">15+</div>
            <div class="text-sm text-gray-300">Anos de Experiência</div>
          </div>
          <div class="text-center">
            <div class="mb-2 text-4xl font-bold text-primary">5000+</div>
            <div class="text-sm text-gray-300">Clientes Satisfeitos</div>
          </div>
          <div class="text-center">
            <div class="mb-2 text-4xl font-bold text-primary">24/7</div>
            <div class="text-sm text-gray-300">Suporte</div>
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
          <div class="flex h-14 w-14 items-center justify-center rounded-lg bg-primary">
            <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
          </div>
          <div class="text-center">
            <h2 class="text-xl font-bold text-foreground">Auto Mecânica Pro</h2>
            <p class="text-sm text-muted-foreground">Acesse seu painel e gerencie sua oficina</p>
          </div>
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
