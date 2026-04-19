/**
 * Configuração do Menu Tenant
 *
 * Estrutura de cada item:
 * - label: Texto exibido no menu
 * - route: Nome da rota Laravel (usado com route() helper)
 * - icon: Classes Keenicons do Metronic (ex: 'ki-outline ki-element-11')
 * - children: Array de subitens (opcional, cria accordion)
 * - badge: Objeto com { text, variant } para badges (opcional)
 * - roles: Array de perfis que podem ver este item (omitir = visível para todos)
 */

export const tenantMenu = [
    {
        label: 'Clientes',
        route: 'clients.index',
        icon: 'ki-outline ki-profile-circle',
        roles: ['administrator', 'reception'],
    },
    {
        label: 'Veículos',
        route: 'vehicles.index',
        icon: 'ki-outline ki-car',
        roles: ['administrator', 'reception'],
    },
    {
        label: 'Ordens de Serviço',
        route: 'service-orders.index',
        icon: 'ki-outline ki-note-2',
        roles: ['administrator', 'reception', 'mechanic'],
    },
    {
        label: 'Usuários',
        route: 'users.index',
        icon: 'ki-outline ki-user',
        roles: ['administrator'],
    },
    {
        label: 'Configurações',
        route: 'settings.index',
        icon: 'ki-outline ki-setting-2',
        roles: ['administrator'],
    },
];
