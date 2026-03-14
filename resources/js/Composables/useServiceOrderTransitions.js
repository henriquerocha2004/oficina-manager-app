import { ServiceOrderStatus } from '@/Data/serviceOrderStatuses.js';

/**
 * Regras de transição de status das Ordens de Serviço
 */
export const transitions = {
    [ServiceOrderStatus.DRAFT]: {
        canGoTo: [ServiceOrderStatus.WAITING_APPROVAL],
        requiresData: {
            [ServiceOrderStatus.WAITING_APPROVAL]: ['diagnosis', 'items']
        }
    },
    [ServiceOrderStatus.WAITING_APPROVAL]: {
        canGoTo: [ServiceOrderStatus.DRAFT, ServiceOrderStatus.APPROVED],
        requiresData: {}
    },
    [ServiceOrderStatus.APPROVED]: {
        canGoTo: [ServiceOrderStatus.WAITING_APPROVAL, ServiceOrderStatus.IN_PROGRESS],
        requiresData: {}
    },
    [ServiceOrderStatus.IN_PROGRESS]: {
        canGoTo: [ServiceOrderStatus.WAITING_APPROVAL, ServiceOrderStatus.WAITING_PAYMENT],
        requiresData: {
            [ServiceOrderStatus.WAITING_APPROVAL]: ['diagnosis', 'items']
        }
    },
    [ServiceOrderStatus.WAITING_PAYMENT]: {
        canGoTo: [ServiceOrderStatus.IN_PROGRESS],
        requiresData: {}
    },
    [ServiceOrderStatus.COMPLETED]: {
        canGoTo: [],
        requiresData: {}
    },
    [ServiceOrderStatus.CANCELLED]: {
        canGoTo: [],
        requiresData: {}
    }
};

/**
 * Labels para os tipos de transição
 */
export const transitionLabels = {
    [ServiceOrderStatus.DRAFT]: {
        [ServiceOrderStatus.WAITING_APPROVAL]: 'Enviar para Aprovação'
    },
    [ServiceOrderStatus.WAITING_APPROVAL]: {
        [ServiceOrderStatus.DRAFT]: 'Voltar para Rascunho',
        [ServiceOrderStatus.APPROVED]: 'Aprovar'
    },
    [ServiceOrderStatus.APPROVED]: {
        [ServiceOrderStatus.WAITING_APPROVAL]: 'Solicitar Nova Aprovação',
        [ServiceOrderStatus.IN_PROGRESS]: 'Iniciar Trabalho'
    },
    [ServiceOrderStatus.IN_PROGRESS]: {
        [ServiceOrderStatus.WAITING_APPROVAL]: 'Solicitar Nova Aprovação',
        [ServiceOrderStatus.WAITING_PAYMENT]: 'Finalizar Trabalho'
    },
    [ServiceOrderStatus.WAITING_PAYMENT]: {
        [ServiceOrderStatus.IN_PROGRESS]: 'Retomar Trabalho'
    }
};

/**
 * Verifica se uma transição é permitida
 * @param {string} fromStatus - Status atual
 * @param {string} toStatus - Status desejado
 * @returns {boolean}
 */
export function canTransition(fromStatus, toStatus) {
    const allowedTransitions = transitions[fromStatus];
    if (!allowedTransitions) {
        return false;
    }
    return allowedTransitions.canGoTo.includes(toStatus);
}

/**
 * Verifica se uma transição requer dados adicionais
 * @param {string} fromStatus - Status atual
 * @param {string} toStatus - Status desejado
 * @returns {Array} Array com os campos necessários
 */
export function requiresData(fromStatus, toStatus) {
    const allowedTransitions = transitions[fromStatus];
    if (!allowedTransitions || !allowedTransitions.requiresData) {
        return [];
    }
    return allowedTransitions.requiresData[toStatus] || [];
}

/**
 * Verifica se uma transição requer diagnóstico
 * @param {string} fromStatus - Status atual
 * @param {string} toStatus - Status desejado
 * @returns {boolean}
 */
export function requiresDiagnosis(fromStatus, toStatus) {
    return requiresData(fromStatus, toStatus).includes('diagnosis');
}

/**
 * Verifica se uma transição requer itens
 * @param {string} fromStatus - Status atual
 * @param {string} toStatus - Status desejado
 * @returns {boolean}
 */
export function requiresItems(fromStatus, toStatus) {
    return requiresData(fromStatus, toStatus).includes('items');
}

/**
 * Retorna o label de uma transição
 * @param {string} fromStatus - Status atual
 * @param {string} toStatus - Status desejado
 * @returns {string}
 */
export function getTransitionLabel(fromStatus, toStatus) {
    return transitionLabels[fromStatus]?.[toStatus] || `Mover para ${toStatus}`;
}

/**
 * Obtém os status disponíveis para transição a partir de um status
 * @param {string} fromStatus - Status atual
 * @returns {Array} Array de status disponíveis
 */
export function getAvailableTransitions(fromStatus) {
    const allowedTransitions = transitions[fromStatus];
    return allowedTransitions ? allowedTransitions.canGoTo : [];
}
