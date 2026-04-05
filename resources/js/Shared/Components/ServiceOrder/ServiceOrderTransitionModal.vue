<template>
  <teleport to="body">
    <Transition name="modal">
      <div
        v-if="isVisible"
        class="kt-modal"
        data-kt-modal="true"
        id="service_order_transition_modal"
        ref="modalEl"
      >
        <div class="kt-modal-content max-w-2xl top-[10%]">
          <div class="kt-modal-header py-4 px-5">
            <h3 class="kt-modal-title">{{ title }}</h3>
            <button
              class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0 text-white"
              data-kt-modal-dismiss="true"
              @click="close(false)"
            >
              <i class="ki-filled ki-cross text-white"></i>
            </button>
          </div>
          <div class="kt-modal-body p-5">
            <div class="space-y-4">
              <div v-if="needsDiagnosis">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Diagnóstico <span class="text-red-500">*</span>
                </label>
                <textarea
                  v-model="formDiagnosis"
                  class="kt-input"
                  rows="8"
                  style="min-height: 150px; resize: vertical;"
                  placeholder="Descreva o diagnóstico ou serviço adicional..."
                ></textarea>
              </div>

              <div v-if="needsItems">
                <div class="flex justify-between items-center mb-2">
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Itens do Orçamento <span class="text-red-500">*</span>
                  </label>
                  <button
                    type="button"
                    class="kt-btn kt-btn-sm kt-btn-primary"
                    @click="addItem"
                  >
                    <i class="ki-filled ki-plus"></i>
                    Adicionar Item
                  </button>
                </div>

                <div v-if="!formItems || formItems.length === 0" class="text-center py-4 text-gray-500">
                  Nenhum item adicionado. Clique em "Adicionar Item".
                </div>

                <div v-else class="space-y-2">
                  <div
                    v-for="(item, index) in formItems"
                    :key="index"
                    class="flex gap-2 items-start p-2 bg-gray-50 dark:bg-gray-800 rounded"
                    :class="{ 'ring-2 ring-red-500': itemErrors[index] }"
                  >
                    <select v-model="item.type" class="kt-select w-24" @change="onTypeChange(index)">
                      <option value="service">Serviço</option>
                      <option value="part">Peça</option>
                    </select>
                    <div class="flex-1 relative">
                      <input
                        ref="serviceInputRefs"
                        v-model="item.description"
                        type="text"
                        class="kt-input w-full"
                        :class="{ 'border-red-500': itemErrors[index]?.description }"
                        :placeholder="item.type === 'service' ? 'Buscar serviço ou digite...' : 'Digite o nome da peça...'"
                        @input="onServiceSearch($event, index)"
                        @focus="(e) => onServiceFocus(e, index)"
                        @blur="onServiceBlur(index)"
                        @keydown.escape="hideDropdown(index)"
                        :data-index="index"
                      />
                      <teleport to="body">
                        <div
                          v-if="dropdownVisibleIndex === index && filteredServices.length > 0"
                          class="service-dropdown"
                          :style="getDropdownStyle(index)"
                        >
                          <div
                            v-for="service in filteredServices"
                            :key="service.id"
                            class="service-dropdown-item"
                            @mousedown.prevent="selectService(service, index)"
                          >
                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ service.name }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                              {{ formatCurrency(service.base_price) }}
                            </div>
                          </div>
                        </div>
                      </teleport>
                    </div>
                    <input
                      v-model.number="item.quantity"
                      type="number"
                      class="kt-input w-20"
                      :class="{ 'border-red-500': itemErrors[index]?.quantity }"
                      placeholder="Qtd"
                      min="1"
                    />
                    <input
                      v-model.number="item.unit_price"
                      type="number"
                      class="kt-input w-28"
                      :class="{ 'border-red-500': itemErrors[index]?.unit_price }"
                      placeholder="Preço"
                      step="0.01"
                      min="0"
                    />
                    <button
                      type="button"
                      class="kt-btn kt-btn-sm kt-btn-icon kt-btn-danger"
                      @click="removeItem(index)"
                    >
                      <i class="ki-filled ki-trash"></i>
                    </button>
                    <div v-if="itemErrors[index]" class="text-xs text-red-500 mt-1 w-full">
                      <span v-if="itemErrors[index].description">• Descrição obrigatória</span>
                      <span v-if="itemErrors[index].quantity"> • Quantidade obrigatória</span>
                      <span v-if="itemErrors[index].unit_price"> • Valor obrigatório</span>
                    </div>
                  </div>
                </div>

                <div v-if="formItems.length > 0 && Object.keys(itemErrors).length > 0" class="mt-2 text-sm text-red-500">
                  <i class="ki-filled ki-information"></i>
                  Preencha todos os campos dos itens ou remova os itens incompletos
                </div>

                <div v-if="formItems.length > 0" class="mt-2 text-right">
                  <span class="text-sm font-medium">Total: </span>
                  <span class="text-lg font-bold text-gray-900 dark:text-gray-100">
                    {{ formatCurrency(totalItems) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
          <div class="kt-modal-footer flex justify-end gap-2 p-5">
            <button
              class="kt-btn kt-btn-light !bg-gray-200 !text-gray-700 hover:!bg-gray-300"
              data-kt-modal-dismiss="true"
              @click="close(false)"
            >
              Cancelar
            </button>
            <button
              class="kt-btn kt-btn-primary"
              :disabled="!isValid"
              @click="onConfirmClick"
            >
              Confirmar
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </teleport>
</template>

<script setup>
import { ref, onMounted, nextTick, onUnmounted, watch } from 'vue';
import { fetchServices } from '@/services/serviceService.js';

const emit = defineEmits(['confirm', 'cancel']);

const modalEl = ref(null);
const serviceInputRefs = ref([]);
const title = ref('Enviar para Aprovação');
const isVisible = ref(false);
let resolvePromise = null;

const fromStatus = ref('');
const toStatus = ref('');
const needsDiagnosis = ref(false);
const needsItems = ref(false);

const availableServices = ref([]);
const loadingServices = ref(false);
const filteredServices = ref([]);
const dropdownVisibleIndex = ref(-1);
const itemErrors = ref({});
let searchTimeout = null;

const formDiagnosis = ref('');
const formItems = ref([]);
const isValid = ref(false);

const totalItems = ref(0);

const recomputeIsValid = () => {
  totalItems.value = formItems.value.reduce((sum, item) => {
    return sum + ((item.quantity || 0) * (item.unit_price || 0));
  }, 0);

  if (needsDiagnosis.value) {
    if (!formDiagnosis.value.trim()) {
      isValid.value = false;
      return;
    }
  }

  if (needsItems.value) {
    if (formItems.value.length === 0) {
      isValid.value = false;
      return;
    }

    for (const item of formItems.value) {
      if (!item.type || !item.type.trim()) { isValid.value = false; return; }
      if (!item.description || !item.description.trim()) { isValid.value = false; return; }
      if (!item.quantity || item.quantity <= 0) { isValid.value = false; return; }
      if (item.unit_price === undefined || item.unit_price === null || item.unit_price < 0) { isValid.value = false; return; }
    }
  }

  isValid.value = true;
};

watch([formItems, formDiagnosis, needsDiagnosis, needsItems], recomputeIsValid, { deep: true, immediate: true });

const validateAndShowErrors = () => {
  itemErrors.value = {};

  formItems.value.forEach((item, index) => {
    const errors = {};

    if (!item.type || !item.type.trim()) errors.type = true;
    if (!item.description || !item.description.trim()) errors.description = true;
    if (!item.quantity || item.quantity <= 0) errors.quantity = true;
    if (item.unit_price === undefined || item.unit_price === null || item.unit_price < 0) errors.unit_price = true;

    if (Object.keys(errors).length > 0) {
      itemErrors.value[index] = errors;
    }
  });
};

const open = async (options = {}) => {
  fromStatus.value = options.fromStatus || '';
  toStatus.value = options.toStatus || '';
  needsDiagnosis.value = options.needsDiagnosis || false;
  needsItems.value = options.needsItems || false;
  title.value = options.title || 'Enviar para Aprovação';

  formDiagnosis.value = options.initialDiagnosis || '';
  formItems.value = options.initialItems ? options.initialItems.map(item => ({
    ...item,
    service_id: item.service_id || null
  })) : [];

  if (formItems.value.length === 0 && needsItems.value) {
    addItem();
  }

  loadServices();
  dropdownVisibleIndex.value = -1;
  filteredServices.value = [];
  itemErrors.value = {};

  return new Promise((resolve) => {
    resolvePromise = resolve;
    isVisible.value = true;
    nextTick(() => {
      showModal();
    });
  });
};

const showModal = () => {
  if (!window.KTModal) {
    if (modalEl.value) {
      modalEl.value.style.display = 'block';
    }
    return;
  }

  let modal = window.KTModal.getInstance(modalEl.value);
  if (!modal) {
    window.KTModal.createInstances();
    modal = window.KTModal.getInstance(modalEl.value);
  }

  modal ? modal.show() : (modalEl.value && (modalEl.value.style.display = 'block'));
};

const addItem = () => {
  formItems.value.push({
    type: 'service',
    service_id: null,
    description: '',
    quantity: null,
    unit_price: null
  });
};

const removeItem = (index) => {
  formItems.value.splice(index, 1);
  // Limpar erro deste item
  const newErrors = { ...itemErrors.value };
  delete newErrors[index];
  itemErrors.value = newErrors;
};

const loadServices = async () => {
  if (availableServices.value.length > 0) return;
  
  loadingServices.value = true;
  try {
    const result = await fetchServices({ perPage: 100 });
    availableServices.value = result.items || [];
  } catch (error) {
    console.error('Erro ao carregar serviços:', error);
  } finally {
    loadingServices.value = false;
  }
};

const onServiceSearch = async (event, index) => {
  if (formItems.value[index]?.type !== 'service') return;

  const search = event.target.value;
  
  if (search.length < 2) {
    filteredServices.value = [];
    dropdownVisibleIndex.value = -1;
    return;
  }
  
  if (searchTimeout) clearTimeout(searchTimeout);
  
  searchTimeout = setTimeout(async () => {
    try {
      const result = await fetchServices({ search, perPage: 10 });
      filteredServices.value = result.items || [];
      dropdownVisibleIndex.value = index;
    } catch (error) {
      console.error('Erro ao buscar serviços:', error);
      filteredServices.value = [];
    }
  }, 300);
};

const onServiceFocus = (event, index) => {
  if (formItems.value[index]?.type !== 'service') return;

  const search = event.target.value;

  if (search.length >= 2) {
    onServiceSearch(event, index);
  }
};

const onTypeChange = (index) => {
  if (dropdownVisibleIndex.value === index) {
    dropdownVisibleIndex.value = -1;
    filteredServices.value = [];
  }
};

const onServiceBlur = (index) => {
  setTimeout(() => {
    if (dropdownVisibleIndex.value === index) {
      dropdownVisibleIndex.value = -1;
    }
  }, 200);
};

const hideDropdown = (index) => {
  dropdownVisibleIndex.value = -1;
};

const selectService = (service, index) => {
  const item = formItems.value[index];
  item.service_id = service.id;
  item.description = service.name;
  item.unit_price = parseFloat(service.base_price) || 0;
  dropdownVisibleIndex.value = -1;
  filteredServices.value = [];
};

const getDropdownStyle = (index) => {
  const inputs = serviceInputRefs.value;
  const inputEl = inputs[index];
  
  if (!inputEl) {
    return { display: 'none' };
  }
  
  const rect = inputEl.getBoundingClientRect();
  
  return {
    position: 'fixed',
    top: `${rect.bottom + window.scrollY}px`,
    left: `${rect.left + window.scrollX}px`,
    width: `${rect.width}px`,
    zIndex: 99999
  };
};

const formatCurrency = (value) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value);
};

