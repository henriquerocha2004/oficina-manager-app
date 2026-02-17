<template>
  <teleport to="body">
    <Transition name="drawer">
      <div
        v-if="open"
        class="fixed inset-0 z-50 flex items-center justify-end"
        style="background: rgba(0, 0, 0, 0.5);"
      >
        <div class="w-full max-w-105 h-full bg-background border-l border-border shadow-xl flex flex-col">
          <!-- Header -->
          <div class="flex items-center justify-between px-5 py-4 border-b border-border">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
              {{ isEdit ? 'Editar Produto' : 'Novo Produto' }}
            </h2>
            <button class="kt-btn kt-btn-sm kt-btn-icon" @click="$emit('close')">
              <i class="ki-filled ki-cross"></i>
            </button>
          </div>

          <div v-if="isEdit" class="kt-tabs kt-tabs-line px-5 border-b border-border" data-kt-tabs="true">
            <button 
              class="kt-tab-toggle py-3 active" 
              data-kt-tab-toggle="#tab_product_data"
            >
              Dados do Produto
            </button>
            <button 
              class="kt-tab-toggle py-3" 
              data-kt-tab-toggle="#tab_product_suppliers"
            >
              Fornecedores
            </button>
          </div>

          <div class="flex-1 overflow-y-auto">
            <div 
              id="tab_product_data" 
              class="h-full p-5"
            >
              <ProductForm
                :set-field-value="setFieldValue"
                @cancel="$emit('close')"
                @submit="submitProductHandler"
                @price-input="applyMask"
              />
            </div>

            <div 
              v-if="isEdit"
              id="tab_product_suppliers" 
              class="hidden h-full p-5"
            >
              <ProductSuppliersList
                v-if="supplierFormMode === 'list'"
                :suppliers="productSuppliers"
                :format-currency="formatCurrency"
                @add="onAddSupplier"
                @edit="onEditSupplier"
                @remove="onRemoveSupplier"
              />

              <ProductSupplierForm
                v-if="supplierFormMode === 'add' || supplierFormMode === 'edit'"
                :mode="supplierFormMode"
                :form-values="supplierFormState"
                :form-errors="supplierFormErrors"
                :supplier-search="supplierSearch"
                :selected-supplier="selectedSupplier"
                :filtered-suppliers="filteredSuppliers"
                :loading-suppliers="loadingSuppliers"
                :show-dropdown="showSupplierDropdown"
                @submit="submitSupplierHandler"
                @cancel="onCancelSupplierForm"
                @search-input="onSupplierSearchInput"
                @focus-supplier="showSupplierDropdown = true"
                @blur-supplier="onSupplierBlur"
                @select-supplier="selectSupplier"
                @update:supplier-sku="supplierSku = $event"
                @update:cost-price="onSupplierCostPriceInput"
                @update:lead-time-days="supplierLeadTimeDays = $event"
                @update:min-order-quantity="supplierMinOrderQuantity = $event"
                @update:is-preferred="supplierIsPreferred = $event"
                @update:notes="supplierNotes = $event"
              />
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </teleport>
</template>

<script setup>
import { useForm } from 'vee-validate';
import { ref, computed, watch, nextTick } from 'vue';
import * as yup from 'yup';
import ProductForm from './Product/ProductForm.vue';
import ProductSuppliersList from './Product/ProductSuppliersList.vue';
import ProductSupplierForm from './Product/ProductSupplierForm.vue';
import { useMasks } from '@/Composables/useMasks';
import { fetchSuppliers } from '@/services/supplierService';
import { attachSupplier, updateProductSupplier, detachSupplier } from '@/services/productService';
import { useToast } from '@/Shared/composables/useToast';

const { maskCurrency, unmaskCurrency } = useMasks();
const toast = useToast();

const props = defineProps({
  open: Boolean,
  isEdit: Boolean,
  product: Object,
});

const emit = defineEmits(['close', 'submit', 'supplier-updated']);

const initializeTabs = () => {
  if (props.isEdit) {
    nextTick(() => {
      const tabsElement = document.querySelector('[data-kt-tabs="true"]');
      if (tabsElement && window.KTTabs) {
        window.KTTabs.getOrCreateInstance(tabsElement);
      }
    });
  }
};

