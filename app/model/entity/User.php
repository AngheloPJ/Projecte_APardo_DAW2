<?php

class User {
    private $id;
    private $nom;
    private $email;
    private $rol;
    private $contrasenya;

    public function __construct($id, $nom, $email, $rol, $contrasenya) {
        $this->id = $id;
        $this->nom = $nom;
        $this->email = $email;
        $this->rol = $rol;
        $this->contrasenya = $contrasenya;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getUsername() { return $this->nom; }
    public function getEmail() { return $this->email; }
    public function getRol() { return $this->rol; }
    public function getPassword() { return $this->contrasenya; }

    // Setters
    public function setUsername($nom) { $this->nom = $nom; }
    public function setEmail($email) { $this->email = $email; }
    public function setRol($rol) { $this->rol = $rol; }
    public function setPassword($pass) { $this->contrasenya = $pass; }

    // Métodos auxiliares
    public function isAdmin() {
        return $this->rol === 'admin';
    }
}

?>