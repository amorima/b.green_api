<?php
// GET /info
header('Content-Type: application/json');
echo json_encode([
    'name' => 'B.Green API',
    'version' => '1.0',
    'description' => 'API para cálculo e gestão de chaves',
    'author' => 'António Amorim @ ESMAD'
]);
