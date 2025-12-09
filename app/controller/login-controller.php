<?php
require_once BASE_PATH . '/app/model/dao/UserDAO.php';
require_once BASE_PATH . '/app/controller/session-controller.php';
require_once BASE_PATH . '/app/controller/cookie-controller.php';

class LoginController {

    private $session;

    public function __construct() {
        $this->session = new SessionController();
        $this->session->start();
    }

    /**
     * Mostrar formulario de login
     */
    public function showLoginForm() {
        require BASE_PATH . '/app/view/login-view.php';
    }

    /**
     * Procesar login
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $userInput = trim($_POST['user'] ?? '');
        $password  = $_POST['contrasenya'] ?? '';
        $remember  = isset($_POST['recordar']);

        $user = UserDAO::getByEmailOrName($userInput);

        if ($user && password_verify($password, $user->getPassword())) {
            // Iniciar sesión
            $this->session->login($user);

            // Recordar usuario si se pidió
            if ($remember) {
                $cookie = new CookieController();
                $cookie->setRememberMe($user);
            }

            // Redirigir según rol
            $role = $user->getRol();
            header('Location: ' . BASE_URL . ($role === 'admin' ? 'home' : 'my-articles'));
            exit;
        }

        $error = "Usuario/Email o contraseña incorrectos.";
        require BASE_PATH . '/app/view/login-view.php';
    }

    /**
     * Mostrar formulario de registro
     */
    public function showRegisterForm() {
        require BASE_PATH . '/app/view/register-view.php';
    }

    /**
     * Procesar registro
     */
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $name              = trim($_POST['nom'] ?? '');
        $email             = trim($_POST['email'] ?? '');
        $password          = $_POST['contrasenya'] ?? '';
        $confirm_password  = $_POST['confirmar-pass'] ?? '';

        // Validaciones básicas
        if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
            $error = "Todos los campos son obligatorios.";
            require BASE_PATH . '/app/view/register-view.php';
            return;
        }

        if (!$this->isValidEmail($email)) {
            $error = "El email no es válido.";
            require BASE_PATH . '/app/view/register-view.php';
            return;
        }

        if ($password !== $confirm_password) {
            $error = "Las contraseñas no coinciden.";
            require BASE_PATH . '/app/view/register-view.php';
            return;
        }

        $passwordErrors = $this->validatePassword($password);
        if (!empty($passwordErrors)) {
            $error = implode(" ", $passwordErrors);
            require BASE_PATH . '/app/view/register-view.php';
            return;
        }

        if (UserDAO::getByName($name)) {
            $error = "Ese nombre de usuario ya existe.";
            require BASE_PATH . '/app/view/register-view.php';
            return;
        }

        if (UserDAO::getByEmail($email)) {
            $error = "Ese correo electrónico ya existe.";
            require BASE_PATH . '/app/view/register-view.php';
            return;
        }

        // Crear usuario
        $hashedPass = password_hash($password, PASSWORD_DEFAULT);
        $created = UserDAO::create($name, $email, $hashedPass);

        if ($created) {
            $user = UserDAO::getByEmail($email);
            $this->session->login($user);

            header('Location: ' . BASE_URL . 'my-articles');
            exit;
        }

        $error = "Error al crear el usuario.";
        require BASE_PATH . '/app/view/register-view.php';
    }

    /**
     * Validar email
     */
    private function isValidEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validar contraseña
     */
    private function validatePassword(string $password): array {
        $errors = [];

        if (strlen($password) < 8) $errors[] = "La contraseña debe contener al menos 8 caracteres.";
        if (!preg_match('/[A-Z]/', $password)) $errors[] = "La contraseña debe contener al menos una letra mayúscula.";
        if (!preg_match('/[a-z]/', $password)) $errors[] = "La contraseña debe contener al menos una letra minúscula.";
        if (!preg_match('/\d/', $password)) $errors[] = "La contraseña debe contener al menos un número.";
        if (!preg_match('/[\W_]/', $password)) $errors[] = "La contraseña debe contener al menos un símbolo (como !@#$%).";

        return $errors;
    }
}
