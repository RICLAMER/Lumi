<?php

declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

header('X-Robots-Tag: noindex, nofollow');

$portalError = '';
$portalNotice = (string) ($_SESSION['portal_notice'] ?? '');
unset($_SESSION['portal_notice']);

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $providedToken = (string) ($_POST['_token'] ?? '');
    if (!hash_equals(csrf_token(), $providedToken)) {
        $portalError = 'A sessão expirou. Atualize a página e tente novamente.';
    } else {
        $action = (string) ($_POST['action'] ?? '');

        if ($action === 'login') {
            $attempts = $_SESSION['portal_login_attempts'] ?? ['count' => 0, 'started_at' => time()];
            if ((int) $attempts['started_at'] < time() - 900) {
                $attempts = ['count' => 0, 'started_at' => time()];
            }

            if ((int) $attempts['count'] >= 6) {
                $portalError = 'Muitas tentativas. Aguarde 15 minutos.';
            } else {
                $email = strtolower(trim((string) ($_POST['email'] ?? '')));
                $password = (string) ($_POST['password'] ?? '');
                $statement = db()->prepare(
                    'SELECT id, language, password_hash, verified_at, is_tester
                     FROM users WHERE email = :email LIMIT 1'
                );
                $statement->execute(['email' => $email]);
                $candidate = $statement->fetch();

                if (
                    !$candidate
                    || empty($candidate['verified_at'])
                    || !(bool) $candidate['is_tester']
                    || !password_verify($password, (string) $candidate['password_hash'])
                ) {
                    $attempts['count'] = (int) $attempts['count'] + 1;
                    $_SESSION['portal_login_attempts'] = $attempts;
                    $portalError = 'Acesso permitido somente para o usuário de teste autorizado.';
                } else {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = (int) $candidate['id'];
                    unset($_SESSION['portal_login_attempts']);
                    set_language_preference((string) $candidate['language']);
                    header('Location: Portal.php');
                    exit;
                }
            }
        }
    }
}

$user = current_user();

if (
    ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'
    && $portalError === ''
    && (string) ($_POST['action'] ?? '') === 'save'
) {
    if (!$user || !$user['is_tester']) {
        http_response_code(403);
        $portalError = 'Este usuário não possui acesso ao portal.';
    } else {
        $registrationLimit = filter_var($_POST['registration_attempt_limit'] ?? null, FILTER_VALIDATE_INT);
        $windowMinutes = filter_var($_POST['registration_window_minutes'] ?? null, FILTER_VALIDATE_INT);
        $imageLimit = filter_var($_POST['image_daily_limit'] ?? null, FILTER_VALIDATE_INT);
        $voiceLimit = filter_var($_POST['voice_daily_limit'] ?? null, FILTER_VALIDATE_INT);

        if (
            $registrationLimit === false || $registrationLimit < 1 || $registrationLimit > 20
            || $windowMinutes === false || $windowMinutes < 1 || $windowMinutes > 60
            || $imageLimit === false || $imageLimit < 1 || $imageLimit > 100
            || $voiceLimit === false || $voiceLimit < 1 || $voiceLimit > 100
        ) {
            $portalError = 'Revise os valores. Os campos precisam permanecer dentro dos limites indicados.';
        } else {
            $settings = [
                'registration_attempt_limit' => $registrationLimit,
                'registration_window_seconds' => $windowMinutes * 60,
                'image_daily_limit' => $imageLimit,
                'voice_daily_limit' => $voiceLimit,
            ];

            $pdo = db();
            $pdo->beginTransaction();
            try {
                $statement = $pdo->prepare(
                    'INSERT INTO app_settings (setting_key, setting_value)
                     VALUES (:setting_key, :setting_value)
                     ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
                );
                foreach ($settings as $key => $value) {
                    $statement->execute([
                        'setting_key' => $key,
                        'setting_value' => (string) $value,
                    ]);
                }
                $pdo->commit();
                $_SESSION['portal_notice'] = 'Parâmetros atualizados com sucesso.';
                header('Location: Portal.php');
                exit;
            } catch (Throwable $exception) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                log_event('portal_settings_failed', ['exception' => $exception::class]);
                $portalError = 'Não foi possível salvar os parâmetros agora.';
            }
        }
    }
}

$settings = $user && $user['is_tester'] ? load_app_settings() : default_app_settings();
$h = static fn(string $value): string =>
    htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#17324d">
    <meta name="robots" content="noindex,nofollow">
    <title>Portal de controle | Lumi</title>
    <link rel="icon" href="favicon.ico" sizes="any">
    <link rel="icon" type="image/png" sizes="48x48" href="assets/icons/lumi-icon-48.png">
    <link rel="stylesheet" href="assets/css/portal.css">
