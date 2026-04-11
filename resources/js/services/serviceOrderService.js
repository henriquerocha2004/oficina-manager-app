import axios from 'axios';
import { ServiceOrderStatus } from '@/Data/serviceOrderStatuses.js';

/**
 * Mapeia campos do backend para formato usado pelo frontend
 * @param {Object} os - Dados da OS vindos do backend
 * @returns {Object}
 */
function mapServiceOrder(os) {
    return {
        id: os.id,
        code: os.order_number,
        status: os.status,
        diagnosis: os.diagnosis,
        observations: os.observations,
        entry_date: os.created_at,
        completed_at: os.completed_at,
        total: parseFloat(os.total) || 0,
        total_parts: parseFloat(os.total_parts) || 0,
        total_services: parseFloat(os.total_services) || 0,
        discount: parseFloat(os.discount) || 0,
        paid_amount: parseFloat(os.paid_amount) || 0,
        outstanding_balance: parseFloat(os.outstanding_balance) || 0,
        client: os.client ? {
            id: os.client.id,
            name: os.client.name,
            email: os.client.email,
            phone: os.client.phone,
        } : null,
        vehicle: os.vehicle ? {
            id: os.vehicle.id,
            brand: os.vehicle.brand,
            model: os.vehicle.model,
            plate: os.vehicle.license_plate,
            year: os.vehicle.year,
            color: os.vehicle.color,
        } : null,
        technician: os.technician ? {
            id: os.technician.id,
            name: os.technician.name,
        } : null,
        items: Array.isArray(os.items) ? os.items.map(item => ({
            id: item.id,
            type: item.type,
            service_id: item.service_id || null,
            product_id: item.product_id || null,
            description: item.description,
            quantity: item.quantity,
            unit_price: parseFloat(item.unit_price) || 0,
            subtotal: parseFloat(item.subtotal) || 0,
        })) : [],
        created_at: os.created_at,
        updated_at: os.updated_at,
    };
}

/**
 * Busca Ordens de Serviço para o Kanban
 * @param {number} days - Número de dias para buscar (30, 60, 90)
 * @returns {Promise<{success: boolean, data?: Array, error?: any}>}
 */
export async function fetchServiceOrdersKanban(days = 30) {
    try {
        const dateFrom = new Date();
        dateFrom.setDate(dateFrom.getDate() - days);

        const { data } = await axios.get('/service-orders/search', {
            params: {
                date_from: dateFrom.toISOString().split('T')[0],
                per_page: 100,
            }
        });

        const serviceOrders = data.data.service_orders.data.map(mapServiceOrder);

        return { success: true, data: serviceOrders };
    } catch (error) {
        console.error('Error fetching kanban service orders:', error);
        return { success: false, error: error.response?.data?.message || error.message };
    }
}

/**
 * Busca Ordens de Serviço com paginação e filtros
 * @param {Object} params
 * @param {number} params.page
 * @param {number} params.perPage
 * @param {string} params.search
 * @param {string} params.sortKey
 * @param {string} params.sortDir
 * @param {Object} params.filters - { dateFrom, dateTo, client, plate, status }
 * @returns {Promise<{items: Array, total: number, page: number, perPage: number}>}
 */
export async function fetchServiceOrders({ page = 1, perPage = 10, search = '', sortKey = '', sortDir = 'desc', filters = {} } = {}) {
    try {
        const params = {
            page,
            per_page: perPage,
            search,
            sort_by: sortKey || 'created_at',
            sort_direction: sortDir,
        };

        if (filters.dateFrom)     params.date_from    = filters.dateFrom;
        if (filters.dateTo)       params.date_to      = filters.dateTo;
        if (filters.status)       params.status       = filters.status;
        if (filters.client)       params.client_name  = filters.client;
        if (filters.plate)        params.plate        = filters.plate;
        if (filters.orderNumber)  params.order_number = filters.orderNumber;
        if (filters.clientId)     params.client_id    = filters.clientId;
        if (filters.vehicleId)    params.vehicle_id   = filters.vehicleId;
        if (filters.technicianId) params.technician_id = filters.technicianId;

        const { data } = await axios.get('/service-orders/search', { params });

        const serviceOrders = data.data.service_orders.data.map(mapServiceOrder);

        return {
            items: serviceOrders,
            total: data.data.service_orders.total,
            page: data.data.service_orders.current_page,
            perPage: data.data.service_orders.per_page,
        };
    } catch (error) {
        console.error('Error fetching service orders:', error);
        throw error;
    }
}

/**
 * Busca uma OS pelo ID
 * @param {string} id
 * @returns {Promise<{success: boolean, data?: Object, error?: any}>}
 */
export async function fetchServiceOrderById(id) {
    try {
        const { data } = await axios.get(`/service-orders/${id}`);
        return { success: true, data: mapServiceOrder(data.data.service_order) };
    } catch (error) {
        console.error('Error fetching service order:', error);
        return { success: false, error: error.response?.data?.message || error.message };
    }
}

/**
 * Busca os itens de uma Ordem de Serviço
 * @param {string} id - ID da OS
 * @returns {Promise<{success: boolean, data?: Array, error?: any}>}
 */
