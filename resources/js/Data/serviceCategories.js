/**
 * Categorias de serviços com tradução PT/EN
 */

export const serviceCategories = [
    { value: 'maintenance', label: 'Manutenção' },
    { value: 'repair', label: 'Reparo' },
    { value: 'diagnostic', label: 'Diagnóstico' },
    { value: 'painting', label: 'Pintura' },
    { value: 'alignment', label: 'Alinhamento' },
    { value: 'other', label: 'Outro' },
];

/**
 * Retorna o label traduzido de uma categoria
 * @param {string} category - Valor da categoria em inglês
 * @returns {string} - Label em português
 */
export function getCategoryLabel(category) {
    const found = serviceCategories.find(c => c.value === category);
    return found ? found.label : category;
}
