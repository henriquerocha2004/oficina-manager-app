<template>
    <div class="kt-date-picker-field">
        <input
            ref="inputRef"
            :id="id"
            type="text"
            :value="displayValue"
            :placeholder="placeholder"
            :disabled="disabled"
            :aria-label="ariaLabel"
            autocomplete="off"
            class="kt-input kt-date-picker-input"
            @input="onManualInput"
        />

        <button
            type="button"
            class="kt-date-picker-trigger"
            :disabled="disabled"
            :aria-label="triggerLabel"
            @click="openPicker"
        >
            <i class="ki-outline ki-calendar"></i>
        </button>
    </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
    id: {
        type: String,
        default: undefined,
    },
    modelValue: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: 'dd/mm/aaaa',
    },
    min: {
        type: String,
        default: '',
    },
    max: {
        type: String,
        default: '',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    ariaLabel: {
        type: String,
        default: 'Selecionar data',
    },
    triggerLabel: {
        type: String,
        default: 'Abrir calendário',
    },
});

const emit = defineEmits(['update:modelValue', 'change']);

const inputRef = ref(null);
const pickerInstance = ref(null);
const changeListener = ref(null);

const displayValue = computed(() => formatIsoDateToDisplay(props.modelValue));

function formatIsoDateToDisplay(value) {
    if (!value) {
        return '';
    }

    const [year, month, day] = value.split('-');

    if (!year || !month || !day) {
        return value;
    }

    return `${day}/${month}/${year}`;
}

function formatDisplayDateToIso(value) {
    if (!value) {
        return '';
    }

    const match = value.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);

    if (!match) {
        return '';
    }

    const [, day, month, year] = match;

    return `${year}-${month}-${day}`;
}

function destroyPicker() {
    if (inputRef.value && changeListener.value) {
        inputRef.value.removeEventListener('kt.date-picker.change', changeListener.value);
    }

    changeListener.value = null;

    if (pickerInstance.value?.dispose) {
        pickerInstance.value.dispose();
    }

    pickerInstance.value = null;
}

function syncInputValue() {
    if (!inputRef.value) {
        return;
    }

    inputRef.value.value = displayValue.value;
}

async function initPicker() {
    await nextTick();

    if (!inputRef.value || props.disabled) {
        return;
    }

    destroyPicker();
    syncInputValue();

    if (!window.KTDatePicker) {
        return;
    }

    changeListener.value = (event) => {
        const nextValue = event?.detail?.payload?.dates?.[0] ?? '';

        emit('update:modelValue', nextValue);
        emit('change', nextValue);
    };

    inputRef.value.addEventListener('kt.date-picker.change', changeListener.value);

    pickerInstance.value = new window.KTDatePicker(inputRef.value, {
        inputMode: true,
        locale: 'pt-BR',
        dateFormat: 'DD/MM/YYYY',
        firstWeekday: 1,
        selectedDates: props.modelValue ? [props.modelValue] : [],
        dateMin: props.min || undefined,
        dateMax: props.max || undefined,
    });

    syncInputValue();
}

function openPicker() {
    if (props.disabled) {
        return;
    }

    if (pickerInstance.value?.show) {
        pickerInstance.value.show();
        return;
    }

    inputRef.value?.focus();
    inputRef.value?.click();
}

function onManualInput(event) {
    const nextValue = formatDisplayDateToIso(event.target.value.trim());

    emit('update:modelValue', nextValue);
    emit('change', nextValue);
}

onMounted(() => {
    initPicker();
});

onBeforeUnmount(() => {
    destroyPicker();
});

watch(() => props.modelValue, (nextValue, previousValue) => {
    if (nextValue === previousValue) {
        return;
    }

    syncInputValue();

    if (!nextValue && pickerInstance.value?.reset) {
        pickerInstance.value.reset();
        return;
    }

    initPicker();
});

watch(() => [props.min, props.max, props.disabled], () => {
    initPicker();
});
</script>

<style scoped>
.kt-date-picker-field {
    position: relative;
}

.kt-date-picker-input {
    padding-right: 2.75rem;
}

.kt-date-picker-trigger {
    position: absolute;
    top: 50%;
    right: 0.75rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    color: #6b7280;
    background: transparent;
    border: none;
    cursor: pointer;
    transform: translateY(-50%);
}

.kt-date-picker-trigger:hover {
    color: #374151;
}

.kt-date-picker-trigger:disabled {
    cursor: not-allowed;
    opacity: 0.6;
}

.kt-date-picker-trigger:focus-visible {
    outline: none;
}

.dark .kt-date-picker-trigger {
    color: #94a3b8;
}

.dark .kt-date-picker-trigger:hover {
    color: #e2e8f0;
}
</style>