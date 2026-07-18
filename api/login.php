<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

require_post();
require_csrf();

$attempts = $_SESSION['login_attempts'] ?? ['count' => 0, 'started_at' => time()];
if ((int) $attempts['started_at'] < time() - 900) {
    $attempts = ['count' => 0, 'started_at' => time()];
}
if ((int) $attempts['count'] >= 8) {
    json_response([
        'ok' => false,
        'message' => 'Muitas tentativas. Aguarde 15 minutos antes de tentar novamente.',
    ], 429);
}

$input = json_input();
$email = strtolower(trim((string) ($input['email'] ?? '')));
$password = (string) ($input['password'] ?? '');

try {
    $statement = db()->prepare(
        'SELECT id, password_hash, verified_at FROM users WHERE email = :email LIMIT 1'
    );
    $statement->execute(['email' => $email]);
    $user = $statement->fetch();

    if (!$user || !password_verify($password, (string) $user['password_hash'])) {
        $attempts['count'] = (int) $attempts['count'] + 1;
        $_SESSION['login_attempts'] = $attempts;
        json_response(['ok' => false, 'message' => 'E-mail ou senha não conferem.'], 422);
    }
    if (empty($user['verified_at'])) {
        json_response([
            'ok' => false,
            'code' => 'email_unverified',
            'message' => 'Confirme o cadastro pelo e-mail antes de entrar.',
        ], 403);
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    unset($_SESSION['login_attempts']);
    csrf_token();

    json_response(['ok' => true, 'redirect' => 'app.php']);
} catch (Throwable $exception) {
    log_event('login_failed', ['exception' => $exception::class]);
    json_response(['ok' => false, 'message' => 'Não foi possível entrar agora. Tente novamente.'], 500);
}
