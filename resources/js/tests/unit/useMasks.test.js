import { describe, it, expect } from 'vitest';
import { useMasks } from '@/Composables/useMasks.js';

describe('useMasks', () => {
    const {
        maskCEP,
        maskDocument,
        maskPhone,
        unmask,
        getDocMaxLength,
        getPhoneMaxLength,
        licensePlateMask,
        unmaskLicensePlate,
        maskCurrency,
        unmaskCurrency,
    } = useMasks();

    // ─── maskCEP ─────────────────────────────────────────────────────────

    describe('maskCEP', () => {
        it('formats 8-digit CEP', () => {
            expect(maskCEP('01310100')).toBe('01310-100');
        });

        it('formats partial CEP input', () => {
            expect(maskCEP('01310')).toBe('01310');
            expect(maskCEP('0131010')).toBe('01310-10');
        });

        it('strips non-numeric characters', () => {
            expect(maskCEP('01310-100')).toBe('01310-100');
        });

        it('returns empty string for falsy value', () => {
            expect(maskCEP('')).toBe('');
            expect(maskCEP(null)).toBe('');
        });
    });

    // ─── maskDocument ─────────────────────────────────────────────────────

    describe('maskDocument', () => {
        it('formats CPF (11 digits)', () => {
            expect(maskDocument('12345678901')).toBe('123.456.789-01');
        });

        it('formats partial CPF input', () => {
            expect(maskDocument('12345')).toBe('123.45');
            expect(maskDocument('123456789')).toBe('123.456.789');
        });

        it('formats CNPJ (14 digits)', () => {
            expect(maskDocument('12345678000195')).toBe('12.345.678/0001-95');
        });

        it('strips non-numeric characters before formatting', () => {
            expect(maskDocument('123.456.789-01')).toBe('123.456.789-01');
        });

        it('returns empty string for falsy value', () => {
            expect(maskDocument('')).toBe('');
            expect(maskDocument(null)).toBe('');
        });
    });

    // ─── maskPhone ────────────────────────────────────────────────────────

    describe('maskPhone', () => {
        it('formats landline (10 digits)', () => {
            expect(maskPhone('1133334444')).toBe('(11) 3333-4444');
        });

        it('formats mobile (11 digits)', () => {
            expect(maskPhone('11987654321')).toBe('(11) 98765-4321');
        });

        it('formats partial input', () => {
            // 2 digits: regex needs 3 to trigger area-code format
            expect(maskPhone('11')).toBe('11');
            expect(maskPhone('1133')).toBe('(11) 33');
        });

        it('strips non-numeric characters before formatting', () => {
            expect(maskPhone('(11) 3333-4444')).toBe('(11) 3333-4444');
        });

        it('returns empty string for falsy value', () => {
            expect(maskPhone('')).toBe('');
            expect(maskPhone(null)).toBe('');
        });
    });

    // ─── unmask ───────────────────────────────────────────────────────────

    describe('unmask', () => {
        it('removes all non-numeric characters from CPF', () => {
            expect(unmask('123.456.789-01')).toBe('12345678901');
        });

        it('removes all non-numeric characters from phone', () => {
            expect(unmask('(11) 98765-4321')).toBe('11987654321');
        });

        it('removes all non-numeric characters from CEP', () => {
            expect(unmask('01310-100')).toBe('01310100');
        });

        it('returns empty string for falsy value', () => {
            expect(unmask('')).toBe('');
            expect(unmask(null)).toBe('');
        });
    });

    // ─── getDocMaxLength ──────────────────────────────────────────────────

    describe('getDocMaxLength', () => {
        it('returns 14 for incomplete CPF (fewer than 11 clean digits)', () => {
            expect(getDocMaxLength('123.456')).toBe(14);
        });

        it('returns 18 when CPF is complete or value is a CNPJ (>= 11 clean digits)', () => {
            // Complete CPF (11 clean digits) → allows CNPJ length in case user continues typing
            expect(getDocMaxLength('123.456.789-01')).toBe(18);
            expect(getDocMaxLength('12.345.678/0001-95')).toBe(18);
        });

        it('returns 14 for empty value', () => {
            expect(getDocMaxLength('')).toBe(14);
            expect(getDocMaxLength(null)).toBe(14);
        });
    });

    // ─── getPhoneMaxLength ────────────────────────────────────────────────

    describe('getPhoneMaxLength', () => {
        it('returns 15 for mobile (11 clean digits)', () => {
            expect(getPhoneMaxLength('(11) 98765-4321')).toBe(15);
        });

        it('returns 16 for landline (10 or fewer clean digits)', () => {
            expect(getPhoneMaxLength('(11) 3333-4444')).toBe(16);
        });

        it('returns 16 for empty value', () => {
            expect(getPhoneMaxLength('')).toBe(16);
            expect(getPhoneMaxLength(null)).toBe(16);
        });
    });

    // ─── licensePlateMask ─────────────────────────────────────────────────

    describe('licensePlateMask', () => {
        it('formats old-style plate (ABC-1234)', () => {
            expect(licensePlateMask('ABC1234')).toBe('ABC-1234');
        });

        it('formats Mercosul plate (ABC-1D23)', () => {
            expect(licensePlateMask('ABC1D23')).toBe('ABC-1D23');
        });

        it('converts to uppercase', () => {
            expect(licensePlateMask('abc1234')).toBe('ABC-1234');
        });

        it('returns empty string for falsy value', () => {
            expect(licensePlateMask('')).toBe('');
            expect(licensePlateMask(null)).toBe('');
        });

        it('limits to 8 characters (ABC-XXXX)', () => {
            expect(licensePlateMask('ABC12345678')).toBe('ABC-1234');
        });
    });

    // ─── unmaskLicensePlate ───────────────────────────────────────────────

    describe('unmaskLicensePlate', () => {
        it('removes hyphen from plate', () => {
            expect(unmaskLicensePlate('ABC-1234')).toBe('ABC1234');
        });

        it('converts to uppercase', () => {
            expect(unmaskLicensePlate('abc-1234')).toBe('ABC1234');
        });

        it('returns empty string for falsy value', () => {
            expect(unmaskLicensePlate('')).toBe('');
            expect(unmaskLicensePlate(null)).toBe('');
        });
    });

    // ─── maskCurrency ─────────────────────────────────────────────────────

    describe('maskCurrency', () => {
        it('formats integer value as currency', () => {
            expect(maskCurrency('100')).toBe('R$ 1,00');
        });

        it('formats value with cents', () => {
            expect(maskCurrency('12356')).toBe('R$ 123,56');
        });

        it('formats thousands with dot separator', () => {
            expect(maskCurrency('123456')).toBe('R$ 1.234,56');
        });

        it('returns empty string for falsy value', () => {
            expect(maskCurrency('')).toBe('');
            expect(maskCurrency(null)).toBe('');
        });
    });

    // ─── unmaskCurrency ───────────────────────────────────────────────────

    describe('unmaskCurrency', () => {
        it('converts "R$ 1.234,56" to 1234.56', () => {
            expect(unmaskCurrency('R$ 1.234,56')).toBe(1234.56);
        });

        it('converts "R$ 100,00" to 100', () => {
            expect(unmaskCurrency('R$ 100,00')).toBe(100);
        });

        it('returns 0 for falsy value', () => {
            expect(unmaskCurrency('')).toBe(0);
            expect(unmaskCurrency(null)).toBe(0);
        });
    });
});
