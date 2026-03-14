<?php

enum Role: int {
    case USER = 1;
    case ADMIN = 10;
}

class User {

    private int $id;
    private string $uuid;
    private string $username;
    private string $displayName;
    private string $email;
    private string $password;
    private ?string $avatarUrl;
    private Role $role;
    private ?string $rememberToken;
    private ?string $rememberTokenExpires;
    private ?string $passwordResetToken;
    private ?string $passwordResetExpires;
    private string $createdAt;

    public function __construct(
        int $id,
        string $uuid,
        string $username,
        string $displayName,
        string $email,
        string $password,
        ?string $avatarUrl = null,
        Role $role = Role::USER,
        ?string $rememberToken = null,
        ?string $rememberTokenExpires = null,
        ?string $passwordResetToken = null,
        ?string $passwordResetExpires = null,
        string $createdAt = ''
    ) {
        $this->id = $id;
        $this->uuid = $uuid;
        $this->username = $username;
        $this->displayName = $displayName;
        $this->email = $email;
        $this->password = $password;
        $this->avatarUrl = $avatarUrl;
        $this->role = $role;
        $this->rememberToken = $rememberToken;
        $this->rememberTokenExpires = $rememberTokenExpires;
        $this->passwordResetToken = $passwordResetToken;
        $this->passwordResetExpires = $passwordResetExpires;
        $this->createdAt = $createdAt;
    }


    /* Getters  */
    public function getId(): int { return $this->id; }
    public function getUUID(): string { return $this->uuid; }
    public function getUsername(): string { return $this->username; }
    public function getDisplayName(): string { return $this->displayName; }
    public function getEmail(): string { return $this->email; }
    public function getPassword(): string { return $this->password; }
    public function getRole(): Role { return $this->role; }
    public function getRoleValue(): int { return $this->role->value; }
    public function getAvatarUrl(): ?string { return $this->avatarUrl; }
    public function getCreatedAt(): string { return $this->createdAt; }

    public function getRememberToken(): ?string { return $this->rememberToken; }
    public function getRememberTokenExpires(): ?string { return $this->rememberTokenExpires; }

    public function getPasswordResetToken(): ?string { return $this->passwordResetToken; }
    public function getPasswordResetExpires(): ?string { return $this->passwordResetExpires; }

    /* Setters */
    public function setUsername(string $username): void { $this->username = $username; }
    public function setDisplayname(string $displayName): void { $this->displayName = $displayName; }
    public function setEmail(string $email): void { $this->email = $email; }
    public function setPassword(string $password): void { $this->password = $password; }
    public function setRole(Role $role): void { $this->role = $role; }
    public function setAvatarUrl(?string $avatarUrl): void { $this->avatarUrl = $avatarUrl; }

    /* Métodos auxiliares */
    public function isAdmin(): bool {
        return $this->role === Role::ADMIN;
    }
}