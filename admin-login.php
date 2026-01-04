<?php

/**
 * POST /admin-login
 * Autenticação do administrador
 */
require_once 'config.php';
require_once 'functions.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError(405, 'Método não permitido');
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

// Validar apenas a password (já que o frontend só envia password)
if (password_verify($pass, ADMIN_PASS_HASH)) {
    sendSuccess('Login efetuado', ['token' => ADMIN_TOKEN]);
} else {
    sendError(401, 'Credenciais inválidas');
}
