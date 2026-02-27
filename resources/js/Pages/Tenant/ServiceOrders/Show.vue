<template>
  <TenantLayout :title="`OS ${serviceOrder?.code || ''}`" :breadcrumbs="breadcrumbs">
    <div class="kt-container-fixed w-full py-4 px-2">
      <div v-if="loading" class="flex items-center justify-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
      </div>

      <div v-else-if="error" class="flex items-center justify-center py-12">
        <div class="text-center">
          <p class="text-red-500 mb-2">Erro ao carregar ordem de serviço</p>
          <button class="kt-btn kt-btn-primary" @click="load">
            Tentar novamente
          </button>
        </div>
      </div>

      <div v-else-if="serviceOrder" class="space-y-4">
        <div class="card bg-white dark:bg-card border border-border rounded-lg">
          <div class="card-header px-6 py-4 border-b border-neutral-300 dark:border-white/20 flex items-center justify-between">
            <div class="flex items-center gap-4">
              <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                Ordem de Serviço
              </h2>
              <span class="text-lg font-bold text-gray-600 dark:text-gray-400">
                {{ serviceOrder.code }}
              </span>
              <span
                :class="[
                  'text-sm px-3 py-1 rounded-full font-medium',
                  getStatusColor(serviceOrder.status)
                ]"
              >
                {{ getStatusLabel(serviceOrder.status) }}
              </span>
            </div>
            <button class="kt-btn kt-btn-ghost" @click="goBack">
              <i class="ki-filled ki-left mr-2"></i>
              Voltar
            </button>
          </div>

          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase mb-3">
                  Cliente
                </h3>
                <div class="space-y-2">
                  <div class="flex items-center gap-2">
                    <i class="ki-filled ki-profile-circle text-gray-400"></i>
                    <span class="font-medium text-gray-900 dark:text-gray-100">
                      {{ serviceOrder.client.name }}
                    </span>
                  </div>
                  <div class="flex items-center gap-2">
                    <i class="ki-filled ki-sms text-gray-400"></i>
                    <span class="text-gray-600 dark:text-gray-400">
                      {{ serviceOrder.client.email }}
                    </span>
                  </div>
                  <div class="flex items-center gap-2">
                    <i class="ki-filled ki-call text-gray-400"></i>
                    <span class="text-gray-600 dark:text-gray-400">
                      {{ serviceOrder.client.phone }}
                    </span>
                  </div>
                </div>
              </div>

              <div>
                <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase mb-3">
                  Veículo
                </h3>
                <div class="space-y-2">
                  <div class="flex items-center gap-2">
                    <i class="ki-filled ki-car text-gray-400"></i>
                    <span class="font-medium text-gray-900 dark:text-gray-100">
                      {{ serviceOrder.vehicle.brand }} {{ serviceOrder.vehicle.model }}
                    </span>
                  </div>
                  <div class="flex items-center gap-2">
                    <i class="ki-filled ki-id text-gray-400"></i>
                    <span class="text-gray-600 dark:text-gray-400">
                      {{ serviceOrder.vehicle.plate }}
                    </span>
                  </div>
                  <div class="flex items-center gap-2">
                    <i class="ki-filled ki-calendar text-gray-400"></i>
                    <span class="text-gray-600 dark:text-gray-400">
                      {{ serviceOrder.vehicle.year }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                  <div class="text-sm text-gray-500 dark:text-gray-400">Data de Entrada</div>
                  <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{ formatDate(serviceOrder.entry_date) }}
                  </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                  <div class="text-sm text-gray-500 dark:text-gray-400">Previsão de Entrega</div>
                  <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{ formatDate(serviceOrder.expected_delivery) }}
                  </div>
                </div>
                <div v-if="serviceOrder.completion_date" class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                  <div class="text-sm text-gray-500 dark:text-gray-400">Data de Conclusão</div>
                  <div class="text-lg font-semibold text-green-600 dark:text-green-400">
                    {{ formatDate(serviceOrder.completion_date) }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="card bg-white dark:bg-card border border-border rounded-lg">
          <div class="card-header px-6 py-4 border-b border-neutral-300 dark:border-white/20">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
              Diagnóstico
            </h3>
          </div>
          <div class="p-6">
            <p class="text-gray-700 dark:text-gray-300">
              {{ serviceOrder.diagnosis || 'Sem diagnóstico registrado' }}
            </p>
          </div>
        </div>

        <div class="card bg-white dark:bg-card border border-border rounded-lg">
          <div class="card-header px-6 py-4 border-b border-neutral-300 dark:border-white/20">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
              Itens / Serviços
            </h3>
          </div>
          <div class="p-3 overflow-x-auto">
            <table class="min-w-full table-auto">
              <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700">
                  <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 dark:text-gray-400">Descrição</th>
                  <th class="px-4 py-3 text-center text-sm font-semibold text-gray-600 dark:text-gray-400">Tipo</th>
                  <th class="px-4 py-3 text-center text-sm font-semibold text-gray-600 dark:text-gray-400">Qtd</th>
                  <th class="px-4 py-3 text-right text-sm font-semibold text-gray-600 dark:text-gray-400">Valor Unit.</th>
                  <th class="px-4 py-3 text-right text-sm font-semibold text-gray-600 dark:text-gray-400">Total</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                <tr v-for="item in serviceOrder.items" :key="item.id">
                  <td class="px-4 py-3 text-gray-900 dark:text-gray-100">
                    {{ item.description }}
                  </td>
                  <td class="px-4 py-3 text-center">
                    <span
                      :class="[
                        'text-xs px-2 py-1 rounded-full font-medium',
                        item.type === 'labor' 
                          ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'
                          : 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200'
                      ]"
                    >
                      {{ item.type === 'labor' ? 'Mão de Obra' : 'Peça' }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-400">
                    {{ item.quantity }}
                  </td>
                  <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-400">
                    {{ formatCurrency(item.unit_price) }}
                  </td>
                  <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-gray-100">
                    {{ formatCurrency(item.total) }}
                  </td>
                </tr>
              </tbody>
              <tfoot class="bg-gray-50 dark:bg-gray-800">
                <tr>
                  <td colspan="4" class="px-4 py-3 text-right font-semibold text-gray-600 dark:text-gray-400">
                    Subtotal
                  </td>
                  <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-gray-100">
                    {{ formatCurrency(serviceOrder.subtotal) }}
                  </td>
                </tr>
                <tr v-if="serviceOrder.discount > 0">
                  <td colspan="4" class="px-4 py-3 text-right text-gray-600 dark:text-gray-400">
                    Desconto
                  </td>
                  <td class="px-4 py-3 text-right text-red-600 dark:text-red-400">
                    -{{ formatCurrency(serviceOrder.discount) }}
                  </td>
                </tr>
                <tr class="border-t-2 border-gray-200 dark:border-gray-700">
                  <td colspan="4" class="px-4 py-3 text-right font-bold text-lg text-gray-900 dark:text-gray-100">
                    Total
                  </td>
                  <td class="px-4 py-3 text-right font-bold text-lg text-gray-900 dark:text-gray-100">
                    {{ formatCurrency(serviceOrder.total) }}
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <div class="card bg-white dark:bg-card border border-border rounded-lg">
          <div class="card-header px-6 py-4 border-b border-neutral-300 dark:border-white/20">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
              Histórico
            </h3>
          </div>
          <div class="p-6">
            <div class="relative">
              <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700"></div>
              <div class="space-y-6">
                <div 
                  v-for="(event, index) in serviceOrder.history" 
                  :key="event.id"
                  class="relative pl-10"
                >
                  <div class="absolute left-2.5 w-3 h-3 rounded-full bg-primary border-2 border-white dark:border-gray-800"></div>
                  <div>
                    <div class="font-semibold text-gray-900 dark:text-gray-100">
                      {{ event.action }}
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                      {{ event.description }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                      {{ event.user }} • {{ formatDateTime(event.created_at) }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4">
          <button 
            v-if="canCancel"
            class="kt-btn kt-btn-danger"
            @click="onCancel"
          >
            Cancelar OS
          </button>
          <button 
            v-if="canApprove"
            class="kt-btn kt-btn-primary"
            @click="onApprove"
          >
            Aprovar OS
          </button>
          <button 
            v-if="canStartWork"
            class="kt-btn kt-btn-primary"
            @click="onStartWork"
          >
            Iniciar Trabalho
          </button>
          <button 
            v-if="canComplete"
            class="kt-btn kt-btn-success"
            @click="onComplete"
          >
            Concluir OS
          </button>
        </div>
      </div>
    </div>
  </TenantLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { fetchServiceOrderById } from '@/services/serviceOrderService.js';
import { 
  ServiceOrderStatusLabels, 
  ServiceOrderStatusColors, 
  ServiceOrderStatus 
} from '@/Data/serviceOrderStatuses.js';

const props = defineProps({
  id: {
    type: String,
    required: true,
  },
});

const serviceOrder = ref(null);
const loading = ref(true);
const error = ref(null);

const breadcrumbs = computed(() => [
  { label: 'Ordens de Serviço', href: '/service-orders' },
  { label: serviceOrder.value?.code || 'Detalhes' },
]);

function getStatusLabel(status) {
  return ServiceOrderStatusLabels[status] || status;
}

function getStatusColor(status) {
  return ServiceOrderStatusColors[status] || 'bg-gray-100 text-gray-800';
}

function formatDate(date) {
  return new Date(date).toLocaleDateString('pt-BR');
}

function formatDateTime(dateTime) {
  return new Date(dateTime).toLocaleString('pt-BR');
}

function formatCurrency(value) {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  }).format(value);
}

function goBack() {
  router.get('/service-orders');
}

const canCancel = computed(() => {
  if (!serviceOrder.value) return false;
  const s = serviceOrder.value.status;
  return s !== ServiceOrderStatus.COMPLETED && s !== ServiceOrderStatus.CANCELLED;
});

const canApprove = computed(() => {
  if (!serviceOrder.value) return false;
  return serviceOrder.value.status === ServiceOrderStatus.WAITING_APPROVAL;
});

const canStartWork = computed(() => {
  if (!serviceOrder.value) return false;
  return serviceOrder.value.status === ServiceOrderStatus.APPROVED;
});

const canComplete = computed(() => {
  if (!serviceOrder.value) return false;
  return serviceOrder.value.status === ServiceOrderStatus.IN_PROGRESS;
});

function onCancel() {
  console.log('Cancel OS');
}

function onApprove() {
  console.log('Approve OS');
}

function onStartWork() {
  console.log('Start Work');
}

function onComplete() {
  console.log('Complete OS');
}

async function load() {
  loading.value = true;
  error.value = null;

  try {
    const result = await fetchServiceOrderById(props.id);
    if (result.success) {
      serviceOrder.value = result.data;
    } else {
      error.value = result.error;
    }
  } catch (e) {
    error.value = e;
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  load();
});
</script>

<style>
.kt-container-fixed {
  box-sizing: border-box;
  width: 100%;
  max-width: 100%;
  margin: 0 auto;
}

@media (min-width: 640px) {
  .kt-container-fixed { max-width: 640px; }
}
@media (min-width: 768px) {
  .kt-container-fixed { max-width: 768px; }
}
@media (min-width: 1024px) {
  .kt-container-fixed { max-width: 1400px; }
}
@media (min-width: 1280px) {
  .kt-container-fixed { max-width: 1600px; }
}
@media (max-width: 640px) {
  html, body {
    font-size: 1.1rem !important;
    zoom: 1 !important;
  }
}

.kt-btn-danger {
  padding: 0.5rem 1rem;
  background-color: #dc2626;
  color: white;
  border-radius: 0.5rem;
  font-weight: 500;
  transition: background-color 0.2s;
  border: none;
  cursor: pointer;
}

.kt-btn-danger:hover {
  background-color: #b91c1c;
}

.kt-btn-success {
  padding: 0.5rem 1rem;
  background-color: #16a34a;
  color: white;
  border-radius: 0.5rem;
  font-weight: 500;
  transition: background-color 0.2s;
  border: none;
  cursor: pointer;
}

.kt-btn-success:hover {
  background-color: #15803d;
}
</style>