const onConfirmClick = () => {
  if (needsItems.value) {
    validateAndShowErrors();
  }
  if (isValid.value) {
    close(true);
  }
};

const close = (confirmed) => {
  dropdownVisibleIndex.value = -1;
  
  const result = confirmed ? {
    diagnosis: formDiagnosis.value,
    items: formItems.value.filter(item => item.description.trim())
  } : null;

  if (resolvePromise) {
    resolvePromise(result);
    resolvePromise = null;
  }

  emit(confirmed ? 'confirm' : 'cancel', result);
  hideModal();
  isVisible.value = false;
};

const hideModal = () => {
  dropdownVisibleIndex.value = -1;
  
  if (!window.KTModal) {
    if (modalEl.value) {
      modalEl.value.style.display = 'none';
    }
    return;
  }

  const modal = window.KTModal.getInstance(modalEl.value);
  modal ? modal.hide() : (modalEl.value && (modalEl.value.style.display = 'none'));
};

onMounted(() => {
  if (window.KTModal) {
    window.KTModal.createInstances();
  }
});

onUnmounted(() => {
  if (searchTimeout) {
    clearTimeout(searchTimeout);
  }
});

defineExpose({ open });
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-active > div,
.modal-leave-active > div {
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-from > div,
.modal-leave-to > div {
  transform: scale(0.9);
}

.modal-enter-to > div,
.modal-leave-from > div {
  transform: scale(1);
}

.service-dropdown {
  background-color: white;
  border: 1px solid rgb(209, 213, 219);
  border-radius: 0.375rem;
  box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1);
  max-height: 12rem;
  overflow-y: auto;
}

.dark .service-dropdown {
  background-color: rgb(31 41 55);
  border-color: rgb(55 65 81);
}

.service-dropdown-item {
  padding: 0.5rem 0.75rem;
  cursor: pointer;
  border-bottom: 1px solid rgb(229 231 235);
}

.service-dropdown-item:last-child {
  border-bottom: none;
}

.service-dropdown-item:hover {
  background-color: rgb(243 244 246);
}

.dark .service-dropdown-item:hover {
  background-color: rgb(55 65 81);
}
</style>
