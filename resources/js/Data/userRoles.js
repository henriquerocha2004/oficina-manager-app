export const userRoles = [
    { value: 'administrator', label: 'Administrador' },
    { value: 'reception', label: 'Recepcao' },
    { value: 'mechanic', label: 'Mecanico' },
];

export function getUserRoleLabel(role) {
    const found = userRoles.find((item) => item.value === role);

    return found ? found.label : role;
}

export function getUserRoleBadgeClass(role) {
    if (role === 'administrator') {
        return 'px-2 py-1 text-xs rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
    }

    if (role === 'reception') {
        return 'px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
    }

    if (role === 'mechanic') {
        return 'px-2 py-1 text-xs rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200';
    }

    return 'px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
}