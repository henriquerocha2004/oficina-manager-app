<template>
  <div :class="cardClasses">
    <slot />
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'accent'].includes(value),
  },
});

const cardClasses = computed(() => {
  const classes = ['kt-card'];
  
  if (props.variant === 'accent') {
    classes.push('kt-card-accent');
  }
  
  return classes.join(' ');
});
</script>
