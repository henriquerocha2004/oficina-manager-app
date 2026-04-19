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
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Gestão operacional da sua oficina em um só lugar</p>
          </div>
        </div>
      </AuthBrandedPanel>
    </template>

    <form @submit.prevent="handleSubmit">
      <div class="flex flex-col gap-3.5">
        <div
          v-if="page.props.flash?.error"
          class="rounded-lg border border-destructive/20 bg-destructive/10 px-4 py-3 text-sm text-destructive"
        >
          {{ page.props.flash.error }}
        </div>

        <div class="mb-2 flex flex-col items-center gap-3">
          <img :src="logoLight" alt="Easy Oficina" class="h-16 object-contain dark:hidden" />
          <img :src="logoDark" alt="Easy Oficina" class="h-16 object-contain hidden dark:block" />
          <div class="text-center">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Crie sua senha</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Este é seu primeiro acesso. Por segurança, defina uma nova senha antes de continuar.
            </p>
          </div>
        </div>

        <div class="flex flex-col gap-1">
          <label for="password" class="kt-form-label font-normal text-mono">
            Nova Senha
          </label>
          <PasswordInput
            id="password"
            v-model="form.password"
            placeholder="Mínimo 8 caracteres"
            required
            :aria-invalid="form.errors.password ? 'true' : 'false'"
          />
          <span v-if="form.errors.password" class="text-xs text-destructive">
            {{ form.errors.password }}
          </span>
        </div>

        <div class="flex flex-col gap-1">
          <label for="password_confirmation" class="kt-form-label font-normal text-mono">
            Confirmar Nova Senha
          </label>
          <PasswordInput
            id="password_confirmation"
            v-model="form.password_confirmation"
            placeholder="Repita a nova senha"
            required
            :aria-invalid="form.errors.password_confirmation ? 'true' : 'false'"
          />
          <span v-if="form.errors.password_confirmation" class="text-xs text-destructive">
            {{ form.errors.password_confirmation }}
          </span>
        </div>

        <Button
          type="submit"
          variant="primary"
          :disabled="form.processing"
          class="flex grow justify-center"
        >
          {{ form.processing ? 'Salvando...' : 'Definir Senha e Continuar' }}
        </Button>
      </div>
    </form>
  </AuthLayout>
</template>

<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import AuthBrandedPanel from '@/Components/Auth/AuthBrandedPanel.vue';
import Button from '@/Components/UI/Button.vue';
import PasswordInput from '@/Components/Auth/PasswordInput.vue';
import logoLight from '@assets/media/images/logo_light.png';
import logoDark from '@assets/media/images/logo_dark.png';

const page = usePage();

const form = useForm({
  password: '',
  password_confirmation: '',
});

const handleSubmit = () => {
  form.post('/change-password', {
    onFinish: () => {
      if (Object.keys(form.errors).length > 0) {
        form.password = '';
        form.password_confirmation = '';
      }
    },
  });
};
</script>
