vi.mock('axios');
vi.mock('mixpanel-browser', () => ({
    default: {
        track: vi.fn(),
    },
}));

import { describe, it, expect, vi, beforeEach } from 'vitest';
import axios from 'axios';
import mixpanel from 'mixpanel-browser';
import {
    fetchServiceOrdersKanban,
    fetchServiceOrders,
    fetchServiceOrderById,
    fetchServiceOrderItems,
    changeServiceOrderStatus,
    sendForApprovalWithData,
    requestNewApproval,
    updateDiagnosis,
    updateDiscount,
    cancelServiceOrder,
    createServiceOrder,
    addServiceOrderItem,
    removeServiceOrderItem,
    deleteServiceOrder,
    registerPayment,
    registerRefund,
    uploadServiceOrderPhoto,
    deleteServiceOrderPhoto,
    fetchServiceOrderStats,
} from '@/services/serviceOrderService';

const mockedAxios = vi.mocked(axios);
const mockedMixpanel = vi.mocked(mixpanel);
const trackingContext = {
    serviceOrderNumber: '2026-0001',
    serviceOrderStatus: 'draft',
};

// ─── Helpers ──────────────────────────────────────────────────────────────

const makeBackendOS = (overrides = {}) => ({
    id: 'os-001',
    order_number: '2026-0001',
    status: 'draft',
    diagnosis: 'Motor com ruído',
    observations: null,
    created_at: '2026-01-01T10:00:00Z',
    completed_at: null,
    total: '300.00',
    total_parts: '100.00',
    total_services: '200.00',
    discount: '0.00',
    paid_amount: '0.00',
    outstanding_balance: '300.00',
    client: { id: 'c-1', name: 'João', email: 'joao@test.com', phone: '11999999999' },
    vehicle: { id: 'v-1', brand: 'Toyota', model: 'Corolla', license_plate: 'ABC1234', year: 2022, color: 'Preto' },
    technician: null,
    items: [],
    updated_at: '2026-01-01T10:00:00Z',
    ...overrides,
});

const makePaginatedResponse = (serviceOrders = []) => ({
    data: {
        data: {
            service_orders: {
                data: serviceOrders,
                total: serviceOrders.length,
                current_page: 1,
                per_page: 10,
            },
        },
    },
});

