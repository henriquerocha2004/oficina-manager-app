<template>
  <div class="h-full flex flex-col px-2 sm:px-0">
    <div class="card bg-white dark:bg-card border border-border rounded-lg flex-1 flex flex-col min-h-0">

      <!-- Header: busca + filtros + ações -->
      <div class="card-header px-4 py-4 sm:px-6 sm:py-5 border-b border-neutral-300 dark:border-white/20 shrink-0">
        <!-- Linha 1: busca (full width no mobile) -->
        <div v-if="searchable" class="relative w-full sm:w-[280px] mb-3">
          <i class="ki-outline ki-magnifier absolute left-3 top-1/2 -translate-y-1/2 text-lg text-gray-500 dark:text-gray-400 z-10 pointer-events-none"></i>
          <input
            v-model="localSearch"
            @input="onSearchInput"
            :placeholder="searchPlaceholder"
            class="kt-input w-full !pl-10"
          />
        </div>

        <!-- Linha 2: filtros à esquerda, ações à direita -->
        <div class="flex items-center justify-between gap-2">
          <div v-if="$slots.filters">
            <slot name="filters"></slot>
          </div>
          <div v-else class="flex-1"></div>

          <div v-if="$slots.actions" class="flex items-center gap-2 flex-shrink-0">
            <slot name="actions"></slot>
          </div>
        </div>
      </div>

      <!-- Tabela com scroll interno -->
      <div class="flex-1 overflow-x-auto overflow-y-auto min-h-0 p-3">
        <table class="min-w-full table-auto border-collapse">
          <thead>
            <tr>
              <th
                v-for="col in visibleColumns"
                :key="col.key"
                :class="col.headerClass || 'px-6 py-4 text-base font-semibold text-secondary-foreground text-start border-b border-neutral-300 dark:border-white/20'"
                @click="() => onHeaderClick(col)"
              >
                <div class="flex items-center gap-2">
                  <span :class="col.sortable ? 'sortable' : ''">{{ col.label }}</span>
                  <span v-if="col.sortable" class="sort-indicator">{{ sortIndicator(col) }}</span>
                </div>
              </th>
            </tr>
          </thead>
          <tbody class="text-sm text-foreground">
            <tr v-if="items.length === 0">
              <td :colspan="visibleColumns.length" class="py-6 text-center text-secondary-foreground">Nenhum registro encontrado.</td>
            </tr>
            <tr v-for="row in items" :key="row.id" class="last:border-b-0">
              <td
                v-for="col in visibleColumns"
                :key="col.key + '-' + row.id"
                class="px-6 py-4 text-sm border-b border-neutral-300 dark:border-white/20 align-top"
              >
                <slot v-if="$slots['cell-' + col.key]" :name="'cell-' + col.key" :row="row" />
                <template v-else>{{ row[col.key] }}</template>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginação: sempre visível no rodapé do card -->
      <div class="flex items-center justify-between py-3 px-4 border-t border-neutral-300 dark:border-white/20 shrink-0">
        <div class="text-sm text-secondary-foreground">Exibindo {{ infoStart }}-{{ infoEnd }} de {{ total }}</div>
        <div class="kt-pagination flex items-center gap-1">
          <div class="page px-2 py-1" @click="prevPage">
            <svg xmlns="http://www.w3.org/2000/svg" class="inline-block" width="18" height="18" fill="none" viewBox="0 0 24 24"><path fill="currentColor" d="M15.53 19.53a.75.75 0 0 1-1.06 0l-6-6a.75.75 0 0 1 0-1.06l6-6a.75.75 0 1 1 1.06 1.06L10.06 12l5.47 5.47a.75.75 0 0 1 0 1.06Z"/></svg>
          </div>
          <div
            v-for="p in pageRange"
            :key="p"
            class="page px-2 py-1 rounded"
            :class="{ 'active': p === page }"
            @click="goToPage(p)"
          >{{ p }}</div>
          <div class="page px-2 py-1" @click="nextPage">
            <svg xmlns="http://www.w3.org/2000/svg" class="inline-block" width="18" height="18" fill="none" viewBox="0 0 24 24"><path fill="currentColor" d="M8.47 4.47a.75.75 0 0 1 1.06 0l6 6a.75.75 0 0 1 0 1.06l-6 6a.75.75 0 1 1-1.06-1.06L13.94 12 8.47 6.53a.75.75 0 0 1 0-1.06Z"/></svg>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  columns: { type: Array, required: true },
  items: { type: Array, required: true },
  total: { type: Number, required: true },
  page: { type: Number, required: true },
  perPage: { type: Number, required: true },
  searchable: { type: Boolean, default: true },
  searchPlaceholder: { type: String, default: 'Buscar...' }
});

const emit = defineEmits(['update:page', 'sort', 'search', 'edit', 'delete']);

const windowWidth = ref(typeof window !== 'undefined' ? window.innerWidth : 1024);
const isMobile = computed(() => windowWidth.value < 768);
function onResize() { windowWidth.value = window.innerWidth; }
onMounted(() => window.addEventListener('resize', onResize));
onUnmounted(() => window.removeEventListener('resize', onResize));

const visibleColumns = computed(() => {
  if (!isMobile.value) return props.columns;
  const nonAction = props.columns.filter(c => c.key !== 'actions');
  const actionCol = props.columns.find(c => c.key === 'actions');
  const result = nonAction.slice(0, 2);
  if (actionCol) result.push(actionCol);
  return result;
});

const localSearch = ref('');

function onHeaderClick(col) {
  if (!col.sortable) return;
  const dir = col._lastDir === 'asc' ? 'desc' : 'asc';
  col._lastDir = dir;
  emit('sort', { key: col.key, dir });
}

function sortIndicator(col) {
  if (!col.sortable) return '';
  return col._lastDir === 'asc' ? '▲' : col._lastDir === 'desc' ? '▼' : '';
}

const infoStart = computed(() => props.total === 0 ? 0 : ((props.page - 1) * props.perPage) + 1);
const infoEnd   = computed(() => Math.min(props.page * props.perPage, props.total));

const pageRange = computed(() => {
  const pages   = Math.max(1, Math.ceil(props.total / props.perPage));
  const maxShow = 7;
  let start = Math.max(1, props.page - Math.floor(maxShow / 2));
  let end   = Math.min(start + maxShow - 1, pages);
  if (end - start < maxShow - 1) start = Math.max(1, end - maxShow + 1);
  const arr = [];
  for (let p = start; p <= end; p++) arr.push(p);
  return arr;
});

function goToPage(p) { emit('update:page', p); }
function prevPage() { if (props.page > 1) emit('update:page', props.page - 1); }
function nextPage() {
  const pages = Math.max(1, Math.ceil(props.total / props.perPage));
  if (props.page < pages) emit('update:page', props.page + 1);
}

let searchTimeout = null;
function onSearchInput() {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => { emit('search', localSearch.value); }, 200);
}
</script>

<style scoped>
@media (max-width: 640px) {
  .card { border-radius: 0 !important; margin: 0 !important; }
  .card-header { padding: 0.75rem 0.5rem !important; }
  th, td { padding: 0.75rem 0.5rem !important; font-size: 1rem !important; }
  .kt-btn, .kt-btn-sm { min-width: 2.5rem; min-height: 2.5rem; font-size: 1.1rem; }
}

.sortable { cursor: pointer; user-select: none; }
.sort-indicator { width: 0.75rem; display: inline-block; text-align: center; }

.page { cursor: pointer; color: #374151; }
.dark .page { color: #d1d5db; }
.page.active {
  background: #f97316;
  color: #fff !important;
  border-radius: 4px;
}
</style>