watch(() => props.open, (isOpen) => {
  if (isOpen) {
    initializeTabs();
  }
});

const schema = yup.object({
  name: yup.string().required('Nome é obrigatório'),
  description: yup.string().nullable(),
  category: yup.string().required('Categoria é obrigatória'),
  unit: yup.string().required('Unidade é obrigatória'),
  unit_price: yup.string().required('Preço unitário é obrigatório'),
  suggested_price: yup.string().nullable(),
  is_active: yup.boolean(),
});

const {
  handleSubmit,
  setValues,
  resetForm,
  setFieldValue,
  values,
} = useForm({
  validationSchema: schema,
  initialValues: computed(() => {
    if (props.product) {
      return {
        ...props.product,
        unit_price: props.product.unit_price ? maskCurrency(String(props.product.unit_price * 100)) : '',
        suggested_price: props.product.suggested_price ? maskCurrency(String(props.product.suggested_price * 100)) : '',
      };
    }
    return {
      name: '',
      description: '',
      category: '',
      unit: '',
      unit_price: '',
      suggested_price: '',
      is_active: true,
    };
  }),
});

const applyMask = (fieldName, maskFn, event) => {
  const rawValue = event.target.value;
  const maskedValue = maskFn(rawValue);
  setFieldValue(fieldName, maskedValue);
  nextTick(() => {
    event.target.value = maskedValue;
  });
};

watch(
  () => props.product,
  (val) => {
    if (val) {
      setValues({
        ...val,
        unit_price: val.unit_price ? maskCurrency(String(val.unit_price * 100)) : '',
        suggested_price: val.suggested_price ? maskCurrency(String(val.suggested_price * 100)) : '',
      });
      loadProductSuppliers();
    } else {
      resetForm();
    }
  }
);

const submitProductHandler = handleSubmit((values) => {
  const unmaskedData = {
    ...values,
    unit_price: unmaskCurrency(values.unit_price),
    suggested_price: values.suggested_price ? unmaskCurrency(values.suggested_price) : null,
  };
  emit('submit', unmaskedData);
});

const supplierFormMode = ref('list');
const productSuppliers = ref([]);
const availableSuppliers = ref([]);
const editingSupplierId = ref(null);

const supplierFormRef = ref(null);
const supplierFormValues = ref({
  supplier_id: '',
  cost_price: '',
  lead_time_days: '',
});

const supplierSearch = ref('');
const showSupplierDropdown = ref(false);
const filteredSuppliers = ref([]);
const loadingSuppliers = ref(false);
const debounceTimer = ref(null);
const selectedSupplier = ref(null);
const supplierCostPrice = ref('');
const supplierLeadTimeDays = ref('');
const supplierSku = ref('');
const supplierMinOrderQuantity = ref('');
const supplierIsPreferred = ref(false);
const supplierNotes = ref('');
const supplierFormErrors = ref({});

const supplierFormState = computed(() => ({
  supplier_id: supplierFormValues.value.supplier_id,
  supplier_sku: supplierSku.value,
  cost_price: supplierCostPrice.value,
  lead_time_days: supplierLeadTimeDays.value,
  min_order_quantity: supplierMinOrderQuantity.value,
  is_preferred: supplierIsPreferred.value,
  notes: supplierNotes.value,
}));

const applySupplierMask = (fieldName, maskFn, event) => {
  const rawValue = event.target.value;
  const maskedValue = maskFn(rawValue);
  event.target.value = maskedValue;
};

const onSupplierCostPriceInput = (eventOrValue) => {
  const rawValue = typeof eventOrValue === 'string' ? eventOrValue : eventOrValue.target.value;
  const maskedValue = maskCurrency(rawValue);
  supplierCostPrice.value = maskedValue;
  supplierFormValues.value.cost_price = maskedValue;
  if (typeof eventOrValue !== 'string') {
    nextTick(() => {
      eventOrValue.target.value = maskedValue;
    });
  }
};

const loadProductSuppliers = () => {
  if (props.product && props.product.suppliers) {
    productSuppliers.value = props.product.suppliers;
  }
};

