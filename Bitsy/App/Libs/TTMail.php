<?php 
namespace App\Libs;

// Manually pull in the PHPMailer classes since we are not using Composer
require_once 'PHPMailer/Exception.php';
require_once 'PHPMailer/PHPMailer.php';
require_once 'PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Modernised TTMail Class
 * Supports PHPMailer (SMTP) and Native PHP Mail
 */
class TTMail 
{
    private string $type;
    private string $smtpHost;
    private int $smtpPort = 25;
    private bool $smtpAuth = false;
    private ?string $smtpUser = null;
    private ?string $smtpPass = null;
    private string $smtpSecure = ''; // 'tls' or 'ssl'

    public function __construct() 
    {
        $mailType = defined('CONFIG') && isset(CONFIG['mail_type']) ? strtolower(CONFIG['mail_type']) : 'php';

        // Treat 'pear' config as 'smtp' now that we have modernised the backend
        if ($mailType === 'pear' || $mailType === 'smtp' || $mailType === 'phpmailer') {
            $this->type = 'phpmailer';
            $this->smtpHost = CONFIG['mail_smtp_host'] ?? 'localhost';
            $this->smtpPort = (int)(CONFIG['mail_smtp_port'] ?? 25);
            $this->smtpAuth = (bool)(CONFIG['mail_smtp_auth'] ?? false);
            $this->smtpUser = CONFIG['mail_smtp_user'] ?? null;
            $this->smtpPass = CONFIG['mail_smtp_pass'] ?? null;
            
            // Set encryption scheme based on port or config setting
            $smtpSsl = CONFIG['mail_smtp_ssl'] ?? false;
            if ($smtpSsl) {
                $this->smtpSecure = ($this->smtpPort === 465) ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            }
        } else {
            $this->type = 'php';
        }
    }

    /**
     * Sends an email using the configured driver
     */
    public function send(string $to, string $subject, string $message, string $additionalHeaders = "", string $additionalParameters = ""): bool
    {
        $from = (defined('CONFIG') && isset(CONFIG['SITEEMAIL'])) ? CONFIG['SITEEMAIL'] : '';
        if (preg_match("/^From:\s*(.*)$/im", $additionalHeaders, $matches)) {
            $from = trim($matches[1]);
        }

        // Clean up headers to prevent mail injection attacks
        $cleanHeaders = preg_replace("/^From:\s*.*$/im", "", $additionalHeaders);
        $cleanHeaders = trim($cleanHeaders);

        if ($this->type === 'phpmailer') {
            return $this->sendViaPhpMailer($to, $subject, $message, $from, $cleanHeaders);
        }

        return $this->sendViaNativePhp($to, $subject, $message, $from, $cleanHeaders, $additionalParameters);
    }

    /**
     * Internal handler for PHPMailer SMTP
     */
    private function sendViaPhpMailer(string $to, string $subject, string $message, string $from, string $cleanHeaders): bool
    {
        $mail = new PHPMailer(true); // Passing true enables explicit exceptions

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = $this->smtpHost;
            $mail->SMTPAuth   = $this->smtpAuth;
            $mail->Username   = $this->smtpUser;
            $mail->Password   = $this->smtpPass;
            $mail->Port       = $this->smtpPort;
            
            if (!empty($this->smtpSecure)) {
                $mail->SMTPSecure = $this->smtpSecure;
            }

            // Recipients
            $mail->setFrom($from);
            $mail->addAddress($to);

            // Content
            $mail->Subject = $subject;
            
            // If message contains HTML tags, send as HTML, otherwise fallback to plain text
            if ($message !== strip_tags($message)) {
                $mail->isHTML(true);
                $mail->Body    = $message;
                $mail->AltBody = strip_tags($message);
            } else {
                $mail->isHTML(false);
                $mail->Body    = $message;
            }

            // Parse additional text headers if provided
            if (!empty($cleanHeaders)) {
                foreach (explode("\n", str_replace("\r", "", $cleanHeaders)) as $headerLine) {
                    if (str_contains($headerLine, ':')) {
                        [$key, $value] = explode(':', $headerLine, 2);
                        $mail->addCustomHeader(trim($key), trim($value));
                    }
                }
            }

            return $mail->send();
        } catch (Exception $e) {
            trigger_error("PHPMailer Error: " . $mail->ErrorInfo, E_USER_WARNING);
            return false;
        }
    }

    /**
     * Internal handler for PHP native mail()
     */
    private function sendViaNativePhp(string $to, string $subject, string $message, string $from, string $cleanHeaders, string $additionalParameters): bool
    {
        $nativeHeaders = [];
        $nativeHeaders[] = "From: $from";
        $nativeHeaders[] = "Return-Path: $from";
        
        if (!empty($cleanHeaders)) {
            $nativeHeaders[] = $cleanHeaders;
        }

        $headersString = implode("\r\n", $nativeHeaders);

        return mail($to, $subject, $message, $headersString, $additionalParameters);
    }
}