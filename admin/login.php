<?php
declare(strict_types=1);
require __DIR__ . '/_auth.php';

$error = '';
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $pdo = db();

    // Máximo 5 intentos fallidos por IP cada 15 minutos
    $q = $pdo->prepare('SELECT COUNT(*) FROM login_intentos WHERE ip = ? AND creado_en > (NOW() - INTERVAL 15 MINUTE)');
    $q->execute([$ip]);
    if ((int)$q->fetchColumn() >= 5) {
        $error = 'Demasiados intentos. Espera 15 minutos.';
    } else {
        $usuario = trim((string)($_POST['usuario'] ?? ''));
        $pass    = (string)($_POST['password'] ?? '');
        $st = $pdo->prepare('SELECT id, usuario, password_hash FROM usuarios WHERE usuario = ?');
        $st->execute([$usuario]);
        $u = $st->fetch();

        if ($u && password_verify($pass, $u['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['uid'] = (int)$u['id'];
            $_SESSION['usuario'] = $u['usuario'];
            $_SESSION['ultimo'] = time();
            $pdo->prepare('UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?')->execute([$u['id']]);
            $pdo->prepare('DELETE FROM login_intentos WHERE ip = ?')->execute([$ip]);
            if (password_needs_rehash($u['password_hash'], PASSWORD_DEFAULT)) {
                $pdo->prepare('UPDATE usuarios SET password_hash = ? WHERE id = ?')
                    ->execute([password_hash($pass, PASSWORD_DEFAULT), $u['id']]);
            }
            header('Location: index.php');
            exit;
        }
        $pdo->prepare('INSERT INTO login_intentos (ip) VALUES (?)')->execute([$ip]);
        $error = 'Usuario o contraseña incorrectos.';
    }
}
?>
<!doctype html>
<html lang="es-MX">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Panel Safe Pixel</title>
  <link rel="icon" href="../assets/img/favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="admin.css">
</head>
<body class="login-body">
  <form method="post" class="login-card" autocomplete="on">
    <div class="brand"><span class="brand-mark"><i></i><i></i><i></i><i></i></span>Safe<b>Pixel</b></div>
    <h1>Panel privado</h1>
    <?php if ($error): ?><p class="alert"><?= e($error) ?></p><?php endif; ?>
    <?= csrf_field() ?>
    <label>Usuario<input name="usuario" required autocomplete="username" autofocus></label>
    <label>Contraseña<input name="password" type="password" required autocomplete="current-password"></label>
    <button class="btn" type="submit">Entrar</button>
  </form>
</body>
</html>