export async function fetchServiceOrderItems(id) {
    try {
        const { data } = await axios.get(`/service-orders/${id}/items`);
        return {
            success: true,
            data: (data.data.items || []).map(item => ({
                id: item.id,
                type: item.type,
                service_id: item.service_id || null,
                product_id: item.product_id || null,
                description: item.description,
                quantity: item.quantity,
                unit_price: parseFloat(item.unit_price) || 0,
                subtotal: parseFloat(item.subtotal) || 0,
            })),
        };
    } catch (error) {
        console.error('Error fetching service order items:', error);
        return { success: false, error: error.response?.data?.message || error.message };
    }
}

/**
 * Mapa de transições de status para endpoints
 */
const statusEndpoints = {
    [ServiceOrderStatus.WAITING_APPROVAL]: 'send-for-approval',
    [ServiceOrderStatus.APPROVED]: 'approve',
    [ServiceOrderStatus.IN_PROGRESS]: 'start-work',
    [ServiceOrderStatus.WAITING_PAYMENT]: 'finish-work',
    [ServiceOrderStatus.COMPLETED]: 'finish-work',
};

/**
 * Muda o status de uma OS
 * @param {string} id - ID da OS
 * @param {string} newStatus - Novo status
 * @param {string|null} fromStatus - Status atual (necessário para rotas contextuais)
 * @returns {Promise<{success: boolean, data?: Object, error?: any}>}
 */
export async function changeServiceOrderStatus(id, newStatus, fromStatus = null) {
    let endpoint = statusEndpoints[newStatus];

    if (fromStatus === ServiceOrderStatus.WAITING_PAYMENT && newStatus === ServiceOrderStatus.WAITING_APPROVAL) {
        endpoint = 'return-to-approval';
    }

    if (!endpoint) {
        return { success: false, error: new Error('Transição de status inválida') };
    }

    try {
        const { data } = await axios.post(`/service-orders/${id}/${endpoint}`);
        return { success: true, data: mapServiceOrder(data.data.service_order) };
    } catch (error) {
        console.error('Error changing service order status:', error);
        return { success: false, error: error.response?.data?.message || error.message };
    }
}

/**
 * Envia OS para aprovação com diagnóstico e itens
 * @param {string} id - ID da OS
 * @param {Object} params - Parâmetros
 * @param {string} params.diagnosis - Diagnóstico
 * @param {Array} params.items - Itens (serviços/produtos)
 * @returns {Promise<{success: boolean, data?: Object, error?: any}>}
 */
export async function sendForApprovalWithData(id, { diagnosis, items }) {
    try {
        const { data } = await axios.post(`/service-orders/${id}/send-for-approval`, {
            diagnosis,
            items
        });
        return { success: true, data: mapServiceOrder(data.data.service_order) };
    } catch (error) {
        console.error('Error sending for approval:', error);
        return { success: false, error: error.response?.data?.message || error.message };
    }
}

/**
 * Solicita nova aprovação (retrocesso de IN_PROGRESS para WAITING_APPROVAL)
 * @param {string} id - ID da OS
 * @param {Object} params - Parâmetros
 * @param {string} params.diagnosis - Diagnóstico atualizado
 * @param {Array} params.items - Novos itens
 * @returns {Promise<{success: boolean, data?: Object, error?: any}>}
 */
export async function requestNewApproval(id, { diagnosis, items }) {
    try {
        const { data } = await axios.post(`/service-orders/${id}/request-new-approval`, {
            diagnosis,
            items
        });
        return { success: true, data: mapServiceOrder(data.data.service_order) };
    } catch (error) {
        console.error('Error requesting new approval:', error);
        return { success: false, error: error.response?.data?.message || error.message };
    }
}

/**
 * Atualiza o diagnóstico de uma OS
 * @param {string} id
 * @param {string} diagnosis
 * @returns {Promise<{success: boolean, data?: Object, error?: any}>}
 */
export async function updateDiagnosis(id, diagnosis) {
    try {
        const { data } = await axios.put(`/service-orders/${id}/diagnosis`, { technical_diagnosis: diagnosis });
        return { success: true, data: mapServiceOrder(data.data.service_order) };
    } catch (error) {
        console.error('Error updating diagnosis:', error);
        return { success: false, error: error.response?.data?.message || error.message };
    }
}

/**
 * Atualiza o desconto de uma OS
 * @param {string} id
 * @param {number} discount
 * @returns {Promise<{success: boolean, data?: Object, error?: any}>}
 */
export async function updateDiscount(id, discount) {
    try {
        const { data } = await axios.put(`/service-orders/${id}/discount`, { discount });
        return { success: true, data: mapServiceOrder(data.data.service_order) };
    } catch (error) {
        console.error('Error updating discount:', error);
        return { success: false, error: error.response?.data?.message || error.message };
    }
}

/**
 * Cancela uma OS
 * @param {string} id
 * @param {string} reason
 * @returns {Promise<{success: boolean, data?: Object, error?: any}>}
 */
