<?php

require_once BASE_PATH . '/app/model/dao/UserDAO.php';
require_once BASE_PATH . '/app/controller/auth/session/session-controller.php';
require_once BASE_PATH . '/app/controller/auth/cookie/cookie-controller.php';

class LoginController {

    private SessionController $session;

    public function __construct() {
        $this->session = new SessionController();
        $this->session->start();
    }

    /**
     * Procesar login
     */
    public function login(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showLoginForm();
            return;
        }

        $userInput = trim($_POST['user'] ?? '');
        $password  = $_POST['contrasenya'] ?? '';
        $csrfTokenInput = $_POST['csrf_token'] ?? null;

        if (!$this->session->validateCsrfToken($csrfTokenInput)) {
            $error = 'Sesión expirada o solicitud inválida. Recarga la página e inténtalo de nuevo.';
            $errorMessages = $this->splitErrorMessages($error);
            $captchaRequired = false;
            $successMsg = null;
            $userInputValue = $userInput;
            $isLogged = false;
            $currentUser = null;
            $avatarUrl = BASE_URL . 'public/uploads/avatars/default.webp';
            $csrfToken = $this->session->getCsrfToken();
            require BASE_PATH . '/app/view/auth/login-view.php';
            return;
        }

        $remember  = isset($_POST['recordar']);
        $successMsg = null;
        $userInputValue = $userInput;
        $isLogged = false;
        $currentUser = null;
        $avatarUrl = BASE_URL . 'public/uploads/avatars/default.webp';

        // Sistema de intentos fallidos
        $attempts = $_SESSION['login_attempts'][$userInput]['contador'] ?? 0;
        $captchaRequired = $attempts >= 3;

        // Validar captcha si es necesario
        if ($captchaRequired && empty($_POST['g-recaptcha-response'])) {
            $error = "El captcha es obligatorio.";
            $errorMessages = $this->splitErrorMessages($error);
            $csrfToken = $this->session->getCsrfToken();
            require BASE_PATH . '/app/view/auth/login-view.php';
            return;
        }

        // Buscar usuario por email o username
        $user = UserDAO::getByEmailOrUsername($userInput);

        // Verificar credenciales
        if ($user && password_verify($password, $user->getPassword())) {
            // Login exitoso - limpiar intentos
            unset($_SESSION['login_attempts'][$userInput]);

            // Iniciar sesión
            $this->session->login($user);

            // Recordar usuario si se pidió
            if ($remember) {
                $cookie = new CookieController();
                $cookie->setRememberMe($user);
            }

            // Redirigir según rol
            $this->redirectAfterLogin($user);
            return;
        }

        // Login fallido - incrementar intentos
        $_SESSION['login_attempts'][$userInput]['contador'] = $attempts + 1;
        $captchaRequired = $_SESSION['login_attempts'][$userInput]['contador'] >= 3;

