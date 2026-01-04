<?php

/**
 * POST /api/admin/login
 * Autenticação admin
 */

require_once 'config.php';
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError(405, 'Método não permitido');
}

$input = getJsonInput();
$password = $input['password'] ?? null;

if ($password !== ADMIN_PASSWORD) {
    sendError(401, 'Password incorreta');
}

sendSuccess('Login efetuado');
