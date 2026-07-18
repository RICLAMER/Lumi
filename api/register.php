<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/Mailer.php';

require_post();
require_csrf();

$input = json_input();
$languageInput = (string) ($input['language'] ?? request_language());
$language = normalize_language($languageInput);
if (!empty($input['website'])) {
    json_response(['ok' => true, 'message' => lumi_t($language, 'register_success')]);
}

try {
    $pdo = db();
    $registrationLimit = consume_registration_attempt(
        $pdo,
        app_setting_int('registration_attempt_limit'),
        app_setting_int('registration_window_seconds')
    );
} catch (Throwable $exception) {
    log_event('registration_rate_limit_failed', ['exception' => $exception::class]);
    json_response([
        'ok' => false,
        'message' => lumi_t($language, 'register_start_failed'),
    ], 500);
}

if (!$registrationLimit['allowed']) {
    $retryAfter = (int) $registrationLimit['retry_after'];
    header('Retry-After: ' . $retryAfter);
    json_response([
        'ok' => false,
        'code' => 'registration_rate_limited',
        'retry_after' => $retryAfter,
        'message' => lumi_t($language, 'register_limited'),
    ], 429);
}

$lastRegistration = (int) ($_SESSION['last_registration_at'] ?? 0);
if ($lastRegistration > time() - 45) {
    json_response(['ok' => false, 'message' => lumi_t($language, 'register_wait')], 429);
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
    json_response(['ok' => false, 'message' => lumi_t($language, 'name_invalid')], 422);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
    json_response(['ok' => false, 'message' => lumi_t($language, 'email_invalid')], 422);
}
if ($age === false || $age < 6 || $age > 14) {
    json_response(['ok' => false, 'message' => lumi_t($language, 'age_invalid')], 422);
}
if (strlen($password) < 10 || strlen($password) > 128) {
    json_response(['ok' => false, 'message' => lumi_t($language, 'password_invalid')], 422);
}
if (!in_array(strtolower($languageInput), supported_languages(), true)) {
    json_response(['ok' => false, 'message' => lumi_t($language, 'language_invalid')], 422);
}
if (!$consent) {
    json_response(['ok' => false, 'message' => lumi_t($language, 'consent_required')], 422);
}

try {
    $statement = $pdo->prepare('SELECT id, verified_at FROM users WHERE email = :email LIMIT 1');
    $statement->execute(['email' => $email]);
    $existing = $statement->fetch();

    if ($existing && !empty($existing['verified_at'])) {
        json_response([
            'ok' => false,
            'message' => lumi_t($language, 'email_exists'),
        ], 409);
    }

    $token = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $token);
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    if ($existing) {
        $statement = $pdo->prepare(
            'UPDATE users
             SET display_name = :display_name, age = :age, language = :language,
                 password_hash = :password_hash,
                 verification_token_hash = :token_hash,
                 verification_expires_at = DATE_ADD(NOW(), INTERVAL 24 HOUR)
             WHERE id = :id'
        );
        $statement->execute([
            'display_name' => $name,
            'age' => $age,
            'language' => $language,
            'password_hash' => $passwordHash,
            'token_hash' => $tokenHash,
            'id' => $existing['id'],
        ]);
    } else {
        $statement = $pdo->prepare(
            'INSERT INTO users
                (email, display_name, age, language, password_hash,
                 verification_token_hash, verification_expires_at)
             VALUES
                (:email, :display_name, :age, :language, :password_hash,
                 :token_hash, DATE_ADD(NOW(), INTERVAL 24 HOUR))'
        );
        $statement->execute([
            'email' => $email,
            'display_name' => $name,
            'age' => $age,
            'language' => $language,
            'password_hash' => $passwordHash,
            'token_hash' => $tokenHash,
        ]);
    }

    (new Mailer())->sendVerification(
        $email,
        $name,
        app_url('verify.php?token=' . $token),
        $language
    );
    $_SESSION['last_registration_at'] = time();

    json_response([
        'ok' => true,
        'message' => lumi_t($language, 'register_success'),
    ]);
} catch (Throwable $exception) {
    log_event('registration_failed', ['exception' => $exception::class]);
    json_response([
        'ok' => false,
        'message' => lumi_t($language, 'register_failed'),
    ], 500);
}
