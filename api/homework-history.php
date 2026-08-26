<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

$user = require_user();
$language = request_language($user);
$historyId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if (!$historyId) {
    json_response(['ok' => false, 'message' => lumi_t($language, 'homework_history_missing')], 422);
}

$item = homework_history_item($user, $historyId);
if (!$item) {
    json_response(['ok' => false, 'message' => lumi_t($language, 'homework_history_missing')], 404);
}

json_response(['ok' => true, 'result' => homework_history_payload($item)]);
