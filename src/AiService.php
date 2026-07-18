<?php

declare(strict_types=1);

final class AiService
{
    private OpenAIClient $client;

    public function __construct()
    {
        $this->client = new OpenAIClient();
    }

    public function analyzeImage(string $jpegBytes, string $ageGroup): array
    {
        $dataUrl = 'data:image/jpeg;base64,' . base64_encode($jpegBytes);
        if ($this->imageIsUnsafe($dataUrl)) {
            return $this->refusal();
        }

        $prompt = $this->basePrompt($ageGroup) . "\n\n"
            . "Tarefa de imagem:\n"
            . "- Identifique o elemento principal sem identificar pessoas nem inferir dados sensíveis.\n"
            . "- Para objeto: diga o que é, materiais comuns, para que serve e como costuma ser adquirido.\n"
            . "- Para animal: diga o grupo ou espécie quando houver confiança, habitat e características.\n"
            . "- Para planta, vegetação ou fruta: diga o tipo, onde cresce e características.\n"
            . "- Se não houver confiança, diga isso com honestidade e peça uma foto melhor.\n"
            . "- Recuse qualquer explicação que possa expor conteúdo sexual, violento, ilegal, perigoso, "
            . "de automutilação, drogas, armas, dados privados ou risco físico/moral para uma criança.";

        $result = $this->createExplanation([
            [
                'role' => 'user',
                'content' => [
                    ['type' => 'input_text', 'text' => $prompt],
                    ['type' => 'input_image', 'image_url' => $dataUrl, 'detail' => 'low'],
                ],
            ],
        ]);

        return $this->finalize($result, $ageGroup);
    }

    public function answerVoice(string $audioPath, string $mimeType, string $ageGroup): array
    {
        $transcript = $this->transcribe($audioPath, $mimeType);
        if ($transcript === '' || $this->textIsUnsafe($transcript)) {
            return $this->refusal($transcript);
        }

        $prompt = $this->basePrompt($ageGroup) . "\n\n"
            . "Pergunta da criança: " . $transcript . "\n\n"
            . "Responda à curiosidade com fatos confiáveis e um exemplo do cotidiano. "
            . "Recuse instruções ou explicações com conteúdo sexual, violento, ilegal, perigoso, "
            . "de automutilação, drogas, armas, invasão de privacidade, emergências médicas ou "
            . "qualquer risco físico ou moral para uma criança.";

        $result = $this->createExplanation([
            [
                'role' => 'user',
                'content' => [
                    ['type' => 'input_text', 'text' => $prompt],
                ],
            ],
        ]);

        $result['transcript'] = $transcript;
        return $this->finalize($result, $ageGroup);
    }

    private function basePrompt(string $ageGroup): string
    {
        $length = match ($ageGroup) {
            '6 a 8 anos' => 'Use de 55 a 80 palavras, frases curtas e uma ideia por vez.',
            '9 a 11 anos' => 'Use de 80 a 110 palavras, comparações simples e termos escolares explicados.',
            default => 'Use de 100 a 140 palavras, com um pouco mais de detalhe e pensamento crítico.',
        };

        return "Você é Lumi, uma gatinha exploradora e companheira educacional para crianças.\n"
            . "A explicação é para a faixa de {$ageGroup}. {$length}\n"
            . "Fale em português do Brasil, de forma calorosa, curiosa, clara e respeitosa.\n"
            . "Não inclua o nome da criança: o aplicativo fará a saudação localmente.\n"
            . "Não peça nome completo, endereço, escola, telefone, localização ou outros dados pessoais.\n"
            . "Não identifique pessoas em imagens e não faça inferências sensíveis.\n"
            . "Elogie a curiosidade, mas não invente fatos e não valide algo incorreto.\n"
            . "O campo spoken_text deve funcionar sozinho como a fala completa da Lumi.\n"
            . "Se o tema não for seguro e apropriado, use decision=refuse e deixe os demais textos breves.";
    }

    private function createExplanation(array $input): array
    {
        $schema = [
            'type' => 'object',
            'properties' => [
                'decision' => ['type' => 'string', 'enum' => ['safe', 'refuse']],
                'title' => ['type' => 'string'],
                'category' => [
                    'type' => 'string',
                    'enum' => ['objeto', 'animal', 'planta', 'alimento', 'pergunta', 'outro'],
                ],
                'spoken_text' => ['type' => 'string'],
                'summary' => ['type' => 'string'],
                'school_subject' => ['type' => 'string'],
                'curiosity' => ['type' => 'string'],
                'safety_note' => ['type' => 'string'],
            ],
            'required' => [
                'decision', 'title', 'category', 'spoken_text', 'summary',
                'school_subject', 'curiosity', 'safety_note',
            ],
            'additionalProperties' => false,
        ];

        $response = $this->client->json('responses', [
            'model' => (string) env('OPENAI_MODEL', 'gpt-5.6-luna'),
            'input' => $input,
            'reasoning' => ['effort' => 'low'],
            'max_output_tokens' => 1000,
            'text' => [
                'format' => [
                    'type' => 'json_schema',
                    'name' => 'lumi_explanation',
                    'strict' => true,
                    'schema' => $schema,
                ],
            ],
        ]);

        $text = $this->extractOutputText($response);
        $result = json_decode($text, true);
        if (!is_array($result) || !isset($result['decision'], $result['spoken_text'])) {
            throw new RuntimeException('A Lumi não conseguiu organizar a explicação.');
        }

        return $result;
    }

