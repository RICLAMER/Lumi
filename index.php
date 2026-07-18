<?php

declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

$verificationMessages = [
    'invalid' => 'Esse link de confirmação não é válido.',
    'expired' => 'Esse link expirou. Entre e peça um novo e-mail de confirmação.',
    'error' => 'Não foi possível confirmar agora. Tente novamente em instantes.',
];
$verification = (string) ($_GET['verification'] ?? '');
$pageMessage = $verificationMessages[$verification] ?? '';
$hasSession = !empty($_SESSION['user_id']);
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#176b87">
    <meta name="description" content="Lumi transforma perguntas, voz e fotos em descobertas educativas apropriadas para cada idade.">
    <meta name="csrf-token" content="<?= htmlspecialchars(csrf_token(), ENT_QUOTES) ?>">
    <title>Lumi | Pergunte, pense e descubra</title>
    <link rel="icon" type="image/png" href="assets/images/lumi-camera.png">
    <link rel="stylesheet" href="assets/css/site.css">
    <script src="assets/js/site.js" defer></script>
</head>
<body>
    <a class="skip-link" href="#conteudo">Pular para o conteúdo</a>

    <header class="site-header" data-header>
        <a class="wordmark" href="#inicio" aria-label="Lumi, início">
            <span class="wordmark-mark" aria-hidden="true">L</span>
            <span>Lumi</span>
        </a>
        <nav aria-label="Navegação principal">
            <a href="#descobrir">Como funciona</a>
            <a href="#seguranca">Segurança</a>
        </nav>
        <?php if ($hasSession): ?>
            <a class="header-action" href="app.php">Continuar</a>
        <?php else: ?>
            <button class="header-action" type="button" data-open-dialog="login-dialog">Entrar</button>
        <?php endif; ?>
    </header>

    <main id="conteudo">
        <section class="hero" id="inicio" aria-labelledby="hero-title">
            <div class="hero-overlay" aria-hidden="true"></div>
            <div class="hero-content">
                <p class="hero-kicker">Uma companheira de descobertas para 6 a 14 anos</p>
                <h1 id="hero-title">Lumi</h1>
                <p class="hero-copy">Pergunte, fotografe e descubra. A gatinha exploradora transforma as coisas difíceis da escola e do dia a dia em explicações que cada criança consegue entender.</p>
                <div class="hero-actions">
                    <button class="button button-primary" type="button" data-open-dialog="register-dialog">
                        Começar uma descoberta
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </button>
                    <button class="button button-quiet" type="button" data-open-dialog="login-dialog">Já tenho acesso</button>
                </div>
            </div>
        </section>

        <section class="discovery-band" id="descobrir" aria-labelledby="discovery-title">
            <div class="section-inner">
                <div class="section-heading">
                    <p class="eyebrow">Curiosidade em movimento</p>
                    <h2 id="discovery-title">Difícil, só até a Lumi chegar.</h2>
                    <p>Uma descoberta começa com aquilo que a criança já tem por perto: uma dúvida, uma voz ou um objeto diante da câmera.</p>
                </div>

                <div class="discovery-steps">
                    <article>
                        <span class="step-number">01</span>
                        <div class="step-icon step-icon-coral" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M4 8h3l2-3h6l2 3h3v11H4z"/><circle cx="12" cy="13" r="3.5"/></svg>
                        </div>
                        <h3>Mostre</h3>
                        <p>Fotografe um objeto, animal, fruta ou planta que despertou curiosidade.</p>
                    </article>
                    <article>
                        <span class="step-number">02</span>
                        <div class="step-icon step-icon-blue" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><rect x="8" y="3" width="8" height="13" rx="4"/><path d="M5 11v1a7 7 0 0 0 14 0v-1M12 19v3"/></svg>
                        </div>
                        <h3>Pergunte</h3>
                        <p>Fale naturalmente. A Lumi escuta a pergunta e entende o assunto.</p>
                    </article>
                    <article>
                        <span class="step-number">03</span>
                        <div class="step-icon step-icon-yellow" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M9 18h6M10 22h4M8.5 15.5A7 7 0 1 1 16 15.5c-1 1-1.5 1.5-1.5 2.5h-5c0-1-.3-1.5-1-2.5z"/></svg>
                        </div>
                        <h3>Descubra</h3>
                        <p>Receba uma explicação em voz, com vocabulário e profundidade adequados à idade.</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="benefit-band" aria-labelledby="benefit-title">
            <div class="section-inner benefit-layout">
                <div class="benefit-copy">
                    <p class="eyebrow">Aprender cabe no cotidiano</p>
                    <h2 id="benefit-title">A escola encontra o mundo real.</h2>
                    <p>A Lumi aproxima Ciências, Matemática, Geografia, Português e outras matérias das coisas que a criança vê todos os dias.</p>
                    <ul class="benefit-list">
                        <li><span aria-hidden="true">✓</span> Explicações curtas e adaptadas à idade</li>
                        <li><span aria-hidden="true">✓</span> Respostas por voz para quem prefere ouvir</li>
                        <li><span aria-hidden="true">✓</span> Curiosidades que incentivam novas perguntas</li>
                    </ul>
                </div>
                <figure class="benefit-figure">
                    <img src="assets/images/lumi-thinking.png" alt="Lumi pensando em uma nova descoberta" loading="lazy">
                    <figcaption>Observar. Perguntar. Entender.</figcaption>
                </figure>
            </div>
        </section>

        <section class="safety-band" id="seguranca" aria-labelledby="safety-title">
            <div class="section-inner safety-layout">
                <div class="safety-seal" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M12 3l7 3v5c0 4.5-2.8 8-7 10-4.2-2-7-5.5-7-10V6z"/><path d="M9 12l2 2 4-5"/></svg>
                </div>
                <div>
                    <p class="eyebrow">Proteção faz parte da aprendizagem</p>
                    <h2 id="safety-title">Segura para explorar. Clara para os responsáveis.</h2>
                    <p>Fotos e áudios são usados apenas durante a descoberta e não ficam guardados. A Lumi bloqueia assuntos inadequados, evita dados pessoais e limita o uso diário para proteger as crianças e os créditos de IA.</p>
                    <a class="text-link" href="privacy.php">Ler o compromisso de privacidade</a>
                </div>
            </div>
        </section>

        <section class="closing-band" aria-labelledby="closing-title">
            <div class="closing-content">
                <p class="eyebrow">Um clique. Uma pergunta. Uma descoberta.</p>
                <h2 id="closing-title">O que vamos entender hoje?</h2>
                <button class="button button-primary" type="button" data-open-dialog="register-dialog">Conhecer a Lumi</button>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <a class="wordmark wordmark-light" href="#inicio"><span class="wordmark-mark">L</span><span>Lumi</span></a>
        <p>Uma experiência educacional com inteligência artificial. As vozes da Lumi são geradas por IA.</p>
        <a href="privacy.php">Privacidade</a>
    </footer>

    <dialog class="auth-dialog" id="login-dialog" aria-labelledby="login-title">
        <button class="dialog-close" type="button" data-close-dialog aria-label="Fechar">
            <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18"/></svg>
        </button>
        <div class="auth-dialog-heading">
            <span class="auth-symbol" aria-hidden="true">L</span>
            <p class="eyebrow">Que bom ver você</p>
            <h2 id="login-title">Entrar na Lumi</h2>
        </div>
        <form id="login-form" class="auth-form" novalidate>
            <label>
                E-mail do responsável
                <input type="email" name="email" autocomplete="email" required>
            </label>
            <label>
                Senha
                <input type="password" name="password" autocomplete="current-password" minlength="10" required>
            </label>
            <p class="form-message" data-form-message aria-live="polite"></p>
            <button class="button button-primary button-full" type="submit">Entrar</button>
            <button class="text-button" type="button" data-resend hidden>Reenviar confirmação</button>
        </form>
        <p class="dialog-switch">Primeira visita? <button type="button" data-switch-dialog="register-dialog">Criar acesso</button></p>
    </dialog>

    <dialog class="auth-dialog auth-dialog-wide" id="register-dialog" aria-labelledby="register-title">
        <button class="dialog-close" type="button" data-close-dialog aria-label="Fechar">
            <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18"/></svg>
        </button>
        <div class="auth-dialog-heading">
            <span class="auth-symbol auth-symbol-coral" aria-hidden="true">?</span>
            <p class="eyebrow">Comece com segurança</p>
            <h2 id="register-title">Criar acesso</h2>
            <p>O e-mail precisa ser de um adulto responsável. A criança pode usar apenas um nome ou apelido.</p>
        </div>
        <form id="register-form" class="auth-form" novalidate>
            <div class="form-grid">
                <label>
                    Como a criança quer ser chamada?
                    <input type="text" name="display_name" maxlength="30" autocomplete="nickname" required>
                </label>
                <label>
                    Idade
                    <input type="number" name="age" min="6" max="14" value="8" inputmode="numeric" required>
                </label>
            </div>
            <label>
                E-mail do responsável
                <input type="email" name="email" autocomplete="email" required>
            </label>
            <label>
                Crie uma senha
                <input type="password" name="password" autocomplete="new-password" minlength="10" required>
                <span class="field-hint">Use pelo menos 10 caracteres.</span>
            </label>
            <label class="honeypot" aria-hidden="true">
                Site
                <input type="text" name="website" tabindex="-1" autocomplete="off">
            </label>
            <label class="consent-row">
                <input type="checkbox" name="consent" value="1" required>
                <span>Sou responsável pela criança e concordo com o uso seguro descrito na <a href="privacy.php">Política de Privacidade</a>.</span>
            </label>
            <p class="form-message" data-form-message aria-live="polite"></p>
            <button class="button button-primary button-full" type="submit">Enviar confirmação por e-mail</button>
        </form>
        <p class="dialog-switch">Já tem acesso? <button type="button" data-switch-dialog="login-dialog">Entrar</button></p>
    </dialog>

    <div class="page-notice<?= $pageMessage ? ' is-visible' : '' ?>" data-page-notice role="status">
        <?= htmlspecialchars($pageMessage, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
    </div>
</body>
</html>
