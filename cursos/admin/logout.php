<?php
declare(strict_types=1);
require __DIR__ . '/../lib/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();
    auditar('logout');
    $_SESSION = [];
    session_destroy();
}
redirecionar('login.php');
