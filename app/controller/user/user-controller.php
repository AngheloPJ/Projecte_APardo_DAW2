<?php

require_once BASE_PATH . '/app/model/dao/UserDAO.php';
require_once BASE_PATH . '/app/model/entity/User.php';
require_once BASE_PATH . '/app/controller/auth/session/session-controller.php';
require_once BASE_PATH . '/app/utils/avatar-utils.php';

class UserController {

    private SessionController $session;
    private ?User $currentUser;

    public function __construct() {
        $this->session = new SessionController();
        $this->session->start();
        $this->currentUser = $this->session->getUser();
    }

    /**
     * Mostrar perfil del usuario logueado
     */
    public function showUserProfile(): void {
        if (!$this->currentUser) {
            header("Location: " . BASE_URL . "login");
            exit;
        }

        $this->renderProfileView($this->currentUser, true);
    }

    /**
     * Función para mostrar formulario de edición de cualquier usuario (admin)
     */
    public function showEditUser(int $id): void {
        $this->validateAdminAccess();

        $user = UserDAO::getById($id);
        if (!$user) $this->show404();

        $isOwnProfile = $this->currentUser->getId() === $user->getId();        
        $this->renderProfileView($user, $isOwnProfile);
    }

    /**
     * Función para guardar cambios del perfil propio
     */
    public function editSubmit(): void {
        if (!$this->currentUser) {
            header("Location: " . BASE_URL . "login");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showUserProfile();
            return;
        }

        $user = UserDAO::getById($this->currentUser->getId());
        if (!$user) $this->show404();

        $this->processUserForm($user, true);
    }

    /**
     * Función guardar datos editados de un usuario (Admin)
     */
    public function editUserSubmit(int $id): void {
        $this->validateAdminAccess();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showEditUser($id);
            return;
        }

        $user = UserDAO::getById($id);
        if (!$user) $this->show404();

