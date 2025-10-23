<?php
header('Content-Type: application/json; charset=utf-8');
$config = require __DIR__ . '/env_switch.php';

$db = $config['db'];
$db_ok = false;
$mysqli = @new mysqli($db['host'], $db['user'], $db['pass'], $db['name']);
if (!$mysqli->connect_errno) { 
    $db_ok = true; 
    $mysqli->close();
}

echo json_encode([
    'env' => $config['env'],
    'db_ok' => $db_ok,
    'server_time' => date('Y-m-d H:i:s'),
    'domain' => $_SERVER['HTTP_HOST'] ?? 'localhost',
    'db_info' => [
        'host' => $db['host'],
        'name' => $db['name'],
        'user' => $db['user']
    ],
    'development_mode' => true
]);