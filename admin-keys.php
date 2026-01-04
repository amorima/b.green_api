<?php

/**
 * GET/POST /admin-keys
 * Gestão de API Keys pelo admin
 */
require_once 'config.php';
require_once 'functions.php';
// Autenticação por token
$headers = getallheaders();
$token = $headers['Authorization'] ?? '';
if ($token !== ADMIN_TOKEN) {
    sendError(401, 'Token de admin inválido');
}
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $keys = loadApiKeys();
    sendSuccess('Lista de API Keys', ['keys' => $keys]);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = getJsonInput();
    $action = $input['action'] ?? '';
    $key = $input['key'] ?? '';
    $keys = loadApiKeys();
    $found = false;
    foreach ($keys as &$k) {
        if ($k['key'] === $key) {
            $found = true;
            if ($action === 'block') {
                $k['blocked'] = true;
            } elseif ($action === 'unblock') {
                $k['blocked'] = false;
            }
            break;
        }
    }
    if (!$found) {
        sendError(404, 'API Key não encontrada');
    }
    saveApiKeys($keys);
    sendSuccess('API Key atualizada', ['key' => $key, 'action' => $action]);
} else {
    sendError(405, 'Método não permitido');
}
