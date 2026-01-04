<?php

/**
 * GET /api/info
 * Informação sobre a API
 */

require_once 'config.php';

sendSuccess('Informação da API', [
    'name' => 'b.green API',
    'version' => '2.0.0',
    'types' => array_keys(CARBON_FACTORS),
    'devices' => array_keys(DEVICE_POWER_WATTS),
    'rateLimit' => '100 requests / 15 minutes per key',
    'documentation' => 'https://www.antonioamorim.pt/api/',
    'endpoints' => [
        'POST /api/request-key.php' => 'Obter API Key',
        'POST /api/calculate.php' => 'Calcular emissões',
        'POST /api/admin-login.php' => 'Login admin',
        'GET /api/admin-keys.php' => 'Listar keys (admin)',
        'GET /api/info.php' => 'Informação da API'
    ]
]);
