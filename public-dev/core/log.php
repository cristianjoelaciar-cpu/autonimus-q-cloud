<?php
header('Content-Type: application/json; charset=utf-8');

$config = require __DIR__ . '/env_switch.php';
$domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
$isDev = str_contains($domain, 'dev.');

$envPath = $isDev ? __DIR__ . '/../../dev/.env' : __DIR__ . '/../../.env';
$env = parse_ini_file($envPath);
$token = $env['PANEL_TOKEN'] ?? '';
$logFile = __DIR__ . '/../../autonimus-dev.log';

$action = $_GET['action'] ?? 'read';

if ($action === 'read') {
    $limit = max(10, min(1000, intval($_GET['limit'] ?? 200)));
    if (!file_exists($logFile)) { 
        echo json_encode(['lines'=>['[INFO] Development log file not found - system ready']]); 
        exit; 
    }
    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $slice = array_slice($lines, -$limit);
    echo json_encode(['lines'=>$slice]); 
    exit;
}

if ($action === 'write') {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    if (($input['token'] ?? '') !== $token || $token === '') {
        http_response_code(403); 
        echo json_encode(['error'=>'forbidden']); 
        exit;
    }
    $msg = trim($input['message'] ?? 'Log');
    $line = sprintf("[%s] %s | %s\n", date('Y-m-d H:i:s'), $config['env'], $msg);
    file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    echo json_encode(['ok'=>true]); 
    exit;
}

http_response_code(400);
echo json_encode(['error'=>'bad_request']);