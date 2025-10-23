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

$action = $_GET['action'] ?? 'list';

try {
    $pdo = getConnection();
    
    switch ($action) {
        case 'list':
            listUsers();
            break;
        case 'create':
            createUser($input);
            break;
        case 'update':
            updateUser($input);
            break;
        case 'delete':
            deleteUser($input);
            break;
        case 'stats':
            getUserStats();
            break;
        default:
            echo json_encode(['error' => 'Acción no válida']);
    }
} catch (Exception $e) {
    logMessage("Error en users.php: " . $e->getMessage());
    echo json_encode(['error' => 'Error interno del servidor']);
}

function listUsers() {
    global $pdo;
    
    $stmt = $pdo->query("
        SELECT id, username, email, role, status, created_at, last_login 
        FROM users 
        ORDER BY created_at DESC 
        LIMIT 100
    ");
    
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['users' => $users]);
}

function createUser($data) {
    global $pdo;
    
    $username = $data['username'] ?? '';
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    $role = $data['role'] ?? 'user';
    
    if (!$username || !$email || !$password) {
        echo json_encode(['error' => 'Faltan campos requeridos']);
        return;
    }
    
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password_hash, role, status, created_at) 
        VALUES (?, ?, ?, ?, 'active', NOW())
    ");
    
    $stmt->execute([$username, $email, $hashedPassword, $role]);
    
    logMessage("Usuario creado: $username ($email)");
    echo json_encode(['success' => true, 'message' => 'Usuario creado correctamente']);
}

function updateUser($data) {
    global $pdo;
    
    $userId = $data['id'] ?? 0;
    $username = $data['username'] ?? '';
    $email = $data['email'] ?? '';
    $role = $data['role'] ?? 'user';
    $status = $data['status'] ?? 'active';
    
    if (!$userId) {
        echo json_encode(['error' => 'ID de usuario requerido']);
        return;
    }
    
    $stmt = $pdo->prepare("
        UPDATE users 
        SET username = ?, email = ?, role = ?, status = ?, updated_at = NOW() 
        WHERE id = ?
    ");
    
    $stmt->execute([$username, $email, $role, $status, $userId]);
    
    logMessage("Usuario actualizado: ID $userId");
    echo json_encode(['success' => true, 'message' => 'Usuario actualizado correctamente']);
}

function deleteUser($data) {
    global $pdo;
    
    $userId = $data['id'] ?? 0;
    
    if (!$userId) {
        echo json_encode(['error' => 'ID de usuario requerido']);
        return;
    }
    
    $stmt = $pdo->prepare("UPDATE users SET status = 'deleted', updated_at = NOW() WHERE id = ?");
    $stmt->execute([$userId]);
    
    logMessage("Usuario eliminado: ID $userId");
    echo json_encode(['success' => true, 'message' => 'Usuario eliminado correctamente']);
}

function getUserStats() {
    global $pdo;
    
    $stats = [];
    
    // Total usuarios
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE status != 'deleted'");
    $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Usuarios activos
    $stmt = $pdo->query("SELECT COUNT(*) as active FROM users WHERE status = 'active'");
    $stats['active_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['active'];
    
    // Usuarios por rol
    $stmt = $pdo->query("SELECT role, COUNT(*) as count FROM users WHERE status != 'deleted' GROUP BY role");
    $stats['users_by_role'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Registros recientes (últimos 7 días)
    $stmt = $pdo->query("SELECT COUNT(*) as recent FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $stats['recent_registrations'] = $stmt->fetch(PDO::FETCH_ASSOC)['recent'];
    
    echo json_encode(['stats' => $stats]);
}

function logMessage($message) {
    $logFile = ($_ENV['ENVIRONMENT'] === 'development') ? 'autonimus-dev.log' : 'autonimus.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}
?>