describe('serviceOrderService', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    // ─── fetchServiceOrdersKanban ──────────────────────────────────────────

    describe('fetchServiceOrdersKanban', () => {
        it('calls /service-orders/search with date_from and per_page 100', async () => {
            mockedAxios.get.mockResolvedValue(makePaginatedResponse([makeBackendOS()]));

            const result = await fetchServiceOrdersKanban(30);

            expect(mockedAxios.get).toHaveBeenCalledWith(
                '/service-orders/search',
                expect.objectContaining({ params: expect.objectContaining({ per_page: 100 }) })
            );
            expect(result.success).toBe(true);
            expect(result.data).toHaveLength(1);
        });

        it('maps backend fields to frontend structure', async () => {
            mockedAxios.get.mockResolvedValue(makePaginatedResponse([makeBackendOS()]));

            const { data } = await fetchServiceOrdersKanban();

            const os = data[0];
            expect(os.code).toBe('2026-0001');
            expect(os.total).toBe(300);
            expect(os.client.name).toBe('João');
            expect(os.vehicle.plate).toBe('ABC1234');
        });

        it('returns success:false on error', async () => {
            mockedAxios.get.mockRejectedValue({ response: { data: { message: 'Server error' } } });

            const result = await fetchServiceOrdersKanban();

            expect(result.success).toBe(false);
            expect(result.error).toBe('Server error');
        });
    });

    // ─── fetchServiceOrders ────────────────────────────────────────────────

    describe('fetchServiceOrders', () => {
        it('calls /service-orders/search with correct params', async () => {
            mockedAxios.get.mockResolvedValue(makePaginatedResponse([]));

            await fetchServiceOrders({ page: 2, perPage: 25, sortKey: 'status', sortDir: 'asc' });

            expect(mockedAxios.get).toHaveBeenCalledWith('/service-orders/search', {
                params: expect.objectContaining({
                    page: 2,
                    per_page: 25,
                    sort_by: 'status',
                    sort_direction: 'asc',
                }),
            });
        });

        it('includes optional filters when provided', async () => {
            mockedAxios.get.mockResolvedValue(makePaginatedResponse([]));

            await fetchServiceOrders({
                filters: { status: 'draft', clientId: 'c-1', dateFrom: '2026-01-01' },
            });

            expect(mockedAxios.get).toHaveBeenCalledWith('/service-orders/search', {
                params: expect.objectContaining({
                    status: 'draft',
                    client_id: 'c-1',
                    date_from: '2026-01-01',
                }),
            });
        });

        it('returns paginated structure', async () => {
            mockedAxios.get.mockResolvedValue(makePaginatedResponse([makeBackendOS()]));

            const result = await fetchServiceOrders();

            expect(result.items).toHaveLength(1);
            expect(result.total).toBe(1);
            expect(result.page).toBe(1);
            expect(result.perPage).toBe(10);
        });

        it('throws on error', async () => {
            mockedAxios.get.mockRejectedValue(new Error('Network error'));

            await expect(fetchServiceOrders()).rejects.toThrow('Network error');
        });
    });

    // ─── fetchServiceOrderById ─────────────────────────────────────────────

    describe('fetchServiceOrderById', () => {
        it('calls /service-orders/:id', async () => {
            mockedAxios.get.mockResolvedValue({
                data: { data: { service_order: makeBackendOS() } },
            });

            const result = await fetchServiceOrderById('os-001');

            expect(mockedAxios.get).toHaveBeenCalledWith('/service-orders/os-001');
            expect(result.success).toBe(true);
            expect(result.data.id).toBe('os-001');
        });

        it('maps items array correctly', async () => {
            const osWithItem = makeBackendOS({
                items: [{
                    id: 'item-1', type: 'service', service_id: 's-1', product_id: null,
                    description: 'Oil change', quantity: 1, unit_price: '100.00', subtotal: '100.00',
                }],
            });
            mockedAxios.get.mockResolvedValue({
                data: { data: { service_order: osWithItem } },
            });

            const { data } = await fetchServiceOrderById('os-001');

            expect(data.items).toHaveLength(1);
            expect(data.items[0].unit_price).toBe(100);
            expect(data.items[0].service_id).toBe('s-1');
        });

        it('returns success:false on error', async () => {
            mockedAxios.get.mockRejectedValue({ response: { data: { message: 'Not found' } } });

            const result = await fetchServiceOrderById('missing');

            expect(result.success).toBe(false);
            expect(result.error).toBe('Not found');
        });
    });

    // ─── fetchServiceOrderItems ────────────────────────────────────────────

    describe('fetchServiceOrderItems', () => {
        it('calls /service-orders/:id/items and maps items', async () => {
            mockedAxios.get.mockResolvedValue({
                data: {
                    data: {
                        items: [{
                            id: 'i-1', type: 'part', service_id: null, product_id: 'p-1',
                            description: 'Filtro', quantity: 2, unit_price: '25.00', subtotal: '50.00',
                        }],
                    },
                },
            });

            const result = await fetchServiceOrderItems('os-001');

            expect(mockedAxios.get).toHaveBeenCalledWith('/service-orders/os-001/items');
            expect(result.success).toBe(true);
            expect(result.data[0].subtotal).toBe(50);
            expect(result.data[0].product_id).toBe('p-1');
        });
    });

    // ─── changeServiceOrderStatus ──────────────────────────────────────────

    describe('changeServiceOrderStatus', () => {
        const mockOsResponse = () => ({
            data: { data: { service_order: makeBackendOS({ status: 'waiting_approval' }) } },
        });

        it('calls send-for-approval for waiting_approval target', async () => {
            mockedAxios.post.mockResolvedValue(mockOsResponse());

            await changeServiceOrderStatus('os-001', 'waiting_approval');

            expect(mockedAxios.post).toHaveBeenCalledWith('/service-orders/os-001/send-for-approval');
        });

        it('calls approve for approved target', async () => {
            mockedAxios.post.mockResolvedValue(mockOsResponse());

            await changeServiceOrderStatus('os-001', 'approved');

            expect(mockedAxios.post).toHaveBeenCalledWith('/service-orders/os-001/approve');
        });

        it('calls start-work for in_progress target', async () => {
            mockedAxios.post.mockResolvedValue(mockOsResponse());

            await changeServiceOrderStatus('os-001', 'in_progress');

            expect(mockedAxios.post).toHaveBeenCalledWith('/service-orders/os-001/start-work');
        });

        it('calls finish-work for waiting_payment target', async () => {
            mockedAxios.post.mockResolvedValue(mockOsResponse());

            await changeServiceOrderStatus('os-001', 'waiting_payment');

            expect(mockedAxios.post).toHaveBeenCalledWith('/service-orders/os-001/finish-work');
        });

        it('calls return-to-approval when coming from waiting_payment', async () => {
            mockedAxios.post.mockResolvedValue(mockOsResponse());

            await changeServiceOrderStatus('os-001', 'waiting_approval', 'waiting_payment');

            expect(mockedAxios.post).toHaveBeenCalledWith('/service-orders/os-001/return-to-approval');
        });

        it('tracks the status change with the OS number', async () => {
            mockedAxios.post.mockResolvedValue({
                data: { data: { service_order: makeBackendOS({ status: 'completed' }) } },
            });

            await changeServiceOrderStatus('os-001', 'waiting_payment', 'in_progress', {
                serviceOrderNumber: '2026-0001',
                serviceOrderStatus: 'in_progress',
            });

            expect(mockedMixpanel.track).toHaveBeenCalledWith(
                'service_order_completed',
                expect.objectContaining({
                    service_order_id: 'os-001',
                    service_order_number: '2026-0001',
                    previous_status: 'in_progress',
                    current_status: 'completed',
                })
            );
        });

        it('tracks waiting payment without marking the OS as completed', async () => {
            mockedAxios.post.mockResolvedValue({
                data: { data: { service_order: makeBackendOS({ status: 'waiting_payment' }) } },
            });

            await changeServiceOrderStatus('os-001', 'waiting_payment', 'in_progress', {
                serviceOrderNumber: '2026-0001',
                serviceOrderStatus: 'in_progress',
            });

            expect(mockedMixpanel.track).toHaveBeenCalledWith(
                'service_order_waiting_payment',
                expect.objectContaining({
                    service_order_id: 'os-001',
                    service_order_number: '2026-0001',
                    previous_status: 'in_progress',
                    current_status: 'waiting_payment',
                })
            );
        });

        it('returns success:false for invalid transition', async () => {
            const result = await changeServiceOrderStatus('os-001', 'cancelled');

            expect(result.success).toBe(false);
        });
    });

    // ─── sendForApprovalWithData ───────────────────────────────────────────

    describe('sendForApprovalWithData', () => {
        it('posts diagnosis and items to send-for-approval endpoint', async () => {
            mockedAxios.post.mockResolvedValue({
                data: { data: { service_order: makeBackendOS() } },
            });

            const result = await sendForApprovalWithData('os-001', {
                diagnosis: 'Diagnóstico completo',
                items: [{ type: 'service', description: 'Test', quantity: 1, unit_price: 100, subtotal: 100 }],
            }, trackingContext);

            expect(mockedAxios.post).toHaveBeenCalledWith(
                '/service-orders/os-001/send-for-approval',
                expect.objectContaining({ diagnosis: 'Diagnóstico completo' })
            );
            expect(result.success).toBe(true);
            expect(mockedMixpanel.track).toHaveBeenCalledWith(
                'service_order_sent_for_approval',
                expect.objectContaining({
                    service_order_number: '2026-0001',
                    items_count: 1,
                })
            );
        });
    });

    // ─── requestNewApproval ────────────────────────────────────────────────

    describe('requestNewApproval', () => {
        it('posts to request-new-approval endpoint', async () => {
            mockedAxios.post.mockResolvedValue({
                data: { data: { service_order: makeBackendOS() } },
            });

            await requestNewApproval('os-001', { diagnosis: 'Updated', items: [] }, {
                serviceOrderNumber: '2026-0001',
                serviceOrderStatus: 'in_progress',
            });

            expect(mockedAxios.post).toHaveBeenCalledWith(
                '/service-orders/os-001/request-new-approval',
                { diagnosis: 'Updated', items: [] }
            );
            expect(mockedMixpanel.track).toHaveBeenCalledWith(
                'service_order_new_approval_requested',
                expect.objectContaining({
                    service_order_number: '2026-0001',
                    previous_status: 'in_progress',
                })
            );
        });
    });

    // ─── updateDiagnosis ──────────────────────────────────────────────────

    describe('updateDiagnosis', () => {
        it('puts technical_diagnosis to diagnosis endpoint', async () => {
            mockedAxios.put.mockResolvedValue({
                data: { data: { service_order: makeBackendOS() } },
            });

            await updateDiagnosis('os-001', 'Nova diagnose', trackingContext);

            expect(mockedAxios.put).toHaveBeenCalledWith(
                '/service-orders/os-001/diagnosis',
                { technical_diagnosis: 'Nova diagnose' }
            );
            expect(mockedMixpanel.track).toHaveBeenCalledWith(
                'service_order_diagnosis_updated',
                expect.objectContaining({
                    service_order_number: '2026-0001',
                    diagnosis_length: 13,
                })
            );
        });
    });

    // ─── updateDiscount ───────────────────────────────────────────────────

    describe('updateDiscount', () => {
        it('puts discount to discount endpoint', async () => {
            mockedAxios.put.mockResolvedValue({
                data: { data: { service_order: makeBackendOS() } },
            });

            await updateDiscount('os-001', 50, trackingContext);

            expect(mockedAxios.put).toHaveBeenCalledWith(
                '/service-orders/os-001/discount',
                { discount: 50 }
            );
            expect(mockedMixpanel.track).toHaveBeenCalledWith(
                'service_order_discount_updated',
                expect.objectContaining({
                    service_order_number: '2026-0001',
                    discount_amount: 50,
                })
            );
        });
    });

    // ─── cancelServiceOrder ────────────────────────────────────────────────

    describe('cancelServiceOrder', () => {
        it('posts reason to cancel endpoint', async () => {
            mockedAxios.post.mockResolvedValue({
                data: { data: { service_order: makeBackendOS({ status: 'cancelled' }) } },
            });

            const result = await cancelServiceOrder('os-001', 'Cliente desistiu', trackingContext);

            expect(mockedAxios.post).toHaveBeenCalledWith(
                '/service-orders/os-001/cancel',
                { reason: 'Cliente desistiu' }
            );
            expect(result.success).toBe(true);
            expect(mockedMixpanel.track).toHaveBeenCalledWith(
                'service_order_cancelled',
                expect.objectContaining({
                    service_order_number: '2026-0001',
                    cancel_reason: 'Cliente desistiu',
                })
            );
        });
    });

    // ─── createServiceOrder ────────────────────────────────────────────────

    describe('createServiceOrder', () => {
        it('posts OS data and maps response', async () => {
            mockedAxios.post.mockResolvedValue({
                data: { data: { service_order: makeBackendOS() } },
            });

            const result = await createServiceOrder({ client_id: 'c-1', vehicle_id: 'v-1' });

            expect(mockedAxios.post).toHaveBeenCalledWith('/service-orders', expect.any(Object));
            expect(result.success).toBe(true);
            expect(result.data.id).toBe('os-001');
            expect(mockedMixpanel.track).toHaveBeenCalledWith(
                'service_order_created',
                expect.objectContaining({
                    service_order_number: '2026-0001',
                    current_status: 'draft',
                })
            );
        });

        it('returns success:false on error', async () => {
            mockedAxios.post.mockRejectedValue({ response: { data: { message: 'Validation failed' } } });

            const result = await createServiceOrder({});

            expect(result.success).toBe(false);
        });
    });

    // ─── addServiceOrderItem ───────────────────────────────────────────────

    describe('addServiceOrderItem', () => {
        it('posts item to items endpoint', async () => {
            mockedAxios.post.mockResolvedValue({ data: { data: { service_order: makeBackendOS() } } });

            const item = { type: 'service', description: 'Troca de óleo', quantity: 1, unit_price: 100, subtotal: 100 };
            await addServiceOrderItem('os-001', item, trackingContext);

            expect(mockedAxios.post).toHaveBeenCalledWith('/service-orders/os-001/items', item);
            expect(mockedMixpanel.track).toHaveBeenCalledWith(
                'service_order_item_added',
                expect.objectContaining({
                    service_order_number: '2026-0001',
                    item_description: 'Troca de óleo',
                })
            );
        });
    });

    // ─── removeServiceOrderItem ────────────────────────────────────────────

    describe('removeServiceOrderItem', () => {
        it('deletes item from items endpoint', async () => {
            mockedAxios.delete.mockResolvedValue({ data: { data: { service_order: makeBackendOS() } } });

            await removeServiceOrderItem('os-001', 'item-1', trackingContext);

            expect(mockedAxios.delete).toHaveBeenCalledWith('/service-orders/os-001/items/item-1');
            expect(mockedMixpanel.track).toHaveBeenCalledWith(
                'service_order_item_removed',
                expect.objectContaining({
                    service_order_number: '2026-0001',
                    item_id: 'item-1',
                })
            );
        });
    });

    // ─── deleteServiceOrder ────────────────────────────────────────────────

    describe('deleteServiceOrder', () => {
        it('calls delete endpoint and returns success', async () => {
            mockedAxios.delete.mockResolvedValue({});

            const result = await deleteServiceOrder('os-001');

            expect(mockedAxios.delete).toHaveBeenCalledWith('/service-orders/os-001');
            expect(result.success).toBe(true);
        });

        it('returns success:false on error', async () => {
            mockedAxios.delete.mockRejectedValue({ response: { data: { message: 'Not found' } } });

            const result = await deleteServiceOrder('missing');

            expect(result.success).toBe(false);
        });
    });

    // ─── registerPayment ──────────────────────────────────────────────────

    describe('registerPayment', () => {
        it('posts payment data to payments endpoint', async () => {
            mockedAxios.post.mockResolvedValue({ data: { data: { payment: { id: 'pay-1' } } } });

            const result = await registerPayment('os-001', {
                payment_method: 'cash',
                amount: 150,
                installments: null,
                notes: null,
            }, {
                serviceOrderNumber: '2026-0001',
                serviceOrderStatus: 'waiting_payment',
            });

            expect(mockedAxios.post).toHaveBeenCalledWith(
                '/service-orders/os-001/payments',
                expect.objectContaining({ payment_method: 'cash', amount: 150 })
            );
            expect(result.success).toBe(true);
            expect(result.data.id).toBe('pay-1');
            expect(mockedMixpanel.track).toHaveBeenCalledWith(
                'service_order_payment_registered',
                expect.objectContaining({
                    service_order_number: '2026-0001',
                    payment_amount: 150,
                })
            );
        });
    });

    // ─── registerRefund ────────────────────────────────────────────────────

    describe('registerRefund', () => {
        it('posts refund data to refund endpoint', async () => {
            mockedAxios.post.mockResolvedValue({ data: { data: { service_order: makeBackendOS() } } });

            await registerRefund('os-001', { amount: 50, payment_method: 'cash', notes: 'Cobrou a mais' }, trackingContext);

            expect(mockedAxios.post).toHaveBeenCalledWith(
                '/service-orders/os-001/refund',
                expect.objectContaining({ amount: 50, payment_method: 'cash' })
            );
            expect(mockedMixpanel.track).toHaveBeenCalledWith(
                'service_order_refund_registered',
                expect.objectContaining({
                    service_order_number: '2026-0001',
                    refund_amount: 50,
                })
            );
        });
    });

    // ─── uploadServiceOrderPhoto ────────────────────────────────────────────

    describe('uploadServiceOrderPhoto', () => {
        it('uploads the photo and tracks it with the OS number', async () => {
            const photoFile = new File(['photo'], 'before.jpg', { type: 'image/jpeg' });

            mockedAxios.post.mockResolvedValue({
                data: {
                    data: {
                        photo: { id: 'photo-1', original_filename: 'before.jpg', file_size: 12345 },
                    }
                }
            });

            const result = await uploadServiceOrderPhoto('os-001', photoFile, trackingContext);

            expect(mockedAxios.post).toHaveBeenCalledWith(
                '/service-orders/os-001/photos',
                expect.any(FormData),
                expect.objectContaining({
                    headers: { 'Content-Type': 'multipart/form-data' },
                })
            );
            expect(result.success).toBe(true);
            expect(mockedMixpanel.track).toHaveBeenCalledWith(
                'service_order_photo_uploaded',
                expect.objectContaining({
                    service_order_number: '2026-0001',
                    photo_id: 'photo-1',
                })
            );
        });
    });

    // ─── deleteServiceOrderPhoto ────────────────────────────────────────────

    describe('deleteServiceOrderPhoto', () => {
        it('deletes the photo and tracks it with the OS number', async () => {
            mockedAxios.delete.mockResolvedValue({});

            const result = await deleteServiceOrderPhoto('os-001', 'photo-1', trackingContext);

            expect(mockedAxios.delete).toHaveBeenCalledWith('/service-orders/os-001/photos/photo-1');
            expect(result.success).toBe(true);
            expect(mockedMixpanel.track).toHaveBeenCalledWith(
                'service_order_photo_deleted',
                expect.objectContaining({
                    service_order_number: '2026-0001',
                    photo_id: 'photo-1',
                })
            );
        });
    });

    // ─── fetchServiceOrderStats ────────────────────────────────────────────

    describe('fetchServiceOrderStats', () => {
        it('calls /service-orders/stats and returns data', async () => {
            const stats = { total: 10, completed: 7, pending: 3 };
            mockedAxios.get.mockResolvedValue({ data: { data: { stats } } });

            const result = await fetchServiceOrderStats();

            expect(mockedAxios.get).toHaveBeenCalledWith('/service-orders/stats');
            expect(result.success).toBe(true);
            expect(result.data).toEqual(stats);
        });
    });
});
