<?php

/**
 * POST /api/request-key
 * Criar ou obter API Key por email
 */

require_once 'config.php';
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError(405, 'Método não permitido');
}

$input = getJsonInput();
$email = $input['email'] ?? null;

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendError(400, 'Email válido obrigatório');
}

$keys = loadApiKeys();

// Verificar se já existe key para este email
foreach ($keys as $k) {
    if ($k['email'] === $email) {
        sendSuccess('Key existente retornada', [
            'key' => $k['key'],
            'email' => $k['email'],
            'createdAt' => $k['createdAt'],
            'requests' => $k['requests'] ?? 0
        ]);
    }
}

// Criar nova key
$newKey = [
    'key' => generateApiKey(),
    'email' => $email,
    'createdAt' => date('c'),
    'requests' => 0,
    'blocked' => false,
    'lastUsed' => null
];

$keys[] = $newKey;
saveApiKeys($keys);

sendSuccess('API Key criada com sucesso', [
    'key' => $newKey['key'],
    'email' => $newKey['email'],
    'createdAt' => $newKey['createdAt']
]);
