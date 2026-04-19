<template>
  <teleport to="body">
    <Transition name="drawer">
      <div
        v-if="open"
        class="fixed inset-0 z-50 flex items-center justify-end"
        style="background: rgba(0, 0, 0, 0.5);"
      >
        <div class="w-full max-w-105 h-full bg-background border-l border-border shadow-xl flex flex-col">
          <div class="flex items-center justify-between px-5 py-4 border-b border-border">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
              {{ isEdit ? 'Editar Cliente' : 'Novo Cliente' }}
            </h2>
            <button
              type="button"
              class="inline-flex items-center justify-center rounded-lg font-medium transition-colors h-8 px-3 text-sm w-8 px-0 bg-gray-100 dark:bg-accent text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-accent"
              @click="$emit('close')"
            >
              <i class="ki-filled ki-cross"></i>
            </button>
          </div>

          <div v-if="isEdit" class="flex items-center border-b border-border px-5">
            <div class="flex gap-6">
              <button
                type="button"
                class="px-1 py-3 text-sm font-medium border-b-2 border-transparent transition-colors"
                :class="activeTab === 'client'
                  ? 'text-primary border-primary'
                  : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100'"
                @click="activeTab = 'client'"
              >
                Cliente
              </button>
              <button
                type="button"
                class="px-1 py-3 text-sm font-medium border-b-2 border-transparent transition-colors"
                :class="activeTab === 'tenants'
                  ? 'text-primary border-primary'
                  : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100'"
                @click="activeTab = 'tenants'"
              >
                Tenants
              </button>
            </div>
          </div>

          <form
            v-show="!isEdit || activeTab === 'client'"
            class="flex flex-col gap-4 p-5 flex-1 overflow-y-auto"
            @submit.prevent="submitHandler"
          >
            <FormField name="name" label="Nome" v-slot="{ field, errors }">
              <input v-bind="field" class="kt-input w-full" placeholder="Nome da empresa" />
              <FormError :errors="errors" />
            </FormField>

            <FormField name="email" label="Email" v-slot="{ field, errors }">
              <input v-bind="field" class="kt-input w-full" placeholder="Email" type="email" />
              <FormError :errors="errors" />
            </FormField>

            <FormField name="document" label="CNPJ / CPF" v-slot="{ field, errors }">
              <input
                v-bind="field"
                class="kt-input w-full"
                placeholder="CNPJ ou CPF"
                :maxlength="docLimit"
                @input="applyMask('document', maskDocument, $event)"
              />
              <FormError :errors="errors" />
            </FormField>

            <FormField name="phone" label="Telefone" v-slot="{ field, errors }">
              <input
                v-bind="field"
                class="kt-input w-full"
                placeholder="Telefone"
                :maxlength="phoneLimit"
                @input="applyMask('phone', maskPhone, $event)"
              />
              <FormError :errors="errors" />
            </FormField>

            <FormField name="zip_code" label="CEP" v-slot="{ field, errors }">
              <input
                v-bind="field"
                class="kt-input w-full"
                placeholder="CEP"
                :maxlength="9"
                @input="applyMask('zip_code', maskCEP, $event)"
                @blur="onCepBlur(field.value)"
              />
              <FormError :errors="errors" />
            </FormField>

            <FormField name="street" label="Endereço" v-slot="{ field, errors }">
              <input v-bind="field" class="kt-input w-full" placeholder="Rua, número" />
              <FormError :errors="errors" />
            </FormField>

            <FormField name="city" label="Cidade" v-slot="{ field, errors }">
              <input v-bind="field" class="kt-input w-full" placeholder="Cidade" />
              <FormError :errors="errors" />
            </FormField>

            <FormField name="state" label="Estado" v-slot="{ field, errors }">
              <select v-bind="field" class="kt-select w-full">
                <option value="">Selecione</option>
                <option v-for="s in brazilianStates" :key="s.value" :value="s.value">{{ s.label }}</option>
              </select>
              <FormError :errors="errors" />
            </FormField>

            <div v-if="!isEdit" class="rounded-lg border border-dashed border-border/60 p-4 space-y-4">
              <div>
                <p class="text-sm font-medium text-gray-800 dark:text-gray-200">Dados do Tenant</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  O tenant inicial sera criado junto com o cliente.
                </p>
              </div>

              <FormField name="domain" label="Domínio" v-slot="{ field, errors }">
                <input v-bind="field" class="kt-input w-full" placeholder="subdominio" />
                <FormError :errors="errors" />
              </FormField>

              <FormField name="trade_name" label="Nome Fantasia / Filial" v-slot="{ field, errors }">
                <input v-bind="field" class="kt-input w-full" placeholder="Ex: Oficina Centro, Filial Norte" />
                <FormError :errors="errors" />
              </FormField>

              <label class="flex items-center gap-2 cursor-pointer">
                <input
                  type="checkbox"
                  class="kt-checkbox"
                  :checked="values.tenant_is_active"
                  :disabled="values.tenant_is_trial"
                  @change="setFieldValue('tenant_is_active', $event.target.checked)"
                />
                <span class="text-sm text-gray-900 dark:text-gray-100">Tenant ativo</span>
              </label>

              <label class="flex items-center gap-2 cursor-pointer">
                <input
                  type="checkbox"
                  class="kt-checkbox"
                  :checked="values.tenant_is_trial"
                  @change="onCreateTrialToggle($event.target.checked)"
                />
                <span class="text-sm text-gray-900 dark:text-gray-100">Tenant em trial</span>
              </label>

              <FormField v-if="values.tenant_is_trial" name="trial_until" label="Trial até" v-slot="{ field, errors }">
                <input v-bind="field" class="kt-input w-full" type="date" :min="today" />
                <FormError :errors="errors" />
              </FormField>
            </div>

            <div class="flex justify-end gap-2 mt-4">
              <button type="button" class="kt-btn kt-btn-ghost" @click="$emit('close')">
                Cancelar
              </button>
              <button type="submit" class="kt-btn kt-btn-primary">
                Salvar
              </button>
            </div>
          </form>

          <div v-show="isEdit && activeTab === 'tenants'" class="flex flex-col gap-4 p-5 flex-1 overflow-y-auto">
            <div class="flex items-start justify-between gap-3">
              <div>
                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Tenants vinculados</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  Gerencie status, trial e crie novos tenants para este cliente.
                </p>
              </div>
              <button
                type="button"
                class="kt-btn kt-btn-sm kt-btn-primary"
                @click="toggleNewTenantForm"
              >
                {{ showNewTenantForm ? 'Fechar cadastro' : 'Novo tenant' }}
              </button>
            </div>

            <div v-if="showNewTenantForm" class="rounded-lg border border-dashed border-border/60 p-4 space-y-3">
              <div>
                <p class="text-sm font-medium text-gray-800 dark:text-gray-200">Novo tenant</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  Os dados principais serão reaproveitados do cliente atual.
                </p>
              </div>

              <div>
                <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Domínio <span class="text-red-500">*</span></label>
                <input v-model="newTenantForm.domain" class="kt-input w-full" placeholder="subdominio" />
                <p v-if="newTenantErrors.domain" class="text-xs text-red-500 mt-1">{{ newTenantErrors.domain }}</p>
              </div>

              <div>
                <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Nome Fantasia / Filial</label>
                <input v-model="newTenantForm.trade_name" class="kt-input w-full" placeholder="Ex: Oficina Centro, Filial Norte" />
              </div>

              <label class="flex items-center gap-2 cursor-pointer">
                <input
                  v-model="newTenantForm.is_active"
                  type="checkbox"
                  class="kt-checkbox"
                  :disabled="newTenantForm.is_trial"
                />
                <span class="text-sm text-gray-900 dark:text-gray-100">Tenant ativo</span>
              </label>

              <label class="flex items-center gap-2 cursor-pointer">
                <input
                  v-model="newTenantForm.is_trial"
                  type="checkbox"
                  class="kt-checkbox"
                  @change="onInlineTrialChange"
                />
                <span class="text-sm text-gray-900 dark:text-gray-100">Tenant em trial</span>
              </label>

              <div v-if="newTenantForm.is_trial">
                <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Trial até <span class="text-red-500">*</span></label>
                <input v-model="newTenantForm.trial_until" class="kt-input w-full" type="date" :min="today" />
                <p v-if="newTenantErrors.trial_until" class="text-xs text-red-500 mt-1">{{ newTenantErrors.trial_until }}</p>
              </div>

              <div class="flex justify-end gap-2">
                <button type="button" class="kt-btn kt-btn-sm kt-btn-ghost" @click="cancelNewTenantForm">
                  Cancelar
                </button>
                <button type="button" class="kt-btn kt-btn-sm kt-btn-primary" @click="submitNewTenant">
                  Salvar tenant
                </button>
              </div>
            </div>

            <div v-if="!clientTenants.length" class="rounded-lg border border-border/60 p-4 text-sm text-gray-500 dark:text-gray-400">
              Nenhum tenant associado.
            </div>

            <div v-for="tenant in clientTenants" :key="tenant.id" class="rounded-lg border border-border/60 p-4 space-y-3">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ tenant.domain }}</p>
                  <div class="flex items-center gap-2 mt-1">
                    <span class="kt-badge" :class="statusBadgeClass(tenantForms[tenant.id]?.status ?? tenant.status)">
                      {{ statusLabel(tenantForms[tenant.id]?.status ?? tenant.status) }}
                    </span>
                    <span v-if="tenantForms[tenant.id]?.trial_until" class="text-xs text-gray-500 dark:text-gray-400">
                      Trial até {{ formatTrialDate(tenantForms[tenant.id].trial_until) }}
                    </span>
                  </div>
                </div>

                <div class="flex items-center gap-2">
                  <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-lg font-medium transition-colors h-8 px-3 text-sm bg-gray-100 dark:bg-accent text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-accent"
                    @click="toggleTenantExpansion(tenant.id)"
                  >
                    {{ expandedTenantId === tenant.id ? 'Fechar' : 'Editar' }}
                  </button>
                  <button
                    type="button"
                    class="kt-btn kt-btn-sm"
                    :class="tenantForms[tenant.id]?.is_active ? 'kt-btn-danger' : 'kt-btn-success'"
                    @click="toggleTenantActivation(tenant.id)"
                  >
                    {{ tenantForms[tenant.id]?.is_active ? 'Desativar' : 'Ativar' }}
                  </button>
                </div>
              </div>

              <div v-if="expandedTenantId === tenant.id && tenantForms[tenant.id]" class="space-y-3 border-t border-border/60 pt-3">
                <div class="grid grid-cols-1 gap-3">
                  <div>
                    <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Domínio</label>
                    <input v-model="tenantForms[tenant.id].domain" class="kt-input w-full" />
                    <p v-if="tenantErrors[tenant.id]?.domain" class="text-xs text-red-500 mt-1">
                      {{ tenantErrors[tenant.id].domain }}
                    </p>
                  </div>

                  <div>
                    <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Nome Fantasia / Filial</label>
                    <input v-model="tenantForms[tenant.id].trade_name" class="kt-input w-full" placeholder="Ex: Oficina Centro, Filial Norte" />
                  </div>

                  <div class="grid grid-cols-2 gap-3">
                    <div>
                      <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Email</p>
                      <div class="kt-input w-full bg-gray-50 dark:bg-gray-800/50">{{ tenantForms[tenant.id].email }}</div>
                    </div>
                    <div>
                      <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Documento</p>
                      <div class="kt-input w-full bg-gray-50 dark:bg-gray-800/50">{{ tenantForms[tenant.id].document }}</div>
                    </div>
                  </div>

                  <div v-if="tenantForms[tenant.id].database_name">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Banco</p>
                    <div class="kt-input w-full bg-gray-50 dark:bg-gray-800/50">{{ tenantForms[tenant.id].database_name }}</div>
                  </div>

                  <label class="flex items-center gap-2 cursor-pointer">
                    <input
                      v-model="tenantForms[tenant.id].is_active"
                      type="checkbox"
                      class="kt-checkbox"
                      :disabled="tenantForms[tenant.id].is_trial"
                    />
                    <span class="text-sm text-gray-900 dark:text-gray-100">Tenant ativo</span>
                  </label>

                  <label class="flex items-center gap-2 cursor-pointer">
                    <input
                      v-model="tenantForms[tenant.id].is_trial"
                      type="checkbox"
                      class="kt-checkbox"
                      @change="onTenantTrialChange(tenant.id)"
                    />
                    <span class="text-sm text-gray-900 dark:text-gray-100">Tenant em trial</span>
                  </label>

                  <div v-if="tenantForms[tenant.id].is_trial">
                    <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">
                      Trial até <span class="text-red-500">*</span>
                    </label>
                    <input
                      v-model="tenantForms[tenant.id].trial_until"
                      class="kt-input w-full"
                      type="date"
                      :min="today"
                    />
                    <p v-if="tenantErrors[tenant.id]?.trial_until" class="text-xs text-red-500 mt-1">
                      {{ tenantErrors[tenant.id].trial_until }}
                    </p>
                  </div>
                </div>

                <div class="flex justify-end">
                  <button type="button" class="kt-btn kt-btn-sm kt-btn-primary" @click="submitTenantUpdate(tenant.id)">
                    Salvar tenant
                  </button>
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
import { computed, reactive, ref, watch } from 'vue';
import { useForm } from 'vee-validate';
import * as yup from 'yup';
import FormField from './FormField.vue';
import FormError from './FormError.vue';
import { useMasks } from '@/Composables/useMasks';
import { fetchAddressByCep } from '@/Composables/useCep';
import { brazilianStates } from '@/Data/brazilianStates.js';

