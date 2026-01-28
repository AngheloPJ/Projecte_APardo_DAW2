<?php

require_once BASE_PATH . '/app/model/dao/UserDAO.php';
require_once BASE_PATH . '/app/model/entity/User.php';
require_once BASE_PATH . '/app/controller/session-controller.php';

class UserController {

    private $session;
    private $user;

    public function __construct() {
        $this->session = new SessionController();
        $this->session->start();
        $this->user = $this->session->getUser();
    }

    /**
     * Mostrar perfil de usuario (propio)
     */
    public function showUserProfile() {
        if (!$this->user) {
            header("Location: " . BASE_URL . "login");
            exit;
        }

        $isProfile = true;
        $user = $this->user;
        require BASE_PATH . '/app/view/profile-view.php';
    }

    /**
     * Mostrar formulario de edición de perfil propio
     */
    public function showEditForm() {
        if (!$this->user) {
            header("Location: " . BASE_URL . "login");
            exit;
        }

        $isProfile = true;
        $user = $this->user;
        require BASE_PATH . '/app/view/profile-view.php';
    }

    /**
     * Guardar cambios de perfil propio
     */
    public function editSubmit() {
        if (!$this->user) {
            header("Location: " . BASE_URL . "login");
            exit;
        }

        $userId = $this->user->getId();
        $user = UserDAO::getById($userId);

        if (!$user) {
            http_response_code(404);
            require BASE_PATH . '/public/errors/404-view.php';
            exit();
        }

        $username    = trim($_POST['username'] ?? '');
        $email       = trim($_POST['email'] ?? '');
        $currentPass = $_POST['pass'] ?? '';
        $newPass     = $_POST['pass-nueva'] ?? '';
        $confirmPass = $_POST['confirm-nueva'] ?? '';

        // Verificar contraseña actual
        if (!empty($currentPass) && !password_verify($currentPass, $user->getPassword())) {
            $errorMsg = 'Contraseña actual incorrecta.';
            $isProfile = true;
            require BASE_PATH . '/app/view/profile-view.php';
            return;
        }

        // Comparar contraseñas
        if (!empty($newPass) && $newPass !== $confirmPass) {
            $errorMsg = 'Las contraseñas no coinciden.';
            $isProfile = true;
            require BASE_PATH . '/app/view/profile-view.php';
            return;
        }

        $user->setUsername($username);
        $user->setEmail($email);

        $changedPass = false;
        if (!empty($newPass)) {
            $user->setPassword(password_hash($newPass, PASSWORD_DEFAULT));
            $changedPass = true;
        }

        // Avatar
        if (!empty($_FILES['avatar']['tmp_name'])) {
            $avatarPath = '/public/uploads/avatars/' . $user->getId() . '.png';
            move_uploaded_file($_FILES['avatar']['tmp_name'], BASE_PATH . $avatarPath);
            $user->setAvatar($avatarPath);
        }

        UserDAO::updateAll($user);

        if ($changedPass) return $successMsg .= ' ¡Contraseña actualizada!';
        else $successMsg = 'Perfil actualizado correctamente.';

        $isProfile = true;
        require BASE_PATH . '/app/view/profile-view.php';
    }

    /**
     * Mostrar formulario de edición de cualquier usuario (admin)
     */
    public function showEditUser($id) {
        if (!$this->user || !$this->user->isAdmin()) {
            header("Location: " . BASE_URL . "home");
            exit;
        }

        $user = UserDAO::getById($id);
        if (!$user) {
            http_response_code(404);
            require BASE_PATH . '/public/errors/404-view.php';
            exit();
        }

        $isProfile = false;
        require BASE_PATH . '/app/view/profile-view.php';
    }

    /**
     * Guardar cambios de cualquier usuario (admin)
     */
    public function editUserSubmit($id) {
        if (!$this->user || !$this->user->isAdmin()) {
            header("Location: " . BASE_URL . "home");
            exit;
        }

        $user = UserDAO::getById($id);
        if (!$user) {
            http_response_code(404);
            require BASE_PATH . '/public/errors/404-view.php';
            exit();
        }

        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $rol      = $_POST['rol'] ?? $user->getRol();
        $newPass  = $_POST['pass-nueva'] ?? '';

        $user->setUsername($username);
        $user->setEmail($email);
        $user->setRol($rol);

        if (!empty($newPass)) {
            $user->setPassword(password_hash($newPass, PASSWORD_DEFAULT));
        }

        // Avatar
        if (!empty($_FILES['avatar']['tmp_name'])) {
            $avatarPath = '/public/uploads/avatars/' . $user->getId() . '.png';
            move_uploaded_file($_FILES['avatar']['tmp_name'], BASE_PATH . $avatarPath);
            $user->setAvatar($avatarPath);
        }

        UserDAO::updateAll($user);

        $successMsg = 'Usuario actualizado correctamente.';
        $isProfile = false;
        require BASE_PATH . '/app/view/profile-view.php';
    }
}
