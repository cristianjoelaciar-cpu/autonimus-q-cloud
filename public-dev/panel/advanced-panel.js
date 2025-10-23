async function fetchJSON(url, opts = {}) {
  const r = await fetch(url, { headers: { "Accept": "application/json" }, ...opts });
  if (!r.ok) throw new Error(`HTTP ${r.status}`);
  return r.json();
}

const $ = (id) => document.getElementById(id);
const apiBase = location.pathname.includes('/panel/') ? '../core' : 'core';

let isConnected = true;
let currentSection = 'dashboard';

// Gestión de navegación
document.addEventListener('DOMContentLoaded', () => {
    // Event listeners para navegación
    document.querySelectorAll('.nav-item').forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            const section = item.dataset.section;
            if (section) {
                switchSection(section);
            }
        });
    });
    
    // Event listeners para comandos
    document.querySelectorAll('button[data-cmd]').forEach(btn => {
        btn.addEventListener('click', () => runCommand(btn.dataset.cmd));
    });
    
    // Inicializar
    refreshStatus();
    loadLogs();
    
    // Auto-refresh cada 30 segundos
    setInterval(refreshStatus, 30000);
    
    logMessage('[PANEL] Panel IA Avanzado iniciado correctamente');
});

function switchSection(sectionName) {
    // Ocultar todas las secciones
    document.querySelectorAll('.content-section').forEach(section => {
        section.style.display = 'none';
    });
    
    // Mostrar sección seleccionada
    const targetSection = $(`section-${sectionName}`);
    if (targetSection) {
        targetSection.style.display = 'block';
    }
    
    // Actualizar navegación activa
    document.querySelectorAll('.nav-item').forEach(item => {
        item.classList.remove('active');
    });
    
    document.querySelector(`[data-section="${sectionName}"]`)?.classList.add('active');
    
    // Actualizar título
    const titles = {
        dashboard: '🚀 Dashboard Autonimus-Q Cloud',
        analytics: '📈 Analytics del Sistema',
        users: '👥 Gestión de Usuarios',
        security: '🔒 Centro de Seguridad',
        logs: '📝 Sistema de Logs',
        backup: '💾 Sistema de Backups',
        settings: '⚙️ Configuración',
        commands: '⚡ Comandos del Sistema'
    };
    
    $('pageTitle').textContent = titles[sectionName] || 'Panel IA';
    currentSection = sectionName;
    
    // Cargar datos específicos de la sección
    loadSectionData(sectionName);
}

function loadSectionData(section) {
    switch (section) {
        case 'analytics':
            loadAnalytics();
            break;
        case 'users':
            loadUsers();
            break;
        case 'security':
            // Cargar datos de seguridad automáticamente
            break;
    }
}

// Funciones de estado del sistema
async function updateConnectionStatus(connected) {
    isConnected = connected;
    const status = $('connectionStatus');
    if (connected) {
        status.className = 'badge ok pulse';
        status.textContent = '🟢 Sistema Conectado';
    } else {
        status.className = 'badge err';
        status.textContent = '🔴 Sistema Desconectado';
    }
}

async function refreshStatus() {
    try {
        const data = await fetchJSON(`${apiBase}/status.php`);
        
        // Actualizar métricas del dashboard
        $('systemStatus').textContent = data.env || 'Online';
        
        const dbStatusElement = $('dbStatus');
        if (dbStatusElement) {
            dbStatusElement.textContent = data.db_ok ? 'Conectada' : 'Error';
        }
        
        updateConnectionStatus(true);
        logMessage(`[SYSTEM] Estado actualizado - Entorno: ${data.env}, DB: ${data.db_ok ? 'OK' : 'ERROR'}`);
        
    } catch (e) {
        updateConnectionStatus(false);
        logMessage(`[ERROR] No se pudo actualizar el estado: ${e.message}`);
    }
}