const props = defineProps({
  open: Boolean,
  isEdit: Boolean,
  client: Object,
});

const emit = defineEmits(['close', 'submit', 'update-tenant', 'create-tenant']);

const { maskCEP, maskDocument, maskPhone, getDocMaxLength, getPhoneMaxLength } = useMasks();
const today = new Date().toISOString().split('T')[0];
const activeTab = ref('client');
const expandedTenantId = ref(null);
const showNewTenantForm = ref(false);
const tenantForms = ref({});
const tenantErrors = ref({});
const newTenantErrors = ref({});

const buildEmptyNewTenantForm = () => ({
  domain: '',
  trade_name: '',
  is_active: true,
  is_trial: false,
  trial_until: '',
});

const newTenantForm = reactive(buildEmptyNewTenantForm());

const schema = computed(() => {
  const baseSchema = {
    name: yup.string().required('Nome é obrigatório'),
    email: yup.string().email('Email inválido').required('Email é obrigatório'),
    document: yup.string().required('Documento é obrigatório'),
    phone: yup.string().nullable(),
    zip_code: yup.string().nullable(),
    street: yup.string().nullable(),
    city: yup.string().nullable(),
    state: yup.string().nullable(),
  };

  if (props.isEdit) {
    return yup.object(baseSchema);
  }

  return yup.object({
    ...baseSchema,
    domain: yup.string().required('Domínio é obrigatório'),
    trade_name: yup.string().nullable(),
    tenant_is_active: yup.boolean().default(true),
    tenant_is_trial: yup.boolean().default(false),
    trial_until: yup.string().nullable().when('tenant_is_trial', {
      is: true,
      then: (currentSchema) => currentSchema.required('A data de expiração do trial é obrigatória.'),
    }),
  });
});

