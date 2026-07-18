<?php

declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

$user = current_user();
$language = $user
    ? set_language_preference((string) $user['language'])
    : interface_language();
$locale = language_locale($language);
$copy = [
    'en' => [
        'title' => 'Privacy | Lumi',
        'back' => 'Back',
        'eyebrow' => 'Privacy commitment',
        'heading' => 'Exploring safely comes first.',
        'intro' => 'This first Lumi version was designed to minimize data and keep an adult caregiver involved in account creation.',
        'sections' => [
            ['Account data', 'We keep the caregiver email, a first name or nickname for the child, age, preferred language, protected password and account confirmation status. We do not request a full name, school, phone, address or location.'],
            ['Photos and recordings', 'Photos and audio are processed only during a discovery. The photo is resized and recreated before analysis, removing metadata such as location. Lumi does not save these files after the response.'],
            ['Artificial intelligence', 'To reduce children’s data exposure, we send OpenAI only the age group, preferred language, question or image needed for the explanation. The name and exact age are not sent. The browser speaks the name locally.'],
            ['Appropriate content', 'Inputs and responses pass through safety filters. Lumi refuses topics that could put a child’s physical or moral safety at risk and encourages talking to a trusted adult when needed.'],
            ['Responsible use', 'Access requires a confirmed email. Separate daily limits apply to photos and audio questions, and account requests are limited by a protected hash of the IP address. Lumi is an educational tool and does not replace caregivers, teachers, doctors or emergency services.'],
        ],
        'updated' => 'Version published for OpenAI Build Week, July 2026.',
    ],
    'pt' => [
        'title' => 'Privacidade | Lumi',
        'back' => 'Voltar',
        'eyebrow' => 'Compromisso de privacidade',
        'heading' => 'Explorar com segurança vem primeiro.',
        'intro' => 'Esta primeira versão da Lumi foi desenhada para minimizar dados e manter um adulto responsável no processo de cadastro.',
        'sections' => [
            ['Dados do cadastro', 'Guardamos o e-mail do responsável, um nome ou apelido para a criança, a idade, o idioma preferido, a senha protegida e o estado de confirmação da conta. Não pedimos nome completo, escola, telefone, endereço ou localização.'],
            ['Fotos e gravações', 'Fotos e áudios são processados somente durante a descoberta. A foto é redimensionada e recriada antes da análise, removendo metadados como localização. Esses arquivos não são salvos pela Lumi depois da resposta.'],
            ['Uso da inteligência artificial', 'Para reduzir a exposição de dados infantis, enviamos à OpenAI apenas a faixa etária, o idioma preferido, a pergunta ou a imagem necessária para a explicação. O nome e a idade exata não são enviados. O nome é pronunciado localmente pelo navegador.'],
            ['Conteúdo apropriado', 'Entradas e respostas passam por filtros de segurança. A Lumi recusa temas que possam colocar a segurança física ou moral da criança em risco e orienta a procurar um adulto de confiança quando necessário.'],
            ['Uso responsável', 'O acesso exige e-mail confirmado. Há limites diários separados para fotos e perguntas de áudio, e as solicitações de cadastro são limitadas por um hash protegido do IP. A Lumi é uma ferramenta educacional e não substitui responsáveis, professores, médicos ou serviços de emergência.'],
        ],
        'updated' => 'Versão publicada para o OpenAI Build Week, julho de 2026.',
    ],
    'es' => [
        'title' => 'Privacidad | Lumi',
        'back' => 'Volver',
        'eyebrow' => 'Compromiso de privacidad',
        'heading' => 'Explorar con seguridad es lo primero.',
        'intro' => 'Esta primera versión de Lumi fue diseñada para minimizar los datos y mantener a un adulto responsable en el proceso de registro.',
        'sections' => [
            ['Datos de la cuenta', 'Guardamos el correo del responsable, un nombre o apodo del niño, la edad, el idioma preferido, la contraseña protegida y el estado de confirmación. No pedimos nombre completo, escuela, teléfono, dirección ni ubicación.'],
            ['Fotos y grabaciones', 'Las fotos y los audios se procesan solo durante el descubrimiento. La foto se redimensiona y se recrea antes del análisis, eliminando metadatos como la ubicación. Lumi no guarda estos archivos después de responder.'],
            ['Inteligencia artificial', 'Para reducir la exposición de datos infantiles, enviamos a OpenAI solo el grupo de edad, el idioma preferido, la pregunta o la imagen necesaria. No se envían el nombre ni la edad exacta. El navegador pronuncia el nombre localmente.'],
            ['Contenido apropiado', 'Las entradas y respuestas pasan por filtros de seguridad. Lumi rechaza temas que puedan poner en riesgo la seguridad física o moral del niño y recomienda hablar con un adulto de confianza cuando sea necesario.'],
            ['Uso responsable', 'El acceso requiere un correo confirmado. Hay límites diarios separados para fotos y preguntas de audio, y las solicitudes de registro se limitan mediante un hash protegido de la IP. Lumi es una herramienta educativa y no sustituye a responsables, profesores, médicos ni servicios de emergencia.'],
        ],
        'updated' => 'Versión publicada para OpenAI Build Week, julio de 2026.',
    ],
][$language];
$h = static fn(string $value): string =>
    htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<!doctype html>
<html lang="<?= $h($locale) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#176b87">
    <title><?= $h($copy['title']) ?></title>
    <link rel="icon" href="favicon.ico" sizes="any">
    <link rel="icon" type="image/png" sizes="48x48" href="assets/icons/lumi-icon-48.png">
    <link rel="stylesheet" href="assets/css/site.css">
</head>
<body class="legal-page">
    <header class="site-header site-header-solid">
        <a class="wordmark" href="index.php?lang=<?= $h($language) ?>"><img class="brand-icon" src="assets/icons/lumi-icon-48.png" alt=""><span>Lumi</span></a>
        <a class="header-action" href="index.php?lang=<?= $h($language) ?>"><?= $h($copy['back']) ?></a>
    </header>
    <main class="legal-content">
        <p class="eyebrow"><?= $h($copy['eyebrow']) ?></p>
        <h1><?= $h($copy['heading']) ?></h1>
        <p class="legal-intro"><?= $h($copy['intro']) ?></p>

        <?php foreach ($copy['sections'] as [$heading, $text]): ?>
            <section>
                <h2><?= $h($heading) ?></h2>
                <p><?= $h($text) ?></p>
            </section>
        <?php endforeach; ?>

        <p class="legal-updated"><?= $h($copy['updated']) ?></p>
    </main>
</body>
</html>
