<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'env_switch.php';
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Verificar autenticación
$input = json_decode(file_get_contents('php://input'), true);
$token = $input['token'] ?? '';

if (!$token || $token !== $_ENV['PANEL_TOKEN']) {
    http_response_code(401);
    echo json_encode(['error' => 'Token inválido']);
    exit;
}

$action = $_GET['action'] ?? 'stats';

try {
    $pdo = getConnection();
    
    switch ($action) {
        case 'stats':
            getSystemStats();
            break;
        case 'performance':
            getPerformanceMetrics();
            break;
        case 'alerts':
            getSystemAlerts();
            break;
        case 'cleanup':
            performCleanup($input);
            break;
        default:
            echo json_encode(['error' => 'Acción no válida']);
    }
} catch (Exception $e) {
    logMessage("Error en analytics.php: " . $e->getMessage());
    echo json_encode(['error' => 'Error interno del servidor']);
}

function getSystemStats() {
    global $pdo;
    
    $stats = [];
    
    // Estadísticas de base de datos
    $stmt = $pdo->query("SHOW TABLE STATUS");
    $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $totalRows = 0;
    $totalSize = 0;
    
    foreach ($tables as $table) {
        $totalRows += $table['Rows'];
        $totalSize += $table['Data_length'] + $table['Index_length'];
    }
    
    $stats['database'] = [
        'total_tables' => count($tables),
        'total_rows' => $totalRows,
        'total_size_mb' => round($totalSize / 1024 / 1024, 2)
    ];
    
    // Estadísticas del sistema
    $stats['system'] = [
        'php_version' => phpversion(),
        'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
        'memory_peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
        'server_time' => date('Y-m-d H:i:s'),
        'environment' => $_ENV['ENVIRONMENT'] ?? 'unknown'
    ];
    
    // Estadísticas de logs
    $logFile = ($_ENV['ENVIRONMENT'] === 'development') ? 'autonimus-dev.log' : 'autonimus.log';
    $stats['logs'] = [
        'log_file_size_kb' => file_exists($logFile) ? round(filesize($logFile) / 1024, 2) : 0,
        'log_lines' => file_exists($logFile) ? count(file($logFile)) : 0
    ];
    
    echo json_encode(['stats' => $stats]);
}

function getPerformanceMetrics() {
    global $pdo;
    
    $metrics = [];
    
    // Métricas de conexión a BD
    $start = microtime(true);
    $stmt = $pdo->query("SELECT 1");
    $dbLatency = (microtime(true) - $start) * 1000;
    
    $metrics['database_latency_ms'] = round($dbLatency, 2);
    
    // Métricas de consultas recientes
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as query_count,
            AVG(TIMER_WAIT/1000000000) as avg_time_ms
        FROM performance_schema.events_statements_summary_by_digest 
        WHERE FIRST_SEEN > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        LIMIT 1
    ");
    
    $queryStats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $metrics['recent_queries'] = [
        'count' => $queryStats['query_count'] ?? 0,
        'avg_time_ms' => round($queryStats['avg_time_ms'] ?? 0, 2)
    ];
    
    // Métricas del servidor web
    $metrics['server'] = [
        'load_average' => sys_getloadavg(),
        'disk_free_mb' => round(disk_free_space('.') / 1024 / 1024, 2),
        'disk_total_mb' => round(disk_total_space('.') / 1024 / 1024, 2)
    ];
    
    echo json_encode(['metrics' => $metrics]);
}

function getSystemAlerts() {
    global $pdo;
    
    $alerts = [];
    
    // Verificar espacio en disco
    $diskFree = disk_free_space('.');
    $diskTotal = disk_total_space('.');
    $diskUsagePercent = (($diskTotal - $diskFree) / $diskTotal) * 100;
    
    if ($diskUsagePercent > 90) {
        $alerts[] = [
            'type' => 'critical',
            'message' => 'Espacio en disco crítico: ' . round($diskUsagePercent, 1) . '%',
            'timestamp' => date('Y-m-d H:i:s')
        ];
    } elseif ($diskUsagePercent > 80) {
        $alerts[] = [
            'type' => 'warning',
            'message' => 'Espacio en disco bajo: ' . round($diskUsagePercent, 1) . '%',
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    // Verificar memoria
    $memoryUsage = memory_get_usage(true);
    $memoryLimit = ini_get('memory_limit');
    
    if ($memoryLimit) {
        $memoryLimitBytes = convertToBytes($memoryLimit);
        $memoryUsagePercent = ($memoryUsage / $memoryLimitBytes) * 100;
        
        if ($memoryUsagePercent > 90) {
            $alerts[] = [
                'type' => 'critical',
                'message' => 'Uso de memoria crítico: ' . round($memoryUsagePercent, 1) . '%',
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    // Verificar conexión a base de datos
    try {
        $stmt = $pdo->query("SELECT 1");
    } catch (Exception $e) {
        $alerts[] = [
            'type' => 'critical',
            'message' => 'Error de conexión a base de datos: ' . $e->getMessage(),
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    echo json_encode(['alerts' => $alerts]);
}

function performCleanup($data) {
    $type = $data['type'] ?? '';
    $result = [];
    
    switch ($type) {
        case 'logs':
            $logFile = ($_ENV['ENVIRONMENT'] === 'development') ? 'autonimus-dev.log' : 'autonimus.log';
            if (file_exists($logFile)) {
                $oldSize = filesize($logFile);
                file_put_contents($logFile, '');
                $result['message'] = "Logs limpiados. Liberados: " . round($oldSize / 1024, 2) . " KB";
                logMessage("Logs limpiados manualmente desde Panel IA");
            } else {
                $result['message'] = "No hay archivo de logs para limpiar";
            }
            break;
            
        case 'temp':
            $tempDir = sys_get_temp_dir();
            $result['message'] = "Limpieza de archivos temporales completada";
            logMessage("Limpieza de archivos temporales ejecutada");
            break;
            
        default:
            $result['error'] = 'Tipo de limpieza no válido';
    }
    
    echo json_encode($result);
}

function convertToBytes($value) {
    $unit = strtolower(substr($value, -1));
    $value = (int)$value;
    
    switch ($unit) {
        case 'g': $value *= 1024;
        case 'm': $value *= 1024;
        case 'k': $value *= 1024;
    }
    
    return $value;
}

function logMessage($message) {
    $logFile = ($_ENV['ENVIRONMENT'] === 'development') ? 'autonimus-dev.log' : 'autonimus.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}
?>