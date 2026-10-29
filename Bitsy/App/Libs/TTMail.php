<?php 

declare(strict_types=1);

namespace App\Libs;

// Manually pull in the PHPMailer classes since we are not using Composer
require_once APPROOT . "/App/Libs/PHPMailer/Exception.php";
require_once APPROOT . "/App/Libs/PHPMailer/PHPMailer.php";
require_once APPROOT . "/App/Libs/PHPMailer/SMTP.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Mail helper: PHPMailer (SMTP) or native mail().
 */
class TTMail
{
    private string $type = 'php';
    private string $smtpHost = 'localhost';
    private int $smtpPort = 25;
    private bool $smtpAuth = false;
    private ?string $smtpUser = null;
    private ?string $smtpPass = null;
    private string $smtpSecure = '';

    public function __construct()
    {
        $mailType = strtolower((string) (CONFIG['mail_type'] ?? 'php'));

        if (in_array($mailType, ['pear', 'smtp', 'phpmailer'], true)) {
            $this->type       = 'phpmailer';
            $this->smtpHost   = (string) (CONFIG['mail_smtp_host'] ?? 'localhost');
            $this->smtpPort   = (int) (CONFIG['mail_smtp_port'] ?? 25);
            $this->smtpAuth   = (bool) (CONFIG['mail_smtp_auth'] ?? false);
            $this->smtpUser   = CONFIG['mail_smtp_user'] ?? null;
            $this->smtpPass   = CONFIG['mail_smtp_pass'] ?? null;

            if (!empty(CONFIG['mail_smtp_ssl'])) {
                $this->smtpSecure = ($this->smtpPort === 465)
                    ? PHPMailer::ENCRYPTION_SMTPS
                    : PHPMailer::ENCRYPTION_STARTTLS;
            }
        }
    }

    public function send(
        string $to,
        string $subject,
        string $message,
        string $additionalHeaders = '',
        string $additionalParameters = ''
    ): bool {
        $to      = $this->sanitizeHeaderValue($to);
        $subject = $this->sanitizeHeaderValue($subject);

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->log('Invalid recipient: ' . $to);
            return false;
        }

        $from = (string) (CONFIG['SITEEMAIL'] ?? '');
        if (preg_match('/^From:\s*(.*)$/im', $additionalHeaders, $matches)) {
            $from = trim($matches[1]);
        }
        $from = $this->sanitizeHeaderValue($from);
        if ($from === '' || !filter_var($from, FILTER_VALIDATE_EMAIL)) {
            $this->log('Invalid or missing From address');
            return false;
        }

        $cleanHeaders = trim((string) preg_replace('/^From:\s*.*$/im', '', $additionalHeaders));

        if ($this->type === 'phpmailer') {
            return $this->sendViaPhpMailer($to, $subject, $message, $from, $cleanHeaders);
        }

        // Do not forward arbitrary $additionalParameters unless you fully trust the caller
        return $this->sendViaNativePhp($to, $subject, $message, $from, $cleanHeaders, '');
    }

    private function sendViaPhpMailer(
        string $to,
        string $subject,
        string $message,
        string $from,
        string $cleanHeaders
    ): bool {
        $mail = new PHPMailer(true);

        try {
            $mail->CharSet  = 'UTF-8';
            $mail->Encoding = 'base64';

            $mail->isSMTP();
            $mail->Host     = $this->smtpHost;
            $mail->Port     = $this->smtpPort;
            $mail->SMTPAuth = $this->smtpAuth;
            if ($this->smtpAuth) {
                $mail->Username = (string) $this->smtpUser;
                $mail->Password = (string) $this->smtpPass;
            }
            if ($this->smtpSecure !== '') {
                $mail->SMTPSecure = $this->smtpSecure;
            }

            $mail->setFrom($from, (string) (CONFIG['SITENAME'] ?? ''));
            $mail->addAddress($to);
            $mail->Subject = $subject;

            if ($message !== strip_tags($message)) {
                $mail->isHTML(true);
                $mail->Body    = $message;
                $mail->AltBody = strip_tags($message);
            } else {
                $mail->isHTML(false);
                $mail->Body = $message;
            }

            if ($cleanHeaders !== '') {
                foreach (explode("\n", str_replace("\r", '', $cleanHeaders)) as $headerLine) {
                    if (str_contains($headerLine, ':')) {
                        [$key, $value] = explode(':', $headerLine, 2);
                        $mail->addCustomHeader(trim($key), $this->sanitizeHeaderValue(trim($value)));
                    }
                }
            }

            return $mail->send();
        } catch (Exception $e) {
            $this->log('PHPMailer: ' . $mail->ErrorInfo);
            return false;
        }
    }

    private function sendViaNativePhp(
        string $to,
        string $subject,
        string $message,
        string $from,
        string $cleanHeaders,
        string $additionalParameters
    ): bool {
        $isHtml = $message !== strip_tags($message);
        $headers = [
            'From: ' . $from,
            'MIME-Version: 1.0',
            $isHtml
                ? 'Content-Type: text/html; charset=UTF-8'
                : 'Content-Type: text/plain; charset=UTF-8',
        ];
        if ($cleanHeaders !== '') {
            $headers[] = $cleanHeaders;
        }

        $ok = mail($to, $subject, $message, implode("\r\n", $headers), $additionalParameters);
        if (!$ok) {
            $this->log('Native mail() failed for ' . $to);
        }
        return $ok;
    }

    private function sanitizeHeaderValue(string $value): string
    {
        return trim(str_replace(["\r", "\n", "\0"], '', $value));
    }

    private function log(string $message): void
    {
        $line = sprintf("[%s] %s\n", date('Y-m-d H:i:s'), $message);
        if (defined('LOG_PATH') && is_dir(LOG_PATH)) {
            error_log($line, 3, LOG_PATH . DIRECTORY_SEPARATOR . 'mail_errors.log');
        }
        trigger_error('TTMail: ' . $message, E_USER_WARNING);
    }
}