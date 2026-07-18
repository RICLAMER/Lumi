<?php

declare(strict_types=1);

const LUMI_ROOT = __DIR__ . '/..';

require_once __DIR__ . '/i18n.php';

function load_env(string $path): void
{
    if (!is_file($path)) {
        return;
    }

    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = array_map('trim', explode('=', $line, 2));
        if ($key === '') {
            continue;
        }

        if (
            strlen($value) >= 2
            && (($value[0] === '"' && str_ends_with($value, '"'))
                || ($value[0] === "'" && str_ends_with($value, "'")))
        ) {
            $value = substr($value, 1, -1);
        }

        $_ENV[$key] = $value;
    }
}

load_env(LUMI_ROOT . '/.env');

function env(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? getenv($key);
    if ($value === false || $value === null || $value === '') {
        return $default;
    }

    return match (strtolower((string) $value)) {
        'true', '(true)' => true,
        'false', '(false)' => false,
        'null', '(null)' => null,
        default => $value,
    };
}

date_default_timezone_set((string) env('APP_TIMEZONE', 'America/Sao_Paulo'));

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

session_name('lumi_session');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
]);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(self), microphone=(self), geolocation=()');
header(
    "Content-Security-Policy: default-src 'self'; "
    . "connect-src 'self'; img-src 'self' data: blob:; media-src 'self' data: blob:; "
    . "style-src 'self'; script-src 'self'; object-src 'none'; frame-ancestors 'none'; "
    . "base-uri 'self'; form-action 'self'"
);

function app_url(string $path = ''): string
{
    $base = rtrim((string) env('APP_URL', ''), '/');
    if ($base === '') {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $base = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    }

    return $base . '/' . ltrim($path, '/');
}

function text_length(string $value): int
{
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['csrf_token'];
}

function require_csrf(): void
{
    $provided = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['_token'] ?? '';
    if (!is_string($provided) || !hash_equals(csrf_token(), $provided)) {
        $language = request_language();
        json_response(['ok' => false, 'message' => lumi_t($language, 'csrf_expired')], 419);
    }
}

function json_input(): array
{
    $payload = json_decode((string) file_get_contents('php://input'), true);
    return is_array($payload) ? $payload : [];
}

function json_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function require_post(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        $language = request_language();
        json_response(['ok' => false, 'message' => lumi_t($language, 'method_not_allowed')], 405);
    }
}

function log_event(string $event, array $context = []): void
{
    $safeContext = array_intersect_key($context, array_flip([
        'user_id', 'request_type', 'status', 'code', 'exception',
    ]));
    $record = [
        'time' => date(DATE_ATOM),
        'event' => $event,
        'context' => $safeContext,
    ];

    @file_put_contents(
        LUMI_ROOT . '/storage/logs/app.log',
        json_encode($record, JSON_UNESCAPED_UNICODE) . PHP_EOL,
        FILE_APPEND | LOCK_EX
    );
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        (string) env('DB_HOST', 'localhost'),
        (int) env('DB_PORT', 3306),
        (string) env('DB_NAME', '')
    );

    $pdo = new PDO($dsn, (string) env('DB_USER', ''), (string) env('DB_PASS', ''), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    ensure_schema($pdo);
    ensure_tester($pdo);

    return $pdo;
}

