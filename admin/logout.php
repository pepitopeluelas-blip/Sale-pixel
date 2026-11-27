<?php
declare(strict_types=1);
require __DIR__ . '/_auth.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $_SESSION = [];
    session_destroy();
}
header('Location: login.php');
