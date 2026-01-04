<?php

/**
 * b.green API - Funções auxiliares
 */

function loadApiKeys()
{
    if (!file_exists(API_KEYS_FILE)) {
        return [];
    }
    $data = file_get_contents(API_KEYS_FILE);
    return json_decode($data, true) ?: [];
}

function saveApiKeys($keys)
{
    $dir = dirname(API_KEYS_FILE);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents(API_KEYS_FILE, json_encode($keys, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function generateApiKey()
{
    return 'bgk_' . bin2hex(random_bytes(24));
}

function authenticateApiKey()
{
    $headers = getallheaders();
    $apiKey = $headers['X-API-Key'] ?? $headers['x-api-key'] ?? null;

    if (!$apiKey) {
        sendError(401, 'API Key obrigatória. Use header X-API-Key');
        return false;
    }

    $keys = loadApiKeys();
    $keyData = null;
    $keyIndex = null;

    foreach ($keys as $index => $k) {
        if ($k['key'] === $apiKey) {
            $keyData = $k;
            $keyIndex = $index;
            break;
        }
    }

    if (!$keyData) {
        sendError(401, 'API Key inválida');
        return false;
    }

    if ($keyData['blocked'] ?? false) {
        sendError(403, 'API Key bloqueada');
        return false;
    }

    // Atualizar contagem de pedidos
    $keys[$keyIndex]['requests'] = ($keyData['requests'] ?? 0) + 1;
    $keys[$keyIndex]['lastUsed'] = date('c');
    saveApiKeys($keys);

    return true;
}

function authenticateAdmin()
{
    $headers = getallheaders();
    $password = $headers['X-Admin-Password'] ?? $headers['x-admin-password'] ?? null;

    if ($password !== ADMIN_PASSWORD) {
        sendError(401, 'Não autenticado');
        return false;
    }
    return true;
}

function sendError($code, $message)
{
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'error' => $message
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

function sendSuccess($message, $data = null)
{
    $response = ['success' => true, 'message' => $message];
    if ($data !== null) {
        $response['data'] = $data;
    }
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit();
}

function getJsonInput()
{
    $input = file_get_contents('php://input');
    return json_decode($input, true) ?: [];
}
