<?php

/**
 * b.green API - Configuração
 */

define('ADMIN_PASSWORD', 'bgreen2026');
define('API_KEYS_FILE', __DIR__ . '/../data/api-keys.json');
define('RATE_LIMIT_WINDOW', 15 * 60); // 15 minutos
define('RATE_LIMIT_MAX', 100);

// Fatores de emissão de carbono (kg CO2 por unidade)
const CARBON_FACTORS = [
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

// Potência de dispositivos (watts)
const DEVICE_POWER_WATTS = [
    'laptop' => 50,
    'desktop' => 200,
    'television' => 150,
    'air_conditioner' => 3500,
    'refrigerator' => 150,
    'washing_machine' => 500,
    'dishwasher' => 1800,
];

// Headers CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-API-Key, X-Admin-Password');
header('Content-Type: application/json; charset=UTF-8');

// Tratar OPTIONS request (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