function ensure_schema(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS app_meta (
            meta_key VARCHAR(80) PRIMARY KEY,
            meta_value TEXT NOT NULL,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    $version = $pdo->query(
        "SELECT meta_value FROM app_meta WHERE meta_key = 'schema_version'"
    )->fetchColumn();

    if ((int) $version < 1) {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS users (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(254) NOT NULL UNIQUE,
                display_name VARCHAR(40) NOT NULL,
                age TINYINT UNSIGNED NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                verification_token_hash CHAR(64) NULL UNIQUE,
                verification_expires_at DATETIME NULL,
                verified_at DATETIME NULL,
                is_tester TINYINT(1) NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_users_verification (verification_token_hash)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS ai_usage (
                user_id BIGINT UNSIGNED NOT NULL,
                usage_date DATE NOT NULL,
                total_requests SMALLINT UNSIGNED NOT NULL DEFAULT 0,
                image_requests SMALLINT UNSIGNED NOT NULL DEFAULT 0,
                voice_requests SMALLINT UNSIGNED NOT NULL DEFAULT 0,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (user_id, usage_date),
                CONSTRAINT fk_ai_usage_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $statement = $pdo->prepare(
            "INSERT INTO app_meta (meta_key, meta_value) VALUES ('schema_version', '1')
             ON DUPLICATE KEY UPDATE meta_value = VALUES(meta_value)"
        );
        $statement->execute();
        $version = 1;
    }

    if ((int) $version < 2) {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS registration_attempts (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                ip_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
                attempted_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
                INDEX idx_registration_attempts_ip_time (ip_hash, attempted_at),
                INDEX idx_registration_attempts_time (attempted_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $statement = $pdo->prepare(
            "INSERT INTO app_meta (meta_key, meta_value) VALUES ('schema_version', '2')
             ON DUPLICATE KEY UPDATE meta_value = VALUES(meta_value)"
        );
        $statement->execute();
        $version = 2;
    }

    if ((int) $version < 3) {
        $languageColumn = $pdo->query("SHOW COLUMNS FROM users LIKE 'language'")->fetch();
        if (!$languageColumn) {
            $pdo->exec("ALTER TABLE users ADD COLUMN language CHAR(2) NULL AFTER age");
        }
        $pdo->exec(
            "UPDATE users SET language = 'pt'
             WHERE language IS NULL OR language NOT IN ('en', 'pt', 'es')"
        );
        $pdo->exec(
            "ALTER TABLE users MODIFY language CHAR(2) NOT NULL DEFAULT 'en'"
        );

        $statement = $pdo->prepare(
            "INSERT INTO app_meta (meta_key, meta_value) VALUES ('schema_version', '3')
             ON DUPLICATE KEY UPDATE meta_value = VALUES(meta_value)"
        );
        $statement->execute();
        $version = 3;
    }

    if ((int) $version < 4) {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS app_settings (
                setting_key VARCHAR(80) PRIMARY KEY,
                setting_value VARCHAR(255) NOT NULL,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $pdo->exec(
            'INSERT IGNORE INTO app_settings (setting_key, setting_value) VALUES
                ("registration_attempt_limit", "3"),
                ("registration_window_seconds", "300"),
                ("image_daily_limit", "5"),
                ("voice_daily_limit", "5")'
        );

        $statement = $pdo->prepare(
            "INSERT INTO app_meta (meta_key, meta_value) VALUES ('schema_version', '4')
             ON DUPLICATE KEY UPDATE meta_value = VALUES(meta_value)"
        );
        $statement->execute();
    }
}

function ensure_tester(PDO $pdo): void
{
    $email = strtolower(trim((string) env('TESTER_EMAIL', '')));
    $password = (string) env('TESTER_PASSWORD', '');
    $language = normalize_language((string) env('TESTER_LANGUAGE', 'en'));
    if ($email === '' || $password === '') {
        return;
    }

    $fingerprint = hash('sha256', $email . "\0" . $password . "\0" . $language);
    $statement = $pdo->prepare("SELECT meta_value FROM app_meta WHERE meta_key = 'tester_fingerprint'");
    $statement->execute();
    if (hash_equals((string) ($statement->fetchColumn() ?: ''), $fingerprint)) {
        return;
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $statement = $pdo->prepare(
        'INSERT INTO users
            (email, display_name, age, language, password_hash, verified_at, is_tester)
         VALUES
            (:email, :display_name, 10, :language, :password_hash, NOW(), 1)
         ON DUPLICATE KEY UPDATE
            display_name = VALUES(display_name),
            language = VALUES(language),
            password_hash = VALUES(password_hash),
            verified_at = NOW(),
            is_tester = 1'
    );
    $statement->execute([
        'email' => $email,
        'display_name' => 'Explorador Tester',
        'language' => $language,
        'password_hash' => $passwordHash,
    ]);

    $statement = $pdo->prepare(
        "INSERT INTO app_meta (meta_key, meta_value) VALUES ('tester_fingerprint', :fingerprint)
         ON DUPLICATE KEY UPDATE meta_value = VALUES(meta_value)"
    );
    $statement->execute(['fingerprint' => $fingerprint]);
}

function current_user(): ?array
{
    static $loaded = false;
    static $user = null;

    if ($loaded) {
        return $user;
    }
    $loaded = true;

    $userId = (int) ($_SESSION['user_id'] ?? 0);
    if ($userId < 1) {
        return null;
    }

    $statement = db()->prepare(
        'SELECT id, email, display_name, age, language, verified_at, is_tester
         FROM users WHERE id = :id LIMIT 1'
    );
    $statement->execute(['id' => $userId]);
    $row = $statement->fetch();

    if (!$row || empty($row['verified_at'])) {
        unset($_SESSION['user_id']);
        return null;
    }

    $row['id'] = (int) $row['id'];
    $row['age'] = (int) $row['age'];
    $row['language'] = normalize_language((string) $row['language']);
    $row['is_tester'] = (bool) $row['is_tester'];
    $user = $row;
    return $user;
}

function require_user(): array
{
    $user = current_user();
    if (!$user) {
        $language = request_language();
        json_response(['ok' => false, 'message' => lumi_t($language, 'auth_required')], 401);
    }
    return $user;
}

function age_group(int $age): string
{
    return match (true) {
        $age <= 8 => '6-8',
        $age <= 11 => '9-11',
        default => '12-14',
    };
}

function default_app_settings(): array
{
    return [
        'registration_attempt_limit' => 3,
        'registration_window_seconds' => 300,
        'image_daily_limit' => 5,
        'voice_daily_limit' => 5,
    ];
}

function load_app_settings(?PDO $pdo = null): array
{
    $pdo ??= db();
    $settings = default_app_settings();
    $rows = $pdo->query('SELECT setting_key, setting_value FROM app_settings')->fetchAll();
    foreach ($rows as $row) {
        $key = (string) $row['setting_key'];
        if (array_key_exists($key, $settings)) {
            $settings[$key] = (int) $row['setting_value'];
        }
    }
    return $settings;
}

function app_setting_int(string $key): int
{
    static $settings = null;
    if (!is_array($settings)) {
        $settings = load_app_settings();
    }
    $defaults = default_app_settings();
    return (int) ($settings[$key] ?? $defaults[$key] ?? 0);
}

function ai_usage_summary(array $user): array
{
    $statement = db()->prepare(
        'SELECT image_requests, voice_requests
         FROM ai_usage
         WHERE user_id = :user_id AND usage_date = CURRENT_DATE()
         LIMIT 1'
    );
    $statement->execute(['user_id' => $user['id']]);
    $row = $statement->fetch() ?: [];

    $imageLimit = app_setting_int('image_daily_limit');
    $voiceLimit = app_setting_int('voice_daily_limit');
    $imageUsed = (int) ($row['image_requests'] ?? 0);
    $voiceUsed = (int) ($row['voice_requests'] ?? 0);

    return [
        'image' => [
            'used' => $imageUsed,
            'limit' => $imageLimit,
            'remaining' => max(0, $imageLimit - $imageUsed),
        ],
        'voice' => [
            'used' => $voiceUsed,
            'limit' => $voiceLimit,
            'remaining' => max(0, $voiceLimit - $voiceUsed),
        ],
    ];
}

function reserve_ai_request(array $user, string $type): array
{
    $column = match ($type) {
        'image' => 'image_requests',
        'voice' => 'voice_requests',
        default => throw new InvalidArgumentException('Invalid usage type.'),
    };
    $limit = app_setting_int($type . '_daily_limit');
    $language = request_language($user);

    $pdo = db();
    $pdo->beginTransaction();
    try {
        $insert = $pdo->prepare(
            'INSERT IGNORE INTO ai_usage (user_id, usage_date) VALUES (:user_id, CURRENT_DATE())'
        );
        $insert->execute(['user_id' => $user['id']]);

        $select = $pdo->prepare(
            "SELECT {$column} FROM ai_usage
             WHERE user_id = :user_id AND usage_date = CURRENT_DATE() FOR UPDATE"
        );
        $select->execute(['user_id' => $user['id']]);
        $current = (int) $select->fetchColumn();
        if ($current >= $limit) {
            $pdo->rollBack();
            throw new RuntimeException(
                lumi_t($language, 'daily_limit_message', [
                    'type' => lumi_t($language, 'type_' . $type),
                ])
            );
        }

        $update = $pdo->prepare(
            "UPDATE ai_usage
             SET total_requests = total_requests + 1, {$column} = {$column} + 1
             WHERE user_id = :user_id AND usage_date = CURRENT_DATE()"
        );
        $update->execute(['user_id' => $user['id']]);
        $pdo->commit();

        $used = $current + 1;
        return [
            'used' => $used,
            'limit' => $limit,
            'remaining' => max(0, $limit - $used),
        ];
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }
}

function client_fingerprint(): string
{
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    return hash_hmac('sha256', $ip, (string) env('APP_KEY', 'lumi'));
}

function consume_registration_attempt(PDO $pdo, int $limit = 3, int $windowSeconds = 300): array
{
    $limit = max(1, min(20, $limit));
    $windowSeconds = max(60, min(3600, $windowSeconds));
    $ipHash = client_fingerprint();
    $lockName = 'lumi_registration_' . substr($ipHash, 0, 40);

    $lock = $pdo->prepare('SELECT GET_LOCK(:lock_name, 3)');
    $lock->execute(['lock_name' => $lockName]);
    if ((int) $lock->fetchColumn() !== 1) {
        return ['allowed' => false, 'retry_after' => 5];
    }

    try {
        $windowStart = "DATE_SUB(NOW(6), INTERVAL {$windowSeconds} SECOND)";
        $statement = $pdo->prepare(
            "SELECT COUNT(*) AS attempt_count,
                    COALESCE(TIMESTAMPDIFF(SECOND, MIN(attempted_at), NOW(6)), 0) AS oldest_age
             FROM registration_attempts
             WHERE ip_hash = :ip_hash AND attempted_at > {$windowStart}"
        );
        $statement->execute(['ip_hash' => $ipHash]);
        $usage = $statement->fetch() ?: [];
        $attemptCount = (int) ($usage['attempt_count'] ?? 0);

        if ($attemptCount >= $limit) {
            $oldestAge = (int) ($usage['oldest_age'] ?? 0);
            return [
                'allowed' => false,
                'retry_after' => max(1, $windowSeconds - $oldestAge),
            ];
        }

        $statement = $pdo->prepare(
            'INSERT INTO registration_attempts (ip_hash) VALUES (:ip_hash)'
        );
        $statement->execute(['ip_hash' => $ipHash]);

        if (random_int(1, 100) === 1) {
            try {
                $pdo->exec(
                    'DELETE FROM registration_attempts
                     WHERE attempted_at < DATE_SUB(NOW(6), INTERVAL 1 DAY)
                     LIMIT 1000'
                );
            } catch (Throwable $exception) {
                log_event('registration_attempt_cleanup_failed', [
                    'exception' => $exception::class,
                ]);
            }
        }

        return [
            'allowed' => true,
            'retry_after' => 0,
            'remaining' => max(0, $limit - $attemptCount - 1),
        ];
    } finally {
        $release = $pdo->prepare('SELECT RELEASE_LOCK(:lock_name)');
        $release->execute(['lock_name' => $lockName]);
    }
}
