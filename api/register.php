<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/Mailer.php';

require_post();
require_csrf();

$input = json_input();
if (!empty($input['website'])) {
    json_response(['ok' => true, 'message' => 'Confira seu e-mail para continuar.']);
}

try {
    $pdo = db();
    $registrationLimit = consume_registration_attempt($pdo);
} catch (Throwable $exception) {
    log_event('registration_rate_limit_failed', ['exception' => $exception::class]);
    json_response([
        'ok' => false,
        'message' => 'Não conseguimos iniciar o cadastro agora. Tente novamente em instantes.',
    ], 500);
}

if (!$registrationLimit['allowed']) {
    $retryAfter = (int) $registrationLimit['retry_after'];
    header('Retry-After: ' . $retryAfter);
    json_response([
        'ok' => false,
        'code' => 'registration_rate_limited',
        'retry_after' => $retryAfter,
        'message' => 'Muitas tentativas de cadastro. Aguarde alguns minutos e tente novamente.',
    ], 429);
}

$lastRegistration = (int) ($_SESSION['last_registration_at'] ?? 0);
if ($lastRegistration > time() - 45) {
    json_response(['ok' => false, 'message' => 'Aguarde um pouquinho antes de tentar novamente.'], 429);
}

$name = trim((string) ($input['display_name'] ?? ''));
$email = strtolower(trim((string) ($input['email'] ?? '')));
$age = filter_var($input['age'] ?? null, FILTER_VALIDATE_INT);
$password = (string) ($input['password'] ?? '');
$consent = filter_var($input['consent'] ?? false, FILTER_VALIDATE_BOOL);

if (
    text_length($name) < 2
    || text_length($name) > 30
    || !preg_match("/^[\p{L}\p{M}][\p{L}\p{M}\s.'-]*$/u", $name)
) {
    json_response(['ok' => false, 'message' => 'Informe um nome ou apelido com 2 a 30 letras.'], 422);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
    json_response(['ok' => false, 'message' => 'Informe um e-mail válido do responsável.'], 422);
}
if ($age === false || $age < 6 || $age > 14) {
    json_response(['ok' => false, 'message' => 'A primeira versão da Lumi atende idades de 6 a 14 anos.'], 422);
}
if (strlen($password) < 10 || strlen($password) > 128) {
    json_response(['ok' => false, 'message' => 'Crie uma senha com pelo menos 10 caracteres.'], 422);
}
if (!$consent) {
    json_response(['ok' => false, 'message' => 'O responsável precisa autorizar o cadastro.'], 422);
}

try {
    $statement = $pdo->prepare('SELECT id, verified_at FROM users WHERE email = :email LIMIT 1');
    $statement->execute(['email' => $email]);
    $existing = $statement->fetch();

    if ($existing && !empty($existing['verified_at'])) {
        json_response([
            'ok' => false,
            'message' => 'Este e-mail já tem acesso. Use a opção Entrar.',
        ], 409);
    }

    $token = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $token);
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    if ($existing) {
        $statement = $pdo->prepare(
            'UPDATE users
             SET display_name = :display_name, age = :age, password_hash = :password_hash,
                 verification_token_hash = :token_hash,
                 verification_expires_at = DATE_ADD(NOW(), INTERVAL 24 HOUR)
             WHERE id = :id'
        );
        $statement->execute([
            'display_name' => $name,
            'age' => $age,
            'password_hash' => $passwordHash,
            'token_hash' => $tokenHash,
            'id' => $existing['id'],
        ]);
    } else {
        $statement = $pdo->prepare(
            'INSERT INTO users
                (email, display_name, age, password_hash, verification_token_hash, verification_expires_at)
             VALUES
                (:email, :display_name, :age, :password_hash, :token_hash, DATE_ADD(NOW(), INTERVAL 24 HOUR))'
        );
        $statement->execute([
            'email' => $email,
            'display_name' => $name,
            'age' => $age,
            'password_hash' => $passwordHash,
            'token_hash' => $tokenHash,
        ]);
    }

    (new Mailer())->sendVerification($email, $name, app_url('verify.php?token=' . $token));
    $_SESSION['last_registration_at'] = time();

    json_response([
        'ok' => true,
        'message' => 'Cadastro recebido! Abra o e-mail do responsável e toque no botão para entrar.',
    ]);
} catch (Throwable $exception) {
    log_event('registration_failed', ['exception' => $exception::class]);
    json_response([
        'ok' => false,
        'message' => 'Não conseguimos concluir o cadastro agora. Tente novamente em instantes.',
    ], 500);
}
