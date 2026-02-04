import './bootstrap';
import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import FlashMessages from './Components/FlashMessages.vue'
import { useTheme } from './Composables/useTheme'

// Inicializar tema
const { initTheme } = useTheme();
initTheme();

createInertiaApp({
    resolve: name => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
        const page = pages[`./Pages/${name}.vue`]
        if (!page) {
            throw new Error(`Page not found: ./Pages/${name}.vue`)
        }
        return page.default
    },
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .component('FlashMessages', FlashMessages)

        // Registrar modal global - removido para manter isolado

        // Provide toast
        const toast = {
          show: (options) => {
            if (window.KTToast) {
              window.KTToast.show(options)
            } else {
              console.warn('KTToast not available, fallback:', options.message)
            }
          },
          success: (message) => toast.show({ message, icon: '<i class="ki-filled ki-check-circle text-green-500 text-xl"></i>' }),
          error: (message) => toast.show({ message, icon: '<i class="ki-filled ki-cross-circle text-red-500 text-xl"></i>' }),
          info: (message) => toast.show({ message, icon: '<i class="ki-filled ki-information text-blue-500 text-xl"></i>' }),
          warning: (message) => toast.show({ message, icon: '<i class="ki-filled ki-warning text-yellow-500 text-xl"></i>' })
        }
        app.provide('toast', toast)

        app.mount(el)
    },
})
