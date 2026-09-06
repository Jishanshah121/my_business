<?php

declare(strict_types=1);

namespace App\Services\Notification;

use App\Support\Config;
use App\Support\Logger;
use App\Support\View;
use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * SMTP mail sending.
 *
 * PHPMailer is the one runtime dependency Phase 2 adds: SMTP with STARTTLS,
 * correct header encoding and injection-safe address handling is exactly the
 * kind of thing not worth hand-rolling.
 */
final class Mailer
{
    public function __construct(
        private readonly View $view,
        private readonly Logger $logger,
    ) {
    }

    /**
     * @param array<string,mixed> $data
     * @return bool Whether the message was handed to the SMTP server.
     */
    public function send(string $to, string $subject, string $template, array $data = []): bool
    {
        $html = $this->view->render('emails/' . $template, $data + ['subject' => $subject]);

        // The `log` driver writes the rendered message to storage/logs/mail
        // instead of sending it. It exists so the full verification and reset
        // flows can be exercised locally without an SMTP server — the tokens
        // themselves are only ever stored hashed, so there is no other way to
        // read one back.
        if (Config::get('mail.mailer') === 'log') {
            return $this->writeToLog($to, $subject, $html);
        }

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = (string) Config::get('mail.host', '127.0.0.1');
            $mail->Port = (int) Config::get('mail.port', 1025);
            $mail->CharSet = PHPMailer::CHARSET_UTF8;

            $username = (string) Config::get('mail.username', '');
            if ($username !== '') {
                $mail->SMTPAuth = true;
                $mail->Username = $username;
                $mail->Password = (string) Config::get('mail.password', '');
            } else {
                // Mailhog in local development accepts unauthenticated mail.
                $mail->SMTPAuth = false;
                $mail->SMTPAutoTLS = false;
            }

            $encryption = (string) Config::get('mail.encryption', '');
            if ($encryption !== '') {
                $mail->SMTPSecure = $encryption;
            }

            $mail->setFrom(
                (string) Config::get('mail.from.address', 'no-reply@supplykaro.test'),
                (string) Config::get('mail.from.name', 'SupplyKaro')
            );
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->isHTML(true);
            $mail->Body = $html;
            $mail->AltBody = $this->toPlainText($html);

            $mail->send();

            $this->logger->info('Email sent', ['to' => $this->maskEmail($to), 'subject' => $subject]);

            return true;
        } catch (MailException $e) {
            // A mail failure must never break the flow that triggered it —
            // a customer who registered is registered whether or not the
            // welcome email left the building.
            $this->logger->error('Email failed', [
                'to'      => $this->maskEmail($to),
                'subject' => $subject,
                'error'   => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function writeToLog(string $to, string $subject, string $html): bool
    {
        $directory = (string) Config::get('app.log.path') . '/mail';
        if (!is_dir($directory) && !@mkdir($directory, 0775, true) && !is_dir($directory)) {
            $this->logger->error('Could not write mail log', ['directory' => $directory]);

            return false;
        }

        $name = date('Ymd-His') . '-' . substr(bin2hex(random_bytes(4)), 0, 8) . '.html';
        $header = sprintf(
            "<!-- To: %s\n     Subject: %s\n     Sent: %s\n     Driver: log (not actually delivered) -->\n",
            $to,
            $subject,
            date('c')
        );

        @file_put_contents($directory . '/' . $name, $header . $html);

        $this->logger->info('Email written to log driver', [
            'to'      => $this->maskEmail($to),
            'subject' => $subject,
            'file'    => 'storage/logs/mail/' . $name,
        ]);

        return true;
    }

    private function toPlainText(string $html): string
    {
        $text = preg_replace('/<br\s*\/?>|<\/p>|<\/div>|<\/h[1-6]>/i', "\n", $html) ?? $html;
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        $text = preg_replace("/\n{3,}/", "\n\n", $text) ?? $text;

        return trim($text);
    }

    /** Logs record that mail was sent, not who to. */
    private function maskEmail(string $email): string
    {
        $parts = explode('@', $email, 2);
        if (count($parts) !== 2) {
            return '***';
        }

        $name = $parts[0];
        $masked = mb_substr($name, 0, 1) . str_repeat('*', max(1, mb_strlen($name) - 1));

        return $masked . '@' . $parts[1];
    }
}
