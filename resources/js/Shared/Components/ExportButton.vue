<template>
    <button
        type="button"
        class="kt-btn"
        :disabled="disabled || loading"
        @click="handleExport"
    >
        <i v-if="!loading" class="ki-outline ki-exit-down"></i>
        <i v-else class="ki-outline ki-loading"></i>
        <span>{{ loading ? 'Exportando...' : label }}</span>
    </button>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
    label: {
        type: String,
        default: 'Exportar CSV',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['export']);

const loading = ref(false);

const handleExport = async () => {
    loading.value = true;
    try {
        emit('export');
    } finally {
        setTimeout(() => {
            loading.value = false;
        }, 500);
    }
};
</script>

<style scoped>
.ki-loading {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}
</style>
