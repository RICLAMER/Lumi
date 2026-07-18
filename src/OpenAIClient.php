<?php

declare(strict_types=1);

final class OpenAIClient
{
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = trim((string) env('OPENAI_API_KEY', ''));
        if ($this->apiKey === '') {
            throw new RuntimeException('A inteligência da Lumi ainda não está configurada.');
        }
        if (!function_exists('curl_init')) {
            throw new RuntimeException('A hospedagem não possui o módulo HTTP necessário.');
        }
    }

    public function json(string $path, array $payload, int $timeout = 90): array
    {
        $body = json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        );
        $curl = curl_init('https://api.openai.com/v1/' . ltrim($path, '/'));
        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => $body,
        ]);

        return $this->executeJson($curl);
    }

    public function multipart(string $path, array $fields, int $timeout = 90): array
    {
        $curl = curl_init('https://api.openai.com/v1/' . ltrim($path, '/'));
        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $this->apiKey],
            CURLOPT_POSTFIELDS => $fields,
        ]);

        return $this->executeJson($curl);
    }

    public function binary(string $path, array $payload, int $timeout = 90): string
    {
        $body = json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        );
        $curl = curl_init('https://api.openai.com/v1/' . ltrim($path, '/'));
        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => $body,
        ]);

        $response = curl_exec($curl);
        if ($response === false) {
            $message = curl_error($curl);
            curl_close($curl);
            throw new RuntimeException('A Lumi não conseguiu se conectar agora: ' . $message);
        }

        $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $contentType = (string) curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
        curl_close($curl);

        if ($status < 200 || $status >= 300 || str_contains($contentType, 'application/json')) {
            throw new RuntimeException('A voz da Lumi não pôde ser preparada agora.');
        }

        return (string) $response;
    }

    private function executeJson($curl): array
    {
        $response = curl_exec($curl);
        if ($response === false) {
            $message = curl_error($curl);
            curl_close($curl);
            throw new RuntimeException('A Lumi não conseguiu se conectar agora: ' . $message);
        }

        $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);

        $decoded = json_decode((string) $response, true);
        if ($status < 200 || $status >= 300) {
            $code = is_array($decoded) ? ($decoded['error']['code'] ?? 'api_error') : 'api_error';
            log_event('openai_error', ['status' => $status, 'code' => (string) $code]);
            throw new RuntimeException('A Lumi não conseguiu preparar a resposta agora.');
        }
        if (!is_array($decoded)) {
            throw new RuntimeException('A Lumi recebeu uma resposta inesperada.');
        }

        return $decoded;
    }
}
