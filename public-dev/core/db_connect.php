<?php
/**
 * Conexión MySQL inteligente (detecta entorno)
 */
$config = require __DIR__ . '/env_switch.php';
$db = $config['db'];

mysqli_report(MYSQLI_REPORT_OFF);
$mysqli = @new mysqli($db['host'], $db['user'], $db['pass'], $db['name']);

if ($mysqli->connect_error) {
    http_response_code(500);
    die("❌ Error de conexión MySQL en entorno [{$config['env']}]: " . $mysqli->connect_error);
} else {
    echo "✅ Conectado a MySQL ({$config['env']}) correctamente.<br>";
    echo "📊 Base de datos: {$db['name']}<br>";
    echo "🔗 Host: {$db['host']}<br>";
    echo "👤 Usuario: {$db['user']}<br>";
    echo "⏰ Hora del servidor: " . date('Y-m-d H:i:s') . "<br>";
    echo "🧩 <strong>ENTORNO DE DESARROLLO</strong>";
}

$mysqli->close();
?>