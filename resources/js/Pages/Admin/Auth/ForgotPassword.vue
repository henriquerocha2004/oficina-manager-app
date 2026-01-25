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
              <p class="text-sm text-gray-300">Admin Portal</p>
            </div>
          </div>
        </div>

        <!-- Título e Subtítulo -->
        <div class="mb-12">
          <h2 class="mb-3 text-4xl font-bold text-white">
            Recuperação de<br />Senha Admin
          </h2>
          <p class="text-lg text-gray-300">
            Acesso administrativo seguro e protegido
          </p>
        </div>

        <!-- Informação de segurança -->
        <div class="rounded-lg bg-white/10 p-6 backdrop-blur-sm">
          <div class="mb-3 flex items-center gap-2">
            <svg class="h-6 w-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            <h3 class="font-semibold text-white">Segurança Administrativa</h3>
          </div>
          <p class="text-sm text-gray-300">
            Por segurança, o link expira em 30 minutos e requer autenticação adicional.
          </p>
        </div>
      </AuthBrandedPanel>
    </template>

    <!-- Forgot Password Form -->
    <form
      id="admin_forgot_password_form"
      @submit.prevent="handleSubmit"
    >
      <div class="flex flex-col gap-4">
        <!-- Logo no topo do formulário -->
        <div class="mb-2 flex flex-col items-center gap-3">
          <div class="flex h-14 w-14 items-center justify-center rounded-lg bg-primary">
            <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
          </div>
          <div class="text-center">
            <h2 class="text-xl font-bold text-foreground">Recuperar Senha Admin</h2>
            <p class="text-sm text-muted-foreground">Digite seu email administrativo</p>
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
                Verifique sua caixa de entrada. O link expira em 30 minutos.
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
            placeholder="admin@email.com"
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
            href="/admin"
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

const submitted = ref(false);

const form = useForm({
  email: '',
});

const handleSubmit = () => {
  // Quando o back-end estiver pronto, descomente a linha abaixo
  // form.post('/admin/forgot-password', {
  //   onSuccess: () => {
  //     submitted.value = true;
  //   },
  // });
  
  // Simulação temporária (remover quando integrar com back-end)
  form.processing = true;
  setTimeout(() => {
    form.processing = false;
    submitted.value = true;
  }, 1000);
};
</script>
