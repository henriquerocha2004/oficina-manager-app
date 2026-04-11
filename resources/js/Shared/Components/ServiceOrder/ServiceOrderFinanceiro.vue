<template>
  <div class="space-y-4">

    <!-- Banner: OS Cancelada com pagamentos estornados -->
    <div
      v-if="serviceOrder.status === 'cancelled' && totalPaid > 0"
      class="rounded-lg border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 p-4"
    >
      <div class="flex items-start gap-3">
        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/50 flex items-center justify-center">
          <i class="ki-filled ki-cross-circle text-red-600 dark:text-red-400 text-xl"></i>
        </div>
        <div class="flex-1">
          <h4 class="text-sm font-semibold text-red-900 dark:text-red-100 mb-1">
            Ordem de Serviço Cancelada
          </h4>
          <p class="text-sm text-red-700 dark:text-red-300">
            Todos os pagamentos foram estornados.
          </p>
        </div>
      </div>
    </div>

    <!-- Card 1: Resumo da OS -->
    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-5">
      <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">Resumo da OS</h3>

      <div class="space-y-2 text-sm">
        <div class="flex justify-between">
          <span class="text-gray-500 dark:text-gray-400">Serviços ({{ servicesCount }})</span>
          <span class="text-gray-700 dark:text-gray-300">{{ formatCurrency(serviceOrder.total_services || 0) }}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-gray-500 dark:text-gray-400">Peças ({{ partsCount }})</span>
          <span class="text-gray-700 dark:text-gray-300">{{ formatCurrency(serviceOrder.total_parts || 0) }}</span>
        </div>
        <div class="flex items-center justify-between gap-2">
          <span class="text-gray-500 dark:text-gray-400">Desconto</span>
          <div class="flex items-center gap-1.5">
            <span class="text-xs text-gray-400 dark:text-gray-500">R$</span>
            <input
              v-model.number="discountValue"
              type="number"
              min="0"
              step="0.01"
              class="kt-input text-right w-24 py-1 text-sm"
              :disabled="serviceOrder.status === 'cancelled'"
              @blur="handleDiscountUpdate"
              @keydown.enter.prevent="handleDiscountUpdate"
            />
          </div>
        </div>
        <div class="flex justify-between font-bold pt-2 border-t border-gray-100 dark:border-gray-800 text-base">
          <span class="text-gray-900 dark:text-gray-100">Total</span>
          <span class="text-gray-900 dark:text-gray-100">{{ formatCurrency(total) }}</span>
        </div>
      </div>
    </div>

    <!-- Card 2: Pagamentos -->
    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-5">

      <!-- Header -->
      <div class="flex items-center justify-between mb-3">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Pagamentos</h3>
        <div class="flex items-center gap-2">
          <transition name="fade">
            <span v-if="showSavedBadge" class="text-xs text-green-600 dark:text-green-400 flex items-center gap-1">
              <i class="ki-filled ki-check text-xs"></i> Salvo
            </span>
          </transition>
          <button
            v-if="history.length > 0"
            type="button"
            class="kt-btn kt-btn-xs"
            @click="openReceiptModal"
          >
            <i class="ki-filled ki-printer text-xs"></i>
            <span class="hidden sm:inline ml-1">Imprimir Recibo</span>
          </button>
        </div>
      </div>

      <!-- Pago / Restante -->
      <div class="flex justify-between text-sm mb-1">
        <span class="text-gray-700 dark:text-gray-300">
          Pago: <strong>{{ formatCurrency(totalPaid) }}</strong>
          <span v-if="totalRefunded > 0" class="text-red-500 dark:text-red-400 text-xs ml-1">
            (estornado: {{ formatCurrency(totalRefunded) }})
          </span>
        </span>
        <span v-if="isFullyPaid" class="text-green-600 dark:text-green-400 font-medium">Quitado</span>
        <span v-else class="text-orange-500 dark:text-orange-400 font-medium">
          Restante: {{ formatCurrency(outstanding) }}
        </span>
      </div>

      <!-- Progress bar -->
      <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mb-1">
        <div
          class="h-2 rounded-full transition-all duration-500"
          :class="progressBarClass"
          :style="{ width: `${percent}%` }"
        ></div>
      </div>
      <p class="text-xs text-gray-400 dark:text-gray-500 text-right mb-4">
        <span v-if="serviceOrder.status === 'cancelled'">Cancelada</span>
        <span v-else>{{ Math.round(percent) }}% pago</span>
      </p>

      <!-- Histórico -->
      <div v-if="history.length > 0" class="mb-4">
        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2">Histórico</p>
        <div class="space-y-2">
          <div
            v-for="item in history"
            :key="item.id"
            class="flex items-center justify-between p-2.5 rounded-lg border"
            :class="item.type === 'refund'
              ? 'bg-red-50 dark:bg-red-900/10 border-red-200 dark:border-red-800'
              : 'bg-gray-50 dark:bg-gray-800/50 border-gray-100 dark:border-gray-700'"
          >
            <div class="flex items-center gap-2.5 min-w-0">
              <div
                class="w-7 h-7 rounded-full flex items-center justify-center shrink-0"
                :class="item.type === 'refund'
                  ? 'bg-red-100 dark:bg-red-900/30 text-red-500'
                  : 'bg-blue-100 dark:bg-blue-900/30 text-blue-600'"
              >
                <i class="text-xs" :class="item.type === 'refund' ? 'ki-filled ki-arrows-circle' : methodIcon(item.payment_method)"></i>
              </div>
              <div class="min-w-0">
                <div class="flex items-center gap-1.5">
                  <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                    {{ item.type === 'refund' ? 'Estorno' : methodLabel(item.payment_method) }}
                  </span>
                  <span
                    v-if="item.type === 'refund'"
                    class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400"
                  >
                    Estorno
                  </span>
                  <span v-if="item.installments && item.installments > 1" class="text-xs text-gray-500 dark:text-gray-400">
                    {{ item.installments }}x
                  </span>
                </div>
                <p class="text-xs text-gray-400 dark:text-gray-500 flex items-center gap-1 mt-0.5 truncate">
                  <i class="ki-filled ki-calendar text-[10px] shrink-0"></i>
                  {{ formatDate(item.paid_at) }}
                  <span v-if="item.notes" class="ml-1 truncate">· {{ item.notes }}</span>
                </p>
              </div>
            </div>
            <span
              class="text-sm font-semibold shrink-0"
              :class="item.type === 'refund' ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-gray-100'"
            >
              {{ item.type === 'refund' ? '-' : '' }}{{ formatCurrency(parseFloat(item.amount)) }}
            </span>
          </div>
        </div>
      </div>

      <!-- Empty state -->
      <div v-else-if="!showPaymentForm" class="text-center py-6 mb-3">
        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Nenhum pagamento registrado</p>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
          Registre adiantamentos ou pagamentos parciais a qualquer momento
        </p>
      </div>

      <!-- Formulário novo pagamento -->
      <div v-if="showPaymentForm" class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 mt-2">
        <div class="flex items-center justify-between mb-3">
          <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Novo Pagamento</p>
          <button type="button" class="text-xs text-gray-400 hover:text-gray-600" @click="closePaymentForm">
            Cancelar
          </button>
        </div>

        <!-- Método -->
        <div class="grid grid-cols-3 sm:grid-cols-6 gap-2 mb-3">
          <button
            v-for="m in paymentMethods"
            :key="m.value"
            type="button"
            class="flex flex-col items-center gap-1 py-2 px-1 rounded-lg border text-xs font-medium transition-colors"
            :class="paymentForm.method === m.value
              ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400'
              : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:border-gray-300'"
            @click="paymentForm.method = m.value"
          >
            <i class="text-base" :class="m.icon"></i>
            {{ m.label }}
          </button>
        </div>

        <!-- Valor + Parcelas (crédito) -->
        <div v-if="paymentForm.method === 'credit_card'" class="flex gap-3 mb-3">
          <div class="flex-1">
            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Valor (R$)</label>
            <input
              v-model.number="paymentForm.amount"
              type="number"
              min="0.01"
              step="0.01"
              class="kt-input w-full"
              placeholder="0,00"
            />
          </div>
          <div class="flex-1">
            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Parcelas</label>
            <select v-model.number="paymentForm.installments" class="kt-select w-full">
              <option v-for="n in 12" :key="n" :value="n">
                {{ n }}x {{ paymentForm.amount > 0 ? formatCurrency(paymentForm.amount / n) : '' }}
              </option>
            </select>
          </div>
        </div>

        <!-- Somente valor (outros métodos) -->
        <div v-else class="mb-3">
          <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Valor (R$)</label>
          <input
            v-model.number="paymentForm.amount"
            type="number"
            min="0.01"
            step="0.01"
            class="kt-input w-full"
            placeholder="0,00"
          />
        </div>

        <!-- Atalhos -->
        <div v-if="outstanding > 0" class="flex flex-wrap gap-2 mb-3">
          <button
            type="button"
            class="text-xs px-2 py-1 rounded border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:border-gray-300 transition-colors"
            @click="paymentForm.amount = parseFloat(outstanding.toFixed(2))"
          >
            Total restante ({{ formatCurrency(outstanding) }})
          </button>
          <button
            type="button"
            class="text-xs px-2 py-1 rounded border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:border-gray-300 transition-colors"
            @click="paymentForm.amount = parseFloat((outstanding / 2).toFixed(2))"
          >
            Metade ({{ formatCurrency(outstanding / 2) }})
          </button>
        </div>

        <!-- Observação -->
        <div class="mb-4">
          <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Observação (opcional)</label>
          <input
            v-model="paymentForm.notes"
            type="text"
            class="kt-input w-full"
            placeholder="Ex: Adiantamento, Sinal, Pagamento final..."
          />
        </div>

        <!-- Submit -->
        <button
          type="button"
          class="w-full kt-btn kt-btn-primary"
          :disabled="!paymentForm.amount || paymentForm.amount <= 0 || savingPayment"
          @click="handleRegisterPayment"
        >
          <span v-if="savingPayment">
            <i class="ki-filled ki-arrows-circle animate-spin text-xs mr-1"></i> Registrando...
          </span>
          <span v-else>
            <i class="ki-filled ki-check text-xs mr-1"></i>
            Registrar {{ paymentForm.amount > 0 ? formatCurrency(paymentForm.amount) : '' }}
          </span>
        </button>
      </div>

      <!-- Botão adicionar pagamento -->
      <button
        v-if="!showPaymentForm && serviceOrder.status !== 'cancelled'"
        type="button"
        class="mt-2 w-full py-2.5 border border-dashed border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-500 dark:text-gray-400 hover:border-orange-400 hover:text-orange-500 transition-colors"
        @click="openPaymentForm"
      >
        + Registrar Pagamento
      </button>
    </div>

    <!-- Card 3: Ações -->
    <div v-if="totalPaid > 0 && serviceOrder.status !== 'cancelled'" class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-5">
      <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Ações</h3>
      <div class="flex flex-wrap gap-2">
        <button type="button" class="kt-btn kt-btn-sm" @click="openRefundModal">
          <i class="ki-filled ki-arrows-circle text-xs"></i>
          Estornar Valor
        </button>
        <button type="button" class="kt-btn kt-btn-sm" style="color: #ef4444; border-color: #fca5a5;" @click="emit('cancel')">
          <i class="ki-filled ki-cross-circle text-xs"></i>
          Cancelar OS
        </button>
      </div>
    </div>

    <!-- Modal de Recibo -->
    <teleport to="body">
      <div
        v-if="showReceiptModal"
        class="fixed inset-0 z-[9999] flex items-center justify-center"
        style="background: rgba(0,0,0,0.5)"
        @click.self="showReceiptModal = false"
      >
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-2xl w-full max-w-md mx-4 p-6">
          <div class="flex items-center justify-between mb-1">
            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Gerar Recibo</h3>
            <button type="button" class="text-gray-400 hover:text-gray-600" @click="showReceiptModal = false">
              <i class="ki-filled ki-cross text-sm"></i>
            </button>
          </div>
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">
            Escolha o tipo, a forma e o valor para o recibo imprimível.
          </p>

          <!-- Tipo: Pagamento / Estorno -->
          <div class="flex gap-2 mb-4">
            <button
              type="button"
              class="flex-1 py-2 rounded-lg border text-sm font-medium transition-colors"
              :class="receiptForm.type === 'payment'
                ? 'border-orange-500 bg-orange-50 dark:bg-orange-900/20 text-orange-700 dark:text-orange-400'
                : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:border-gray-300'"
              @click="receiptForm.type = 'payment'; receiptForm.amount = 0"
            >
              <i class="ki-filled ki-dollar text-xs mr-1"></i>
              Pagamento
            </button>
            <button
              v-if="hasRefunds"
              type="button"
              class="flex-1 py-2 rounded-lg border text-sm font-medium transition-colors"
              :class="receiptForm.type === 'refund'
                ? 'border-red-500 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400'
                : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:border-gray-300'"
              @click="receiptForm.type = 'refund'; receiptForm.amount = 0"
            >
              <i class="ki-filled ki-arrows-circle text-xs mr-1"></i>
              Estorno
            </button>
          </div>

          <!-- Método -->
          <div class="grid grid-cols-4 gap-2 mb-4">
            <button
              v-for="m in paymentMethods"
              :key="m.value"
              type="button"
              class="flex flex-col items-center gap-1 py-2 px-1 rounded-lg border text-xs font-medium transition-colors"
              :class="receiptForm.method === m.value
                ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400'
                : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:border-gray-300'"
              @click="receiptForm.method = m.value"
            >
              <i class="text-base" :class="m.icon"></i>
              {{ m.label }}
            </button>
          </div>

          <!-- Valor -->
          <div class="mb-3">
            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Valor (R$)</label>
            <input
              v-model.number="receiptForm.amount"
              type="number"
              min="0.01"
              step="0.01"
              class="kt-input w-full text-lg"
              placeholder="0,00"
            />
          </div>

          <!-- Atalhos de valor -->
          <div class="flex flex-wrap gap-2 mb-6">
            <template v-if="receiptForm.type === 'payment'">
              <button
                type="button"
                class="text-xs px-2 py-1 rounded border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:border-gray-300 transition-colors"
                @click="receiptForm.amount = parseFloat(totalPaid.toFixed(2))"
              >
                Total pago ({{ formatCurrency(totalPaid) }})
              </button>
              <button
                v-if="outstanding > 0"
                type="button"
                class="text-xs px-2 py-1 rounded border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:border-gray-300 transition-colors"
                @click="receiptForm.amount = parseFloat(outstanding.toFixed(2))"
              >
                Restante ({{ formatCurrency(outstanding) }})
              </button>
              <button
                v-if="totalPaid > 0"
                type="button"
                class="text-xs px-2 py-1 rounded border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:border-gray-300 transition-colors"
                @click="receiptForm.amount = parseFloat((totalPaid / 2).toFixed(2))"
              >
                Metade ({{ formatCurrency(totalPaid / 2) }})
              </button>
              <button
                v-for="p in paymentHistory"
                :key="p.id"
                type="button"
                class="text-xs px-2 py-1 rounded border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:border-gray-300 transition-colors"
                @click="receiptForm.amount = parseFloat(p.amount)"
              >
                {{ methodLabel(p.payment_method) }} {{ formatCurrency(parseFloat(p.amount)) }}
              </button>
            </template>
            <template v-else>
              <button
                type="button"
                class="text-xs px-2 py-1 rounded border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:border-gray-300 transition-colors"
                @click="receiptForm.amount = parseFloat(totalRefunded.toFixed(2))"
              >
                Total estornado ({{ formatCurrency(totalRefunded) }})
              </button>
              <button
                v-for="r in refundHistory"
                :key="r.id"
                type="button"
                class="text-xs px-2 py-1 rounded border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:border-gray-300 transition-colors"
                @click="receiptForm.amount = parseFloat(r.amount)"
              >
                Estorno {{ formatCurrency(parseFloat(r.amount)) }}
              </button>
            </template>
          </div>

          <div class="flex gap-3">
            <button type="button" class="kt-btn flex-1" @click="showReceiptModal = false">Cancelar</button>
            <a
              :href="receiptUrl"
              target="_blank"
              class="kt-btn kt-btn-primary flex-1 text-center"
              :class="{ 'opacity-50 pointer-events-none': !receiptForm.amount || receiptForm.amount <= 0 }"
              @click="showReceiptModal = false"
            >
              <i class="ki-filled ki-printer text-xs mr-1"></i>
              Gerar Recibo
            </a>
          </div>
        </div>
      </div>
    </teleport>

    <!-- Modal de Estorno -->
    <teleport to="body">
      <div
        v-if="showRefundModal"
        class="fixed inset-0 z-[9999] flex items-center justify-center"
        style="background: rgba(0,0,0,0.5)"
        @click.self="showRefundModal = false"
      >
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-2xl w-full max-w-md mx-4 p-6">
          <div class="flex items-center justify-between mb-1">
            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Estornar Valor</h3>
            <button type="button" class="text-gray-400 hover:text-gray-600" @click="showRefundModal = false">
              <i class="ki-filled ki-cross text-sm"></i>
            </button>
          </div>
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">
            Registre um estorno parcial ou total para o cliente. O valor será subtraído do montante pago.
          </p>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Valor do Estorno (R$)</label>
            <input
              v-model.number="refundForm.amount"
              type="number"
              min="0.01"
              :max="totalPaid"
              step="0.01"
              class="kt-input w-full text-lg"
              placeholder="0,00"
            />
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Total pago ({{ formatCurrency(totalPaid) }})</p>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Forma de Devolução</label>
            <div class="grid grid-cols-4 gap-2">
              <button
                v-for="m in paymentMethods"
                :key="m.value"
                type="button"
                class="flex flex-col items-center gap-1 py-2 px-1 rounded-lg border text-xs font-medium transition-colors"
                :class="refundForm.method === m.value
                  ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400'
                  : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:border-gray-300'"
                @click="refundForm.method = m.value"
              >
                <i class="text-base" :class="m.icon"></i>
                {{ m.label }}
              </button>
            </div>
          </div>

          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Motivo</label>
            <input
              v-model="refundForm.notes"
              type="text"
              class="kt-input w-full"
              placeholder="Ex: Cliente desistiu do serviço, erro de cobrança..."
            />
          </div>

          <div class="flex gap-3">
            <button type="button" class="kt-btn flex-1" @click="showRefundModal = false">Cancelar</button>
            <button
              type="button"
              class="kt-btn kt-btn-danger flex-1"
              :disabled="!refundForm.amount || refundForm.amount <= 0 || refundForm.amount > totalPaid || !refundForm.notes || refundForm.notes.trim().length < 3 || savingRefund"
              @click="handleRegisterRefund"
            >
              <span v-if="savingRefund">
                <i class="ki-filled ki-arrows-circle animate-spin text-xs mr-1"></i> Confirmando...
              </span>
              <span v-else>
                <i class="ki-filled ki-arrows-circle text-xs mr-1"></i>
                Confirmar Estorno
              </span>
            </button>
          </div>
        </div>
      </div>
    </teleport>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { registerPayment, registerRefund, updateDiscount } from '@/services/serviceOrderService.js';
