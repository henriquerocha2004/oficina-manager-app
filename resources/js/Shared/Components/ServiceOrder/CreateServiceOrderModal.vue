<template>
  <teleport to="body">
    <Transition name="modal">
      <div
        v-if="isVisible"
        ref="modalEl"
        class="kt-modal"
        data-kt-modal="true"
        id="create_service_order_modal"
      >
        <div class="kt-modal-content max-w-xl top-[8%]">
          <div class="kt-modal-header py-4 px-5">
            <h3 class="kt-modal-title">Nova Ordem de Serviço</h3>
            <button
              class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0 text-white"
              @click="close"
            >
              <i class="ki-filled ki-cross text-white"></i>
            </button>
          </div>

          <div class="kt-modal-body p-5 space-y-5">

            <!-- ETAPA 1: Cliente -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Cliente <span class="text-red-500">*</span>
              </label>

              <!-- Cliente selecionado -->
              <div v-if="selectedClient" class="flex items-center justify-between rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-3 py-2">
                <div>
                  <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ selectedClient.name }}</p>
                  <p v-if="selectedClient.phone" class="text-xs text-gray-500 dark:text-gray-400">{{ selectedClient.phone }}</p>
                </div>
                <button type="button" @click="clearClient" class="text-gray-400 hover:text-red-500 transition-colors ml-2">
                  <i class="ki-filled ki-cross text-sm"></i>
                </button>
              </div>

              <!-- Busca de cliente -->
              <template v-else>
                <div class="relative">
                  <input
                    ref="searchInput"
                    v-model="searchQuery"
                    type="text"
                    class="kt-input w-full"
                    placeholder="Buscar pelo nome..."
                    @input="onSearchInput"
                    @focus="showDropdown = true"
                    @blur="onSearchBlur"
                    autocomplete="off"
                  />
                  <div v-if="loadingSearch" class="absolute right-3 top-1/2 -translate-y-1/2">
                    <span class="text-gray-400 text-xs">...</span>
                  </div>

                  <!-- Dropdown de resultados (teleportado para body) -->
                  <teleport to="body">
                    <div
                      v-if="showDropdown && (searchResults.length > 0 || searchQuery.length >= 2)"
                      class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg max-h-48 overflow-y-auto"
                      :style="dropdownStyle"
                    >
                    <div v-if="searchResults.length > 0">
                      <button
                        v-for="client in searchResults"
                        :key="client.id"
                        type="button"
                        class="w-full text-left px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                        @mousedown.prevent="selectClient(client)"
                      >
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ client.name }}</p>
                        <p v-if="client.phone" class="text-xs text-gray-500 dark:text-gray-400">{{ client.phone }}</p>
                      </button>
                    </div>
                    <div v-else-if="!loadingSearch && searchQuery.length >= 2" class="px-3 py-2">
                      <p class="text-sm text-gray-500 dark:text-gray-400">Nenhum cliente encontrado.</p>
                      <button
                        type="button"
                        class="mt-1 text-sm text-orange-600 hover:text-orange-700 font-medium"
                        @mousedown.prevent="showNewClientForm = true; showDropdown = false"
                      >
                        + Criar novo cliente
                      </button>
                    </div>
                  </div>
                  </teleport>
                </div>

                <!-- Formulário novo cliente -->
                <div v-if="showNewClientForm" class="mt-3 p-3 border border-dashed border-gray-300 dark:border-gray-600 rounded-lg space-y-3">
                  <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Novo Cliente</p>
                  <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2">
                      <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Nome <span class="text-red-500">*</span></label>
                      <input v-model="newClient.name" type="text" class="kt-input w-full" placeholder="Nome completo" />
                    </div>
                    <div>
                      <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Telefone</label>
                      <input
                        v-model="newClient.phone"
                        type="text"
                        class="kt-input w-full"
                        placeholder="(11) 99999-9999"
                        :maxlength="phoneLengthLimit"
                        @input="applyClientMask('phone', maskPhone, $event)"
                      />
                    </div>
                    <div>
                      <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">CPF/CNPJ</label>
                      <input
                        v-model="newClient.document_number"
                        type="text"
                        class="kt-input w-full"
                        placeholder="000.000.000-00"
                        :maxlength="docLimit"
                        @input="applyClientMask('document_number', maskDocument, $event)"
                      />
                    </div>
                    <div class="col-span-2">
                      <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">E-mail</label>
                      <input v-model="newClient.email" type="email" class="kt-input w-full" placeholder="email@exemplo.com" />
                    </div>
                  </div>
                  <div class="flex gap-2 justify-end">
                    <button type="button" class="kt-btn kt-btn-sm kt-btn-light" @click="cancelNewClient">Cancelar</button>
                    <button
                      type="button"
                      class="kt-btn kt-btn-sm kt-btn-primary"
                      :disabled="!newClient.name || savingClient"
                      @click="saveNewClient"
                    >
                      <span v-if="savingClient">Salvando...</span>
                      <span v-else>Salvar Cliente</span>
                    </button>
                  </div>
                  <p v-if="clientError" class="text-xs text-red-500">{{ clientError }}</p>
                </div>
              </template>
            </div>

            <!-- ETAPA 2: Veículo (visível após cliente selecionado) -->
            <div v-if="selectedClient">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Veículo <span class="text-red-500">*</span>
              </label>

              <!-- Veículo selecionado -->
              <div v-if="selectedVehicle" class="flex items-center justify-between rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-3 py-2">
                <div>
                  <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                    {{ selectedVehicle.brand }} {{ selectedVehicle.model }}
                    <span v-if="selectedVehicle.year" class="text-gray-500">({{ selectedVehicle.year }})</span>
                  </p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">{{ selectedVehicle.license_plate }}</p>
                </div>
                <button type="button" @click="clearVehicle" class="text-gray-400 hover:text-red-500 transition-colors ml-2">
                  <i class="ki-filled ki-cross text-sm"></i>
                </button>
              </div>

              <!-- Lista de veículos do cliente -->
              <template v-else>
                <div v-if="loadingVehicles" class="text-sm text-gray-500 dark:text-gray-400 py-2">Carregando veículos...</div>
                <div v-else>
                  <div v-if="clientVehicles.length > 0" class="space-y-1 mb-2 max-h-40 overflow-y-auto">
                    <button
                      v-for="vehicle in clientVehicles"
                      :key="vehicle.id"
                      type="button"
                      class="w-full text-left px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-orange-400 hover:bg-orange-50 dark:hover:bg-orange-900/20 transition-colors"
                      @click="selectVehicle(vehicle)"
                    >
                      <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                        {{ vehicle.brand }} {{ vehicle.model }}
                        <span v-if="vehicle.year" class="text-gray-500">({{ vehicle.year }})</span>
                      </p>
                      <p class="text-xs text-gray-500 dark:text-gray-400">{{ vehicle.license_plate }}</p>
                    </button>
                  </div>
                  <p v-else class="text-sm text-gray-500 dark:text-gray-400 py-1">Nenhum veículo cadastrado para este cliente.</p>

                  <button
                    type="button"
                    class="mt-1 text-sm text-orange-600 hover:text-orange-700 font-medium"
                    @click="showNewVehicleForm = !showNewVehicleForm"
                  >
                    <i class="ki-filled ki-plus text-xs mr-1"></i>
                    Adicionar veículo
                  </button>
                </div>

                <!-- Formulário novo veículo -->
                <div v-if="showNewVehicleForm" class="mt-3 p-3 border border-dashed border-gray-300 dark:border-gray-600 rounded-lg space-y-3">
                  <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Novo Veículo</p>
                  <div class="grid grid-cols-2 gap-3">
                    <div>
                      <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Placa <span class="text-red-500">*</span></label>
                      <input v-model="newVehicle.license_plate" type="text" class="kt-input w-full" placeholder="ABC-1234" />
                    </div>
                    <div>
                      <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Tipo</label>
                      <select v-model="newVehicle.vehicle_type" class="kt-input w-full">
                        <option value="car">Carro</option>
                        <option value="motorcycle">Moto</option>
                        <option value="truck">Caminhão</option>
                        <option value="van">Van</option>
                      </select>
                    </div>
                    <div>
                      <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Marca <span class="text-red-500">*</span></label>
                      <input v-model="newVehicle.brand" type="text" class="kt-input w-full" placeholder="Honda" />
                    </div>
                    <div>
                      <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Modelo <span class="text-red-500">*</span></label>
                      <input v-model="newVehicle.model" type="text" class="kt-input w-full" placeholder="Civic" />
                    </div>
                    <div>
                      <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">
                        Ano <span class="text-red-500">*</span>
                      </label>
                      <input v-model="newVehicle.year" type="text" class="kt-input w-full" placeholder="2020" required />
                    </div>
                    <div>
                      <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Cor</label>
                      <input v-model="newVehicle.color" type="text" class="kt-input w-full" placeholder="Preto" />
                    </div>
                  </div>
                  <div class="flex gap-2 justify-end">
                    <button type="button" class="kt-btn kt-btn-sm kt-btn-light" @click="showNewVehicleForm = false">Cancelar</button>
                    <button
                      type="button"
                      class="kt-btn kt-btn-sm kt-btn-primary"
                      :disabled="!canSaveVehicle || savingVehicle"
                      @click="saveNewVehicle"
                    >
                      <span v-if="savingVehicle">Salvando...</span>
                      <span v-else>Salvar Veículo</span>
                    </button>
                  </div>
                  <p v-if="vehicleError" class="text-xs text-red-500">{{ vehicleError }}</p>
                </div>
              </template>
            </div>

            <!-- ETAPA 3: Descrição (visível após veículo selecionado) -->
            <div v-if="selectedClient && selectedVehicle">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Descrição do Problema
              </label>
              <textarea
                v-model="diagnosis"
                class="kt-input w-full"
                rows="6"
                style="min-height: 120px; resize: vertical;"
                placeholder="Descreva o problema relatado pelo cliente..."
              ></textarea>
            </div>

            <p v-if="createError" class="text-sm text-red-500">{{ createError }}</p>
          </div>

          <div class="kt-modal-footer px-5 py-4 flex justify-end gap-3">
            <button type="button" class="kt-btn kt-btn-light" @click="close">Cancelar</button>
            <button
              type="button"
              class="kt-btn kt-btn-primary"
              :disabled="!canCreate || creatingOrder"
              @click="createOrder"
            >
              <span v-if="creatingOrder">Criando...</span>
              <span v-else>Criar Rascunho</span>
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </teleport>
</template>

