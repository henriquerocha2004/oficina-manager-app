/**
 * Composable para gerar URLs de rotas de forma segura
 * 
 * Diferente do Ziggy, este helper NÃO expõe todas as rotas da aplicação.
 * Apenas as rotas explicitamente mapeadas aqui estão disponíveis no front-end.
 */

import { usePage } from '@inertiajs/vue3';

// Mapa de rotas Admin
const adminRoutes = {
  'admin.dashboard': '/admin/dashboard',
  'admin.clients.index': '/admin/clients',
  'admin.tenants.create': '/admin/tenants/create',
  'admin.tenants.show': (id) => `/admin/tenants/${id}`,
  'admin.tenants.edit': (id) => `/admin/tenants/${id}/edit`,
  'admin.subscriptions.index': '/admin/subscriptions',
  'admin.subscriptions.plans': '/admin/subscriptions/plans',
  'admin.users.index': '/admin/users',
  'admin.settings.index': '/admin/settings',
  'admin.settings.general': '/admin/settings/general',
  'admin.settings.system': '/admin/settings/system',
  'admin.settings.profile': '/admin/settings/profile',
  'admin.reports.index': '/admin/reports',
  'admin.logout': '/admin/logout',
};

// Mapa de rotas Tenant
const tenantRoutes = {
  'tenant.dashboard': '/dashboard',
  'tenant.clients.index': '/clients',
  'tenant.clients.create': '/clients/create',
  'tenant.clients.show': (id) => `/clients/${id}`,
  'tenant.clients.edit': (id) => `/clients/${id}/edit`,
  'tenant.service-orders.index': '/service-orders',
  'tenant.service-orders.create': '/service-orders/create',
  'tenant.service-orders.show': (id) => `/service-orders/${id}`,
  'tenant.service-orders.edit': (id) => `/service-orders/${id}/edit`,
  'tenant.service-orders.in-progress': '/service-orders/in-progress',
  'tenant.service-orders.completed': '/service-orders/completed',
  'tenant.inventory.index': '/inventory',
  'tenant.inventory.products': '/inventory/products',
  'tenant.inventory.categories': '/inventory/categories',
  'tenant.inventory.low-stock': '/inventory/low-stock',
  'tenant.appointments.index': '/appointments',
  'tenant.appointments.calendar': '/appointments/calendar',
  'tenant.appointments.create': '/appointments/create',
  'tenant.invoicing.index': '/invoicing',
  'tenant.invoicing.invoices': '/invoicing/invoices',
  'tenant.invoicing.payments': '/invoicing/payments',
  'tenant.invoicing.reports': '/invoicing/reports',
  'tenant.settings.index': '/settings',
  'tenant.settings.profile': '/settings/profile',
  'tenant.settings.users': '/settings/users',
  'tenant.settings.account': '/settings/account',
  'tenant.logout': '/logout',
};

// Combina todos os mapas de rotas
const allRoutes = {
  ...adminRoutes,
  ...tenantRoutes,
};

/**
 * Gera a URL para uma rota nomeada
 * 
 * @param {string} name - Nome da rota
 * @param {object|string|number} params - Parâmetros da rota
 * @returns {string} URL gerada
 */
export function route(name, params = {}) {
  const routeDefinition = allRoutes[name];

  if (!routeDefinition) {
    console.error(`Rota "${name}" não encontrada no mapeamento de rotas.`);
    return '/';
  }

  // Se a rota é uma função, chama com os parâmetros
  if (typeof routeDefinition === 'function') {
    return routeDefinition(params);
  }

  // Se a rota é uma string simples, retorna diretamente
  // Para rotas do Laravel com domínios, retorna a URL completa
  return routeDefinition;
}

/**
 * Verifica se a rota atual corresponde ao nome fornecido
 * 
 * @param {string} name - Nome da rota para verificar
 * @returns {boolean}
 */
export function routeCurrent(name) {
  const page = usePage();

  // Usa o component do Inertia para verificar a rota atual
  // O Inertia passa o nome da rota no component path
  const currentComponent = page.component;

  // Verifica se é a mesma rota baseado no component
  if (name === 'admin.dashboard' && currentComponent === 'Admin/Dashboard') return true;
  if (name === 'tenant.dashboard' && currentComponent === 'Tenant/Dashboard') return true;
  if (name === 'admin.clients.index' && currentComponent === 'Admin/Clients/Index') return true;
  if (name === 'tenant.clients.index' && currentComponent === 'Tenant/Clients/Index') return true;

  // Fallback: Verifica pela URL
  const currentUrl = page.url;
  const routeUrl = route(name);
  return currentUrl === routeUrl || currentUrl.startsWith(routeUrl);
}

/**
 * Composable principal
 */
export function useRoute() {
  return {
    route,
    current: routeCurrent,
  };
}
