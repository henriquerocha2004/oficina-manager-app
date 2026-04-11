/**
 * Status das Ordens de Serviço
 * @readonly
 * @enum {string}
 */
export const ServiceOrderStatus = {
    DRAFT: 'draft',
    WAITING_APPROVAL: 'waiting_approval',
    APPROVED: 'approved',
    IN_PROGRESS: 'in_progress',
    WAITING_PAYMENT: 'waiting_payment',
    COMPLETED: 'completed',
    CANCELLED: 'cancelled',
};

/**
 * Labels para exibição dos status
 */
export const ServiceOrderStatusLabels = {
    [ServiceOrderStatus.DRAFT]: 'Rascunho',
    [ServiceOrderStatus.WAITING_APPROVAL]: 'Aguardando Aprovação',
    [ServiceOrderStatus.APPROVED]: 'Aprovado',
    [ServiceOrderStatus.IN_PROGRESS]: 'Em Progresso',
    [ServiceOrderStatus.WAITING_PAYMENT]: 'Aguardando Pagamento',
    [ServiceOrderStatus.COMPLETED]: 'Concluído',
    [ServiceOrderStatus.CANCELLED]: 'Cancelado',
};

/**
 * Ícones para os status
 */
export const ServiceOrderStatusIcons = {
    [ServiceOrderStatus.DRAFT]:           'ki-filled ki-note',
    [ServiceOrderStatus.WAITING_APPROVAL]:'ki-filled ki-time',
    [ServiceOrderStatus.APPROVED]:        'ki-filled ki-check-circle',
    [ServiceOrderStatus.IN_PROGRESS]:     'ki-filled ki-setting-2',
    [ServiceOrderStatus.WAITING_PAYMENT]: 'ki-filled ki-wallet',
    [ServiceOrderStatus.COMPLETED]:       'ki-filled ki-double-check',
    [ServiceOrderStatus.CANCELLED]:       'ki-filled ki-cross-circle',
};

/**
 * Cores para os status (para badges)
 */
export const ServiceOrderStatusColors = {
    [ServiceOrderStatus.DRAFT]: 'bg-gray-100 text-gray-800',
    [ServiceOrderStatus.WAITING_APPROVAL]: 'bg-yellow-100 text-yellow-800',
    [ServiceOrderStatus.APPROVED]: 'bg-blue-100 text-blue-800',
    [ServiceOrderStatus.IN_PROGRESS]: 'bg-orange-100 text-orange-800',
    [ServiceOrderStatus.WAITING_PAYMENT]: 'bg-purple-100 text-purple-800',
    [ServiceOrderStatus.COMPLETED]: 'bg-green-100 text-green-800',
    [ServiceOrderStatus.CANCELLED]: 'bg-red-100 text-red-800',
};

/**
 * Status exibidos no Kanban
 */
export const KanbanStatuses = [
    ServiceOrderStatus.DRAFT,
    ServiceOrderStatus.WAITING_APPROVAL,
    ServiceOrderStatus.APPROVED,
    ServiceOrderStatus.IN_PROGRESS,
    ServiceOrderStatus.WAITING_PAYMENT,
];

/**
 * Labels das colunas do Kanban
 */
export const KanbanColumnLabels = {
    [ServiceOrderStatus.DRAFT]: 'Iniciado',
    [ServiceOrderStatus.WAITING_APPROVAL]: 'Aguardando Aprovação',
    [ServiceOrderStatus.APPROVED]: 'Aprovado',
    [ServiceOrderStatus.IN_PROGRESS]: 'Serviço em Andamento',
    [ServiceOrderStatus.WAITING_PAYMENT]: 'Aguardando Pagamento',
};
