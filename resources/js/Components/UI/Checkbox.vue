<template>
  <div class="kt-label">
    <input
      :id="id"
      type="checkbox"
      :checked="modelValue"
      :disabled="disabled"
      :required="required"
      :aria-invalid="ariaInvalid"
      :class="checkboxClasses"
      @change="$emit('update:modelValue', $event.target.checked)"
    />
    <label
      v-if="$slots.default"
      :for="id"
      class="kt-checkbox-label text-sm font-normal leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
    >
      <slot />
    </label>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  id: {
    type: String,
    required: true,
  },
  modelValue: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  required: {
    type: Boolean,
    default: false,
  },
  ariaInvalid: {
    type: [Boolean, String],
    default: false,
  },
  size: {
    type: String,
    default: 'default',
    validator: (value) => ['sm', 'default', 'lg'].includes(value),
  },
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'mono'].includes(value),
  },
});

defineEmits(['update:modelValue']);

const checkboxClasses = computed(() => {
  const classes = ['kt-checkbox'];
  
  if (props.size !== 'default') {
    classes.push(`kt-checkbox-${props.size}`);
  }
  
  if (props.variant !== 'default') {
    classes.push(`kt-checkbox-${props.variant}`);
  }
  
  return classes.join(' ');
});
</script>
