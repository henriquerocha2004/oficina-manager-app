<template>
  <div class="flex flex-col gap-1">
    <label v-if="label" class="text-sm font-medium mb-1 text-gray-900 dark:text-gray-100">
      {{ label }}
    </label>
    <slot :field="fieldProps" :errors="errors" />
  </div>
</template>

<script setup>
import { useField } from 'vee-validate';
import { computed } from 'vue';

const props = defineProps({
  name: { type: String, required: true },
  label: String,
});

// useField conecta ao useForm do pai através do 'name'
const { value, errorMessage: errors, handleBlur, handleChange } = useField(() => props.name);

// Criamos um objeto computado para o v-bind do input
const fieldProps = computed(() => ({
  name: props.name,
  value: value.value, // Reage ao setFieldValue do pai
  onBlur: handleBlur,
  onInput: handleChange,
  'onUpdate:modelValue': (val) => (value.value = val),
}));
</script>