<?php
require_once BASE_PATH . '/app/model/dao/UserDAO.php';
require_once BASE_PATH . '/app/controller/session-controller.php';

class AdminController {
    private $session;
    private $currentUser;

    private function requireAdmin() {
        $this->session = new SessionController();
        $this->session->start();

        $this->currentUser = $this->session->getUser();
        if (!$this->currentUser || !$this->currentUser->isAdmin()) {
            header('Location: ' . BASE_URL . 'home');
            exit;
        }
    }

    // Listar usuarios con paginación
    public function listUsers() {
        $this->requireAdmin();

        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        $perPage = 20;
        $total = UserDAO::countAll();
        $totalPages = ceil($total / $perPage);

        if ($page < 1) $page = 1;
        if ($page > $totalPages) $page = $totalPages;

        $users = UserDAO::listAll($perPage, ($page-1)*$perPage);
        require BASE_PATH . '/app/view/users-view.php';
    }

    // Formulario de edición de usuario
    public function editForm($id) {
        $this->requireAdmin();

        $user = UserDAO::getById($id);
        if (!$user) {
            http_response_code(404);
            echo "Usuario no encontrado";
            exit;
        }

        // Determinar si el admin está editando su propio perfil
        $isProfile = $this->currentUser->getId() === $user->getId();

        require BASE_PATH . '/app/view/profile-view.php';
    }

    // Guardar cambios de usuario
    public function editSubmit($id) {
        $this->requireAdmin();

        $user = UserDAO::getById($id);
        if (!$user) {
            http_response_code(404);
            echo "Usuario no encontrado";
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $rol      = $_POST['rol'] ?? $user->getRol();
        $currentPass = $_POST['pass'] ?? '';
        $newPass = $_POST['pass-nueva'] ?? '';

        // Bloquear el cambio de rol si es perfil propio
        $isProfile = $this->currentUser->getId() === $user->getId();
        if ($isProfile) {
            $rol = $user->getRol();
        }

        // Cambiar contraseña si hay nueva
        if (!empty($newPass)) {
            $user->setPassword(password_hash($newPass, PASSWORD_DEFAULT));
        }

        // Actualizar datos
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setRol($rol);

        // Subir avatar
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = BASE_PATH . '/public/uploads/avatars/';
            $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $filename = 'user_' . $user->getId() . '.' . $ext;
            move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadDir . $filename);
            $user->setAvatar('public/uploads/avatars/' . $filename);
        }

        UserDAO::updateAll($user);

        $successMsg = 'Usuario actualizado correctamente.';

        require BASE_PATH . '/app/view/profile-view.php';
    }

    // Eliminar usuario
    public function delete($id) {
        $this->requireAdmin();

        $user = UserDAO::getById($id);
        if ($user) {
            UserDAO::delete($user);
        }
        header('Location: ' . BASE_URL . 'admin/users');
        exit;
    }
}
