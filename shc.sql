-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 19-06-2025 a las 17:10:36
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
-- Base de datos: `shc`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel_cache_356a192b7913b04c54574d18c28d46e6395428ab', 'i:2;', 1750164546),
('laravel_cache_356a192b7913b04c54574d18c28d46e6395428ab:timer', 'i:1750164546;', 1750164546),
('laravel_cache_af3e133428b9e25c55bc59fe534248e6a0c0f17b', 'i:1;', 1750162744),
('laravel_cache_af3e133428b9e25c55bc59fe534248e6a0c0f17b:timer', 'i:1750162744;', 1750162744),
('laravel_cache_carjavalosiste@gmail.com|127.0.0.1', 'i:1;', 1750186422),
('laravel_cache_carjavalosiste@gmail.com|127.0.0.1:timer', 'i:1750186422;', 1750186422),
('laravel_cache_cb7a1d775e800fd1ee4049f7dca9e041eb9ba083', 'i:4;', 1750110969),
('laravel_cache_cb7a1d775e800fd1ee4049f7dca9e041eb9ba083:timer', 'i:1750110969;', 1750110969),
('laravel_cache_fc074d501302eb2b93e2554793fcaf50b3bf7291', 'i:2;', 1750108851),
('laravel_cache_fc074d501302eb2b93e2554793fcaf50b3bf7291:timer', 'i:1750108851;', 1750108851);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `descripcion` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `descripcion`, `created_at`, `updated_at`) VALUES
(1, 'Medicina General', '2025-06-19 19:13:43', '2025-06-19 19:13:43'),
(2, 'Pediatría', '2025-06-19 19:13:43', '2025-06-19 19:13:43'),
(3, 'Ginecología', '2025-06-19 19:13:43', '2025-06-19 19:13:43'),
(4, 'Cardiología', '2025-06-19 19:13:43', '2025-06-19 19:13:43'),
(5, 'Neurología', '2025-06-19 19:13:43', '2025-06-19 19:13:43'),
(6, 'Dermatología', '2025-06-19 19:13:43', '2025-06-19 19:13:43'),
(7, 'Oftalmología', '2025-06-19 19:13:43', '2025-06-19 19:13:43'),
(8, 'Traumatología', '2025-06-19 19:13:43', '2025-06-19 19:13:43'),
(9, 'Psiquiatría', '2025-06-19 19:13:43', '2025-06-19 19:13:43'),
(10, 'Radiología', '2025-06-19 19:13:43', '2025-06-19 19:13:43'),
(11, 'Laboratorio Clínico', '2025-06-19 19:13:43', '2025-06-19 19:13:43'),
(12, 'Enfermería', '2025-06-19 19:13:43', '2025-06-19 19:13:43'),
(13, 'Farmacia', '2025-06-19 19:13:43', '2025-06-19 19:13:43'),
(14, 'Administración Hospitalaria', '2025-06-19 19:13:43', '2025-06-19 19:13:43'),
(15, 'Gestión de Calidad', '2025-06-19 19:13:43', '2025-06-19 19:13:43'),
(16, 'Seguridad del Paciente', '2025-06-19 19:13:43', '2025-06-19 19:13:43'),
(17, 'Bioseguridad', '2025-06-19 19:13:43', '2025-06-19 19:13:43'),
(18, 'Manejo de Residuos', '2025-06-19 19:13:43', '2025-06-19 19:13:43'),
(19, 'Atención al Usuario', '2025-06-19 19:13:43', '2025-06-19 19:13:43'),
(20, 'Sistemas de Información', '2025-06-19 19:13:43', '2025-06-19 19:13:43'),
(21, 'Recursos Humanos', '2025-06-19 19:13:43', '2025-06-19 19:13:43'),
(22, 'Contabilidad y Finanzas', '2025-06-19 19:13:43', '2025-06-19 19:13:43'),
(23, 'Auditoría Médica', '2025-06-19 19:13:43', '2025-06-19 19:13:43'),
(24, 'Epidemiología', '2025-06-19 19:13:43', '2025-06-19 19:13:43'),
(25, 'Salud Pública', '2025-06-19 19:13:43', '2025-06-19 19:13:43');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_06_05_181325_add_apellidos_to_users_table', 1),
(5, '2025_06_05_213518_create_procedimientos_table', 1),
(6, '2025_06_16_134428_add_role_to_users_table', 2),
(7, '2025_06_16_142644_add_document_fields_to_users_table', 3),
(8, '2025_06_18_213757_create_user_logins_table', 4),
(9, '2025_06_19_140901_create_categorias_table', 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `procedimientos`
--

CREATE TABLE `procedimientos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `Cod_Episodio` int(11) DEFAULT NULL,
  `Cod_Sala` int(11) DEFAULT NULL,
  `Nom_Sala` varchar(100) DEFAULT NULL,
  `Num_Cama` varchar(20) DEFAULT NULL,
  `F_Ingreso` datetime DEFAULT NULL,
  `Cod_Eps` varchar(20) DEFAULT NULL,
  `Nom_Eps` varchar(100) DEFAULT NULL,
  `Hist_Clinica` int(11) DEFAULT NULL,
  `Tipo_Ident` varchar(5) DEFAULT NULL,
  `Num_Ident` varchar(20) DEFAULT NULL,
  `Edad` int(11) DEFAULT NULL,
  `Sexo` char(1) DEFAULT NULL,
  `Servicio` varchar(100) DEFAULT NULL,
  `Estado` varchar(50) DEFAULT NULL,
  `Medico_Trata` varchar(100) DEFAULT NULL,
  `Cod_Diag` varchar(10) DEFAULT NULL,
  `CIE10` varchar(10) DEFAULT NULL,
  `Diagnostico` varchar(255) DEFAULT NULL,
  `Antimicrobiano` varchar(100) DEFAULT NULL,
  `Cantidad` varchar(50) DEFAULT NULL,
  `Presentacion` varchar(50) DEFAULT NULL,
  `Via_Aplicacion` varchar(50) DEFAULT NULL,
  `Tiem_Horas` varchar(50) DEFAULT NULL,
  `Dias_Antibioticos` varchar(50) DEFAULT NULL,
  `Fec_Sumistro` date DEFAULT NULL,
  `Ho_Sumisnistro` time DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('CbbwkpzgAShmP4vniL0RjwQsjW6winHQiXiBrkfO', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoic3R6Y1h4ODJFQUJGRFNWTThtbVR4aFNlTWhGVWt6UjhGNEVWOWo3ViI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9jYXBhY2l0YWNpb25lcy9jYXRlZ29yaWFzIjt9czozOiJ1cmwiO2E6MDp7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==', 1750345792),
('S50PtNOXGxAYL7mYp711nfXgbw5jeYttIP8BaYV0', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiODVqTUpMQUVjTjNXT2pWSTF3Zm5BcnU3RzVmeGlOUW5udTdvamJWSyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzU6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC91c2Vycy8zNi9lZGl0Ijt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1750285162);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `apellido1` varchar(100) DEFAULT NULL,
  `apellido2` varchar(100) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `role` enum('Super Admin','Administrador','Docente','Estudiante','Registrado') NOT NULL DEFAULT 'Registrado',
  `tipo_documento` enum('DNI','Pasaporte','Carnet de Extranjería','Cédula') DEFAULT NULL,
  `numero_documento` varchar(20) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `name`, `apellido1`, `apellido2`, `email`, `role`, `tipo_documento`, `numero_documento`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Carlos Jairton', 'Valderrama', 'Orobio', 'carjavalosistem@gmail.com', 'Super Admin', 'DNI', '121424443', '2025-06-17 02:36:45', '$2y$12$EnOfSKid6Q0GxBR0ncZjde2okJWsrZIr999R7/gzJAEcAZJ2IIvPq', NULL, '2025-06-16 18:26:54', '2025-06-17 02:38:57'),
(36, 'Cristian', 'Salamanca', 'Perez', 'innovacionydesarrollo@correohuv.gov.co', 'Registrado', 'Pasaporte', '6427785448', '2025-06-17 02:19:51', '$2y$12$vqcWDupojpWno7FubN9m0ezjJOHmXSVuBFGnbm6MD73KPyytabHtu', NULL, '2025-06-17 02:17:36', '2025-06-17 02:19:51'),
(37, 'kevin', 'Chavarro', 'Erazo', 'keviindavid00@gmail.com', 'Registrado', 'Cédula', '1233321', '2025-06-17 05:04:04', '$2y$12$y1RMMw0/bqy4KgVa.L4DRetmOPVCm0ceI38zTioB0tbjlCRjjicYa', NULL, '2025-06-17 02:48:13', '2025-06-17 02:48:13'),
(38, 'Usuario', 'Prueba', 'Verificado', 'test@example.com', 'Registrado', 'DNI', '87654321', '2025-06-17 05:04:04', '$2y$12$8RjSJS9V/WqEVYGKw8HQAuTI7G73FXCIcfbbCpK4sXUopD1dwyLCi', NULL, '2025-06-17 03:03:26', '2025-06-17 03:03:26'),
(40, 'Daniver', 'Torres', 'Campaz', 'danivertorres90@gmail.com', 'Registrado', 'Cédula', '9666966', NULL, '$2y$12$WI4zQaCU4nnXjI4PKndPeea7fCIK/NHFQMK6/ChzvBgRXLJGRZd/a', NULL, '2025-06-17 17:16:48', '2025-06-17 17:16:48'),
(41, 'danniver', 'Torres', 'Campaz', 'carjavalo1@hotmail.com', 'Registrado', 'Cédula', '9696696', NULL, '$2y$12$G66PknUc/yI9gTmw.nLewuT0RDhrE3gLGPBSpl/aw6Rkng6bocRvS', NULL, '2025-06-17 17:43:36', '2025-06-17 17:43:36'),
(42, 'Usuario', 'Prueba', 'Verificacion', 'test.verificacion@example.com', 'Registrado', 'DNI', '99999999', NULL, '$2y$12$IkoJLmdplYi5D89Q0V6GB.eE5ejaMqxS66Dxuow3/mj26DTYS31Xq', NULL, '2025-06-17 17:51:24', '2025-06-17 17:51:24'),
(43, 'Usuario', 'Sin', 'Verificar', 'sin.verificar@example.com', 'Registrado', 'DNI', '88888888', NULL, '$2y$12$kVdczM2GLVJeHSk3Vtx2AeaLPsFZVVYy8z25w/GBIImS5BRl1XSqC', NULL, '2025-06-17 17:51:24', '2025-06-17 17:51:24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_logins`
--

CREATE TABLE `user_logins` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `status` enum('success','failed') NOT NULL,
  `email_verified` enum('verified','unverified') DEFAULT NULL,
  `failure_reason` varchar(255) DEFAULT NULL,
  `attempted_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `user_logins`
--

INSERT INTO `user_logins` (`id`, `user_id`, `email`, `ip_address`, `user_agent`, `status`, `email_verified`, `failure_reason`, `attempted_at`, `created_at`, `updated_at`) VALUES
(1, 36, 'innovacionydesarrollo@correohuv.gov.co', '198.51.100.5', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1', 'success', 'verified', NULL, '2025-05-20 12:24:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(2, 38, 'test@example.com', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'verified', NULL, '2025-05-20 11:14:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(3, 40, 'danivertorres90@gmail.com', '203.0.113.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'unverified', NULL, '2025-05-20 23:31:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(4, 1, 'carjavalosistem@gmail.com', '203.0.113.10', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'verified', NULL, '2025-05-21 20:28:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(5, 42, 'test.verificacion@example.com', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', 'success', 'unverified', NULL, '2025-05-22 00:38:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(6, 38, 'test@example.com', '203.0.113.10', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1', 'success', 'verified', NULL, '2025-05-23 01:16:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(7, 38, 'test@example.com', '203.0.113.10', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'verified', NULL, '2025-05-22 12:36:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(8, 1, 'carjavalosistem@gmail.com', '192.168.1.100', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'verified', NULL, '2025-05-23 18:02:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(9, 40, 'danivertorres90@gmail.com.fake', '172.16.0.25', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'failed', 'unverified', 'Email no verificado', '2025-05-23 17:14:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(10, 36, 'innovacionydesarrollo@correohuv.gov.co', '172.16.0.25', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'verified', NULL, '2025-05-23 16:39:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(11, 40, 'danivertorres90@gmail.com', '192.168.1.100', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'unverified', NULL, '2025-05-24 16:31:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(12, 37, 'keviindavid00@gmail.com', '10.0.0.50', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'verified', NULL, '2025-05-24 12:44:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(13, 40, 'danivertorres90@gmail.com', '10.0.0.50', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'unverified', NULL, '2025-05-25 00:08:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(14, 1, 'carjavalosistem@gmail.com', '172.16.0.25', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', 'success', 'verified', NULL, '2025-05-24 18:36:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(15, 37, 'keviindavid00@gmail.com', '172.16.0.25', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'verified', NULL, '2025-05-24 17:25:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(16, 38, 'test@example.com', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'verified', NULL, '2025-05-25 15:08:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(17, NULL, 'innovacionydesarrollo@correohuv.gov.co', '198.51.100.5', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'failed', 'verified', 'Credenciales inválidas', '2025-05-25 16:05:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(18, 42, 'test.verificacion@example.com', '172.16.0.25', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1', 'failed', 'unverified', 'Credenciales inválidas', '2025-05-25 17:06:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(19, 40, 'danivertorres90@gmail.com', '192.168.1.100', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1', 'failed', 'unverified', 'Email no verificado', '2025-05-25 18:49:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(20, 42, 'test.verificacion@example.com', '198.51.100.5', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', 'success', 'unverified', NULL, '2025-05-26 12:37:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(21, 1, 'carjavalosistem@gmail.com', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', 'success', 'verified', NULL, '2025-05-26 20:18:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(22, 38, 'test@example.com', '172.16.0.25', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'verified', NULL, '2025-05-26 16:27:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(23, 1, 'carjavalosistem@gmail.com', '192.168.1.100', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1', 'success', 'verified', NULL, '2025-05-26 11:46:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(24, 43, 'sin.verificar@example.com', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', 'success', 'unverified', NULL, '2025-05-26 14:30:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(25, 38, 'test@example.com', '198.51.100.5', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1', 'failed', 'verified', 'Email no verificado', '2025-05-27 00:47:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(26, 42, 'test.verificacion@example.com', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'unverified', NULL, '2025-05-26 19:25:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(27, 40, 'danivertorres90@gmail.com', '198.51.100.5', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'unverified', NULL, '2025-05-26 14:11:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(28, 41, 'carjavalo1@hotmail.com', '198.51.100.5', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'unverified', NULL, '2025-05-27 23:24:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(29, 43, 'sin.verificar@example.com', '198.51.100.5', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'unverified', NULL, '2025-05-27 15:11:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(30, 1, 'carjavalosistem@gmail.com', '172.16.0.25', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'verified', NULL, '2025-05-27 16:47:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(31, 1, 'carjavalosistem@gmail.com', '203.0.113.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', 'success', 'verified', NULL, '2025-05-27 12:24:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(32, 43, 'sin.verificar@example.com', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', 'success', 'unverified', NULL, '2025-05-27 14:42:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(33, NULL, 'danivertorres90@gmail.com', '172.16.0.25', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'failed', 'unverified', 'Email no verificado', '2025-05-28 22:22:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(34, 38, 'test@example.com', '198.51.100.5', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1', 'success', 'verified', NULL, '2025-05-28 22:54:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(35, 43, 'sin.verificar@example.com', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', 'success', 'unverified', NULL, '2025-05-28 17:52:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(36, 1, 'carjavalosistem@gmail.com', '10.0.0.50', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'verified', NULL, '2025-05-29 22:23:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(37, 41, 'carjavalo1@hotmail.com', '198.51.100.5', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'unverified', NULL, '2025-05-29 18:22:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(38, 41, 'carjavalo1@hotmail.com', '172.16.0.25', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1', 'success', 'unverified', NULL, '2025-05-30 17:25:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(39, 36, 'innovacionydesarrollo@correohuv.gov.co', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', 'success', 'verified', NULL, '2025-05-30 22:40:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(40, NULL, 'carjavalo1@hotmail.com', '203.0.113.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', 'failed', 'unverified', 'Email no verificado', '2025-05-31 01:32:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(41, 43, 'sin.verificar@example.com', '203.0.113.10', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'unverified', NULL, '2025-05-30 12:50:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(42, 41, 'carjavalo1@hotmail.com.fake', '203.0.113.10', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'failed', 'unverified', 'Credenciales inválidas', '2025-05-31 14:18:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(43, 37, 'keviindavid00@gmail.com', '203.0.113.10', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1', 'success', 'verified', NULL, '2025-05-31 13:32:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(44, 37, 'keviindavid00@gmail.com', '172.16.0.25', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'verified', NULL, '2025-06-02 01:05:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(45, 42, 'test.verificacion@example.com', '198.51.100.5', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1', 'success', 'unverified', NULL, '2025-06-01 13:33:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(46, 41, 'carjavalo1@hotmail.com', '203.0.113.10', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1', 'success', 'unverified', NULL, '2025-06-01 23:57:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(47, 36, 'innovacionydesarrollo@correohuv.gov.co', '198.51.100.5', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'verified', NULL, '2025-06-01 13:35:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(48, 1, 'carjavalosistem@gmail.com', '10.0.0.50', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'verified', NULL, '2025-06-01 22:59:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(49, 41, 'carjavalo1@hotmail.com', '172.16.0.25', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1', 'success', 'unverified', NULL, '2025-06-01 12:35:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(50, 36, 'innovacionydesarrollo@correohuv.gov.co', '172.16.0.25', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1', 'success', 'verified', NULL, '2025-06-01 14:10:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(51, 37, 'keviindavid00@gmail.com', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'verified', NULL, '2025-06-02 22:11:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(52, 1, 'carjavalosistem@gmail.com', '203.0.113.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', 'success', 'verified', NULL, '2025-06-02 16:19:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(53, 40, 'danivertorres90@gmail.com.fake', '198.51.100.5', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'failed', 'unverified', 'Email no verificado', '2025-06-03 00:52:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(54, 40, 'danivertorres90@gmail.com', '203.0.113.10', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1', 'success', 'unverified', NULL, '2025-06-02 14:54:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(55, 40, 'danivertorres90@gmail.com', '10.0.0.50', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'unverified', NULL, '2025-06-02 20:31:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(56, 41, 'carjavalo1@hotmail.com.fake', '172.16.0.25', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', 'failed', 'unverified', 'Email no verificado', '2025-06-02 23:54:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(57, 42, 'test.verificacion@example.com', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', 'success', 'unverified', NULL, '2025-06-03 17:50:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(58, 1, 'carjavalosistem@gmail.com', '10.0.0.50', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'verified', NULL, '2025-06-03 16:29:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(59, 1, 'carjavalosistem@gmail.com', '172.16.0.25', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'verified', NULL, '2025-06-03 14:17:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(60, 40, 'danivertorres90@gmail.com', '203.0.113.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', 'success', 'unverified', NULL, '2025-06-03 14:47:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(61, 43, 'sin.verificar@example.com', '172.16.0.25', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', 'failed', 'unverified', 'Email no verificado', '2025-06-04 23:27:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(62, 1, 'carjavalosistem@gmail.com', '10.0.0.50', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1', 'failed', 'verified', 'Email no verificado', '2025-06-04 20:42:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(63, 41, 'carjavalo1@hotmail.com', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', 'success', 'unverified', NULL, '2025-06-04 11:01:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(64, 42, 'test.verificacion@example.com', '192.168.1.100', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1', 'success', 'unverified', NULL, '2025-06-04 18:41:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(65, 1, 'carjavalosistem@gmail.com', '172.16.0.25', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'verified', NULL, '2025-06-04 21:52:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(66, 41, 'carjavalo1@hotmail.com', '172.16.0.25', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'unverified', NULL, '2025-06-04 23:24:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(67, 42, 'test.verificacion@example.com', '198.51.100.5', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1', 'success', 'unverified', NULL, '2025-06-04 15:24:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(68, 36, 'innovacionydesarrollo@correohuv.gov.co', '172.16.0.25', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'verified', NULL, '2025-06-04 19:58:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(69, 1, 'carjavalosistem@gmail.com', '10.0.0.50', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'verified', NULL, '2025-06-05 20:24:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(70, 1, 'carjavalosistem@gmail.com', '172.16.0.25', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', 'success', 'verified', NULL, '2025-06-05 11:15:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(71, 40, 'danivertorres90@gmail.com', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', 'success', 'unverified', NULL, '2025-06-05 14:23:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(72, 41, 'carjavalo1@hotmail.com', '198.51.100.5', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'unverified', NULL, '2025-06-05 12:49:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(73, 1, 'carjavalosistem@gmail.com', '198.51.100.5', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'verified', NULL, '2025-06-05 16:33:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(74, 37, 'keviindavid00@gmail.com', '192.168.1.100', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'verified', NULL, '2025-06-05 22:10:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(75, 40, 'danivertorres90@gmail.com', '172.16.0.25', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', 'failed', 'unverified', 'Credenciales inválidas', '2025-06-06 14:24:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(76, 42, 'test.verificacion@example.com', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'unverified', NULL, '2025-06-07 01:00:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(77, 43, 'sin.verificar@example.com', '192.168.1.100', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1', 'success', 'unverified', NULL, '2025-06-06 17:27:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(78, NULL, 'carjavalosistem@gmail.com', '198.51.100.5', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', 'failed', 'verified', 'Credenciales inválidas', '2025-06-06 23:09:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(79, 41, 'carjavalo1@hotmail.com', '203.0.113.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', 'success', 'unverified', NULL, '2025-06-06 11:00:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(80, NULL, 'innovacionydesarrollo@correohuv.gov.co', '192.168.1.100', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'failed', 'verified', 'Email no verificado', '2025-06-07 15:25:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(81, 42, 'test.verificacion@example.com', '192.168.1.100', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'unverified', NULL, '2025-06-07 13:00:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(82, 42, 'test.verificacion@example.com', '203.0.113.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'failed', 'unverified', 'Credenciales inválidas', '2025-06-07 19:37:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(83, 41, 'carjavalo1@hotmail.com', '10.0.0.50', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'unverified', NULL, '2025-06-07 14:54:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(84, 40, 'danivertorres90@gmail.com', '198.51.100.5', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1', 'success', 'unverified', NULL, '2025-06-08 16:20:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(85, 41, 'carjavalo1@hotmail.com', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'unverified', NULL, '2025-06-08 13:40:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(86, 37, 'keviindavid00@gmail.com', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'verified', NULL, '2025-06-08 23:51:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(87, 38, 'test@example.com', '198.51.100.5', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1', 'success', 'verified', NULL, '2025-06-08 19:08:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(88, 41, 'carjavalo1@hotmail.com', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', 'success', 'unverified', NULL, '2025-06-09 21:07:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(89, 43, 'sin.verificar@example.com', '198.51.100.5', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'unverified', NULL, '2025-06-10 00:37:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(90, 40, 'danivertorres90@gmail.com', '198.51.100.5', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', 'success', 'unverified', NULL, '2025-06-09 21:18:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(91, 36, 'innovacionydesarrollo@correohuv.gov.co', '203.0.113.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', 'failed', 'verified', 'Email no verificado', '2025-06-09 18:26:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(92, 37, 'keviindavid00@gmail.com', '192.168.1.100', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'verified', NULL, '2025-06-10 21:35:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(93, 42, 'test.verificacion@example.com', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', 'success', 'unverified', NULL, '2025-06-10 17:23:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(94, NULL, 'test@example.com.fake', '198.51.100.5', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'failed', 'verified', 'Email no verificado', '2025-06-10 12:57:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(95, 1, 'carjavalosistem@gmail.com', '198.51.100.5', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'verified', NULL, '2025-06-10 22:46:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(96, 42, 'test.verificacion@example.com', '198.51.100.5', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'failed', 'unverified', 'Credenciales inválidas', '2025-06-10 18:35:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(97, 36, 'innovacionydesarrollo@correohuv.gov.co', '198.51.100.5', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'verified', NULL, '2025-06-10 13:32:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(98, 37, 'keviindavid00@gmail.com', '172.16.0.25', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'verified', NULL, '2025-06-10 15:03:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(99, 38, 'test@example.com', '203.0.113.10', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'failed', 'verified', 'Email no verificado', '2025-06-11 23:08:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(100, 40, 'danivertorres90@gmail.com', '203.0.113.10', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'unverified', NULL, '2025-06-11 15:17:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(101, 36, 'innovacionydesarrollo@correohuv.gov.co', '198.51.100.5', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'verified', NULL, '2025-06-11 17:14:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(102, 42, 'test.verificacion@example.com', '10.0.0.50', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1', 'success', 'unverified', NULL, '2025-06-12 19:51:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(103, 37, 'keviindavid00@gmail.com', '172.16.0.25', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', 'success', 'verified', NULL, '2025-06-12 11:24:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(104, 38, 'test@example.com', '198.51.100.5', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'verified', NULL, '2025-06-12 16:28:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(105, 38, 'test@example.com', '172.16.0.25', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', 'success', 'verified', NULL, '2025-06-12 14:47:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(106, 42, 'test.verificacion@example.com', '198.51.100.5', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'unverified', NULL, '2025-06-12 23:03:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(107, 1, 'carjavalosistem@gmail.com', '172.16.0.25', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1', 'success', 'verified', NULL, '2025-06-13 22:11:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(108, 38, 'test@example.com', '10.0.0.50', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'verified', NULL, '2025-06-13 22:17:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(109, 37, 'keviindavid00@gmail.com', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', 'success', 'verified', NULL, '2025-06-13 11:11:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(110, 40, 'danivertorres90@gmail.com', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', 'success', 'unverified', NULL, '2025-06-13 19:05:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(111, 40, 'danivertorres90@gmail.com', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', 'failed', 'unverified', 'Email no verificado', '2025-06-13 14:34:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(112, 38, 'test@example.com', '192.168.1.100', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1', 'success', 'verified', NULL, '2025-06-13 23:00:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(113, 41, 'carjavalo1@hotmail.com', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'unverified', NULL, '2025-06-13 22:17:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(114, 40, 'danivertorres90@gmail.com', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', 'success', 'unverified', NULL, '2025-06-13 17:17:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(115, 1, 'carjavalosistem@gmail.com', '192.168.1.100', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1', 'success', 'verified', NULL, '2025-06-14 12:02:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(116, 42, 'test.verificacion@example.com', '203.0.113.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', 'failed', 'unverified', 'Email no verificado', '2025-06-14 21:24:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(117, 40, 'danivertorres90@gmail.com', '198.51.100.5', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1', 'success', 'unverified', NULL, '2025-06-15 11:47:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(118, 38, 'test@example.com', '198.51.100.5', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'verified', NULL, '2025-06-15 15:02:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(119, 40, 'danivertorres90@gmail.com', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'unverified', NULL, '2025-06-16 22:11:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(120, 38, 'test@example.com', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'verified', NULL, '2025-06-17 01:44:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(121, 36, 'innovacionydesarrollo@correohuv.gov.co', '198.51.100.5', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'verified', NULL, '2025-06-16 23:42:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(122, 41, 'carjavalo1@hotmail.com', '203.0.113.10', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'unverified', NULL, '2025-06-16 15:28:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(123, 38, 'test@example.com', '203.0.113.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'verified', NULL, '2025-06-16 18:06:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(124, 41, 'carjavalo1@hotmail.com', '198.51.100.5', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'unverified', NULL, '2025-06-16 13:39:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(125, 43, 'sin.verificar@example.com', '198.51.100.5', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'unverified', NULL, '2025-06-16 22:50:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(126, 38, 'test@example.com', '192.168.1.100', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'verified', NULL, '2025-06-16 21:54:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(127, 43, 'sin.verificar@example.com', '192.168.1.100', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'unverified', NULL, '2025-06-17 13:09:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(128, 41, 'carjavalo1@hotmail.com', '203.0.113.10', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1', 'success', 'unverified', NULL, '2025-06-17 15:50:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(129, 41, 'carjavalo1@hotmail.com', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'unverified', NULL, '2025-06-17 12:42:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(130, 40, 'danivertorres90@gmail.com', '192.168.1.100', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'unverified', NULL, '2025-06-17 18:29:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(131, 37, 'keviindavid00@gmail.com', '192.168.1.100', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'failed', 'verified', 'Credenciales inválidas', '2025-06-17 18:47:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(132, 43, 'sin.verificar@example.com', '172.16.0.25', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', 'success', 'unverified', NULL, '2025-06-18 21:47:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(133, 38, 'test@example.com', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'verified', NULL, '2025-06-18 22:53:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(134, 40, 'danivertorres90@gmail.com', '198.51.100.5', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'failed', 'unverified', 'Credenciales inválidas', '2025-06-18 22:24:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(135, 43, 'sin.verificar@example.com.fake', '203.0.113.10', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'failed', 'unverified', 'Email no verificado', '2025-06-18 23:21:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(136, 40, 'danivertorres90@gmail.com', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', 'success', 'unverified', NULL, '2025-06-18 19:13:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(137, 42, 'test.verificacion@example.com', '10.0.0.50', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'unverified', NULL, '2025-06-18 13:21:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(138, 41, 'carjavalo1@hotmail.com', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', 'success', 'unverified', NULL, '2025-06-18 20:20:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(139, 42, 'test.verificacion@example.com', '10.0.0.50', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'unverified', NULL, '2025-06-19 13:08:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(140, 1, 'carjavalosistem@gmail.com', '10.0.0.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', 'success', 'verified', NULL, '2025-06-19 23:32:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(141, 40, 'danivertorres90@gmail.com', '192.168.1.100', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'unverified', NULL, '2025-06-19 13:02:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(142, 37, 'keviindavid00@gmail.com', '172.16.0.25', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'success', 'verified', NULL, '2025-06-19 20:21:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(143, 41, 'carjavalo1@hotmail.com', '198.51.100.5', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', 'success', 'unverified', NULL, '2025-06-19 23:43:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(144, 37, 'keviindavid00@gmail.com', '198.51.100.5', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', 'success', 'verified', NULL, '2025-06-19 13:57:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(145, NULL, 'spam@bot.net', '203.0.113.10', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'failed', 'unverified', 'Usuario no encontrado', '2025-06-07 16:48:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(146, NULL, 'admin@fake.com', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', 'failed', 'unverified', 'Usuario no encontrado', '2025-06-06 00:48:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(147, NULL, 'test@nonexistent.com', '192.168.1.100', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'failed', 'unverified', 'Usuario no encontrado', '2025-06-15 09:48:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(148, NULL, 'test@nonexistent.com', '203.0.113.10', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1', 'failed', 'unverified', 'Usuario no encontrado', '2025-06-11 13:48:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(149, NULL, 'hacker@malicious.com', '198.51.100.5', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'failed', 'unverified', 'Usuario no encontrado', '2025-06-16 08:48:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(150, NULL, 'spam@bot.net', '172.16.0.25', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', 'failed', 'unverified', 'Usuario no encontrado', '2025-06-06 10:48:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(151, NULL, 'user@invalid.org', '172.16.0.25', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'failed', 'unverified', 'Usuario no encontrado', '2025-06-10 15:48:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(152, NULL, 'admin@fake.com', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'failed', 'unverified', 'Usuario no encontrado', '2025-06-09 07:48:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(153, NULL, 'user@invalid.org', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', 'failed', 'unverified', 'Usuario no encontrado', '2025-06-12 19:48:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(154, NULL, 'user@invalid.org', '172.16.0.25', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', 'failed', 'unverified', 'Usuario no encontrado', '2025-06-11 19:48:33', '2025-06-19 02:48:33', '2025-06-19 02:48:33'),
(155, 1, 'carjavalosistem@gmail.com', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'success', 'verified', NULL, '2025-06-19 02:52:06', '2025-06-19 02:52:06', '2025-06-19 02:52:06'),
(156, 1, 'carjavalosistem@gmail.com', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'success', 'verified', NULL, '2025-06-19 02:53:50', '2025-06-19 02:53:50', '2025-06-19 02:53:50'),
(157, 1, 'carjavalosistem@gmail.com', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'success', 'verified', NULL, '2025-06-19 03:18:53', '2025-06-19 03:18:53', '2025-06-19 03:18:53'),
(158, 1, 'carjavalosistem@gmail.com', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'success', 'verified', NULL, '2025-06-19 17:42:36', '2025-06-19 17:42:36', '2025-06-19 17:42:36'),
(159, 1, 'carjavalosistem@gmail.com', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'success', 'verified', NULL, '2025-06-19 19:17:29', '2025-06-19 19:17:29', '2025-06-19 19:17:29');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indices de la tabla `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categorias_descripcion_unique` (`descripcion`);

--
-- Indices de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indices de la tabla `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indices de la tabla `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indices de la tabla `procedimientos`
--
ALTER TABLE `procedimientos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_numero_documento_unique` (`numero_documento`);

--
-- Indices de la tabla `user_logins`
--
ALTER TABLE `user_logins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_logins_user_id_attempted_at_index` (`user_id`,`attempted_at`),
  ADD KEY `user_logins_email_attempted_at_index` (`email`,`attempted_at`),
  ADD KEY `user_logins_status_attempted_at_index` (`status`,`attempted_at`),
  ADD KEY `user_logins_ip_address_index` (`ip_address`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `procedimientos`
--
ALTER TABLE `procedimientos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT de la tabla `user_logins`
--
ALTER TABLE `user_logins`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=160;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `user_logins`
--
ALTER TABLE `user_logins`
  ADD CONSTRAINT `user_logins_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
