<template>
  <!-- Modal Overlay -->
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="isOpen"
        id="search_modal"
        class="fixed inset-0 z-50 flex items-start justify-center pt-20"
        style="background: rgba(0, 0, 0, 0.5);"
        @click.self="close"
      >
        <!-- Modal Content -->
        <div class="w-full max-w-2xl bg-white dark:bg-[#080808] rounded-xl shadow-2xl flex flex-col max-h-[600px]">
          <!-- Search Input -->
          <div class="px-6 py-4 border-b border-gray-200 dark:border-border">
            <div class="flex items-center gap-3 px-4 py-3 bg-gray-50 dark:bg-accent rounded-lg">
              <i class="ki-outline ki-magnifier text-xl text-gray-400"></i>
              <input
                ref="searchInput"
                v-model="searchQuery"
                type="text"
                class="flex-1 bg-transparent border-0 outline-none text-gray-900 dark:text-gray-100 placeholder-gray-400"
                placeholder="Buscar..."
                @keydown.esc="close"
              />
              <button
                v-if="searchQuery"
                type="button"
                class="inline-flex items-center justify-center rounded-lg font-medium transition-colors h-6 px-2 text-xs w-6 px-0 bg-gray-100 dark:bg-muted text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-accent"
                @click="searchQuery = ''"
              >
                <i class="ki-outline ki-cross text-sm"></i>
              </button>
            </div>
          </div>

          <!-- Tabs -->
          <div class="flex items-center border-b border-gray-200 dark:border-border px-6">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              type="button"
              class="px-4 py-3 text-sm font-medium border-b-2 border-transparent hover:text-gray-900 dark:hover:text-gray-100 transition-colors"
              :class="activeTab === tab.id ? 'text-primary border-primary' : 'text-gray-600 dark:text-gray-400'"
              @click="activeTab = tab.id"
            >
              {{ tab.label }}
            </button>
          </div>

          <!-- Results -->
          <div class="flex-1 overflow-y-auto px-6 py-4">
            <!-- Mixed Results -->
            <div v-show="activeTab === 'mixed'" class="space-y-2">
              <div v-if="searchQuery && filteredResults.length > 0">
                <div
                  v-for="result in filteredResults"
                  :key="result.id"
                  class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-[#242424] cursor-pointer transition-colors"
                  @click="handleResultClick(result)"
                >
                  <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-100 dark:bg-muted">
                    <i :class="result.icon"></i>
                  </div>
                  <div class="flex-1 min-w-0">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                      {{ result.title }}
                    </h4>
                    <p class="text-xs text-gray-600 dark:text-gray-400">
                      {{ result.description }}
                    </p>
                  </div>
                  <span class="px-2 py-1 text-xs font-medium rounded bg-gray-100 dark:bg-muted text-gray-600 dark:text-gray-400">
                    {{ result.type }}
                  </span>
                </div>
              </div>

              <!-- Empty State -->
              <div v-else-if="searchQuery" class="flex flex-col items-center justify-center py-12 text-center">
                <i class="ki-outline ki-file-search text-4xl text-gray-400 mb-3"></i>
                <p class="text-gray-600 dark:text-gray-400">
                  Nenhum resultado encontrado para "{{ searchQuery }}"
                </p>
              </div>

              <!-- Initial State -->
              <div v-else class="flex flex-col items-center justify-center py-12 text-center">
                <i class="ki-outline ki-magnifier text-4xl text-gray-400 mb-3"></i>
                <p class="text-gray-600 dark:text-gray-400">
                  Digite para buscar em todo o sistema
                </p>
                <div class="mt-4 space-y-2 text-left w-full max-w-xs">
                  <p class="text-xs text-gray-500 dark:text-gray-500">Buscas rápidas:</p>
                  <div class="flex flex-wrap gap-2">
                    <button
                      v-for="quick in quickSearches"
                      :key="quick"
                      type="button"
                      class="inline-flex items-center justify-center rounded-lg font-medium transition-colors h-6 px-2 text-xs bg-gray-100 dark:bg-muted text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-accent"
                      @click="searchQuery = quick"
                    >
                      {{ quick }}
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Other tabs -->
            <div v-show="activeTab !== 'mixed'" class="flex flex-col items-center justify-center py-12 text-center">
              <p class="text-gray-600 dark:text-gray-400">
                Busca específica por {{ tabs.find(t => t.id === activeTab)?.label }}
              </p>
            </div>
          </div>

          <!-- Footer -->
          <div class="px-6 py-4 border-t border-gray-200 dark:border-border">
            <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-500">
              <span><kbd class="px-1.5 py-0.5 text-xs font-semibold bg-gray-100 dark:bg-muted border border-gray-300 dark:border-border rounded">Esc</kbd> para fechar</span>
              <span><kbd class="px-1.5 py-0.5 text-xs font-semibold bg-gray-100 dark:bg-muted border border-gray-300 dark:border-border rounded">↑</kbd> <kbd class="px-1.5 py-0.5 text-xs font-semibold bg-gray-100 dark:bg-muted border border-gray-300 dark:border-border rounded">↓</kbd> para navegar</span>
              <span><kbd class="px-1.5 py-0.5 text-xs font-semibold bg-gray-100 dark:bg-muted border border-gray-300 dark:border-border rounded">Enter</kbd> para selecionar</span>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import { useRoute } from '@/Composables/useRoute';

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['close']);

