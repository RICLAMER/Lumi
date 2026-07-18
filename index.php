<?php

declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

$user = current_user();
$requestedLanguage = strtolower(trim((string) ($_GET['lang'] ?? '')));
if (
    $user
    && in_array($requestedLanguage, supported_languages(), true)
    && $requestedLanguage !== $user['language']
) {
    $statement = db()->prepare('UPDATE users SET language = :language WHERE id = :id');
    $statement->execute([
        'language' => $requestedLanguage,
        'id' => $user['id'],
    ]);
    $user['language'] = $requestedLanguage;
}
$language = $user
    ? set_language_preference((string) $user['language'])
    : interface_language();
$locale = language_locale($language);
$t = static fn(string $key, array $replacements = []): string =>
    lumi_t($language, $key, $replacements);
$h = static fn(string $value): string =>
    htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

$verificationMessages = [
    'invalid' => $t('verification_invalid'),
    'expired' => $t('verification_expired'),
    'error' => $t('verification_error'),
];
$verification = (string) ($_GET['verification'] ?? '');
$pageMessage = $verificationMessages[$verification] ?? '';
$hasSession = $user !== null;
?>
<!doctype html>
<html lang="<?= $h($locale) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#176b87">
    <meta name="description" content="<?= $h($t('meta_description')) ?>">
    <meta name="csrf-token" content="<?= $h(csrf_token()) ?>">
    <meta property="og:site_name" content="Lumi">
    <meta property="og:title" content="<?= $h($t('page_title')) ?>">
    <meta property="og:description" content="<?= $h($t('meta_description')) ?>">
    <meta property="og:image" content="<?= $h(app_url('assets/icons/lumi-icon-512.png')) ?>">
    <meta property="og:type" content="website">
    <title><?= $h($t('page_title')) ?></title>
    <link rel="canonical" href="<?= $h(app_url()) ?>">
    <link rel="icon" href="favicon.ico" sizes="any">
    <link rel="icon" type="image/png" sizes="48x48" href="assets/icons/lumi-icon-48.png">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/icons/apple-touch-icon.png">
    <link rel="manifest" href="site.webmanifest">
    <link rel="stylesheet" href="assets/css/site.css">
    <script src="assets/js/site.js" defer></script>
