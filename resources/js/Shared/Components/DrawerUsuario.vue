<template>
  <teleport to="body">
    <Transition name="drawer">
      <div
        v-if="open"
        class="fixed inset-0 z-50 flex items-center justify-end"
        style="background: rgba(0, 0, 0, 0.5);"
      >
        <div class="w-full max-w-105 h-full bg-background border-l border-border shadow-xl flex flex-col">
          <div class="flex items-center justify-between px-5 py-4 border-b border-border">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
              {{ isEdit ? 'Editar Usuario' : 'Novo Usuario' }}
            </h2>
            <button class="kt-btn kt-btn-sm kt-btn-icon" @click="$emit('close')">
              <i class="ki-filled ki-cross"></i>
            </button>
          </div>

          <form
            class="flex flex-col gap-4 p-5 flex-1 overflow-y-auto"
            @submit.prevent="submitHandler"
          >
            <FormField name="name" label="Nome" v-slot="{ field, errors }">
              <input v-bind="field" class="kt-input w-full" placeholder="Nome completo" />
              <FormError :errors="errors" />
            </FormField>

            <FormField name="email" label="Email" v-slot="{ field, errors }">
              <input v-bind="field" class="kt-input w-full" placeholder="Email" />
              <FormError :errors="errors" />
            </FormField>

            <FormField name="role" label="Perfil" v-slot="{ field, errors }">
              <select v-bind="field" class="kt-select w-full">
                <option value="">Selecione um perfil</option>
                <option v-for="role in userRoles" :key="role.value" :value="role.value">
                  {{ role.label }}
                </option>
              </select>
              <FormError :errors="errors" />
            </FormField>

            <FormField name="password" label="Senha" v-slot="{ field, errors }">
              <input
                v-bind="field"
                type="password"
                class="kt-input w-full"
                :placeholder="isEdit ? 'Preencha somente para alterar' : 'Senha de acesso'"
              />
              <FormError :errors="errors" />
            </FormField>

            <FormField name="password_confirmation" label="Confirmar senha" v-slot="{ field, errors }">
              <input
                v-bind="field"
                type="password"
                class="kt-input w-full"
                :placeholder="isEdit ? 'Repita a nova senha' : 'Confirme a senha'"
              />
              <FormError :errors="errors" />
            </FormField>

            <div class="flex flex-col gap-2">
              <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
                Foto de perfil
              </label>
              <div class="flex items-center gap-3">
                <img
                  :src="avatarPreviewUrl"
                  alt="Preview do avatar"
                  class="w-14 h-14 rounded-full object-cover border border-border"
                />
                <span class="text-xs text-gray-500 dark:text-gray-400">
                  A imagem sera exibida no avatar do sidebar.
                </span>
              </div>
              <input
                ref="avatarInputRef"
                type="file"
                accept="image/jpeg,image/jpg,image/png,image/webp"
                class="kt-input w-full"
                @change="onAvatarChange"
              />
              <div class="flex items-center justify-between gap-3">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                  Formatos: JPG, PNG, WEBP (max. 10MB)
                </p>
                <button
                  v-if="showRemoveAvatarButton"
                  type="button"
                  class="kt-btn kt-btn-sm kt-btn-ghost text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20"
                  @click="removeAvatar"
                >
                  Remover foto
                </button>
              </div>
            </div>

            <FormField name="is_active" label="Status" v-slot="{ field, errors }">
              <label class="flex items-center gap-2 cursor-pointer">
                <input
                  type="checkbox"
                  :checked="field.value"
                  @change="setFieldValue('is_active', $event.target.checked)"
                  class="kt-checkbox"
                />
                <span class="text-gray-900 dark:text-gray-100">Usuario ativo</span>
              </label>
              <FormError :errors="errors" />
            </FormField>

            <div class="flex justify-end gap-2 mt-4">
              <button
                type="button"
                class="kt-btn kt-btn-ghost text-black! hover:text-black! hover:bg-gray-100 dark:text-gray-200! dark:hover:text-gray-200! dark:hover:bg-gray-700"
                @click="$emit('close')"
              >
                Cancelar
              </button>
              <button type="submit" class="kt-btn kt-btn-primary">
                Salvar
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>
  </teleport>
</template>

<script setup>
import { useForm } from 'vee-validate';
import { computed, ref, watch, onUnmounted } from 'vue';
import * as yup from 'yup';
import FormField from './FormField.vue';
import FormError from './FormError.vue';
import blankAvatar from '@assets/media/avatars/blank.png';
import { userRoles } from '@/Data/userRoles.js';

const props = defineProps({
  open: Boolean,
  isEdit: Boolean,
  user: Object,
});

const emit = defineEmits(['close', 'submit']);
const avatarPreviewUrl = ref(blankAvatar);
const activeObjectUrl = ref(null);
const removeAvatarFlag = ref(false);
const avatarInputRef = ref(null);

