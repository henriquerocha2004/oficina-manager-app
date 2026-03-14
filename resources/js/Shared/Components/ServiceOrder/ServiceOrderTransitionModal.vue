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
                  v-model="form.diagnosis"
                  class="kt-input"
                  rows="5"
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

                <div v-if="form.items.length === 0" class="text-center py-4 text-gray-500">
                  Nenhum item adicionado. Clique em "Adicionar Item".
                </div>

                <div v-else class="space-y-2">
                  <div
                    v-for="(item, index) in form.items"
                    :key="index"
                    class="flex gap-2 items-start p-2 bg-gray-50 dark:bg-gray-800 rounded"
                  >
                    <select v-model="item.type" class="kt-select w-24">
                      <option value="service">Serviço</option>
                      <option value="part">Peça</option>
                    </select>
                    <input
                      v-model="item.description"
                      type="text"
                      class="kt-input flex-1"
                      placeholder="Descrição"
                    />
                    <input
                      v-model.number="item.quantity"
                      type="number"
                      class="kt-input w-20"
                      placeholder="Qtd"
                      min="1"
                    />
                    <input
                      v-model.number="item.unit_price"
                      type="number"
                      class="kt-input w-28"
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
                  </div>
                </div>

                <div v-if="form.items.length > 0" class="mt-2 text-right">
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
              @click="close(true)"
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
import { ref, computed, onMounted, nextTick, reactive } from 'vue';

const emit = defineEmits(['confirm', 'cancel']);

const modalEl = ref(null);
const title = ref('Enviar para Aprovação');
const isVisible = ref(false);
let resolvePromise = null;

const fromStatus = ref('');
const toStatus = ref('');
const needsDiagnosis = ref(false);
const needsItems = ref(false);

const form = reactive({
  diagnosis: '',
  items: []
});

const totalItems = computed(() => {
  return form.items.reduce((sum, item) => {
    return sum + (item.quantity * item.unit_price);
  }, 0);
});

const isValid = computed(() => {
  if (needsDiagnosis.value && !form.diagnosis.trim()) {
    return false;
  }
  if (needsItems.value && form.items.length === 0) {
    return false;
  }
  if (needsItems.value) {
    const hasValidItems = form.items.some(item => 
      item.description.trim() && item.quantity > 0 && item.unit_price >= 0
    );
    if (!hasValidItems) {
      return false;
    }
  }
  return true;
});

const open = async (options = {}) => {
  fromStatus.value = options.fromStatus || '';
  toStatus.value = options.toStatus || '';
  needsDiagnosis.value = options.needsDiagnosis || false;
  needsItems.value = options.needsItems || false;
  title.value = options.title || 'Enviar para Aprovação';

  form.diagnosis = options.initialDiagnosis || '';
  form.items = options.initialItems ? [...options.initialItems] : [];

  if (form.items.length === 0 && needsItems.value) {
    addItem();
  }

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
    modalEl.value.style.display = 'block';
    return;
  }

  let modal = window.KTModal.getInstance(modalEl.value);
  if (!modal) {
    window.KTModal.createInstances();
    modal = window.KTModal.getInstance(modalEl.value);
  }

  modal ? modal.show() : modalEl.value.style.display = 'block';
};

const addItem = () => {
  form.items.push({
    type: 'service',
    description: '',
    quantity: 1,
    unit_price: 0
  });
};

const removeItem = (index) => {
  form.items.splice(index, 1);
};

const formatCurrency = (value) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value);
};

const close = (confirmed) => {
  const result = confirmed ? {
    diagnosis: form.diagnosis,
    items: form.items.filter(item => item.description.trim())
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
  if (!window.KTModal) {
    modalEl.value.style.display = 'none';
    return;
  }

  const modal = window.KTModal.getInstance(modalEl.value);
  modal ? modal.hide() : modalEl.value.style.display = 'none';
};

onMounted(() => {
  if (window.KTModal) {
    window.KTModal.createInstances();
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

.modal-enter-to,
.modal-leave-from {
  opacity: 1;
}

.modal-enter-to > div,
.modal-leave-from > div {
  transform: scale(1);
}
</style>
