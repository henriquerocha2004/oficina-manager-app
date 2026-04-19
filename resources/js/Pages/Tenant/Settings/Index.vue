<template>
    <TenantLayout title="Configurações" :breadcrumbs="breadcrumbs">
        <div class="kt-container-fixed w-full py-4 px-2">
            <div class="max-w-5xl mx-auto grid grid-cols-1 xl:grid-cols-[320px_minmax(0,1fr)] gap-5">

                <!-- Aside: informações readonly do tenant -->
                <aside class="kt-card h-fit">
                    <div class="kt-card-content p-6 flex flex-col items-center text-center gap-4">
                        <!-- Logo preview -->
                        <div class="w-24 h-24 rounded-xl border border-border overflow-hidden flex items-center justify-center bg-gray-50 dark:bg-gray-800">
                            <img
                                v-if="logoPreviewUrl"
                                :src="logoPreviewUrl"
                                alt="Logomarca"
                                class="w-full h-full object-contain"
                            />
                            <i v-else class="ki-outline ki-abstract-26 text-4xl text-gray-300 dark:text-gray-600"></i>
                        </div>

                        <div class="space-y-1">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                                {{ tenant?.name }}
                            </h2>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 tracking-wider">
                                {{ tenant?.domain }}
                            </span>
                        </div>

                        <div class="w-full grid grid-cols-2 gap-3 pt-2">
                            <div class="rounded-xl border border-border px-4 py-3 text-left">
                                <div class="text-[11px] uppercase tracking-wide text-gray-400 dark:text-gray-500">Status</div>
                                <div class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ tenant?.is_active ? 'Ativo' : 'Inativo' }}
                                </div>
                            </div>
                            <div class="rounded-xl border border-border px-4 py-3 text-left">
                                <div class="text-[11px] uppercase tracking-wide text-gray-400 dark:text-gray-500">Email</div>
                                <div class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                    {{ tenant?.email ?? '—' }}
                                </div>
                            </div>
                            <div class="rounded-xl border border-border px-4 py-3 text-left">
                                <div class="text-[11px] uppercase tracking-wide text-gray-400 dark:text-gray-500">Documento</div>
                                <div class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ tenant?.document ?? '—' }}
                                </div>
                            </div>
                            <div class="rounded-xl border border-border px-4 py-3 text-left">
                                <div class="text-[11px] uppercase tracking-wide text-gray-400 dark:text-gray-500">Telefone</div>
                                <div class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ tenant?.phone ?? '—' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>

                <!-- Main: formulário de configurações -->
                <section class="kt-card">
                    <div class="kt-card-content p-6 md:p-8">
                        <div class="mb-6">
                            <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Configurações</h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                Personalize as informações e identidade visual da sua oficina.
                            </p>
                        </div>

                        <!-- Tabs (estrutura extensível para futuras configs) -->
                        <div class="flex gap-1 mb-6 border-b border-border">
                            <button
                                v-for="tab in tabs"
                                :key="tab.key"
                                type="button"
                                class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors"
                                :class="activeTab === tab.key
                                    ? 'border-primary text-primary'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                                @click="activeTab = tab.key"
                            >
                                {{ tab.label }}
                            </button>
                        </div>

                        <!-- Tab: Identidade Visual -->
                        <div v-if="activeTab === 'identity'">
                            <div class="flex flex-col gap-3 rounded-2xl border border-border p-4 md:p-5">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Logomarca</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        A logomarca será exibida no topo do menu lateral e no cabeçalho mobile.
                                    </p>
                                </div>

                                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                                    <!-- Preview -->
                                    <div class="w-20 h-20 rounded-lg border border-border overflow-hidden flex items-center justify-center bg-gray-50 dark:bg-gray-800 flex-shrink-0">
                                        <img
                                            v-if="logoPreviewUrl"
                                            :src="logoPreviewUrl"
                                            alt="Preview da logomarca"
                                            class="w-full h-full object-contain"
                                        />
                                        <i v-else class="ki-outline ki-abstract-26 text-3xl text-gray-300 dark:text-gray-600"></i>
                                    </div>

                                    <div class="flex-1 flex flex-col gap-3">
                                        <input
                                            ref="logoInputRef"
                                            type="file"
                                            accept="image/jpeg,image/jpg,image/png,image/webp"
                                            class="kt-input w-full"
                                            @change="onLogoChange"
                                        />
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                Formatos: JPG, PNG, WEBP (max. 5MB)
                                            </p>
                                            <button
                                                v-if="showRemoveButton"
                                                type="button"
                                                class="kt-btn kt-btn-sm kt-btn-ghost text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20"
                                                :disabled="isSubmitting"
                                                @click="handleRemoveLogo"
                                            >
                                                Remover logomarca
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-5">
                                <button
                                    type="button"
                                    class="kt-btn kt-btn-ghost"
                                    :disabled="isSubmitting"
                                    @click="resetForm"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="button"
                                    class="kt-btn kt-btn-primary"
                                    :disabled="isSubmitting || !pendingLogoFile"
                                    @click="handleSave"
                                >
                                    {{ isSubmitting ? 'Salvando...' : 'Salvar Alterações' }}
                                </button>
                            </div>
                        </div>

                    </div>
                </section>
            </div>
        </div>
    </TenantLayout>
