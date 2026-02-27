import { ref, computed } from 'vue';
import { fetchServiceOrdersKanban, updateServiceOrderStatus } from '@/services/serviceOrderService.js';
import { KanbanStatuses, ServiceOrderStatus } from '@/Data/serviceOrderStatuses.js';

const STORAGE_KEY = 'service-order-kanban-days';

export function useServiceOrderKanban() {
    const serviceOrders = ref([]);
    const loading = ref(false);
    const error = ref(null);
    const days = ref(loadDaysFromStorage());

    // Arrays reativos para cada coluna (usados pelo vuedraggable)
    const columnsData = ref({});
    KanbanStatuses.forEach(status => {
        columnsData.value[status] = ref([]);
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

    const columns = computed(() => {
        const cols = {};
        KanbanStatuses.forEach(status => {
            cols[status] = columnsData.value[status].value;
        });
        return cols;
    });

    const columnCounts = computed(() => {
        const counts = {};
        KanbanStatuses.forEach(status => {
            counts[status] = columnsData.value[status].value?.length || 0;
        });
        return counts;
    });

    function updateColumns() {
        KanbanStatuses.forEach(status => {
            columnsData.value[status].value = serviceOrders.value.filter(so => so.status === status);
        });
    }

    async function load() {
        loading.value = true;
        error.value = null;
        
        try {
            const result = await fetchServiceOrdersKanban(days.value);
            if (result.success) {
                serviceOrders.value = result.data;
                updateColumns();
            } else {
                error.value = result.error;
            }
        } catch (e) {
            error.value = e;
        } finally {
            loading.value = false;
        }
    }

    function setDays(newDays) {
        days.value = newDays;
        saveDaysToStorage();
        load();
    }

    function updateServiceOrderColumn(serviceOrderId, newStatus) {
        // Remove o item da coluna atual
        let foundItem = null;
        KanbanStatuses.forEach(status => {
            const idx = columnsData.value[status].value.findIndex(so => String(so.id) === String(serviceOrderId));
            if (idx !== -1) {
                foundItem = columnsData.value[status].value.splice(idx, 1)[0];
            }
        });

        if (foundItem) {
            // Adiciona na nova coluna
            foundItem.status = newStatus;
            columnsData.value[newStatus].value.push(foundItem);
            
            // Também atualiza no array principal
            const idx = serviceOrders.value.findIndex(so => String(so.id) === String(serviceOrderId));
            if (idx !== -1) {
                serviceOrders.value[idx].status = newStatus;
            }
        }
    }

    async function changeStatus(serviceOrderId, newStatus) {
        const oldStatus = serviceOrders.value.find(so => String(so.id) === String(serviceOrderId))?.status;
        
        // Atualiza visualmente primeiro (otimista)
        updateServiceOrderColumn(serviceOrderId, newStatus);

        const result = await updateServiceOrderStatus(serviceOrderId, newStatus);
        
        if (!result.success) {
            // Se falhar, reverte
            updateServiceOrderColumn(serviceOrderId, oldStatus);
            throw result.error;
        }
    }

    return {
        serviceOrders,
        columns,
        columnCounts,
        loading,
        error,
        days,
        setDays,
        load,
        changeStatus,
    };
}