const { handleSubmit, setValues, resetForm, setFieldValue, values } = useForm({
  validationSchema: schema,
  initialValues: {
    name: '',
    email: '',
    document: '',
    phone: '',
    zip_code: '',
    street: '',
    city: '',
    state: '',
    domain: '',
    trade_name: '',
    tenant_is_active: true,
    tenant_is_trial: false,
    trial_until: '',
  },
});

const docLimit = computed(() => getDocMaxLength(values.document || ''));
const phoneLimit = computed(() => getPhoneMaxLength(values.phone || ''));
const clientTenants = computed(() => props.client?.tenants ?? []);

const applyMask = (fieldName, maskFn, event) => {
  setFieldValue(fieldName, maskFn(event.target.value));
};

const statusLabel = (status) => {
  const labels = {
    active: 'Ativo',
    inactive: 'Inativo',
    trial: 'Trial',
  };

  return labels[status] ?? status;
};

const statusBadgeClass = (status) => {
  const classes = {
    active: 'kt-badge-success',
    inactive: 'kt-badge-danger',
    trial: 'kt-badge-warning',
  };

  return classes[status] ?? '';
};

const formatTrialDate = (value) => (value ? value.substring(0, 10) : '');

const normalizeTenantStatus = (form) => {
  if (form.is_trial) {
    return 'trial';
  }

  return form.is_active ? 'active' : 'inactive';
};

