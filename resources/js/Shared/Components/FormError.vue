<template>
  <div v-if="errors && errors.length" class="text-xs text-red-500 mt-1">
    <span v-for="err in [].concat(errors)" :key="err">{{ err }}</span>
  </div>
</template>

<script setup>
const props = defineProps({
  errors: [String, Array]
});
</script>
