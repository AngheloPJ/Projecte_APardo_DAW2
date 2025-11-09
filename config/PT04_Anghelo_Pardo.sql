SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- ········································
-- · Base de dades: `PT04_Anghelo_Pardo`  ·
-- ········································
DROP DATABASE IF EXISTS `PT04_Anghelo_Pardo`;
CREATE DATABASE IF NOT EXISTS `PT04_Anghelo_Pardo`
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `PT04_Anghelo_Pardo`;

-- ·································
-- · Taula | Usuaris               ·
-- ·································
DROP TABLE IF EXISTS `usuaris`;
CREATE TABLE `usuaris` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nom` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `contrasenya` VARCHAR(255) NOT NULL,
  `data_registre` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Afegint registres [ Usuaris ]
INSERT INTO `usuaris` (`nom`, `email`, `contrasenya`) VALUES
('admin', 'admin@example.com', '1234'),
('anghelopj', 'anghelopj@gmail.com', 'abcd');

-- ·································
-- · Taula | Articles              ·
-- ·································
DROP TABLE IF EXISTS `articles`;
CREATE TABLE `articles` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `titol` VARCHAR(150) NOT NULL,
  `cos` TEXT NOT NULL,
  `imatge_url` VARCHAR(255) DEFAULT NULL,
  `autor_id` INT(11) DEFAULT NULL,
  `data_creacio` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`autor_id`) REFERENCES `usuaris`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Afegint registres [ Articles ]
INSERT INTO `articles` (`titol`, `cos`, `imatge_url`, `autor_id`) VALUES
('Primer article', 'Aquest és el primer article de prova.', 'assets/img/articles/article1.jpg', 1),
('Segon article', 'Aquest és el segon article de prova.', 'assets/img/articles/article2.jpg', 2);

COMMIT;