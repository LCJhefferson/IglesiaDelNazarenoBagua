-- =====================================================
-- FIX: Crear tablas faltantes para el módulo Discipulado
-- Ejecutar en phpMyAdmin sobre la base: iglesiadelnazareno
-- =====================================================

-- Tabla de estados para los GRUPOS de discipulado
CREATE TABLE IF NOT EXISTS `estados_grupo_discipulado` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `estados_grupo_discipulado` (`id`, `nombre`) VALUES
(1, 'Activo'),
(2, 'Inactivo'),
(3, 'En formación');

-- Tabla de estados para los DISCÍPULOS (integrantes)
CREATE TABLE IF NOT EXISTS `estados_discipulo` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `estados_discipulo` (`id`, `nombre`) VALUES
(1, 'Activo'),
(2, 'Inactivo'),
(3, 'Graduado');
