<template>
  <div class="filter-dropdown-container" ref="dropdownRef">
    <button
      @click="toggleFilters"
      class="kt-btn"
      :class="{ 'active': isOpen }"
    >
      <i class="ki-outline ki-setting-3"></i>
      <span>Filtros</span>
      <span v-if="activeCount > 0" class="filter-count-badge">{{ activeCount }}</span>
      <i class="ki-outline" :class="isOpen ? 'ki-up' : 'ki-down'"></i>
    </button>

    <transition name="filter-panel">
      <div v-if="isOpen" class="filter-panel">
        <div class="filter-panel-header">
          <h3 class="filter-panel-title">Filtros Avançados</h3>
          <div class="flex items-center gap-2">
            <button
              v-if="activeCount > 0"
              @click="$emit('clear')"
              class="filter-panel-clear"
            >
              <i class="ki-outline ki-cross-circle"></i>
              Limpar todos
            </button>
            <button
              @click="closeFilters"
              class="filter-panel-close"
              title="Fechar"
            >
              <i class="ki-outline ki-cross"></i>
            </button>
          </div>
        </div>

        <div class="filter-panel-content">
          <slot></slot>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    activeCount: {
        type: Number,
        default: 0
    }
});

defineEmits(['clear']);

const isOpen = ref(false);
const dropdownRef = ref(null);

function toggleFilters() {
    isOpen.value = !isOpen.value;
}

function closeFilters() {
    isOpen.value = false;
}

function handleClickOutside(event) {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        closeFilters();
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
.filter-dropdown-container {
    position: relative;
}

.kt-btn.active {
    border-color: #ff9800;
    color: #ff9800;
    background: rgba(255, 152, 0, 0.1);
}

.filter-count-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 1.25rem;
    height: 1.25rem;
    padding: 0 0.375rem;
    font-size: 0.75rem;
    font-weight: 600;
    color: #ffffff;
    background: #ff9800;
    border-radius: 9999px;
}

.filter-panel {
    position: absolute !important;
    bottom: calc(100% + 0.5rem) !important;
    top: auto !important;
    left: 0;
    min-width: 600px;
    z-index: 50;
    background: #ffffff;
    border: 2px solid #ff9800;
    border-radius: 0.75rem;
    box-shadow: 0 10px 25px rgba(255, 152, 0, 0.15), 0 4px 10px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

@media (max-width: 768px) {
    .filter-panel {
        min-width: auto;
        left: -100px;
        right: -100px;
    }
}

.filter-panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.25rem;
    border-bottom: 1.5px solid #e5e7eb;
    background: #f9fafb;
}

.filter-panel-title {
    font-size: 1rem;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.filter-panel-clear {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.75rem;
    font-size: 0.8125rem;
    font-weight: 500;
    color: #6b7280;
    background: transparent;
    border: 1.5px solid #d1d5db;
    border-radius: 0.375rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.filter-panel-clear:hover {
    color: #ef4444;
    border-color: #ef4444;
    background: rgba(239, 68, 68, 0.05);
}

.filter-panel-close {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    padding: 0;
    color: #6b7280;
    background: transparent;
    border: none;
    border-radius: 0.375rem;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 1.25rem;
}

.filter-panel-close:hover {
    color: #374151;
    background: rgba(0, 0, 0, 0.05);
}

.filter-panel-content {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    padding: 1.25rem;
}

.dark .kt-btn.active {
    border-color: #ff9800;
    color: #ff9800;
    background: rgba(255, 152, 0, 0.15);
}

.dark .filter-panel {
    background: #1a1a1a;
    border-color: #ff9800;
    box-shadow: 0 10px 25px rgba(255, 152, 0, 0.2), 0 4px 10px rgba(0, 0, 0, 0.3);
}

.dark .filter-panel-header {
    background: #080808;
    border-color: #1f1f1f;
}

.dark .filter-panel-title {
    color: #f8fafc;
}

.dark .filter-panel-clear {
    color: #94a3b8;
    border-color: #1f1f1f;
}

.dark .filter-panel-clear:hover {
    color: #f87171;
    border-color: #f87171;
    background: rgba(248, 113, 113, 0.1);
}

.dark .filter-panel-close {
    color: #94a3b8;
}

.dark .filter-panel-close:hover {
    color: #f8fafc;
    background: rgba(255, 255, 255, 0.1);
}
</style>
