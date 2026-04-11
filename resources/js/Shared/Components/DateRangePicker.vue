<template>
    <div class="flex flex-col sm:flex-row gap-3">
        <div class="flex-1">
            <label 
                :for="dateFromId" 
                class="filter-label"
            >
                Data Início
            </label>
            <KtDatePickerInput
                :id="dateFromId"
                :model-value="modelValue.from"
                :max="modelValue.to || undefined"
                aria-label="Selecionar data inicial"
                trigger-label="Selecionar data inicial"
                @update:model-value="updateDateFrom"
            />
        </div>
        <div class="flex-1">
            <label 
                :for="dateToId" 
                class="filter-label"
            >
                Data Fim
            </label>
            <KtDatePickerInput
                :id="dateToId"
                :model-value="modelValue.to"
                :min="modelValue.from || undefined"
                aria-label="Selecionar data final"
                trigger-label="Selecionar data final"
                @update:model-value="updateDateTo"
            />
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import KtDatePickerInput from '@/Shared/Components/KtDatePickerInput.vue';

const props = defineProps({
    modelValue: {
        type: Object,
        default: () => ({ from: '', to: '' }),
    },
});

const emit = defineEmits(['update:modelValue']);

// Gera IDs únicos para os inputs
const dateFromId = computed(() => `date-from-${Math.random().toString(36).substring(7)}`);
const dateToId = computed(() => `date-to-${Math.random().toString(36).substring(7)}`);

/**
 * Atualiza a data inicial
 * @param {string} value
 */
function updateDateFrom(value) {
    emit('update:modelValue', {
        ...props.modelValue,
        from: value,
    });
}

/**
 * Atualiza a data final
 * @param {string} value
 */
function updateDateTo(value) {
    emit('update:modelValue', {
        ...props.modelValue,
        to: value,
    });
}
</script>

<style scoped>
.filter-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.25rem;
    display: block;
}

/* Dark mode para labels */
:deep(.dark) .filter-label,
.dark .filter-label {
    color: #cbd5e1;
}

</style>
