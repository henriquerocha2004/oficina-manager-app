<template>
  <div>
    <div v-if="sortedEvents.length === 0" class="text-center py-8 text-sm text-gray-500 dark:text-gray-400">
      Nenhum evento registrado.
    </div>

    <div v-else class="relative">
      <!-- Linha vertical -->
      <div class="absolute left-4 top-0 bottom-0 w-px bg-gray-200 dark:bg-gray-700"></div>

      <div class="space-y-6">
        <div
          v-for="event in sortedEvents"
          :key="event.id"
          class="relative flex gap-4"
        >
          <!-- Ícone do evento -->
          <div
            class="relative z-10 flex items-center justify-center w-8 h-8 rounded-full shrink-0 text-white text-sm"
            :class="eventIconBg(event.event_type)"
          >
            <i :class="`ki-filled ${eventIcon(event.event_type)}`"></i>
          </div>

          <!-- Conteúdo -->
          <div class="flex-1 pb-2 min-w-0">
            <div class="flex items-start justify-between gap-2">
              <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                {{ eventTitle(event) }}
              </p>
              <span class="text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap shrink-0">
                {{ formatDate(event.created_at) }}
              </span>
            </div>

            <!-- Detalhe por tipo -->
            <div class="mt-1">

              <!-- status_changed -->
              <div v-if="event.event_type === 'status_changed' && event.metadata?.from && event.metadata?.to" class="flex items-center gap-2 flex-wrap">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" :class="statusColor(event.metadata.from)">
                  {{ statusLabel(event.metadata.from) }}
                </span>
                <i class="ki-filled ki-arrow-right text-xs text-gray-400"></i>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" :class="statusColor(event.metadata.to)">
                  {{ statusLabel(event.metadata.to) }}
                </span>
              </div>

              <!-- item_added -->
              <p v-else-if="event.event_type === 'item_added' && event.metadata" class="text-xs text-gray-600 dark:text-gray-400">
                <span class="font-medium">{{ event.metadata.description }}</span>
                — Qtd: {{ event.metadata.quantity }} × {{ formatCurrency(event.metadata.unit_price) }}
                = <span class="font-medium">{{ formatCurrency(event.metadata.subtotal) }}</span>
              </p>

              <!-- item_removed -->
              <p v-else-if="event.event_type === 'item_removed' && event.metadata" class="text-xs text-gray-600 dark:text-gray-400">
                <span class="font-medium">{{ event.metadata.description }}</span>
              </p>

              <!-- diagnosis_updated -->
              <p v-else-if="event.event_type === 'diagnosis_updated' && event.metadata?.new" class="text-xs text-gray-600 dark:text-gray-400 line-clamp-2">
                {{ event.metadata.new }}
              </p>

              <!-- payment_received -->
              <p v-else-if="event.event_type === 'payment_received' && event.metadata" class="text-xs text-gray-600 dark:text-gray-400">
                <span class="font-medium text-green-600 dark:text-green-400">{{ formatCurrency(event.metadata.amount) }}</span>
                <span v-if="event.metadata.payment_method"> via {{ paymentMethodLabel(event.metadata.payment_method) }}</span>
              </p>

              <!-- payment_refunded -->
              <p v-else-if="event.event_type === 'payment_refunded' && event.metadata" class="text-xs text-gray-600 dark:text-gray-400">
                <span class="font-medium text-red-600 dark:text-red-400">{{ formatCurrency(event.metadata.refunded_amount) }}</span>
                <span v-if="event.metadata.reason"> — {{ event.metadata.reason }}</span>
              </p>

              <!-- note_added (desconto) -->
              <p v-else-if="event.event_type === 'note_added' && event.metadata?.new_discount !== undefined" class="text-xs text-gray-600 dark:text-gray-400">
                {{ formatCurrency(event.metadata.old_discount) }} → <span class="font-medium">{{ formatCurrency(event.metadata.new_discount) }}</span>
              </p>

            </div>

            <!-- Usuário -->
            <p v-if="event.user?.name" class="mt-1 text-xs text-gray-400 dark:text-gray-500">
              Por: {{ event.user.name }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { ServiceOrderStatusLabels, ServiceOrderStatusColors } from '@/Data/serviceOrderStatuses.js';

const props = defineProps({
  events: { type: Array, required: true },
});

const sortedEvents = computed(() =>
  [...props.events].sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
);

const EVENT_ICONS = {
  status_changed:    'ki-arrows-circle',
  item_added:        'ki-plus-circle',
  item_removed:      'ki-minus-circle',
  diagnosis_updated: 'ki-notepad-edit',
  payment_received:  'ki-dollar',
  payment_refunded:  'ki-arrows-square',
  note_added:        'ki-message-text',
};

const EVENT_BG = {
  status_changed:    'bg-blue-500',
  item_added:        'bg-green-500',
  item_removed:      'bg-red-400',
  diagnosis_updated: 'bg-orange-500',
  payment_received:  'bg-green-600',
  payment_refunded:  'bg-red-500',
  note_added:        'bg-gray-400',
};

const PAYMENT_METHOD_LABELS = {
  cash:        'Dinheiro',
  credit_card: 'Cartão de Crédito',
  debit_card:  'Cartão de Débito',
  pix:         'Pix',
  transfer:    'Transferência',
};

function eventIcon(type) {
  return EVENT_ICONS[type] ?? 'ki-information';
}

function eventIconBg(type) {
  return EVENT_BG[type] ?? 'bg-gray-400';
}

function eventTitle(event) {
  const { event_type, metadata } = event;
  switch (event_type) {
    case 'status_changed':
      if (metadata?.from === undefined) return 'OS criada';
      return 'Status alterado';
    case 'item_added':        return 'Item adicionado';
    case 'item_removed':      return 'Item removido';
    case 'diagnosis_updated': return 'Diagnóstico atualizado';
    case 'payment_received':  return 'Pagamento recebido';
    case 'payment_refunded':  return 'Pagamento reembolsado';
    case 'note_added':        return 'Desconto atualizado';
    default:                  return 'Evento';
  }
}

function statusLabel(status) {
  return ServiceOrderStatusLabels[status] ?? status;
}

function statusColor(status) {
  return ServiceOrderStatusColors[status] ?? 'bg-gray-100 text-gray-700';
}

function paymentMethodLabel(method) {
  return PAYMENT_METHOD_LABELS[method] ?? method;
}

function formatCurrency(value) {
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value || 0);
}

function formatDate(iso) {
  if (!iso) return '—';
  return new Intl.DateTimeFormat('pt-BR', {
    day: '2-digit', month: '2-digit', year: 'numeric',
    hour: '2-digit', minute: '2-digit',
  }).format(new Date(iso));
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
