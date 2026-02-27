import axios from 'axios';
import { ServiceOrderStatus } from '@/Data/serviceOrderStatuses.js';

const mockServiceOrders = [
    {
        id: '1',
        code: 'OS-001',
        status: ServiceOrderStatus.DRAFT,
        client: { name: 'João Silva', email: 'joao@email.com', phone: '(11) 99999-9999' },
        vehicle: { brand: 'Volkswagen', model: 'Gol', plate: 'ABC-1234', year: 2020 },
        entry_date: '2026-02-20',
        expected_delivery: '2026-02-25',
        diagnosis: 'Troca de óleo e filtros',
        items: [
            { id: 1, description: 'Troca de óleo', quantity: 1, unit_price: 150.00, total: 150.00, type: 'labor' },
            { id: 2, description: 'Filtro de óleo', quantity: 1, unit_price: 45.00, total: 45.00, type: 'product' },
        ],
        subtotal: 195.00,
        discount: 0.00,
        total: 195.00,
    },
    {
        id: '2',
        code: 'OS-002',
        status: ServiceOrderStatus.DRAFT,
        client: { name: 'Maria Santos', email: 'maria@email.com', phone: '(11) 88888-8888' },
        vehicle: { brand: 'Ford', model: 'Fiesta', plate: 'XYZ-5678', year: 2019 },
        entry_date: '2026-02-21',
        expected_delivery: '2026-02-26',
        diagnosis: 'Revisão completa',
        items: [
            { id: 1, description: 'Revisão', quantity: 1, unit_price: 300.00, total: 300.00, type: 'labor' },
        ],
        subtotal: 300.00,
        discount: 20.00,
        total: 280.00,
    },
    {
        id: '3',
        code: 'OS-003',
        status: ServiceOrderStatus.WAITING_APPROVAL,
        client: { name: 'Pedro Costa', email: 'pedro@email.com', phone: '(11) 77777-7777' },
        vehicle: { brand: 'Chevrolet', model: 'Onix', plate: 'DEF-9012', year: 2021 },
        entry_date: '2026-02-19',
        expected_delivery: '2026-02-24',
        diagnosis: 'Troca de pastilhas de freio',
        items: [
            { id: 1, description: 'Pastilhas dianteiras', quantity: 2, unit_price: 120.00, total: 240.00, type: 'product' },
            { id: 2, description: 'Mão de obra freio', quantity: 1, unit_price: 100.00, total: 100.00, type: 'labor' },
        ],
        subtotal: 340.00,
        discount: 0.00,
        total: 340.00,
    },
    {
        id: '4',
        code: 'OS-004',
        status: ServiceOrderStatus.WAITING_APPROVAL,
        client: { name: 'Ana Oliveira', email: 'ana@email.com', phone: '(11) 66666-6666' },
        vehicle: { brand: 'Toyota', model: 'Corolla', plate: 'GHI-3456', year: 2022 },
        entry_date: '2026-02-18',
        expected_delivery: '2026-02-23',
        diagnosis: 'Alinhamento e balanceamento',
        items: [
            { id: 1, description: 'Alinhamento', quantity: 1, unit_price: 80.00, total: 80.00, type: 'labor' },
            { id: 2, description: 'Balanceamento', quantity: 4, unit_price: 25.00, total: 100.00, type: 'labor' },
        ],
        subtotal: 180.00,
        discount: 0.00,
        total: 180.00,
    },
    {
        id: '5',
        code: 'OS-005',
        status: ServiceOrderStatus.APPROVED,
        client: { name: 'Carlos Souza', email: 'carlos@email.com', phone: '(11) 55555-5555' },
        vehicle: { brand: 'Honda', model: 'Civic', plate: 'JKL-7890', year: 2020 },
        entry_date: '2026-02-15',
        expected_delivery: '2026-02-22',
        diagnosis: 'Troca de correia dentada',
        items: [
            { id: 1, description: 'Correia dentada', quantity: 1, unit_price: 250.00, total: 250.00, type: 'product' },
            { id: 2, description: 'Mão de obra', quantity: 1, unit_price: 200.00, total: 200.00, type: 'labor' },
        ],
        subtotal: 450.00,
        discount: 50.00,
        total: 400.00,
    },
    {
        id: '6',
        code: 'OS-006',
        status: ServiceOrderStatus.IN_PROGRESS,
        client: { name: 'Fernanda Lima', email: 'fernanda@email.com', phone: '(11) 44444-4444' },
        vehicle: { brand: 'Renault', model: 'Kwid', plate: 'MNO-1234', year: 2023 },
        entry_date: '2026-02-10',
        expected_delivery: '2026-02-20',
        diagnosis: 'Reparo no motor',
        items: [
            { id: 1, description: 'Diagnóstico', quantity: 1, unit_price: 150.00, total: 150.00, type: 'labor' },
            { id: 2, description: 'Junta do motor', quantity: 1, unit_price: 180.00, total: 180.00, type: 'product' },
        ],
        subtotal: 330.00,
        discount: 0.00,
        total: 330.00,
    },
    {
        id: '7',
        code: 'OS-007',
        status: ServiceOrderStatus.IN_PROGRESS,
        client: { name: 'Roberto Alves', email: 'roberto@email.com', phone: '(11) 33333-3333' },
        vehicle: { brand: 'Hyundai', model: 'HB20', plate: 'PQR-5678', year: 2021 },
        entry_date: '2026-02-12',
        expected_delivery: '2026-02-19',
        diagnosis: 'Troca de bateria',
        items: [
            { id: 1, description: 'Bateria 60Ah', quantity: 1, unit_price: 350.00, total: 350.00, type: 'product' },
        ],
        subtotal: 350.00,
        discount: 0.00,
        total: 350.00,
    },
    {
        id: '8',
        code: 'OS-008',
        status: ServiceOrderStatus.COMPLETED,
        client: { name: 'Juliana Martins', email: 'juliana@email.com', phone: '(11) 22222-2222' },
        vehicle: { brand: 'Nissan', model: 'Versa', plate: 'STU-9012', year: 2022 },
        entry_date: '2026-02-01',
        expected_delivery: '2026-02-10',
        completion_date: '2026-02-09',
        diagnosis: 'Pintura e funilaria',
        items: [
            { id: 1, description: 'Funilaria', quantity: 1, unit_price: 800.00, total: 800.00, type: 'labor' },
            { id: 2, description: 'Tinta', quantity: 2, unit_price: 150.00, total: 300.00, type: 'product' },
        ],
        subtotal: 1100.00,
        discount: 100.00,
        total: 1000.00,
    },
    {
        id: '9',
        code: 'OS-009',
        status: ServiceOrderStatus.COMPLETED,
        client: { name: 'Marcos Pereira', email: 'marcos@email.com', phone: '(11) 11111-1111' },
        vehicle: { brand: 'Volkswagen', model: 'Polo', plate: 'VWX-3456', year: 2019 },
        entry_date: '2026-01-28',
        expected_delivery: '2026-02-05',
        completion_date: '2026-02-04',
        diagnosis: 'Troca de amortecedores',
        items: [
            { id: 1, description: 'Amortecedores', quantity: 2, unit_price: 250.00, total: 500.00, type: 'product' },
            { id: 2, description: 'Mão de obra', quantity: 1, unit_price: 200.00, total: 200.00, type: 'labor' },
        ],
        subtotal: 700.00,
        discount: 0.00,
        total: 700.00,
    },
    {
        id: '10',
        code: 'OS-010',
        status: ServiceOrderStatus.COMPLETED,
        client: { name: 'Luciana Rodrigues', email: 'luciana@email.com', phone: '(11) 00000-0000' },
        vehicle: { brand: 'Fiat', model: 'Cronos', plate: 'YZA-7890', year: 2023 },
        entry_date: '2026-01-25',
        expected_delivery: '2026-02-01',
        completion_date: '2026-01-31',
        diagnosis: 'Manutenção preventiva',
        items: [
            { id: 1, description: 'Troca de óleo', quantity: 1, unit_price: 150.00, total: 150.00, type: 'labor' },
            { id: 2, description: 'Filtros', quantity: 3, unit_price: 35.00, total: 105.00, type: 'product' },
        ],
        subtotal: 255.00,
        discount: 0.00,
        total: 255.00,
    },
];

