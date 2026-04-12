<template>
    <TenantLayout title="Minha Conta" :breadcrumbs="breadcrumbs">
        <div class="kt-container-fixed w-full py-4 px-2">
            <div class="max-w-5xl mx-auto grid grid-cols-1 xl:grid-cols-[320px_minmax(0,1fr)] gap-5">
                <aside class="kt-card h-fit">
                    <div class="kt-card-content p-6 flex flex-col items-center text-center gap-4">
                        <img
                            :src="avatarPreviewUrl"
                            :alt="currentUser?.name"
                            class="w-28 h-28 rounded-full object-cover border border-border shadow-sm"
                        />

                        <div class="space-y-1">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                                {{ currentUser?.name }}
                            </h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 break-all">
                                {{ currentUser?.email }}
                            </p>
                        </div>

                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium"
                            :class="currentRoleBadgeClass"
                        >
                            {{ currentRoleLabel }}
                        </span>

                        <div class="w-full grid grid-cols-2 gap-3 pt-2">
                            <div class="rounded-xl border border-border px-4 py-3 text-left">
                                <div class="text-[11px] uppercase tracking-wide text-gray-400 dark:text-gray-500">
                                    Perfil
                                </div>
                                <div class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ currentRoleLabel }}
                                </div>
                            </div>
                            <div class="rounded-xl border border-border px-4 py-3 text-left">
                                <div class="text-[11px] uppercase tracking-wide text-gray-400 dark:text-gray-500">
                                    Status
                                </div>
                                <div class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ currentUser?.is_active ? 'Ativo' : 'Inativo' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>

                <section class="kt-card">
                    <div class="kt-card-content p-6 md:p-8">
                        <div class="mb-6">
                            <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Dados da Conta</h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                Atualize seu nome, troque sua senha e gerencie sua foto de perfil.
                            </p>
                        </div>

                        <form class="grid grid-cols-1 md:grid-cols-2 gap-5" @submit.prevent="submitHandler">
                            <FormField name="name" label="Nome" v-slot="{ field, errors }">
                                <input v-bind="field" class="kt-input w-full" placeholder="Seu nome" />
                                <FormError :errors="errors" />
                            </FormField>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                    Email
                                </label>
                                <input :value="currentUser?.email || ''" class="kt-input w-full" disabled />
                                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                    O email nao pode ser alterado nesta tela.
                                </p>
                            </div>

                            <FormField name="password" label="Nova Senha" v-slot="{ field, errors }">
                                <input
                                    v-bind="field"
                                    type="password"
                                    class="kt-input w-full"
                                    placeholder="Preencha somente para alterar"
                                />
                                <FormError :errors="errors" />
                            </FormField>

                            <FormField name="password_confirmation" label="Confirmar Nova Senha" v-slot="{ field, errors }">
                                <input
                                    v-bind="field"
                                    type="password"
                                    class="kt-input w-full"
                                    placeholder="Repita a nova senha"
                                />
                                <FormError :errors="errors" />
                            </FormField>

                            <div class="md:col-span-2 flex flex-col gap-3 rounded-2xl border border-border p-4 md:p-5">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Foto de Perfil</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        A imagem escolhida sera exibida no menu da conta e no footer lateral.
                                    </p>
                                </div>

                                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                                    <img
                                        :src="avatarPreviewUrl"
                                        alt="Preview do avatar"
                                        class="w-20 h-20 rounded-full object-cover border border-border"
                                    />

                                    <div class="flex-1 flex flex-col gap-3">
                                        <input
                                            ref="avatarInputRef"
                                            type="file"
                                            accept="image/jpeg,image/jpg,image/png,image/webp"
                                            class="kt-input w-full"
                                            @change="onAvatarChange"
                                        />

                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
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
                                </div>
                            </div>

                            <div class="md:col-span-2 flex items-center justify-end gap-3 pt-2">
                                <button type="button" class="kt-btn kt-btn-ghost" @click="resetToCurrentUser">
                                    Cancelar
                                </button>
                                <button type="submit" class="kt-btn kt-btn-primary" :disabled="isSubmitting">
                                    {{ isSubmitting ? 'Salvando...' : 'Salvar Alteracoes' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </section>
            </div>
        </div>
    </TenantLayout>
</template>

<script setup>
import { computed, onUnmounted, ref, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { useForm } from 'vee-validate';
import * as yup from 'yup';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import FormField from '@/Shared/Components/FormField.vue';
import FormError from '@/Shared/Components/FormError.vue';
import { useToast } from '@/Shared/composables/useToast.js';
import { updateAccount } from '@/services/accountService.js';
import { getUserRoleBadgeClass, getUserRoleLabel } from '@/Data/userRoles.js';
import blankAvatar from '@assets/media/avatars/blank.png';

const page = usePage();
const toast = useToast();
const avatarPreviewUrl = ref(blankAvatar);
const activeObjectUrl = ref(null);
const removeAvatarFlag = ref(false);
const avatarInputRef = ref(null);
const isSubmitting = ref(false);

const breadcrumbs = [{ label: 'Minha Conta' }];

const currentUser = computed(() => page.props.auth?.user || null);
const currentRoleLabel = computed(() => getUserRoleLabel(currentUser.value?.role || ''));
const currentRoleBadgeClass = computed(() => getUserRoleBadgeClass(currentUser.value?.role || ''));

const schema = yup.object({
    name: yup.string().required('Nome e obrigatorio').max(255, 'Maximo de 255 caracteres'),
    password: yup
        .string()
        .nullable()
        .transform((value) => (value === '' ? null : value))
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

            if (!password) {
                return true;
            }

            return !!value;
        })
        .test('password-confirmation-match', 'A confirmacao nao confere', function (value) {
            const { password } = this.parent;

            if (!password) {
                return true;
            }

            return value === password;
        }),
    avatar: yup.mixed().nullable(),
    remove_avatar: yup.boolean().default(false),
});

const showRemoveAvatarButton = computed(() => {
    return !!currentUser.value?.avatar || !!activeObjectUrl.value || removeAvatarFlag.value;
});

const { handleSubmit, resetForm, setValues, setFieldValue } = useForm({
    validationSchema: schema,
    initialValues: {
        name: '',
        password: '',
        password_confirmation: '',
        avatar: null,
        remove_avatar: false,
    },
});

watch(
    currentUser,
    (user) => {
        if (activeObjectUrl.value) {
            URL.revokeObjectURL(activeObjectUrl.value);
            activeObjectUrl.value = null;
        }

        avatarPreviewUrl.value = user?.avatar || blankAvatar;
        removeAvatarFlag.value = false;

        setValues({
            name: user?.name ?? '',
            password: '',
            password_confirmation: '',
            avatar: null,
            remove_avatar: false,
        });

        if (avatarInputRef.value) {
            avatarInputRef.value.value = '';
        }
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

    avatarPreviewUrl.value = currentUser.value?.avatar || blankAvatar;
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

function resetToCurrentUser() {
    resetForm({
        values: {
            name: currentUser.value?.name ?? '',
            password: '',
            password_confirmation: '',
            avatar: null,
            remove_avatar: false,
        },
    });

    if (avatarInputRef.value) {
        avatarInputRef.value.value = '';
    }

    if (activeObjectUrl.value) {
        URL.revokeObjectURL(activeObjectUrl.value);
        activeObjectUrl.value = null;
    }

    removeAvatarFlag.value = false;
    avatarPreviewUrl.value = currentUser.value?.avatar || blankAvatar;
}

const submitHandler = handleSubmit(async (values) => {
    isSubmitting.value = true;

    const payload = {
        name: values.name,
        avatar: values.avatar ?? null,
        remove_avatar: values.remove_avatar ?? false,
    };

    if (values.password) {
        payload.password = values.password;
        payload.password_confirmation = values.password_confirmation;
    }

    const result = await updateAccount(payload);

    isSubmitting.value = false;

    if (!result.success) {
        toast.error('Erro ao atualizar conta: ' + (result.error.response?.data?.message || result.error.message));
        return;
    }

    toast.success('Conta atualizada com sucesso.');
    await router.reload({ preserveState: true, preserveScroll: true });
    resetToCurrentUser();
});

onUnmounted(() => {
    if (activeObjectUrl.value) {
        URL.revokeObjectURL(activeObjectUrl.value);
    }
});
</script>
