<?php
header('Content-Type: application/json; charset=utf-8');

$config = require __DIR__ . '/env_switch.php';
$domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
$isDev = str_contains($domain, 'dev.');

$envPath = $isDev ? __DIR__ . '/../../dev/.env' : __DIR__ . '/../../.env';
$env = parse_ini_file($envPath);
$token = $env['PANEL_TOKEN'] ?? '';
$logFile = __DIR__ . '/../../autonimus-dev.log';

$input = json_decode(file_get_contents('php://input'), true) ?? [];
if (($input['token'] ?? '') !== $token || $token === '') {
    http_response_code(403); 
    echo json_encode(['error'=>'forbidden']); 
    exit;
}
$cmd = $input['command'] ?? '';

function write_log($path, $env, $msg) {
    $line = sprintf("[%s] %s | %s\n", date('Y-m-d H:i:s'), $env, $msg);
    file_put_contents($path, $line, FILE_APPEND | LOCK_EX);
}

switch ($cmd) {
    case 'ping':
        write_log($logFile, $config['env'], 'PING OK - Panel IA DEV conectado');
        echo json_encode(['ok'=>true, 'message'=>'PING OK (Development)']); 
        break;

    case 'cache_clear':
        // Limpiar caches de app/plantillas si existieran
        $cacheDir = __DIR__ . '/../../cache/';
        if (is_dir($cacheDir)) {
            $files = glob($cacheDir . '*');
            foreach($files as $file) {
                if(is_file($file)) unlink($file);
            }
        }
        write_log($logFile, $config['env'], 'DEV Cache limpiada correctamente');
        echo json_encode(['ok'=>true, 'message'=>'DEV Cache limpiada']); 
        break;

    case 'rotate_logs':
        if (file_exists($logFile)) {
            $rot = __DIR__ . '/../../autonimus-dev-' . date('Ymd-His') . '.log';
            rename($logFile, $rot);
            write_log($logFile, $config['env'], 'DEV Logs rotados - archivo anterior: ' . basename($rot));
        }
        echo json_encode(['ok'=>true, 'message'=>'DEV Logs rotados']); 
        break;

    case 'system_info':
        $info = [
            'php_version' => PHP_VERSION,
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
            'disk_space' => disk_free_space(__DIR__),
            'development' => true,
        ];
        write_log($logFile, $config['env'], 'DEV System info: ' . json_encode($info));
        echo json_encode(['ok'=>true, 'message'=>'DEV System info logged', 'data'=>$info]); 
        break;

    default:
        http_response_code(400);
        echo json_encode(['error'=>'command_not_supported']);
}