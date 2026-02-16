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

          <!-- Tabs (só mostra aba Fornecedores se isEdit) -->
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

          <!-- Tab Content Container -->
          <div class="flex-1 overflow-y-auto">
            <!-- Tab Content: Dados do Produto -->
            <div 
              id="tab_product_data" 
              class="h-full"
            >
              <form
                class="flex flex-col gap-4 p-5"
                @submit.prevent="submitProductHandler"
              >
                <FormField name="name" label="Nome" v-slot="{ field, errors }">
                  <input v-bind="field" class="kt-input w-full" placeholder="Nome do produto" />
                  <FormError :errors="errors" />
                </FormField>

                <FormField name="description" label="Descrição" v-slot="{ field, errors }">
                  <textarea
                    v-bind="field"
                    class="kt-input w-full h-24"
                    rows="3"
                    placeholder="Descrição do produto (opcional)"
                  />
                  <FormError :errors="errors" />
                </FormField>

                <FormField name="category" label="Categoria" v-slot="{ field, errors }">
                  <select v-bind="field" class="kt-input w-full">
                    <option value="">Selecione</option>
                    <option v-for="cat in productCategories" :key="cat.value" :value="cat.value">
                      {{ cat.label }}
                    </option>
                  </select>
                  <FormError :errors="errors" />
                </FormField>

                <FormField name="unit" label="Unidade" v-slot="{ field, errors }">
                  <select v-bind="field" class="kt-input w-full">
                    <option value="">Selecione</option>
                    <option v-for="unit in productUnits" :key="unit.value" :value="unit.value">
                      {{ unit.label }}
                    </option>
                  </select>
                  <FormError :errors="errors" />
                </FormField>

                <FormField name="unit_price" label="Preço Unitário" v-slot="{ field, errors }">
                  <input
                    :value="field.value"
                    class="kt-input w-full"
                    placeholder="R$ 0,00"
                    @input="applyMask('unit_price', maskCurrency, $event)"
                  />
                  <FormError :errors="errors" />
                </FormField>

                <FormField name="suggested_price" label="Preço Sugerido" v-slot="{ field, errors }">
                  <input
                    :value="field.value"
                    class="kt-input w-full"
                    placeholder="R$ 0,00 (opcional)"
                    @input="applyMask('suggested_price', maskCurrency, $event)"
                  />
                  <FormError :errors="errors" />
                </FormField>

                <FormField name="is_active" label="Status" v-slot="{ field, errors }">
                  <label class="flex items-center gap-2 cursor-pointer">
                    <input
                      type="checkbox"
                      :checked="field.value"
                      @change="setFieldValue('is_active', $event.target.checked)"
                      class="kt-checkbox"
                    />
                    <span class="text-sm text-gray-900 dark:text-gray-100">Produto ativo</span>
                  </label>
                  <FormError :errors="errors" />
                </FormField>

                <div class="flex justify-end gap-2 mt-4">
                  <button type="button" class="kt-btn kt-btn-ghost" @click="$emit('close')">
                    Cancelar
                  </button>
                  <button type="submit" class="kt-btn kt-btn-primary">
                    Salvar Produto
                  </button>
                </div>
              </form>
            </div>

            <!-- Tab Content: Fornecedores (só se isEdit) -->
            <div 
              v-if="isEdit"
              id="tab_product_suppliers" 
              class="hidden h-full"
            >
              <div class="p-5">
              <!-- Listagem de fornecedores -->
              <div v-if="supplierFormMode === 'list'">
                <div class="flex justify-between items-center mb-4">
                  <h3 class="text-md font-semibold text-gray-900 dark:text-gray-100">
                    Fornecedores Vinculados
                  </h3>
                  <button 
                    type="button"
                    class="kt-btn kt-btn-sm kt-btn-primary" 
                    @click="onAddSupplier"
                  >
                    <i class="ki-filled ki-plus"></i>
                    Adicionar Fornecedor
                  </button>
                </div>

                <div v-if="productSuppliers.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                  Nenhum fornecedor vinculado
                </div>

                <!-- Cards de fornecedores -->
                <div v-else class="grid gap-4">
                  <div
                    v-for="supplier in productSuppliers"
                    :key="supplier.id"
                    class="relative bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow"
                  >
                    <!-- Badge Preferencial (canto superior direito) -->
                    <div
                      v-if="supplier.pivot.is_preferred"
                      class="absolute top-3 right-3 flex items-center gap-1 px-2 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 text-xs font-medium rounded"
                    >
                      <i class="ki-filled ki-check-circle text-sm"></i>
                      Preferencial
                    </div>

                    <!-- Nome do Fornecedor -->
                    <h4 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3 pr-24">
                      {{ supplier.name }}
                    </h4>

                    <!-- Informações em Grid -->
                    <div class="grid grid-cols-2 gap-x-4 gap-y-2 mb-3">
                      <!-- Preço de Custo -->
                      <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Preço de Custo</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                          {{ formatCurrency(supplier.pivot.cost_price) }}
                        </p>
                      </div>

                      <!-- Prazo de Entrega -->
                      <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Prazo de Entrega</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                          {{ supplier.pivot.lead_time_days ? `${supplier.pivot.lead_time_days} dias` : '-' }}
                        </p>
                      </div>

                      <!-- SKU -->
                      <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">SKU do Fornecedor</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                          {{ supplier.pivot.supplier_sku || '-' }}
                        </p>
                      </div>

                      <!-- Quantidade Mínima -->
                      <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Qtd. Mínima</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                          {{ supplier.pivot.min_order_quantity || '-' }}
                        </p>
                      </div>
                    </div>

                    <!-- Observações (se houver) -->
                    <div v-if="supplier.pivot.notes" class="mb-3">
                      <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Observações</p>
                      <p class="text-sm text-gray-700 dark:text-gray-300 line-clamp-2">
                        {{ supplier.pivot.notes }}
                      </p>
                    </div>

                    <!-- Ações -->
                    <div class="flex gap-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                      <button
                        type="button"
                        class="kt-btn kt-btn-sm kt-btn-ghost flex-1"
                        @click="onEditSupplier(supplier)"
                      >
                        <i class="ki-filled ki-pencil"></i>
                        Editar
                      </button>
                      <button
                        type="button"
                        class="kt-btn kt-btn-sm kt-btn-light-danger"
                        @click="onRemoveSupplier(supplier.id)"
                      >
                        <i class="ki-filled ki-trash"></i>
                        Remover
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Formulário de adicionar/editar fornecedor -->
              <div v-if="supplierFormMode === 'add' || supplierFormMode === 'edit'">
                <div class="mb-4">
                  <h3 class="text-md font-semibold text-gray-900 dark:text-gray-100">
                    {{ supplierFormMode === 'add' ? 'Adicionar Fornecedor' : 'Editar Fornecedor' }}
                  </h3>
                </div>

                <form 
                  @submit.prevent="submitSupplierHandler" 
                  class="flex flex-col gap-4"
                >
                  <div class="relative">
                    <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                      Fornecedor <span class="text-red-500">*</span>
                    </label>
                    <input
                      v-model="supplierSearch"
                      type="text"
                      class="kt-input w-full"
                      :class="{ 'border-red-500': supplierFormErrors.supplier_id }"
                      placeholder="Buscar fornecedor..."
                      :disabled="supplierFormMode === 'edit'"
                      @input="onSupplierSearchInput"
                      @focus="showSupplierDropdown = true"
                      @blur="onSupplierBlur"
                    />
                    <div
                      v-if="showSupplierDropdown && !selectedSupplier && (loadingSuppliers || filteredSuppliers.length > 0)"
                      class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg max-h-60 overflow-auto"
                    >
                      <!-- Skeleton loading -->
                      <template v-if="loadingSuppliers">
                        <div
                          v-for="i in 3"
                          :key="'skeleton-' + i"
                          class="px-4 py-3 animate-pulse"
                        >
                          <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4 mb-2"></div>
                          <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
                        </div>
                      </template>
                      <!-- Lista de fornecedores -->
                      <template v-else>
                        <div
                          v-for="supplier in filteredSuppliers"
                          :key="supplier.id"
                          class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer"
                          @mousedown.prevent="selectSupplier(supplier)"
                        >
                          <div class="font-medium text-gray-900 dark:text-gray-100">{{ supplier.name }}</div>
                          <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ supplier.document_number || supplier.email }}
                          </div>
                        </div>
                      </template>
                    </div>
                    <p v-if="supplierFormErrors.supplier_id" class="text-sm text-red-500 mt-1">
                      {{ supplierFormErrors.supplier_id }}
                    </p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                      SKU do Fornecedor
                    </label>
                    <input
                      v-model="supplierSku"
                      type="text"
                      maxlength="50"
                      class="kt-input w-full"
                      :class="{ 'border-red-500': supplierFormErrors.supplier_sku }"
                      placeholder="Código do produto no fornecedor"
                    />
                    <p v-if="supplierFormErrors.supplier_sku" class="text-sm text-red-500 mt-1">
                      {{ supplierFormErrors.supplier_sku }}
                    </p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                      Preço de Custo <span class="text-red-500">*</span>
                    </label>
                    <input
                      :value="supplierCostPrice"
                      class="kt-input w-full"
                      :class="{ 'border-red-500': supplierFormErrors.cost_price }"
                      placeholder="R$ 0,00"
                      @input="onSupplierCostPriceInput"
                    />
                    <p v-if="supplierFormErrors.cost_price" class="text-sm text-red-500 mt-1">
                      {{ supplierFormErrors.cost_price }}
                    </p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                      Prazo de Entrega (dias)
                    </label>
                    <input
                      v-model="supplierLeadTimeDays"
                      type="number"
                      min="1"
                      class="kt-input w-full"
                      :class="{ 'border-red-500': supplierFormErrors.lead_time_days }"
                      placeholder="Ex: 7"
                    />
                    <p v-if="supplierFormErrors.lead_time_days" class="text-sm text-red-500 mt-1">
                      {{ supplierFormErrors.lead_time_days }}
                    </p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                      Quantidade Mínima de Pedido
                    </label>
                    <input
                      v-model="supplierMinOrderQuantity"
                      type="number"
                      min="1"
                      class="kt-input w-full"
                      :class="{ 'border-red-500': supplierFormErrors.min_order_quantity }"
                      placeholder="Ex: 10"
                    />
                    <p v-if="supplierFormErrors.min_order_quantity" class="text-sm text-red-500 mt-1">
                      {{ supplierFormErrors.min_order_quantity }}
                    </p>
                  </div>

                  <div>
                    <label class="flex items-center gap-2 cursor-pointer">
                      <input
                        type="checkbox"
                        v-model="supplierIsPreferred"
                        class="kt-checkbox"
                      />
                      <span class="text-sm text-gray-900 dark:text-gray-100">Fornecedor preferencial</span>
                    </label>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                      Marque se este é o fornecedor preferencial para este produto
                    </p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                      Observações
                    </label>
                    <textarea
                      v-model="supplierNotes"
                      class="kt-input w-full h-24"
                      :class="{ 'border-red-500': supplierFormErrors.notes }"
                      maxlength="2000"
                      rows="3"
                      placeholder="Informações adicionais sobre este fornecedor..."
                    />
                    <p v-if="supplierFormErrors.notes" class="text-sm text-red-500 mt-1">
                      {{ supplierFormErrors.notes }}
                    </p>
                  </div>

                  <div class="flex justify-end gap-2 mt-4">
                    <button type="button" class="kt-btn kt-btn-ghost" @click="onCancelSupplierForm">
                      Cancelar
                    </button>
                    <button type="submit" class="kt-btn kt-btn-primary">
                      {{ supplierFormMode === 'add' ? 'Adicionar' : 'Salvar' }}
                    </button>
                  </div>
                </form>
              </div>
            </div>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </teleport>
