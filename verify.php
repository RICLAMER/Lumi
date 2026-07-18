<?php

declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

$token = strtolower(trim((string) ($_GET['token'] ?? '')));
if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
    header('Location: index.php?verification=invalid');
    exit;
}

try {
    $statement = db()->prepare(
        'SELECT id, verification_expires_at
         FROM users WHERE verification_token_hash = :token_hash LIMIT 1'
    );
    $statement->execute(['token_hash' => hash('sha256', $token)]);
    $user = $statement->fetch();

    if (!$user || strtotime((string) $user['verification_expires_at']) < time()) {
        header('Location: index.php?verification=expired');
        exit;
    }

    $statement = db()->prepare(
        'UPDATE users
         SET verified_at = NOW(), verification_token_hash = NULL, verification_expires_at = NULL
         WHERE id = :id'
    );
    $statement->execute(['id' => $user['id']]);

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['flash'] = 'Cadastro confirmado. A Lumi já estava esperando por você!';

    header('Location: app.php');
    exit;
} catch (Throwable $exception) {
    log_event('verification_failed', ['exception' => $exception::class]);
    header('Location: index.php?verification=error');
    exit;
}