<script setup>
 import { ref, computed, nextTick } from 'vue';
 import { fetchClients, createClient } from '@/services/clientService.js';
 import { fetchVehiclesByClient, createVehicle } from '@/services/vehicleService.js';
 import { createServiceOrder } from '@/services/serviceOrderService.js';
 import { useMasks } from '@/Composables/useMasks';

const emit = defineEmits(['created', 'close']);

const { maskDocument, maskPhone, getDocMaxLength, getPhoneMaxLength, unmask } = useMasks();

const isVisible = ref(false);
const modalEl = ref(null);
const searchInput = ref(null);

// Cliente
const searchQuery = ref('');
const searchResults = ref([]);
const selectedClient = ref(null);
const showDropdown = ref(false);
const loadingSearch = ref(false);
const showNewClientForm = ref(false);
const newClient = ref({ name: '', email: '', document_number: '', phone: '' });
const savingClient = ref(false);
const clientError = ref('');

// Veículo
const clientVehicles = ref([]);
const selectedVehicle = ref(null);
const loadingVehicles = ref(false);
const showNewVehicleForm = ref(false);
const newVehicle = ref({ license_plate: '', vehicle_type: 'car', brand: '', model: '', year: '', color: '' });
const savingVehicle = ref(false);
const vehicleError = ref('');

