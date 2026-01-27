<template>
  <div class="grid min-h-screen lg:grid-cols-2">
    <!-- Form Column - Left on desktop, Bottom on mobile -->
    <div class="order-2 flex items-center justify-center p-8 lg:order-1 lg:p-10">
      <div class="kt-card w-full max-w-[370px]">
        <div class="kt-card-content flex flex-col gap-5 p-10">
          <slot />
        </div>
      </div>
    </div>

    <!-- Branded Column - Right on desktop, Top on mobile -->
    <div class="order-1 lg:order-2 lg:m-5">
      <!-- Conteúdo customizado via slot nomeado -->
      <slot name="branded">
        <!-- Fallback: conteúdo padrão quando slot não for fornecido -->
        <div 
          class="flex min-h-full flex-col gap-4 rounded-xl border border-border bg-background bg-cover bg-top bg-no-repeat p-8 lg:p-16 xxl:bg-center" 
          :style="brandedBackgroundStyle"
        >
          <!-- Logo -->
          <div>
            <img
              :src="logoUrl"
              alt="Logo"
              class="h-7"
            />
          </div>

          <!-- Title -->
          <h3 :class="titleClasses">
            {{ title }}
          </h3>

          <!-- Description -->
          <div :class="descriptionClasses">
            {{ description }}
          </div>
        </div>
      </slot>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { useTheme } from '@/Composables/useTheme';

// Import background images
import bgLight from '@assets/media/images/2600x1600/1.png';
import bgDark from '@assets/media/images/2600x1600/1-dark.png';
import logo from '@assets/media/app/mini-logo.svg';

const props = defineProps({
  title: {
    type: String,
    default: 'Secure Access Portal',
  },
  description: {
    type: String,
    default: 'Sign in to access your dashboard and manage your account securely.',
  },
});

const { initTheme, getCurrentTheme, theme } = useTheme();

// Initialize theme on mount
onMounted(() => {
  initTheme();
});

// Compute background image based on theme (only on desktop lg+)
const brandedBackgroundStyle = computed(() => {
  const isDark = getCurrentTheme() === 'dark';
  // Only apply background image on large screens
  if (window.innerWidth >= 1024) {
    return {
      backgroundImage: `url(${isDark ? bgDark : bgLight})`,
    };
  }
  return {};
});

// Compute title classes based on theme
const titleClasses = computed(() => {
  const isDark = getCurrentTheme() === 'dark';
  return [
    'text-2xl',
    'font-semibold',
    isDark ? 'text-white' : 'text-gray-900',
  ];
});

// Compute description classes based on theme
const descriptionClasses = computed(() => {
  const isDark = getCurrentTheme() === 'dark';
  return [
    'text-base',
    'font-medium',
    isDark ? 'text-gray-200' : 'text-gray-700',
  ];
});

const logoUrl = logo;
</script>