</template>

<script setup>
import { useForm } from 'vee-validate';
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import * as yup from 'yup';
import FormField from './FormField.vue';
import FormError from './FormError.vue';
import { useMasks } from '@/Composables/useMasks';
import { productCategories, productUnits } from '@/Data/productData';
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

/* ---------------------------
 * Initialize KTUI Tabs
 * --------------------------- */
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

/* ---------------------------
 * Validation schema
 * --------------------------- */
const schema = yup.object({
  name: yup.string().required('Nome é obrigatório'),
  description: yup.string().nullable(),
  category: yup.string().required('Categoria é obrigatória'),
  unit: yup.string().required('Unidade é obrigatória'),
  unit_price: yup.string().required('Preço unitário é obrigatório'),
  suggested_price: yup.string().nullable(),
  is_active: yup.boolean(),
});

/* ---------------------------
 * Form controller
 * --------------------------- */
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

/* ---------------------------
 * Apply mask helper
 * --------------------------- */
const applyMask = (fieldName, maskFn, event) => {
  const rawValue = event.target.value;
  const maskedValue = maskFn(rawValue);
  setFieldValue(fieldName, maskedValue);
  nextTick(() => {
    event.target.value = maskedValue;
  });
};

/* ---------------------------
 * Sync edit / create
 * --------------------------- */
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