// OS
const diagnosis = ref('');
const creatingOrder = ref(false);
const createError = ref('');

let searchTimer = null;

const docLimit = computed(() => getDocMaxLength(newClient.value.document_number || ''));

const phoneLengthLimit = computed(() => getPhoneMaxLength(newClient.value.phone || ''));

const dropdownStyle = computed(() => {
    if (!searchInput.value) return { display: 'none' };
    const rect = searchInput.value.getBoundingClientRect();
    return {
        position: 'fixed',
        top: `${rect.bottom + 4}px`,
        left: `${rect.left}px`,
        width: `${rect.width}px`,
        zIndex: 99999,
    };
});

function onSearchBlur() {
    setTimeout(() => { showDropdown.value = false; }, 200);
}

function applyClientMask(fieldName, maskFn, event) {
    const maskedValue = maskFn(event.target.value);
    newClient.value = {
        ...newClient.value,
        [fieldName]: maskedValue,
    };
}

const canSaveVehicle = computed(() =>
    newVehicle.value.license_plate && newVehicle.value.brand && newVehicle.value.model && newVehicle.value.year
);

const canCreate = computed(() => selectedClient.value && selectedVehicle.value);

function open() {
    reset();
    isVisible.value = true;
    nextTick(() => {
        if (!window.KTModal) {
            if (modalEl.value) modalEl.value.style.display = 'block';
            return;
        }
        let modal = window.KTModal.getInstance(modalEl.value);
        if (!modal) {
            window.KTModal.createInstances();
            modal = window.KTModal.getInstance(modalEl.value);
        }
        modal ? modal.show() : (modalEl.value && (modalEl.value.style.display = 'block'));
    });
}