</template>

<script setup>
import { computed, onUnmounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { useToast } from '@/Shared/composables/useToast.js';
import { uploadLogo, removeLogo } from '@/services/settingService.js';

const props = defineProps({
    tenant:   { type: Object, required: true },
    settings: { type: Object, required: true },
});

const toast = useToast();
const logoInputRef    = ref(null);
const pendingLogoFile = ref(null);
const activeObjectUrl = ref(null);
const isSubmitting    = ref(false);
const activeTab       = ref('identity');

const tabs = [
    { key: 'identity', label: 'Identidade Visual' },
];

const breadcrumbs = [{ label: 'Configurações' }];

const logoPreviewUrl = computed(() => {
    if (activeObjectUrl.value) return activeObjectUrl.value;
    return props.settings?.logo_url ?? null;
});

const showRemoveButton = computed(() =>
    !!(props.settings?.logo_url || activeObjectUrl.value)
);

function onLogoChange(event) {
    const file = event.target.files?.[0] ?? null;
    pendingLogoFile.value = file;

    if (activeObjectUrl.value) {
        URL.revokeObjectURL(activeObjectUrl.value);
        activeObjectUrl.value = null;
    }

    if (file) {
        activeObjectUrl.value = URL.createObjectURL(file);
    }
}

function resetForm() {
    pendingLogoFile.value = null;

    if (activeObjectUrl.value) {
        URL.revokeObjectURL(activeObjectUrl.value);
        activeObjectUrl.value = null;
    }

    if (logoInputRef.value) {
        logoInputRef.value.value = '';
    }
}

async function handleSave() {
    if (!pendingLogoFile.value) return;

    isSubmitting.value = true;
    const result = await uploadLogo(pendingLogoFile.value);
    isSubmitting.value = false;

    if (result.success) {
        toast.success('Logomarca atualizada com sucesso!');
        resetForm();
        router.reload({ preserveState: true, preserveScroll: true });
    } else {
        toast.error('Erro ao fazer upload da logomarca. Tente novamente.');
    }
}

async function handleRemoveLogo() {
    isSubmitting.value = true;
    const result = await removeLogo();
    isSubmitting.value = false;

    if (result.success) {
        toast.success('Logomarca removida com sucesso!');
        resetForm();
        router.reload({ preserveState: true, preserveScroll: true });
    } else {
        toast.error('Erro ao remover logomarca. Tente novamente.');
    }
}

onUnmounted(() => {
    if (activeObjectUrl.value) {
        URL.revokeObjectURL(activeObjectUrl.value);
    }
});
</script>
