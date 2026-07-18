<?php

declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#176b87">
    <title>Privacidade | Lumi</title>
    <link rel="stylesheet" href="assets/css/site.css">
</head>
<body class="legal-page">
    <header class="site-header site-header-solid">
        <a class="wordmark" href="index.php"><span class="wordmark-mark">L</span><span>Lumi</span></a>
        <a class="header-action" href="index.php">Voltar</a>
    </header>
    <main class="legal-content">
        <p class="eyebrow">Compromisso de privacidade</p>
        <h1>Explorar com segurança vem primeiro.</h1>
        <p class="legal-intro">Esta primeira versão da Lumi foi desenhada para minimizar dados e manter um adulto responsável no processo de cadastro.</p>

        <section>
            <h2>Dados do cadastro</h2>
            <p>Guardamos o e-mail do responsável, um nome ou apelido para a criança, a idade, a senha protegida e o estado de confirmação da conta. Não pedimos nome completo, escola, telefone, endereço ou localização.</p>
        </section>
        <section>
            <h2>Fotos e gravações</h2>
            <p>Fotos e áudios são processados somente durante a descoberta. A foto é redimensionada e recriada antes da análise, removendo metadados como localização. Esses arquivos não são salvos pela Lumi depois da resposta.</p>
        </section>
        <section>
            <h2>Uso da inteligência artificial</h2>
            <p>Para reduzir a exposição de dados infantis, enviamos à OpenAI apenas a faixa etária, a pergunta ou a imagem necessária para a explicação. O nome e a idade exata não são enviados. O nome é pronunciado localmente pelo navegador.</p>
        </section>
        <section>
            <h2>Conteúdo apropriado</h2>
            <p>Entradas e respostas passam por filtros de segurança. A Lumi recusa temas que possam colocar a segurança física ou moral da criança em risco e orienta a procurar um adulto de confiança quando necessário.</p>
        </section>
        <section>
            <h2>Uso responsável</h2>
            <p>O acesso exige e-mail confirmado e há limite diário de descobertas. Isso reduz abuso automatizado e ajuda a controlar os custos da IA. A Lumi é uma ferramenta educacional e não substitui responsáveis, professores, médicos ou serviços de emergência.</p>
        </section>
        <p class="legal-updated">Versão publicada para o OpenAI Build Week, julho de 2026.</p>
    </main>
</body>
</html>
