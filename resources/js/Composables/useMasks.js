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

    return {
        maskCEP,
        maskDocument,
        maskPhone,
        unmask,
        getDocMaxLength,
        getPhoneMaxLength
    };
}