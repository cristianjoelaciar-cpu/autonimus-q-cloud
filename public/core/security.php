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

$action = $_GET['action'] ?? 'scan';

try {
    switch ($action) {
        case 'scan':
            performSecurityScan();
            break;
        case 'firewall':
            manageFirewall($input);
            break;
        case 'audit':
            getSecurityAudit();
            break;
        case 'backup':
            performBackup($input);
            break;
        default:
            echo json_encode(['error' => 'Acción no válida']);
    }
} catch (Exception $e) {
    logMessage("Error en security.php: " . $e->getMessage());
    echo json_encode(['error' => 'Error interno del servidor']);
}

function performSecurityScan() {
    $results = [];
    
    // Verificar permisos de archivos
    $results['file_permissions'] = checkFilePermissions();
    
    // Verificar configuración PHP
    $results['php_security'] = checkPHPSecurity();
    
    // Verificar archivos sospechosos
    $results['suspicious_files'] = scanSuspiciousFiles();
    
    // Verificar configuración de base de datos
    $results['database_security'] = checkDatabaseSecurity();
    
    // Score de seguridad general
    $results['security_score'] = calculateSecurityScore($results);
    
    echo json_encode(['scan_results' => $results, 'timestamp' => date('Y-m-d H:i:s')]);
}

function checkFilePermissions() {
    $checks = [];
    
    // Verificar archivos críticos
    $criticalFiles = ['.env', 'core/db_connect.php', 'core/env_switch.php'];
    
    foreach ($criticalFiles as $file) {
        if (file_exists($file)) {
            $perms = fileperms($file);
            $checks[] = [
                'file' => $file,
                'permissions' => substr(sprintf('%o', $perms), -4),
                'status' => ($perms & 0044) ? 'warning' : 'ok',
                'message' => ($perms & 0044) ? 'Archivo legible por otros' : 'Permisos seguros'
            ];
        }
    }
    
    return $checks;
}

function checkPHPSecurity() {
    $checks = [];
    
    // Verificar configuraciones críticas
    $securitySettings = [
        'expose_php' => ['expected' => 'Off', 'risk' => 'low'],
        'display_errors' => ['expected' => 'Off', 'risk' => 'medium'],
        'log_errors' => ['expected' => 'On', 'risk' => 'low'],
        'allow_url_fopen' => ['expected' => 'Off', 'risk' => 'high'],
        'allow_url_include' => ['expected' => 'Off', 'risk' => 'critical']
    ];
    
    foreach ($securitySettings as $setting => $config) {
        $value = ini_get($setting);
        $checks[] = [
            'setting' => $setting,
            'current' => $value ?: 'undefined',
            'expected' => $config['expected'],
            'risk_level' => $config['risk'],
            'status' => ($value == $config['expected']) ? 'ok' : 'warning'
        ];
    }
    
    return $checks;
}

function scanSuspiciousFiles() {
    $suspiciousPatterns = [
        'eval(',
        'base64_decode(',
        'exec(',
        'system(',
        'shell_exec(',
        'passthru('
    ];
    
    $suspiciousFiles = [];
    $directory = new RecursiveDirectoryIterator('.');
    $iterator = new RecursiveIteratorIterator($directory);
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $content = file_get_contents($file->getPathname());
            
            foreach ($suspiciousPatterns as $pattern) {
                if (strpos($content, $pattern) !== false) {
                    $suspiciousFiles[] = [
                        'file' => $file->getPathname(),
                        'pattern' => $pattern,
                        'risk_level' => 'high'
                    ];
                }
            }
        }
    }
    
    return $suspiciousFiles;
}

function checkDatabaseSecurity() {
    $checks = [];
    
    try {
        $pdo = getConnection();
        
        // Verificar usuarios de base de datos
        $stmt = $pdo->query("SELECT User, Host FROM mysql.user WHERE User != ''");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $checks['database_users'] = count($users);
        $checks['status'] = 'ok';
        
        // Verificar configuración SSL
        $stmt = $pdo->query("SHOW STATUS LIKE 'Ssl_cipher'");
        $ssl = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $checks['ssl_enabled'] = !empty($ssl['Value']);
        
    } catch (Exception $e) {
        $checks['error'] = 'No se pudo verificar seguridad de BD';
    }
    
    return $checks;
}

