<?php

/**
 * GET /api/admin/keys - Listar todas as keys
 * PUT /api/admin/keys/{key}/block - Bloquear/desbloquear key
 * DELETE /api/admin/keys/{key} - Eliminar key
 */

require_once 'config.php';
require_once 'functions.php';

if (!authenticateAdmin()) {
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

// GET - Listar todas as keys
if ($method === 'GET') {
    $keys = loadApiKeys();
    sendSuccess('Keys listadas', $keys);
}

// PUT - Bloquear/desbloquear key
if ($method === 'PUT') {
    $uri = $_SERVER['REQUEST_URI'];
    preg_match('/\/admin-keys\.php\?key=([^&]+)&action=block/', $uri, $matches);

    if (!isset($matches[1])) {
        sendError(400, 'Key não especificada');
    }

    $keyToBlock = urldecode($matches[1]);
    $input = getJsonInput();
    $blocked = $input['blocked'] ?? true;

    $keys = loadApiKeys();
    $found = false;

    foreach ($keys as $index => $k) {
        if ($k['key'] === $keyToBlock) {
            $keys[$index]['blocked'] = $blocked;
            $found = true;
            break;
        }
    }

    if (!$found) {
        sendError(404, 'Key não encontrada');
    }

    saveApiKeys($keys);
    sendSuccess($blocked ? 'Key bloqueada' : 'Key desbloqueada', $keys[$index]);
}

// DELETE - Eliminar key
if ($method === 'DELETE') {
    $uri = $_SERVER['REQUEST_URI'];
    preg_match('/\/admin-keys\.php\?key=([^&]+)/', $uri, $matches);

    if (!isset($matches[1])) {
        sendError(400, 'Key não especificada');
    }

    $keyToDelete = urldecode($matches[1]);
    $keys = loadApiKeys();
    $initialCount = count($keys);

    $keys = array_values(array_filter($keys, function ($k) use ($keyToDelete) {
        return $k['key'] !== $keyToDelete;
    }));

    if (count($keys) === $initialCount) {
        sendError(404, 'Key não encontrada');
    }

    saveApiKeys($keys);
    sendSuccess('Key eliminada');
}

sendError(405, 'Método não permitido');
