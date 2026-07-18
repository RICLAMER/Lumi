<?php

declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: index.php');
    exit;
}

$provided = (string) ($_POST['_token'] ?? '');
if (hash_equals(csrf_token(), $provided)) {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], '', $params['secure'], true);
    }
    session_destroy();
}

header('Location: index.php');
exit;
