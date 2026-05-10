<?php
/**
 * Mail Helper Class
 * Sends emails using PHPMailer or built-in mail() function
 */

class MailHelper {
    private $config;
    private $mailer;
    private $error = '';

    public function __construct() {
        $this->config = require __DIR__ . '/../config/mail.php';
        $this->initializeMailer();
    }

    private function initializeMailer() {
        // Check if PHPMailer is available
        if ($this->isPhpMailerAvailable()) {
            $this->mailer = 'phpmailer';
            return;
        }

        // Check if SMTP functions are available
        if (ini_get('SMTP') || ini_get('sendmail_path')) {
            $this->mailer = 'mail';
            return;
        }

        $this->error = 'No email service configured. Install PHPMailer or configure PHP mail()';
    }

    private function isPhpMailerAvailable() {
        // Check if PHPMailer\PHPMailer exists
        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            return true;
        }

        // Check if it's in vendor directory
        if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
            require_once __DIR__ . '/../vendor/autoload.php';
            return class_exists('PHPMailer\PHPMailer\PHPMailer');
        }

        return false;
    }

    /**
     * Send email
     * 
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $body Email body (HTML or plain text)
     * @param array $options Additional options (cc, bcc, attachments, etc)
     * @return bool
     */
    public function send($to, $subject, $body, $options = []) {
        if (!$this->mailer) {
            return false;
        }

        if ($this->mailer === 'phpmailer') {
            return $this->sendWithPhpMailer($to, $subject, $body, $options);
        }

        return $this->sendWithMail($to, $subject, $body, $options);
    }

    private function sendWithPhpMailer($to, $subject, $body, $options) {
        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $driver = $this->config['driver'];

            // Server settings
            if ($driver === 'gmail') {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = $this->config['gmail']['email'];
                $mail->Password = $this->config['gmail']['app_password'];
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
            } else {
                // Generic SMTP
                $mail->isSMTP();
                $mail->Host = $this->config['smtp']['host'];
                $mail->SMTPAuth = true;
                $mail->Username = $this->config['smtp']['username'];
                $mail->Password = $this->config['smtp']['password'];
                $mail->SMTPSecure = $this->config['smtp']['encryption'] === 'ssl' ? 
                    \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : 
                    \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = $this->config['smtp']['port'];
            }

            $mail->setFrom($this->config['from']['address'], $this->config['from']['name']);
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->isHTML(strpos($body, '<') === 0);

            // Additional recipients
            if (isset($options['cc'])) {
                foreach ((array)$options['cc'] as $cc) {
                    $mail->addCC($cc);
                }
            }
            if (isset($options['bcc'])) {
                foreach ((array)$options['bcc'] as $bcc) {
                    $mail->addBCC($bcc);
                }
            }

            return $mail->send();
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            error_log("PHPMailer Error: " . $this->error);
            return false;
        }
    }

    private function sendWithMail($to, $subject, $body, $options) {
        $headers = "From: {$this->config['from']['address']}\r\n";
        $headers .= "Reply-To: {$this->config['from']['address']}\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        if (isset($options['cc'])) {
            $cc = is_array($options['cc']) ? implode(', ', $options['cc']) : $options['cc'];
            $headers .= "Cc: $cc\r\n";
        }
        if (isset($options['bcc'])) {
            $bcc = is_array($options['bcc']) ? implode(', ', $options['bcc']) : $options['bcc'];
            $headers .= "Bcc: $bcc\r\n";
        }

        $result = mail($to, $subject, $body, $headers);
        
        if (!$result) {
            $this->error = 'mail() function failed. Check PHP mail configuration.';
            error_log("Mail Error: " . $this->error);
        }

        return $result;
    }

    public function getError() {
        return $this->error;
    }

    public function isConfigured() {
        return $this->mailer !== null;
    }
}
?>
