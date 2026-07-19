<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

require_post();
require_csrf();
$user = require_user();
$currentLanguage = request_language($user);
$input = json_input();
$languageInput = strtolower(trim((string) ($input['language'] ?? '')));

if (!in_array($languageInput, supported_languages(), true)) {
    json_response([
        'ok' => false,
        'message' => lumi_t($currentLanguage, 'language_invalid'),
    ], 422);
}

try {
    $statement = db()->prepare('UPDATE users SET language = :language WHERE id = :id');
    $statement->execute([
        'language' => $languageInput,
        'id' => $user['id'],
    ]);
    set_language_preference($languageInput);

    json_response([
        'ok' => true,
        'language' => $languageInput,
        'redirect' => 'app.php',
    ]);
} catch (Throwable $exception) {
    log_event('language_update_failed', [
        'user_id' => $user['id'],
        'exception' => $exception::class,
    ]);
    json_response([
        'ok' => false,
        'message' => lumi_t($currentLanguage, 'language_update_failed'),
    ], 500);
}
