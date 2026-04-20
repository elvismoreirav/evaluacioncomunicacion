<?php
/**
 * Envío de correos
 */

$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class Mailer
{
    private static ?PHPMailer $instance = null;

    private static function getInstance(): PHPMailer
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8';
        $mail->isHTML(true);
        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addReplyTo(MAIL_FROM, MAIL_FROM_NAME);
        $mail->isSMTP();
        $mail->Host = MAIL_SMTP_HOST;
        $mail->Port = MAIL_SMTP_PORT;
        $mail->SMTPSecure = MAIL_SMTP_SECURE;
        $mail->SMTPAuth = true;
        $mail->Timeout = 10;
        $mail->SMTPKeepAlive = false;

        $oauthClass = 'PHPMailer\\PHPMailer\\OAuth';
        $googleClass = 'League\\OAuth2\\Client\\Provider\\Google';
        $hasOAuth = class_exists($oauthClass) && class_exists($googleClass);

        try {
            if ($hasOAuth && OAUTH_CLIENT_ID && OAUTH_CLIENT_SECRET && OAUTH_REFRESH_TOKEN) {
                $provider = new $googleClass([
                    'clientId' => OAUTH_CLIENT_ID,
                    'clientSecret' => OAUTH_CLIENT_SECRET,
                ]);

                $mail->AuthType = 'XOAUTH2';
                $mail->Username = MAIL_FROM;
                $mail->setOAuth(new $oauthClass([
                    'provider' => $provider,
                    'clientId' => OAUTH_CLIENT_ID,
                    'clientSecret' => OAUTH_CLIENT_SECRET,
                    'refreshToken' => OAUTH_REFRESH_TOKEN,
                    'userName' => MAIL_FROM,
                ]));
            } else {
                $mail->isMail();
            }
        } catch (Throwable $throwable) {
            error_log('Mailer init failed: ' . $throwable->getMessage());
            $mail->isMail();
        }

        self::$instance = $mail;
        return $mail;
    }

    public static function sendAdminCredentials(string $email, array $data): bool
    {
        return self::sendTemplate(
            $email,
            'Acceso al Panel de Diagnóstico de Comunicación - UECR',
            'admin_credentials',
            $data
        );
    }

    public static function sendSupervisorPendingNotification(string $email, array $data): bool
    {
        return self::sendTemplate(
            $email,
            'Tiene un diagnóstico de comunicación pendiente - UECR',
            'evaluation_notification',
            $data
        );
    }

    private static function sendTemplate(string $email, string $subject, string $template, array $data): bool
    {
        try {
            $mail = self::getInstance();
            $mail->clearAllRecipients();
            $mail->clearAttachments();
            $mail->addAddress($email);
            $mail->Subject = $subject;
            $mail->msgHTML(self::renderTemplate($template, $data));
            $mail->AltBody = self::renderPlainText($template, $data);
            return $mail->send();
        } catch (Throwable $throwable) {
            error_log('Mailer send failed: ' . $throwable->getMessage());
            return false;
        }
    }

    private static function renderTemplate(string $template, array $data): string
    {
        $templatePath = __DIR__ . '/../templates/email_' . $template . '.html';
        $html = file_exists($templatePath)
            ? (string) file_get_contents($templatePath)
            : '<p>%mensaje%</p>';

        $data['year'] = date('Y');
        $data['app_name'] = APP_NAME;
        $data['base_url'] = APP_URL;
        $data['login_url'] = admin_url('login.php');

        foreach ($data as $key => $value) {
            $replacement = htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
            $html = str_replace('%' . $key . '%', $replacement, $html);
            $html = preg_replace('/\{\{\s*' . preg_quote((string) $key, '/') . '\s*\}\}/', $replacement, $html);
        }

        return $html;
    }

    private static function renderPlainText(string $template, array $data): string
    {
        if ($template === 'admin_credentials') {
            return implode("\n", [
                INSTITUTION_NAME,
                'Credenciales administrativas',
                'Usuario: ' . ($data['usuario'] ?? ''),
                'Contraseña temporal: ' . ($data['password'] ?? ''),
                'Rol: ' . ($data['rol'] ?? ''),
                'Acceso: ' . admin_url('login.php'),
            ]);
        }

        return implode("\n", [
            INSTITUTION_NAME,
            (string) ($data['mensaje'] ?? 'Notificación del sistema de diagnóstico de comunicación.'),
            APP_URL,
        ]);
    }
}
