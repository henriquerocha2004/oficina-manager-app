<template>
  <!-- Drawer Overlay -->
  <Teleport to="body">
    <Transition name="drawer">
      <div
        v-if="isOpen"
        id="notifications_drawer"
        class="fixed inset-0 z-50 flex items-center justify-end"
        style="background: rgba(0, 0, 0, 0.5);"
        @click.self="close"
      >
        <!-- Drawer Content -->
        <div class="w-full max-w-md h-full bg-white dark:bg-popover shadow-xl flex flex-col">
          <!-- Header -->
          <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-border">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
              Notificações
            </h3>
            <button
              type="button"
              class="inline-flex items-center justify-center rounded-lg font-medium transition-colors h-8 px-3 text-sm w-8 px-0 bg-gray-100 dark:bg-accent text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-accent"
              @click="close"
            >
              <i class="ki-outline ki-cross"></i>
            </button>
          </div>

          <!-- Tabs -->
          <div class="flex items-center border-b border-gray-200 dark:border-border px-5">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              type="button"
              class="px-4 py-3 text-sm font-medium border-b-2 border-transparent hover:text-gray-900 dark:hover:text-gray-100 transition-colors"
              :class="activeTab === tab.id ? 'text-primary border-primary' : 'text-gray-600 dark:text-gray-400'"
              @click="activeTab = tab.id"
            >
              {{ tab.label }}
              <span v-if="tab.count > 0" class="inline-flex items-center justify-center rounded-full px-1.5 py-0.5 text-xs bg-primary/20 text-primary ml-2">
                {{ tab.count }}
              </span>
            </button>
          </div>

          <!-- Content -->
          <div class="flex-1 overflow-y-auto px-5 py-4">
            <!-- All Tab -->
            <div v-show="activeTab === 'all'" class="space-y-3">
              <div
                v-for="notification in notifications"
                :key="notification.id"
                class="p-4 rounded-lg transition-colors"
                :class="notification.read ? 'bg-gray-50 dark:bg-accent' : 'bg-primary/5 border border-primary/20'"
              >
                <div class="flex gap-3">
                  <div class="flex items-center justify-center w-10 h-10 rounded-full bg-white dark:bg-muted">
                    <i :class="notification.icon"></i>
                  </div>
                  <div class="flex-1 min-w-0">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-1">
                      {{ notification.title }}
                    </h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">
                      {{ notification.message }}
                    </p>
                    <span class="text-xs text-gray-500 dark:text-gray-500">
                      {{ notification.time }}
                    </span>
                  </div>
                  <button
                    v-if="!notification.read"
                    type="button"
                    class="inline-flex items-center justify-center rounded-lg font-medium transition-colors h-6 px-2 text-xs w-6 px-0 bg-gray-100 dark:bg-muted text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-accent"
                    @click="markAsRead(notification.id)"
                    title="Marcar como lida"
                  >
                    <i class="ki-outline ki-check"></i>
                  </button>
                </div>
              </div>

              <!-- Empty State -->
              <div v-if="notifications.length === 0" class="flex flex-col items-center justify-center py-12 text-center">
                <i class="ki-outline ki-notification-on text-4xl text-gray-400 mb-3"></i>
                <p class="text-gray-600 dark:text-gray-400">
                  Nenhuma notificação
                </p>
              </div>
            </div>

            <!-- Other tabs (Inbox, Team, Following) -->
            <div v-show="activeTab === 'inbox'" class="flex flex-col items-center justify-center py-12 text-center">
              <p class="text-gray-600 dark:text-gray-400">Nenhuma mensagem na caixa de entrada</p>
            </div>

            <div v-show="activeTab === 'team'" class="flex flex-col items-center justify-center py-12 text-center">
              <p class="text-gray-600 dark:text-gray-400">Nenhuma notificação da equipe</p>
            </div>

            <div v-show="activeTab === 'following'" class="flex flex-col items-center justify-center py-12 text-center">
              <p class="text-gray-600 dark:text-gray-400">Nenhuma atualização</p>
            </div>
          </div>

          <!-- Footer -->
          <div class="flex items-center gap-2 px-5 py-4 border-t border-gray-200 dark:border-border">
            <button
              type="button"
              class="inline-flex items-center justify-center rounded-lg font-medium transition-colors h-8 px-3 text-sm bg-gray-100 dark:bg-muted text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-accent flex-1"
              @click="markAllAsRead"
            >
              Marcar todas como lidas
            </button>
            <button
              type="button"
              class="inline-flex items-center justify-center rounded-lg font-medium transition-colors h-8 px-3 text-sm bg-gray-100 dark:bg-muted text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-accent flex-1"
              @click="archiveAll"
            >
              Arquivar todas
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['close', 'mark-read', 'mark-all-read', 'archive-all']);

const activeTab = ref('all');

const tabs = [
  { id: 'all', label: 'Todas', count: 3 },
  { id: 'inbox', label: 'Caixa de Entrada', count: 0 },
  { id: 'team', label: 'Equipe', count: 0 },
  { id: 'following', label: 'Seguindo', count: 0 },
];

// Mock de notificações (substituir por dados reais)
const notifications = ref([
  {
    id: 1,
    title: 'Nova ordem de serviço',
    message: 'Ordem #1234 foi criada para o cliente João Silva',
    time: 'Há 5 minutos',
    icon: 'ki-outline ki-setting-3 text-primary',
    read: false,
  },
  {
    id: 2,
    title: 'Pagamento recebido',
    message: 'Pagamento de R$ 500,00 foi confirmado',
    time: 'Há 1 hora',
    icon: 'ki-outline ki-dollar text-success',
    read: false,
  },
  {
    id: 3,
    title: 'Estoque baixo',
    message: 'O produto "Óleo de Motor" está com estoque baixo',
    time: 'Há 2 horas',
    icon: 'ki-outline ki-notification-status text-warning',
    read: true,
  },
]);

function close() {
  emit('close');
}

function markAsRead(id) {
  const notification = notifications.value.find(n => n.id === id);
  if (notification) {
    notification.read = true;
  }
  emit('mark-read', id);
}

function markAllAsRead() {
  notifications.value.forEach(n => n.read = true);
  emit('mark-all-read');
}

function archiveAll() {
  notifications.value = [];
  emit('archive-all');
}
</script>

<style>
.drawer-enter-active,
.drawer-leave-active {
  transition: opacity 0.3s ease;
}

.drawer-enter-active > div,
.drawer-leave-active > div {
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.drawer-enter-from,
.drawer-leave-to {
  opacity: 0;
}

.drawer-enter-from > div,
.drawer-leave-to > div {
  transform: translateX(100%);
}

.drawer-enter-to,
.drawer-leave-from {
  opacity: 1;
}

.drawer-enter-to > div,
.drawer-leave-from > div {
  transform: translateX(0);
}
</style>
