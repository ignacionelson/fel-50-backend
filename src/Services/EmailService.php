<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    private PHPMailer $mailer;
    private array $config;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->config = [
            'host' => $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com',
            'port' => (int)($_ENV['MAIL_PORT'] ?? 587),
            'username' => $_ENV['MAIL_USERNAME'] ?? '',
            'password' => $_ENV['MAIL_PASSWORD'] ?? '',
            'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? PHPMailer::ENCRYPTION_STARTTLS,
            'from_address' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@felapi.com',
            'from_name' => $_ENV['MAIL_FROM_NAME'] ?? 'FEL API',
            'app_url' => $_ENV['APP_URL'] ?? 'http://localhost:8000',
            'app_name' => $_ENV['APP_NAME'] ?? 'FEL API'
        ];

        $this->configureSMTP();
    }

    private function configureSMTP(): void
    {
        try {
            // Configuración del servidor
            $this->mailer->isSMTP();
            $this->mailer->Host = $this->config['host'];
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $this->config['username'];
            $this->mailer->Password = $this->config['password'];
            $this->mailer->SMTPSecure = $this->config['encryption'];
            $this->mailer->Port = $this->config['port'];
            $this->mailer->CharSet = 'UTF-8';

            // Configuración del remitente por defecto
            $this->mailer->setFrom($this->config['from_address'], $this->config['from_name']);
        } catch (Exception $e) {
            error_log("Error configurando SMTP: {$e->getMessage()}");
        }
    }

    /**
     * Enviar email de verificación de cuenta
     */
    public function sendVerificationEmail(string $email, string $verificationCode): bool
    {
        try {
            // Limpiar destinatarios anteriores
            $this->mailer->clearAddresses();
            $this->mailer->clearAllRecipients();

            // Configurar destinatario
            $this->mailer->addAddress($email);

            // Asunto y contenido
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Verificación de cuenta - ' . $this->config['app_name'];

            // Generar link de verificación
            $verificationLink = $this->config['app_url'] . '/api/v1/verify-account?email=' . urlencode($email) . '&code=' . $verificationCode;

            // Contenido HTML del email
            $htmlBody = $this->getVerificationEmailTemplate($email, $verificationCode, $verificationLink);
            $this->mailer->Body = $htmlBody;

            // Contenido alternativo en texto plano
            $this->mailer->AltBody = $this->getVerificationEmailPlainText($email, $verificationCode, $verificationLink);

            // Enviar email
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Error enviando email de verificación: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Enviar email de bienvenida después de verificar la cuenta
     */
    public function sendWelcomeEmail(string $email, string $name = null): bool
    {
        try {
            // Limpiar destinatarios anteriores
            $this->mailer->clearAddresses();
            $this->mailer->clearAllRecipients();

            // Configurar destinatario
            $this->mailer->addAddress($email, $name);

            // Asunto y contenido
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Bienvenido a ' . $this->config['app_name'];

            // Contenido HTML del email
            $htmlBody = $this->getWelcomeEmailTemplate($email, $name);
            $this->mailer->Body = $htmlBody;

            // Contenido alternativo en texto plano
            $this->mailer->AltBody = $this->getWelcomeEmailPlainText($email, $name);

            // Enviar email
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Error enviando email de bienvenida: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Enviar email de restablecimiento de contraseña
     */
    public function sendPasswordResetEmail(string $email, string $resetToken): bool
    {
        try {
            // Limpiar destinatarios anteriores
            $this->mailer->clearAddresses();
            $this->mailer->clearAllRecipients();

            // Configurar destinatario
            $this->mailer->addAddress($email);

            // Asunto y contenido
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Restablecer contraseña - ' . $this->config['app_name'];

            // Generar link de restablecimiento
            $resetLink = $this->config['app_url'] . '/api/v1/reset-password?token=' . $resetToken;

            // Contenido HTML del email
            $htmlBody = $this->getPasswordResetEmailTemplate($email, $resetToken, $resetLink);
            $this->mailer->Body = $htmlBody;

            // Contenido alternativo en texto plano
            $this->mailer->AltBody = $this->getPasswordResetEmailPlainText($email, $resetToken, $resetLink);

            // Enviar email
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Error enviando email de restablecimiento: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Template HTML para email de verificación
     */
    private function getVerificationEmailTemplate(string $email, string $code, string $link): string
    {
        $appName = htmlspecialchars($this->config['app_name']);
        $emailSafe = htmlspecialchars($email);
        $codeSafe = htmlspecialchars($code);

        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de cuenta</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table cellpadding="0" cellspacing="0" width="100%" style="background-color: #f4f4f4; padding: 20px;">
        <tr>
            <td align="center">
                <table cellpadding="0" cellspacing="0" width="600" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="padding: 40px 30px; text-align: center; background-color: #4299e1; border-radius: 8px 8px 0 0;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px;">{$appName}</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="color: #333333; font-size: 24px; margin-bottom: 20px;">Verificación de cuenta</h2>

                            <p style="color: #666666; font-size: 16px; line-height: 1.6; margin-bottom: 20px;">
                                Hola,
                            </p>

                            <p style="color: #666666; font-size: 16px; line-height: 1.6; margin-bottom: 20px;">
                                Gracias por registrarte en {$appName}. Para completar tu registro, por favor verifica tu dirección de correo electrónico.
                            </p>

                            <p style="color: #666666; font-size: 16px; line-height: 1.6; margin-bottom: 30px;">
                                Tu código de verificación es:
                            </p>

                            <div style="background-color: #f8f9fa; border: 2px dashed #4299e1; padding: 20px; text-align: center; margin-bottom: 30px; border-radius: 4px;">
                                <span style="font-size: 24px; color: #4299e1; font-weight: bold; letter-spacing: 2px;">
                                    {$codeSafe}
                                </span>
                            </div>

                            <p style="color: #666666; font-size: 16px; line-height: 1.6; margin-bottom: 30px;">
                                O puedes hacer clic en el siguiente botón para verificar tu cuenta automáticamente:
                            </p>

                            <table cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 30px;">
                                <tr>
                                    <td align="center">
                                        <a href="{$link}" style="display: inline-block; padding: 14px 30px; background-color: #4299e1; color: #ffffff; text-decoration: none; border-radius: 4px; font-size: 16px; font-weight: bold;">
                                            Verificar mi cuenta
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="color: #999999; font-size: 14px; line-height: 1.6; margin-bottom: 20px;">
                                Si no creaste una cuenta en {$appName}, puedes ignorar este mensaje de forma segura.
                            </p>

                            <p style="color: #999999; font-size: 14px; line-height: 1.6;">
                                Este código de verificación expirará en 24 horas.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 20px 30px; background-color: #f8f9fa; border-radius: 0 0 8px 8px; text-align: center;">
                            <p style="color: #999999; font-size: 12px; margin: 0;">
                                © 2024 {$appName}. Todos los derechos reservados.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    /**
     * Versión en texto plano del email de verificación
     */
    private function getVerificationEmailPlainText(string $email, string $code, string $link): string
    {
        $appName = $this->config['app_name'];

        return <<<TEXT
{$appName} - Verificación de cuenta

Hola,

Gracias por registrarte en {$appName}. Para completar tu registro, por favor verifica tu dirección de correo electrónico.

Tu código de verificación es: {$code}

También puedes verificar tu cuenta visitando el siguiente enlace:
{$link}

Si no creaste una cuenta en {$appName}, puedes ignorar este mensaje de forma segura.

Este código de verificación expirará en 24 horas.

© 2024 {$appName}. Todos los derechos reservados.
TEXT;
    }

    /**
     * Template HTML para email de bienvenida
     */
    private function getWelcomeEmailTemplate(string $email, ?string $name): string
    {
        $appName = htmlspecialchars($this->config['app_name']);
        $greeting = $name ? "Hola " . htmlspecialchars($name) : "Hola";
        $loginUrl = $this->config['app_url'] . '/login';

        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a {$appName}</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table cellpadding="0" cellspacing="0" width="100%" style="background-color: #f4f4f4; padding: 20px;">
        <tr>
            <td align="center">
                <table cellpadding="0" cellspacing="0" width="600" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="padding: 40px 30px; text-align: center; background-color: #48bb78; border-radius: 8px 8px 0 0;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px;">¡Bienvenido a {$appName}!</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="color: #333333; font-size: 24px; margin-bottom: 20px;">{$greeting},</h2>

                            <p style="color: #666666; font-size: 16px; line-height: 1.6; margin-bottom: 20px;">
                                ¡Tu cuenta ha sido verificada exitosamente! Ahora puedes acceder a todas las funcionalidades de {$appName}.
                            </p>

                            <p style="color: #666666; font-size: 16px; line-height: 1.6; margin-bottom: 30px;">
                                Para completar tu perfil y establecer tu contraseña, por favor inicia sesión:
                            </p>

                            <table cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 30px;">
                                <tr>
                                    <td align="center">
                                        <a href="{$loginUrl}" style="display: inline-block; padding: 14px 30px; background-color: #48bb78; color: #ffffff; text-decoration: none; border-radius: 4px; font-size: 16px; font-weight: bold;">
                                            Iniciar sesión
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="color: #666666; font-size: 16px; line-height: 1.6; margin-bottom: 20px;">
                                Si tienes alguna pregunta o necesitas ayuda, no dudes en contactarnos.
                            </p>

                            <p style="color: #666666; font-size: 16px; line-height: 1.6;">
                                ¡Gracias por unirte a nosotros!
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 20px 30px; background-color: #f8f9fa; border-radius: 0 0 8px 8px; text-align: center;">
                            <p style="color: #999999; font-size: 12px; margin: 0;">
                                © 2024 {$appName}. Todos los derechos reservados.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    /**
     * Versión en texto plano del email de bienvenida
     */
    private function getWelcomeEmailPlainText(string $email, ?string $name): string
    {
        $appName = $this->config['app_name'];
        $greeting = $name ? "Hola {$name}" : "Hola";
        $loginUrl = $this->config['app_url'] . '/login';

        return <<<TEXT
¡Bienvenido a {$appName}!

{$greeting},

¡Tu cuenta ha sido verificada exitosamente! Ahora puedes acceder a todas las funcionalidades de {$appName}.

Para completar tu perfil y establecer tu contraseña, por favor inicia sesión:
{$loginUrl}

Si tienes alguna pregunta o necesitas ayuda, no dudes en contactarnos.

¡Gracias por unirte a nosotros!

© 2024 {$appName}. Todos los derechos reservados.
TEXT;
    }

    /**
     * Template HTML para email de restablecimiento de contraseña
     */
    private function getPasswordResetEmailTemplate(string $email, string $token, string $link): string
    {
        $appName = htmlspecialchars($this->config['app_name']);

        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer contraseña</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table cellpadding="0" cellspacing="0" width="100%" style="background-color: #f4f4f4; padding: 20px;">
        <tr>
            <td align="center">
                <table cellpadding="0" cellspacing="0" width="600" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="padding: 40px 30px; text-align: center; background-color: #ed8936; border-radius: 8px 8px 0 0;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px;">Restablecer contraseña</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="color: #666666; font-size: 16px; line-height: 1.6; margin-bottom: 20px;">
                                Hola,
                            </p>

                            <p style="color: #666666; font-size: 16px; line-height: 1.6; margin-bottom: 30px;">
                                Hemos recibido una solicitud para restablecer la contraseña de tu cuenta en {$appName}.
                            </p>

                            <table cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 30px;">
                                <tr>
                                    <td align="center">
                                        <a href="{$link}" style="display: inline-block; padding: 14px 30px; background-color: #ed8936; color: #ffffff; text-decoration: none; border-radius: 4px; font-size: 16px; font-weight: bold;">
                                            Restablecer mi contraseña
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="color: #999999; font-size: 14px; line-height: 1.6; margin-bottom: 20px;">
                                Si no solicitaste restablecer tu contraseña, puedes ignorar este mensaje de forma segura.
                            </p>

                            <p style="color: #999999; font-size: 14px; line-height: 1.6;">
                                Este enlace expirará en 2 horas.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 20px 30px; background-color: #f8f9fa; border-radius: 0 0 8px 8px; text-align: center;">
                            <p style="color: #999999; font-size: 12px; margin: 0;">
                                © 2024 {$appName}. Todos los derechos reservados.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    /**
     * Versión en texto plano del email de restablecimiento
     */
    private function getPasswordResetEmailPlainText(string $email, string $token, string $link): string
    {
        $appName = $this->config['app_name'];

        return <<<TEXT
{$appName} - Restablecer contraseña

Hola,

Hemos recibido una solicitud para restablecer la contraseña de tu cuenta en {$appName}.

Para restablecer tu contraseña, haz clic en el siguiente enlace:
{$link}

Si no solicitaste restablecer tu contraseña, puedes ignorar este mensaje de forma segura.

Este enlace expirará en 2 horas.

© 2024 {$appName}. Todos los derechos reservados.
TEXT;
    }
}