        $error = "Usuario/Email o contraseña incorrectos.";
        $errorMessages = $this->splitErrorMessages($error);
        $csrfToken = $this->session->getCsrfToken();
        require BASE_PATH . '/app/view/auth/login-view.php';
    }

    /**
     * Procesar registro
     */
    public function register(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showRegisterForm();
            return;
        }

        $username         = trim($_POST['username'] ?? '');
        $csrfTokenInput   = $_POST['csrf_token'] ?? null;

        if (!$this->session->validateCsrfToken($csrfTokenInput)) {
            $error = 'Sesión expirada o solicitud inválida. Recarga la página e inténtalo de nuevo.';
            $errorMessages = $this->splitErrorMessages($error);
            $formUsername = trim($_POST['username'] ?? '');
            $formDisplayName = trim($_POST['displayname'] ?? '');
            $formEmail = trim($_POST['email'] ?? '');
            $isLogged = false;
            $currentUser = null;
            $avatarUrl = BASE_URL . 'public/uploads/avatars/default.webp';
            $csrfToken = $this->session->getCsrfToken();
            require BASE_PATH . '/app/view/auth/register-view.php';
            return;
        }

        $displayName      = trim($_POST['displayname'] ?? '');
        $email            = trim($_POST['email'] ?? '');
        $password         = $_POST['password'] ?? '';
        $confirmPassword  = $_POST['confirmPass'] ?? '';
        $isLogged = false;
        $currentUser = null;
        $avatarUrl = BASE_URL . 'public/uploads/avatars/default.webp';

        // Usar username si no hay displayname
        if (empty($displayName)) $displayName = $username;
        $username = strtolower($username); // Transformar username a toLowerCase()
        $formUsername = $username;
        $formDisplayName = $displayName;
        $formEmail = $email;

        // Validación de campos vacíos
        if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
            $error = "Todos los campos son obligatorios.";
            $errorMessages = $this->splitErrorMessages($error);
            $csrfToken = $this->session->getCsrfToken();
            require BASE_PATH . '/app/view/auth/register-view.php';
            return;
        }

        // Validar email
        if (!$this->isValidEmail($email)) {
            $error = "El email no es válido.";
            $errorMessages = $this->splitErrorMessages($error);
            $csrfToken = $this->session->getCsrfToken();
            require BASE_PATH . '/app/view/auth/register-view.php';
            return;
        }

        // Validar contraseñas coinciden
        if ($password !== $confirmPassword) {
            $error = "Las contraseñas no coinciden.";
            $errorMessages = $this->splitErrorMessages($error);
            $csrfToken = $this->session->getCsrfToken();
            require BASE_PATH . '/app/view/auth/register-view.php';
            return;
        }

        // Validar fortaleza de contraseña
        $passwordErrors = $this->validatePassword($password);
        if (!empty($passwordErrors)) {
            $error = implode("\n", $passwordErrors);
            $errorMessages = $this->splitErrorMessages($error);
            $csrfToken = $this->session->getCsrfToken();
            require BASE_PATH . '/app/view/auth/register-view.php';
            return;
        }

        // Verificar si el username ya existe
        if (UserDAO::getByUsername($username)) {
            $error = "Ese nombre de usuario ya existe.";
            $errorMessages = $this->splitErrorMessages($error);
            $csrfToken = $this->session->getCsrfToken();
            require BASE_PATH . '/app/view/auth/register-view.php';
            return;
        }

        // Verificar si el email ya existe
        if (UserDAO::getByEmail($email)) {
            $error = "Ese correo electrónico ya está registrado.";
            $errorMessages = $this->splitErrorMessages($error);
            $csrfToken = $this->session->getCsrfToken();
            require BASE_PATH . '/app/view/auth/register-view.php';
            return;
        }

        // Crear usuario
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $avatarUrl = 'public/uploads/avatars/default.webp';
        
        try {
            // ORDEN CORRECTO: username, displayName, email, passwordHash, role, avatarUrl
            $newUser = UserDAO::create(
                $username,
                $displayName,
                $email,
                $hashedPassword,
                Role::USER,
                $avatarUrl
            );

            // Login automático después del registro
            $this->session->login($newUser);

            // Redirigir a mis artículos
            header('Location: ' . BASE_URL . 'my-articles');
            exit;

        } catch (Exception $e) {
            error_log("Error al crear usuario: " . $e->getMessage());
            $error = "Error al crear el usuario. Por favor, inténtalo de nuevo.";
            $errorMessages = $this->splitErrorMessages($error);
            $csrfToken = $this->session->getCsrfToken();
            require BASE_PATH . '/app/view/auth/register-view.php';
        }
    }


    /**
     * Mostrar formulario de login
     */
    public function showLoginForm(): void {
        $this->redirectIfLoggedIn();

        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['error']);
        $captchaRequired = false;

        // Mensaje de éxito si viene del reseteo
        $success = null;
        if (isset($_SESSION['reset_success'])) {
            $success = "Contraseña actualizada correctamente. Ya puedes iniciar sesión.";
            unset($_SESSION['reset_success']);
        }

        $errorMessages = $this->splitErrorMessages($error);
        $successMsg = $success;
        $csrfToken = $this->session->getCsrfToken();
        $userInputValue = '';
        $isLogged = false;
        $currentUser = null;
        $avatarUrl = BASE_URL . 'public/uploads/avatars/default.webp';
        require BASE_PATH . '/app/view/auth/login-view.php';
    }

    /**
     * Mostrar formulario de registro
     */
    public function showRegisterForm(): void {
        $this->redirectIfLoggedIn();

        $errorMessages = [];
        $csrfToken = $this->session->getCsrfToken();
        $formUsername = '';
        $formDisplayName = '';
        $formEmail = '';
        $isLogged = false;
        $currentUser = null;
        $avatarUrl = BASE_URL . 'public/uploads/avatars/default.webp';
        require BASE_PATH . '/app/view/auth/register-view.php';
    }

    private function splitErrorMessages(?string $error): array {
        if (!$error) {
            return [];
        }

        if (strpos($error, "\n") !== false) {
            return array_values(array_filter(explode("\n", $error)));
        }

        return [$error];
    }

    /* 
    ··························
    ·   MÉTODOS PRIVADOS     ·
    ··························
    */

    /**
     * Validar formato de email
     */
    private function isValidEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validar fortaleza de contraseña
     * 
     * @return array Array de errores (vacío si la contraseña es válida)
     */
    private function validatePassword(string $password): array {
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = "La contraseña debe contener al menos 8 caracteres.";
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "La contraseña debe contener al menos una letra mayúscula.";
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "La contraseña debe contener al menos una letra minúscula.";
        }
        
        if (!preg_match('/\d/', $password)) {
            $errors[] = "La contraseña debe contener al menos un número.";
        }
        
        if (!preg_match('/[\W_]/', $password)) {
            $errors[] = "La contraseña debe contener al menos un símbolo (como !@#$%).";
        }

        return $errors;
    }

    /**
     * Redirigir si el usuario ya está logeado
     */
    private function redirectIfLoggedIn(): void {
        if ($this->session->isLogged()) {
            header('Location: ' . BASE_URL . 'home');
            exit;
        }
    }

    /**
     * Redirigir después del login según el rol del usuario
     */
    private function redirectAfterLogin(User $user): void {
        $redirectUrl = $user->getRole() === Role::ADMIN 
            ? BASE_URL . 'home' 
            : BASE_URL . 'my-articles';
            
        header('Location: ' . $redirectUrl);
        exit;
    }
}