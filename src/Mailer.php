<?php

declare(strict_types=1);

final class Mailer
{
    public function sendVerification(
        string $to,
        string $displayName,
        string $verificationUrl,
        string $language
    ): void
    {
        $language = normalize_language($language);
        $copy = [
            'en' => [
                'subject' => 'Confirm your Lumi access',
                'heading' => 'A discovery is waiting!',
                'intro' => 'Hello! Confirm {name}’s account to unlock safe access to Lumi.',
                'button' => 'Select here to access Lumi',
                'note' => 'This link is valid for 24 hours. If you did not request this account, ignore this message.',
                'text' => "Confirm {name}’s account to access Lumi:\n{url}\n\nThis link is valid for 24 hours.",
            ],
            'pt' => [
                'subject' => 'Confirme seu acesso à Lumi',
                'heading' => 'Uma descoberta está esperando!',
                'intro' => 'Olá! Confirme o cadastro de {name} para liberar o acesso seguro à Lumi.',
                'button' => 'Clique aqui para já acessar a Lumi',
                'note' => 'O link é válido por 24 horas. Se você não solicitou este cadastro, ignore esta mensagem.',
                'text' => "Confirme o cadastro de {name} para acessar a Lumi:\n{url}\n\nEste link é válido por 24 horas.",
            ],
            'es' => [
                'subject' => 'Confirma tu acceso a Lumi',
                'heading' => '¡Hay un descubrimiento esperando!',
                'intro' => '¡Hola! Confirma la cuenta de {name} para liberar el acceso seguro a Lumi.',
                'button' => 'Haz clic aquí para acceder a Lumi',
                'note' => 'Este enlace es válido durante 24 horas. Si no solicitaste esta cuenta, ignora este mensaje.',
                'text' => "Confirma la cuenta de {name} para acceder a Lumi:\n{url}\n\nEste enlace es válido durante 24 horas.",
            ],
        ][$language];

        $subject = $copy['subject'];
        $safeName = htmlspecialchars($displayName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeUrl = htmlspecialchars($verificationUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeIntro = str_replace('{name}', $safeName, $copy['intro']);
        $locale = language_locale($language);
        $html = <<<HTML
<!doctype html>
<html lang="{$locale}">
<body style="margin:0;background:#f4fbff;font-family:Arial,sans-serif;color:#17324d">
  <div style="max-width:560px;margin:0 auto;padding:32px 20px">
    <div style="background:#ffffff;border:1px solid #d9edf4;border-radius:16px;padding:30px">
      <p style="font-size:14px;font-weight:700;color:#e94f87;margin:0 0 12px">LUMI</p>
      <h1 style="font-size:26px;margin:0 0 14px">{$copy['heading']}</h1>
      <p style="font-size:17px;line-height:1.6">{$safeIntro}</p>
      <p style="margin:28px 0">
        <a href="{$safeUrl}" style="display:inline-block;background:#176b87;color:#fff;text-decoration:none;font-weight:700;padding:14px 22px;border-radius:8px">{$copy['button']}</a>
      </p>
      <p style="font-size:13px;line-height:1.5;color:#607487">{$copy['note']}</p>
    </div>
  </div>
</body>
</html>
HTML;

        $text = str_replace(
            ['{name}', '{url}'],
            [$displayName, $verificationUrl],
            $copy['text']
        );

        $this->send($to, $subject, $html, $text);
    }

    private function send(string $to, string $subject, string $html, string $text): void
    {
        $host = (string) env('SMTP_HOST', '');
        $port = (int) env('SMTP_PORT', 465);
        $encryption = strtolower((string) env('SMTP_ENCRYPTION', 'ssl'));
        $username = (string) env('SMTP_USER', '');
        $password = (string) env('SMTP_PASS', '');
        $from = (string) env('MAIL_FROM', $username);
        $fromName = (string) env('MAIL_FROM_NAME', 'Lumi');

        if ($host === '' || $username === '' || $password === '' || $from === '') {
            throw new RuntimeException('O envio de e-mail ainda não está configurado.');
        }

        $remote = ($encryption === 'ssl' ? 'ssl://' : 'tcp://') . $host . ':' . $port;
        $socket = @stream_socket_client($remote, $errorNumber, $errorMessage, 20);
        if (!$socket) {
            throw new RuntimeException('Não foi possível conectar ao serviço de e-mail.');
        }

        stream_set_timeout($socket, 20);

        try {
            $this->expect($socket, [220]);
            $this->command($socket, 'EHLO lumi', [250]);

            if ($encryption === 'tls') {
                $this->command($socket, 'STARTTLS', [220]);
                if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new RuntimeException('Não foi possível proteger a conexão de e-mail.');
                }
                $this->command($socket, 'EHLO lumi', [250]);
            }

            $this->command($socket, 'AUTH LOGIN', [334]);
            $this->command($socket, base64_encode($username), [334]);
            $this->command($socket, base64_encode($password), [235]);
            $this->command($socket, 'MAIL FROM:<' . $from . '>', [250]);
            $this->command($socket, 'RCPT TO:<' . $to . '>', [250, 251]);
            $this->command($socket, 'DATA', [354]);

            $boundary = 'lumi_' . bin2hex(random_bytes(12));
            $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
            $encodedFromName = '=?UTF-8?B?' . base64_encode($fromName) . '?=';
            $headers = [
                'Date: ' . date(DATE_RFC2822),
                'From: ' . $encodedFromName . ' <' . $from . '>',
                'To: <' . $to . '>',
                'Subject: ' . $encodedSubject,
                'Message-ID: <' . bin2hex(random_bytes(12)) . '@' . $host . '>',
                'MIME-Version: 1.0',
                'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
            ];

            $message = implode("\r\n", $headers) . "\r\n\r\n";
            $message .= "--{$boundary}\r\nContent-Type: text/plain; charset=UTF-8\r\n";
            $message .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $message .= chunk_split(base64_encode($text)) . "\r\n";
            $message .= "--{$boundary}\r\nContent-Type: text/html; charset=UTF-8\r\n";
            $message .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $message .= chunk_split(base64_encode($html)) . "\r\n";
            $message .= "--{$boundary}--\r\n";
            $message = preg_replace('/^\./m', '..', $message) ?? $message;

            fwrite($socket, $message . "\r\n.\r\n");
            $this->expect($socket, [250]);
            $this->command($socket, 'QUIT', [221]);
        } finally {
            fclose($socket);
        }
    }

    private function command($socket, string $command, array $expectedCodes): void
    {
        fwrite($socket, $command . "\r\n");
        $this->expect($socket, $expectedCodes);
    }

    private function expect($socket, array $expectedCodes): void
    {
        $response = '';
        while (($line = fgets($socket, 515)) !== false) {
            $response .= $line;
            if (preg_match('/^(\d{3}) /', $line, $matches)) {
                $code = (int) $matches[1];
                if (!in_array($code, $expectedCodes, true)) {
                    throw new RuntimeException('O serviço de e-mail recusou a operação.');
                }
                return;
            }
        }

        throw new RuntimeException('O serviço de e-mail não respondeu.');
    }
}
