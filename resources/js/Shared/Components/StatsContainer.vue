<template>
    <div class="stats-container">
        <slot></slot>
    </div>
</template>

<style scoped>
.stats-container {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.75rem;
    margin-bottom: 1rem;
    padding: 0.5rem 0.5rem 0;
}

@media (min-width: 640px) {
    .stats-container {
        padding: 0;
        margin-bottom: 1.5rem;
    }
}

@media (min-width: 768px) {
    .stats-container {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (min-width: 1024px) {
    .stats-container {
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 1rem;
    }
}
</style>