import { useToast } from '@/Shared/composables/useToast.js';

const props = defineProps({
  serviceOrder: { type: Object, required: true },
});

const emit = defineEmits(['cancel']);

const toast = useToast();

// ---- Estado ----
const showPaymentForm  = ref(false);
const showRefundModal  = ref(false);
const showReceiptModal = ref(false);
const showSavedBadge   = ref(false);
const savingPayment    = ref(false);
const savingRefund     = ref(false);
const paymentForm  = ref({ method: 'cash', amount: 0, installments: 1, notes: '' });
const refundForm   = ref({ amount: 0, method: 'cash', notes: '' });
const receiptForm  = ref({ type: 'payment', amount: 0, method: 'cash' });
const discountValue = ref(parseFloat(props.serviceOrder.discount) || 0);

// ---- Computed ----
const history = computed(() =>
  [...(props.serviceOrder.payments || [])]
    .sort((a, b) => new Date(b.paid_at) - new Date(a.paid_at))
);

const totalPaid   = computed(() => parseFloat(props.serviceOrder.paid_amount) || 0);
const outstanding = computed(() => parseFloat(props.serviceOrder.outstanding_balance) || 0);
const total       = computed(() => parseFloat(props.serviceOrder.total) || 0);
const isFullyPaid = computed(() => outstanding.value <= 0 && totalPaid.value > 0);
const percent     = computed(() => total.value > 0 ? Math.min(100, (totalPaid.value / total.value) * 100) : 0);
const progressBarClass = computed(() => {
  if (props.serviceOrder.status === 'cancelled') return 'bg-red-500';
  return isFullyPaid.value ? 'bg-green-500' : 'bg-blue-500';
});