</head>
<body>
    <header class="portal-header">
        <a class="portal-brand" href="<?= $user ? 'app.php' : 'index.php' ?>">
            <img src="assets/icons/lumi-icon-48.png" alt="">
            <span>Lumi</span>
        </a>
        <span class="portal-product">Portal de controle</span>
        <?php if ($user && $user['is_tester']): ?>
            <form action="logout.php" method="post">
                <input type="hidden" name="_token" value="<?= $h(csrf_token()) ?>">
                <button class="icon-command" type="submit" aria-label="Sair" title="Sair">
                    <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M10 5H5v14h5M14 8l4 4-4 4M8 12h10"/></svg>
                </button>
            </form>
        <?php endif; ?>
    </header>

    <?php if (!$user || !$user['is_tester']): ?>
        <main class="portal-login">
            <section aria-labelledby="portal-login-title">
                <img class="portal-login-icon" src="assets/icons/lumi-icon-192.png" alt="">
                <p class="portal-kicker">Acesso restrito</p>
                <h1 id="portal-login-title">Entrar no portal</h1>
                <form method="post" class="portal-form">
                    <input type="hidden" name="_token" value="<?= $h(csrf_token()) ?>">
                    <input type="hidden" name="action" value="login">
                    <label>
                        E-mail
                        <input type="email" name="email" autocomplete="email" required>
                    </label>
                    <label>
                        Senha
                        <input type="password" name="password" autocomplete="current-password" required>
                    </label>
                    <?php if ($portalError): ?>
                        <p class="portal-message portal-message-error" role="alert"><?= $h($portalError) ?></p>
                    <?php endif; ?>
                    <button class="primary-command" type="submit">Entrar</button>
                </form>
            </section>
        </main>
    <?php else: ?>
        <main class="portal-main">
            <header class="portal-heading">
                <div>
                    <p class="portal-kicker">Configuração operacional</p>
                    <h1>Limites de uso</h1>
                </div>
                <a class="back-command" href="app.php">
                    <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
                    Voltar ao app
                </a>
            </header>

            <?php if ($portalNotice): ?>
                <p class="portal-message portal-message-success" role="status"><?= $h($portalNotice) ?></p>
            <?php endif; ?>
            <?php if ($portalError): ?>
                <p class="portal-message portal-message-error" role="alert"><?= $h($portalError) ?></p>
            <?php endif; ?>

            <form method="post" class="settings-form">
                <input type="hidden" name="_token" value="<?= $h(csrf_token()) ?>">
                <input type="hidden" name="action" value="save">

                <section class="settings-band" aria-labelledby="registration-settings">
                    <div class="settings-copy">
                        <p class="portal-kicker">Proteção de cadastro</p>
                        <h2 id="registration-settings">Solicitações por IP</h2>
                    </div>
                    <div class="settings-fields">
                        <label>
                            Cadastros permitidos
                            <input
                                type="number"
                                name="registration_attempt_limit"
                                min="1"
                                max="20"
                                value="<?= (int) $settings['registration_attempt_limit'] ?>"
                                required
                            >
                            <span>Entre 1 e 20 solicitações.</span>
                        </label>
                        <label>
                            Intervalo em minutos
                            <input
                                type="number"
                                name="registration_window_minutes"
                                min="1"
                                max="60"
                                value="<?= (int) round($settings['registration_window_seconds'] / 60) ?>"
                                required
                            >
                            <span>Entre 1 e 60 minutos.</span>
                        </label>
                    </div>
                </section>

                <section class="settings-band" aria-labelledby="daily-settings">
                    <div class="settings-copy">
                        <p class="portal-kicker">Consumo diário</p>
                        <h2 id="daily-settings">Envios por usuário</h2>
                    </div>
                    <div class="settings-fields">
                        <label>
                            Fotos por dia
                            <input
                                type="number"
                                name="image_daily_limit"
                                min="1"
                                max="100"
                                value="<?= (int) $settings['image_daily_limit'] ?>"
                                required
                            >
                            <span>Entre 1 e 100 fotos.</span>
                        </label>
                        <label>
                            Perguntas de áudio por dia
                            <input
                                type="number"
                                name="voice_daily_limit"
                                min="1"
                                max="100"
                                value="<?= (int) $settings['voice_daily_limit'] ?>"
                                required
                            >
                            <span>Entre 1 e 100 perguntas.</span>
                        </label>
                    </div>
                </section>

                <footer class="settings-actions">
                    <button class="primary-command" type="submit">
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M5 4h12l2 2v14H5z"/><path d="M8 4v6h8V4M8 20v-6h8v6"/></svg>
                        Salvar parâmetros
                    </button>
                </footer>
            </form>
        </main>
    <?php endif; ?>
</body>
</html>