const buildTenantForm = (tenant) => ({
  id: tenant.id,
  name: tenant.name ?? props.client?.name ?? '',
  email: tenant.email ?? props.client?.email ?? '',
  document: tenant.document ?? props.client?.document ?? '',
  domain: tenant.domain ?? '',
  trade_name: tenant.trade_name ?? '',
  client_id: tenant.client_id ?? props.client?.id ?? '',
  database_name: tenant.database_name ?? '',
  is_active: tenant.status !== 'inactive',
  is_trial: tenant.status === 'trial',
  trial_until: tenant.trial_until ? tenant.trial_until.substring(0, 10) : '',
  status: tenant.status ?? 'active',
});

const hydrateTenantForms = (tenants) => {
  tenantForms.value = Object.fromEntries(tenants.map((tenant) => [tenant.id, buildTenantForm(tenant)]));
  tenantErrors.value = {};
};

const resetNewTenantForm = () => {
  Object.assign(newTenantForm, buildEmptyNewTenantForm());
  newTenantErrors.value = {};
};

const validateTenantForm = (form) => {
  const errors = {};

  if (!form.domain?.trim()) {
    errors.domain = 'O domínio é obrigatório.';
  }

  if (form.is_trial && !form.trial_until) {
    errors.trial_until = 'A data de expiração do trial é obrigatória.';
  }

  if (form.is_trial && form.trial_until && form.trial_until < today) {
    errors.trial_until = 'A data de expiração do trial deve ser hoje ou uma data futura.';
  }

  return errors;
};

