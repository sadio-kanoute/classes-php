-- Création de la base de données "classes"
CREATE DATABASE IF NOT EXISTS `classes` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Sélectionner la base de données
USE `classes`;

-- Création de la table "utilisateurs"
CREATE TABLE `utilisateurs` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `login` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `firstname` VARCHAR(50) NOT NULL,
    `lastname` VARCHAR(50) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
