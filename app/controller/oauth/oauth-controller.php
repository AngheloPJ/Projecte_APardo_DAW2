<?php

require_once BASE_PATH . '/app/model/dao/UserDAO.php';
require_once BASE_PATH . '/app/model/dao/AccountDAO.php';
require_once BASE_PATH . '/app/controller/auth/session/session-controller.php';

class OAuthController {

    private SessionController $session;

    public function __construct() {
        $this->session = new SessionController();
        $this->session->start();
    }

    /**
     * Obtener datos del .env
     */
    private function getConfig($provider) {
        $configs = [
            'discord' => [
                'auth_url' => 'https://discord.com/oauth2/authorize',
                'token_url' => 'https://discord.com/api/oauth2/token',
                'user_url' => 'https://discord.com/api/users/@me',
                'client_id' => DISCORD_CLIENT_ID,
                'client_secret' => DISCORD_CLIENT_SECRET,
                'scope' => 'identify email',
                'response_type' => 'code',
                'redirect_uri' => $_ENV['OAUTH_DISCORD_REDIRECT_URI'] ?? BASE_URL . 'oauth/discord'
            ],
            'github' => [
                'auth_url' => 'https://github.com/login/oauth/authorize',
                'token_url' => 'https://github.com/login/oauth/access_token',
                'user_url' => 'https://api.github.com/user',
                'client_id' => GITHUB_CLIENT_ID,
                'client_secret' => GITHUB_CLIENT_SECRET,
                'scope' => 'user:email',
                'response_type' => 'code',
                'redirect_uri' => $_ENV['OAUTH_GITHUB_REDIRECT_URI'] ?? BASE_URL . 'oauth/github'
            ]
        ];
        return $configs[$provider] ?? null;
    }