const loadAvailableSuppliers = async () => {
  const result = await fetchSuppliers({ page: 1, perPage: 1000, search: '' });
  availableSuppliers.value = result.items;
};

// Autocomplete de fornecedores com debounce
async function onSupplierSearchInput(value) {
  if (typeof value === 'string') {
    supplierSearch.value = value;
  }
  
  if (debounceTimer.value) {
    clearTimeout(debounceTimer.value);
  }

  debounceTimer.value = setTimeout(async () => {
    if (supplierSearch.value.length < 2) {
      filteredSuppliers.value = [];
      return;
    }

    loadingSuppliers.value = true;
    try {
      const result = await fetchSuppliers({
        search: supplierSearch.value,
        perPage: 10,
        page: 1,
      });
      filteredSuppliers.value = result.items;
      showSupplierDropdown.value = true;
    } catch (error) {
      console.error('Erro ao buscar fornecedores:', error);
      filteredSuppliers.value = [];
    } finally {
      loadingSuppliers.value = false;
    }
  }, 300);
}

function selectSupplier(supplier) {
  selectedSupplier.value = supplier;
  supplierFormValues.value.supplier_id = supplier.id;
  supplierSearch.value = supplier.name;
  showSupplierDropdown.value = false;
}

function onSupplierBlur() {
  setTimeout(() => {
    showSupplierDropdown.value = false;
  }, 200);
}

const formatCurrency = (value) => {
  if (!value) return 'R$ 0,00';
  return maskCurrency(String(value * 100));
};

const onAddSupplier = () => {
  supplierFormValues.value = {
    supplier_id: '',
    cost_price: '',
    lead_time_days: '',
  };
  supplierSearch.value = '';
  selectedSupplier.value = null;
  filteredSuppliers.value = [];
  supplierCostPrice.value = '';
  supplierLeadTimeDays.value = '';
  supplierSku.value = '';
  supplierMinOrderQuantity.value = '';
  supplierIsPreferred.value = false;
  supplierNotes.value = '';
  supplierFormErrors.value = {};
  supplierFormMode.value = 'add';
};

const onEditSupplier = (supplier) => {
  editingSupplierId.value = supplier.id;
  const maskedPrice = maskCurrency(String(supplier.pivot.cost_price * 100));
  supplierFormValues.value = {
    supplier_id: supplier.id,
    cost_price: maskedPrice,
    lead_time_days: supplier.pivot.lead_time_days,
  };
  supplierSearch.value = supplier.name;
  selectedSupplier.value = supplier;
  supplierCostPrice.value = maskedPrice;
  supplierLeadTimeDays.value = supplier.pivot.lead_time_days || '';
  supplierSku.value = supplier.pivot.supplier_sku || '';
  supplierMinOrderQuantity.value = supplier.pivot.min_order_quantity || '';
  supplierIsPreferred.value = supplier.pivot.is_preferred || false;
  supplierNotes.value = supplier.pivot.notes || '';
  supplierFormErrors.value = {};
  supplierFormMode.value = 'edit';
};

const onCancelSupplierForm = () => {
  supplierFormValues.value = {
    supplier_id: '',
    cost_price: '',
    lead_time_days: '',
  };
  supplierSearch.value = '';
  selectedSupplier.value = null;
  filteredSuppliers.value = [];
  supplierCostPrice.value = '';
  supplierLeadTimeDays.value = '';
  supplierSku.value = '';
  supplierMinOrderQuantity.value = '';
  supplierIsPreferred.value = false;
  supplierNotes.value = '';
  supplierFormErrors.value = {};
  editingSupplierId.value = null;
  supplierFormMode.value = 'list';
};

