CREATE DATABASE IF NOT EXISTS `AlfaReserva`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `AlfaReserva`;

CREATE TABLE IF NOT EXISTS `user` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `cuenta` VARCHAR(100) NOT NULL,
  `name` VARCHAR(255) DEFAULT NULL,
  `superadmin` BIT(1) DEFAULT b'0',
  `active` BIT(1) DEFAULT b'1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_user` (`user`),
  UNIQUE KEY `uq_user_email` (`email`),
  UNIQUE KEY `uq_user_cuenta` (`cuenta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
