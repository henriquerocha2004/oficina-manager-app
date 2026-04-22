import { describe, it, expect } from 'vitest';
import {
    canTransition,
    requiresData,
    requiresDiagnosis,
    requiresItems,
    getTransitionLabel,
    getAvailableTransitions,
} from '@/Composables/useServiceOrderTransitions.js';
import { ServiceOrderStatus } from '@/Data/serviceOrderStatuses.js';

describe('useServiceOrderTransitions', () => {

    describe('canTransition', () => {
        it('allows draft → waiting_approval', () => {
            expect(canTransition(ServiceOrderStatus.DRAFT, ServiceOrderStatus.WAITING_APPROVAL)).toBe(true);
        });

        it('allows waiting_approval → approved', () => {
            expect(canTransition(ServiceOrderStatus.WAITING_APPROVAL, ServiceOrderStatus.APPROVED)).toBe(true);
        });

        it('allows approved → in_progress', () => {
            expect(canTransition(ServiceOrderStatus.APPROVED, ServiceOrderStatus.IN_PROGRESS)).toBe(true);
        });

        it('allows in_progress → waiting_approval (new approval request)', () => {
            expect(canTransition(ServiceOrderStatus.IN_PROGRESS, ServiceOrderStatus.WAITING_APPROVAL)).toBe(true);
        });

        it('allows in_progress → waiting_payment', () => {
            expect(canTransition(ServiceOrderStatus.IN_PROGRESS, ServiceOrderStatus.WAITING_PAYMENT)).toBe(true);
        });

        it('allows waiting_payment → waiting_approval (return for approval)', () => {
            expect(canTransition(ServiceOrderStatus.WAITING_PAYMENT, ServiceOrderStatus.WAITING_APPROVAL)).toBe(true);
        });

        it('blocks completed → any status', () => {
            expect(canTransition(ServiceOrderStatus.COMPLETED, ServiceOrderStatus.DRAFT)).toBe(false);
            expect(canTransition(ServiceOrderStatus.COMPLETED, ServiceOrderStatus.WAITING_APPROVAL)).toBe(false);
            expect(canTransition(ServiceOrderStatus.COMPLETED, ServiceOrderStatus.CANCELLED)).toBe(false);
        });

        it('blocks cancelled → any status', () => {
            expect(canTransition(ServiceOrderStatus.CANCELLED, ServiceOrderStatus.DRAFT)).toBe(false);
            expect(canTransition(ServiceOrderStatus.CANCELLED, ServiceOrderStatus.WAITING_APPROVAL)).toBe(false);
        });

        it('blocks draft → in_progress (skipping steps)', () => {
            expect(canTransition(ServiceOrderStatus.DRAFT, ServiceOrderStatus.IN_PROGRESS)).toBe(false);
        });

        it('blocks approved → completed (skipping steps)', () => {
            expect(canTransition(ServiceOrderStatus.APPROVED, ServiceOrderStatus.COMPLETED)).toBe(false);
        });

        it('returns false for unknown status', () => {
            expect(canTransition('unknown_status', ServiceOrderStatus.DRAFT)).toBe(false);
        });
    });

    describe('requiresData', () => {
        it('returns ["diagnosis","items"] for draft → waiting_approval', () => {
            expect(requiresData(ServiceOrderStatus.DRAFT, ServiceOrderStatus.WAITING_APPROVAL))
                .toEqual(['diagnosis', 'items']);
        });

        it('returns ["diagnosis","items"] for in_progress → waiting_approval', () => {
            expect(requiresData(ServiceOrderStatus.IN_PROGRESS, ServiceOrderStatus.WAITING_APPROVAL))
                .toEqual(['diagnosis', 'items']);
        });

        it('returns empty array for waiting_approval → approved', () => {
            expect(requiresData(ServiceOrderStatus.WAITING_APPROVAL, ServiceOrderStatus.APPROVED)).toEqual([]);
        });

        it('returns empty array for in_progress → waiting_payment', () => {
            expect(requiresData(ServiceOrderStatus.IN_PROGRESS, ServiceOrderStatus.WAITING_PAYMENT)).toEqual([]);
        });

        it('returns empty array for unknown transition', () => {
            expect(requiresData('unknown', ServiceOrderStatus.DRAFT)).toEqual([]);
        });
    });

    describe('requiresDiagnosis', () => {
        it('returns true for draft → waiting_approval', () => {
            expect(requiresDiagnosis(ServiceOrderStatus.DRAFT, ServiceOrderStatus.WAITING_APPROVAL)).toBe(true);
        });

        it('returns true for in_progress → waiting_approval', () => {
            expect(requiresDiagnosis(ServiceOrderStatus.IN_PROGRESS, ServiceOrderStatus.WAITING_APPROVAL)).toBe(true);
        });

        it('returns false for approved → in_progress', () => {
            expect(requiresDiagnosis(ServiceOrderStatus.APPROVED, ServiceOrderStatus.IN_PROGRESS)).toBe(false);
        });
    });

    describe('requiresItems', () => {
        it('returns true for draft → waiting_approval', () => {
            expect(requiresItems(ServiceOrderStatus.DRAFT, ServiceOrderStatus.WAITING_APPROVAL)).toBe(true);
        });

        it('returns false for approved → in_progress', () => {
            expect(requiresItems(ServiceOrderStatus.APPROVED, ServiceOrderStatus.IN_PROGRESS)).toBe(false);
        });
    });

    describe('getTransitionLabel', () => {
        it('returns "Enviar para Aprovação" for draft → waiting_approval', () => {
            expect(getTransitionLabel(ServiceOrderStatus.DRAFT, ServiceOrderStatus.WAITING_APPROVAL))
                .toBe('Enviar para Aprovação');
        });

        it('returns "Aprovar" for waiting_approval → approved', () => {
            expect(getTransitionLabel(ServiceOrderStatus.WAITING_APPROVAL, ServiceOrderStatus.APPROVED))
                .toBe('Aprovar');
        });

        it('returns "Iniciar Trabalho" for approved → in_progress', () => {
            expect(getTransitionLabel(ServiceOrderStatus.APPROVED, ServiceOrderStatus.IN_PROGRESS))
                .toBe('Iniciar Trabalho');
        });

        it('returns "Solicitar Nova Aprovação" for in_progress → waiting_approval', () => {
            expect(getTransitionLabel(ServiceOrderStatus.IN_PROGRESS, ServiceOrderStatus.WAITING_APPROVAL))
                .toBe('Solicitar Nova Aprovação');
        });

        it('returns "Finalizar Trabalho" for in_progress → waiting_payment', () => {
            expect(getTransitionLabel(ServiceOrderStatus.IN_PROGRESS, ServiceOrderStatus.WAITING_PAYMENT))
                .toBe('Finalizar Trabalho');
        });

        it('returns "Retornar para Aprovação" for waiting_payment → waiting_approval', () => {
            expect(getTransitionLabel(ServiceOrderStatus.WAITING_PAYMENT, ServiceOrderStatus.WAITING_APPROVAL))
                .toBe('Retornar para Aprovação');
        });

        it('returns fallback label for unknown transition', () => {
            expect(getTransitionLabel(ServiceOrderStatus.COMPLETED, ServiceOrderStatus.DRAFT))
                .toBe(`Mover para ${ServiceOrderStatus.DRAFT}`);
        });
    });

    describe('getAvailableTransitions', () => {
        it('returns [waiting_approval] for draft', () => {
            expect(getAvailableTransitions(ServiceOrderStatus.DRAFT))
                .toEqual([ServiceOrderStatus.WAITING_APPROVAL]);
        });

        it('returns [waiting_approval, waiting_payment] for in_progress', () => {
            expect(getAvailableTransitions(ServiceOrderStatus.IN_PROGRESS))
                .toEqual([ServiceOrderStatus.WAITING_APPROVAL, ServiceOrderStatus.WAITING_PAYMENT]);
        });

        it('returns [] for completed', () => {
            expect(getAvailableTransitions(ServiceOrderStatus.COMPLETED)).toEqual([]);
        });

        it('returns [] for cancelled', () => {
            expect(getAvailableTransitions(ServiceOrderStatus.CANCELLED)).toEqual([]);
        });

        it('returns [] for unknown status', () => {
            expect(getAvailableTransitions('unknown')).toEqual([]);
        });
    });
});