export async function cancelServiceOrder(id, reason) {
    try {
        const { data } = await axios.post(`/service-orders/${id}/cancel`, { reason });
        return { success: true, data: mapServiceOrder(data.data.service_order) };
    } catch (error) {
        console.error('Error cancelling service order:', error);
        return { success: false, error: error.response?.data?.message || error.message };
    }
}

/**
 * Busca estatísticas das OS
 * @returns {Promise<{success: boolean, data?: Object, error?: any}>}
 */
export async function fetchServiceOrderStats() {
    try {
        const { data } = await axios.get('/service-orders/stats');
        return { success: true, data: data.data.stats };
    } catch (error) {
        console.error('Error fetching service order stats:', error);
        return { success: false, error: error.response?.data?.message || error.message };
    }
}

/**
 * Cria uma nova OS
 * @param {Object} osData
 * @returns {Promise<{success: boolean, data?: Object, error?: any}>}
 */
export async function createServiceOrder(osData) {
    try {
        const { data } = await axios.post('/service-orders', osData);
        return { success: true, data: mapServiceOrder(data.data.service_order) };
    } catch (error) {
        console.error('Error creating service order:', error);
        return { success: false, error: error.response?.data?.message || error.message };
    }
}

/**
 * Adiciona um item a uma OS
 * @param {string} serviceOrderId
 * @param {Object} item - { type, description, quantity, unit_price, service_id?, product_id? }
 * @returns {Promise<{success: boolean, data?: Object, error?: any}>}
 */
export async function addServiceOrderItem(serviceOrderId, item) {
    try {
        const { data } = await axios.post(`/service-orders/${serviceOrderId}/items`, item);
        return { success: true, data: data.data.service_order };
    } catch (error) {
        return { success: false, error: error.response?.data?.message || error.message };
    }
}

/**
 * Remove um item de uma OS
 * @param {string} serviceOrderId
 * @param {string} itemId
 * @returns {Promise<{success: boolean, data?: Object, error?: any}>}
 */
export async function removeServiceOrderItem(serviceOrderId, itemId) {
    try {
        const { data } = await axios.delete(`/service-orders/${serviceOrderId}/items/${itemId}`);
        return { success: true, data: data.data.service_order };
    } catch (error) {
        return { success: false, error: error.response?.data?.message || error.message };
    }
}

/**
 * Remove uma OS
 * @param {string} id
 * @returns {Promise<{success: boolean, error?: any}>}
 */
export async function deleteServiceOrder(id) {
    try {
        await axios.delete(`/service-orders/${id}`);
        return { success: true };
    } catch (error) {
        console.error('Error deleting service order:', error);
        return { success: false, error: error.response?.data?.message || error.message };
    }
}

/**
 * Registra um pagamento em uma OS
 * @param {string} serviceOrderId
 * @param {{ payment_method: string, amount: number, installments?: number, notes?: string }} payload
 * @returns {Promise<{success: boolean, data?: Object, error?: any}>}
 */
export async function registerPayment(serviceOrderId, { payment_method, amount, installments, notes }) {
    try {
        const { data } = await axios.post(`/service-orders/${serviceOrderId}/payments`, {
            payment_method,
            amount,
            installments: installments || null,
            notes: notes || null,
        });
        return { success: true, data: data.data.payment };
    } catch (error) {
        return { success: false, error: error.response?.data?.message || error.message };
    }
}

/**
 * Registra um estorno em uma OS (valor livre, não vinculado a um pagamento específico)
 * @param {string} serviceOrderId
 * @param {{ amount: number, payment_method: string, notes?: string }} payload
 * @returns {Promise<{success: boolean, data?: Object, error?: any}>}
 */
export async function registerRefund(serviceOrderId, { amount, payment_method, notes }) {
    try {
        const { data } = await axios.post(`/service-orders/${serviceOrderId}/refund`, {
            amount,
            payment_method,
            notes: notes || null,
        });
        return { success: true, data: data.data.service_order };
    } catch (error) {
        return { success: false, error: error.response?.data?.message || error.message };
    }
}

/**
 * Upload a photo to a service order
 * @param {string} serviceOrderId
 * @param {File} photoFile
 * @returns {Promise<{success: boolean, data?: Object, error?: any}>}
 */
export async function uploadServiceOrderPhoto(serviceOrderId, photoFile) {
    try {
        const formData = new FormData();
        formData.append('photo', photoFile);

        const { data } = await axios.post(
            `/service-orders/${serviceOrderId}/photos`,
            formData,
            {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            }
        );

        return { success: true, data: data.data.photo };
    } catch (error) {
        console.error('Error uploading photo:', error);
        return {
            success: false,
            error: error.response?.data?.message || error.message
        };
    }
}

/**
 * Delete a photo from a service order
 * @param {string} serviceOrderId
 * @param {string} photoId
 * @returns {Promise<{success: boolean, error?: any}>}
 */
export async function deleteServiceOrderPhoto(serviceOrderId, photoId) {
    try {
        await axios.delete(`/service-orders/${serviceOrderId}/photos/${photoId}`);
        return { success: true };
    } catch (error) {
        console.error('Error deleting photo:', error);
        return {
            success: false,
            error: error.response?.data?.message || error.message
        };
    }
}
