export function useMasks() {
    const maskCEP = (value) => {
        if (!value) return '';
        return value
            .replace(/\D/g, '')
            .replace(/^(\d{5})(\d)/, '$1-$2')
            .substring(0, 9);
    };

    const maskDocument = (value) => {
        if (!value) return '';
        const clean = value.replace(/\D/g, '');

        if (clean.length <= 11) {
            // CPF: 000.000.000-00
            return clean
                .replace(/(\d{3})(\d)/, '$1.$2')
                .replace(/(\d{3})(\d)/, '$1.$2')
                .replace(/(\d{3})(\d{1,2})$/, '$1-$2')
                .substring(0, 14);
        }
        // CNPJ: 00.000.000/0000-00
        return clean
            .replace(/^(\d{2})(\d)/, '$1.$2')
            .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
            .replace(/\.(\d{3})(\d)/, '.$1/$2')
            .replace(/(\d{4})(\d)/, '$1-$2')
            .substring(0, 18);
    };

    const maskPhone = (value) => {
        if (!value) return '';
        const clean = value.replace(/\D/g, '');

        // Landline (10 digits): (00) 0000-0000 -> max length 14
        // Mobile (11 digits): (00) 00000-0000 -> max length 15
        if (clean.length <= 10) {
            return clean
                .replace(/^(\d{2})(\d)/, '($1) $2')
                .replace(/(\d{4})(\d)/, '$1-$2')
                .substring(0, 14);
        }

        return clean
            .replace(/^(\d{2})(\d)/, '($1) $2')
            .replace(/(\d{5})(\d)/, '$1-$2')
            .substring(0, 15);
    };

    const unmask = (value) => (value ? value.replace(/\D/g, '') : '');

    const getDocMaxLength = (value) => {
        if (!value) return 14;
        const clean = value.replace(/\D/g, '');
        return clean.length >= 11 ? 18 : 14;
    };

    const getPhoneMaxLength = (value) => {
        if (!value) return 16;
        const clean = value.replace(/\D/g, '');
        return clean.length >= 11 ? 15 : 16;
    };

    const licensePlateMask = (value) => {
        if (!value) return '';
        const clean = value.replace(/[^A-Za-z0-9]/g, '').toUpperCase();

        // Formato Mercosul: ABC1D23 (7 caracteres)
        // Formato Antigo: ABC1234 (7 caracteres)
        if (clean.length <= 3) {
            // Apenas letras no início
            return clean.replace(/[^A-Z]/g, '');
        } else if (clean.length === 4) {
            // ABC1
            return clean.substring(0, 3) + '-' + clean.substring(3);
        } else if (clean.length <= 7) {
            // ABC-1234 ou ABC-1D23
            const letters = clean.substring(0, 3);
            const rest = clean.substring(3);
            return letters + '-' + rest;
        }

        // Limita a 8 caracteres (ABC-1234 ou ABC-1D23)
        return clean.substring(0, 3) + '-' + clean.substring(3, 7);
    };

    const unmaskLicensePlate = (value) => {
        if (!value) return '';
        // Remove apenas o hífen, mantém letras e números
        return value.replace(/-/g, '').toUpperCase();
    };

    return {
        maskCEP,
        maskDocument,
        maskPhone,
        licensePlateMask,
        unmaskLicensePlate,
        unmask,
        getDocMaxLength,
        getPhoneMaxLength
    };
}