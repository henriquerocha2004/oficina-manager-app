import { ref } from 'vue';
import { fetchServiceOrdersKanban, changeServiceOrderStatus, sendForApprovalWithData, requestNewApproval } from '@/services/serviceOrderService.js';
import { KanbanStatuses } from '@/Data/serviceOrderStatuses.js';
import { canTransition, requiresDiagnosis, requiresItems, getTransitionLabel } from './useServiceOrderTransitions.js';

const STORAGE_KEY = 'service-order-kanban-days';

export function useServiceOrderKanban() {
    const loading = ref(false);
    const error = ref(null);
    const days = ref(loadDaysFromStorage());

    // Arrays simples para cada coluna
    const columns = {};
    KanbanStatuses.forEach(status => {
        columns[status] = ref([]);
    });

    function loadDaysFromStorage() {
        try {
            const saved = sessionStorage.getItem(STORAGE_KEY);
            return saved ? parseInt(saved, 10) : 30;
        } catch {
            return 30;
        }
    }

    function saveDaysToStorage() {
        try {
            sessionStorage.setItem(STORAGE_KEY, days.value.toString());
        } catch (e) {
            console.error('Error saving kanban days to storage:', e);
        }
    }

    function updateColumns(serviceOrders) {
        KanbanStatuses.forEach(status => {
            columns[status].value = serviceOrders.filter(so => so.status === status);
        });
    }

    async function load() {
        loading.value = true;
        error.value = null;
        
        const result = await fetchServiceOrdersKanban(days.value);
        if (result.success) {
            updateColumns(result.data);
        } else {
            error.value = result.error;
        }
        loading.value = false;
    }

    function setDays(newDays) {
        days.value = newDays;
        saveDaysToStorage();
        load();
    }

    /**
     * Verifica se a transição é permitida
     * @param {string} serviceOrderId - ID da OS
     * @param {string} newStatus - Novo status
     * @returns {Object} { allowed: boolean, needsData: boolean, error?: string }
     */
    function checkTransition(serviceOrderId, newStatus) {
        const currentStatus = findCurrentStatus(serviceOrderId);
        
        if (!currentStatus) {
            return { allowed: false, needsData: false, error: 'OS não encontrada' };
        }

        if (!canTransition(currentStatus, newStatus)) {
            const label = getTransitionLabel(currentStatus, newStatus);
            return { 
                allowed: false, 
                needsData: false, 
                error: `Não é possível mover para "${label}"` 
            };
        }

        const needsDiagnosisData = requiresDiagnosis(currentStatus, newStatus);
        const needsItemsData = requiresItems(currentStatus, newStatus);

        if (needsDiagnosisData || needsItemsData) {
            return { 
                allowed: true, 
                needsData: true,
                needsDiagnosis: needsDiagnosisData,
                needsItems: needsItemsData 
            };
        }

        return { allowed: true, needsData: false };
    }

    /**
     * Encontra o status atual de uma OS
     */
    function findCurrentStatus(serviceOrderId) {
        for (const status of KanbanStatuses) {
            const found = columns[status].value.find(so => String(so.id) === String(serviceOrderId));
            if (found) {
                return found.status;
            }
        }
        return null;
    }

    /**
     * Remove item da coluna atual
     */
    function removeFromCurrentColumn(serviceOrderId) {
        for (const status of KanbanStatuses) {
            const idx = columns[status].value.findIndex(so => String(so.id) === String(serviceOrderId));
            if (idx !== -1) {
                return columns[status].value.splice(idx, 1)[0];
            }
        }
        return null;
    }

    /**
     * Adiciona item na nova coluna
     */
    function addToColumn(serviceOrderId, newStatus) {
        const item = removeFromCurrentColumn(serviceOrderId);
        if (item) {
            item.status = newStatus;
            columns[newStatus].value.push(item);
        }
        return item;
    }

    /**
     * Move status diretamente (sem dados)
     */
    async function changeStatus(serviceOrderId, newStatus) {
        const currentStatus = findCurrentStatus(serviceOrderId);
        addToColumn(serviceOrderId, newStatus);

        const result = await changeServiceOrderStatus(serviceOrderId, newStatus, currentStatus);

        if (!result.success) {
            await load();
            throw result.error;
        }
    }

    /**
     * Move status com dados (diagnóstico e itens)
     */
    async function changeStatusWithData(serviceOrderId, currentStatus, newStatus, data) {
        let result;
        
        if (currentStatus === 'draft') {
            result = await sendForApprovalWithData(serviceOrderId, data);
        } else if (currentStatus === 'in_progress') {
            result = await requestNewApproval(serviceOrderId, data);
        } else {
            result = await changeServiceOrderStatus(serviceOrderId, newStatus);
        }

        if (!result.success) {
            throw result.error;
        }
        
        return result.data;
    }

    return {
        columns,
        loading,
        error,
        days,
        setDays,
        load,
        changeStatus,
        changeStatusWithData,
        checkTransition,
        findCurrentStatus,
        removeFromCurrentColumn,
        addToColumn,
    };
}
