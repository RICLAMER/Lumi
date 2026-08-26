<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

$user = require_user();
$historyId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$type = (string) ($_GET['type'] ?? '');
$item = $historyId ? homework_history_item($user, $historyId) : null;

if (!$item || !in_array($type, ['audio', 'image'], true)) {
    http_response_code(404);
    exit;
}

$filename = $type === 'audio' ? (string) ($item['audio_path'] ?? '') : (string) ($item['answer_image_path'] ?? '');
$extension = $type === 'audio' ? 'mp3' : 'svg';
if (!preg_match('/^[a-f0-9]{36}\.' . $extension . '$/', $filename)) {
    http_response_code(404);
    exit;
}

$path = homework_storage_directory() . '/' . $filename;
if (!is_file($path)) {
    http_response_code(404);
    exit;
}

header('Content-Type: ' . ($type === 'audio' ? 'audio/mpeg' : 'image/svg+xml'));
header('Content-Length: ' . (string) filesize($path));
header('Cache-Control: private, max-age=3600');
header('X-Content-Type-Options: nosniff');
readfile($path);
