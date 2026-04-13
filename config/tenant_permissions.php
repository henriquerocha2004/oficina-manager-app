<?php

/**
 * Permissões por perfil de usuário (tenant).
 *
 * Cada chave é um valor de UserRoleEnum. O valor é um array de padrões
 * de nome de rota permitidos. Use '*' para acesso total ou 'prefixo.*'
 * para liberar um grupo de rotas.
 *
 * Para expandir:
 *   - Novo perfil: adicione uma chave com os padrões de rota permitidos.
 *   - Nova rota protegida: adicione o nome da rota no(s) perfil(s) permitido(s).
 */
return [
    'administrator' => [
        '*',
    ],

    'reception' => [
        'clients.*',
        'vehicles.*',
        'services.*',
        'suppliers.*',
        'products.*',
        'stock.*',
        'service-orders.*',
        'account.*',
    ],

    'mechanic' => [
        // Páginas de OS
        'service-orders.index',
        // Visualização de OS (API)
        'service-orders.find',
        'service-orders.search',
        'service-orders.show',
        'service-orders.detail',
        'service-orders.stats',
        'service-orders.list-items',
        // Atualização de OS
        'service-orders.update-diagnosis',
        'service-orders.start-work',
        'service-orders.finish-work',
        'service-orders.upload-photo',
        'service-orders.delete-photo',
        // Conta própria
        'account.*',
    ],
];
