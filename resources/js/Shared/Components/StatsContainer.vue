<template>
    <div class="stats-container">
        <slot></slot>
    </div>
</template>

<style scoped>
.stats-container {
    display: grid;
    grid-template-columns: repeat(1, 1fr);
    gap: 1.5rem;
    margin-bottom: 2rem;
}

/* Tablet: 2 colunas */
@media (min-width: 768px) {
    .stats-container {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* Desktop: 4 colunas */
@media (min-width: 1024px) {
    .stats-container {
        grid-template-columns: repeat(4, 1fr);
    }
}
</style>
