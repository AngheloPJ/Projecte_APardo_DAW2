<?php

require_once BASE_PATH . '/app/model/dao/UserDAO.php';
require_once BASE_PATH . '/app/model/entity/User.php';

class UserController {
    public function showUserProfile() {
        require BASE_PATH . '/app/view/profile-view.php';
    }

    public function showEditForm() {
        $session = new SessionController();
        $user = $session->getUser();

        if (!$user) {
            header("Location: " . BASE_URL . "login");
            exit;
        }

        require BASE_PATH . '/app/view/profile-view.php';
    }

    public function editSubmit() {
        $userId = $_SESSION['user_id'];
        $user = UserDAO::getById($userId);

        if (!$user) {
            http_response_code(404);
            require_once BASE_PATH . '/public/errors/404-view.php';
            exit();
        }

        $username    = trim($_POST['username'] ?? '');
        $email       = trim($_POST['email'] ?? '');
        $currentPass = $_POST['pass'] ?? '';
        $newPass     = $_POST['pass-nueva'] ?? '';

        if (!empty($currentPass) && !password_verify($currentPass, $user->getPassword())) {
            $errorMsg = 'Contraseña actual incorrecta.';
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

        UserDAO::updateCredentials($user);

        $successMsg = 'Perfil actualizado correctamente.';
        if ($changedPass) {
            $successMsg .= ' ¡Contraseña actualizada!';
        }

        require BASE_PATH . '/app/view/profile-view.php';
    }


}

?>