<?php

require_once BASE_PATH . '/app/controller/oauth/oauth-controller.php';
require_once BASE_PATH . '/app/controller/oauth/hybridauth-controller.php';

class OAuthCallbackController {

    private OAuthController $oauthController;
    private HybridAuthController $hybridAuthController;

    public function __construct() {
        $this->oauthController = new OAuthController();
        $this->hybridAuthController = new HybridAuthController();
    }

    // OAuth

    /**
     * Redirigir al proveedor (Discord)
     */
    public function redirect($provider = null) {
        if (!$provider) {
            $_SESSION['error'] = 'Proveedor no especificado';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        $this->oauthController->redirect($provider);
    }

    /**
     * Callback del proveedor (Discord)
     */
    public function discord() {
        $this->oauthController->callback('discord');
    }

    // HybridAuth

    /**
     * Redirigir al proveedor (GitHub)
     */
    public function hybridRedirect($provider = null) {
        if (!$provider) {
            $_SESSION['error'] = 'Proveedor no especificado';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        $this->hybridAuthController->redirect($provider);
    }

    /**
     * Callback del proveedor (GitHub)
     */
    public function hybridCallback($provider = null) {
        if (!$provider) {
            $_SESSION['error'] = 'Proveedor no especificado';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        $this->hybridAuthController->callback($provider);
    }

    /**
     * Eliminar nombre cuando hay conflicto de usernames
     */
    public function chooseUsername() {
        if (!empty($_SESSION['oauth_pending'])) {
            if ($_SESSION['oauth_pending']['provider'] === 'github') $this->hybridAuthController->showChooseUsername();
            else $this->oauthController->showChooseUsername();
        } else {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
    }

    /**
     * Enviar nuevo username del conflicto
     */
    public function chooseUsernameSubmit() {
        if (!empty($_SESSION['oauth_pending'])) {
            if ($_SESSION['oauth_pending']['provider'] === 'github') $this->hybridAuthController->submitUsername();
            else $this->oauthController->submitUsername();
        } else {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
    }

    /**
     * Confirmar token de validación
     */
    public function confirmLink() {
        $token = $_GET['token'] ?? null;

        if (!$token) {
            $_SESSION['error'] = 'Token no válido';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        require_once BASE_PATH . '/app/model/dao/AccountDAO.php';
        $account = AccountDAO::getByConfirmationToken($token);

        if (!$account) {
            $_SESSION['error'] = 'Token expirado o no válido';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        if (AccountDAO::confirmByToken($token)) {
            $_SESSION['success'] = 'Cuenta vinculada correctamente. Ya puedes iniciar sesión con ' . ucfirst($account['provider']);
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        $_SESSION['error'] = 'Error al confirmar la vinculación';
        header('Location: ' . BASE_URL . 'login');
        exit;
    }
}
