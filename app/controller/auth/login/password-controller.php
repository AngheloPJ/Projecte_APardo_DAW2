<?php

require_once BASE_PATH . '/app/model/dao/UserDAO.php';
require_once BASE_PATH . '/app/model/dao/PasswordResetDAO.php';
require_once BASE_PATH . '/app/model/entity/User.php';

require_once BASE_PATH . '/lib/PHPMailer/src/Exception.php';
require_once BASE_PATH . '/lib/PHPMailer/src/PHPMailer.php';
require_once BASE_PATH . '/lib/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class PasswordResetController {

    /**
     * Mostrar formulario de recuperar contraseña
     */
    public function showForgotPasswordForm(): void {
        $error = null;
        $success = null;
        require BASE_VIEW . '/auth/forgot-password-view.php';
    }

    /**
     * Enviar email
     */
    public function forgotPassword(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showForgotPasswordForm();
            return;
        }

        $email = trim($_POST['email'] ?? '');
        $error = null;
        $success = null;

        if (empty($email)) {
            $error = "Debes introducir un correo electrónico.";
            require BASE_VIEW . '/auth/forgot-password-view.php';
            return;
        }

        $user = UserDAO::getByEmail($email);


        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hora
            PasswordResetDAO::deleteByUser($user->getId());
            PasswordResetDAO::create($user->getId(), $token, $expiresAt);

            $link = BASE_URL . "reset-password?token=$token";
            $this->sendResetEmail($email, $user->getDisplayName(), $link);
        }

        $success = "¡Correo enviado! Revisa tu bandeja de correos.";
        require BASE_VIEW . '/auth/forgot-password-view.php';
    }

    /**
     * Mostrar formulario de cambiar contraseña
     */
    public function showResetPasswordForm(): void {
        $token = $_GET['token'] ?? null;
        $error = null;

        if (!$token || !PasswordResetDAO::getByToken($token)) {
            $error = "El enlace no es válido o ha caducado. Por favor, solicita uno nuevo.";
            $token = null;
        }

        require BASE_VIEW . '/auth/reset-password-view.php';
    }

    /**
     * Guardar nueva contraseña
     */
    public function resetPassword(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "login");
            exit;
        }

        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm'] ?? '';
        $error = null;

        // Check | Token válido
        $resetData = PasswordResetDAO::getByToken($token);

        if (!$resetData) {
            $error = "Token inválido o caducado.";
            require BASE_VIEW . '/auth/reset-password-view.php';
            return;
        }

        // Check | Contraseñas
        if ($password !== $confirm) {
            $error = "Las contraseñas no coinciden.";
            require BASE_VIEW . '/auth/reset-password-view.php';
            return;
        }

        $errors = $this->validatePassword($password);
        if (!empty($errors)) {
            $error = "La contraseña debe incluir:<br>" . implode('<br>', $errors);
            require BASE_PATH . '/app/view/auth/reset-password-view.php';
            return;
        }

        // Actualizar contraseña
        $hash = password_hash($password, PASSWORD_DEFAULT);
        UserDAO::updatePassword($resetData['user_id'], $hash);

        // Eliminar token usado
        PasswordResetDAO::deleteByUser($resetData['user_id']);
        header("Location: " . BASE_URL . "login?success=password_changed");
        exit;
    }

    /**
     * Validar fortaleza de contra
     */
    private function validatePassword(string $password): array {
        $errors = [];
        if (strlen($password) < 8) $errors[] = "· Mínimo 8 caracteres.";
        if (!preg_match('/[A-Z]/', $password)) $errors[] = "· Al menos una mayúscula.";
        if (!preg_match('/[a-z]/', $password)) $errors[] = "· Al menos una minúscula.";
        if (!preg_match('/\d/', $password)) $errors[] = "· Al menos un número.";
        if (!preg_match('/[\W_]/', $password)) $errors[] = "· Al menos un símbolo.";
        return $errors;
    }

    /**
     * Construir email (PHPMailer)
     */
    private function sendResetEmail(string $toEmail, string $toName, string $link): bool {
        $mail = new PHPMailer(true);

        try {
            // Config del mailer
            $mail->isSMTP();
            $mail->Host       = MAIL_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USER;
            $mail->Password   = MAIL_PASS;
            $mail->SMTPSecure = MAIL_SECURE;
            $mail->Port       = MAIL_PORT;
            $mail->CharSet    = 'UTF-8';

            // Remitente y Destinatario
            $mail->setFrom(MAIL_FROM, MAIL_NAME);
            $mail->addAddress($toEmail, $toName);

            // Contenido
            $mail->isHTML(true);
            $mail->Subject = 'Recuperación de contraseña';
            
            $body = "
                <h3>Hola {$toName},</h3>
                <p>Has solicitado restablecer tu contraseña.</p>
                <p>Haz clic en el siguiente enlace para continuar:</p>
                <p><a href='{$link}'>Restablecer Contraseña</a></p>
                <p>Si no puedes hacer clic, copia y pega esta URL en tu navegador:<br>{$link}</p>
                <p>Este enlace caducará en 1 hora.</p>
                <hr>
                <small>Si no solicitaste este cambio, ignora este mensaje.</small>
            ";
            
            $mail->Body = $body;
            $mail->AltBody = "Hola {$toName}, para restablecer tu contraseña visita: {$link}";

            $mail->send();
            return true;

        } catch (Exception $e) {
            error_log("Error enviando email a {$toEmail}: {$mail->ErrorInfo}");
            return false;
        }
    }
}