const searchQuery = ref('');
const activeTab = ref('mixed');
const searchInput = ref(null);
const { route } = useRoute();

const tabs = [
  { id: 'mixed', label: 'Todos' },
  { id: 'settings', label: 'Configurações' },
  { id: 'integrations', label: 'Integrações' },
  { id: 'users', label: 'Usuários' },
  { id: 'docs', label: 'Documentação' },
];

const quickSearches = [
  'Clientes',
  'Ordens de Serviço',
  'Estoque',
  'Relatórios',
];

// Mock de resultados (substituir por busca real via API)
const mockResults = [
  {
    id: 1,
    title: 'Clientes',
    description: 'Gerenciar clientes e contatos',
    type: 'Página',
    icon: 'ki-outline ki-profile-user text-primary',
    route: 'tenant.clients.index',
  },
  {
    id: 2,
    title: 'Nova Ordem de Serviço',
    description: 'Criar nova ordem de serviço',
    type: 'Ação',
    icon: 'ki-outline ki-plus-squared text-success',
    route: 'tenant.service-orders.create',
  },
  {
    id: 3,
    title: 'Configurações do Sistema',
    description: 'Ajustes e preferências',
    type: 'Configuração',
    icon: 'ki-outline ki-setting-2 text-gray-600',
    route: 'tenant.settings.profile',
  },
  {
    id: 4,
    title: 'Relatórios Financeiros',
    description: 'Visualizar relatórios e análises',
    type: 'Relatório',
    icon: 'ki-outline ki-chart-line text-warning',
    route: 'tenant.invoicing.reports',
  },
];

const filteredResults = computed(() => {
  if (!searchQuery.value) return [];
  
  const query = searchQuery.value.toLowerCase();
  return mockResults.filter(result => 
    result.title.toLowerCase().includes(query) ||
    result.description.toLowerCase().includes(query)
  );
});

function close() {
  emit('close');
  searchQuery.value = '';
}

function handleResultClick(result) {
  if (result.route) {
    router.visit(route(result.route));
    close();
  }
}

// Auto-focus no input quando o modal abre
watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    nextTick(() => {
      searchInput.value?.focus();
    });
  }
});
</script>

<style>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-active > div,
.modal-leave-active > div {
  transition: all 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-from > div,
.modal-leave-to > div {
  opacity: 0;
  transform: translateY(-50px) scale(0.95);
}

.modal-enter-to,
.modal-leave-from {
  opacity: 1;
}

.modal-enter-to > div,
.modal-leave-from > div {
  opacity: 1;
  transform: translateY(0) scale(1);
}
</style>
