<template>
  <div class="filter-dropdown-container" ref="dropdownRef">
    <!-- Botão trigger -->
    <button @click="toggleFilters" class="kt-btn" :class="{ active: isOpen }">
      <i class="ki-outline ki-setting-3"></i>
      <span>Filtros</span>
      <span v-if="activeCount > 0" class="filter-count-badge">{{ activeCount }}</span>
      <i class="ki-outline" :class="isOpen ? 'ki-up' : 'ki-down'"></i>
    </button>

    <!-- Desktop: dropdown posicionado absolutamente -->
    <Transition name="dropdown-fade">
      <div v-if="isOpen && !isMobile" class="desktop-panel">
        <div class="panel-header">
          <span class="panel-title">Filtros Avançados</span>
          <div class="flex items-center gap-2">
            <button v-if="activeCount > 0" class="btn-clear" @click="$emit('clear')">
              <i class="ki-outline ki-cross-circle"></i> Limpar
            </button>
            <button class="btn-close" @click="closeFilters">
              <i class="ki-outline ki-cross"></i>
            </button>
          </div>
        </div>
        <div class="panel-body">
          <slot :isMobile="false" />
        </div>
      </div>
    </Transition>
  </div>

  <!-- Mobile: bottom sheet via Teleport -->
  <Teleport to="body">
    <Transition name="backdrop-fade">
      <div v-if="isOpen && isMobile" class="bs-backdrop" @click="closeFilters" />
    </Transition>

    <Transition name="sheet-slide">
      <div v-if="isOpen && isMobile" class="bs-sheet">
        <!-- Alça -->
        <div class="bs-handle-wrap">
          <div class="bs-handle" />
        </div>

        <!-- Cabeçalho -->
        <div class="bs-header">
          <span class="panel-title">Filtros</span>
          <button v-if="activeCount > 0" class="btn-clear" @click="$emit('clear')">
            <i class="ki-outline ki-cross-circle"></i> Limpar
          </button>
        </div>

        <!-- Conteúdo com scroll -->
        <div class="bs-body">
          <slot :isMobile="true" />
        </div>

        <!-- Rodapé: fechar -->
        <div class="bs-footer">
          <button class="bs-apply-btn" @click="closeFilters">Aplicar filtros</button>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

defineProps({ activeCount: { type: Number, default: 0 } });
defineEmits(['clear']);

const isOpen    = ref(false);
const dropdownRef = ref(null);
const windowWidth = ref(typeof window !== 'undefined' ? window.innerWidth : 1024);

const isMobile = computed(() => windowWidth.value < 768);

function onResize()      { windowWidth.value = window.innerWidth; }
function toggleFilters() { isOpen.value = !isOpen.value; }
function closeFilters()  { isOpen.value = false; }

function handleClickOutside(e) {
  if (isMobile.value) return;
  if (e.target.closest('[data-vc="calendar"]')) return;
  if (dropdownRef.value && !dropdownRef.value.contains(e.target)) closeFilters();
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
  window.addEventListener('resize', onResize);
});
onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
  window.removeEventListener('resize', onResize);
});
</script>

<style scoped>
/* ── Trigger ── */
.filter-dropdown-container { position: relative; }

.kt-btn.active {
  border-color: #f97316;
  color: #f97316;
  background: rgba(249, 115, 22, 0.1);
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
  color: #fff;
  background: #f97316;
  border-radius: 9999px;
}

/* ── Desktop panel ── */
.desktop-panel {
  position: absolute;
  top: calc(100% + 0.5rem);
  left: 0;
  min-width: 680px;
  max-width: min(800px, calc(100vw - 2rem));
  z-index: 100;
  background: #fff;
  border: 2px solid #f97316;
  border-radius: 0.75rem;
  box-shadow: 0 8px 24px rgba(0,0,0,0.12);
  overflow: hidden;
}

.dark .desktop-panel {
  background: #1a1a1a;
  border-color: #f97316;
  box-shadow: 0 8px 24px rgba(0,0,0,0.4);
}


/* ── Shared panel parts ── */
.panel-header, .bs-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
}

