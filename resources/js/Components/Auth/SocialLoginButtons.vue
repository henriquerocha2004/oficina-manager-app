<template>
  <div class="grid grid-cols-2 gap-2.5">
    <!-- Google Sign In Button -->
    <button
      type="button"
      class="kt-btn kt-btn-outline flex justify-center"
      :disabled="disabled"
      @click="$emit('google-login')"
    >
      <img
        :src="googleIcon"
        alt="Google"
        class="h-5 w-5"
      />
    </button>

    <!-- Apple Sign In Button -->
    <button
      type="button"
      class="kt-btn kt-btn-outline flex justify-center"
      :disabled="disabled"
      @click="$emit('apple-login')"
    >
      <img
        :src="appleIcon"
        alt="Apple"
        class="h-5 w-5"
      />
    </button>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useTheme } from '@/Composables/useTheme';

import googleIconSrc from '@assets/media/brand-logos/google.svg';
import appleBlackIcon from '@assets/media/brand-logos/apple-black.svg';
import appleWhiteIcon from '@assets/media/brand-logos/apple-white.svg';

defineProps({
  disabled: {
    type: Boolean,
    default: true, // Disabled by default since OAuth is not implemented
  },
});

defineEmits(['google-login', 'apple-login']);

const { getCurrentTheme } = useTheme();

const googleIcon = googleIconSrc;
const appleIcon = computed(() => {
  return getCurrentTheme() === 'dark' ? appleWhiteIcon : appleBlackIcon;
});
</script>
