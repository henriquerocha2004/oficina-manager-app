<template>
  <div class="flex flex-col gap-1">
    <label
      v-if="label"
      :for="id"
      class="kt-form-label font-normal text-mono"
    >
      {{ label }}
    </label>
    
    <div class="relative" data-kt-toggle-password="true">
      <input
        :id="id"
        :type="showPassword ? 'text' : 'password'"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :required="required"
        :aria-invalid="ariaInvalid"
        class="flex h-10 w-full rounded-lg border border-input bg-background px-3 py-2 pr-10 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
        @input="$emit('update:modelValue', $event.target.value)"
        @blur="$emit('blur', $event)"
        @focus="$emit('focus', $event)"
      />
      
      <button
        type="button"
        class="kt-btn kt-btn-sm kt-btn-ghost kt-btn-icon absolute right-1 top-1/2 -translate-y-1/2"
        data-kt-toggle-password-trigger="true"
        @click="toggleVisibility"
      >
        <i
          v-show="!showPassword"
          class="ki-filled ki-eye text-muted-foreground"
        ></i>
        <i
          v-show="showPassword"
          class="ki-filled ki-eye-slash text-muted-foreground"
        ></i>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

defineProps({
  id: {
    type: String,
    required: true,
  },
  label: {
    type: String,
    default: '',
  },
  modelValue: {
    type: String,
    default: '',
  },
  placeholder: {
    type: String,
    default: '',
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
});

defineEmits(['update:modelValue', 'blur', 'focus']);

const showPassword = ref(false);

const toggleVisibility = () => {
  showPassword.value = !showPassword.value;
};
</script>