        $isOwnProfile = $this->currentUser->getId() === $user->getId();        
        $this->processUserForm($user, $isOwnProfile);
    }

    /**
     * Función para eliminar usuario
     */
    public function delete(int $id): void {
        $this->validateAdminAccess();

        // Evitar que el admin sea down (No se borre a el mismo)
        if ($id === $this->currentUser->getId()) {
            header('Location: ' . BASE_URL . 'admin/users?error=self_delete');
            exit;
        }

        $user = UserDAO::getById($id);
        
        if ($user) {
            // Eliminar avatar físico antes de borrar usuario
            $this->deleteAvatarFile($user->getAvatarUrl());
            
            UserDAO::delete($id);
            header('Location: ' . BASE_URL . 'admin/users?success=deleted');
            exit;
        }

        header('Location: ' . BASE_URL . 'admin/users');
        exit;
    }

    /**
     * Función para listar todos los usuarios
     */
    public function listUsers(): void {
        $this->validateAdminAccess();

        /* Validación para la vista */

        $successMsg = null;
        $errorMsg = null;

        $success = $_GET['success'] ?? null;
        $error = $_GET['error'] ?? null;

        if ($success === 'deleted') $successMsg = 'Usuario eliminado correctamente.';
        if ($error === 'self_delete') $errorMsg = 'No puedes eliminarte a ti mismo.';
        elseif ($error === 'delete') $errorMsg = 'Error al eliminar el usuario.';

        // ---

        $page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
        $perPage = 20;
        
        $total = UserDAO::countAll();
        $totalPages = max(1, (int)ceil($total / $perPage));

        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $offset = ($page - 1) * $perPage;
        $usersRaw = UserDAO::listAll($perPage, $offset);
        $users = [];

        foreach ($usersRaw as $u) {
            $users[] = [
                'id' => $u->getId(),
                'username' => $u->getUsername(),
                'displayName' => $u->getDisplayName(),
                'email' => $u->getEmail(),
                'avatarUrl' => buildAvatarURL($u->getAvatarUrl()),
                'roleValue' => $u->getRole()->value,
                'roleLabel' => $u->getRole() === Role::ADMIN ? 'Admin' : 'Usuario',
                'createdAtFormatted' => date('d/m/Y', strtotime((string)$u->getCreatedAt())),
                'canDelete' => $this->currentUser->getId() !== $u->getId(),
            ];
        }

        $currentUser = $this->currentUser;
        $isLogged = $currentUser !== null;
        $avatarUrl = buildAvatarURL($currentUser ? $currentUser->getAvatarUrl() : null);

        require BASE_PATH . '/app/view/user/users-view.php';
    }

    /**
     * Función refactor creada al ver el mismo código
     * en las 2 funciones anteriores.
     */
    private function processUserForm(User $user, bool $isProfile): void {
        $errorMsg = null;
        $successMsg = null;

        $username = trim($_POST['username'] ?? '');
        $displayName = trim($_POST['displayname'] ?? '');
        $email = trim($_POST['email'] ?? '');

        // Checks | Campos vacíos o Email sin formato correcto
        if (empty($username) || empty($email)) {
            $errorMsg = 'El nombre de usuario y el email son obligatorios.';
            $this->renderProfileView($user, $isProfile, $errorMsg);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMsg = 'El email no es válido.';
            $this->renderProfileView($user, $isProfile, $errorMsg);
            return;
        }

        // Check | Username en uso
        if ($username !== $user->getUsername()) {
            $existing = UserDAO::getByUsername($username);
            if ($existing && $existing->getId() !== $user->getId()) {
                $errorMsg = 'Ese nombre de usuario ya está en uso.';
                $this->renderProfileView($user, $isProfile, $errorMsg);
                return;
            }
        }

        // Check | Email en uso
        if ($email !== $user->getEmail()) {
            $existing = UserDAO::getByEmail($email);
            if ($existing && $existing->getId() !== $user->getId()) {
                $errorMsg = 'Ese email ya está en uso.';
                $this->renderProfileView($user, $isProfile, $errorMsg);
                return;
            }
        }

        // Actualizar usuario (Entity)
        $user->setUsername($username);
        $user->setDisplayName($displayName);
        $user->setEmail($email);

        $newPass = $_POST['pass-nueva'] ?? '';
        $confirmPass = $_POST['confirm-nueva'] ?? '';
        $passwordChanged = false;

        // Perfil propio
        if ($isProfile) {
            $currentPass = $_POST['pass'] ?? '';
            
            // Si intenta cambiar la pass
            if (!empty($newPass)) {
                if (empty($currentPass)) {
                    $errorMsg = 'Debes ingresar tu contraseña actual para cambiarla.';
                } elseif (!password_verify($currentPass, $user->getPassword())) {
                    $errorMsg = 'La contraseña actual es incorrecta.';
                } elseif ($newPass !== $confirmPass) {
                    $errorMsg = 'Las contraseñas nuevas no coinciden.';
                } else {
                    $errors = $this->validatePassword($newPass);
                    if (!empty($errors)) $errorMsg = "La contraseña debe incluir:<br>" . implode('<br>', $errors);
                }
            }
        } else {
            // Gestión de Rol (Solo Admin)
            $roleVal = (int)($_POST['rol'] ?? $user->getRole()->value);
            $user->setRole(Role::from($roleVal));

            if (!empty($newPass)) {
                if ($newPass !== $confirmPass) {
                    $errorMsg = 'Las contraseñas no coinciden.';
                } else {
                    $errors = $this->validatePassword($newPass);
                    if (!empty($errors)) $errorMsg = implode('<br>', $errors);
                }
            }
        }

        // Comprobar error
        if ($errorMsg) {
            $this->renderProfileView($user, $isProfile, $errorMsg);
            return;
        }

        // Guardar contraseña
        if (!empty($newPass)) {
            UserDAO::updatePassword($user->getId(), password_hash($newPass, PASSWORD_DEFAULT));
            $passwordChanged = true;
        }

        // Avatar
        $avatarResult = $this->handleAvatarUpload($user);
        if (isset($avatarResult['error'])) {
            $errorMsg = $avatarResult['error'];
        }

        // Guardar en BBDD
        if (!$errorMsg) {
            UserDAO::update($user);
            
            if ($passwordChanged) {
                $successMsg = 'Perfil y contraseña actualizados correctamente.';
            } elseif (isset($avatarResult['success'])) {
                $successMsg = 'Avatar actualizado correctamente.';
            } else {
                $successMsg = 'Perfil actualizado correctamente.';
            }
        }

        $this->renderProfileView($user, $isProfile, $errorMsg, $successMsg);
    }

    private function handleAvatarUpload(User $user): array {
        if (isset($_POST['remove_avatar']) && $_POST['remove_avatar'] === '1') {
            $this->deleteAvatarFile($user->getAvatarUrl());
            $user->setAvatarUrl(null);
            return ['success' => 'Avatar eliminado.'];
        }

        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = mime_content_type($_FILES['avatar']['tmp_name']);

            if (!in_array($fileType, $allowedTypes)) {
                return ['error' => 'Tipo de imagen no permitido.'];
            }

            if ($_FILES['avatar']['size'] > 2097152) {
                return ['error' => 'La imagen es demasiado grande (máx 2MB).'];
            }

            $extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $fileName = 'avatar_' . $user->getId() . '_' . time() . '.' . $extension;
            $relativePath = 'public/uploads/avatars/' . $fileName;
            $absolutePath = BASE_PATH . '/' . $relativePath;

            // Crear dinrectorio si no existe
            $dir = dirname($absolutePath);
            if (!is_dir($dir)) mkdir($dir, 0755, true);

            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $absolutePath)) {
                $this->deleteAvatarFile($user->getAvatarUrl());
                $user->setAvatarUrl($relativePath);
                return ['success' => 'Avatar actualizado.'];
            } else {
                return ['error' => 'Error al mover el archivo al servidor.'];
            }
        }

        return [];
    }

    private function deleteAvatarFile(?string $avatarUrl): void {
        if ($avatarUrl) {
            $fullPath = BASE_PATH . '/' . $avatarUrl;
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
    }

    private function validateAdminAccess(): void {
        if (!$this->currentUser || !$this->currentUser->isAdmin()) {
            header("Location: " . BASE_URL . "home");
            exit;
        }
    }

    private function show404(): void {
        http_response_code(404);
        require BASE_PATH . '/public/errors/404-view.php';
        exit;
    }

    private function renderProfileView(User $user, bool $isProfile, ?string $errorMsg = null, ?string $successMsg = null): void {
        $currentUser = $this->currentUser;
        $isLogged = $currentUser !== null;
        $avatarUrl = buildAvatarURL($currentUser ? $currentUser->getAvatarUrl() : null);

        $formTitle = $isProfile ? 'Mi Perfil' : 'Perfil de ' . $user->getUsername();
        $actionUrl = $isProfile ? BASE_URL . 'profile/edit-submit' : BASE_URL . 'admin/users/edit-submit/' . $user->getId();
        $currentAvatar = buildAvatarURL($user->getAvatarUrl());
        $formTitleIcon = $currentAvatar;

        require BASE_PATH . '/app/view/user/profile-view.php';
    }

    private function validatePassword(string $password): array {
        $errors = [];
        if (strlen($password) < 8) $errors[] = "· Mínimo 8 carácteres.";
        if (!preg_match('/[A-Z]/', $password)) $errors[] = "· Al menos una mayúscula.";
        if (!preg_match('/[a-z]/', $password)) $errors[] = "· Al menos una minúscula.";
        if (!preg_match('/\d/', $password)) $errors[] = "· Al menos un número.";
        if (!preg_match('/[\W_]/', $password)) $errors[] = "· Al menos un símbolo.";
        return $errors;
    }
}