/**
 * Configuração do Menu Admin
 * 
 * Estrutura de cada item:
 * - label: Texto exibido no menu
 * - route: Nome da rota Laravel (usado com route() helper)
 * - icon: Classes Keenicons do Metronic (ex: 'ki-outline ki-element-11')
 * - children: Array de subitens (opcional, cria accordion)
 * - badge: Objeto com { text, variant } para badges (opcional)
 */

export const adminMenu = [
    {
        label: 'Dashboard',
        route: 'admin.dashboard',
        icon: 'ki-outline ki-element-11',
    },
    {
        label: 'Clientes',
        route: 'admin.clients.index',
        icon: 'ki-outline ki-profile-circle',
    },
];