const totalRefunded = computed(() =>
  history.value
    .filter(p => p.type === 'refund')
    .reduce((s, p) => s + parseFloat(p.amount), 0)
);

const paymentHistory = computed(() => history.value.filter(h => h.type !== 'refund'));
const refundHistory  = computed(() => history.value.filter(h => h.type === 'refund'));
const hasRefunds     = computed(() => refundHistory.value.length > 0);

const receiptUrl = computed(() => {
  if (!receiptForm.value.amount || receiptForm.value.amount <= 0) return '#';
  const params = new URLSearchParams({
    type:   receiptForm.value.type,
    amount: String(receiptForm.value.amount),
    method: receiptForm.value.method,
  });
  return `/service-orders/${props.serviceOrder.id}/receipt?${params}`;
});

const servicesCount = computed(() => props.serviceOrder.items?.filter(i => i.type === 'service').length ?? 0);
const partsCount    = computed(() => props.serviceOrder.items?.filter(i => i.type === 'part').length ?? 0);

// ---- Constantes ----
const paymentMethods = [
  { value: 'cash',        label: 'Dinheiro', icon: 'ki-filled ki-dollar'       },
  { value: 'credit_card', label: 'Crédito',  icon: 'ki-filled ki-simcard'      },
  { value: 'debit_card',  label: 'Débito',   icon: 'ki-filled ki-simcard'      },
  { value: 'pix',         label: 'PIX',      icon: 'ki-filled ki-scan-barcode' },
];

