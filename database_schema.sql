-- Autonimus-Q Cloud Database Schema
-- Crear base de datos si no existe
CREATE DATABASE IF NOT EXISTS u196678264_autonimus_core;
USE u196678264_autonimus_core;

-- Tabla de usuarios del sistema
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'manager', 'user', 'readonly') DEFAULT 'user',
    status ENUM('active', 'inactive', 'suspended', 'deleted') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    login_attempts INT DEFAULT 0,
    locked_until TIMESTAMP NULL,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- Tabla de logs del sistema
CREATE TABLE IF NOT EXISTS system_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    level ENUM('debug', 'info', 'warning', 'error', 'critical') DEFAULT 'info',
    message TEXT NOT NULL,
    context JSON,
    user_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_level (level),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Tabla de sesiones
CREATE TABLE IF NOT EXISTS user_sessions (
    id VARCHAR(128) PRIMARY KEY,
    user_id INT NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    payload TEXT NOT NULL,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_last_activity (last_activity),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabla de configuraciones del sistema
CREATE TABLE IF NOT EXISTS system_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    config_key VARCHAR(100) UNIQUE NOT NULL,
    config_value TEXT,
    config_type ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    is_encrypted BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_config_key (config_key)
);

-- Tabla de métricas del sistema
CREATE TABLE IF NOT EXISTS system_metrics (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    metric_name VARCHAR(100) NOT NULL,
    metric_value DECIMAL(15,4) NOT NULL,
    metric_unit VARCHAR(20),
    tags JSON,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_metric_name (metric_name),
    INDEX idx_recorded_at (recorded_at)
);

-- Tabla de notificaciones
CREATE TABLE IF NOT EXISTS notifications (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
    priority ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    is_read BOOLEAN DEFAULT FALSE,
    data JSON,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read),
    INDEX idx_type (type),
    INDEX idx_priority (priority),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabla de eventos de seguridad
CREATE TABLE IF NOT EXISTS security_events (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    event_type VARCHAR(100) NOT NULL,
    severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    source_ip VARCHAR(45),
    user_id INT,
    description TEXT NOT NULL,
    data JSON,
    is_resolved BOOLEAN DEFAULT FALSE,
    resolved_by INT,
    resolved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_event_type (event_type),
    INDEX idx_severity (severity),
    INDEX idx_source_ip (source_ip),
    INDEX idx_user_id (user_id),
    INDEX idx_is_resolved (is_resolved),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Tabla de backups
CREATE TABLE IF NOT EXISTS backups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    backup_name VARCHAR(255) NOT NULL,
    backup_type ENUM('full', 'database', 'files', 'incremental') DEFAULT 'full',
    file_path VARCHAR(500),
    file_size BIGINT,
    compression_type VARCHAR(20),
    checksum VARCHAR(64),
    status ENUM('pending', 'running', 'completed', 'failed') DEFAULT 'pending',
    started_by INT,
    error_message TEXT,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    INDEX idx_backup_type (backup_type),
    INDEX idx_status (status),
    INDEX idx_started_at (started_at),
    FOREIGN KEY (started_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Tabla de tareas programadas
CREATE TABLE IF NOT EXISTS scheduled_tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_name VARCHAR(100) NOT NULL,
    task_type VARCHAR(50) NOT NULL,
    command TEXT NOT NULL,
    cron_expression VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    last_run TIMESTAMP NULL,
    next_run TIMESTAMP NULL,
    run_count INT DEFAULT 0,
    success_count INT DEFAULT 0,
    failure_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_task_type (task_type),
    INDEX idx_is_active (is_active),
    INDEX idx_next_run (next_run)
);

-- Insertar usuario administrador por defecto
INSERT IGNORE INTO users (username, email, password_hash, role, status) VALUES 
('admin', 'admin@autonimuscorp.online', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active');

-- Insertar configuraciones por defecto
INSERT IGNORE INTO system_config (config_key, config_value, config_type, description) VALUES 
('site_name', 'Autonimus-Q Cloud', 'string', 'Nombre del sitio'),
('maintenance_mode', 'false', 'boolean', 'Modo de mantenimiento'),
('max_login_attempts', '5', 'integer', 'Máximo de intentos de login'),
('session_timeout', '3600', 'integer', 'Timeout de sesión en segundos'),
('backup_retention_days', '30', 'integer', 'Días de retención de backups'),
('security_scan_interval', '24', 'integer', 'Intervalo de escaneo de seguridad en horas');

-- Vista para estadísticas rápidas
CREATE OR REPLACE VIEW system_stats AS
SELECT 
    (SELECT COUNT(*) FROM users WHERE status = 'active') as active_users,
    (SELECT COUNT(*) FROM system_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)) as logs_24h,
    (SELECT COUNT(*) FROM security_events WHERE is_resolved = FALSE) as open_security_events,
    (SELECT COUNT(*) FROM notifications WHERE is_read = FALSE) as unread_notifications,
    (SELECT COUNT(*) FROM backups WHERE status = 'completed' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) as backups_7d;

-- Procedimiento para limpiar logs antiguos
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS CleanOldLogs()
BEGIN
    DELETE FROM system_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);
    DELETE FROM system_metrics WHERE recorded_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
    DELETE FROM user_sessions WHERE last_activity < DATE_SUB(NOW(), INTERVAL 7 DAY);
END //
DELIMITER ;

-- Trigger para actualizar métricas automáticamente
DELIMITER //
CREATE TRIGGER IF NOT EXISTS user_login_metrics 
AFTER UPDATE ON users 
FOR EACH ROW
BEGIN
    IF NEW.last_login != OLD.last_login THEN
        INSERT INTO system_metrics (metric_name, metric_value, metric_unit, tags)
        VALUES ('user_login', 1, 'count', JSON_OBJECT('user_id', NEW.id, 'username', NEW.username));
    END IF;
END //
DELIMITER ;