/* ---------------------------
 * Submit product
 * --------------------------- */
const submitProductHandler = handleSubmit((values) => {
  const unmaskedData = {
    ...values,
    unit_price: unmaskCurrency(values.unit_price),
    suggested_price: values.suggested_price ? unmaskCurrency(values.suggested_price) : null,
  };
  emit('submit', unmaskedData);
});

/* ---------------------------
 * Supplier management
 * --------------------------- */
const supplierFormMode = ref('list'); // 'list', 'add', 'edit'
const productSuppliers = ref([]);
const availableSuppliers = ref([]);
const editingSupplierId = ref(null);

const supplierFormRef = ref(null);
const supplierFormValues = ref({
  supplier_id: '',
  cost_price: '',
  lead_time_days: '',
});

// Autocomplete de fornecedores
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

const applySupplierMask = (fieldName, maskFn, event) => {
  const rawValue = event.target.value;
  const maskedValue = maskFn(rawValue);
  event.target.value = maskedValue;
};

const onSupplierCostPriceInput = (event) => {
  const rawValue = event.target.value;
  const maskedValue = maskCurrency(rawValue);
  supplierCostPrice.value = maskedValue;
  supplierFormValues.value.cost_price = maskedValue;
  nextTick(() => {
    event.target.value = maskedValue;
  });
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
async function onSupplierSearchInput() {
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
  // Validação manual
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
  
  // Se houver erros, não submete
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
  
  // Atualiza a lista de fornecedores
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
  
  // Atualiza a lista de fornecedores
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