const METHOD_LABELS = {
  cash: 'Dinheiro', credit_card: 'Crédito', debit_card: 'Débito',
  pix: 'PIX', bank_transfer: 'Transferência', check: 'Cheque',
};

const METHOD_ICONS = {
  cash: 'ki-filled ki-dollar', credit_card: 'ki-filled ki-simcard',
  debit_card: 'ki-filled ki-simcard', pix: 'ki-filled ki-scan-barcode',
  bank_transfer: 'ki-filled ki-bank', check: 'ki-filled ki-document',
};

function methodLabel(method) { return METHOD_LABELS[method] || method; }
function methodIcon(method)  { return METHOD_ICONS[method]  || 'ki-filled ki-dollar'; }

// ---- Ações ----
function openPaymentForm() {
  paymentForm.value = { method: 'cash', amount: 0, installments: 1, notes: '' };
  showPaymentForm.value = true;
}

function closePaymentForm() {
  showPaymentForm.value = false;
}

function openRefundModal() {
  refundForm.value = { amount: 0, method: 'cash', notes: '' };
  showRefundModal.value = true;
}

function openReceiptModal() {
  receiptForm.value = { type: 'payment', amount: 0, method: 'cash' };
  showReceiptModal.value = true;
}

async function handleRegisterPayment() {
  if (!paymentForm.value.amount || paymentForm.value.amount <= 0) return;
  savingPayment.value = true;
  const result = await registerPayment(props.serviceOrder.id, {
    payment_method: paymentForm.value.method,
    amount: paymentForm.value.amount,
    installments: paymentForm.value.method === 'credit_card' ? paymentForm.value.installments : null,
    notes: paymentForm.value.notes || null,
  });
  savingPayment.value = false;
  if (result.success) {
    showPaymentForm.value = false;
    flashSaved();
    toast.success('Pagamento registrado com sucesso!');
    router.reload();
  } else {
    toast.error(result.error || 'Erro ao registrar pagamento.');
  }
}

