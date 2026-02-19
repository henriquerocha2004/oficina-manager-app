export const movementTypes = [
    { value: 'IN', label: 'Entrada', icon: 'ki-arrow-down', color: 'green' },
    { value: 'OUT', label: 'Saída', icon: 'ki-arrow-up', color: 'red' },
];

export const movementReasons = [
    { value: 'purchase', label: 'Compra' },
    { value: 'sale', label: 'Venda' },
    { value: 'adjustment', label: 'Ajuste' },
    { value: 'loss', label: 'Perda' },
    { value: 'return', label: 'Devolução' },
    { value: 'transfer', label: 'Transferência' },
    { value: 'initial', label: 'Estoque Inicial' },
    { value: 'other', label: 'Outro' },
];

export function getMovementTypeLabel(value) {
    const type = movementTypes.find(t => t.value === value);
    return type ? type.label : value;
}

export function getMovementReasonLabel(value) {
    const reason = movementReasons.find(r => r.value === value);
    return reason ? reason.label : value;
}

export function getMovementTypeIcon(value) {
    const type = movementTypes.find(t => t.value === value);
    return type ? type.icon : 'ki-question';
}

export function getMovementTypeColor(value) {
    const type = movementTypes.find(t => t.value === value);
    return type ? type.color : 'gray';
}
