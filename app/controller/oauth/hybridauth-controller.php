<?php

require_once BASE_PATH . '/lib/hybridauth-3.12.2/src/autoload.php';
require_once BASE_PATH . '/app/model/dao/UserDAO.php';
require_once BASE_PATH . '/app/model/dao/AccountDAO.php';
require_once BASE_PATH . '/app/controller/auth/session/session-controller.php';

class HybridAuthController {

    private SessionController $session;

    public function __construct() {
        $this->session = new SessionController();
        $this->session->start();
    }

    /**
     * Obtener datos del .env
     */
    private function getConfig(string $provider): array {
        return [
            'callback' => BASE_URL . 'oauth/hybridauth/callback/' . $provider,
            'providers' => [
                'GitHub' => [
                    'enabled' => true,
                    'keys' => [
                        'id' => GITHUB_CLIENT_ID,
                        'secret' => GITHUB_CLIENT_SECRET,
                    ],
                    'scope' => 'user:email',
                ],
            ],
        ];
    }

    /**
     * Función auxiliar para resolver el proveedor Github
     */
    private function resolveProviderName(string $provider): ?string {
        $map = ['github' => 'GitHub' ];
        return $map[strtolower($provider)] ?? null;
    }

    /**
     * Redirigir al proveedor (GitHub)
     */
    public function redirect(string $provider) {
        $providerName = $this->resolveProviderName($provider);
        if (!$providerName) {
            $_SESSION['error'] = 'Proveedor HybridAuth no soportado';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        try {
            $config = $this->getConfig($provider);
            $hybridauth = new Hybridauth\Hybridauth($config);
            $adapter = $hybridauth->authenticate($providerName);
            
            $this->processCallback($provider, $adapter);

        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error al conectar con ' . ucfirst($provider);
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
    }

    /**
     * Callback del proveedor (GitHub)
     */
    public function callback(string $provider) {
        $providerName = $this->resolveProviderName($provider);
        if (!$providerName) {
            $_SESSION['error'] = 'Proveedor no válido';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        try {
            $config = $this->getConfig($provider);
            $hybridauth = new Hybridauth\Hybridauth($config);
            $adapter = $hybridauth->authenticate($providerName);

            $this->processCallback($provider, $adapter);

        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error en la autenticación con ' . ucfirst($provider);
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
    }

    /**
     * Procesar el perfil autenticado
     */
    private function processCallback(string $provider, $adapter) {
        try {
            $userProfile = $adapter->getUserProfile();
            $githubLogin = null;
            if (strtolower($provider) === 'github') {
                try {
                    $rawUser = $adapter->apiRequest('user');
                    $githubLogin = $rawUser->login ?? null;

                    // Si GitHub no trae email en el perfil, busca en /user/emails de github
                    if (empty($userProfile->email)) {
                        $emails = $adapter->apiRequest('user/emails');
                        if (is_array($emails)) {
                            foreach ($emails as $emailItem) {
                                $item = is_object($emailItem) ? (array)$emailItem : (array)$emailItem;
                                $isPrimary = !empty($item['primary']);
                                $isVerified = !empty($item['verified']);
                                if ($isPrimary && $isVerified && !empty($item['email'])) {
                                    $userProfile->email = (string)$item['email'];
                                    break;
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                }
            }

            $normalizedData = $this->normalizeUserData($provider, $userProfile, $githubLogin);
            if (!$normalizedData['email'] || !$normalizedData['username'] || !$normalizedData['provider_id']) {
                $_SESSION['error'] = "No se pudieron obtener datos del usuario";
                header('Location: ' . BASE_URL . 'login');
                exit;
            }

            $adapter->disconnect();
            $this->loginOrRegister($provider, $normalizedData);

        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error al obtener datos del usuario';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
    }


    /**
     * Función auxiliar para normalizar los datos para el objeto
     */
    private function normalizeUserData(string $provider, $profile, ?string $githubLogin = null): array {
        $normalized = [
            'email' => $profile->email ?? null,
            'username' => null,
            'display_name' => $profile->displayName ?? null,
            'avatar' => $profile->photoURL ?? null,
            'provider' => $provider,
            'provider_id' => $profile->identifier ?? null,
        ];

        if (strtolower($provider) === 'github') {
            $normalized['username'] = $githubLogin ?? $profile->displayName ?? ('github_' . ($profile->identifier ?? uniqid()));
            $normalized['display_name'] = $profile->displayName ?? $githubLogin ?? $normalized['username'];
        }

        return $normalized;
    }

    /**
     * Autentificarse o registrarse
     */
    private function loginOrRegister(string $provider, array $normalizedData) {
        $existingAccount = AccountDAO::getByProviderAndId($provider, $normalizedData['provider_id']);
        if ($existingAccount && $existingAccount['confirmed_at']) {
            $user = UserDAO::getById($existingAccount['user_id']);
            $this->session->login($user);
            header('Location: ' . BASE_URL . 'home');
            exit;
        }

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

                $this->sendLinkingConfirmationEmail($existingUser, $provider, $confirmationToken);

                $_SESSION['success'] = "Se ha enviado un email de confirmación. Por favor revisa tu bandeja.";
                header('Location: ' . BASE_URL . 'login');
                exit;

            } catch (\Exception $e) {
                $_SESSION['error'] = "Error al vincular cuenta: " . $e->getMessage();
                header('Location: ' . BASE_URL . 'login');
                exit;
            }
        }

        $existingByUsername = UserDAO::getByUsername($normalizedData['username']);
        if ($existingByUsername) {
            $_SESSION['oauth_pending'] = $normalizedData;
            header('Location: ' . BASE_URL . 'oauth/choose-username');
            exit;
        }

        $this->createOAuthUser($normalizedData, $provider);
    }
    
    /**
     * Crear autentificación HybridAuth
     */
    public function createOAuthUser(array $normalizedData, string $provider) {
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
                AccountDAO::create(
                    $newUser->getId(),
                    $provider,
                    $normalizedData['provider_id'],
                    $normalizedData['email']
                );

                unset($_SESSION['oauth_pending']);
                $this->session->login($newUser);

                header('Location: ' . BASE_URL . 'home');
                exit;
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error al crear usuario: " . $e->getMessage();
        }

        $_SESSION['error'] = "Error al procesar login";
        header('Location: ' . BASE_URL . 'login');
        exit;
    }

    /**
     * Construir email de confirmación
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
            throw new \Exception("Error al enviar email: " . $e->getMessage());
        }
    }

    /**
     * Vista para elegir username (Conflicto)
     */
    public function showChooseUsername() {
        if (empty($_SESSION['oauth_pending'])) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        $pending = $_SESSION['oauth_pending'];
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['error']);

        $suggestedUsername = $pending['username'] ?? '';
        $provider = $pending['provider'] ?? '';
        $avatar = $pending['avatar'] ?? null;
        $displayName = $pending['display_name'] ?? '';
        $isLogged = false;
        $currentUser = null;
        $avatarUrl = BASE_URL . 'public/uploads/avatars/default.webp';
        $csrfToken = $this->session->getCsrfToken();

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

        $csrfToken = $_POST['csrf_token'] ?? null;
        if (!$this->session->validateCsrfToken($csrfToken)) {
            $_SESSION['error'] = 'Solicitud inválida. Recarga la página e inténtalo de nuevo.';
            header('Location: ' . BASE_URL . 'oauth/choose-username');
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
}
