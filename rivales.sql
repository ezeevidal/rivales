-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 14-05-2025 a las 18:07:18
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `rivales`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `canchas`
--

CREATE TABLE `canchas` (
  `id` int(10) UNSIGNED NOT NULL,
  `club_id` int(10) UNSIGNED NOT NULL,
  `numero` tinyint(3) UNSIGNED NOT NULL COMMENT 'Número de cancha (1–20)',
  `tipo_suelo` enum('sintetico','cemento') NOT NULL,
  `tipo_pared` enum('cemento','blindex') NOT NULL,
  `fraccion_horaria` time NOT NULL COMMENT 'Duración del turno: 01:00, 01:30, etc.',
  `dias_disponibles` text NOT NULL COMMENT 'JSON con días y horarios',
  `precio` int(10) UNSIGNED NOT NULL COMMENT 'Precio en ARS sin centavos',
  `imagen` varchar(255) DEFAULT NULL COMMENT 'Nombre de archivo de imagen',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `canchas`
--

INSERT INTO `canchas` (`id`, `club_id`, `numero`, `tipo_suelo`, `tipo_pared`, `fraccion_horaria`, `dias_disponibles`, `precio`, `imagen`, `fecha_registro`, `fecha_modificacion`) VALUES
(7, 4, 1, 'sintetico', 'blindex', '01:30:00', '{\"lunes\":{\"desde\":\"06:00\",\"hasta\":\"22:30\"},\"martes\":{\"desde\":\"06:00\",\"hasta\":\"22:30\"},\"miercoles\":{\"desde\":\"06:00\",\"hasta\":\"22:30\"},\"jueves\":{\"desde\":\"06:00\",\"hasta\":\"22:30\"},\"viernes\":{\"desde\":\"06:00\",\"hasta\":\"22:30\"},\"sabado\":{\"desde\":\"06:00\",\"hasta\":\"22:30\"},\"domingo\":{\"desde\":\"06:00\",\"hasta\":\"22:30\"}}', 23400, 'san bernardo.jpg', '2025-04-28 21:55:57', '2025-04-28 23:43:51'),
(8, 4, 2, 'sintetico', 'blindex', '01:30:00', '{\"lunes\":{\"desde\":\"06:00\",\"hasta\":\"22:30\"},\"martes\":{\"desde\":\"06:00\",\"hasta\":\"22:30\"},\"miercoles\":{\"desde\":\"06:00\",\"hasta\":\"22:30\"},\"jueves\":{\"desde\":\"06:00\",\"hasta\":\"22:30\"},\"viernes\":{\"desde\":\"06:00\",\"hasta\":\"22:30\"},\"sabado\":{\"desde\":\"06:00\",\"hasta\":\"22:30\"},\"domingo\":{\"desde\":\"06:00\",\"hasta\":\"22:30\"}}', 23400, 'san bernardo.jpg', '2025-04-28 22:03:40', '2025-04-28 22:03:40'),
(9, 4, 3, 'sintetico', 'blindex', '01:30:00', '{\"lunes\":{\"desde\":\"06:00\",\"hasta\":\"22:30\"},\"martes\":{\"desde\":\"06:00\",\"hasta\":\"22:30\"},\"miercoles\":{\"desde\":\"06:00\",\"hasta\":\"22:30\"}}', 23400, 'san bernardo.jpg', '2025-04-28 22:04:09', '2025-04-28 22:04:09');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clubes`
--

CREATE TABLE `clubes` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre_encargado` varchar(100) NOT NULL,
  `dni_encargado` varchar(20) NOT NULL,
  `telefono_encargado` varchar(50) NOT NULL,
  `email_encargado` varchar(100) NOT NULL,
  `nombre_complejo` varchar(100) NOT NULL,
  `cuit_complejo` varchar(20) NOT NULL,
  `direccion_complejo` varchar(255) NOT NULL,
  `telefono_complejo` varchar(50) NOT NULL,
  `cantidad_canchas` int(11) NOT NULL,
  `tipo_cesped` varchar(50) NOT NULL,
  `tipo_pared` varchar(50) NOT NULL,
  `tipo_techo` varchar(50) NOT NULL,
  `horario_apertura` time NOT NULL,
  `horario_cierre` time NOT NULL,
  `imagen_complejo` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clubes`
--

INSERT INTO `clubes` (`id`, `nombre_encargado`, `dni_encargado`, `telefono_encargado`, `email_encargado`, `nombre_complejo`, `cuit_complejo`, `direccion_complejo`, `telefono_complejo`, `cantidad_canchas`, `tipo_cesped`, `tipo_pared`, `tipo_techo`, `horario_apertura`, `horario_cierre`, `imagen_complejo`, `password`, `fecha_registro`) VALUES
(4, 'Ezequiel Vidal', '34747216', '2615587456', 'hola@ezequielvidal.com', 'Kondor', '23423423', '77 Madame Butterfly', '2615587456', 3, 'sintetico', 'cemento', 'cubierto', '15:39:00', '16:41:00', 'Captura de pantalla 2025-03-10 120832.png', '$2y$10$VpcHehHUHj0zdSzRCQO4kODqI2WVCWJJhnNuFwBIjwe1xclrOqfKy', '2025-04-28 16:38:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `invitaciones`
--

CREATE TABLE `invitaciones` (
  `id` int(10) UNSIGNED NOT NULL,
  `emisor_id` int(10) UNSIGNED NOT NULL,
  `receptor_id` int(10) UNSIGNED NOT NULL,
  `reserva_id` int(10) UNSIGNED DEFAULT NULL,
  `fecha_envio` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` enum('pendiente','aceptada','rechazada') NOT NULL DEFAULT 'pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `invitaciones`
--

INSERT INTO `invitaciones` (`id`, `emisor_id`, `receptor_id`, `reserva_id`, `fecha_envio`, `estado`) VALUES
(4, 1, 2, 22, '2025-04-29 16:47:58', 'pendiente'),
(5, 1, 4, 22, '2025-04-29 16:48:07', 'pendiente'),
(8, 5, 2, 24, '2025-04-29 16:48:55', 'pendiente'),
(9, 5, 4, 24, '2025-04-29 16:48:56', 'pendiente'),
(12, 5, 1, 26, '2025-04-29 17:35:42', 'aceptada');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id` int(10) UNSIGNED NOT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL,
  `mensaje` text NOT NULL,
  `estado` enum('pendiente','leida') NOT NULL DEFAULT 'pendiente',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notificaciones`
--

INSERT INTO `notificaciones` (`id`, `usuario_id`, `mensaje`, `estado`, `fecha_creacion`) VALUES
(1, 5, 'Te han invitado a la reserva del 29/04/2025 a las 15:00:00 en Kondor (Cancha 1).', 'pendiente', '2025-04-29 17:27:16'),
(2, 1, 'Te han invitado a la reserva del 29/04/2025 a las 16:30:00 en Kondor (Cancha 1).', 'leida', '2025-04-29 17:35:42');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservas`
--

CREATE TABLE `reservas` (
  `id` int(10) UNSIGNED NOT NULL,
  `cancha_id` int(10) UNSIGNED NOT NULL,
  `jugador_id` int(10) UNSIGNED NOT NULL,
  `fecha` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `estado` enum('confirmada','cancelada') NOT NULL DEFAULT 'confirmada',
  `fecha_reserva` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `reservas`
--

INSERT INTO `reservas` (`id`, `cancha_id`, `jugador_id`, `fecha`, `hora_inicio`, `hora_fin`, `estado`, `fecha_reserva`) VALUES
(26, 7, 5, '2025-04-29', '16:30:00', '18:00:00', 'cancelada', '2025-04-29 17:35:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(10) UNSIGNED NOT NULL,
  `tipo_usuario` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) DEFAULT NULL,
  `edad` int(11) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `provincia` varchar(100) DEFAULT NULL,
  `pais` varchar(100) DEFAULT NULL,
  `mano_habil` varchar(20) DEFAULT NULL,
  `posicion` varchar(20) DEFAULT NULL,
  `categoria` varchar(20) DEFAULT NULL,
  `tipo_juego` varchar(20) DEFAULT NULL,
  `imagen_perfil` varchar(255) DEFAULT NULL,
  `fecha_registro` date NOT NULL DEFAULT current_timestamp(),
  `token_recuperacion` varchar(100) DEFAULT NULL,
  `expiracion_token` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `tipo_usuario`, `nombre`, `apellido`, `edad`, `email`, `password`, `telefono`, `direccion`, `ciudad`, `provincia`, `pais`, `mano_habil`, `posicion`, `categoria`, `tipo_juego`, `imagen_perfil`, `fecha_registro`, `token_recuperacion`, `expiracion_token`) VALUES
(1, 'jugador', 'Ezequiel', 'Vidal', 35, 'pezequielvidal@gmail.com', '$2y$10$owX109JG94scpgtqu/Jwm.QTtjC6APDPx7orGowuCmToqWbXH8sB2', '02616627166', 'Madame butterfly 77 dpto 4', 'Guaymallen', 'Mendoza', 'Argentina', 'derecha', 'reves', '8va', 'ofensivo', 'san bernardo.jpg', '2025-04-25', NULL, NULL),
(2, 'jugador', 'pepe', 'pepe', 34, 'carlos@gmail.com', '$2y$10$nb/4gSsXBRw2FIdM/YawaOx7grJeY3668HlhDw3i0nAZUwv9folru', '02616627166', 'Madame butterfly 77 dpto 4', 'Guaymallen', 'Mendoza', 'Argentina', 'derecha', 'reves', '4ta', 'ofensivo', 'san bernardo.jpg', '2025-04-29', NULL, NULL),
(4, 'jugador', 'Pedro', 'Vidal', 22, 'pedro@gmail.com', '$2y$10$6YuVZ3v7uu87VoXRHEffp.yt.y1jvFmqDpC3Eqm6AfMhXim9/RuyW', '02616627166', 'Madame butterfly 77 dpto 4', 'Guaymallen', 'Mendoza', 'Argentina', 'derecha', 'reves', '6ta', 'ofensivo', 'san bernardo.jpg', '2025-04-29', NULL, NULL),
(5, 'jugador', 'asd', 'asd', 22, 'asdf@gmail.com', '$2y$10$Ve3sV/Sl3UKzurl03./l9u8L3K.rmLErvqGywFphqV4pjV8QZ2TLa', '02616627166', 'Madame Butterfly', 'Mendoza', 'Mendoza', 'Argentina', 'izquierda', 'drive', '1ra', 'defensivo', 'san bernardo.jpg', '2025-04-29', NULL, NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `canchas`
--
ALTER TABLE `canchas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_canchas_club` (`club_id`);

--
-- Indices de la tabla `clubes`
--
ALTER TABLE `clubes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `invitaciones`
--
ALTER TABLE `invitaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_emisor` (`emisor_id`),
  ADD KEY `idx_receptor` (`receptor_id`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario` (`usuario_id`);

--
-- Indices de la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_reservas_cancha` (`cancha_id`),
  ADD KEY `idx_reservas_jugador` (`jugador_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `canchas`
--
ALTER TABLE `canchas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `clubes`
--
ALTER TABLE `clubes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `invitaciones`
--
ALTER TABLE `invitaciones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `canchas`
--
ALTER TABLE `canchas`
  ADD CONSTRAINT `fk_canchas_clubes` FOREIGN KEY (`club_id`) REFERENCES `clubes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `invitaciones`
--
ALTER TABLE `invitaciones`
  ADD CONSTRAINT `fk_invitaciones_emisor` FOREIGN KEY (`emisor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_invitaciones_receptor` FOREIGN KEY (`receptor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `fk_notificaciones_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD CONSTRAINT `fk_reservas_canchas` FOREIGN KEY (`cancha_id`) REFERENCES `canchas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reservas_usuarios` FOREIGN KEY (`jugador_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
