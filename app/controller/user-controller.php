<?php

require_once BASE_PATH . '/app/model/dao/UserDAO.php';
require_once BASE_PATH . '/app/model/entity/User.php';

class UserController {
    public function showUserProfile() {
        require BASE_PATH . '/app/view/profile-view.php';
    }

    public function showEditForm($id) {
        $user = UserDAO::getById($id);
        if (!$user) {
            http_response_code(404);
            exit('Usuario no encontrado');
        }

        require BASE_PATH . '/app/view/profile-view.php';
    }

    public function editSubmit() {
        $userId = $_SESSION['user_id'];
        $user = UserDAO::getById($userId);

        if (!$user) {
            http_response_code(404);
            exit('Usuario no encontrado');
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