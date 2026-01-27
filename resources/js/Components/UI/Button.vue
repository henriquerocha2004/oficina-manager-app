<template>
  <button
    :type="type"
    :disabled="disabled"
    :class="buttonClasses"
    @click="$emit('click', $event)"
  >
    <slot />
  </button>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary', 'outline', 'ghost', 'destructive', 'mono'].includes(value),
  },
  size: {
    type: String,
    default: 'default',
    validator: (value) => ['sm', 'default', 'lg', 'icon'].includes(value),
  },
  type: {
    type: String,
    default: 'button',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
});

defineEmits(['click']);

const buttonClasses = computed(() => {
  const classes = ['kt-btn'];
  
  // Add variant class
  if (props.variant !== 'primary') {
    classes.push(`kt-btn-${props.variant}`);
  }
  
  // Add size class
  if (props.size !== 'default') {
    classes.push(`kt-btn-${props.size}`);
  }
  
  return classes.join(' ');
});
</script>
