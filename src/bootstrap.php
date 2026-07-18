<?php

declare(strict_types=1);

const LUMI_ROOT = __DIR__ . '/..';

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
        json_response(['ok' => false, 'message' => 'Sua sessão expirou. Atualize a página e tente novamente.'], 419);
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
        json_response(['ok' => false, 'message' => 'Método não permitido.'], 405);
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

    if ((int) $version >= 1) {
        return;
    }

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
}

function ensure_tester(PDO $pdo): void
{
    $email = strtolower(trim((string) env('TESTER_EMAIL', '')));
    $password = (string) env('TESTER_PASSWORD', '');
    if ($email === '' || $password === '') {
        return;
    }

    $fingerprint = hash('sha256', $email . "\0" . $password);
    $statement = $pdo->prepare("SELECT meta_value FROM app_meta WHERE meta_key = 'tester_fingerprint'");
    $statement->execute();
    if (hash_equals((string) ($statement->fetchColumn() ?: ''), $fingerprint)) {
        return;
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $statement = $pdo->prepare(
        'INSERT INTO users
            (email, display_name, age, password_hash, verified_at, is_tester)
         VALUES
            (:email, :display_name, 10, :password_hash, NOW(), 1)
         ON DUPLICATE KEY UPDATE
            display_name = VALUES(display_name),
            password_hash = VALUES(password_hash),
            verified_at = NOW(),
            is_tester = 1'
    );
    $statement->execute([
        'email' => $email,
        'display_name' => 'Explorador Tester',
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
        'SELECT id, email, display_name, age, verified_at, is_tester
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
    $row['is_tester'] = (bool) $row['is_tester'];
    $user = $row;
    return $user;
}

function require_user(): array
{
    $user = current_user();
    if (!$user) {
        json_response(['ok' => false, 'message' => 'Entre na sua conta para continuar.'], 401);
    }
    return $user;
}

function age_group(int $age): string
{
    return match (true) {
        $age <= 8 => '6 a 8 anos',
        $age <= 11 => '9 a 11 anos',
        default => '12 a 14 anos',
    };
}

function reserve_ai_request(array $user, string $type): int
{
    $column = match ($type) {
        'image' => 'image_requests',
        'voice' => 'voice_requests',
        default => throw new InvalidArgumentException('Tipo de uso inválido.'),
    };
    $limit = (int) env(
        $user['is_tester'] ? 'TESTER_DAILY_AI_LIMIT' : 'USER_DAILY_AI_LIMIT',
        $user['is_tester'] ? 30 : 8
    );

    $pdo = db();
    $pdo->beginTransaction();
    try {
        $insert = $pdo->prepare(
            'INSERT IGNORE INTO ai_usage (user_id, usage_date) VALUES (:user_id, CURRENT_DATE())'
        );
        $insert->execute(['user_id' => $user['id']]);

        $select = $pdo->prepare(
            'SELECT total_requests FROM ai_usage
             WHERE user_id = :user_id AND usage_date = CURRENT_DATE() FOR UPDATE'
        );
        $select->execute(['user_id' => $user['id']]);
        $current = (int) $select->fetchColumn();
        if ($current >= $limit) {
            $pdo->rollBack();
            throw new RuntimeException(
                'Você já fez muitas descobertas hoje. Volte amanhã para continuar explorando!'
            );
        }

        $update = $pdo->prepare(
            "UPDATE ai_usage
             SET total_requests = total_requests + 1, {$column} = {$column} + 1
             WHERE user_id = :user_id AND usage_date = CURRENT_DATE()"
        );
        $update->execute(['user_id' => $user['id']]);
        $pdo->commit();

        return max(0, $limit - $current - 1);
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
