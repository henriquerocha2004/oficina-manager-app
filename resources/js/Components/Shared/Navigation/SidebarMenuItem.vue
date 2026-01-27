<template>
  <div class="menu-item">
    <!-- Item sem filhos (link direto) -->
    <Link
      v-if="!item.children || item.children.length === 0"
      :href="route(item.route)"
      class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-gray-300 hover:bg-primary hover:text-white transition-colors cursor-pointer no-underline"
      :class="{ 'bg-primary text-white': isActive }"
      @click="handleClick"
    >
      <span class="flex items-center justify-center w-6 h-6 text-lg">
        <i :class="item.icon"></i>
      </span>
      <span v-if="!collapsed" class="flex-1 text-sm font-medium">
        {{ item.label }}
      </span>
      <span v-if="!collapsed && item.badge" class="ml-auto">
        <span 
          class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-semibold rounded"
          :class="{
            'bg-primary/20 text-primary': item.badge.variant === 'primary' || !item.badge.variant,
            'bg-red-500/20 text-red-400': item.badge.variant === 'danger',
            'bg-green-500/20 text-green-400': item.badge.variant === 'success',
            'bg-yellow-500/20 text-yellow-400': item.badge.variant === 'warning',
          }"
        >
          {{ item.badge.text }}
        </span>
      </span>
    </Link>

    <!-- Item com filhos (accordion) -->
    <template v-else>
      <button
        type="button"
        class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-gray-300 hover:bg-primary hover:text-white transition-colors cursor-pointer w-full border-0 bg-transparent text-left"
        :class="{ 'bg-primary text-white': isActive }"
        @click="toggleAccordion"
      >
        <span class="flex items-center justify-center w-6 h-6 text-lg">
          <i :class="item.icon"></i>
        </span>
        <span v-if="!collapsed" class="flex-1 text-sm font-medium">
          {{ item.label }}
        </span>
        <span v-if="!collapsed && item.badge" class="ml-auto">
          <span 
            class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-semibold rounded"
            :class="{
              'bg-primary/20 text-primary': item.badge.variant === 'primary' || !item.badge.variant,
              'bg-red-500/20 text-red-400': item.badge.variant === 'danger',
              'bg-green-500/20 text-green-400': item.badge.variant === 'success',
              'bg-yellow-500/20 text-yellow-400': item.badge.variant === 'warning',
            }"
          >
            {{ item.badge.text }}
          </span>
        </span>
        <span v-if="!collapsed" class="flex items-center justify-center w-5 h-5 transition-transform" :class="{ 'rotate-180': isOpen }">
          <i class="ki-outline ki-down text-xs"></i>
        </span>
      </button>

      <!-- Submenu -->
      <Transition
        name="accordion"
        @enter="onEnter"
        @after-enter="onAfterEnter"
        @leave="onLeave"
        @after-leave="onAfterLeave"
      >
        <div
          v-if="!collapsed && isOpen"
          class="pl-4 mt-1 overflow-hidden"
        >
          <div class="flex flex-col list-none m-0 p-0 gap-1 w-full">
            <SidebarMenuItem
              v-for="(child, index) in item.children"
              :key="index"
              :item="child"
              :level="level + 1"
              :collapsed="collapsed"
              @toggle-accordion="$emit('toggle-accordion', $event)"
              @item-click="$emit('item-click', $event)"
            />
          </div>
        </div>
      </Transition>
    </template>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useRoute } from '@/Composables/useRoute';

const props = defineProps({
  item: {
    type: Object,
    required: true,
  },
  level: {
    type: Number,
    default: 0,
  },
  collapsed: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['toggle-accordion', 'item-click']);

// Injetar função de verificação de accordion aberto
const isAccordionOpen = inject('isAccordionOpen', () => false);

const page = usePage();
const { route, current: routeCurrent } = useRoute();

// Gerar ID único para o accordion
const accordionId = computed(() => {
  return `menu-${props.item.route?.replace(/\./g, '-') || Math.random()}`;
});

// Verificar se o menu está aberto (accordion)
const isOpen = computed(() => {
  return isAccordionOpen(accordionId.value);
});

// Verificar se a rota atual corresponde ao item
const isActive = computed(() => {
  if (!props.item.route) return false;
  
  // Verificar rota exata
  if (routeCurrent(props.item.route)) return true;
  
  // Verificar se algum filho está ativo
  if (props.item.children && props.item.children.length > 0) {
    return props.item.children.some(child => 
      child.route && routeCurrent(child.route)
    );
  }
  
  return false;
});

function toggleAccordion() {
  emit('toggle-accordion', accordionId.value);
}

function handleClick() {
  emit('item-click', props.item);
}

// Funções para animação suave do accordion
function onEnter(el) {
  el.style.height = '0';
  el.offsetHeight; // force reflow
  el.style.transition = 'height 0.3s ease-out';
  el.style.height = el.scrollHeight + 'px';
}

function onAfterEnter(el) {
  el.style.height = '';
}

function onLeave(el) {
  el.style.height = el.scrollHeight + 'px';
  el.offsetHeight; // force reflow
  el.style.transition = 'height 0.3s ease-in';
  el.style.height = '0';
}

function onAfterLeave(el) {
  el.style.height = '';
}
</script>
