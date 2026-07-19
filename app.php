<?php

declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

$user = current_user();
if (!$user) {
    header('Location: index.php');
    exit;
}

$language = set_language_preference((string) $user['language']);
$locale = language_locale($language);
$usage = ai_usage_summary($user);
$t = static fn(string $key, array $replacements = []): string =>
    lumi_t($language, $key, $replacements);
$h = static fn(string $value): string =>
    htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

$flash = (string) ($_SESSION['flash'] ?? '');
unset($_SESSION['flash']);
?>
<!doctype html>
<html lang="<?= $h($locale) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#bce8f5">
    <meta name="csrf-token" content="<?= $h(csrf_token()) ?>">
    <meta name="description" content="<?= $h($t('app_meta_description')) ?>">
    <title><?= $h($t('app_title')) ?></title>
    <link rel="icon" href="favicon.ico" sizes="any">
    <link rel="icon" type="image/png" sizes="48x48" href="assets/icons/lumi-icon-48.png">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/icons/apple-touch-icon.png">
    <link rel="manifest" href="site.webmanifest">
    <link rel="stylesheet" href="assets/css/app.css">
    <script src="assets/js/app.js" defer></script>
</head>
<body
    class="lumi-app"
    data-user-language="<?= $h($language) ?>"
>
    <a class="skip-link" href="#lumi-stage"><?= $h($t('skip_discovery')) ?></a>

    <header class="app-header">
        <a class="app-brand" href="app.php" aria-label="<?= $h($t('home_label')) ?>">
            <img class="app-brand-icon" src="assets/icons/lumi-icon-48.png" alt="">
            <span>Lumi</span>
        </a>
        <div class="app-header-actions">
            <?php if ($user['is_tester']): ?>
                <a class="icon-button" href="Portal.php" aria-label="Portal" title="Portal">
                    <svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a2 2 0 0 0 .4 2.2l.1.1-2.6 2.6-.1-.1A2 2 0 0 0 15 19.4a2 2 0 0 0-1.2 1.8V21h-3.6v-.2A2 2 0 0 0 9 19.4a2 2 0 0 0-2.2.4l-.1.1-2.6-2.6.1-.1A2 2 0 0 0 4.6 15a2 2 0 0 0-1.8-1.2H2v-3.6h.8A2 2 0 0 0 4.6 9a2 2 0 0 0-.4-2.2l-.1-.1 2.6-2.6.1.1A2 2 0 0 0 9 4.6a2 2 0 0 0 1.2-1.8V2h3.6v.8A2 2 0 0 0 15 4.6a2 2 0 0 0 2.2-.4l.1-.1 2.6 2.6-.1.1A2 2 0 0 0 19.4 9a2 2 0 0 0 1.8 1.2h.8v3.6h-.8A2 2 0 0 0 19.4 15z"/></svg>
                </a>
            <?php endif; ?>
            <label class="app-language-picker" title="<?= $h($t('language_label')) ?>">
                <span class="sr-only"><?= $h($t('language_label')) ?></span>
                <select data-app-language aria-label="<?= $h($t('language_label')) ?>">
                    <?php foreach (supported_languages() as $optionLanguage): ?>
                        <option value="<?= $h($optionLanguage) ?>"<?= $optionLanguage === $language ? ' selected' : '' ?>>
                            <?= strtoupper($h($optionLanguage)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button class="icon-button" type="button" data-music-toggle aria-label="<?= $h($t('pause_music')) ?>" title="<?= $h($t('pause_music')) ?>">
                <svg class="icon-sound-on" aria-hidden="true" viewBox="0 0 24 24"><path d="M4 10v4h4l5 4V6L8 10zM17 9a4 4 0 0 1 0 6M19 6a8 8 0 0 1 0 12"/></svg>
                <svg class="icon-sound-off" aria-hidden="true" viewBox="0 0 24 24"><path d="M4 10v4h4l5 4V6L8 10zM17 10l5 5M22 10l-5 5"/></svg>
            </button>
            <form action="logout.php" method="post">
                <input type="hidden" name="_token" value="<?= $h(csrf_token()) ?>">
                <button class="icon-button" type="submit" aria-label="<?= $h($t('logout')) ?>" title="<?= $h($t('logout')) ?>">
                    <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M10 5H5v14h5M14 8l4 4-4 4M8 12h10"/></svg>
                </button>
            </form>
        </div>
    </header>

    <main class="lumi-stage" id="lumi-stage">
        <div class="sky-shape sky-shape-one" aria-hidden="true"></div>
        <div class="sky-shape sky-shape-two" aria-hidden="true"></div>
        <section class="welcome-copy" aria-labelledby="welcome-title">
            <p class="welcome-kicker"><?= $h($t('welcome_kicker')) ?></p>
            <h1 id="welcome-title"><?= $h($t('hello', ['name' => (string) $user['display_name']])) ?></h1>
            <p><?= $h($t('what_today')) ?></p>
        </section>

        <div class="discovery-scene">
            <div class="discovery-controls">
                <button class="voice-bubble" type="button" data-open-voice aria-label="<?= $h($t('ask_voice')) ?>">
                    <img src="assets/icons/microphone.svg" alt="">
                    <span><?= $h($t('ask')) ?></span>
                </button>

                <button class="photo-cube" type="button" data-open-photo aria-label="<?= $h($t('take_photo')) ?>">
                    <span class="cube-face cube-front">?</span>
                    <span class="cube-face cube-top" aria-hidden="true"></span>
                    <span class="cube-face cube-side" aria-hidden="true"></span>
                    <span class="cube-label"><?= $h($t('photograph')) ?></span>
                </button>
            </div>

            <div class="lumi-character">
                <img src="assets/images/lumi-camera.png" alt="">
            </div>
        </div>

        <p class="ai-disclosure">
            <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 3l7 3v5c0 4.5-2.8 8-7 10-4.2-2-7-5.5-7-10V6z"/><path d="M9 12l2 2 4-5"/></svg>
            <?= $h($t('ai_disclosure')) ?>
        </p>
    </main>

    <audio src="assets/audio/music-sugar-pocket.mp3" data-background-music loop preload="auto"></audio>

    <dialog class="flow-dialog photo-dialog" id="photo-dialog" aria-labelledby="photo-title">
        <div class="flow-shell">
            <header class="flow-header">
                <div>
                    <p class="flow-kicker"><?= $h($t('photo_kicker')) ?></p>
                    <h2 id="photo-title"><?= $h($t('photo_title')) ?></h2>
                    <p
                        class="usage-meter"
                        data-usage-image
                        data-used="<?= $usage['image']['used'] ?>"
                        data-limit="<?= $usage['image']['limit'] ?>"
                    ><?= $h($t('photo_usage', $usage['image'])) ?></p>
                </div>
                <button class="icon-button icon-button-dark" type="button" data-close-photo aria-label="<?= $h($t('exit')) ?>">
                    <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18"/></svg>
                </button>
            </header>

            <div class="camera-stage" data-camera-stage>
                <video data-camera-video autoplay playsinline muted></video>
                <img data-photo-preview alt="<?= $h($t('photo_preview')) ?>" hidden>
                <div class="camera-empty" data-camera-empty>
                    <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 8h3l2-3h6l2 3h3v11H4z"/><circle cx="12" cy="13" r="3.5"/></svg>
                    <p><?= $h($t('camera_hint')) ?></p>
                </div>
            </div>

            <p class="privacy-tip"><?= $h($t('privacy_tip')) ?></p>
            <input type="file" accept="image/jpeg,image/png,image/webp" capture="environment" data-photo-input hidden>

            <div class="flow-actions camera-actions">
                <button class="action-button action-secondary" type="button" data-choose-photo>
                    <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 5h16v14H4z"/><path d="M4 16l5-5 4 4 2-2 5 5"/><circle cx="16.5" cy="8.5" r="1.5"/></svg>
                    <?= $h($t('gallery')) ?>
                </button>
                <button class="capture-button" type="button" data-capture-photo aria-label="<?= $h($t('capture_photo')) ?>">
                    <span></span>
                </button>
                <button class="action-button action-primary" type="button" data-send-photo disabled>
                    <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 12l16-8-5 16-3-6z"/><path d="M12 14l8-10"/></svg>
                    <?= $h($t('send')) ?>
                </button>
            </div>
        </div>
    </dialog>

    <dialog class="flow-dialog voice-dialog" id="voice-dialog" aria-labelledby="voice-title">
        <div class="voice-shell">
            <button class="icon-button voice-close" type="button" data-close-voice aria-label="<?= $h($t('exit')) ?>">
                <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
            <div class="voice-heading">
                <p class="flow-kicker"><?= $h($t('voice_kicker')) ?></p>
                <h2 id="voice-title"><?= $h($t('voice_title')) ?></h2>
                <p
                    class="usage-meter usage-meter-light"
                    data-usage-voice
                    data-used="<?= $usage['voice']['used'] ?>"
                    data-limit="<?= $usage['voice']['limit'] ?>"
                ><?= $h($t('voice_usage', $usage['voice'])) ?></p>
                <p data-recording-status><?= $h($t('record_start')) ?></p>
            </div>

            <button class="record-button" type="button" data-record aria-label="<?= $h($t('record_label_start')) ?>">
                <img src="assets/icons/microphone.svg" alt="">
                <span class="record-ring" aria-hidden="true"></span>
            </button>

            <div class="recording-timer" data-recording-timer>00:00</div>
            <audio data-recording-preview controls hidden></audio>

            <div class="flow-actions voice-actions">
                <button class="action-button action-secondary" type="button" data-listen-recording disabled>
                    <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                    <?= $h($t('listen')) ?>
                </button>
                <button class="action-button action-primary" type="button" data-send-voice disabled>
                    <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 12l16-8-5 16-3-6z"/><path d="M12 14l8-10"/></svg>
                    <?= $h($t('send')) ?>
                </button>
                <button class="action-button action-ghost-light" type="button" data-close-voice><?= $h($t('exit')) ?></button>
            </div>
        </div>
    </dialog>

    <dialog class="flow-dialog processing-dialog" id="processing-dialog" aria-labelledby="processing-title">
        <div class="media-shell">
            <video src="assets/video/waiting-processing.mp4" data-processing-video playsinline loop></video>
            <div class="media-overlay"></div>
            <div class="processing-content">
                <div class="loading-dots" data-loading-dots aria-hidden="true"><span></span><span></span><span></span></div>
                <p class="flow-kicker"><?= $h($t('processing_kicker')) ?></p>
                <h2 id="processing-title" data-processing-title><?= $h($t('processing_title')) ?></h2>
                <p data-processing-message><?= $h($t('processing_message')) ?></p>
                <div class="processing-actions">
                    <button class="action-button action-primary action-large" type="button" data-show-explanation disabled>
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        <?= $h($t('explanation')) ?>
                    </button>
                    <button class="action-button action-ghost-light" type="button" data-cancel-processing><?= $h($t('exit')) ?></button>
                </div>
            </div>
        </div>
    </dialog>

    <dialog class="flow-dialog explanation-dialog" id="explanation-dialog" aria-labelledby="explanation-title">
        <div class="media-shell explanation-shell">
            <video src="assets/video/explanation-talk.mp4" data-explanation-video playsinline loop muted></video>
            <div class="media-overlay explanation-overlay"></div>
            <div class="explanation-panel">
                <p class="flow-kicker" data-explanation-subject><?= $h($t('explanation_subject')) ?></p>
                <h2 id="explanation-title" data-explanation-title><?= $h($t('explanation_title')) ?></h2>
                <p class="explanation-text" data-explanation-text></p>
                <div class="explanation-meta">
                    <span data-explanation-school hidden></span>
                    <span data-explanation-curiosity hidden></span>
                </div>
                <p class="voice-disclosure"><?= $h($t('voice_disclosure')) ?></p>
                <div class="flow-actions explanation-actions">
                    <button class="action-button action-secondary" type="button" data-repeat-explanation>
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 12a8 8 0 1 0 3-6M4 4v6h6"/></svg>
                        <?= $h($t('repeat')) ?>
                    </button>
                    <button class="action-button action-primary" type="button" data-close-explanation><?= $h($t('exit')) ?></button>
                </div>
            </div>
            <audio data-explanation-audio></audio>
        </div>
    </dialog>

    <div class="toast<?= $flash ? ' is-visible' : '' ?>" data-toast role="status" aria-live="polite">
        <?= $h($flash) ?>
    </div>
</body>
</html>
