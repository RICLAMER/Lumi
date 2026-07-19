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
    if (!isset($_FILES['image']) || !is_array($_FILES['image'])) {
        json_response(['ok' => false, 'message' => lumi_t($language, 'choose_photo_error')], 422);
    }

    $upload = $_FILES['image'];
    if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        json_response(['ok' => false, 'message' => lumi_t($language, 'receive_photo_error')], 422);
    }
    if ((int) ($upload['size'] ?? 0) > 8 * 1024 * 1024) {
        json_response(['ok' => false, 'message' => lumi_t($language, 'image_max_error')], 422);
    }
    if (!function_exists('imagecreatefromstring')) {
        throw new RuntimeException(lumi_t($language, 'image_processing_unavailable'));
    }

    $raw = file_get_contents((string) $upload['tmp_name']);
    $info = $raw !== false ? @getimagesizefromstring($raw) : false;
    if ($raw === false || !$info || !in_array($info['mime'], ['image/jpeg', 'image/png', 'image/webp'], true)) {
        json_response(['ok' => false, 'message' => lumi_t($language, 'image_format_error')], 422);
    }
    if ($info[0] < 120 || $info[1] < 120 || $info[0] > 6000 || $info[1] > 6000) {
        json_response(['ok' => false, 'message' => lumi_t($language, 'image_resolution_error')], 422);
    }

    $source = @imagecreatefromstring($raw);
    if (!$source) {
        json_response(['ok' => false, 'message' => lumi_t($language, 'image_open_error')], 422);
    }

    $maxEdge = 1280;
    $scale = min(1, $maxEdge / max($info[0], $info[1]));
    $width = max(1, (int) round($info[0] * $scale));
    $height = max(1, (int) round($info[1] * $scale));
    $canvas = imagecreatetruecolor($width, $height);
    $white = imagecolorallocate($canvas, 255, 255, 255);
    imagefill($canvas, 0, 0, $white);
    imagecopyresampled($canvas, $source, 0, 0, 0, 0, $width, $height, $info[0], $info[1]);

    ob_start();
    imagejpeg($canvas, null, 82);
    $cleanJpeg = (string) ob_get_clean();
    imagedestroy($source);
    imagedestroy($canvas);

    $usage = reserve_ai_request($user, 'image');
    $result = (new AiService())->analyzeImage(
        $cleanJpeg,
        age_group($user['age']),
        $language,
        (string) $user['display_name']
    );

    json_response([
        'ok' => true,
        'result' => $result,
        'usage' => array_merge(['type' => 'image'], $usage),
    ]);
} catch (RuntimeException $exception) {
    log_event('image_analysis_failed', [
        'user_id' => $user['id'],
        'request_type' => 'image',
        'exception' => $exception::class,
    ]);
    json_response(['ok' => false, 'message' => $exception->getMessage()], 422);
} catch (Throwable $exception) {
    log_event('image_analysis_failed', [
        'user_id' => $user['id'],
        'request_type' => 'image',
        'exception' => $exception::class,
    ]);
    json_response([
        'ok' => false,
        'message' => lumi_t($language, 'image_analysis_error'),
    ], 500);
}
