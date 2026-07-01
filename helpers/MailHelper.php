<?php

class MailHelper {
    public static function sendPlainText($toEmail, $subject, $message, $fromEmail = null) {
        $toEmail = trim((string)$toEmail);
        $fromEmail = self::resolveFromEmail($fromEmail);
        if ($toEmail === '' || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $smtpHost = getenv('SMTP_HOST');
        if ($smtpHost) {
            return self::sendViaSmtp(
                $smtpHost,
                getenv('SMTP_PORT') ?: '25',
                getenv('SMTP_USER') ?: '',
                getenv('SMTP_PASS') ?: '',
                strtolower(getenv('SMTP_ENCRYPTION') ?: ''),
                $fromEmail,
                $toEmail,
                $subject,
                $message
            );
        }

        $headers = [
            'From: ' . $fromEmail,
            'Reply-To: ' . $fromEmail,
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
        ];

        return mail($toEmail, $subject, $message, implode("\r\n", $headers));
    }

    private static function sendViaSmtp($host, $port, $user, $pass, $encryption, $fromEmail, $toEmail, $subject, $message) {
        $transport = $encryption === 'ssl' ? 'ssl://' : '';
        $stream = @stream_socket_client($transport . $host . ':' . $port, $errno, $errstr, 15, STREAM_CLIENT_CONNECT);
        if (!$stream) {
            error_log("SMTP connect failed: $errstr ($errno)");
            return false;
        }
        stream_set_timeout($stream, 15);

        if (!self::expect($stream, '220')) return self::closeFail($stream);
        if (!self::write($stream, "EHLO localhost\r\n")) return self::closeFail($stream);
        self::read($stream);

        if ($encryption === 'starttls') {
            if (!self::write($stream, "STARTTLS\r\n")) return self::closeFail($stream);
            if (!self::expect($stream, '220')) return self::closeFail($stream);
            if (!stream_socket_enable_crypto($stream, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                return self::closeFail($stream);
            }
            if (!self::write($stream, "EHLO localhost\r\n")) return self::closeFail($stream);
            self::read($stream);
        }

        if ($user !== '' && $pass !== '') {
            if (!self::write($stream, "AUTH LOGIN\r\n")) return self::closeFail($stream);
            if (!self::expect($stream, '334')) return self::closeFail($stream);
            if (!self::write($stream, base64_encode($user) . "\r\n")) return self::closeFail($stream);
            if (!self::expect($stream, '334')) return self::closeFail($stream);
            if (!self::write($stream, base64_encode($pass) . "\r\n")) return self::closeFail($stream);
            if (!self::expect($stream, '235')) return self::closeFail($stream);
        }

        if (!self::write($stream, "MAIL FROM:<$fromEmail>\r\n")) return self::closeFail($stream);
        if (!self::expect($stream, '250')) return self::closeFail($stream);
        if (!self::write($stream, "RCPT TO:<$toEmail>\r\n")) return self::closeFail($stream);
        $response = self::read($stream);
        if (strpos($response, '250') !== 0 && strpos($response, '251') !== 0) return self::closeFail($stream);
        if (!self::write($stream, "DATA\r\n")) return self::closeFail($stream);
        if (!self::expect($stream, '354')) return self::closeFail($stream);

        $payload = implode("\r\n", [
            'From: ' . $fromEmail,
            'To: ' . $toEmail,
            'Subject: ' . self::sanitizeHeader($subject),
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            '',
            self::normalizeMessage($message),
            '.',
            '',
        ]);

        if (!self::write($stream, $payload)) return self::closeFail($stream);
        if (!self::expect($stream, '250')) return self::closeFail($stream);

        self::write($stream, "QUIT\r\n");
        fclose($stream);
        return true;
    }

    private static function resolveFromEmail($fromEmail = null) {
        $fromEmail = $fromEmail ?: (getenv('EMAIL_FROM') ?: 'noreply@ecommerce.local');
        $smtpUser = getenv('SMTP_USER') ?: '';
        $placeholderEmails = ['noreply@example.com', 'noreply@ecommerce.local'];
        if ($smtpUser !== '' && in_array(strtolower($fromEmail), $placeholderEmails, true)) {
            return $smtpUser;
        }
        return $fromEmail;
    }

    private static function sanitizeHeader($value) {
        return str_replace(["\r", "\n"], '', (string)$value);
    }

    private static function normalizeMessage($message) {
        $message = str_replace(["\r\n", "\r"], "\n", (string)$message);
        $lines = explode("\n", $message);
        foreach ($lines as &$line) {
            if (strpos($line, '.') === 0) {
                $line = '.' . $line;
            }
        }
        unset($line);
        return implode("\r\n", $lines);
    }

    private static function write($stream, $command) {
        return fwrite($stream, $command) !== false;
    }

    private static function expect($stream, $code) {
        return strpos(self::read($stream), $code) === 0;
    }

    private static function read($stream) {
        $data = '';
        while (($line = fgets($stream, 515)) !== false) {
            $data .= $line;
            if (substr($line, 3, 1) === ' ') {
                break;
            }
        }
        return $data;
    }

    private static function closeFail($stream) {
        fclose($stream);
        return false;
    }
}
