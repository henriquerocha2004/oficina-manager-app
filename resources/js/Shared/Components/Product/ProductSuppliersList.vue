<template>
    <div>
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-md font-semibold text-gray-900 dark:text-gray-100">
                Fornecedores Vinculados
            </h3>
            <button
                type="button"
                class="kt-btn kt-btn-sm kt-btn-primary"
                @click="emit('add')"
            >
                <i class="ki-filled ki-plus"></i>
                Adicionar Fornecedor
            </button>
        </div>

        <div v-if="suppliers.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
            Nenhum fornecedor vinculado
        </div>

        <div v-else class="grid gap-4">
            <ProductSupplierCard
                v-for="supplier in suppliers"
                :key="supplier.id"
                :supplier="supplier"
                :format-currency="formatCurrency"
                @edit="emit('edit', $event)"
                @remove="emit('remove', $event)"
            />
        </div>
    </div>
</template>

<script setup>
import ProductSupplierCard from './ProductSupplierCard.vue';

const props = defineProps({
    suppliers: {
        type: Array,
        required: true,
    },
    formatCurrency: {
        type: Function,
        required: true,
    },
});

const emit = defineEmits(['add', 'edit', 'remove']);
</script>
