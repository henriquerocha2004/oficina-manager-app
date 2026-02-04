<template>
  <div>
    <div class="card bg-white dark:bg-card border border-border rounded-lg">
      <div class="card-header flex items-center justify-between gap-4 px-6 py-5 border-b border-neutral-300 dark:border-white/20">
        <div>
          <slot name="title">
            <h2 class="text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">Clientes</h2>
          </slot>
          <slot name="subtitle"></slot>
        </div>
        <div class="flex items-center gap-3">
          <div v-if="searchable" class="kt-input max-w-sm">
            <input
              v-model="localSearch"
              @input="onSearchInput"
              :placeholder="searchPlaceholder"
              class="kt-input-ghost"
            />
            <button v-if="localSearch" @click="clearSearch" class="kt-btn kt-btn-icon kt-btn-ghost" title="Limpar">
              ✕
            </button>
          </div>
          <slot name="actions"></slot>
        </div>
      </div>

      <div class="p-3 overflow-x-auto">
        <table class="min-w-full table-auto border-collapse">
          <thead>
            <tr>
              <th v-for="col in columns" :key="col.key" :class="col.headerClass || 'px-6 py-4 text-base font-semibold text-secondary-foreground text-start border-b border-neutral-300 dark:border-white/20'" @click="() => onHeaderClick(col)">
                <div class="flex items-center gap-2">
                  <span :class="col.sortable ? 'sortable' : ''">{{ col.label }}</span>
                  <span v-if="col.sortable" class="sort-indicator">{{ sortIndicator(col) }}</span>
                </div>
              </th>
            </tr>
          </thead>
          <tbody class="text-sm text-foreground">
            <tr v-if="items.length === 0">
              <td :colspan="columns.length" class="py-6 text-center text-secondary-foreground">Nenhum registro encontrado.</td>
            </tr>
            <tr v-for="row in items" :key="row.id" class="last:border-b-0">
              <td v-for="col in columns" :key="col.key + '-' + row.id" class="px-6 py-4 text-sm border-b border-neutral-300 dark:border-white/20 align-top">
                <slot v-if="col.key === 'actions'" name="cell-actions" :row="row" />
                <template v-else>{{ row[col.key] }}</template>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex items-center justify-between mt-4 pb-4 px-3">
        <div class="text-sm text-secondary-foreground">Exibindo {{ infoStart }}-{{ infoEnd }} de {{ total }}</div>
        <div class="kt-pagination flex items-center gap-1">
          <div class="page px-2 py-1" @click="prevPage">
            <svg xmlns="http://www.w3.org/2000/svg" class="inline-block" width="18" height="18" fill="none" viewBox="0 0 24 24"><path fill="currentColor" d="M15.53 19.53a.75.75 0 0 1-1.06 0l-6-6a.75.75 0 0 1 0-1.06l6-6a.75.75 0 1 1 1.06 1.06L10.06 12l5.47 5.47a.75.75 0 0 1 0 1.06Z"/></svg>
          </div>
          <div v-for="p in pageRange" :key="p" class="page px-2 py-1" :class="[ 'rounded', { 'active': p === page } ]" @click="goToPage(p)">{{ p }}</div>
          <div class="page px-2 py-1" @click="nextPage">
            <svg xmlns="http://www.w3.org/2000/svg" class="inline-block" width="18" height="18" fill="none" viewBox="0 0 24 24"><path fill="currentColor" d="M8.47 4.47a.75.75 0 0 1 1.06 0l6 6a.75.75 0 0 1 0 1.06l-6 6a.75.75 0 1 1-1.06-1.06L13.94 12 8.47 6.53a.75.75 0 0 1 0-1.06Z"/></svg>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

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
const infoEnd = computed(() => Math.min(props.page * props.perPage, props.total));

const pageRange = computed(() => {
  const pages = Math.max(1, Math.ceil(props.total / props.perPage));
  const maxShow = 7;
  let start = Math.max(1, props.page - Math.floor(maxShow/2));
  let end = Math.min(start + maxShow - 1, pages);
  if (end - start < maxShow - 1) start = Math.max(1, end - maxShow + 1);
  const arr = [];
  for (let p = start; p <= end; p++) arr.push(p);
  return arr;
});

function goToPage(p) { emit('update:page', p); }
function prevPage() { if (props.page > 1) emit('update:page', props.page - 1); }
function nextPage() { const pages = Math.max(1, Math.ceil(props.total / props.perPage)); if (props.page < pages) emit('update:page', props.page + 1); }

let searchTimeout = null;
function onSearchInput() { clearTimeout(searchTimeout); searchTimeout = setTimeout(() => { emit('search', localSearch.value); }, 200); }
function clearSearch() { localSearch.value = ''; emit('search', ''); }
</script>

<style scoped>
@media (max-width: 640px) {
  .card {
    border-radius: 0 !important;
    margin: 0 !important;
    width: 100vw !important;
    max-width: 100vw !important;
    box-sizing: border-box;
  }
  .card-header {
    padding: 0.75rem 0.5rem !important;
  }
  .p-3 {
    padding: 0.5rem !important;
  }
  table {
    width: 100vw !important;
    min-width: 0 !important;
  }
}
/* Protege font-size e zoom do grid contra alterações globais */
@media (max-width: 640px) {
  .card, .card-header, table, th, td {
    font-size: 1.1rem !important;
    zoom: 1 !important;
  }
}

/* Responsividade para mobile */
@media (max-width: 640px) {
  .card-header { flex-direction: column; gap: 0.5rem; padding: 1rem 0.5rem; }
  table { font-size: 1rem; }
  th, td { padding: 0.75rem 0.5rem !important; }
  th { font-size: 1.1rem; }
  .kt-btn, .kt-btn-sm { min-width: 2.5rem; min-height: 2.5rem; font-size: 1.1rem; }
}

.sortable { cursor: pointer; user-select: none; }
.sort-indicator { width: 0.75rem; display: inline-block; text-align: center; }
.page { cursor: pointer; }
.page.active {
  background: rgba(0,0,0,0.08);
  border-radius: 4px;
  color: #fff;
}
.dark .page {
  color: #fff;
}
.dark .page.active {
  background: #222;
  color: #fff;
}
</style>