const submitSupplierHandler = async () => {
  supplierFormErrors.value = {};
  
  if (!supplierFormValues.value.supplier_id) {
    supplierFormErrors.value.supplier_id = 'Fornecedor é obrigatório';
  }
  
  if (!supplierCostPrice.value) {
    supplierFormErrors.value.cost_price = 'Preço de custo é obrigatório';
  }
  
  if (supplierLeadTimeDays.value && supplierLeadTimeDays.value < 1) {
    supplierFormErrors.value.lead_time_days = 'Prazo deve ser no mínimo 1 dia';
  }
  
  if (supplierMinOrderQuantity.value && supplierMinOrderQuantity.value < 1) {
    supplierFormErrors.value.min_order_quantity = 'Quantidade mínima deve ser no mínimo 1';
  }
  
  if (supplierSku.value && supplierSku.value.length > 50) {
    supplierFormErrors.value.supplier_sku = 'SKU deve ter no máximo 50 caracteres';
  }
  
  if (supplierNotes.value && supplierNotes.value.length > 2000) {
    supplierFormErrors.value.notes = 'Observações devem ter no máximo 2000 caracteres';
  }
  
  if (Object.keys(supplierFormErrors.value).length > 0) {
    return;
  }

  const supplierData = {
    supplier_id: supplierFormValues.value.supplier_id,
    cost_price: unmaskCurrency(supplierCostPrice.value),
    lead_time_days: supplierLeadTimeDays.value || null,
    supplier_sku: supplierSku.value || null,
    min_order_quantity: supplierMinOrderQuantity.value || null,
    is_preferred: supplierIsPreferred.value,
    notes: supplierNotes.value || null,
  };

  let result;
  if (supplierFormMode.value === 'add') {
    result = await attachSupplier(props.product.id, supplierData);
  } else {
    result = await updateProductSupplier(props.product.id, editingSupplierId.value, {
      cost_price: supplierData.cost_price,
      lead_time_days: supplierData.lead_time_days,
      supplier_sku: supplierData.supplier_sku,
      min_order_quantity: supplierData.min_order_quantity,
      is_preferred: supplierData.is_preferred,
      notes: supplierData.notes,
    });
  }

  if (!result.success) {
    toast.error('Erro ao ' + (supplierFormMode.value === 'add' ? 'adicionar' : 'atualizar') + ' fornecedor: ' + (result.error.response?.data?.message || result.error.message));
    return;
  }

  toast.success('Fornecedor ' + (supplierFormMode.value === 'add' ? 'adicionado' : 'atualizado') + ' com sucesso!');
  
  if (result.data && result.data.data && result.data.data.product) {
    productSuppliers.value = result.data.data.product.suppliers || [];
  }
  
  emit('supplier-updated');
  onCancelSupplierForm();
};

const onRemoveSupplier = async (supplierId) => {
  if (!confirm('Tem certeza que deseja remover este fornecedor?')) {
    return;
  }

  const result = await detachSupplier(props.product.id, supplierId);
  
  if (!result.success) {
    toast.error('Erro ao remover fornecedor: ' + (result.error.response?.data?.message || result.error.message));
    return;
  }

  toast.success('Fornecedor removido com sucesso!');
  
  if (result.data && result.data.data && result.data.data.product) {
    productSuppliers.value = result.data.data.product.suppliers || [];
  }
  
  emit('supplier-updated');
};

watch(() => props.open, (isOpen) => {
  if (isOpen && props.isEdit) {
    loadProductSuppliers();
  }
  if (!isOpen) {
    supplierFormMode.value = 'list';
    supplierFormValues.value = {
      supplier_id: '',
      cost_price: '',
      lead_time_days: '',
    };
    supplierSearch.value = '';
    selectedSupplier.value = null;
    filteredSuppliers.value = [];
    supplierCostPrice.value = '';
    supplierLeadTimeDays.value = '';
    supplierSku.value = '';
    supplierMinOrderQuantity.value = '';
    supplierIsPreferred.value = false;
    supplierNotes.value = '';
    supplierFormErrors.value = {};
  }
});
</script>

<style scoped>
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

/* Custom tab colors - Orange theme */
:deep(.kt-tab-toggle.active) {
  color: rgb(234 88 12) !important; /* text-orange-600 */
  border-bottom-color: rgb(234 88 12) !important; /* border-orange-600 */
}

:deep(.dark .kt-tab-toggle.active) {
  color: rgb(251 146 60) !important; /* text-orange-400 */
  border-bottom-color: rgb(251 146 60) !important; /* border-orange-400 */
}

:deep(.kt-tab-toggle:hover) {
  color: rgb(234 88 12) !important; /* text-orange-600 */
}

:deep(.dark .kt-tab-toggle:hover) {
  color: rgb(251 146 60) !important; /* text-orange-400 */
}
</style>
