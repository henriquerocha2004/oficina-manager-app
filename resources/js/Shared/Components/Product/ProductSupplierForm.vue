<template>
    <div>
        <div class="mb-4">
            <h3 class="text-md font-semibold text-gray-900 dark:text-gray-100">
                {{ mode === 'add' ? 'Adicionar Fornecedor' : 'Editar Fornecedor' }}
            </h3>
        </div>

        <form
            @submit.prevent="emit('submit')"
            class="flex flex-col gap-4"
        >
            <div class="relative">
                <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                    Fornecedor <span class="text-red-500">*</span>
                </label>
                <input
                    :value="supplierSearch"
                    type="text"
                    class="kt-input w-full"
                    :class="{ 'border-red-500': formErrors.supplier_id }"
                    placeholder="Buscar fornecedor..."
                    :disabled="mode === 'edit'"
                    @input="emit('search-input', $event.target.value)"
                    @focus="emit('focus-supplier')"
                    @blur="emit('blur-supplier')"
                />

                <div
                    v-if="showDropdown && !selectedSupplier && (loadingSuppliers || filteredSuppliers.length > 0)"
                    class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg max-h-60 overflow-auto"
                >
                    <template v-if="loadingSuppliers">
                        <div
                            v-for="i in 3"
                            :key="'skeleton-' + i"
                            class="px-4 py-3 animate-pulse"
                        >
                            <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4 mb-2"></div>
                            <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
                        </div>
                    </template>

                    <template v-else>
                        <div
                            v-for="supplier in filteredSuppliers"
                            :key="supplier.id"
                            class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer"
                            @mousedown.prevent="emit('select-supplier', supplier)"
                        >
                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ supplier.name }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ supplier.document_number || supplier.email }}
                            </div>
                        </div>
                    </template>
                </div>

                <p v-if="formErrors.supplier_id" class="text-sm text-red-500 mt-1">
                    {{ formErrors.supplier_id }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                    SKU do Fornecedor
                </label>
                <input
                    :value="formValues.supplier_sku"
                    type="text"
                    maxlength="50"
                    class="kt-input w-full"
                    :class="{ 'border-red-500': formErrors.supplier_sku }"
                    placeholder="Código do produto no fornecedor"
                    @input="emit('update:supplier-sku', $event.target.value)"
                />
                <p v-if="formErrors.supplier_sku" class="text-sm text-red-500 mt-1">
                    {{ formErrors.supplier_sku }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                    Preço de Custo <span class="text-red-500">*</span>
                </label>
                <input
                    :value="formValues.cost_price"
                    class="kt-input w-full"
                    :class="{ 'border-red-500': formErrors.cost_price }"
                    placeholder="R$ 0,00"
                    @input="emit('update:cost-price', $event.target.value)"
                />
                <p v-if="formErrors.cost_price" class="text-sm text-red-500 mt-1">
                    {{ formErrors.cost_price }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                    Prazo de Entrega (dias)
                </label>
                <input
                    :value="formValues.lead_time_days"
                    type="number"
                    min="1"
                    class="kt-input w-full"
                    :class="{ 'border-red-500': formErrors.lead_time_days }"
                    placeholder="Ex: 7"
                    @input="emit('update:lead-time-days', $event.target.value)"
                />
                <p v-if="formErrors.lead_time_days" class="text-sm text-red-500 mt-1">
                    {{ formErrors.lead_time_days }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                    Quantidade Mínima de Pedido
                </label>
                <input
                    :value="formValues.min_order_quantity"
                    type="number"
                    min="1"
                    class="kt-input w-full"
                    :class="{ 'border-red-500': formErrors.min_order_quantity }"
                    placeholder="Ex: 10"
                    @input="emit('update:min-order-quantity', $event.target.value)"
                />
                <p v-if="formErrors.min_order_quantity" class="text-sm text-red-500 mt-1">
                    {{ formErrors.min_order_quantity }}
                </p>
            </div>

            <div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input
                        type="checkbox"
                        :checked="formValues.is_preferred"
                        class="kt-checkbox"
                        @change="emit('update:is-preferred', $event.target.checked)"
                    />
                    <span class="text-sm text-gray-900 dark:text-gray-100">Fornecedor preferencial</span>
                </label>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Marque se este é o fornecedor preferencial para este produto
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                    Observações
                </label>
                <textarea
                    :value="formValues.notes"
                    class="kt-input w-full h-24"
                    :class="{ 'border-red-500': formErrors.notes }"
                    maxlength="2000"
                    rows="3"
                    placeholder="Informações adicionais sobre este fornecedor..."
                    @input="emit('update:notes', $event.target.value)"
                />
                <p v-if="formErrors.notes" class="text-sm text-red-500 mt-1">
                    {{ formErrors.notes }}
                </p>
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <button type="button" class="kt-btn kt-btn-ghost" @click="emit('cancel')">
                    Cancelar
                </button>
                <button type="submit" class="kt-btn kt-btn-primary">
                    {{ mode === 'add' ? 'Adicionar' : 'Salvar' }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
const props = defineProps({
    mode: {
        type: String,
        required: true,
        validator: (value) => ['add', 'edit'].includes(value),
    },
    formValues: {
        type: Object,
        required: true,
    },
    formErrors: {
        type: Object,
        default: () => ({}),
    },
    supplierSearch: {
        type: String,
        default: '',
    },
    selectedSupplier: {
        type: Object,
        default: null,
    },
    filteredSuppliers: {
        type: Array,
        default: () => [],
    },
    loadingSuppliers: {
        type: Boolean,
        default: false,
    },
    showDropdown: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits([
    'submit',
    'cancel',
    'search-input',
    'focus-supplier',
    'blur-supplier',
    'select-supplier',
    'update:supplier-sku',
    'update:cost-price',
    'update:lead-time-days',
    'update:min-order-quantity',
    'update:is-preferred',
    'update:notes',
]);
</script>
