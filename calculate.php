<?php

/**
 * POST /calculate
 * Recebe dados, valida API Key e devolve cálculo
 */
require_once 'config.php';
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError(405, 'Metodo nao permitido');
}

$key = null;
if (function_exists('getallheaders')) {
    $headers = getallheaders();
    $key = $headers['X-API-Key'] ?? $headers['x-api-key'] ?? null;
}
if (!$key) {
    $key = $_SERVER['HTTP_X_API_KEY'] ?? null;
}

if (!$key) {
    sendError(401, 'API Key obrigatoria. Use header X-API-Key');
}

$keys = loadApiKeys();
$found = false;
$keyIndex = null;

foreach ($keys as $index => &$k) {
    if ($k['key'] === $key && !($k['blocked'] ?? false)) {
        $found = true;
        $keyIndex = $index;
        break;
    }
}

if (!$found) {
    sendError(403, 'API Key invalida ou bloqueada');
}

$keys[$keyIndex]['requests'] = ($keys[$keyIndex]['requests'] ?? 0) + 1;
$keys[$keyIndex]['lastUsed'] = date('c');
saveApiKeys($keys);

$input = getJsonInput();
$type = $input['type'] ?? null;

$CARBON_FACTORS = [
    'car_gasoline' => 0.17,
    'car_diesel' => 0.185,
    'car_electric' => 0.045,
    'bus' => 0.089,
    'train' => 0.041,
    'plane_short' => 0.255,
    'plane_long' => 0.195,
    'electricity' => 0.188,
    'natural_gas' => 0.203,
    'heating_oil' => 0.265,
    'meal_meat' => 7.2,
    'meal_vegetarian' => 2.0,
    'meal_vegan' => 1.5,
    'waste_general' => 0.45,
    'waste_recycled' => 0.021,
    'water' => 0.149,
];

$DEVICE_POWER_WATTS = [
    'laptop' => 50,
    'desktop' => 200,
    'television' => 150,
    'air_conditioner' => 3500,
    'refrigerator' => 150,
    'washing_machine' => 500,
    'dishwasher' => 1800,
];

$carbon = 0;
$unit = "kg CO2e";

if (isset($DEVICE_POWER_WATTS[$type])) {
    // Cálculo para dispositivos
    $minutes = $input['minutes'] ?? null;
    if ($minutes === null) {
        sendError(400, "Campo 'minutes' obrigatorio para dispositivos");
    }
    $watts = $DEVICE_POWER_WATTS[$type];
    $hours = $minutes / 60;
    $kwh = ($watts * $hours) / 1000;
    $carbon = $kwh * $CARBON_FACTORS['electricity'];
} elseif (isset($CARBON_FACTORS[$type])) {
    // Cálculo geral
    $amount = $input['amount'] ?? null;
    if ($amount === null) {
        sendError(400, "Campo 'amount' obrigatorio");
    }
    $carbon = $amount * $CARBON_FACTORS[$type];
} else {
    sendError(400, "Tipo de calculo invalido");
}

$response = [
    'type' => $type,
    'carbon_kg_co2' => round($carbon, 3),
    'unit' => $unit,
    'factor' => isset($CARBON_FACTORS[$type]) ? $CARBON_FACTORS[$type] : (isset($DEVICE_POWER_WATTS[$type]) ? $CARBON_FACTORS['electricity'] : null)
];

if (isset($minutes)) {
    $response['minutes'] = $minutes;
    $response['kwh'] = isset($kwh) ? round($kwh, 4) : null;
}
if (isset($amount)) {
    $response['amount'] = $amount;
}

sendSuccess('Cálculo efetuado com sucesso', $response);
