<?php

/**
 * POST /calculate
 * Recebe dados, valida API Key e devolve cálculo
 */
require_once 'config.php';
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError(405, 'Método não permitido');
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
    sendError(401, 'API Key obrigatória. Use header X-API-Key');
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
    sendError(403, 'API Key inválida ou bloqueada');
}

$keys[$keyIndex]['requests'] = ($keys[$keyIndex]['requests'] ?? 0) + 1;
$keys[$keyIndex]['lastUsed'] = date('c');
saveApiKeys($keys);

$input = getJsonInput();
$values = $input['values'] ?? [];
if (!is_array($values) || empty($values)) {
    sendError(400, 'Valores inválidos');
}

$result = array_sum($values);
sendSuccess('Cálculo efetuado com sucesso', [
    'result' => $result,
    'count' => count($values)
]);
