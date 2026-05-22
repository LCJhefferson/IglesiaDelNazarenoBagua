-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 22-05-2026 a las 19:13:07
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `iglesiadelnazareno`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bitacora`
--

CREATE TABLE `bitacora` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `accion` text DEFAULT NULL,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cargos`
--

CREATE TABLE `cargos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cargos`
--

INSERT INTO `cargos` (`id`, `nombre`) VALUES
(3, 'Discipulador'),
(5, 'Evangelista'),
(2, 'Lider'),
(4, 'Maestro'),
(1, 'Pastor');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `condiciones_miembro`
--

CREATE TABLE `condiciones_miembro` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `condiciones_miembro`
--

INSERT INTO `condiciones_miembro` (`id`, `nombre`) VALUES
(1, 'saludable'),
(2, 'enfermo'),
(4, 'reposo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion_sistema`
--

CREATE TABLE `configuracion_sistema` (
  `clave` varchar(50) NOT NULL,
  `valor` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `configuracion_sistema`
--

INSERT INTO `configuracion_sistema` (`clave`, `valor`) VALUES
('meses_limite_visita', '24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `datos_usuario`
--

CREATE TABLE `datos_usuario` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `nombres` varchar(100) DEFAULT NULL,
  `apellidos` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `correo` varchar(150) DEFAULT NULL,
  `dni` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `discipulado_grupos`
--

CREATE TABLE `discipulado_grupos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `nivel` varchar(50) DEFAULT NULL,
  `discipulador_id` int(11) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `estado_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `discipulado_grupos`
--

INSERT INTO `discipulado_grupos` (`id`, `nombre`, `nivel`, `discipulador_id`, `fecha_creacion`, `estado_id`) VALUES
(21, 'celula 2', 'I', 83, '2026-05-19 11:56:18', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `discipulado_integrantes`
--

CREATE TABLE `discipulado_integrantes` (
  `id` int(11) NOT NULL,
  `grupo_id` int(11) DEFAULT NULL,
  `miembro_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `discipulado_integrantes`
--

INSERT INTO `discipulado_integrantes` (`id`, `grupo_id`, `miembro_id`) VALUES
(65, 21, 78),
(66, 21, 79),
(67, 21, 81);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estados_discipulado`
--

CREATE TABLE `estados_discipulado` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `estados_discipulado`
--

INSERT INTO `estados_discipulado` (`id`, `nombre`) VALUES
(1, 'Activo'),
(2, 'Inactivo'),
(3, 'Concluido'),
(4, 'Pausado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estados_transmision`
--

CREATE TABLE `estados_transmision` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estados_transmision`
--

INSERT INTO `estados_transmision` (`id`, `nombre`) VALUES
(1, 'en vivo'),
(2, 'finalizado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estados_visita`
--

CREATE TABLE `estados_visita` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estados_visita`
--

INSERT INTO `estados_visita` (`id`, `nombre`) VALUES
(1, 'pendiente'),
(2, 'realizada\n'),
(3, 'cancelada');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `miembros`
--

CREATE TABLE `miembros` (
  `id` int(11) NOT NULL,
  `nombres` varchar(100) DEFAULT NULL,
  `apellidos` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `cargo_id` int(11) DEFAULT NULL,
  `condicion_id` int(11) DEFAULT NULL,
  `latitud` decimal(10,8) DEFAULT NULL,
  `longitud` decimal(11,8) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 1,
  `tipo_miembro_id` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `miembros`
--

INSERT INTO `miembros` (`id`, `nombres`, `apellidos`, `telefono`, `direccion`, `fecha_nacimiento`, `cargo_id`, `condicion_id`, `latitud`, `longitud`, `estado`, `tipo_miembro_id`) VALUES
(77, 'ELI ANDERSON', 'LEON CHUPILLON', '', 'JR. CERRO DE PASCO 300', '2026-05-16', NULL, 1, -5.63417100, -78.53661100, 0, 1),
(78, 'Luis ', 'Arteaga Moro', '987654321', 'jr.Moquegua 338', '2026-05-16', NULL, 1, -5.63275600, -78.53667000, 1, 1),
(79, 'user 1', 'LEON CHUPILLON', '987654321', 'JR. Cajamarca 570', '2026-05-19', NULL, 2, -5.63966300, -78.52880000, 1, 1),
(80, 'usuario de el muyo', 'LEON CHUPILLON', '987654321', 'JR. CERRO DE PASCO 300', '2026-05-19', NULL, 2, -5.42173400, -78.45426000, 0, 2),
(81, 'user 4', 'LEON CHUPILLON', '987654321', 'JR. Aamzonas43', '2026-05-19', NULL, 1, -5.63548500, -78.52945100, 1, 1),
(82, 'ELI ANDERSON', 'LEON CHUPILLON', '987654321', 'JR. CERRO DE PASCO 300', '2026-05-19', NULL, 1, -5.63832900, -78.52888100, 1, 1),
(83, 'eli', 'LEON CHUPILLON', '', 'JR. CERRO DE PASCO 300', '2026-05-19', NULL, 2, -5.64125000, -78.53097800, 1, 1),
(84, 'Jhefferson', 'Leon', '974371160', 'jr.cerro de pasco', '2026-05-21', NULL, 2, -5.64106700, -78.52861200, 1, 1),
(85, 'Matias', 'roll Montengro', '987654321', 'amazonas 100', '2026-05-22', NULL, 1, -5.63714200, -78.53101700, 1, 2),
(86, 'Postman', 'soa torres', '987654321', 'jr.comercio', '2026-05-22', NULL, 4, -5.64000400, -78.53189000, 1, 1),
(87, 'robert', 'wits ed', '987654321', 'JR. CERRO DE PASCO 300', '2026-05-22', NULL, 4, -5.63750000, -78.53335800, 1, 1),
(88, 'ELI ANDERSON', 'LEON CHUPILLON', '987654321', 'JR. CERRO DE PASCO 300', '2026-05-22', NULL, 1, 0.00000000, 0.00000000, 1, 1),
(89, 'ELI ANDERSON', 'LEON CHUPILLON', '987654321', 'JR. CERRO DE PASCO 300', '2026-05-22', NULL, 1, 0.00000000, 0.00000000, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `miembro_cargos`
--

CREATE TABLE `miembro_cargos` (
  `id` int(11) NOT NULL,
  `miembro_id` int(11) NOT NULL,
  `cargo_id` int(11) NOT NULL,
  `fecha_asignacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `miembro_cargos`
--

INSERT INTO `miembro_cargos` (`id`, `miembro_id`, `cargo_id`, `fecha_asignacion`) VALUES
(17, 78, 4, '2026-05-15 18:49:55'),
(18, 79, 4, '2026-05-19 02:11:16'),
(19, 80, 5, '2026-05-19 02:12:50'),
(20, 80, 4, '2026-05-19 02:12:50'),
(24, 82, 3, '2026-05-19 11:27:12'),
(27, 84, 5, '2026-05-21 13:50:33'),
(28, 83, 3, '2026-05-21 14:41:34'),
(29, 83, 2, '2026-05-21 14:41:34'),
(30, 85, 5, '2026-05-22 08:53:09'),
(31, 86, 5, '2026-05-22 09:00:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `noticias`
--

CREATE TABLE `noticias` (
  `id` int(11) NOT NULL,
  `titulo` varchar(150) DEFAULT NULL,
  `resumen` text DEFAULT NULL,
  `imagen_portada` varchar(255) DEFAULT NULL,
  `contenido` text DEFAULT NULL,
  `video_link` varchar(255) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `creado_por` int(11) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `noticias`
--

INSERT INTO `noticias` (`id`, `titulo`, `resumen`, `imagen_portada`, `contenido`, `video_link`, `fecha_creacion`, `creado_por`, `estado`) VALUES
(1, 'visiatas', 'tresumen ', 'admin/imagenes/noticias/1777306615_portada_694da6135fff8a4979fc7d2264d149de.jpg', 'contenido 2', '##', '2026-04-27 18:16:00', NULL, 0),
(2, '1', '', '', '', '', '2026-05-05 05:44:00', NULL, 0),
(3, 'hola', 'hola', '', 'g', '', '2026-05-09 00:43:00', NULL, 1),
(4, 'visiatas', 'd', '', 'ee', '', '2026-05-14 18:55:00', NULL, 1),
(5, 'prueva ', 'pro', 'admin/imagenes/noticias/1778864312_portada_9b1712892bb6b4f51229e153d5537291.jpg', 'contenido', '', '2026-05-15 18:57:00', NULL, 1),
(6, '/.///////////', 're////////////////', 'admin/imagenes/noticias/1778880939_portada_d69cc93bf06c077685a043f2cfe6d9ba.jpg', 'coin////////////tenido ', '.........///////////', '2026-05-15 23:34:00', NULL, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `noticia_imagenes`
--

CREATE TABLE `noticia_imagenes` (
  `id` int(11) NOT NULL,
  `noticia_id` int(11) DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `noticia_imagenes`
--

INSERT INTO `noticia_imagenes` (`id`, `noticia_id`, `imagen`) VALUES
(2, 1, 'admin/imagenes/noticias/1777306615_galeria_píonono.jpeg'),
(3, 1, 'admin/imagenes/noticias/1777306615_galeria_Productos mas vendidos.png'),
(4, 1, 'admin/imagenes/noticias/1777306615_galeria_Productos+Vendidos.png'),
(5, 1, 'admin/imagenes/noticias/1777306615_galeria_representacion-3d-de-fondo-de-textura-hexagonal.jpg'),
(6, 3, 'admin/imagenes/noticias/1778280270_galeria_flan-de-huevo-casero.jpg'),
(7, 5, 'admin/imagenes/noticias/1778864312_galeria_75956bb29353dd0c3582fd9817780f08.jpg'),
(8, 5, 'admin/imagenes/noticias/1778864312_galeria_0997097b90812b193287779a7465b587.jpg'),
(9, 5, 'admin/imagenes/noticias/1778864312_galeria_198212645-una-oveja-negra-rodeada-de-una-metáfora-normal-de-oveja-blanca-para-ser-excepcional-o-única.webp'),
(10, 5, 'admin/imagenes/noticias/1778864312_galeria_7369020299dee04fe88ef58360c84e97.jpg'),
(11, 6, 'admin/imagenes/noticias/1778880939_galeria_images (1).jpg'),
(12, 6, 'admin/imagenes/noticias/1778880939_galeria_images (2).jpg'),
(13, 6, 'admin/imagenes/noticias/1778880939_galeria_images (3).jpg'),
(14, 6, 'admin/imagenes/noticias/1778880939_galeria_images (4).jpg'),
(15, 6, 'admin/imagenes/noticias/1778880939_galeria_images.jpg'),
(16, 6, 'admin/imagenes/noticias/1778880939_galeria_imagesfghjkl.jpg'),
(17, 6, 'admin/imagenes/noticias/1778880988_galeria_píonono.jpeg'),
(18, 4, 'public/admin/imagenes/noticias/1779461889_galeria_images (1).jpg'),
(20, 4, 'public/admin/imagenes/noticias/1779461889_galeria_images (3).jpg'),
(21, 4, 'public/admin/imagenes/noticias/1779461889_galeria_images (4).jpg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permisos`
--

CREATE TABLE `permisos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recursos`
--

CREATE TABLE `recursos` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `categoria` varchar(50) DEFAULT NULL,
  `tipo` enum('pdf','img','vid','doc') DEFAULT NULL,
  `ruta_archivo` varchar(255) DEFAULT NULL,
  `enlace_youtube` varchar(255) DEFAULT NULL,
  `descargas` int(11) DEFAULT 0,
  `creado_por` int(11) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `ruta_thumb` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `recursos`
--

INSERT INTO `recursos` (`id`, `titulo`, `descripcion`, `categoria`, `tipo`, `ruta_archivo`, `enlace_youtube`, `descargas`, `creado_por`, `fecha_creacion`, `ruta_thumb`) VALUES
(1, 'Titulo de Prueba', 'Discripcion de Prueba', 'documentos', 'doc', 'admin/imagenes/recursos/1777263218_INTEGRANTES.docx', '', 1, NULL, '2026-04-26 22:04:35', ''),
(3, 'Titulo de Prueba222', 'pruebaaa222', 'documentos', '', '', '', 0, NULL, '2026-04-27 11:19:42', ''),
(4, '123', 'q', '', '', '', '', 0, NULL, '2026-04-27 11:20:16', ''),
(5, 'hola', 'hola.php', 'imagenes', '', '', '', 0, NULL, '2026-05-08 17:41:14', ''),
(7, 'visita', '', '', 'img', 'admin/imagenes/recursos/1778783332_9b1712892bb6b4f51229e153d5537291.jpg', '', 0, NULL, '2026-05-14 13:28:52', 'admin/imagenes/recursos/1778783332_9b1712892bb6b4f51229e153d5537291.jpg'),
(9, 'visiatas', 'decripcion', '', 'img', 'admin/imagenes/recursos/1778783623_9b1712892bb6b4f51229e153d5537291.jpg', '', 0, NULL, '2026-05-14 13:33:43', 'admin/imagenes/recursos/1778783623_9b1712892bb6b4f51229e153d5537291.jpg'),
(10, '123', '', '', 'img', 'admin/imagenes/recursos/1778783801_9b1712892bb6b4f51229e153d5537291.jpg', '', 0, NULL, '2026-05-14 13:36:41', 'admin/imagenes/recursos/1778783801_9b1712892bb6b4f51229e153d5537291.jpg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recursos_papelera`
--

CREATE TABLE `recursos_papelera` (
  `id` int(11) NOT NULL,
  `recurso_id` int(11) DEFAULT NULL,
  `titulo` varchar(255) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `categoria` varchar(50) DEFAULT NULL,
  `tipo` enum('pdf','img','vid','doc','yt') DEFAULT NULL,
  `ruta_archivo` varchar(255) DEFAULT NULL,
  `enlace_youtube` varchar(255) DEFAULT NULL,
  `eliminado_por` int(11) DEFAULT NULL,
  `fecha_eliminacion` datetime DEFAULT current_timestamp(),
  `ruta_thumb` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `recursos_papelera`
--

INSERT INTO `recursos_papelera` (`id`, `recurso_id`, `titulo`, `descripcion`, `categoria`, `tipo`, `ruta_archivo`, `enlace_youtube`, `eliminado_por`, `fecha_eliminacion`, `ruta_thumb`) VALUES
(3, 8, '2', '', '', 'img', 'admin/imagenes/recursos/1778783419_ea1aea5073b4fa54915519e4fec05db5.jpg', '', NULL, '2026-05-19 18:57:26', 'admin/imagenes/recursos/1778783419_ea1aea5073b4fa54915519e4fec05db5.jpg'),
(4, 11, 'imagen', 'imagen de contenido', 'imagenes', 'img', 'admin/imagenes/recursos/1779235029_d36cf783a6ce28eff7580c5f8a89e621.jpg', '', NULL, '2026-05-21 18:55:21', 'admin/imagenes/recursos/1779235029_d36cf783a6ce28eff7580c5f8a89e621.jpg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre`) VALUES
(10, ''),
(1, 'Admin'),
(9, 'Discipulador'),
(2, 'Pastor');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol_permisos`
--

CREATE TABLE `rol_permisos` (
  `id` int(11) NOT NULL,
  `rol_id` int(11) DEFAULT NULL,
  `permiso_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_miembro`
--

CREATE TABLE `tipos_miembro` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `tipos_miembro`
--

INSERT INTO `tipos_miembro` (`id`, `nombre`) VALUES
(1, 'Local'),
(2, 'Externo/Invitado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `transmisiones`
--

CREATE TABLE `transmisiones` (
  `id` int(11) NOT NULL,
  `titulo` varchar(150) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `link_video` varchar(255) DEFAULT NULL,
  `fecha` datetime DEFAULT current_timestamp(),
  `creado_por` int(11) DEFAULT NULL,
  `estado_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `transmisiones`
--

INSERT INTO `transmisiones` (`id`, `titulo`, `descripcion`, `link_video`, `fecha`, `creado_por`, `estado_id`) VALUES
(22, '1', '1', 'https://www.youtube.com/embed/IhZ3d_UJfgM', '2026-05-15 16:15:31', 1, 2),
(23, '2', '2', 'https://www.youtube.com/embed/IhZ3d_UJfgM', '2026-05-15 16:15:45', 1, 2),
(24, '3', NULL, '', '2026-05-15 16:20:10', 1, 2),
(25, '4', NULL, '', '2026-05-15 16:22:15', 1, 2),
(26, '5', '5', 'https://www.youtube.com/embed/IhZ3d_UJfgM', '2026-05-15 16:28:37', 1, 2),
(27, '6', '6', 'https://www.youtube.com/embed/IhZ3d_UJfgM', '2026-05-15 16:41:29', 1, 2),
(28, '7', '', '', '2026-05-15 16:41:44', 1, 2),
(29, '8modif', '8', 'https://www.youtube.com/embed/IhZ3d_UJfgM', '2026-05-15 16:44:56', 1, 2),
(30, '9', '', '', '2026-05-15 16:56:59', 1, 2),
(31, '10', '', '', '2026-05-15 17:22:18', 1, 2),
(32, 'Transmision', 'Descripcion de transmision', 'https://www.youtube.com/embed/IhZ3d_UJfgM', '2026-05-21 15:22:15', 1, 2),
(33, '11', '', '', '2026-05-21 15:35:13', 1, 2),
(34, 'trasmision 1', '', '', '2026-05-21 15:45:06', 1, 2),
(35, 'Transmisión 2 ', 'Descripción de la transmisión', 'https://www.youtube.com/embed/IhZ3d_UJfgM', '2026-05-21 15:51:03', 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `id_rol` int(11) DEFAULT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `username`, `password`, `id_rol`, `estado`) VALUES
(1, 'yeiner_2007', '$2y$10$2bvfo1GqlTFMCAf/iO7WmeAqOqdAqfNv5vmwg6t1NY1tibN3SHPy2', 1, 'activo'),
(2, '6096855541', '$2y$10$vNKF20KObAl0C1sTU2VtDeS3POssK46cXOeJvNohiuIMjhwEq6MQi', 1, 'activo'),
(3, 'rosa_Melendes', '$2y$10$UQLLR1opbESvkKeAaESFpOrFE9VURNcRHdrNy4yGcd1ftN9I4lUO2', 1, 'activo'),
(4, 'rosa', '$2y$10$5DfroQ5Xvz41ihGPuUZoTO30zwR7Mp9T80P3wwgGCfgYjJDKrCmly', 1, 'activo'),
(8, 'jh', '$2y$10$JnAiq4h4mvWrhUICHAoXw.7Gs6dBORLCnaWtzMMGqlMpMZm4uIXDC', 1, 'activo'),
(10, 'user1', '$2y$10$sH0b9bgRAcP1.MaLoWKIVeNMNspXKskKpH4S3FUsAPNdG2OScQhrS', 2, 'activo'),
(11, 'luis', '$2y$10$feKnyAT6B3coNgNZHBltRuAMgXn8PsOQWQCeDRm6yXXlzRUdOWdYa', 1, 'activo'),
(12, 'o', '$2y$10$lSaWqf00qAdX8pDnAMCGj.oVnOMeyqPjMDj003/6RoEqO98KC7UXW', 1, 'activo'),
(13, 'q', '$2y$10$jXHlvlGSu3wShxFxsmzPhO5L41YJqGmA6KiyMyxphQ5kAwRZCe.2O', 1, 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `visitas`
--

CREATE TABLE `visitas` (
  `id` int(11) NOT NULL,
  `miembro_id` int(11) DEFAULT NULL,
  `fecha_visita` date DEFAULT NULL,
  `motivo` text DEFAULT NULL,
  `registrado_por` int(11) DEFAULT NULL,
  `estado_id` int(11) DEFAULT NULL,
  `estado` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `visitas`
--

INSERT INTO `visitas` (`id`, `miembro_id`, `fecha_visita`, `motivo`, `registrado_por`, `estado_id`, `estado`) VALUES
(33, 77, '2026-03-13', 'Visita Regular', 1, 1, 0),
(34, 78, '2026-03-05', 'Visita Regular', 1, 1, 1),
(35, 79, '2026-05-19', 'Visita Regular', 1, 1, 0),
(36, 80, '2025-05-08', 'Visita Regular', 1, 1, 0),
(37, 80, '2026-04-16', 'Visita Regular', 1, 1, 0),
(38, 81, '2026-04-18', 'Visita Regular', 1, 1, 1),
(39, 81, '2026-04-18', 'Visita Regular', 1, 1, 0),
(40, 77, '2026-04-11', 'Visita Regular', 1, 1, 0),
(41, 81, '2026-05-01', 'Visita Regular', 1, 1, 1),
(42, 77, '2026-05-12', 'Visita Regular', 1, 1, 0),
(43, 78, '2026-05-12', 'Visita Regular', 1, 1, 0),
(44, 81, '2026-05-16', 'Visita Regular', 1, 1, 0),
(45, 80, '2026-05-16', 'Visita Regular', 1, 1, 0),
(46, 77, '2026-05-05', 'Visita Regular', 1, 1, 0),
(47, 77, '2026-05-03', 'Visita Regular', 1, 1, 0),
(48, 78, '2026-05-05', 'Visita Regular', 1, 1, 0),
(49, 80, '2026-05-05', 'Visita Regular', 1, 1, 0),
(50, 77, '2026-05-05', 'Visita Regular', 1, 1, 0),
(51, 80, '2026-04-01', 'Visita Regular', 1, 1, 0),
(52, 80, '2026-04-28', 'Visita Regular', 1, 1, 0),
(53, 80, '2026-05-19', 'Visita Regular', 1, 1, 0),
(54, 79, '2026-05-01', 'Visita Regular', 1, 1, 0),
(55, 78, '2026-04-22', 'Visita Regular', 1, 1, 0),
(56, 83, '2025-12-21', 'Visita Regular', 1, 1, 0),
(57, 84, '2026-05-21', 'Visita Regular', 1, 1, 0),
(58, 83, '2025-12-21', 'Visita Regular', 1, 1, 0),
(59, 78, '2026-05-21', 'Visita Regular', 1, 1, 1),
(60, 83, '2026-05-21', 'Visita Regular', 1, 1, 0),
(61, 82, '2026-03-21', 'Visita Regular', 1, 1, 1),
(62, 79, '2026-02-13', 'Visita Regular', 1, 1, 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `bitacora`
--
ALTER TABLE `bitacora`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `cargos`
--
ALTER TABLE `cargos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `condiciones_miembro`
--
ALTER TABLE `condiciones_miembro`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `configuracion_sistema`
--
ALTER TABLE `configuracion_sistema`
  ADD PRIMARY KEY (`clave`);

--
-- Indices de la tabla `datos_usuario`
--
ALTER TABLE `datos_usuario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `discipulado_grupos`
--
ALTER TABLE `discipulado_grupos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `discipulador_id` (`discipulador_id`),
  ADD KEY `fk_grupo_estado_rel` (`estado_id`);

--
-- Indices de la tabla `discipulado_integrantes`
--
ALTER TABLE `discipulado_integrantes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `grupo_id` (`grupo_id`),
  ADD KEY `miembro_id` (`miembro_id`);

--
-- Indices de la tabla `estados_discipulado`
--
ALTER TABLE `estados_discipulado`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `estados_transmision`
--
ALTER TABLE `estados_transmision`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `estados_visita`
--
ALTER TABLE `estados_visita`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `miembros`
--
ALTER TABLE `miembros`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cargo_id` (`cargo_id`),
  ADD KEY `condicion_id` (`condicion_id`),
  ADD KEY `fk_miembros_tipo` (`tipo_miembro_id`);

--
-- Indices de la tabla `miembro_cargos`
--
ALTER TABLE `miembro_cargos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `miembro_id` (`miembro_id`),
  ADD KEY `cargo_id` (`cargo_id`);

--
-- Indices de la tabla `noticias`
--
ALTER TABLE `noticias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `creado_por` (`creado_por`);

--
-- Indices de la tabla `noticia_imagenes`
--
ALTER TABLE `noticia_imagenes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `noticia_id` (`noticia_id`);

--
-- Indices de la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `recursos`
--
ALTER TABLE `recursos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `creado_por` (`creado_por`);

--
-- Indices de la tabla `recursos_papelera`
--
ALTER TABLE `recursos_papelera`
  ADD PRIMARY KEY (`id`),
  ADD KEY `eliminado_por` (`eliminado_por`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `rol_permisos`
--
ALTER TABLE `rol_permisos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rol_id` (`rol_id`),
  ADD KEY `permiso_id` (`permiso_id`);

--
-- Indices de la tabla `tipos_miembro`
--
ALTER TABLE `tipos_miembro`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `transmisiones`
--
ALTER TABLE `transmisiones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `creado_por` (`creado_por`),
  ADD KEY `estado_id` (`estado_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `id_rol` (`id_rol`);

--
-- Indices de la tabla `visitas`
--
ALTER TABLE `visitas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `miembro_id` (`miembro_id`),
  ADD KEY `registrado_por` (`registrado_por`),
  ADD KEY `estado_id` (`estado_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `bitacora`
--
ALTER TABLE `bitacora`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cargos`
--
ALTER TABLE `cargos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `condiciones_miembro`
--
ALTER TABLE `condiciones_miembro`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `datos_usuario`
--
ALTER TABLE `datos_usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `discipulado_grupos`
--
ALTER TABLE `discipulado_grupos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `discipulado_integrantes`
--
ALTER TABLE `discipulado_integrantes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT de la tabla `estados_discipulado`
--
ALTER TABLE `estados_discipulado`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `estados_transmision`
--
ALTER TABLE `estados_transmision`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `estados_visita`
--
ALTER TABLE `estados_visita`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `miembros`
--
ALTER TABLE `miembros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT de la tabla `miembro_cargos`
--
ALTER TABLE `miembro_cargos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de la tabla `noticias`
--
ALTER TABLE `noticias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `noticia_imagenes`
--
ALTER TABLE `noticia_imagenes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `permisos`
--
ALTER TABLE `permisos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `recursos`
--
ALTER TABLE `recursos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `recursos_papelera`
--
ALTER TABLE `recursos_papelera`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `rol_permisos`
--
ALTER TABLE `rol_permisos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tipos_miembro`
--
ALTER TABLE `tipos_miembro`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `transmisiones`
--
ALTER TABLE `transmisiones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `visitas`
--
ALTER TABLE `visitas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `bitacora`
--
ALTER TABLE `bitacora`
  ADD CONSTRAINT `bitacora_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `datos_usuario`
--
ALTER TABLE `datos_usuario`
  ADD CONSTRAINT `datos_usuario_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `discipulado_grupos`
--
ALTER TABLE `discipulado_grupos`
  ADD CONSTRAINT `fk_grupo_estado` FOREIGN KEY (`estado_id`) REFERENCES `estados_discipulado` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_grupo_estado_rel` FOREIGN KEY (`estado_id`) REFERENCES `estados_discipulado` (`id`),
  ADD CONSTRAINT `fk_grupos_discipulador` FOREIGN KEY (`discipulador_id`) REFERENCES `miembros` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `discipulado_integrantes`
--
ALTER TABLE `discipulado_integrantes`
  ADD CONSTRAINT `discipulado_integrantes_ibfk_1` FOREIGN KEY (`grupo_id`) REFERENCES `discipulado_grupos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `discipulado_integrantes_ibfk_2` FOREIGN KEY (`miembro_id`) REFERENCES `miembros` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `miembros`
--
ALTER TABLE `miembros`
  ADD CONSTRAINT `fk_miembros_tipo` FOREIGN KEY (`tipo_miembro_id`) REFERENCES `tipos_miembro` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `miembros_ibfk_1` FOREIGN KEY (`cargo_id`) REFERENCES `cargos` (`id`),
  ADD CONSTRAINT `miembros_ibfk_2` FOREIGN KEY (`condicion_id`) REFERENCES `condiciones_miembro` (`id`);

--
-- Filtros para la tabla `miembro_cargos`
--
ALTER TABLE `miembro_cargos`
  ADD CONSTRAINT `miembro_cargos_ibfk_1` FOREIGN KEY (`miembro_id`) REFERENCES `miembros` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `miembro_cargos_ibfk_2` FOREIGN KEY (`cargo_id`) REFERENCES `cargos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `noticias`
--
ALTER TABLE `noticias`
  ADD CONSTRAINT `noticias_ibfk_1` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `noticia_imagenes`
--
ALTER TABLE `noticia_imagenes`
  ADD CONSTRAINT `noticia_imagenes_ibfk_1` FOREIGN KEY (`noticia_id`) REFERENCES `noticias` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `recursos`
--
ALTER TABLE `recursos`
  ADD CONSTRAINT `recursos_ibfk_1` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `recursos_papelera`
--
ALTER TABLE `recursos_papelera`
  ADD CONSTRAINT `recursos_papelera_ibfk_1` FOREIGN KEY (`eliminado_por`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `rol_permisos`
--
ALTER TABLE `rol_permisos`
  ADD CONSTRAINT `rol_permisos_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rol_permisos_ibfk_2` FOREIGN KEY (`permiso_id`) REFERENCES `permisos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `transmisiones`
--
ALTER TABLE `transmisiones`
  ADD CONSTRAINT `transmisiones_ibfk_1` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `transmisiones_ibfk_2` FOREIGN KEY (`estado_id`) REFERENCES `estados_transmision` (`id`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id`);

--
-- Filtros para la tabla `visitas`
--
ALTER TABLE `visitas`
  ADD CONSTRAINT `visitas_ibfk_1` FOREIGN KEY (`miembro_id`) REFERENCES `miembros` (`id`),
  ADD CONSTRAINT `visitas_ibfk_2` FOREIGN KEY (`registrado_por`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `visitas_ibfk_3` FOREIGN KEY (`estado_id`) REFERENCES `estados_visita` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
