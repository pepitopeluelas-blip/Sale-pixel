<?php
/** Sesión, protección CSRF y utilidades del panel privado. */
declare(strict_types=1);
require __DIR__ . '/../api/db.php';

header('X-Robots-Tag: noindex, nofollow');
header('X-Frame-Options: DENY');

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/admin/x')), '/') . '/',
    'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Strict',
]);
session_name('sp_admin');
session_start();

const SESION_MAX_INACTIVA = 7200; // 2 horas

function e(?string $s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

function check_csrf(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', (string)($_POST['csrf'] ?? ''))) {
        http_response_code(403);
        exit('Solicitud no válida. Recarga la página.');
    }
}

function require_login(): void {
    $ok = !empty($_SESSION['uid'])
        && (time() - ($_SESSION['ultimo'] ?? 0)) < SESION_MAX_INACTIVA;
    if (!$ok) {
        session_unset();
        header('Location: login.php');
        exit;
    }
    $_SESSION['ultimo'] = time();
}

const ESTATUS = ['nuevo', 'contactado', 'cotizado', 'ganado', 'perdido'];