    private function extractOutputText(array $response): string
    {
        if (isset($response['output_text']) && is_string($response['output_text'])) {
            return $response['output_text'];
        }

        foreach ($response['output'] ?? [] as $item) {
            if (($item['type'] ?? '') !== 'message') {
                continue;
            }
            foreach ($item['content'] ?? [] as $content) {
                if (($content['type'] ?? '') === 'output_text' && isset($content['text'])) {
                    return (string) $content['text'];
                }
            }
        }

        throw new RuntimeException('A Lumi não encontrou uma explicação na resposta.');
    }

    private function finalize(array $result, string $ageGroup): array
    {
        $spokenText = trim((string) ($result['spoken_text'] ?? ''));
        if (($result['decision'] ?? 'refuse') !== 'safe' || $spokenText === '') {
            return $this->refusal($result['transcript'] ?? null);
        }
        if ($this->textIsUnsafe($spokenText)) {
            return $this->refusal($result['transcript'] ?? null);
        }

        $audio = $this->client->binary('audio/speech', [
            'model' => (string) env('OPENAI_TTS_MODEL', 'gpt-4o-mini-tts'),
            'voice' => (string) env('OPENAI_TTS_VOICE', 'marin'),
            'input' => $spokenText,
            'instructions' => $this->voiceInstructions($ageGroup),
            'response_format' => 'mp3',
        ]);

        return [
            'blocked' => false,
            'title' => trim((string) $result['title']),
            'category' => (string) $result['category'],
            'spoken_text' => $spokenText,
            'summary' => trim((string) $result['summary']),
            'school_subject' => trim((string) $result['school_subject']),
            'curiosity' => trim((string) $result['curiosity']),
            'safety_note' => trim((string) $result['safety_note']),
            'transcript' => $result['transcript'] ?? null,
            'audio_data_url' => 'data:audio/mpeg;base64,' . base64_encode($audio),
        ];
    }

    private function voiceInstructions(string $ageGroup): string
    {
        $pace = match ($ageGroup) {
            '6 a 8 anos' => 'Fale devagar, com frases bem separadas e muita clareza.',
            '9 a 11 anos' => 'Fale em ritmo calmo e natural, destacando as palavras importantes.',
            default => 'Fale em ritmo natural, claro e curioso, sem infantilizar.',
        };

        return 'Fale em português do Brasil como uma exploradora jovem, acolhedora e animada. '
            . $pace . ' Não cante e não imite uma pessoa real.';
    }

    private function transcribe(string $path, string $mimeType): string
    {
        $extension = match ($mimeType) {
            'audio/mpeg', 'audio/mp3' => 'mp3',
            'audio/mp4', 'video/mp4' => 'm4a',
            'audio/ogg' => 'ogg',
            'audio/wav', 'audio/x-wav' => 'wav',
            default => 'webm',
        };

        $response = $this->client->multipart('audio/transcriptions', [
            'file' => new CURLFile($path, $mimeType, 'pergunta.' . $extension),
            'model' => (string) env('OPENAI_TRANSCRIBE_MODEL', 'gpt-4o-mini-transcribe'),
            'language' => 'pt',
            'response_format' => 'json',
            'prompt' => 'Pergunta curta de uma criança brasileira para a gatinha educacional Lumi.',
        ]);

        return trim((string) ($response['text'] ?? ''));
    }

    private function textIsUnsafe(string $text): bool
    {
        $response = $this->client->json('moderations', [
            'model' => (string) env('OPENAI_MODERATION_MODEL', 'omni-moderation-latest'),
            'input' => $text,
        ], 45);

        return $this->moderationUnsafe($response);
    }

    private function imageIsUnsafe(string $dataUrl): bool
    {
        $response = $this->client->json('moderations', [
            'model' => (string) env('OPENAI_MODERATION_MODEL', 'omni-moderation-latest'),
            'input' => [
                ['type' => 'text', 'text' => 'Imagem enviada em um aplicativo educacional infantil.'],
                ['type' => 'image_url', 'image_url' => ['url' => $dataUrl]],
            ],
        ], 45);

        return $this->moderationUnsafe($response);
    }

    private function moderationUnsafe(array $response): bool
    {
        $result = $response['results'][0] ?? [];
        if (!empty($result['flagged'])) {
            return true;
        }

        $scores = $result['category_scores'] ?? [];
        $strictThresholds = [
            'sexual/minors' => 0.001,
            'self-harm/instructions' => 0.005,
            'self-harm/intent' => 0.01,
            'violence/graphic' => 0.02,
            'illicit/violent' => 0.02,
        ];

        foreach ($strictThresholds as $category => $threshold) {
            if ((float) ($scores[$category] ?? 0) >= $threshold) {
                return true;
            }
        }

        return false;
    }

    private function refusal(?string $transcript = null): array
    {
        return [
            'blocked' => true,
            'title' => 'Vamos escolher outro mistério',
            'category' => 'outro',
            'spoken_text' => 'Não posso ajudar com isso. Vamos escolher outro assunto seguro para aprender.',
            'summary' => 'Esse assunto não é apropriado para a Lumi explicar.',
            'school_subject' => '',
            'curiosity' => '',
            'safety_note' => 'Se algo deixou você com medo ou em risco, procure um adulto de confiança.',
            'transcript' => $transcript,
            'audio_url' => 'assets/audio/refusal.mp3',
        ];
    }
}
