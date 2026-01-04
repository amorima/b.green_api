<?php

/**
 * POST /api/calculate
 * Calcular emissões de carbono
 */

require_once 'config.php';
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError(405, 'Método não permitido');
}

// Autenticar API Key
if (!authenticateApiKey()) {
    exit();
}

$input = getJsonInput();
$type = $input['type'] ?? null;
$amount = $input['amount'] ?? null;
$minutes = $input['minutes'] ?? null;
$power_watts = $input['power_watts'] ?? null;

if (!$type) {
    sendError(400, 'Campo type obrigatório');
}

// Verificar se é dispositivo
$isDevice = array_key_exists($type, DEVICE_POWER_WATTS);

if ($isDevice) {
    if (!$minutes || $minutes <= 0) {
        sendError(400, 'Campo minutes obrigatório para dispositivos');
    }

    $effectivePower = ($power_watts && $power_watts > 0) ? $power_watts : DEVICE_POWER_WATTS[$type];
    $kwh = ($effectivePower / 1000) * ($minutes / 60);
    $carbonKg = $kwh * CARBON_FACTORS['electricity'];

    sendSuccess('Cálculo concluído', [
        'type' => $type,
        'minutes' => (int)$minutes,
        'power_watts' => $effectivePower,
        'is_custom_power' => $power_watts && $power_watts > 0,
        'standard_power_watts' => DEVICE_POWER_WATTS[$type],
        'kwh' => round($kwh, 3),
        'carbon_kg_co2' => round($carbonKg, 3),
        'unit' => 'kg CO2e'
    ]);
}

// Tipo normal (não dispositivo)
if (!array_key_exists($type, CARBON_FACTORS)) {
    sendError(400, 'Tipo não reconhecido');
}

if (!$amount || $amount <= 0) {
    sendError(400, 'Campo amount obrigatório');
}

$factor = CARBON_FACTORS[$type];
$carbonKg = $amount * $factor;

sendSuccess('Cálculo concluído', [
    'type' => $type,
    'amount' => (float)$amount,
    'factor' => $factor,
    'carbon_kg_co2' => round($carbonKg, 3),
    'unit' => 'kg CO2e'
]);
