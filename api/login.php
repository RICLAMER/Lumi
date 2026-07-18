<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

require_post();
require_csrf();

$language = request_language();
$attempts = $_SESSION['login_attempts'] ?? ['count' => 0, 'started_at' => time()];
if ((int) $attempts['started_at'] < time() - 900) {
    $attempts = ['count' => 0, 'started_at' => time()];
}
if ((int) $attempts['count'] >= 8) {
    json_response([
        'ok' => false,
        'message' => lumi_t($language, 'login_many'),
    ], 429);
}

$input = json_input();
$email = strtolower(trim((string) ($input['email'] ?? '')));
$password = (string) ($input['password'] ?? '');

try {
    $statement = db()->prepare(
        'SELECT id, language, password_hash, verified_at FROM users WHERE email = :email LIMIT 1'
    );
    $statement->execute(['email' => $email]);
    $user = $statement->fetch();

    if (!$user || !password_verify($password, (string) $user['password_hash'])) {
        $attempts['count'] = (int) $attempts['count'] + 1;
        $_SESSION['login_attempts'] = $attempts;
        json_response(['ok' => false, 'message' => lumi_t($language, 'invalid_credentials')], 422);
    }
    $language = normalize_language((string) $user['language']);
    if (empty($user['verified_at'])) {
        json_response([
            'ok' => false,
            'code' => 'email_unverified',
            'message' => lumi_t($language, 'email_unverified'),
        ], 403);
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    unset($_SESSION['login_attempts']);
    set_language_preference($language);
    csrf_token();

    json_response(['ok' => true, 'redirect' => 'app.php']);
} catch (Throwable $exception) {
    log_event('login_failed', ['exception' => $exception::class]);
    json_response(['ok' => false, 'message' => lumi_t($language, 'login_failed')], 500);
}
