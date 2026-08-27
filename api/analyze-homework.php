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
    $imageUploads = [];
    if (isset($_FILES['images']) && is_array($_FILES['images']) && is_array($_FILES['images']['name'] ?? null)) {
        foreach ($_FILES['images']['name'] as $index => $name) {
            $imageUploads[] = [
                'name' => $name,
                'type' => $_FILES['images']['type'][$index] ?? '',
                'tmp_name' => $_FILES['images']['tmp_name'][$index] ?? '',
                'error' => $_FILES['images']['error'][$index] ?? UPLOAD_ERR_NO_FILE,
                'size' => $_FILES['images']['size'][$index] ?? 0,
            ];
        }
    } elseif (isset($_FILES['image']) && is_array($_FILES['image'])) {
        $imageUploads[] = $_FILES['image'];
    }
    if (!$imageUploads) {
        json_response(['ok' => false, 'message' => lumi_t($language, 'choose_photo_error')], 422);
    }
    if (count($imageUploads) > 4) {
        json_response(['ok' => false, 'message' => lumi_t($language, 'homework_photo_limit')], 422);
    }

    $question = trim((string) ($_POST['question'] ?? ''));
    if (text_length($question) > 1200) {
        json_response(['ok' => false, 'message' => lumi_t($language, 'homework_question_long')], 422);
    }
    $answerFormat = (string) ($_POST['answer_format'] ?? 'text');
    if (!in_array($answerFormat, ['text', 'image'], true)) {
        $answerFormat = 'text';
    }

    $audioPath = null;
    $audioMime = null;
    if (isset($_FILES['audio']) && is_array($_FILES['audio']) && ($_FILES['audio']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $audioUpload = $_FILES['audio'];
        if (($audioUpload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            json_response(['ok' => false, 'message' => lumi_t($language, 'receive_audio_error')], 422);
        }
        if ((int) ($audioUpload['size'] ?? 0) > 8 * 1024 * 1024) {
            json_response(['ok' => false, 'message' => lumi_t($language, 'audio_max_error')], 422);
        }
        $audioMime = '';
        if (class_exists('finfo')) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $audioMime = (string) $finfo->file((string) $audioUpload['tmp_name']);
        }
        if ($audioMime === '' || $audioMime === 'application/octet-stream') {
            $audioMime = strtolower(trim((string) ($audioUpload['type'] ?? 'audio/webm')));
            $audioMime = explode(';', $audioMime, 2)[0];
        }
        $allowedAudio = [
            'audio/webm', 'video/webm', 'audio/mpeg', 'audio/mp3', 'audio/mp4',
            'video/mp4', 'audio/ogg', 'audio/wav', 'audio/x-wav',
        ];
        if (!in_array($audioMime, $allowedAudio, true)) {
            json_response(['ok' => false, 'message' => lumi_t($language, 'audio_format_error')], 422);
        }
        $audioPath = (string) $audioUpload['tmp_name'];
    }

    $summary = ai_usage_summary($user);
    if ($summary['image']['remaining'] < count($imageUploads)) {
        throw new RuntimeException(lumi_t($language, 'daily_limit_message', ['type' => lumi_t($language, 'type_image')]));
    }
    if ($audioPath && $summary['voice']['remaining'] < 1) {
        throw new RuntimeException(lumi_t($language, 'daily_limit_message', ['type' => lumi_t($language, 'type_voice')]));
    }

    $cleanImages = array_map(
        static fn (array $upload): string => clean_uploaded_image($upload, $language),
        $imageUploads
    );
    $imageUsage = reserve_ai_requests($user, 'image', count($cleanImages));
    $voiceUsage = $audioPath ? reserve_ai_request($user, 'voice') : null;

    $result = (new AiService())->helpWithHomework(
        $cleanImages,
        $question,
        $audioPath,
        $audioMime,
        age_group($user['age']),
        $language,
        (string) $user['display_name']
    );
    $promptText = trim($question . (($result['transcript'] ?? '') !== $question ? "\n" . (string) ($result['transcript'] ?? '') : ''));
    $history = save_homework_history($user, $result, $answerFormat, $cleanImages, $promptText, $language);

    json_response([
        'ok' => true,
        'result' => $history,
        'usage' => [
            'image' => $imageUsage,
            'voice' => $voiceUsage,
        ],
    ]);
} catch (RuntimeException $exception) {
    log_event('homework_analysis_failed', [
        'user_id' => $user['id'],
        'request_type' => 'homework',
        'exception' => $exception::class,
    ]);
    json_response(['ok' => false, 'message' => $exception->getMessage()], 422);
} catch (Throwable $exception) {
    log_event('homework_analysis_failed', [
        'user_id' => $user['id'],
        'request_type' => 'homework',
        'exception' => $exception::class,
    ]);
    json_response([
        'ok' => false,
        'message' => lumi_t($language, 'homework_analysis_error'),
    ], 500);
}
