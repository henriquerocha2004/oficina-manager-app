<template>
  <div
    id="sidebar_footer"
    class="sidebar-footer px-4 py-3 border-t border-gray-800 min-h-[56px] flex items-center bg-transparent"
  >
    <div class="flex items-center w-full justify-between">
      <!-- Avatar à esquerda -->
      <div class="relative">
        <button @click="toggleDropdown" class="inline-flex items-center justify-center overflow-hidden w-10 h-10 rounded-full bg-gray-700 focus:outline-none">
          <img
            :src="user?.avatar || blankAvatar"
            :alt="user?.name"
            class="rounded-full w-10 h-10 object-cover"
          />
        </button>
        <!-- Dropdown -->
        <Transition name="dropdown-fade-slide">
          <div v-if="isDropdownOpen" ref="dropdownRef" class="absolute left-0 bottom-14 z-50 w-80" style="background:#080808;border-radius:0.75rem;box-shadow:0 8px 32px 0 rgba(31, 38, 135, 0.37);border:1px solid #232323;overflow:hidden;">
          <!-- Header -->
          <div class="flex items-center gap-3 px-5 pt-4 pb-3 border-b border-gray-800" style="background:#080808;">
            <img :src="user?.avatar || blankAvatar" :alt="user?.name" class="w-12 h-12 rounded-full object-cover" />
            <div class="flex-1 min-w-0">
              <div class="font-semibold text-base text-gray-100 truncate">{{ user?.name }}</div>
              <div class="text-xs text-gray-400 truncate">{{ user?.email }}</div>
            </div>
          </div>
          <!-- Menu -->
          <div class="py-1">
            <div class="flex flex-col gap-0.5 px-2 py-1">
              <Link :href="route(profileRoute)" class="flex items-center gap-3 px-3 py-1.5 rounded-lg text-gray-200 hover:bg-gray-800 transition text-sm">
                <i class="ki-outline ki-user text-lg"></i>
                <span>Meu Perfil</span>
              </Link>
              <Link :href="route(settingsRoute)" class="flex items-center gap-3 px-3 py-1.5 rounded-lg text-gray-200 hover:bg-gray-800 transition text-sm">
                <i class="ki-outline ki-setting-2 text-lg"></i>
                <span>Minha Conta</span>
                <i class="ki-outline ki-chevron-right ml-auto text-base opacity-60"></i>
              </Link>
            </div>
            <div class="flex items-center justify-between px-3 py-2 mt-1">
              <div class="flex items-center gap-2 text-gray-300">
                <i class="ki-outline ki-moon text-lg"></i>
                <span>Dark Mode</span>
              </div>
              <button @click="toggleTheme" class="relative inline-flex items-center h-5 rounded-full w-9 transition focus:outline-none" :class="isDarkMode ? 'bg-blue-600' : 'bg-gray-700'">
                <span class="sr-only">Toggle dark mode</span>
                <span :class="isDarkMode ? 'translate-x-4' : 'translate-x-1'" class="inline-block w-3.5 h-3.5 transform bg-white rounded-full transition-transform"></span>
              </button>
            </div>
          </div>
          <!-- Footer -->
          <div class="px-4 py-2">
            <Link :href="route(logoutRoute)" method="post" as="button" class="block w-full text-center px-4 py-2 text-base font-semibold rounded-lg border border-orange-500 text-white bg-orange-500 hover:bg-orange-600 transition">
              Sair
            </Link>
          </div>
          </div>
        </Transition>
      </div>
      <!-- Ícones à direita -->
      <div class="flex items-center gap-3">
        <button
          class="inline-flex items-center justify-center text-gray-400 hover:text-primary transition-colors relative"
          @click="$emit('open-notifications')"
          title="Notificações"
        >
          <i class="ki-outline ki-notification-on text-xl"></i>
          <span
            v-if="notificationsCount > 0"
            class="absolute -top-1 -right-1 inline-flex items-center justify-center rounded-full w-2 h-2 bg-red-500"
          ></span>
        </button>
        <Link
          :href="route(logoutRoute)"
          method="post"
          as="button"
          class="inline-flex items-center justify-center text-gray-400 hover:text-primary transition-colors"
          title="Sair"
        >
          <i class="ki-outline ki-exit-left text-xl"></i>
        </Link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useRoute } from '@/Composables/useRoute';
import { useTheme } from '@/Composables/useTheme';
import blankAvatar from '@assets/media/avatars/blank.png';

const props = defineProps({
  collapsed: {
    type: Boolean,
    default: false,
  },
  profileRoute: {
    type: String,
    required: true,
  },
  settingsRoute: {
    type: String,
    required: true,
  },
  logoutRoute: {
    type: String,
    required: true,
  },
  notificationsCount: {
    type: Number,
    default: 0,
  },
});

defineEmits(['open-notifications']);

const page = usePage();
const user = page.props.auth?.user;
const { route } = useRoute();
const { toggleTheme, getCurrentTheme, THEME_MODES } = useTheme();


const isDropdownOpen = ref(false);
const dropdownRef = ref(null);

// Computed para verificar se está em dark mode
const isDarkMode = computed(() => {
  return getCurrentTheme() === THEME_MODES.DARK;
});

onMounted(() => {
  document.addEventListener('mousedown', handleClickOutside);
});


function toggleDropdown() {
  isDropdownOpen.value = !isDropdownOpen.value;
}

function handleClickOutside(event) {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    isDropdownOpen.value = false;
  }
}


onMounted(() => {
  document.addEventListener('mousedown', handleClickOutside);
});


onUnmounted(() => {
  document.removeEventListener('mousedown', handleClickOutside);
});
</script>

<style scoped>
/* Transição suave para o dropdown do avatar */
.dropdown-fade-slide-enter-active,
.dropdown-fade-slide-leave-active {
  transition: opacity 0.25s cubic-bezier(0.4,0,0.2,1), transform 0.25s cubic-bezier(0.4,0,0.2,1);
}
.dropdown-fade-slide-enter-from,
.dropdown-fade-slide-leave-to {
  opacity: 0;
  transform: translateY(16px);
}
.dropdown-fade-slide-enter-to,
.dropdown-fade-slide-leave-from {
  opacity: 1;
  transform: translateY(0);
}
</style>
