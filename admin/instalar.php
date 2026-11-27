<?php
/**
 * Crea el PRIMER usuario administrador. Solo funciona si no existe ninguno.
 * Después de usarlo, BORRA este archivo del servidor.
 */
declare(strict_types=1);
require __DIR__ . '/_auth.php';

$pdo = db();

// Si las tablas no existen todavía, se crean desde database/safepixel.sql
if (!$pdo->query("SHOW TABLES LIKE 'usuarios'")->fetchColumn()) {
    $sqlFile = __DIR__ . '/../database/safepixel.sql';
    if (!is_file($sqlFile)) {
        http_response_code(500);
        exit('Faltan las tablas y no se encontró database/safepixel.sql');
    }
    $sql = preg_replace('/^\s*--.*$/m', '', (string)file_get_contents($sqlFile));
    foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
        $pdo->exec($stmt);
    }
}

$hayUsuarios = (int)$pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn() > 0;
$msg = '';
$error = '';

if (!$hayUsuarios && $_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $usuario = trim((string)($_POST['usuario'] ?? ''));
    $pass    = (string)($_POST['password'] ?? '');
    $pass2   = (string)($_POST['password2'] ?? '');
    if (!preg_match('/^[a-zA-Z0-9_.-]{3,50}$/', $usuario)) {
        $error = 'El usuario debe tener de 3 a 50 letras, números, punto o guion.';
    } elseif (strlen($pass) < 10) {
        $error = 'La contraseña debe tener al menos 10 caracteres.';
    } elseif ($pass !== $pass2) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        $pdo->prepare('INSERT INTO usuarios (usuario, password_hash) VALUES (?, ?)')
            ->execute([$usuario, password_hash($pass, PASSWORD_DEFAULT)]);
        $hayUsuarios = true;
        $msg = 'Administrador creado. Ahora BORRA el archivo admin/instalar.php del servidor y entra al panel.';
    }
}
?>
<!doctype html>
<html lang="es-MX">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Instalar panel · Safe Pixel</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body class="login-body">
  <div class="login-card">
    <div class="brand"><span class="brand-mark"><i></i><i></i><i></i><i></i></span>Safe<b>Pixel</b></div>
    <h1>Crear administrador</h1>
    <?php if ($msg): ?>
      <p class="ok"><?= e($msg) ?></p><a class="btn" href="login.php">Ir al panel</a>
    <?php elseif ($hayUsuarios): ?>
      <p class="alert">Ya existe un administrador. Borra este archivo del servidor.</p>
    <?php else: ?>
      <?php if ($error): ?><p class="alert"><?= e($error) ?></p><?php endif; ?>
      <form method="post">
        <?= csrf_field() ?>
        <label>Usuario<input name="usuario" required autocomplete="username"></label>
        <label>Contraseña (mín. 10 caracteres)<input name="password" type="password" required minlength="10" autocomplete="new-password"></label>
        <label>Repite la contraseña<input name="password2" type="password" required minlength="10" autocomplete="new-password"></label>
        <button class="btn" type="submit">Crear</button>
      </form>
    <?php endif; ?>
  </div>
</body>
</html>
