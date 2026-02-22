<template>
    <div class="flex flex-col sm:flex-row gap-3">
        <div class="flex-1">
            <label 
                :for="dateFromId" 
                class="filter-label"
            >
                Data Início
            </label>
            <input
                :id="dateFromId"
                type="date"
                :value="modelValue.from"
                @input="updateDateFrom"
                :max="modelValue.to || undefined"
                class="kt-input"
            />
        </div>
        <div class="flex-1">
            <label 
                :for="dateToId" 
                class="filter-label"
            >
                Data Fim
            </label>
            <input
                :id="dateToId"
                type="date"
                :value="modelValue.to"
                @input="updateDateTo"
                :min="modelValue.from || undefined"
                class="kt-input"
            />
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

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
 * @param {Event} event
 */
function updateDateFrom(event) {
    emit('update:modelValue', {
        ...props.modelValue,
        from: event.target.value,
    });
}

/**
 * Atualiza a data final
 * @param {Event} event
 */
function updateDateTo(event) {
    emit('update:modelValue', {
        ...props.modelValue,
        to: event.target.value,
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

/* Corrigir ícones de calendário no dark mode */
input[type="date"]::-webkit-calendar-picker-indicator {
    cursor: pointer;
    filter: invert(0);
}

.dark input[type="date"]::-webkit-calendar-picker-indicator {
    filter: invert(1);
}
</style>
