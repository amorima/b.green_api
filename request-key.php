<?php

/**
 * POST /request-key
 * Criar ou obter API Key por email
 */
require_once 'config.php';
require_once 'functions.php';
    sendError(405, 'Método não permitido');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError(405, 'Metodo nao permitido');
}
$input = getJsonInput();
$email = $input['email'] ?? null;
    sendError(400, 'Email válido obrigatório');
}
if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendError(400, 'Email valido obrigatorio');
}
$keys = loadApiKeys();
foreach ($keys as $k) {
    if ($k['email'] === $email) {
            'key' => $k['key'],
            'email' => $k['email'],
            'createdAt' => $k['createdAt'],
            'requests' => $k['requests'] ?? 0
        ]);
    }
}
        sendSuccess('Key existente retornada', [
            'key' => $k['key'],
            'email' => $k['email'],
            'createdAt' => $k['createdAt'],
            'requests' => $k['requests'] ?? 0
        ]);
    }
}
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
    'key' => $newKey['key'],
    'email' => $newKey['email'],
    'createdAt' => $newKey['createdAt']
]);

sendSuccess('API Key criada com sucesso', [
    'key' => $newKey['key'],
    'email' => $newKey['email'],
    'createdAt' => $newKey['createdAt']
]);
