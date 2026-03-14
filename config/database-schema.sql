SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ········································
-- · Base de dades: `bbdd_backend_apardo`  ·
-- ········································
DROP DATABASE IF EXISTS `bbdd_apardo`;
CREATE DATABASE IF NOT EXISTS `bbdd_apardo`
DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `bbdd_apardo`;

-- ·································
-- ·        Tabla | Users          ·
-- ·································
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `uuid` CHAR(36) NOT NULL UNIQUE,
  `avatar_url` VARCHAR(255),
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `displayname` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` TINYINT UNSIGNED DEFAULT 1,
  `remember_token` CHAR(64) DEFAULT NULL,
  `remember_token_expires` DATETIME DEFAULT NULL,
  `password_reset_token` CHAR(64) DEFAULT NULL,
  `password_reset_expires` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Afegint registres [ Usuaris ] con hash de password
INSERT INTO `users` (`uuid`, `username`, `displayname`, `email`, `password`, `role`) VALUES
(UUID(), 'admin', 'Administrador', 'admin@test.com', '$2y$10$s7U14HeeIQrnZ78mp6chf.M9O/Sp4NA5FA6cEKzQ/gJTmDRv8EFY.', 10),
(UUID(), 'anghelopj', 'AngheloPJ', 'anghelopj@gmail.com', '$2y$10$mwbZ9lnbxFejs9bYUvEA1Ot0t/6ak8ah3UPGV3fOWDrwV2L24YHmm', 1);

-- ·································
-- ·       Tabla | Articles        ·
-- ·································
DROP TABLE IF EXISTS `articles`;
CREATE TABLE `articles` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `author_id` INT(11) DEFAULT NULL,
  `slug` VARCHAR(180) NOT NULL UNIQUE,
  `title` VARCHAR(150) NOT NULL,
  `content` TEXT,
  `image_url` VARCHAR(255),
  `published_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`author_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `articles` (`author_id`, `slug`, `title`, `content`, `image_url`, `published_at`) VALUES
(1, 'primer-article', 'Primer article', 'Este es el primer articulo de prueba.', 'public/assets/img/articles/article1.webp', CURRENT_TIMESTAMP),
(2, 'segon-article', 'Segon article', 'Este es el segundo articulo de prueba.', 'public/assets/img/articles/article2.webp', CURRENT_TIMESTAMP);

-- ·································
-- ·       Tabla | Accounts        ·
-- ·································
DROP TABLE IF EXISTS `accounts`;
CREATE TABLE `accounts` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `provider` VARCHAR(50) NOT NULL,
  `provider_id` VARCHAR(255) NOT NULL,
  `provider_email` VARCHAR(100),
  `confirmation_token` CHAR(64) DEFAULT NULL,
  `confirmation_expires` DATETIME DEFAULT NULL,
  `confirmed_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_provider` (`user_id`, `provider`),
  UNIQUE KEY `uq_provider_id` (`provider`, `provider_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

