-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 06-02-2025 a las 10:55:20
-- Versión del servidor: 8.3.0
-- Versión de PHP: 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `carfinity`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chat`
--

DROP TABLE IF EXISTS `chat`;
CREATE TABLE IF NOT EXISTS `chat` (
  `ID_Chat` int NOT NULL AUTO_INCREMENT,
  `ID_Cliente` int NOT NULL,
  `ID_Vendedor` int NOT NULL,
  `Fecha_Creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `Estado` enum('activo','cerrado') DEFAULT 'activo',
  PRIMARY KEY (`ID_Chat`),
  KEY `ID_Cliente` (`ID_Cliente`),
  KEY `ID_Vendedor` (`ID_Vendedor`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente_servicio`
--

DROP TABLE IF EXISTS `cliente_servicio`;
CREATE TABLE IF NOT EXISTS `cliente_servicio` (
  `ID_Cliente` int NOT NULL,
  `ID_Servicio` int NOT NULL,
  `Fecha_Contratacion` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID_Cliente`,`ID_Servicio`),
  KEY `ID_Servicio` (`ID_Servicio`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `coche`
--

DROP TABLE IF EXISTS `coche`;
CREATE TABLE IF NOT EXISTS `coche` (
  `ID_Coche` int NOT NULL AUTO_INCREMENT,
  `Marca` varchar(50) DEFAULT NULL,
  `Modelo` varchar(50) DEFAULT NULL,
  `Año` int DEFAULT NULL,
  `Precio` decimal(10,2) DEFAULT NULL,
  `Descripcion` text,
  `Estado` enum('disponible','reservado','vendido') DEFAULT 'disponible',
  `Foto` varchar(255) NOT NULL,
  `ID_Admin` int DEFAULT NULL,
  PRIMARY KEY (`ID_Coche`),
  KEY `ID_Admin` (`ID_Admin`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensaje`
--

DROP TABLE IF EXISTS `mensaje`;
CREATE TABLE IF NOT EXISTS `mensaje` (
  `ID_Mensaje` int NOT NULL AUTO_INCREMENT,
  `ID_Chat` int NOT NULL,
  `Remitente` enum('cliente','vendedor') NOT NULL,
  `Contenido` text NOT NULL,
  `Fecha_Envio` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID_Mensaje`),
  KEY `ID_Chat` (`ID_Chat`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `opinion`
--

DROP TABLE IF EXISTS `opinion`;
CREATE TABLE IF NOT EXISTS `opinion` (
  `ID_Opinion` int NOT NULL AUTO_INCREMENT,
  `ID_Coche` int NOT NULL,
  `ID_Cliente` int NOT NULL,
  `Comentario` text,
  `Valoracion` int DEFAULT NULL,
  `Fecha` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID_Opinion`),
  KEY `ID_Coche` (`ID_Coche`),
  KEY `ID_Cliente` (`ID_Cliente`)
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reserva`
--

DROP TABLE IF EXISTS `reserva`;
CREATE TABLE IF NOT EXISTS `reserva` (
  `ID_Reserva` int NOT NULL AUTO_INCREMENT,
  `ID_Coche` int DEFAULT NULL,
  `ID_Cliente` int DEFAULT NULL,
  `Fecha_Reserva` datetime DEFAULT CURRENT_TIMESTAMP,
  `Estado` enum('pendiente','confirmada','cancelada') DEFAULT 'pendiente',
  PRIMARY KEY (`ID_Reserva`),
  KEY `ID_Coche` (`ID_Coche`),
  KEY `ID_Cliente` (`ID_Cliente`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicio_adicional`
--

DROP TABLE IF EXISTS `servicio_adicional`;
CREATE TABLE IF NOT EXISTS `servicio_adicional` (
  `ID_Servicio` int NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(100) DEFAULT NULL,
  `Descripcion` text,
  `Precio` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`ID_Servicio`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

DROP TABLE IF EXISTS `usuario`;
CREATE TABLE IF NOT EXISTS `usuario` (
  `ID_Usuario` int NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(100) DEFAULT NULL,
  `Correo` varchar(100) DEFAULT NULL,
  `Contraseña` varchar(255) DEFAULT NULL,
  `Tipo` enum('admin','cliente') NOT NULL,
  PRIMARY KEY (`ID_Usuario`),
  UNIQUE KEY `Correo` (`Correo`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
