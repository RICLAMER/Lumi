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
$extension = strtolower((string) pathinfo($filename, PATHINFO_EXTENSION));
$allowedExtensions = $type === 'audio' ? ['mp3'] : ['svg', 'jpg', 'jpeg', 'png', 'webp'];
if (!preg_match('/^[a-f0-9]{36}\.[a-z0-9]+$/', $filename) || !in_array($extension, $allowedExtensions, true)) {
    http_response_code(404);
    exit;
}

$path = homework_storage_directory() . '/' . $filename;
if (!is_file($path)) {
    http_response_code(404);
    exit;
}

$contentTypes = [
    'mp3' => 'audio/mpeg',
    'svg' => 'image/svg+xml',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'webp' => 'image/webp',
];
header('Content-Type: ' . $contentTypes[$extension]);
header('Content-Length: ' . (string) filesize($path));
header('Cache-Control: private, max-age=3600');
header('X-Content-Type-Options: nosniff');
readfile($path);
