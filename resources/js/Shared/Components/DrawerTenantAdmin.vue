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
              {{ isEdit ? 'Editar Tenant' : 'Novo Tenant' }}
            </h2>
            <button class="kt-btn kt-btn-sm kt-btn-icon" @click="$emit('close')">
              <i class="ki-filled ki-cross"></i>
            </button>
          </div>

          <!-- Form -->
          <form
            class="flex flex-col gap-4 p-5 flex-1 overflow-y-auto"
            @submit.prevent="submitHandler"
          >
            <FormField name="name" label="Nome" v-slot="{ field, errors }">
              <input v-bind="field" class="kt-input w-full" placeholder="Nome do tenant" />
              <FormError :errors="errors" />
            </FormField>

            <FormField name="email" label="Email" v-slot="{ field, errors }">
              <input v-bind="field" class="kt-input w-full" placeholder="Email" type="email" />
              <FormError :errors="errors" />
            </FormField>

            <FormField name="domain" label="Domínio" v-slot="{ field, errors }">
              <input v-bind="field" class="kt-input w-full" placeholder="subdominio" :disabled="isEdit" />
              <FormError :errors="errors" />
            </FormField>

            <FormField name="document" label="CNPJ / CPF" v-slot="{ field, errors }">
              <input v-bind="field" class="kt-input w-full" placeholder="CNPJ ou CPF" />
              <FormError :errors="errors" />
            </FormField>

            <template v-if="isEdit">
              <FormField name="status" label="Status" v-slot="{ field, errors }">
                <select v-bind="field" class="kt-select w-full">
                  <option value="active">Ativo</option>
                  <option value="inactive">Inativo</option>
                  <option value="trial">Trial</option>
                </select>
                <FormError :errors="errors" />
              </FormField>

              <FormField v-if="values.status === 'trial'" name="trial_until" label="Trial até" v-slot="{ field, errors }">
                <input v-bind="field" class="kt-input w-full" type="date" />
                <FormError :errors="errors" />
              </FormField>

              <FormField name="client_id" label="Cliente" v-slot="{ field, errors }">
                <select v-bind="field" class="kt-select w-full">
                  <option value="">Nenhum</option>
                  <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
                <FormError :errors="errors" />
              </FormField>
            </template>

            <div class="flex justify-end gap-2 mt-4">
              <button type="button" class="kt-btn kt-btn-ghost" @click="$emit('close')">
                Cancelar
              </button>
              <button type="submit" class="kt-btn kt-btn-primary">
                Salvar
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>
  </teleport>
</template>

<script setup>
import { watch } from 'vue';
import { useForm } from 'vee-validate';
import * as yup from 'yup';
import FormField from './FormField.vue';
import FormError from './FormError.vue';

const props = defineProps({
  open: Boolean,
  isEdit: Boolean,
  tenant: Object,
  clients: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(['close', 'submit']);

const schema = yup.object({
  name: yup.string().required('Nome é obrigatório'),
  email: yup.string().email('Email inválido').required('Email é obrigatório'),
  domain: yup.string().required('Domínio é obrigatório'),
  document: yup.string().required('Documento é obrigatório'),
  status: yup.string().oneOf(['active', 'inactive', 'trial']).nullable(),
  trial_until: yup.string().nullable(),
  client_id: yup.string().nullable(),
});

const { handleSubmit, setValues, resetForm, values } = useForm({
  validationSchema: schema,
  initialValues: {
    name: '',
    email: '',
    domain: '',
    document: '',
    status: 'active',
    trial_until: '',
    client_id: '',
  },
});

watch(
  () => props.tenant,
  (val) => {
    if (val) {
      setValues({
        name: val.name || '',
        email: val.email || '',
        domain: val.domain || '',
        document: val.document || '',
        status: val.status || 'active',
        trial_until: val.trial_until ? val.trial_until.substring(0, 10) : '',
        client_id: val.client_id || '',
      });
    } else {
      resetForm();
    }
  }
);

const submitHandler = handleSubmit((values) => {
  emit('submit', values);
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
