<template>
  <form class="flex flex-col gap-4 p-5" @submit.prevent="handleSubmit">
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
      <select v-bind="field" class="kt-select w-full">
        <option value="">Selecione</option>
        <option v-for="cat in productCategories" :key="cat.value" :value="cat.value">
          {{ cat.label }}
        </option>
      </select>
      <FormError :errors="errors" />
    </FormField>

    <FormField name="unit" label="Unidade" v-slot="{ field, errors }">
      <select v-bind="field" class="kt-select w-full">
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
        @input="onPriceInput('unit_price', $event)"
      />
      <FormError :errors="errors" />
    </FormField>

    <FormField name="suggested_price" label="Preço Sugerido" v-slot="{ field, errors }">
      <input
        :value="field.value"
        class="kt-input w-full"
        placeholder="R$ 0,00 (opcional)"
        @input="onPriceInput('suggested_price', $event)"
      />
      <FormError :errors="errors" />
    </FormField>

    <FormField name="is_active" label="Status" v-slot="{ field, errors }">
      <label class="flex items-center gap-2 cursor-pointer">
        <input
          type="checkbox"
          :checked="field.value"
          @change="onActiveChange($event)"
          class="kt-checkbox"
        />
        <span class="text-sm text-gray-900 dark:text-gray-100">Produto ativo</span>
      </label>
      <FormError :errors="errors" />
    </FormField>

    <div class="flex justify-end gap-2 mt-4">
      <button type="button" class="kt-btn kt-btn-ghost" @click="$emit('cancel')">
        Cancelar
      </button>
      <button type="submit" class="kt-btn kt-btn-primary">
        Salvar Produto
      </button>
    </div>
  </form>
</template>

<script setup>
import FormField from '../FormField.vue';
import FormError from '../FormError.vue';
import { productCategories, productUnits } from '@/Data/productData';

defineProps({
  setFieldValue: {
    type: Function,
    required: true,
  },
});

const emit = defineEmits(['cancel', 'submit', 'price-input', 'active-change']);

const handleSubmit = () => {
  emit('submit');
};

const onPriceInput = (field, event) => {
  emit('price-input', field, event);
};

const onActiveChange = (event) => {
  emit('active-change', event);
};
</script>
