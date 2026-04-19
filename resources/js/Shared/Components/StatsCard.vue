<template>
    <div class="stats-card bg-white dark:bg-card border-gray-200 dark:border-border">
        <div class="stats-card__icon-wrap" :class="`stats-card__icon--${color}`">
            <i :class="icon"></i>
        </div>
        <div class="stats-card__body">
            <span class="stats-card__title text-gray-500 dark:text-gray-400">{{ title }}</span>
            <span class="stats-card__value text-gray-900 dark:text-gray-100">{{ value }}</span>
        </div>
    </div>
</template>

<script setup>
defineProps({
    icon:  { type: String, required: true },
    title: { type: String, required: true },
    value: { type: [String, Number], required: true },
    color: {
        type: String,
        default: 'orange',
        validator: (v) => ['orange', 'green', 'blue', 'red', 'purple', 'gray', 'yellow'].includes(v),
    },
});
</script>

<style scoped>
.stats-card {
    border-width: 1px;
    border-style: solid;
    border-radius: 0.75rem;
    padding: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: box-shadow 0.2s ease;
    min-width: 0;
    overflow: hidden;
}

.stats-card:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}
.dark .stats-card:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.stats-card__icon-wrap {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.stats-card__body {
    display: flex;
    flex-direction: column;
    gap: 0.1rem;
    min-width: 0;
}

.stats-card__title {
    font-size: 0.75rem;
    font-weight: 500;
    white-space: normal;
    line-height: 1.2;
}

.stats-card__value {
    font-size: 1.25rem;
    font-weight: 700;
    line-height: 1.1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

@media (min-width: 640px) {
    .stats-card__value {
        font-size: 1.5rem;
    }
}

/* ── Cores dos ícones ── */
.stats-card__icon--orange { background-color: rgba(251,146,60,0.12); color: rgb(251,146,60); }
.dark .stats-card__icon--orange { background-color: rgba(251,146,60,0.2); }

.stats-card__icon--green  { background-color: rgba(34,197,94,0.12);  color: rgb(34,197,94); }
.dark .stats-card__icon--green  { background-color: rgba(34,197,94,0.2); }

.stats-card__icon--blue   { background-color: rgba(59,130,246,0.12); color: rgb(59,130,246); }
.dark .stats-card__icon--blue   { background-color: rgba(59,130,246,0.2); }

.stats-card__icon--red    { background-color: rgba(239,68,68,0.12);  color: rgb(239,68,68); }
.dark .stats-card__icon--red    { background-color: rgba(239,68,68,0.2); }

.stats-card__icon--purple { background-color: rgba(168,85,247,0.12); color: rgb(168,85,247); }
.dark .stats-card__icon--purple { background-color: rgba(168,85,247,0.2); }

.stats-card__icon--gray   { background-color: rgba(156,163,175,0.12); color: rgb(156,163,175); }
.dark .stats-card__icon--gray   { background-color: rgba(156,163,175,0.2); }

.stats-card__icon--yellow { background-color: rgba(234,179,8,0.12);  color: rgb(234,179,8); }
.dark .stats-card__icon--yellow { background-color: rgba(234,179,8,0.2); }
</style>
