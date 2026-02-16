/**
 * Categorias de produtos disponíveis
 * Mapeamento de valor (backend) para label (frontend)
 */
export const productCategories = [
    { value: 'engine', label: 'Motor' },
    { value: 'suspension', label: 'Suspensão' },
    { value: 'brakes', label: 'Freios' },
    { value: 'electrical', label: 'Elétrica' },
    { value: 'filters', label: 'Filtros' },
    { value: 'fluids', label: 'Fluidos' },
    { value: 'tires', label: 'Pneus' },
    { value: 'body_parts', label: 'Lataria' },
    { value: 'interior', label: 'Interior' },
    { value: 'tools', label: 'Ferramentas' },
    { value: 'other', label: 'Outros' },
];

/**
 * Unidades de medida disponíveis
 * Mapeamento de valor (backend) para label (frontend)
 */
export const productUnits = [
    { value: 'unit', label: 'Unidade' },
    { value: 'liter', label: 'Litro' },
    { value: 'kg', label: 'Quilograma' },
    { value: 'meter', label: 'Metro' },
    { value: 'box', label: 'Caixa' },
];

/**
 * Retorna o label de uma categoria pelo valor
 * @param {string} value
 * @returns {string}
 */
export function getCategoryLabel(value) {
    const category = productCategories.find(c => c.value === value);
    return category ? category.label : value;
}

/**
 * Retorna o label de uma unidade pelo valor
 * @param {string} value
 * @returns {string}
 */
export function getUnitLabel(value) {
    const unit = productUnits.find(u => u.value === value);
    return unit ? unit.label : value;
}
