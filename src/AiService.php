<?php

declare(strict_types=1);

final class AiService
{
    private OpenAIClient $client;

    public function __construct()
    {
        $this->client = new OpenAIClient();
    }

    public function analyzeImage(string $jpegBytes, string $ageGroup, string $language): array
    {
        $language = normalize_language($language);
        $dataUrl = 'data:image/jpeg;base64,' . base64_encode($jpegBytes);
        if ($this->imageIsUnsafe($dataUrl)) {
            return $this->refusal($language);
        }

        $prompt = $this->basePrompt($ageGroup, $language) . "\n\n"
            . "Image task:\n"
            . "- Identify the main subject without identifying people or inferring sensitive traits.\n"
            . "- For an object: explain what it is, common materials, its purpose and how it is commonly acquired.\n"
            . "- For an animal: explain its group or species when confident, habitat and characteristics.\n"
            . "- For a plant, vegetation or fruit: explain its type, where it grows and its characteristics.\n"
            . "- If confidence is low, be honest and suggest taking a clearer photo.\n"
            . "- Refuse explanations involving sexual, violent, illegal or dangerous content, self-harm, "
            . "drugs, weapons, private data, or physical or moral risk to a child.";

        $result = $this->createExplanation([
            [
                'role' => 'user',
                'content' => [
                    ['type' => 'input_text', 'text' => $prompt],
                    ['type' => 'input_image', 'image_url' => $dataUrl, 'detail' => 'low'],
                ],
            ],
        ]);

        return $this->finalize($result, $ageGroup, $language);
    }

    public function answerVoice(
        string $audioPath,
        string $mimeType,
        string $ageGroup,
        string $language
    ): array
    {
        $language = normalize_language($language);
        $transcript = $this->transcribe($audioPath, $mimeType, $language);
        if ($transcript === '' || $this->textIsUnsafe($transcript)) {
            return $this->refusal($language, $transcript);
        }

        $prompt = $this->basePrompt($ageGroup, $language) . "\n\n"
            . "The child asked: " . $transcript . "\n\n"
            . "Answer the curiosity with reliable facts and one everyday example. "
            . "Refuse instructions or explanations involving sexual, violent, illegal or dangerous content, "
            . "self-harm, drugs, weapons, invasion of privacy, medical emergencies, or any physical or moral "
            . "risk to a child.";

        $result = $this->createExplanation([
            [
                'role' => 'user',
                'content' => [
                    ['type' => 'input_text', 'text' => $prompt],
                ],
            ],
        ]);

        $result['transcript'] = $transcript;
        return $this->finalize($result, $ageGroup, $language);
    }

    private function basePrompt(string $ageGroup, string $language): string
    {
        $length = match ($ageGroup) {
            '6-8' => 'Use 55 to 80 words, short sentences and one idea at a time.',
            '9-11' => 'Use 80 to 110 words, simple comparisons and explain school terms.',
            default => 'Use 100 to 140 words with a little more detail and critical thinking.',
        };

        $outputLanguage = match ($language) {
            'pt' => 'Brazilian Portuguese',
            'es' => 'neutral international Spanish',
            default => 'natural American English',
        };

        return "You are Lumi, an explorer kitten and educational companion for children.\n"
            . "The explanation is for ages {$ageGroup}. {$length}\n"
            . "Write every user-facing field in {$outputLanguage}, with a warm, curious, clear and respectful tone.\n"
            . "Do not include the child's name because the app will greet them locally.\n"
            . "Do not ask for a full name, address, school, phone, location or other personal data.\n"
            . "Do not identify people in images or infer sensitive traits.\n"
            . "Praise curiosity, but do not invent facts or validate incorrect claims.\n"
            . "The spoken_text field must stand alone as Lumi's complete spoken explanation.\n"
            . "If the topic is unsafe or inappropriate, use decision=refuse and keep the other fields brief.";
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
                    'enum' => ['object', 'animal', 'plant', 'food', 'question', 'other'],
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

    private function finalize(array $result, string $ageGroup, string $language): array
    {
        $spokenText = trim((string) ($result['spoken_text'] ?? ''));
        if (($result['decision'] ?? 'refuse') !== 'safe' || $spokenText === '') {
            return $this->refusal($language, $result['transcript'] ?? null);
        }
        if ($this->textIsUnsafe($spokenText)) {
            return $this->refusal($language, $result['transcript'] ?? null);
        }

        $audio = $this->client->binary('audio/speech', [
            'model' => (string) env('OPENAI_TTS_MODEL', 'gpt-4o-mini-tts'),
            'voice' => (string) env('OPENAI_TTS_VOICE', 'marin'),
            'input' => $spokenText,
            'instructions' => $this->voiceInstructions($ageGroup, $language),
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

    private function voiceInstructions(string $ageGroup, string $language): string
    {
        $pace = match ($ageGroup) {
            '6-8' => 'Speak slowly, separate sentences clearly and use excellent articulation.',
            '9-11' => 'Use a calm, natural pace and gently emphasize important words.',
            default => 'Use a natural, clear and curious pace without sounding overly childish.',
        };

        $spokenLanguage = match ($language) {
            'pt' => 'Brazilian Portuguese',
            'es' => 'neutral international Spanish',
            default => 'American English',
        };

        return "Speak in {$spokenLanguage} as a warm, upbeat young explorer. "
            . $pace . ' Do not sing and do not imitate a real person.';
    }

    private function transcribe(string $path, string $mimeType, string $language): string
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
            'language' => $language,
            'response_format' => 'json',
            'prompt' => match ($language) {
                'pt' => 'Pergunta curta de uma criança para a gatinha educacional Lumi.',
                'es' => 'Pregunta breve de un niño para la gatita educativa Lumi.',
                default => 'A short question from a child to the educational kitten Lumi.',
            },
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

    private function refusal(string $language, ?string $transcript = null): array
    {
        $language = normalize_language($language);
        return [
            'blocked' => true,
            'title' => lumi_t($language, 'refusal_title'),
            'category' => 'other',
            'spoken_text' => lumi_t($language, 'refusal_text'),
            'summary' => lumi_t($language, 'refusal_summary'),
            'school_subject' => '',
            'curiosity' => '',
            'safety_note' => lumi_t($language, 'refusal_note'),
            'transcript' => $transcript,
            'audio_url' => 'assets/audio/refusal-' . $language . '.mp3',
        ];
    }
}