.panel-header {
  padding: 0.875rem 1.25rem;
  border-bottom: 1px solid #e5e7eb;
  background: #f9fafb;
}

.dark .panel-header {
  background: #111;
  border-color: #2a2a2a;
}

.panel-title {
  font-size: 0.9375rem;
  font-weight: 600;
  color: #111827;
}

.dark .panel-title { color: #f1f5f9; }

.panel-body {
  padding: 1.25rem;
}

.btn-clear {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.3rem 0.625rem;
  font-size: 0.8125rem;
  font-weight: 500;
  color: #6b7280;
  background: transparent;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  cursor: pointer;
  transition: color 0.15s, border-color 0.15s;
  white-space: nowrap;
}

.btn-clear:hover { color: #ef4444; border-color: #ef4444; }

.btn-close {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 2rem;
  height: 2rem;
  color: #6b7280;
  background: transparent;
  border: none;
  border-radius: 0.375rem;
  cursor: pointer;
  font-size: 1.25rem;
  transition: color 0.15s;
}
.btn-close:hover { color: #374151; }

/* ── Transitions ── */
.dropdown-fade-enter-active,
.dropdown-fade-leave-active { transition: opacity 0.18s ease, transform 0.18s ease; }
.dropdown-fade-enter-from,
.dropdown-fade-leave-to { opacity: 0; transform: translateY(-4px); }
</style>

<style>
/* ── Bottom sheet — global (Teleport) ── */
.bs-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.45);
  z-index: 500;
  backdrop-filter: blur(2px);
}

.bs-sheet {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 501;
  background: #fff;
  border-radius: 1.25rem 1.25rem 0 0;
  max-height: 85vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 -8px 40px rgba(0,0,0,0.18);
}

.dark .bs-sheet { background: #181818; }

/* Handle */
.bs-handle-wrap {
  display: flex;
  justify-content: center;
  padding: 0.625rem 0 0;
  flex-shrink: 0;
}

.bs-handle {
  width: 2.75rem;
  height: 0.25rem;
  border-radius: 9999px;
  background: #cbd5e1;
}

.dark .bs-handle { background: #374151; }

/* Header */
.bs-header {
  padding: 0.75rem 1.25rem 0.75rem;
  border-bottom: 1px solid #f1f5f9;
  flex-shrink: 0;
}

.dark .bs-header { border-color: #2a2a2a; }

/* Body */
.bs-body {
  flex: 1;
  overflow-y: auto;
  overflow-x: visible;
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  /* Permite que popups (datepicker) escapem do scroll container */
  isolation: auto;
}

/* Filter item labels no dark mode dentro do sheet */
.bs-body .filter-label,
.bs-body label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
}

.dark .bs-body .filter-label,
.dark .bs-body label {
  color: #cbd5e1;
}

/* Footer */
.bs-footer {
  padding: 0.75rem 1.25rem;
  padding-bottom: max(0.75rem, env(safe-area-inset-bottom));
  border-top: 1px solid #f1f5f9;
  flex-shrink: 0;
}

.dark .bs-footer { border-color: #2a2a2a; }

.bs-apply-btn {
  width: 100%;
  padding: 0.75rem;
  font-size: 0.9375rem;
  font-weight: 600;
  color: #fff;
  background: #f97316;
  border: none;
  border-radius: 0.625rem;
  cursor: pointer;
  transition: background 0.15s;
}

.bs-apply-btn:active { background: #ea6c0a; }

/* KTDatePicker calendar must appear above bottom sheet backdrop (z-index: 500) */
[data-vc="calendar"],
.flatpickr-calendar,
.vc-popover-content-wrapper {
  z-index: 600 !important;
}

/* ── Transitions ── */
.backdrop-fade-enter-active,
.backdrop-fade-leave-active { transition: opacity 0.25s ease; }
.backdrop-fade-enter-from,
.backdrop-fade-leave-to { opacity: 0; }

.sheet-slide-enter-active,
.sheet-slide-leave-active { transition: transform 0.32s cubic-bezier(0.32, 0.72, 0, 1); }
.sheet-slide-enter-from,
.sheet-slide-leave-to { transform: translateY(100%); }
</style>