async function handleRegisterRefund() {
  if (!refundForm.value.amount || refundForm.value.amount <= 0) return;
  if (!refundForm.value.notes || refundForm.value.notes.trim().length < 3) {
    toast.error('Informe o motivo do estorno.');
    return;
  }
  savingRefund.value = true;
  const result = await registerRefund(props.serviceOrder.id, {
    amount: refundForm.value.amount,
    payment_method: refundForm.value.method,
    notes: refundForm.value.notes,
  });
  savingRefund.value = false;
  if (result.success) {
    showRefundModal.value = false;
    refundForm.value = { amount: 0, method: 'cash', notes: '' };
    flashSaved();
    toast.success('Estorno registrado com sucesso!');
    router.reload();
  } else {
    toast.error(result.error || 'Erro ao estornar pagamento.');
  }
}

async function handleDiscountUpdate() {
  const newValue = discountValue.value ?? 0;
  if (newValue === (parseFloat(props.serviceOrder.discount) || 0)) return;
  const result = await updateDiscount(props.serviceOrder.id, newValue);
  if (result.success) {
    toast.success('Desconto atualizado!');
    router.reload();
  } else {
    toast.error(result.error || 'Erro ao atualizar desconto.');
    discountValue.value = parseFloat(props.serviceOrder.discount) || 0;
  }
}

function flashSaved() {
  showSavedBadge.value = true;
  setTimeout(() => { showSavedBadge.value = false; }, 3000);
}

function formatCurrency(value) {
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value || 0);
}

function formatDate(value) {
  if (!value) return '';
  return new Intl.DateTimeFormat('pt-BR', {
    day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit',
  }).format(new Date(value));
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
