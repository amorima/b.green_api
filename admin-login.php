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
$input = getJsonInput();
$user = $input['user'] ?? null;
$pass = $input['pass'] ?? null;
if ($user === ADMIN_USER && password_verify($pass, ADMIN_PASS_HASH)) {
    sendSuccess('Login efetuado', ['token' => ADMIN_TOKEN]);
} else {
    sendError(401, 'Credenciais inválidas');
}