watch(
  () => props.open,
  (isOpen) => {
    if (!isOpen) {
      activeTab.value = 'client';
      expandedTenantId.value = null;
      showNewTenantForm.value = false;
      resetNewTenantForm();
    }
  }
);

watch(
  () => props.client,
  (client) => {
    if (client) {
      setValues({
        name: client.name ?? '',
        email: client.email ?? '',
        document: client.document ?? '',
        phone: client.phone ?? '',
        zip_code: client.zip_code ?? '',
        street: client.street ?? '',
        city: client.city ?? '',
        state: client.state ?? '',
        domain: '',
        tenant_is_active: true,
        tenant_is_trial: false,
        trial_until: '',
      });
      hydrateTenantForms(client.tenants ?? []);
      resetNewTenantForm();
      return;
    }

    resetForm();
    hydrateTenantForms([]);
    resetNewTenantForm();
  },
  { immediate: true }
);

async function onCepBlur(value) {
  if (!value) return;

  const address = await fetchAddressByCep(value);

  if (!address) return;
  if (address.street) setFieldValue('street', address.street);
  if (address.city) setFieldValue('city', address.city);
  if (address.state) setFieldValue('state', address.state);
}

function onCreateTrialToggle(checked) {
  setFieldValue('tenant_is_trial', checked);

  if (checked) {
    setFieldValue('tenant_is_active', true);
    return;
  }

  setFieldValue('trial_until', '');
}

function toggleTenantExpansion(tenantId) {
  expandedTenantId.value = expandedTenantId.value === tenantId ? null : tenantId;
}

function onTenantTrialChange(tenantId) {
  const form = tenantForms.value[tenantId];

  if (!form) return;

  if (form.is_trial) {
    form.is_active = true;
    return;
  }

  form.trial_until = '';
}

function toggleTenantActivation(tenantId) {
  const form = tenantForms.value[tenantId];

  if (!form) return;

  form.is_active = !form.is_active;

  if (!form.is_active) {
    form.is_trial = false;
    form.trial_until = '';
  }

  submitTenantUpdate(tenantId);
}

function submitTenantUpdate(tenantId) {
  const form = tenantForms.value[tenantId];

  if (!form) return;

  const errors = validateTenantForm(form);
  tenantErrors.value = {
    ...tenantErrors.value,
    [tenantId]: errors,
  };

  if (Object.keys(errors).length > 0) {
    expandedTenantId.value = tenantId;
    return;
  }

  form.status = normalizeTenantStatus(form);

  emit('update-tenant', {
    id: tenantId,
    name: form.name,
    email: form.email,
    document: form.document,
    domain: form.domain,
    trade_name: form.trade_name || null,
    status: form.status,
    trial_until: form.is_trial ? form.trial_until : null,
    client_id: form.client_id || props.client?.id || null,
  });
}

function toggleNewTenantForm() {
  showNewTenantForm.value = !showNewTenantForm.value;

  if (!showNewTenantForm.value) {
    resetNewTenantForm();
  }
}

function cancelNewTenantForm() {
  showNewTenantForm.value = false;
  resetNewTenantForm();
}

function onInlineTrialChange() {
  if (newTenantForm.is_trial) {
    newTenantForm.is_active = true;
    return;
  }

  newTenantForm.trial_until = '';
}

function submitNewTenant() {
  const errors = validateTenantForm(newTenantForm);
  newTenantErrors.value = errors;

  if (Object.keys(errors).length > 0) {
    return;
  }

  emit('create-tenant', {
    name: props.client?.name ?? '',
    email: props.client?.email ?? '',
    document: props.client?.document ?? '',
    domain: newTenantForm.domain,
    trade_name: newTenantForm.trade_name || null,
    status: normalizeTenantStatus(newTenantForm),
    trial_until: newTenantForm.is_trial ? newTenantForm.trial_until : null,
    client_id: props.client?.id ?? null,
  });
}

const submitHandler = handleSubmit((formValues) => {
  emit('submit', formValues);
});
</script>

<style scoped>
.drawer-enter-active > div,
.drawer-leave-active > div {
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.drawer-enter-from > div,
.drawer-leave-to > div {
  transform: translateX(100%);
}

.drawer-enter-to > div,
.drawer-leave-from > div {
  transform: translateX(0);
}
</style>
