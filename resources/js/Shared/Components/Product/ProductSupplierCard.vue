<template>
    <div
        class="relative bg-white dark:bg-card border border-border rounded-lg p-4 hover:shadow-md transition-shadow"
    >
        <div
            v-if="supplier.pivot.is_preferred"
            class="absolute top-3 right-3 flex items-center gap-1 px-2 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 text-xs font-medium rounded"
        >
            <i class="ki-filled ki-check-circle text-sm"></i>
            Preferencial
        </div>

        <h4 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3 pr-24">
            {{ supplier.name }}
        </h4>

        <div class="grid grid-cols-2 gap-x-4 gap-y-2 mb-3">
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Preço de Custo</p>
                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                    {{ formatCurrency(supplier.pivot.cost_price) }}
                </p>
            </div>

            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Prazo de Entrega</p>
                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                    {{ supplier.pivot.lead_time_days ? `${supplier.pivot.lead_time_days} dias` : '-' }}
                </p>
            </div>

            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">SKU do Fornecedor</p>
                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                    {{ supplier.pivot.supplier_sku || '-' }}
                </p>
            </div>

            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Qtd. Mínima</p>
                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                    {{ supplier.pivot.min_order_quantity || '-' }}
                </p>
            </div>
        </div>

        <div v-if="supplier.pivot.notes" class="mb-3">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Observações</p>
            <p class="text-sm text-gray-700 dark:text-gray-300 line-clamp-2">
                {{ supplier.pivot.notes }}
            </p>
        </div>

        <div class="flex gap-2 pt-2 border-t border-border">
            <button
                type="button"
                class="kt-btn kt-btn-sm kt-btn-ghost flex-1"
                @click="emit('edit', supplier)"
            >
                <i class="ki-filled ki-pencil"></i>
                Editar
            </button>
            <button
                type="button"
                class="kt-btn kt-btn-sm kt-btn-light-danger"
                @click="emit('remove', supplier.id)"
            >
                <i class="ki-filled ki-trash"></i>
                Remover
            </button>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    supplier: {
        type: Object,
        required: true,
    },
    formatCurrency: {
        type: Function,
        required: true,
    },
});

const emit = defineEmits(['edit', 'remove']);
</script>