const schema = yup.object({
    name: yup.string().required('Nome e obrigatorio').max(255, 'Maximo de 255 caracteres'),
    email: yup.string().email('Email invalido').required('Email e obrigatorio'),
  role: yup.string().required('Perfil e obrigatorio'),
    password: yup
      .string()
      .nullable()
      .transform((value) => (value === '' ? null : value))
      .test('password-required', 'Senha e obrigatoria', (value) => {
        if (props.isEdit) {
          return true;
        }

        return !!value;
      })
      .test('password-min', 'Senha deve ter no minimo 8 caracteres', (value) => {
        if (!value) {
          return true;
        }

        return value.length >= 8;
      }),
    password_confirmation: yup
      .string()
      .nullable()
      .transform((value) => (value === '' ? null : value))
      .test('password-confirmation-required', 'Confirme a senha', function (value) {
        const { password } = this.parent;
        const requiresConfirmation = !props.isEdit || !!password;

        if (!requiresConfirmation) {
          return true;
        }

        return !!value;
      })
      .test('password-confirmation-match', 'A confirmacao nao confere', function (value) {
        const { password } = this.parent;

        if (!password && props.isEdit) {
          return true;
        }

        return value === password;
      }),
    avatar: yup.mixed().nullable(),
    remove_avatar: yup.boolean().default(false),
    is_active: yup.boolean().default(true),
});

const showRemoveAvatarButton = computed(() => {
  return !!props.user?.avatar || !!activeObjectUrl.value || removeAvatarFlag.value;
});

const {
  handleSubmit,
  setValues,
  resetForm,
  setFieldValue,
} = useForm({
  validationSchema: schema,
  initialValues: computed(() => {
    if (props.user) {
      return {
        name: props.user.name ?? '',
        email: props.user.email ?? '',
        role: props.user.role ?? '',
        password: '',
        password_confirmation: '',
        avatar: null,
        is_active: props.user.is_active ?? true,
      };
    }

    return {
      name: '',
      email: '',
      role: '',
      password: '',
      password_confirmation: '',
      avatar: null,
      is_active: true,
    };
  }),
});

watch(
  () => props.user,
  (val) => {
    if (activeObjectUrl.value) {
      URL.revokeObjectURL(activeObjectUrl.value);
      activeObjectUrl.value = null;
    }

    if (val) {
      avatarPreviewUrl.value = val.avatar || blankAvatar;
      removeAvatarFlag.value = false;
      setValues({
        name: val.name ?? '',
        email: val.email ?? '',
        role: val.role ?? '',
        password: '',
        password_confirmation: '',
        avatar: null,
        remove_avatar: false,
        is_active: val.is_active ?? true,
      });
      return;
    }

    avatarPreviewUrl.value = blankAvatar;
    removeAvatarFlag.value = false;
    resetForm();
  },
  { immediate: true }
);

function onAvatarChange(event) {
  const file = event.target.files?.[0] ?? null;
  setFieldValue('avatar', file);
  setFieldValue('remove_avatar', false);
  removeAvatarFlag.value = false;

  if (activeObjectUrl.value) {
    URL.revokeObjectURL(activeObjectUrl.value);
    activeObjectUrl.value = null;
  }

  if (file) {
    const objectUrl = URL.createObjectURL(file);
    activeObjectUrl.value = objectUrl;
    avatarPreviewUrl.value = objectUrl;
    return;
  }

  avatarPreviewUrl.value = props.user?.avatar || blankAvatar;
}

function removeAvatar() {
  setFieldValue('avatar', null);
  setFieldValue('remove_avatar', true);
  removeAvatarFlag.value = true;

  if (avatarInputRef.value) {
    avatarInputRef.value.value = '';
  }

  if (activeObjectUrl.value) {
    URL.revokeObjectURL(activeObjectUrl.value);
    activeObjectUrl.value = null;
  }

  avatarPreviewUrl.value = blankAvatar;
}

onUnmounted(() => {
  if (activeObjectUrl.value) {
    URL.revokeObjectURL(activeObjectUrl.value);
  }
});

const submitHandler = handleSubmit((values) => {
  const payload = {
    name: values.name,
    email: values.email,
    role: values.role,
    is_active: values.is_active,
    avatar: values.avatar ?? null,
    remove_avatar: values.remove_avatar ?? false,
  };

  if (values.password) {
    payload.password = values.password;
    payload.password_confirmation = values.password_confirmation;
  }

  emit('submit', payload);
});
</script>

<style scoped>
.drawer-enter-active,
.drawer-leave-active {
  transition: opacity 0.3s ease;
}

.drawer-enter-active > div,
.drawer-leave-active > div {
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.drawer-enter-from,
.drawer-leave-to {
  opacity: 0;
}

.drawer-enter-from > div,
.drawer-leave-to > div {
  transform: translateX(100%);
}

.drawer-enter-to,
.drawer-leave-from {
  opacity: 1;
}

.drawer-enter-to > div,
.drawer-leave-from > div {
  transform: translateX(0);
}
</style>