// Gestión de usuarios
async function loadUsers() {
    const token = $('token').value.trim();
    if (!token) {
        showAlert('Por favor ingresa el PANEL_TOKEN', 'warning');
        return;
    }
    
    try {
        const data = await fetchJSON(`${apiBase}/users.php?action=list`, {
            method: 'POST',
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ token })
        });
        
        const tbody = $('usersTableBody');
        if (data.users && data.users.length > 0) {
            tbody.innerHTML = data.users.map(user => `
                <tr>
                    <td>${user.username}</td>
                    <td>${user.email}</td>
                    <td><span class="status-badge ${user.role}">${user.role}</span></td>
                    <td><span class="status-badge ${user.status === 'active' ? 'online' : 'offline'}">${user.status}</span></td>
                    <td>${user.last_login || 'Nunca'}</td>
                    <td>
                        <button class="btn btn-sm btn-secondary" onclick="editUser(${user.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteUser(${user.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="6" style="text-align: center;">No hay usuarios registrados</td></tr>';
        }
        
        logMessage('[USERS] Lista de usuarios cargada correctamente');
        
    } catch (e) {
        showAlert(`Error cargando usuarios: ${e.message}`, 'error');
        logMessage(`[ERROR] Error cargando usuarios: ${e.message}`);
    }
}

// Analytics del sistema
async function loadAnalytics() {
    const token = $('token').value.trim();
    if (!token) {
        showAlert('Por favor ingresa el PANEL_TOKEN', 'warning');
        return;
    }
    
    try {
        const data = await fetchJSON(`${apiBase}/analytics.php?action=stats`, {
            method: 'POST',
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ token })
        });
        
        const analyticsContent = $('analyticsContent');
        if (data.stats) {
            analyticsContent.innerHTML = `
                <div class="metric-cards">
                    <div class="metric-card">
                        <div class="metric-header">
                            <span class="metric-title">Base de Datos</span>
                            <i class="fas fa-database metric-icon"></i>
                        </div>
                        <div class="metric-value">${data.stats.database.total_tables}</div>
                        <div class="metric-change">
                            <span>Tablas • ${data.stats.database.total_size_mb} MB</span>
                        </div>
                    </div>
                    
                    <div class="metric-card">
                        <div class="metric-header">
                            <span class="metric-title">Memoria PHP</span>
                            <i class="fas fa-memory metric-icon"></i>
                        </div>
                        <div class="metric-value">${data.stats.system.memory_usage_mb}</div>
                        <div class="metric-change">
                            <span>MB • Peak: ${data.stats.system.memory_peak_mb} MB</span>
                        </div>
                    </div>
                    
                    <div class="metric-card">
                        <div class="metric-header">
                            <span class="metric-title">Logs</span>
                            <i class="fas fa-file-alt metric-icon"></i>
                        </div>
                        <div class="metric-value">${data.stats.logs.log_lines}</div>
                        <div class="metric-change">
                            <span>Líneas • ${data.stats.logs.log_file_size_kb} KB</span>
                        </div>
                    </div>
                </div>
                
                <div class="chart-container">
                    <div class="chart-placeholder">
                        📊 Gráficos de rendimiento
                        <br><small>PHP: ${data.stats.system.php_version} • ${data.stats.system.environment}</small>
                    </div>
                </div>
            `;
        }
        
        logMessage('[ANALYTICS] Datos de analytics cargados correctamente');
        
    } catch (e) {
        showAlert(`Error cargando analytics: ${e.message}`, 'error');
        logMessage(`[ERROR] Error cargando analytics: ${e.message}`);
    }
}

// Sistema de seguridad
async function performSecurityScan() {
    const token = $('token').value.trim();
    if (!token) {
        showAlert('Por favor ingresa el PANEL_TOKEN', 'warning');
        return;
    }
    
    showAlert('Iniciando escaneo de seguridad...', 'info');
    
    try {
        const data = await fetchJSON(`${apiBase}/security.php?action=scan`, {
            method: 'POST',
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ token })
        });
        
        if (data.scan_results) {
            $('securityScore').textContent = data.scan_results.security_score;
            
            const securityResults = $('securityResults');
            const securityContent = $('securityContent');
            
            securityContent.innerHTML = `
                <div class="alert info">
                    <i class="fas fa-info-circle"></i>
                    Escaneo completado el ${data.timestamp}
                </div>
                
                <div class="metric-cards">
                    <div class="metric-card">
                        <div class="metric-header">
                            <span class="metric-title">Permisos de Archivos</span>
                            <i class="fas fa-lock metric-icon"></i>
                        </div>
                        <div class="metric-value">${data.scan_results.file_permissions.length}</div>
                        <div class="metric-change">Archivos verificados</div>
                    </div>
                    
                    <div class="metric-card">
                        <div class="metric-header">
                            <span class="metric-title">Configuración PHP</span>
                            <i class="fas fa-cog metric-icon"></i>
                        </div>
                        <div class="metric-value">${data.scan_results.php_security.length}</div>
                        <div class="metric-change">Configuraciones</div>
                    </div>
                </div>
            `;
            
            securityResults.style.display = 'block';
            showAlert(`Escaneo completado. Score: ${data.scan_results.security_score}/100`, 'success');
        }
        
        logMessage('[SECURITY] Escaneo de seguridad completado');
        
    } catch (e) {
        showAlert(`Error en escaneo de seguridad: ${e.message}`, 'error');
        logMessage(`[ERROR] Error en escaneo: ${e.message}`);
    }
}

// Sistema de backups
async function performBackup() {
    const token = $('token').value.trim();
    const backupType = $('backupType').value;
    
    if (!token) {
        showAlert('Por favor ingresa el PANEL_TOKEN', 'warning');
        return;
    }
    
    showAlert(`Iniciando backup ${backupType}...`, 'info');
    
    try {
        const data = await fetchJSON(`${apiBase}/security.php?action=backup`, {
            method: 'POST',
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ token, backup_type: backupType })
        });
        
        if (data.backup) {
            const backupResults = $('backupResults');
            const backupContent = $('backupContent');
            
            backupContent.innerHTML = `
                <div class="alert success">
                    <i class="fas fa-check-circle"></i>
                    ${data.backup.description} completado exitosamente
                </div>
                
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Tamaño</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="status-badge online">${data.backup.type}</span></td>
                                <td>${data.backup.size_mb} MB</td>
                                <td><span class="status-badge online">${data.backup.status}</span></td>
                                <td>${data.backup.timestamp}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            `;
            
            backupResults.style.display = 'block';
            showAlert('Backup completado exitosamente', 'success');
        }
        
        logMessage(`[BACKUP] Backup ${backupType} completado exitosamente`);
        
    } catch (e) {
        showAlert(`Error en backup: ${e.message}`, 'error');
        logMessage(`[ERROR] Error en backup: ${e.message}`);
    }
}

// Gestión de logs
async function loadLogs() {
    try {
        const data = await fetchJSON(`${apiBase}/log.php?action=read&limit=200`);
        $('logBox').textContent = data.lines.join('\n') || 'No hay logs disponibles.';
        updateConnectionStatus(true);
    } catch (e) {
        $('logBox').textContent = 'No se pudieron cargar los logs: ' + e.message;
        updateConnectionStatus(false);
    }
}

async function writeLog() {
    const token = $('token').value.trim();
    if (!token) {
        showAlert('Por favor ingresa el PANEL_TOKEN', 'warning');
        return;
    }
    
    try {
        const data = await fetchJSON(`${apiBase}/log.php?action=write`, {
            method: 'POST',
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ 
                token, 
                message: `Log de prueba desde Panel IA Avanzado - ${new Date().toLocaleString()}` 
            })
        });
        await loadLogs();
        showAlert('Log de prueba escrito correctamente', 'success');
    } catch (e) {
        showAlert(`Error escribiendo log: ${e.message}`, 'error');
    }
}

function clearLogs() {
    $('logBox').textContent = 'Logs limpiados desde el panel avanzado...';
    showAlert('Logs limpiados', 'info');
}

// Comandos del sistema
async function runCommand(cmd) {
    const token = $('token').value.trim();
    if (!token) {
        showAlert('Por favor ingresa el PANEL_TOKEN', 'warning');
        return;
    }
    
    logMessage(`[COMMAND] Ejecutando: ${cmd}`);
    
    try {
        const res = await fetchJSON(`${apiBase}/commands.php`, {
            method: 'POST',
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ token, command: cmd })
        });
        
        const output = $('commandOutput');
        output.textContent = `Comando: ${cmd}\n\n${res.message || 'Ejecutado correctamente'}`;
        
        if (res.data) {
            output.textContent += `\n\nDatos:\n${JSON.stringify(res.data, null, 2)}`;
        }
        
        await loadLogs();
        showAlert(`Comando '${cmd}' ejecutado correctamente`, 'success');
        
    } catch (e) {
        showAlert(`Error ejecutando comando '${cmd}': ${e.message}`, 'error');
        logMessage(`[ERROR] Comando '${cmd}' falló: ${e.message}`);
    }
}

// Utilidades
function logMessage(message) {
    const timestamp = new Date().toLocaleTimeString();
    console.log(`[${timestamp}] ${message}`);
}

function showAlert(message, type = 'info') {
    // Crear elemento de alerta
    const alert = document.createElement('div');
    alert.className = `alert ${type}`;
    
    const icon = {
        info: 'fas fa-info-circle',
        success: 'fas fa-check-circle',
        warning: 'fas fa-exclamation-triangle',
        error: 'fas fa-exclamation-circle'
    };
    
    alert.innerHTML = `
        <i class="${icon[type]}"></i>
        ${message}
    `;
    
    // Insertar al inicio del contenido principal
    const mainContent = document.querySelector('.main-content');
    mainContent.insertBefore(alert, mainContent.firstChild);
    
    // Auto-remover después de 5 segundos
    setTimeout(() => {
        alert.remove();
    }, 5000);
}

// Funciones placeholder para modales
function showCreateUserModal() {
    showAlert('Modal de crear usuario - Función en desarrollo', 'info');
}

function showFirewallModal() {
    showAlert('Modal de firewall - Función en desarrollo', 'info');
}

function editUser(id) {
    showAlert(`Editando usuario ID: ${id}`, 'info');
}

function deleteUser(id) {
    if (confirm('¿Estás seguro de eliminar este usuario?')) {
        showAlert(`Usuario ID: ${id} marcado para eliminación`, 'warning');
    }
}

function getSecurityAudit() {
    showAlert('Generando auditoría de seguridad...', 'info');
}

function listBackups() {
    showAlert('Listando backups disponibles...', 'info');
}