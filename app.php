<?php

declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

$user = current_user();
if (!$user) {
    header('Location: index.php');
    exit;
}

$flash = (string) ($_SESSION['flash'] ?? '');
unset($_SESSION['flash']);
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#bce8f5">
    <meta name="csrf-token" content="<?= htmlspecialchars(csrf_token(), ENT_QUOTES) ?>">
    <meta name="description" content="Descubra o mundo com a Lumi por foto ou voz.">
    <title>Descobrir | Lumi</title>
    <link rel="icon" type="image/png" href="assets/images/lumi-camera.png">
    <link rel="stylesheet" href="assets/css/app.css">
    <script src="assets/js/app.js" defer></script>
</head>
<body
    class="lumi-app"
    data-user-name="<?= htmlspecialchars((string) $user['display_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
    data-user-age="<?= (int) $user['age'] ?>"
>
    <a class="skip-link" href="#lumi-stage">Pular para a descoberta</a>

    <header class="app-header">
        <a class="app-brand" href="app.php" aria-label="Lumi, início">
            <span class="app-brand-mark" aria-hidden="true">L</span>
            <span>Lumi</span>
        </a>
        <div class="app-header-actions">
            <button class="icon-button" type="button" data-music-toggle aria-label="Pausar música" title="Pausar música">
                <svg class="icon-sound-on" aria-hidden="true" viewBox="0 0 24 24"><path d="M4 10v4h4l5 4V6L8 10zM17 9a4 4 0 0 1 0 6M19 6a8 8 0 0 1 0 12"/></svg>
                <svg class="icon-sound-off" aria-hidden="true" viewBox="0 0 24 24"><path d="M4 10v4h4l5 4V6L8 10zM17 10l5 5M22 10l-5 5"/></svg>
            </button>
            <form action="logout.php" method="post">
                <input type="hidden" name="_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES) ?>">
                <button class="icon-button" type="submit" aria-label="Sair da conta" title="Sair da conta">
                    <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M10 5H5v14h5M14 8l4 4-4 4M8 12h10"/></svg>
                </button>
            </form>
        </div>
    </header>

    <main class="lumi-stage" id="lumi-stage">
        <div class="sky-shape sky-shape-one" aria-hidden="true"></div>
        <div class="sky-shape sky-shape-two" aria-hidden="true"></div>
        <section class="welcome-copy" aria-labelledby="welcome-title">
            <p class="welcome-kicker">Sua próxima descoberta começa aqui</p>
            <h1 id="welcome-title">Oi, <?= htmlspecialchars((string) $user['display_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>!</h1>
            <p>O que você quer entender hoje?</p>
        </section>

        <div class="discovery-scene">
            <div class="discovery-controls">
                <button class="voice-bubble" type="button" data-open-voice aria-label="Fazer uma pergunta por voz">
                    <img src="assets/icons/microphone.svg" alt="">
                    <span>Perguntar</span>
                </button>

                <button class="photo-cube" type="button" data-open-photo aria-label="Tirar uma foto para descobrir">
                    <span class="cube-face cube-front">?</span>
                    <span class="cube-face cube-top" aria-hidden="true"></span>
                    <span class="cube-face cube-side" aria-hidden="true"></span>
                    <span class="cube-label">Fotografar</span>
                </button>
            </div>

            <div class="lumi-character">
                <img src="assets/images/lumi-camera.png" alt="Lumi apontando sua câmera para a descoberta">
            </div>
        </div>

        <p class="ai-disclosure">
            <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 3l7 3v5c0 4.5-2.8 8-7 10-4.2-2-7-5.5-7-10V6z"/><path d="M9 12l2 2 4-5"/></svg>
            A Lumi usa IA e mantém fotos e áudios apenas durante esta descoberta.
        </p>
    </main>

    <audio src="assets/audio/music-sugar-pocket.mp3" data-background-music loop preload="auto"></audio>

    <dialog class="flow-dialog photo-dialog" id="photo-dialog" aria-labelledby="photo-title">
        <div class="flow-shell">
            <header class="flow-header">
                <div>
                    <p class="flow-kicker">CuboFoto</p>
                    <h2 id="photo-title">Mostre o seu mistério</h2>
                </div>
                <button class="icon-button icon-button-dark" type="button" data-close-photo aria-label="Sair">
                    <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18"/></svg>
                </button>
            </header>

            <div class="camera-stage" data-camera-stage>
                <video data-camera-video autoplay playsinline muted></video>
                <img data-photo-preview alt="Prévia da foto tirada" hidden>
                <div class="camera-empty" data-camera-empty>
                    <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 8h3l2-3h6l2 3h3v11H4z"/><circle cx="12" cy="13" r="3.5"/></svg>
                    <p>Aponte a câmera para um objeto, animal ou planta.</p>
                </div>
            </div>

            <p class="privacy-tip">Evite rostos, nomes, endereços, escola ou outras informações pessoais.</p>
            <input type="file" accept="image/jpeg,image/png,image/webp" capture="environment" data-photo-input hidden>

            <div class="flow-actions camera-actions">
                <button class="action-button action-secondary" type="button" data-choose-photo>
                    <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 5h16v14H4z"/><path d="M4 16l5-5 4 4 2-2 5 5"/><circle cx="16.5" cy="8.5" r="1.5"/></svg>
                    Galeria
                </button>
                <button class="capture-button" type="button" data-capture-photo aria-label="Tirar foto">
                    <span></span>
                </button>
                <button class="action-button action-primary" type="button" data-send-photo disabled>
                    <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 12l16-8-5 16-3-6z"/><path d="M12 14l8-10"/></svg>
                    Enviar
                </button>
            </div>
        </div>
    </dialog>

    <dialog class="flow-dialog voice-dialog" id="voice-dialog" aria-labelledby="voice-title">
        <div class="voice-shell">
            <button class="icon-button voice-close" type="button" data-close-voice aria-label="Sair">
                <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
            <div class="voice-heading">
                <p class="flow-kicker">Pergunta por voz</p>
                <h2 id="voice-title">Conte o que deixou você curioso</h2>
                <p data-recording-status>Toque no microfone para começar.</p>
            </div>

            <button class="record-button" type="button" data-record aria-label="Começar gravação">
                <img src="assets/icons/microphone.svg" alt="">
                <span class="record-ring" aria-hidden="true"></span>
            </button>

            <div class="recording-timer" data-recording-timer>00:00</div>
            <audio data-recording-preview controls hidden></audio>

            <div class="flow-actions voice-actions">
                <button class="action-button action-secondary" type="button" data-listen-recording disabled>
                    <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                    Ouvir
                </button>
                <button class="action-button action-primary" type="button" data-send-voice disabled>
                    <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 12l16-8-5 16-3-6z"/><path d="M12 14l8-10"/></svg>
                    Enviar
                </button>
                <button class="action-button action-ghost-light" type="button" data-close-voice>Sair</button>
            </div>
        </div>
    </dialog>

    <dialog class="flow-dialog processing-dialog" id="processing-dialog" aria-labelledby="processing-title">
        <div class="media-shell">
            <video src="assets/video/waiting-processing.mp4" data-processing-video playsinline loop></video>
            <div class="media-overlay"></div>
            <div class="processing-content">
                <div class="loading-dots" data-loading-dots aria-hidden="true"><span></span><span></span><span></span></div>
                <p class="flow-kicker">Lumi está investigando</p>
                <h2 id="processing-title" data-processing-title>Juntando as pistas...</h2>
                <p data-processing-message>Isso pode levar alguns segundos.</p>
                <div class="processing-actions">
                    <button class="action-button action-primary action-large" type="button" data-show-explanation hidden>
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        Explicação
                    </button>
                    <button class="action-button action-ghost-light" type="button" data-cancel-processing>Sair</button>
                </div>
            </div>
        </div>
    </dialog>

    <dialog class="flow-dialog explanation-dialog" id="explanation-dialog" aria-labelledby="explanation-title">
        <div class="media-shell explanation-shell">
            <video src="assets/video/explanation-talk.mp4" data-explanation-video playsinline loop muted></video>
            <div class="media-overlay explanation-overlay"></div>
            <div class="explanation-panel">
                <p class="flow-kicker" data-explanation-subject>Descoberta da Lumi</p>
                <h2 id="explanation-title" data-explanation-title>Olha o que eu descobri!</h2>
                <p class="explanation-text" data-explanation-text></p>
                <div class="explanation-meta">
                    <span data-explanation-school hidden></span>
                    <span data-explanation-curiosity hidden></span>
                </div>
                <p class="voice-disclosure">Voz da explicação gerada por inteligência artificial.</p>
                <div class="flow-actions explanation-actions">
                    <button class="action-button action-secondary" type="button" data-repeat-explanation>
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 12a8 8 0 1 0 3-6M4 4v6h6"/></svg>
                        Repetir
                    </button>
                    <button class="action-button action-primary" type="button" data-close-explanation>Sair</button>
                </div>
            </div>
            <audio data-explanation-audio></audio>
        </div>
    </dialog>

    <div class="toast<?= $flash ? ' is-visible' : '' ?>" data-toast role="status" aria-live="polite">
        <?= htmlspecialchars($flash, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
    </div>
</body>
</html>
