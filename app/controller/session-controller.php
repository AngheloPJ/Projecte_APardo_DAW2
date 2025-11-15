<?php

/* 
    ····························
    · Controlador de Session's ·
    ····························
*/

class SessionController {

    /** 
     * Función para comprobar si el usuario está logeado
     */
    public function isLogged(): bool {
        return isset($_SESSION['user_id']);
    }

    /**
     * Función para iniciar sesión (Guardar ID en sesión)
     */
    public function login($userId) {
        $_SESSION['user_id'] = $userId;
    }

    /**
     * Función para cerrar sesión
     */
    public function logout() {
        session_unset();
        session_destroy();
    }
}