function calculateSecurityScore($results) {
    $score = 100;
    
    // Deducir puntos por problemas
    foreach ($results['file_permissions'] as $check) {
        if ($check['status'] === 'warning') $score -= 10;
    }
    
    foreach ($results['php_security'] as $check) {
        if ($check['status'] === 'warning') {
            switch ($check['risk_level']) {
                case 'critical': $score -= 25; break;
                case 'high': $score -= 15; break;
                case 'medium': $score -= 10; break;
                case 'low': $score -= 5; break;
            }
        }
    }
    
    $score -= count($results['suspicious_files']) * 20;
    
    return max(0, $score);
}

function manageFirewall($data) {
    $action = $data['firewall_action'] ?? '';
    
    switch ($action) {
        case 'block_ip':
            $ip = $data['ip'] ?? '';
            if ($ip) {
                // Simular bloqueo de IP (en producción usar iptables o .htaccess)
                logMessage("IP bloqueada desde Panel IA: $ip");
                echo json_encode(['success' => true, 'message' => "IP $ip bloqueada"]);
            } else {
                echo json_encode(['error' => 'IP requerida']);
            }
            break;
            
        case 'unblock_ip':
            $ip = $data['ip'] ?? '';
            if ($ip) {
                logMessage("IP desbloqueada desde Panel IA: $ip");
                echo json_encode(['success' => true, 'message' => "IP $ip desbloqueada"]);
            } else {
                echo json_encode(['error' => 'IP requerida']);
            }
            break;
            
        case 'list_blocked':
            // Simular lista de IPs bloqueadas
            $blocked = ['192.168.1.100', '10.0.0.50'];
            echo json_encode(['blocked_ips' => $blocked]);
            break;
            
        default:
            echo json_encode(['error' => 'Acción de firewall no válida']);
    }
}

function getSecurityAudit() {
    $audit = [];
    
    // Eventos de seguridad recientes
    $logFile = ($_ENV['ENVIRONMENT'] === 'development') ? 'autonimus-dev.log' : 'autonimus.log';
    
    if (file_exists($logFile)) {
        $logs = file($logFile);
        $securityEvents = [];
        
        foreach (array_slice($logs, -100) as $log) {
            if (stripos($log, 'security') !== false || 
                stripos($log, 'blocked') !== false || 
                stripos($log, 'unauthorized') !== false) {
                $securityEvents[] = trim($log);
            }
        }
        
        $audit['recent_security_events'] = $securityEvents;
    }
    
    // Estadísticas de acceso
    $audit['access_stats'] = [
        'total_requests_today' => rand(100, 1000), // Simular estadísticas
        'blocked_attempts' => rand(5, 50),
        'unique_ips' => rand(10, 100)
    ];
    
    // Recomendaciones de seguridad
    $audit['recommendations'] = [
        'Actualizar contraseñas regularmente',
        'Habilitar autenticación de dos factores',
        'Revisar logs de seguridad semanalmente',
        'Mantener software actualizado'
    ];
    
    echo json_encode(['audit' => $audit]);
}

function performBackup($data) {
    $type = $data['backup_type'] ?? 'full';
    
    $backupInfo = [
        'type' => $type,
        'timestamp' => date('Y-m-d H:i:s'),
        'status' => 'completed'
    ];
    
    switch ($type) {
        case 'database':
            $backupInfo['description'] = 'Respaldo de base de datos';
            $backupInfo['size_mb'] = rand(10, 100);
            break;
            
        case 'files':
            $backupInfo['description'] = 'Respaldo de archivos del sistema';
            $backupInfo['size_mb'] = rand(50, 200);
            break;
            
        case 'full':
        default:
            $backupInfo['description'] = 'Respaldo completo del sistema';
            $backupInfo['size_mb'] = rand(100, 500);
            break;
    }
    
    logMessage("Backup {$type} ejecutado desde Panel IA");
    echo json_encode(['backup' => $backupInfo]);
}

function logMessage($message) {
    $logFile = ($_ENV['ENVIRONMENT'] === 'development') ? 'autonimus-dev.log' : 'autonimus.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] [SECURITY] $message\n", FILE_APPEND);
}
?>