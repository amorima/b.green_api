<?php

/**
 * POST /admin-login
 * Autenticação do administrador
 */
require_once 'config.php';
require_once 'functions.php';
    sendError(405, 'Método não permitido');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError(405, 'Metodo nao permitido');
}

// Tentar obter password do header (como o frontend envia)
$pass = null;
if (function_exists('getallheaders')) {
    $headers = getallheaders();
    $pass = $headers['X-Admin-Password'] ?? $headers['x-admin-password'] ?? null;
}
if (!$pass) {
    $pass = $_SERVER['HTTP_X_ADMIN_PASSWORD'] ?? null;
}

// Se não vier no header, tentar no body (fallback)
if (!$pass) {
    $input = getJsonInput();
    $pass = $input['pass'] ?? null;
}

if (password_verify($pass, ADMIN_PASS_HASH)) {
    sendSuccess('Login efetuado', ['token' => ADMIN_TOKEN]);
} else {
    sendError(401, 'Credenciais inválidas');
}
// Validar apenas a password (ja que o frontend so envia password)
if (password_verify($pass, ADMIN_PASS_HASH)) {
    sendSuccess('Login efetuado', ['token' => ADMIN_TOKEN]);
} else {
    sendError(401, 'Credenciais invalidas');
}
