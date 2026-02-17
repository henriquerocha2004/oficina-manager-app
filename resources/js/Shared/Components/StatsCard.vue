<template>
    <div class="stats-card bg-white dark:bg-card border-gray-200 dark:border-border">
        <div class="stats-card__header">
            <div :class="['stats-card__icon', `stats-card__icon--${color}`]">
                <i :class="`ki-outline ki-${icon}`"></i>
            </div>
            <div class="stats-card__main">
                <span class="stats-card__title text-gray-600 dark:text-gray-400">{{ title }}</span>
                <span class="stats-card__value text-gray-900 dark:text-gray-100">{{ value }}</span>
            </div>
        </div>
        <div class="stats-card__footer">
            <span class="stats-card__subtitle text-gray-600 dark:text-gray-400">{{ subtitle }}</span>
            <span v-if="trend" :class="['stats-card__trend', `stats-card__trend--${trendType}`]">
                <i :class="`ki-outline ki-${trendIcon}`"></i>
                {{ trend }}
            </span>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    icon: {
        type: String,
        required: true,
    },
    title: {
        type: String,
        required: true,
    },
    value: {
        type: [String, Number],
        required: true,
    },
    subtitle: {
        type: String,
        default: '',
    },
    trend: {
        type: String,
        default: '',
    },
    color: {
        type: String,
        default: 'orange',
        validator: (value) => ['orange', 'green', 'blue', 'red', 'purple'].includes(value),
    },
});

const trendType = computed(() => {
    if (!props.trend) return '';
    if (props.trend.includes('+') || props.trend.includes('↑')) return 'positive';
    if (props.trend.includes('-') || props.trend.includes('↓')) return 'negative';
    return 'neutral';
});

const trendIcon = computed(() => {
    if (trendType.value === 'positive') return 'arrow-up';
    if (trendType.value === 'negative') return 'arrow-down';
    return 'minus';
});
</script>

<style>
.stats-card {
    border-width: 1.5px;
    border-style: solid;
    border-radius: 0.75rem;
    padding: 1.5rem;
    transition: all 0.2s ease;
    min-width: 220px;
}

.stats-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.dark .stats-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.stats-card__header {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1rem;
}

.stats-card__icon {
    width: 3rem;
    height: 3rem;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.stats-card__main {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    min-width: 0;
}

.stats-card__title {
    font-size: 0.875rem;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.stats-card__value {
    font-size: 1.75rem;
    font-weight: 700;
    line-height: 1.1;
    word-break: break-word;
}

.stats-card__footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.stats-card__subtitle {
    font-size: 0.8125rem;
    flex: 1;
    min-width: 0;
}

.stats-card__trend {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    white-space: nowrap;
}

.stats-card__icon--orange {
    background-color: rgba(251, 146, 60, 0.1);
    color: rgb(251, 146, 60);
}

.dark .stats-card__icon--orange {
    background-color: rgba(251, 146, 60, 0.2);
    color: rgb(251, 146, 60);
}

.stats-card__icon--green {
    background-color: rgba(34, 197, 94, 0.1);
    color: rgb(34, 197, 94);
}

.dark .stats-card__icon--green {
    background-color: rgba(34, 197, 94, 0.2);
    color: rgb(34, 197, 94);
}

.stats-card__icon--blue {
    background-color: rgba(59, 130, 246, 0.1);
    color: rgb(59, 130, 246);
}

.dark .stats-card__icon--blue {
    background-color: rgba(59, 130, 246, 0.2);
    color: rgb(59, 130, 246);
}

.stats-card__icon--red {
    background-color: rgba(239, 68, 68, 0.1);
    color: rgb(239, 68, 68);
}

.dark .stats-card__icon--red {
    background-color: rgba(239, 68, 68, 0.2);
    color: rgb(239, 68, 68);
}

.stats-card__icon--purple {
    background-color: rgba(168, 85, 247, 0.1);
    color: rgb(168, 85, 247);
}

.dark .stats-card__icon--purple {
    background-color: rgba(168, 85, 247, 0.2);
    color: rgb(168, 85, 247);
}

.stats-card__trend--positive {
    background-color: rgba(34, 197, 94, 0.1);
    color: rgb(34, 197, 94);
}

.dark .stats-card__trend--positive {
    background-color: rgba(34, 197, 94, 0.2);
    color: rgb(34, 197, 94);
}

.stats-card__trend--negative {
    background-color: rgba(239, 68, 68, 0.1);
    color: rgb(239, 68, 68);
}

.dark .stats-card__trend--negative {
    background-color: rgba(239, 68, 68, 0.2);
    color: rgb(239, 68, 68);
}

.stats-card__trend--neutral {
    background-color: rgba(156, 163, 175, 0.1);
    color: rgb(156, 163, 175);
}

.dark .stats-card__trend--neutral {
    background-color: rgba(156, 163, 175, 0.2);
    color: rgb(156, 163, 175);
}

/* Responsive */
@media (max-width: 640px) {
    .stats-card {
        padding: 1rem;
        min-width: unset;
    }

    .stats-card__icon {
        width: 2.5rem;
        height: 2.5rem;
        font-size: 1.25rem;
    }

    .stats-card__value {
        font-size: 1.5rem;
    }
}
</style>