    /**
     * Redirigir al proveedor (Discord)
     */
    public function redirect($provider) {
        $config = $this->getConfig($provider);
        if (!$config || !$config['client_id'] || !$config['client_secret']) {
            $_SESSION['error'] = "Proveedor OAuth no configurado correctamente";
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        // Generar y guardar state para protección CSRF
        $state = bin2hex(random_bytes(32));
        $_SESSION['oauth_state'] = $state;
        $_SESSION['oauth_provider'] = $provider;

        // URL de confirmación
        $params = http_build_query([
            'client_id' => $config['client_id'],
            'redirect_uri' => $config['redirect_uri'],
            'response_type' => $config['response_type'],
            'scope' => $config['scope'],
            'state' => $state
        ]);

        // Redirigir al proveedor
        header("Location: {$config['auth_url']}?{$params}");
        exit;
    }

    /**
     * Callback del proveedor (Discord)
     */
    public function callback($provider) {
        if (isset($_GET['error'])) {
            $_SESSION['error'] = "Error del proveedor: " . htmlspecialchars($_GET['error']);
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        if (!isset($_GET['code'])) {
            $_SESSION['error'] = "Código de autorización no recibido";
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        if (!isset($_GET['state']) || $_GET['state'] !== ($_SESSION['oauth_state'] ?? null)) {
            $_SESSION['error'] = "Error de seguridad: state no válido";
            unset($_SESSION['oauth_state']);
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        // Obtener configuración
        $config = $this->getConfig($provider);
        if (!$config) {
            $_SESSION['error'] = "Proveedor no válido";
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        // Obtener token de acceso
        $code = $_GET['code'];
        $token = $this->requestToken($config, $code);
        
        if (!$token) {
            $_SESSION['error'] = "No se pudo obtener el token de acceso";
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        // Obtener datos del usuario
        $userProviderData = $this->requestUser($config, $token);
        
        if (!$userProviderData) {
            $_SESSION['error'] = "No se pudo obtener información del usuario";
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        // Normalizar datos y hacer login/registro
        $this->loginOrRegister($provider, $userProviderData);
    }

    /**
     * Solicitar token de acceso
     */
    private function requestToken($config, $code) {
        $postFields = [
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'code' => $code,
            'redirect_uri' => $config['redirect_uri'],
            'grant_type' => 'authorization_code'
        ];

        // Form-encoded (Lo usa Discord y Github también)
        $postData = http_build_query($postFields);

        $ch = curl_init($config['token_url']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        // Headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ]);
        
        // Desactivar SSL
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);
        if ($httpCode !== 200) return null;
        
        $data = json_decode($response, true);
        if (!$data) return null;
        
        $token = $data['access_token'] ?? null;
        return $token;
    }

    /**
     * Obtener nombre de usuario del proveedor
     */
    private function requestUser($config, $token) {
        $ch = curl_init($config['user_url']);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Accept: application/json',
            'User-Agent: APardo-Backend'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return null;
        }

        $userData = json_decode($response, true);

        // Auxiliar: Si no hay email de github, buscarlo en /user/emails
        if (strpos($config['user_url'], 'github') !== false && empty($userData['email'])) {
            $ch = curl_init('https://api.github.com/user/emails');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $token,
                'Accept: application/json',
                'User-Agent: APardo-Backend'
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            $emailResponse = curl_exec($ch);
            curl_close($ch);

            $emails = json_decode($emailResponse, true);
            if (is_array($emails)) {
                foreach ($emails as $email) {
                    if ($email['primary'] && $email['verified']) {
                        $userData['email'] = $email['email'];
                        break;
                    }
                }
            }
        }

        return $userData;
    }

    /**
     * Normalizar datos para el login o registro
     */
    private function loginOrRegister($provider, $data) {
        $normalizedData = $this->normalizeUserData($provider, $data);
        
        if (!$normalizedData['email'] || !$normalizedData['username'] || !$normalizedData['provider_id']) {
            $_SESSION['error'] = "No se pudieron obtener datos del usuario";
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        // Si existe: Provider_id (vinculación ya existente confirmada)
        $existingAccount = AccountDAO::getByProviderAndId($provider, $normalizedData['provider_id']);

        if ($existingAccount && $existingAccount['confirmed_at']) {
            // Vinculación confirmada -> LOGIN DIRECTO
            unset($_SESSION['oauth_state']);
            unset($_SESSION['oauth_provider']);
            
            $user = UserDAO::getById($existingAccount['user_id']);
            $this->session->login($user);
            
            header('Location: ' . BASE_URL . 'home');
            exit;
        }

        // Si el correo existe vinculado a otra cuenta (Enviar correo de petición de vinculación)
        $existingUser = UserDAO::getByEmail($normalizedData['email']);
        if ($existingUser) {
            $confirmationToken = bin2hex(random_bytes(32));
            
            try {
                AccountDAO::create(
                    $existingUser->getId(),
                    $provider,
                    $normalizedData['provider_id'],
                    $normalizedData['email'],
                    $confirmationToken
                );

                $this->sendLinkingConfirmationEmail(
                    $existingUser,
                    $provider,
                    $confirmationToken
                );

                $_SESSION['success'] = "Se ha enviado un email de confirmación. Por favor revisa tu bandeja.";
                unset($_SESSION['oauth_state']);
                unset($_SESSION['oauth_provider']);
                
                header('Location: ' . BASE_URL . 'login');
                exit;
            } catch (Exception $e) {
                $_SESSION['error'] = "Error al vincular cuenta: " . $e->getMessage();
                header('Location: ' . BASE_URL . 'login');
                exit;
            }
        }

        // Usuario nuevo (Crear cuenta)
        $existingByUsername = UserDAO::getByUsername($normalizedData['username']);
        if ($existingByUsername) {
            $_SESSION['oauth_pending'] = $normalizedData;
            unset($_SESSION['oauth_state']);
            unset($_SESSION['oauth_provider']);
            header('Location: ' . BASE_URL . 'oauth/choose-username');
            exit;
        }

        $this->createOAuthUser($normalizedData, $provider);
    }

    /**
     * Crear usuario OAuth y vinculación
     */
    public function createOAuthUser($normalizedData, $provider) {
        $randomPass = bin2hex(random_bytes(16)); 
        $hashedPass = password_hash($randomPass, PASSWORD_DEFAULT);

        try {
            $newUser = UserDAO::create(
                $normalizedData['username'],
                $normalizedData['display_name'],
                $normalizedData['email'],
                $hashedPass,
                Role::USER,
                $normalizedData['avatar']
            );

            if ($newUser) {
                // Crear vinculación directamente (usuario nuevo)
                $accountCreated = AccountDAO::create(
                    $newUser->getId(),
                    $provider,
                    $normalizedData['provider_id'],
                    $normalizedData['email']
                );

                unset($_SESSION['oauth_state']);
                unset($_SESSION['oauth_provider']);
                unset($_SESSION['oauth_pending']);
                
                $this->session->login($newUser);                
                header('Location: ' . BASE_URL . 'home');
                exit;
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Error al crear usuario: " . $e->getMessage();
        }
        
        $_SESSION['error'] = "Error al procesar login";
        header('Location: ' . BASE_URL . 'login');
        exit;
    }

    /**
     * Vista para elegir username (Conflicto)
     */
    public function showChooseUsername() {
        if (empty($_SESSION['oauth_pending'])) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        require_once BASE_PATH . '/app/view/oauth/choose-username-view.php';
    }

    /**
     * Enviar nuevo username del conflicto
     */
    public function submitUsername() {
        if (empty($_SESSION['oauth_pending'])) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        $username = trim($_POST['username'] ?? '');

        if (strlen($username) < 3) {
            $_SESSION['error'] = 'El nombre de usuario debe tener al menos 3 caracteres';
            header('Location: ' . BASE_URL . 'oauth/choose-username');
            exit;
        }

        if (UserDAO::getByUsername($username)) {
            $_SESSION['error'] = 'Ese nombre de usuario también está en uso. Prueba con otro.';
            header('Location: ' . BASE_URL . 'oauth/choose-username');
            exit;
        }

        $pendingData = $_SESSION['oauth_pending'];
        $pendingData['username'] = $username;
        $provider = $pendingData['provider'];

        $this->createOAuthUser($pendingData, $provider);
    }

    /**
     * Construir email de confirmación de vinculación de cuenta
     */
    private function sendLinkingConfirmationEmail($user, $provider, $token) {
        require_once BASE_PATH . '/lib/PHPMailer/src/PHPMailer.php';
        require_once BASE_PATH . '/lib/PHPMailer/src/SMTP.php';
        require_once BASE_PATH . '/lib/PHPMailer/src/Exception.php';

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = MAIL_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = MAIL_USER;
            $mail->Password = MAIL_PASS;
            $mail->SMTPSecure = MAIL_SECURE;
            $mail->Port = MAIL_PORT;

            $mail->setFrom(MAIL_FROM, MAIL_NAME);
            $mail->addAddress($user->getEmail(), $user->getDisplayName());

            $mail->isHTML(true);
            $mail->Subject = 'Confirmar vinculación de cuenta ' . ucfirst($provider);
            $confirmLink = BASE_URL . 'oauth/confirm-link?token=' . $token;

            $mail->Body = "
                <h2>Confirmar vinculación de cuenta</h2>
                <p>Hola {$user->getDisplayName()},</p>
                <p>Alguien ha intentado vincular tu cuenta de {$provider} a tu perfil.</p>
                <p>Si fuiste tú, haz clic en el enlace para confirmar:</p>
                <p><a href='{$confirmLink}' style='display: inline-block; padding: 10px 20px; background: #0066cc; color: white; text-decoration: none; border-radius: 5px;'>Confirmar vinculación</a></p>
                <p>Este enlace expira en 24 horas.</p>
                <p>Si no fuiste tú, puedes ignorar este email.</p>
            ";

            $mail->AltBody = "Confirma la vinculación en: {$confirmLink}";
            $mail->send();

        } catch (\Exception $e) {
            throw new Exception("Error al enviar email: " . $e->getMessage());
        }
    }

    /**
     * Normalizar datos
     */
    private function normalizeUserData($provider, $data) {
        $normalized = [
            'email' => null,
            'username' => null,
            'display_name' => null,
            'avatar' => null,
            'provider' => $provider,
            'provider_id' => null
        ];

        if ($provider === 'github') {
            $normalized['email'] = $data['email'] ?? null;
            $normalized['username'] = $data['login'] ?? ('github_' . ($data['id'] ?? uniqid()));
            $normalized['display_name'] = $data['name'] ?? $normalized['username'];
            $normalized['avatar'] = $data['avatar_url'] ?? null;
            $normalized['provider_id'] = $data['id'] ?? null;

        } elseif ($provider === 'discord') {
            $normalized['email'] = $data['email'] ?? null;
            $normalized['username'] = $data['username'] ?? ('discord_' . ($data['id'] ?? uniqid()));
            $normalized['display_name'] = $data['global_name'] ?? $data['username'] ?? $normalized['username'];
            
            if (isset($data['id']) && isset($data['avatar'])) {
                $normalized['avatar'] = "https://cdn.discordapp.com/avatars/{$data['id']}/{$data['avatar']}.png";
            } else {
                $normalized['avatar'] = null;
            }
            $normalized['provider_id'] = $data['id'] ?? null;
        }

        return $normalized;
    }
}