<?php
/**
 * SAFE PIXEL — Recibe el formulario de contacto, lo guarda en MySQL
 * y envía un aviso por correo. Responde en JSON.
 */
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

function responder(int $code, array $data): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(405, ['ok' => false, 'error' => 'Método no permitido']);
}

// Anti-spam: campo trampa que solo llenan los bots
if (!empty($_POST['website'])) {
    responder(200, ['ok' => true]);
}

$configFile = __DIR__ . '/config.php';
if (!is_file($configFile)) {
    responder(500, ['ok' => false, 'error' => 'Falta config.php']);
}
$config = require $configFile;

// Limpieza y validación
$campo = fn(string $k, int $max) => mb_substr(trim((string)($_POST[$k] ?? '')), 0, $max);
$nombre   = $campo('nombre', 100);
$telefono = preg_replace('/[^0-9+ ]/', '', $campo('telefono', 20));
$email    = $campo('email', 150);
$servicio = $campo('servicio', 60);
$mensaje  = $campo('mensaje', 2000);

$errores = [];
if ($nombre === '')   $errores[] = 'nombre';
if (strlen(preg_replace('/\D/', '', $telefono)) < 10) $errores[] = 'telefono';
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = 'email';
if ($servicio === '') $errores[] = 'servicio';
if ($errores) {
    responder(422, ['ok' => false, 'error' => 'Datos inválidos', 'campos' => $errores]);
}

$ip     = $_SERVER['REMOTE_ADDR'] ?? null;
$origen = mb_substr((string)($_SERVER['HTTP_REFERER'] ?? ''), 0, 60);

try {
    $pdo = new PDO(
        "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4",
        $config['db_user'],
        $config['db_pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => false]
    );

    // Límite: máximo 5 envíos por IP cada hora
    $q = $pdo->prepare('SELECT COUNT(*) FROM prospectos WHERE ip = ? AND creado_en > (NOW() - INTERVAL 1 HOUR)');
    $q->execute([$ip]);
    if ((int)$q->fetchColumn() >= 5) {
        responder(429, ['ok' => false, 'error' => 'Demasiados envíos, intenta más tarde']);
    }

    $stmt = $pdo->prepare(
        'INSERT INTO prospectos (nombre, telefono, email, servicio, mensaje, origen, ip)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([$nombre, $telefono, $email ?: null, $servicio, $mensaje ?: null, $origen ?: null, $ip]);
} catch (Throwable $e) {
    error_log('SafePixel contacto: ' . $e->getMessage());
    responder(500, ['ok' => false, 'error' => 'No se pudo guardar']);
}

// Aviso por correo (no detiene la respuesta si falla)
if (!empty($config['notify_email'])) {
    $asunto = '=?UTF-8?B?' . base64_encode("Nuevo prospecto: $nombre ($servicio)") . '?=';
    $cuerpo = "Nombre: $nombre\nWhatsApp: $telefono\nCorreo: $email\nServicio: $servicio\n\nMensaje:\n$mensaje\n";
    $headers = "From: Safe Pixel <{$config['from_email']}>\r\nContent-Type: text/plain; charset=UTF-8";
    if ($email !== '') $headers .= "\r\nReply-To: $email";
    @mail($config['notify_email'], $asunto, $cuerpo, $headers);
}

responder(200, ['ok' => true]);
