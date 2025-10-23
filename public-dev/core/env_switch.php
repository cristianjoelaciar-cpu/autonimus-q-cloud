<?php
/**
 * Autonimus-Q Cloud | Configuración de Entornos Inteligente
 * Detecta automáticamente si se ejecuta en producción o desarrollo
 * y carga el archivo .env correcto.
 */

// Detectar dominio actual
$domain = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Ruta base según entorno
if (str_contains($domain, 'dev.')) {
    $envPath = __DIR__ . '/../../dev/.env';
} else {
    $envPath = __DIR__ . '/../../.env';
}

// Validar existencia del archivo
if (!file_exists($envPath)) {
    http_response_code(500);
    die("❌ Error: No se encontró el archivo de entorno ($envPath).");
}

// Cargar variables desde .env
$env = parse_ini_file($envPath);

// Retornar arreglo con las variables cargadas
return [
    'env'   => $env['ENVIRONMENT'] ?? 'unknown',
    'db'    => [
        'host' => $env['DB_HOST'] ?? 'localhost',
        'name' => $env['DB_NAME'] ?? '',
        'user' => $env['DB_USER'] ?? '',
        'pass' => $env['DB_PASS'] ?? '',
    ]
];