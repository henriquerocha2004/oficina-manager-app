<template>
  <div 
    class="relative flex min-h-full flex-col overflow-hidden lg:rounded-xl lg:border lg:border-border"
    :class="containerClasses"
    :style="backgroundStyle"
  >
    <!-- Overlay (opcional) -->
    <div 
      v-if="overlay"
      class="absolute inset-0 z-0"
      :style="overlayStyle"
    ></div>

    <!-- Conteúdo customizado via slot -->
    <div class="relative z-10 flex flex-col p-8 lg:p-16">
      <slot />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  // URL da imagem de background
  backgroundImage: {
    type: String,
    default: '',
  },
  
  // Posição do background
  backgroundPosition: {
    type: String,
    default: 'center',
  },
  
  // Tamanho do background
  backgroundSize: {
    type: String,
    default: 'cover',
  },
  
  // Ativar overlay sobre a imagem
  overlay: {
    type: Boolean,
    default: false,
  },
  
  // Cor do overlay (hex ou rgba)
  overlayColor: {
    type: String,
    default: 'rgba(0, 0, 0, 0.6)',
  },
  
  // Opacidade do overlay (0-100)
  overlayOpacity: {
    type: Number,
    default: 60,
    validator: (value) => value >= 0 && value <= 100,
  },
  
  // Classes CSS adicionais para o container
  customClass: {
    type: String,
    default: '',
  },
});

const containerClasses = computed(() => {
  return props.customClass || 'bg-background';
});

const backgroundStyle = computed(() => {
  if (!props.backgroundImage) return {};
  
  return {
    backgroundImage: `url(${props.backgroundImage})`,
    backgroundPosition: props.backgroundPosition,
    backgroundSize: props.backgroundSize,
    backgroundRepeat: 'no-repeat',
  };
});

const overlayStyle = computed(() => {
  // Se overlayColor já é rgba, usar diretamente
  if (props.overlayColor.startsWith('rgba')) {
    return {
      backgroundColor: props.overlayColor,
    };
  }
  
  // Converter hex para rgba com opacidade
  const opacity = props.overlayOpacity / 100;
  return {
    backgroundColor: props.overlayColor,
    opacity: opacity,
  };
});
</script>
