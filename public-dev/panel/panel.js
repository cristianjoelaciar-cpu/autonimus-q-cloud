async function fetchJSON(url, opts = {}) {
  const r = await fetch(url, { headers: { "Accept": "application/json" }, ...opts });
  if (!r.ok) throw new Error(`HTTP ${r.status}`);
  return r.json();
}

const $ = (id) => document.getElementById(id);
const apiBase = location.pathname.includes('/panel/') ? '../core' : 'core';

let isConnected = true;

async function updateConnectionStatus(connected) {
  isConnected = connected;
  const status = $('connectionStatus');
  if (connected) {
    status.className = 'badge dev pulse';
    status.textContent = '🟡 Dev Connected';
  } else {
    status.className = 'badge err';
    status.textContent = '🔴 Dev Offline';
  }
}

async function refreshStatus() {
  try {
    const data = await fetchJSON(`${apiBase}/status.php`);
    
    $('env').textContent = (data.env || 'unknown') + ' (DEV)';
    $('domain').textContent = data.domain || '—';
    $('serverTime').textContent = data.server_time || '—';
    
    const badge = $('dbStatus').querySelector('.badge') || document.createElement('span');
    badge.className = 'badge ' + (data.db_ok ? 'ok' : 'err');
    badge.textContent = data.db_ok ? '✅ DB Dev OK' : '❌ DB Error';
    $('dbStatus').innerHTML = '';
    $('dbStatus').appendChild(badge);
    
    updateConnectionStatus(true);
    
    // Mostrar info adicional en logs
    logMessage(`[DEV SYSTEM] Estado actualizado - Entorno: ${data.env}, DB: ${data.db_ok ? 'OK' : 'ERROR'}`);
    
  } catch (e) {
    $('dbStatus').innerHTML = '<span class="badge err">❌ Error DEV</span>';
    updateConnectionStatus(false);
    logMessage(`[DEV ERROR] No se pudo actualizar el estado: ${e.message}`);
  }
}

async function loadLogs() {
  try {
    const data = await fetchJSON(`${apiBase}/log.php?action=read&limit=200`);
    $('logBox').textContent = data.lines.join('\n') || 'No hay logs de desarrollo disponibles.';
    updateConnectionStatus(true);
  } catch (e) {
    $('logBox').textContent = 'No se pudieron cargar los logs DEV: ' + e.message;
    updateConnectionStatus(false);
  }
}

async function writeLog() {
  const token = $('token').value.trim();
  if (!token) {
    alert('Por favor ingresa el PANEL_TOKEN para el entorno DEV');
    return;
  }
  
  try {
    const data = await fetchJSON(`${apiBase}/log.php?action=write`, {
      method: 'POST',
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ 
        token, 
        message: `Log de prueba DEV desde Panel IA - Usuario: ${new Date().toLocaleString()}` 
      })
    });
    await loadLogs();
    logMessage('[DEV SUCCESS] Log de prueba escrito en entorno de desarrollo');
  } catch (e) {
    alert('No se pudo escribir el log DEV. Verifica el token.');
    logMessage(`[DEV ERROR] Error escribiendo log: ${e.message}`);
  }
}

async function runCommand(cmd) {
  const token = $('token').value.trim();
  if (!token) {
    alert('Por favor ingresa el PANEL_TOKEN para ejecutar comandos DEV');
    return;
  }
  
  logMessage(`[DEV COMMAND] Ejecutando en desarrollo: ${cmd}`);
  
  try {
    const res = await fetchJSON(`${apiBase}/commands.php`, {
      method: 'POST',
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ token, command: cmd })
    });
    
    await loadLogs();
    logMessage(`[DEV SUCCESS] Comando '${cmd}' ejecutado en DEV: ${res.message || 'OK'}`);
    
    if (res.data) {
      logMessage(`[DEV DATA] ${JSON.stringify(res.data, null, 2)}`);
    }
    
  } catch (e) {
    alert(`Error ejecutando comando '${cmd}' en DEV. Verifica el token.`);
    logMessage(`[DEV ERROR] Comando '${cmd}' falló: ${e.message}`);
  }
}

function logMessage(message) {
  const timestamp = new Date().toLocaleTimeString();
  const logBox = $('logBox');
  const currentContent = logBox.textContent;
  logBox.textContent = `[${timestamp}] ${message}\n` + currentContent;
}

function clearLogs() {
  $('logBox').textContent = 'Logs DEV limpiados desde el panel...';
}

// Event Listeners
window.addEventListener('DOMContentLoaded', () => {
  $('btnPing').addEventListener('click', refreshStatus);
  $('btnLogs').addEventListener('click', loadLogs);
  $('btnWrite').addEventListener('click', writeLog);
  $('btnClear').addEventListener('click', clearLogs);
  
  $('btnSystemInfo').addEventListener('click', () => runCommand('system_info'));
  
  // Comandos con botones
  document.querySelectorAll('button[data-cmd]').forEach(b => {
    b.addEventListener('click', () => runCommand(b.dataset.cmd));
  });
  
  // Cargar estado inicial
  refreshStatus();
  loadLogs();
  
  // Auto-refresh cada 30 segundos
  setInterval(refreshStatus, 30000);
  
  logMessage('[DEV PANEL] Panel IA de desarrollo iniciado correctamente');
});