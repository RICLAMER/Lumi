<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/Mailer.php';

require_post();
require_csrf();

$input = json_input();
$language = request_language();
$email = strtolower(trim((string) ($input['email'] ?? '')));
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(['ok' => false, 'message' => lumi_t($language, 'email_invalid')], 422);
}

try {
    $statement = db()->prepare(
        'SELECT id, display_name, language, verified_at FROM users WHERE email = :email LIMIT 1'
    );
    $statement->execute(['email' => $email]);
    $user = $statement->fetch();

    if ($user && empty($user['verified_at'])) {
        $language = normalize_language((string) $user['language']);
        $token = bin2hex(random_bytes(32));
        $statement = db()->prepare(
            'UPDATE users
             SET verification_token_hash = :token_hash,
                 verification_expires_at = DATE_ADD(NOW(), INTERVAL 24 HOUR)
             WHERE id = :id'
        );
        $statement->execute([
            'token_hash' => hash('sha256', $token),
            'id' => $user['id'],
        ]);
        (new Mailer())->sendVerification(
            $email,
            (string) $user['display_name'],
            app_url('verify.php?token=' . $token),
            $language
        );
    }

    json_response([
        'ok' => true,
        'message' => lumi_t($language, 'resend_generic'),
    ]);
} catch (Throwable $exception) {
    log_event('verification_resend_failed', ['exception' => $exception::class]);
    json_response(['ok' => false, 'message' => lumi_t($language, 'resend_failed')], 500);
}
