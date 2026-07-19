<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/OpenAIClient.php';
require_once __DIR__ . '/../src/AiService.php';

require_post();
require_csrf();
$user = require_user();
$language = request_language($user);

try {
    if (!isset($_FILES['audio']) || !is_array($_FILES['audio'])) {
        json_response(['ok' => false, 'message' => lumi_t($language, 'record_before_send')], 422);
    }

    $upload = $_FILES['audio'];
    if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        json_response(['ok' => false, 'message' => lumi_t($language, 'receive_audio_error')], 422);
    }
    if ((int) ($upload['size'] ?? 0) > 8 * 1024 * 1024) {
        json_response(['ok' => false, 'message' => lumi_t($language, 'audio_max_error')], 422);
    }

    $mime = '';
    if (class_exists('finfo')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = (string) $finfo->file((string) $upload['tmp_name']);
    }
    if ($mime === '' || $mime === 'application/octet-stream') {
        $mime = strtolower(trim((string) ($upload['type'] ?? 'audio/webm')));
        $mime = explode(';', $mime, 2)[0];
    }

    $allowed = [
        'audio/webm', 'video/webm', 'audio/mpeg', 'audio/mp3', 'audio/mp4',
        'video/mp4', 'audio/ogg', 'audio/wav', 'audio/x-wav',
    ];
    if (!in_array($mime, $allowed, true)) {
        json_response(['ok' => false, 'message' => lumi_t($language, 'audio_format_error')], 422);
    }

    $usage = reserve_ai_request($user, 'voice');
    $result = (new AiService())->answerVoice(
        (string) $upload['tmp_name'],
        $mime,
        age_group($user['age']),
        $language,
        (string) $user['display_name']
    );

    json_response([
        'ok' => true,
        'result' => $result,
        'usage' => array_merge(['type' => 'voice'], $usage),
    ]);
} catch (RuntimeException $exception) {
    log_event('voice_analysis_failed', [
        'user_id' => $user['id'],
        'request_type' => 'voice',
        'exception' => $exception::class,
    ]);
    json_response(['ok' => false, 'message' => $exception->getMessage()], 422);
} catch (Throwable $exception) {
    log_event('voice_analysis_failed', [
        'user_id' => $user['id'],
        'request_type' => 'voice',
        'exception' => $exception::class,
    ]);
    json_response([
        'ok' => false,
        'message' => lumi_t($language, 'voice_analysis_error'),
    ], 500);
}