</head>
<body data-language="<?= $h($language) ?>">
    <a class="skip-link" href="#content"><?= $h($t('skip_content')) ?></a>

    <header class="site-header" data-header>
        <a class="wordmark" href="#home" aria-label="<?= $h($t('home_label')) ?>">
            <img class="brand-icon" src="assets/icons/lumi-icon-48.png" alt="">
            <span>Lumi</span>
        </a>
        <nav aria-label="<?= $h($t('nav_label')) ?>">
            <a href="#discover"><?= $h($t('nav_how')) ?></a>
            <a href="#safety"><?= $h($t('nav_safety')) ?></a>
        </nav>
        <div class="header-tools">
            <form class="language-form" method="get" action="index.php">
                <label>
                    <span class="sr-only"><?= $h($t('language_label')) ?></span>
                    <svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c3 3.5 3 14 0 18M12 3c-3 3.5-3 14 0 18"/></svg>
                    <select name="lang" data-language-select aria-label="<?= $h($t('language_label')) ?>">
                        <?php foreach (supported_languages() as $optionLanguage): ?>
                            <option value="<?= $h($optionLanguage) ?>"<?= $optionLanguage === $language ? ' selected' : '' ?>>
                                <?= $h(language_name($optionLanguage)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <noscript><button type="submit">OK</button></noscript>
            </form>
            <?php if ($hasSession): ?>
                <a class="header-action" href="app.php"><?= $h($t('continue')) ?></a>
            <?php else: ?>
                <button class="header-action" type="button" data-open-dialog="login-dialog"><?= $h($t('login')) ?></button>
            <?php endif; ?>
        </div>
    </header>

    <main id="content">
        <section class="hero" id="home" aria-labelledby="hero-title">
            <div class="hero-overlay" aria-hidden="true"></div>
            <div class="hero-content">
                <p class="hero-kicker"><?= $h($t('hero_kicker')) ?></p>
                <h1 id="hero-title">Lumi</h1>
                <p class="hero-copy"><?= $h($t('hero_copy')) ?></p>
                <div class="hero-actions">
                    <button class="button button-primary" type="button" data-open-dialog="register-dialog">
                        <?= $h($t('hero_start')) ?>
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </button>
                    <button class="button button-quiet" type="button" data-open-dialog="login-dialog"><?= $h($t('hero_access')) ?></button>
                </div>
            </div>
        </section>

        <section class="discovery-band" id="discover" aria-labelledby="discovery-title">
            <div class="section-inner">
                <div class="section-heading">
                    <p class="eyebrow"><?= $h($t('discovery_eyebrow')) ?></p>
                    <h2 id="discovery-title"><?= $h($t('discovery_title')) ?></h2>
                    <p><?= $h($t('discovery_intro')) ?></p>
                </div>

                <div class="discovery-steps">
                    <article>
                        <span class="step-number">01</span>
                        <div class="step-icon step-icon-coral" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M4 8h3l2-3h6l2 3h3v11H4z"/><circle cx="12" cy="13" r="3.5"/></svg>
                        </div>
                        <h3><?= $h($t('step_show')) ?></h3>
                        <p><?= $h($t('step_show_text')) ?></p>
                    </article>
                    <article>
                        <span class="step-number">02</span>
                        <div class="step-icon step-icon-blue" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><rect x="8" y="3" width="8" height="13" rx="4"/><path d="M5 11v1a7 7 0 0 0 14 0v-1M12 19v3"/></svg>
                        </div>
                        <h3><?= $h($t('step_ask')) ?></h3>
                        <p><?= $h($t('step_ask_text')) ?></p>
                    </article>
                    <article>
                        <span class="step-number">03</span>
                        <div class="step-icon step-icon-yellow" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M9 18h6M10 22h4M8.5 15.5A7 7 0 1 1 16 15.5c-1 1-1.5 1.5-1.5 2.5h-5c0-1-.3-1.5-1-2.5z"/></svg>
                        </div>
                        <h3><?= $h($t('step_discover')) ?></h3>
                        <p><?= $h($t('step_discover_text')) ?></p>
                    </article>
                </div>
            </div>
        </section>

        <section class="benefit-band" aria-labelledby="benefit-title">
            <div class="section-inner benefit-layout">
                <div class="benefit-copy">
                    <p class="eyebrow"><?= $h($t('benefit_eyebrow')) ?></p>
                    <h2 id="benefit-title"><?= $h($t('benefit_title')) ?></h2>
                    <p><?= $h($t('benefit_text')) ?></p>
                    <ul class="benefit-list">
                        <li><span aria-hidden="true">✓</span> <?= $h($t('benefit_one')) ?></li>
                        <li><span aria-hidden="true">✓</span> <?= $h($t('benefit_two')) ?></li>
                        <li><span aria-hidden="true">✓</span> <?= $h($t('benefit_three')) ?></li>
                    </ul>
                </div>
                <figure class="benefit-figure">
                    <img src="assets/images/lumi-thinking.png" alt="" loading="lazy">
                    <figcaption><?= $h($t('benefit_caption')) ?></figcaption>
                </figure>
            </div>
        </section>

        <section class="safety-band" id="safety" aria-labelledby="safety-title">
            <div class="section-inner safety-layout">
                <div class="safety-seal" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M12 3l7 3v5c0 4.5-2.8 8-7 10-4.2-2-7-5.5-7-10V6z"/><path d="M9 12l2 2 4-5"/></svg>
                </div>
                <div>
                    <p class="eyebrow"><?= $h($t('safety_eyebrow')) ?></p>
                    <h2 id="safety-title"><?= $h($t('safety_title')) ?></h2>
                    <p><?= $h($t('safety_text')) ?></p>
                    <a class="text-link" href="privacy.php?lang=<?= $h($language) ?>"><?= $h($t('privacy_link')) ?></a>
                </div>
            </div>
        </section>

        <section class="closing-band" aria-labelledby="closing-title">
            <div class="closing-content">
                <p class="eyebrow"><?= $h($t('closing_eyebrow')) ?></p>
                <h2 id="closing-title"><?= $h($t('closing_title')) ?></h2>
                <button class="button button-primary" type="button" data-open-dialog="register-dialog"><?= $h($t('closing_button')) ?></button>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <a class="wordmark wordmark-light" href="#home"><img class="brand-icon" src="assets/icons/lumi-icon-48.png" alt=""><span>Lumi</span></a>
        <p><?= $h($t('footer_text')) ?></p>
        <a href="privacy.php?lang=<?= $h($language) ?>"><?= $h($t('privacy')) ?></a>
    </footer>

    <dialog class="auth-dialog" id="login-dialog" aria-labelledby="login-title">
        <button class="dialog-close" type="button" data-close-dialog aria-label="<?= $h($t('close')) ?>">
            <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18"/></svg>
        </button>
        <div class="auth-dialog-heading">
            <img class="auth-brand-icon" src="assets/icons/lumi-icon-192.png" alt="">
            <p class="eyebrow"><?= $h($t('login_eyebrow')) ?></p>
            <h2 id="login-title"><?= $h($t('login_title')) ?></h2>
        </div>
        <form id="login-form" class="auth-form" novalidate>
            <label>
                <?= $h($t('adult_email')) ?>
                <input type="email" name="email" autocomplete="email" required>
            </label>
            <label>
                <?= $h($t('password')) ?>
                <input type="password" name="password" autocomplete="current-password" minlength="10" required>
            </label>
            <p class="form-message" data-form-message aria-live="polite"></p>
            <button class="button button-primary button-full" type="submit"><?= $h($t('login')) ?></button>
            <button class="text-button" type="button" data-resend hidden><?= $h($t('resend_confirmation')) ?></button>
        </form>
        <p class="dialog-switch"><?= $h($t('first_visit')) ?> <button type="button" data-switch-dialog="register-dialog"><?= $h($t('create_access')) ?></button></p>
    </dialog>

    <dialog class="auth-dialog auth-dialog-wide" id="register-dialog" aria-labelledby="register-title">
        <button class="dialog-close" type="button" data-close-dialog aria-label="<?= $h($t('close')) ?>">
            <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18"/></svg>
        </button>
        <div class="auth-dialog-heading">
            <img class="auth-brand-icon" src="assets/icons/lumi-icon-192.png" alt="">
            <p class="eyebrow"><?= $h($t('register_eyebrow')) ?></p>
            <h2 id="register-title"><?= $h($t('register_title')) ?></h2>
            <p><?= $h($t('register_intro')) ?></p>
        </div>
        <form id="register-form" class="auth-form" novalidate>
            <div class="form-grid">
                <label>
                    <?= $h($t('child_name')) ?>
                    <input type="text" name="display_name" maxlength="30" autocomplete="nickname" required>
                </label>
                <label>
                    <?= $h($t('age')) ?>
                    <input type="number" name="age" min="6" max="14" value="8" inputmode="numeric" required>
                </label>
            </div>
            <label>
                <?= $h($t('adult_email')) ?>
                <input type="email" name="email" autocomplete="email" required>
            </label>
            <label>
                <?= $h($t('create_password')) ?>
                <input type="password" name="password" autocomplete="new-password" minlength="10" required>
                <span class="field-hint"><?= $h($t('password_hint')) ?></span>
            </label>
            <label>
                <?= $h($t('preferred_language')) ?>
                <select name="language" required>
                    <?php foreach (supported_languages() as $optionLanguage): ?>
                        <option value="<?= $h($optionLanguage) ?>"<?= $optionLanguage === $language ? ' selected' : '' ?>>
                            <?= $h(language_name($optionLanguage)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="honeypot" aria-hidden="true">
                Site
                <input type="text" name="website" tabindex="-1" autocomplete="off">
            </label>
            <label class="consent-row">
                <input type="checkbox" name="consent" value="1" required>
                <span><?= $h($t('consent_before')) ?> <a href="privacy.php?lang=<?= $h($language) ?>"><?= $h($t('privacy_policy')) ?></a>.</span>
            </label>
            <p class="form-message" data-form-message aria-live="polite"></p>
            <button class="button button-primary button-full" type="submit"><?= $h($t('register_submit')) ?></button>
        </form>
        <p class="dialog-switch"><?= $h($t('already_access')) ?> <button type="button" data-switch-dialog="login-dialog"><?= $h($t('login')) ?></button></p>
    </dialog>

    <div class="page-notice<?= $pageMessage ? ' is-visible' : '' ?>" data-page-notice role="status">
        <?= $h($pageMessage) ?>
    </div>
</body>
</html>