const mockHistory = [
    {
        id: 1,
        action: 'OS Criada',
        description: 'Ordem de serviço criada em modo rascunho',
        user: 'Sistema',
        created_at: '2026-02-20 09:00:00',
    },
    {
        id: 2,
        action: 'Enviada para Aprovação',
        description: 'OS enviada para aprovação do cliente',
        user: 'João Mecânico',
        created_at: '2026-02-20 14:30:00',
    },
    {
        id: 3,
        action: 'Aprovada',
        description: 'Aprovada pelo cliente',
        user: 'João Silva',
        created_at: '2026-02-21 10:15:00',
    },
    {
        id: 4,
        action: 'Trabalho Iniciado',
        description: 'Técnico iniciou os serviços',
        user: 'Carlos Técnico',
        created_at: '2026-02-21 11:00:00',
    },
];

/**
 * Busca Ordens de Serviço para o Kanban
 * @param {number} days - Número de dias para buscar (30, 60, 90)
 * @returns {Promise<{success: boolean, data?: Array, error?: any}>}
 */
export async function fetchServiceOrdersKanban(days = 30) {
    try {
        const { data } = await axios.get(`/service-orders?days=${days}`);
        return { success: true, data: data.data.serviceOrders };
    } catch (error) {
        const startDate = new Date();
        startDate.setDate(startDate.getDate() - days);
        
        const filtered = mockServiceOrders.filter(so => {
            const entryDate = new Date(so.entry_date);
            return entryDate >= startDate;
        });
        
        return { success: true, data: filtered };
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
export async function fetchServiceOrders({ page = 1, perPage = 10, search = '', sortKey = '', sortDir = 'asc', filters = {} } = {}) {
    try {
        const params = { page, per_page: perPage, search };
        if (sortKey) {
            params.sort_field = sortKey;
            params.sort_direction = sortDir;
        }
        if (filters.dateFrom) params.date_from = filters.dateFrom;
        if (filters.dateTo) params.date_to = filters.dateTo;
        if (filters.client) params.client = filters.client;
        if (filters.plate) params.plate = filters.plate;
        if (filters.status) params.status = filters.status;

        const { data } = await axios.get('/service-orders', { params });
        const serviceOrders = data.data.serviceOrders;
        return {
            items: serviceOrders.data,
            total: serviceOrders.total,
            page: serviceOrders.current_page,
            perPage: serviceOrders.per_page,
        };
    } catch (error) {
        let filtered = [...mockServiceOrders];

        if (filters.status) {
            filtered = filtered.filter(so => so.status === filters.status);
        }
        if (filters.client) {
            filtered = filtered.filter(so => 
                so.client.name.toLowerCase().includes(filters.client.toLowerCase())
            );
        }
        if (filters.plate) {
            filtered = filtered.filter(so => 
                so.vehicle.plate.toLowerCase().includes(filters.plate.toLowerCase())
            );
        }
        if (filters.dateFrom) {
            filtered = filtered.filter(so => so.entry_date >= filters.dateFrom);
        }
        if (filters.dateTo) {
            filtered = filtered.filter(so => so.entry_date <= filters.dateTo);
        }
        if (search) {
            const s = search.toLowerCase();
            filtered = filtered.filter(so => 
                so.code.toLowerCase().includes(s) ||
                so.client.name.toLowerCase().includes(s) ||
                so.vehicle.plate.toLowerCase().includes(s)
            );
        }

        if (sortKey) {
            filtered.sort((a, b) => {
                let aVal = a[sortKey];
                let bVal = b[sortKey];
                if (sortKey === 'client') {
                    aVal = a.client.name;
                    bVal = b.client.name;
                }
                if (sortKey === 'vehicle') {
                    aVal = a.vehicle.plate;
                    bVal = b.vehicle.plate;
                }
                if (sortKey === 'total') {
                    aVal = a.total;
                    bVal = b.total;
                }
                if (sortDir === 'asc') {
                    return aVal > bVal ? 1 : -1;
                }
                return aVal < bVal ? 1 : -1;
            });
        }

        const start = (page - 1) * perPage;
        const paged = filtered.slice(start, start + perPage);

        return {
            items: paged,
            total: filtered.length,
            page,
            perPage,
        };
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
        return { success: true, data: data.data.serviceOrder };
    } catch (error) {
        const so = mockServiceOrders.find(s => s.id === id);
        if (so) {
            return { success: true, data: { ...so, history: mockHistory } };
        }
        return { success: false, error: new Error('Ordem de Serviço não encontrada') };
    }
}

/**
 * Atualiza o status de uma OS
 * @param {string} id
 * @param {string} newStatus
 * @returns {Promise<{success: boolean, data?: Object, error?: any}>}
 */
export async function updateServiceOrderStatus(id, newStatus) {
    // Por enquanto, apenas mock
    // O backend tem endpoints específicos para cada ação:
    // - /{id}/send-for-approval
    // - /{id}/approve
    // - /{id}/start-work
    // - /{id}/finish-work
    // - /{id}/cancel
    const stringId = String(id);
    const so = mockServiceOrders.find(s => String(s.id) === stringId);
    if (so) {
        so.status = newStatus;
        return { success: true, data: so };
    }
    return { success: false, error: new Error('OS não encontrada') };
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
        const stats = {
            total: mockServiceOrders.length,
            draft: mockServiceOrders.filter(s => s.status === ServiceOrderStatus.DRAFT).length,
            waiting_approval: mockServiceOrders.filter(s => s.status === ServiceOrderStatus.WAITING_APPROVAL).length,
            approved: mockServiceOrders.filter(s => s.status === ServiceOrderStatus.APPROVED).length,
            in_progress: mockServiceOrders.filter(s => s.status === ServiceOrderStatus.IN_PROGRESS).length,
            completed: mockServiceOrders.filter(s => s.status === ServiceOrderStatus.COMPLETED).length,
        };
        return { success: true, data: stats };
    }
}
