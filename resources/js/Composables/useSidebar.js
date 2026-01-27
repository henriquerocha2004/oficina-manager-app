import { ref, watch, onMounted } from 'vue';

/**
 * Composable para gerenciar o estado da Sidebar
 * 
 * Funcionalidades:
 * - Toggle da sidebar (abrir/fechar)
 * - Gerenciar accordions (menus expandidos)
 * - Persistir estado no localStorage
 * - Responsividade mobile
 */

const isSidebarOpen = ref(true);
const openAccordions = ref(new Set());
const currentContext = ref('admin'); // 'admin' ou 'tenant'

// Keys do localStorage
const SIDEBAR_COLLAPSED_KEY = 'sidebar_collapsed';
const getAccordionsKey = (context) => `sidebar_accordions_${context}`;

export function useSidebar(context = 'admin') {
  currentContext.value = context;

  // Inicializar estado do localStorage
  onMounted(() => {
    initializeSidebarState();
  });

  /**
   * Inicializa o estado da sidebar do localStorage
   */
  function initializeSidebarState() {
    // Recuperar estado collapsed
    const savedCollapsed = localStorage.getItem(SIDEBAR_COLLAPSED_KEY);
    if (savedCollapsed !== null) {
      isSidebarOpen.value = savedCollapsed === 'false';
    }

    // Recuperar accordions abertos
    const savedAccordions = localStorage.getItem(getAccordionsKey(context));
    if (savedAccordions) {
      try {
        const accordions = JSON.parse(savedAccordions);
        openAccordions.value = new Set(accordions);
      } catch (e) {
        console.error('Erro ao carregar accordions do localStorage:', e);
      }
    }

    // Auto-fechar sidebar em mobile
    handleMobileResponsiveness();
  }

  /**
   * Toggle da sidebar (abrir/fechar)
   */
  function toggleSidebar() {
    isSidebarOpen.value = !isSidebarOpen.value;
    saveSidebarState();
  }

  /**
   * Fecha a sidebar
   */
  function closeSidebar() {
    isSidebarOpen.value = false;
    saveSidebarState();
  }

  /**
   * Abre a sidebar
   */
  function openSidebar() {
    isSidebarOpen.value = true;
    saveSidebarState();
  }

  /**
   * Toggle de accordion (menu expandido)
   * @param {string} menuId - ID único do menu
   */
  function toggleAccordion(menuId) {
    if (openAccordions.value.has(menuId)) {
      openAccordions.value.delete(menuId);
    } else {
      openAccordions.value.add(menuId);
    }
    saveAccordionsState();
  }

  /**
   * Verifica se um accordion está aberto
   * @param {string} menuId - ID único do menu
   * @returns {boolean}
   */
  function isAccordionOpen(menuId) {
    return openAccordions.value.has(menuId);
  }

  /**
   * Fecha todos os accordions
   */
  function closeAllAccordions() {
    openAccordions.value.clear();
    saveAccordionsState();
  }

  /**
   * Fecha a sidebar em mobile automaticamente
   */
  function closeSidebarOnMobile() {
    if (window.innerWidth < 1024) { // lg breakpoint do Tailwind
      closeSidebar();
    }
  }

  /**
   * Gerencia responsividade mobile
   */
  function handleMobileResponsiveness() {
    if (window.innerWidth < 1024) {
      isSidebarOpen.value = false; // Sempre fecha ao entrar em mobile
    }

    // Listener para resize
    window.addEventListener('resize', () => {
      if (window.innerWidth < 1024) {
        isSidebarOpen.value = false; // Sempre fecha ao entrar em mobile
      } else if (window.innerWidth >= 1024) {
        isSidebarOpen.value = true;
      }
    });
  }

  /**
   * Salva estado collapsed no localStorage
   */
  function saveSidebarState() {
    localStorage.setItem(SIDEBAR_COLLAPSED_KEY, String(!isSidebarOpen.value));
  }

  /**
   * Salva estado dos accordions no localStorage
   */
  function saveAccordionsState() {
    const accordionsArray = Array.from(openAccordions.value);
    localStorage.setItem(
      getAccordionsKey(currentContext.value),
      JSON.stringify(accordionsArray)
    );
  }

  // Watchers para auto-salvar no localStorage
  watch(isSidebarOpen, () => {
    saveSidebarState();
  });

  watch(openAccordions, () => {
    saveAccordionsState();
  }, { deep: true });

  return {
    // Estado
    isSidebarOpen,
    openAccordions,

    // Métodos
    toggleSidebar,
    closeSidebar,
    openSidebar,
    toggleAccordion,
    isAccordionOpen,
    closeAllAccordions,
    closeSidebarOnMobile,
    initializeSidebarState,
  };
}