function close() {
    if (window.KTModal && modalEl.value) {
        const modal = window.KTModal.getInstance(modalEl.value);
        if (modal) {
            modal.hide();
            setTimeout(() => { isVisible.value = false; emit('close'); }, 200);
            return;
        }
    }
    isVisible.value = false;
    emit('close');
}

function reset() {
    searchQuery.value = '';
    searchResults.value = [];
    selectedClient.value = null;
    showDropdown.value = false;
    showNewClientForm.value = false;
    newClient.value = { name: '', email: '', document_number: '', phone: '' };
    clientError.value = '';

    clientVehicles.value = [];
    selectedVehicle.value = null;
    showNewVehicleForm.value = false;
    newVehicle.value = { license_plate: '', vehicle_type: 'car', brand: '', model: '', year: '', color: '' };
    vehicleError.value = '';

    diagnosis.value = '';
    createError.value = '';
}

function onSearchInput() {
    showDropdown.value = true;
    clearTimeout(searchTimer);
    if (searchQuery.value.length < 2) {
        searchResults.value = [];
        return;
    }
    searchTimer = setTimeout(async () => {
        loadingSearch.value = true;
        try {
            const result = await fetchClients({ search: searchQuery.value, perPage: 10 });
            searchResults.value = result.items || [];
        } catch {
            searchResults.value = [];
        } finally {
            loadingSearch.value = false;
        }
    }, 300);
}

function selectClient(client) {
    selectedClient.value = client;
    showDropdown.value = false;
    showNewClientForm.value = false;
    searchQuery.value = '';
    searchResults.value = [];
    loadClientVehicles(client.id);
}

function clearClient() {
    selectedClient.value = null;
    selectedVehicle.value = null;
    clientVehicles.value = [];
    showNewVehicleForm.value = false;
}

function cancelNewClient() {
    showNewClientForm.value = false;
    newClient.value = { name: '', email: '', document_number: '', phone: '' };
    clientError.value = '';
}

async function saveNewClient() {
    if (!newClient.value.name) return;
    savingClient.value = true;
    clientError.value = '';
    const payload = {
        ...newClient.value,
        document_number: unmask(newClient.value.document_number),
        phone: unmask(newClient.value.phone),
    };
    const result = await createClient(payload);
    savingClient.value = false;
    if (result.success) {
        const created = result.data?.data?.client || result.data?.client || result.data;
        selectClient(created);
        showNewClientForm.value = false;
        newClient.value = { name: '', email: '', document_number: '', phone: '' };
    } else {
        clientError.value = result.error?.response?.data?.message || 'Erro ao salvar cliente.';
    }
}

async function loadClientVehicles(clientId) {
    loadingVehicles.value = true;
    const result = await fetchVehiclesByClient(clientId);
    loadingVehicles.value = false;
    clientVehicles.value = result.success ? (result.data || []) : [];
}

function selectVehicle(vehicle) {
    selectedVehicle.value = vehicle;
    showNewVehicleForm.value = false;
}

function clearVehicle() {
    selectedVehicle.value = null;
}

async function saveNewVehicle() {
    if (!canSaveVehicle.value) return;
    savingVehicle.value = true;
    vehicleError.value = '';
    const payload = {
        ...newVehicle.value,
        client_id: selectedClient.value.id,
    };
    const result = await createVehicle(payload);
    savingVehicle.value = false;
    if (result.success) {
        const created = result.data?.data?.vehicle || result.data?.vehicle || result.data;
        clientVehicles.value.push(created);
        selectVehicle(created);
        newVehicle.value = { license_plate: '', vehicle_type: 'car', brand: '', model: '', year: '', color: '' };
        showNewVehicleForm.value = false;
    } else {
        vehicleError.value = result.error?.response?.data?.message || 'Erro ao salvar veículo.';
    }
}

async function createOrder() {
    if (!canCreate.value) return;
    creatingOrder.value = true;
    createError.value = '';
    const result = await createServiceOrder({
        client_id: selectedClient.value.id,
        vehicle_id: selectedVehicle.value.id,
        diagnosis: diagnosis.value || null,
    });
    creatingOrder.value = false;
    if (result.success) {
        emit('created', result.data);
        close();
    } else {
        createError.value = result.error || 'Erro ao criar ordem de serviço.';
    }
}

// Close dropdown when clicking outside
defineExpose({ open });
</script>
