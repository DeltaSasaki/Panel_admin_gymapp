-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 17-07-2026 a las 03:22:51
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
-- Base de datos: `teleredt_gym_prueba`
--
CREATE DATABASE IF NOT EXISTS `teleredt_gym_prueba` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `teleredt_gym_prueba`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `body_measurements`
--

CREATE TABLE `body_measurements` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `weight_kg` decimal(5,2) NOT NULL,
  `height_cm` decimal(5,2) NOT NULL,
  `bmi` decimal(4,2) DEFAULT NULL,
  `bmi_category` enum('underweight','normal','overweight','obese') NOT NULL,
  `body_fat_pct` decimal(4,2) DEFAULT NULL,
  `muscle_mass_kg` decimal(5,2) DEFAULT NULL,
  `waist_cm` decimal(5,2) DEFAULT NULL,
  `hip_cm` decimal(5,2) DEFAULT NULL,
  `measured_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `createdAt` datetime NOT NULL,
  `updatedAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `body_measurements`
--

INSERT INTO `body_measurements` (`id`, `user_id`, `weight_kg`, `height_cm`, `bmi`, `bmi_category`, `body_fat_pct`, `muscle_mass_kg`, `waist_cm`, `hip_cm`, `measured_at`, `notes`, `createdAt`, `updatedAt`) VALUES
(1, 4, 64.00, 165.00, 23.51, 'normal', NULL, NULL, NULL, NULL, '2026-07-10 00:39:16', NULL, '2026-07-17 00:39:16', '2026-07-17 00:39:16'),
(2, 5, 85.00, 178.00, 26.83, 'overweight', NULL, NULL, NULL, NULL, '2026-07-10 00:39:16', NULL, '2026-07-17 00:39:16', '2026-07-17 00:39:16'),
(3, 10, 58.00, 168.00, 20.55, 'normal', NULL, NULL, NULL, NULL, '2026-07-10 00:39:17', NULL, '2026-07-17 00:39:17', '2026-07-17 00:39:17'),
(4, 11, 78.50, 176.00, 25.34, 'overweight', NULL, NULL, NULL, NULL, '2026-07-16 00:39:17', NULL, '2026-07-17 00:39:17', '2026-07-17 00:39:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Estructura de tabla para la tabla `equipment`
--

CREATE TABLE `equipment` (
  `id` int(11) NOT NULL,
  `gym_id` int(11) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `requires_gym` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `equipment`
--

INSERT INTO `equipment` (`id`, `gym_id`, `name`, `description`, `image_url`, `requires_gym`) VALUES
(1, 5, 'Cinta de Correr Pro Series', 'Cinta de correr motorizada Matrix Pro', NULL, 1),
(2, 5, 'Prensa de Piernas 45° Matrix', 'Prensa de piernas Matrix 45 grados', NULL, 1),
(3, 5, 'Rack de Sentadillas Smith', 'Soporte rack Smith multipower', NULL, 1),
(4, 6, 'Bicicleta de Spinning Matrix', 'Bicicleta estática indoor', NULL, 1),
(5, 6, 'Banco de Pecho Plano Matrix', 'Banco plano ajustable musculación', NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `exercises`
--

CREATE TABLE `exercises` (
  `id` int(11) NOT NULL,
  `gym_id` int(11) DEFAULT NULL,
  `is_global` tinyint(1) DEFAULT 0,
  `category_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  `muscle_group` varchar(150) DEFAULT NULL,
  `difficulty` enum('beginner','intermediate','advanced') DEFAULT 'beginner',
  `requires_equipment` tinyint(1) DEFAULT 0,
  `video_url` varchar(500) DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `calories_per_min` decimal(5,2) DEFAULT NULL,
  `createdAt` datetime NOT NULL,
  `updatedAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `exercises`
--

INSERT INTO `exercises` (`id`, `gym_id`, `is_global`, `category_id`, `name`, `description`, `instructions`, `muscle_group`, `difficulty`, `requires_equipment`, `video_url`, `image_url`, `calories_per_min`, `createdAt`, `updatedAt`) VALUES
(1, 5, 0, 1, 'Sentadilla con Barra Trasera', 'Sentadilla clásica.', NULL, 'Cuádriceps', 'intermediate', 1, NULL, NULL, NULL, '2026-07-17 00:39:16', '2026-07-17 00:39:16'),
(2, 5, 0, 1, 'Prensa de Piernas 45°', 'Prensa pesada.', NULL, 'Cuádriceps', 'beginner', 1, NULL, NULL, NULL, '2026-07-17 00:39:16', '2026-07-17 00:39:16'),
(3, 6, 0, 2, 'Sentadilla Goblet', 'Sentadilla con mancuerna al pecho.', NULL, 'Cuádriceps', 'beginner', 1, NULL, NULL, NULL, '2026-07-17 00:39:17', '2026-07-17 00:39:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `exercise_categories`
--

CREATE TABLE `exercise_categories` (
  `id` int(11) NOT NULL,
  `gym_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon_url` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `exercise_categories`
--

INSERT INTO `exercise_categories` (`id`, `gym_id`, `name`, `description`, `icon_url`) VALUES
(1, 5, 'Fuerza & Musculación G1', 'Ejercicios con pesas y barras libres de GymFlow.', NULL),
(2, 6, 'Acondicionamiento G2', 'Ejercicios corporales y funcionales de PowerHouse.', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `exercise_equipment`
--

CREATE TABLE `exercise_equipment` (
  `exercise_id` int(11) NOT NULL,
  `equipment_id` int(11) NOT NULL,
  `is_optional` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Estructura de tabla para la tabla `gyms`
--

CREATE TABLE `gyms` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `slug` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `logo_url` varchar(500) DEFAULT NULL,
  `primary_color` varchar(7) DEFAULT '#000000',
  `secondary_color` varchar(7) DEFAULT '#FFFFFF',
  `timezone` varchar(80) DEFAULT 'America/Caracas',
  `is_active` tinyint(1) DEFAULT 1,
  `createdAt` datetime NOT NULL,
  `updatedAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `gyms`
--

INSERT INTO `gyms` (`id`, `name`, `slug`, `address`, `phone`, `email`, `logo_url`, `primary_color`, `secondary_color`, `timezone`, `is_active`, `createdAt`, `updatedAt`) VALUES
(1, 'GymFlow HQ', NULL, 'Av. de los Deportes 450, Madrid', '+34 912 345 678', 'info@gymflowhq.com', NULL, '#000000', '#FFFFFF', 'Europe/Madrid', 1, '2026-07-17 00:33:15', '2026-07-17 01:21:46'),
(2, 'PowerHouse Studio', NULL, 'Calle Gran Vía 88, Barcelona', '+34 931 999 888', 'contact@powerhousestudio.com', NULL, '#000000', '#FFFFFF', 'Europe/Madrid', 1, '2026-07-17 00:33:15', '2026-07-17 00:33:15'),
(3, 'GymFlow HQ 2', NULL, 'Av. de los Deportes 450, Madrid', '+34 912 345 678', 'info@gymflowhq.com', NULL, '#000000', '#FFFFFF', 'Europe/Madrid', 1, '2026-07-17 00:38:29', '2026-07-17 00:38:29'),
(4, 'PowerHouse Studio 2', NULL, 'Calle Gran Vía 88, Barcelona', '+34 931 999 888', 'contact@powerhousestudio.com', NULL, '#000000', '#FFFFFF', 'Europe/Madrid', 1, '2026-07-17 00:38:29', '2026-07-17 00:38:29'),
(5, 'GymFlow HQ 3', NULL, 'Av. de los Deportes 450, Madrid', '+34 912 345 678', 'info@gymflowhq.com', NULL, '#000000', '#FFFFFF', 'Europe/Madrid', 0, '2026-07-17 00:39:15', '2026-07-17 01:08:22'),
(6, 'PowerHouse Studio 3', NULL, 'Calle Gran Vía 88, Barcelona', '+34 931 999 888', 'contact@powerhousestudio.com', NULL, '#000000', '#FFFFFF', 'Europe/Madrid', 1, '2026-07-17 00:39:15', '2026-07-17 00:39:15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ingredients`
--

CREATE TABLE `ingredients` (
  `id` int(11) NOT NULL,
  `gym_id` int(11) DEFAULT NULL,
  `is_global` tinyint(1) DEFAULT 1,
  `name` varchar(150) NOT NULL,
  `unit` varchar(30) DEFAULT NULL,
  `calories_per_100g` decimal(7,2) DEFAULT NULL,
  `protein_g` decimal(6,2) DEFAULT NULL,
  `carbs_g` decimal(6,2) DEFAULT NULL,
  `fat_g` decimal(6,2) DEFAULT NULL,
  `fiber_g` decimal(6,2) DEFAULT NULL,
  `createdAt` datetime NOT NULL,
  `updatedAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `ingredients`
--

INSERT INTO `ingredients` (`id`, `gym_id`, `is_global`, `name`, `unit`, `calories_per_100g`, `protein_g`, `carbs_g`, `fat_g`, `fiber_g`, `createdAt`, `updatedAt`) VALUES
(1, 5, 1, 'Pechuga de Pollo', 'g', 120.00, 23.00, 0.00, 2.50, NULL, '2026-07-17 00:39:17', '2026-07-17 00:39:17'),
(2, 5, 1, 'Arroz Blanco Cocido', 'g', 130.00, 2.70, 28.00, 0.30, NULL, '2026-07-17 00:39:17', '2026-07-17 00:39:17'),
(3, 5, 1, 'Huevo Entero', 'unit', 70.00, 6.00, 0.60, 5.00, NULL, '2026-07-17 00:39:17', '2026-07-17 00:39:17'),
(4, 6, 1, 'Atún en lata al natural', 'unit', 110.00, 25.00, 0.00, 1.00, NULL, '2026-07-17 00:39:17', '2026-07-17 00:39:17'),
(5, 6, 1, 'Aguacate Maduro', 'unit', 160.00, 2.00, 9.00, 15.00, NULL, '2026-07-17 00:39:17', '2026-07-17 00:39:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventory_movements`
--

CREATE TABLE `inventory_movements` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `movement_type` enum('in','out','adjustment') NOT NULL,
  `quantity` int(11) NOT NULL,
  `previous_stock` int(11) DEFAULT NULL,
  `new_stock` int(11) DEFAULT NULL,
  `reason` varchar(200) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `performed_by` int(11) NOT NULL,
  `createdAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `inventory_movements`
--

INSERT INTO `inventory_movements` (`id`, `product_id`, `movement_type`, `quantity`, `previous_stock`, `new_stock`, `reason`, `reference_id`, `performed_by`, `createdAt`) VALUES
(1, 3, 'out', 1, NULL, NULL, 'Venta POS #Socio ID 4', NULL, 1, '2026-07-17 00:43:01'),
(2, 1, 'out', 1, NULL, NULL, 'Venta POS #Socio ID 5', NULL, 1, '2026-07-17 00:43:07');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventory_products`
--

CREATE TABLE `inventory_products` (
  `id` int(11) NOT NULL,
  `gym_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `sku` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `cost_price` decimal(10,2) DEFAULT NULL,
  `currency` varchar(10) DEFAULT 'USD',
  `stock_quantity` int(11) DEFAULT 0,
  `min_stock` int(11) DEFAULT 5,
  `unit` varchar(30) DEFAULT 'unidad',
  `image_url` varchar(500) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `is_food` tinyint(1) DEFAULT 0,
  `createdAt` datetime NOT NULL,
  `updatedAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `inventory_products`
--

INSERT INTO `inventory_products` (`id`, `gym_id`, `category_id`, `name`, `description`, `sku`, `price`, `cost_price`, `currency`, `stock_quantity`, `min_stock`, `unit`, `image_url`, `is_available`, `is_food`, `createdAt`, `updatedAt`) VALUES
(1, 5, 1, 'Vaso Mezclador 500ml', 'Shaker clásico hermético', NULL, 10.00, 4.00, 'USD', 14, 3, 'unidad', NULL, 1, 0, '2026-07-17 00:39:17', '2026-07-17 00:43:07'),
(2, 5, 2, 'Whey Protein 1kg (Fresa)', 'Concentrado de suero de leche de alta calidad', NULL, 45.00, 28.00, 'USD', 8, 2, 'unidad', NULL, 1, 0, '2026-07-17 00:39:17', '2026-07-17 00:39:17'),
(3, 5, 2, 'Barra de Proteínas 60g', 'Aperitivo con 20g de proteína', NULL, 3.50, 1.50, 'USD', 1, 5, 'unidad', NULL, 1, 0, '2026-07-17 00:39:17', '2026-07-17 00:43:01'),
(4, 6, 4, 'Iso Protein 900g (Vainilla)', 'Proteína aislada premium', NULL, 55.00, 35.00, 'USD', 12, 3, 'unidad', NULL, 1, 0, '2026-07-17 00:39:17', '2026-07-17 00:39:17');

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
-- Estructura de tabla para la tabla `meal_plans`
--

CREATE TABLE `meal_plans` (
  `id` int(11) NOT NULL,
  `gym_id` int(11) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `goal_type` enum('lose_weight','gain_muscle','gain_weight','maintain','improve_endurance','general') DEFAULT 'general',
  `bmi_category` enum('underweight','normal','overweight','obese','all') DEFAULT 'all',
  `duration_weeks` int(11) DEFAULT 4,
  `daily_calories` decimal(7,2) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `createdAt` datetime NOT NULL,
  `updatedAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `meal_plans`
--

INSERT INTO `meal_plans` (`id`, `gym_id`, `name`, `description`, `goal_type`, `bmi_category`, `duration_weeks`, `daily_calories`, `is_active`, `createdAt`, `updatedAt`) VALUES
(1, 5, 'Volumen G1 2500 kcal', 'Plan hipercalórico base para ganar masa muscular.', 'gain_muscle', 'all', 12, 2500.00, 1, '2026-07-17 00:39:16', '2026-07-17 00:39:16'),
(2, 6, 'Keto Adaptada G2 2000 kcal', 'Plan nutricional bajo en carbohidratos.', 'lose_weight', 'all', 8, 2000.00, 1, '2026-07-17 00:39:17', '2026-07-17 00:39:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `meal_plan_days`
--

CREATE TABLE `meal_plan_days` (
  `id` int(11) NOT NULL,
  `meal_plan_id` int(11) NOT NULL,
  `day_number` int(11) NOT NULL,
  `breakfast_recipe_id` int(11) DEFAULT NULL,
  `snack1_recipe_id` int(11) DEFAULT NULL,
  `lunch_recipe_id` int(11) DEFAULT NULL,
  `snack2_recipe_id` int(11) DEFAULT NULL,
  `dinner_recipe_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `meal_plan_days`
--

INSERT INTO `meal_plan_days` (`id`, `meal_plan_id`, `day_number`, `breakfast_recipe_id`, `snack1_recipe_id`, `lunch_recipe_id`, `snack2_recipe_id`, `dinner_recipe_id`) VALUES
(1, 1, 1, 7, NULL, 8, NULL, NULL),
(2, 1, 2, 7, NULL, 8, NULL, NULL),
(3, 2, 1, 9, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `membership_payments`
--

CREATE TABLE `membership_payments` (
  `id` int(11) NOT NULL,
  `membership_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'USD',
  `payment_method` enum('cash','card','transfer','other') DEFAULT 'cash',
  `payment_date` datetime DEFAULT NULL,
  `reference_code` varchar(100) DEFAULT NULL,
  `received_by` int(11) DEFAULT NULL,
  `receipt_url` varchar(500) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `createdAt` datetime NOT NULL,
  `updatedAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `membership_plans`
--

CREATE TABLE `membership_plans` (
  `id` int(11) NOT NULL,
  `gym_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `duration_days` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'USD',
  `includes_trainer` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `createdAt` datetime NOT NULL,
  `updatedAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `membership_plans`
--

INSERT INTO `membership_plans` (`id`, `gym_id`, `name`, `description`, `duration_days`, `price`, `currency`, `includes_trainer`, `is_active`, `createdAt`, `updatedAt`) VALUES
(1, 5, 'Plan VIP Mensual', 'Acceso libre y entrenador personalizado.', 30, 50.00, 'USD', 1, 1, '2026-07-17 00:39:17', '2026-07-17 00:39:17'),
(2, 5, 'Plan Básico Mensual', 'Acceso libre a máquinas de musculación.', 30, 30.00, 'USD', 0, 1, '2026-07-17 00:39:17', '2026-07-17 00:39:17'),
(3, 6, 'Power Pass Mensual', 'Pase completo con clases dirigidas.', 30, 60.00, 'USD', 1, 1, '2026-07-17 00:39:17', '2026-07-17 00:39:17');

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
(3, '0001_01_01_000002_create_jobs_table', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `body` text DEFAULT NULL,
  `type` enum('membership_expiry','payment_reminder','new_routine','achievement','general') DEFAULT 'general',
  `is_read` tinyint(1) DEFAULT 0,
  `createdAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Estructura de tabla para la tabla `product_categories`
--

CREATE TABLE `product_categories` (
  `id` int(11) NOT NULL,
  `gym_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon_url` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `product_categories`
--

INSERT INTO `product_categories` (`id`, `gym_id`, `name`, `description`, `icon_url`) VALUES
(1, 5, 'Accesorios', 'Shakers, straps y vendas', NULL),
(2, 5, 'Suplementos', 'Proteínas y creatinas', NULL),
(3, 5, 'Bebidas', 'Agua e hidratantes', NULL),
(4, 6, 'Suplementación G2', 'Suplementos deportivos', NULL),
(5, 6, 'Bebidas G2', 'Hidratantes y energizantes', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `product_sales`
--

CREATE TABLE `product_sales` (
  `id` int(11) NOT NULL,
  `gym_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `sold_by` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','card','transfer','other') DEFAULT 'cash',
  `sale_date` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `createdAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `product_sales`
--

INSERT INTO `product_sales` (`id`, `gym_id`, `user_id`, `sold_by`, `total_amount`, `payment_method`, `sale_date`, `notes`, `createdAt`) VALUES
(1, 5, 4, 1, 3.50, 'cash', NULL, '10', '2026-07-17 00:43:01'),
(2, 5, 5, 1, 10.00, 'transfer', NULL, '10', '2026-07-17 00:43:07');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recipes`
--

CREATE TABLE `recipes` (
  `id` int(11) NOT NULL,
  `gym_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  `preparation_min` int(11) DEFAULT NULL,
  `goal_type` enum('lose_weight','gain_muscle','gain_weight','maintain','improve_endurance','general') DEFAULT 'general',
  `bmi_category` enum('underweight','normal','overweight','obese','all') DEFAULT 'all',
  `calories_total` decimal(7,2) DEFAULT NULL,
  `protein_g` decimal(6,2) DEFAULT NULL,
  `carbs_g` decimal(6,2) DEFAULT NULL,
  `fat_g` decimal(6,2) DEFAULT NULL,
  `servings` int(11) DEFAULT 1,
  `image_url` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `createdAt` datetime NOT NULL,
  `updatedAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `recipes`
--

INSERT INTO `recipes` (`id`, `gym_id`, `category_id`, `name`, `description`, `instructions`, `preparation_min`, `goal_type`, `bmi_category`, `calories_total`, `protein_g`, `carbs_g`, `fat_g`, `servings`, `image_url`, `is_active`, `createdAt`, `updatedAt`) VALUES
(1, 1, 1, 'Tortilla de Avena y Claras de Huevo', 'Desayuno pre-entrenamiento de absorción limpia.', '1. Mezclar claras con avena. 2. Cocinar a fuego lento.', 10, 'gain_muscle', 'all', 450.00, 30.00, 55.00, 10.00, 1, NULL, 1, '2026-07-17 00:33:15', '2026-07-17 00:33:15'),
(2, 1, 1, 'Pollo con Arroz Jazmín', 'El almuerzo de los campeones.', 'Cocinar 150g de pechuga con 80g de arroz.', 20, 'gain_muscle', 'all', 600.00, 45.00, 70.00, 8.00, 1, NULL, 1, '2026-07-17 00:33:15', '2026-07-17 00:33:15'),
(3, 2, 2, 'Ensalada de Atún Proteica', 'Comida baja en carbos y rica en grasas saludables.', 'Mezclar atún con aguacate y rúcula.', 10, 'lose_weight', 'all', 380.00, 35.00, 10.00, 22.00, 1, NULL, 1, '2026-07-17 00:33:16', '2026-07-17 00:33:16'),
(4, 3, 3, 'Tortilla de Avena y Claras de Huevo', 'Desayuno pre-entrenamiento de absorción limpia.', '1. Mezclar claras con avena. 2. Cocinar a fuego lento.', 10, 'gain_muscle', 'all', 450.00, 30.00, 55.00, 10.00, 1, NULL, 1, '2026-07-17 00:38:30', '2026-07-17 00:38:30'),
(5, 3, 3, 'Pollo con Arroz Jazmín', 'El almuerzo de los campeones.', 'Cocinar 150g de pechuga con 80g de arroz.', 20, 'gain_muscle', 'all', 600.00, 45.00, 70.00, 8.00, 1, NULL, 1, '2026-07-17 00:38:30', '2026-07-17 00:38:30'),
(6, 4, 4, 'Ensalada de Atún Proteica', 'Comida baja en carbos y rica en grasas saludables.', 'Mezclar atún con aguacate y rúcula.', 10, 'lose_weight', 'all', 380.00, 35.00, 10.00, 22.00, 1, NULL, 1, '2026-07-17 00:38:31', '2026-07-17 00:38:31'),
(7, 5, 5, 'Tortilla de Avena y Claras de Huevo', 'Desayuno pre-entrenamiento de absorción limpia.', '1. Mezclar claras con avena. 2. Cocinar a fuego lento.', 10, 'gain_muscle', 'all', 450.00, 30.00, 55.00, 10.00, 1, NULL, 1, '2026-07-17 00:39:16', '2026-07-17 00:39:16'),
(8, 5, 5, 'Pollo con Arroz Jazmín', 'El almuerzo de los campeones.', 'Cocinar 150g de pechuga con 80g de arroz.', 20, 'gain_muscle', 'all', 600.00, 45.00, 70.00, 8.00, 1, NULL, 1, '2026-07-17 00:39:16', '2026-07-17 00:39:16'),
(9, 6, 6, 'Ensalada de Atún Proteica', 'Comida baja en carbos y rica en grasas saludables.', 'Mezclar atún con aguacate y rúcula.', 10, 'lose_weight', 'all', 380.00, 35.00, 10.00, 22.00, 1, NULL, 1, '2026-07-17 00:39:17', '2026-07-17 00:39:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recipe_categories`
--

CREATE TABLE `recipe_categories` (
  `id` int(11) NOT NULL,
  `gym_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `icon_url` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `recipe_categories`
--

INSERT INTO `recipe_categories` (`id`, `gym_id`, `name`, `icon_url`) VALUES
(1, 1, 'Nutrición Deportiva G1', NULL),
(2, 2, 'Alimentación Saludable G2', NULL),
(3, 3, 'Nutrición Deportiva G1', NULL),
(4, 4, 'Alimentación Saludable G2', NULL),
(5, 5, 'Nutrición Deportiva G1', NULL),
(6, 6, 'Alimentación Saludable G2', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recipe_ingredients`
--

CREATE TABLE `recipe_ingredients` (
  `id` int(11) NOT NULL,
  `recipe_id` int(11) NOT NULL,
  `ingredient_id` int(11) NOT NULL,
  `quantity` decimal(8,2) NOT NULL,
  `unit` varchar(30) DEFAULT NULL,
  `notes` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `routine_days`
--

CREATE TABLE `routine_days` (
  `id` int(11) NOT NULL,
  `routine_id` int(11) NOT NULL,
  `day_number` int(11) NOT NULL,
  `day_name` varchar(50) DEFAULT NULL,
  `focus_area` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `routine_days`
--

INSERT INTO `routine_days` (`id`, `routine_id`, `day_number`, `day_name`, `focus_area`) VALUES
(1, 1, 1, 'Día 1: Fuerza Cuádriceps', 'Piernas'),
(2, 1, 2, 'Día 2: Auxiliares de Pierna', 'Isquiotibiales'),
(3, 2, 1, 'Día 1: Fuerza Corporal', 'Piernas');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `routine_exercises`
--

CREATE TABLE `routine_exercises` (
  `id` int(11) NOT NULL,
  `routine_day_id` int(11) NOT NULL,
  `exercise_id` int(11) NOT NULL,
  `sets` int(11) DEFAULT 3,
  `reps` varchar(20) DEFAULT NULL,
  `rest_seconds` int(11) DEFAULT 60,
  `duration_seconds` int(11) DEFAULT NULL,
  `order_index` int(11) DEFAULT 0,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `routine_exercises`
--

INSERT INTO `routine_exercises` (`id`, `routine_day_id`, `exercise_id`, `sets`, `reps`, `rest_seconds`, `duration_seconds`, `order_index`, `notes`) VALUES
(1, 1, 1, 4, '6-8', 120, NULL, 1, NULL),
(2, 2, 2, 3, '10-12', 90, NULL, 1, NULL),
(3, 3, 3, 3, '12', 60, NULL, 1, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sale_items`
--

CREATE TABLE `sale_items` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `sale_items`
--

INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `unit_price`, `subtotal`) VALUES
(1, 1, 3, 1, 3.50, 3.50),
(2, 2, 1, 1, 10.00, 10.00);

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
('YSTbuUFGR62lvPAWNYY9advAEjLfH8siYerAwQ8T', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiS2IwdHBFRE5BV1FZSHVoMUZzYnRjbnJqTThJaHl6Sk9NMDdteEQ5cyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fX0=', 1784251308);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `session_exercises`
--

CREATE TABLE `session_exercises` (
  `id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `exercise_id` int(11) NOT NULL,
  `sets_completed` int(11) DEFAULT NULL,
  `reps_completed` varchar(50) DEFAULT NULL,
  `weight_kg` decimal(6,2) DEFAULT NULL,
  `duration_seconds` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trainers`
--

CREATE TABLE `trainers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `gym_id` int(11) NOT NULL,
  `first_name` varchar(80) NOT NULL,
  `last_name` varchar(80) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `specialty` varchar(200) DEFAULT NULL,
  `certification` varchar(200) DEFAULT NULL,
  `experience_years` int(11) DEFAULT 0,
  `photo_url` varchar(500) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `hire_date` date DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `createdAt` datetime NOT NULL,
  `updatedAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `trainers`
--

INSERT INTO `trainers` (`id`, `user_id`, `gym_id`, `first_name`, `last_name`, `email`, `phone`, `specialty`, `certification`, `experience_years`, `photo_url`, `bio`, `is_active`, `hire_date`, `salary`, `createdAt`, `updatedAt`) VALUES
(1, 1, 5, 'Carlos', 'Ruiz', 'coach@gymflow.com', '+34 600 111 222', 'Entrenamiento de Fuerza e Hipertrofia', 'NSCA-CPT, Precision Nutrition L1', 8, 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop', 'Apasionado por la fuerza y el acondicionamiento metabólico.', 1, '2022-01-10', 2500.00, '2026-07-17 00:39:15', '2026-07-17 00:59:29'),
(2, 9, 6, 'Laura', 'Blanco', 'coach2@powerhouse.com', '+34 611 222 333', 'Entrenamiento de Resistencia y Funcional', 'FIBO-CPT, Kettlebell Trainer L2', 5, 'https://images.unsplash.com/photo-1548690312-e3b507d8c110?q=80&w=100&auto=format&fit=crop', 'Comprometida con la salud y resistencia de larga duración.', 1, '2023-03-15', 2200.00, '2026-07-17 00:39:17', '2026-07-17 00:39:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `gym_id` int(11) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('member','trainer','admin','superadmin') DEFAULT 'member',
  `is_active` tinyint(1) DEFAULT 1,
  `email_verified` tinyint(1) DEFAULT 0,
  `createdAt` datetime NOT NULL,
  `updatedAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `gym_id`, `email`, `password_hash`, `role`, `is_active`, `email_verified`, `createdAt`, `updatedAt`) VALUES
(1, 5, 'coach@gymflow.com', '$2y$12$O.C/uLGVazB9zc2rXAET6ucIh2mRhuClcKl1G.GmCzpJFWvhaZXjm', 'trainer', 1, 1, '2026-07-17 00:39:15', '2026-07-17 00:59:29'),
(2, 5, 'admin@gymflow.com', '$2y$12$gI1eim3iMh1i8wUkrGQQ/elxxzLnTJU/efZ0SL2WU2nNTbsUbtKF.', 'admin', 1, 1, '2026-07-17 00:39:16', '2026-07-17 00:39:16'),
(3, 5, 'support@gymflow.com', '$2y$12$xjV3yabdvKUSVVsBj0kOgOLMJzIdmAei/7fQdL4E/jWwB/QHpt63K', 'superadmin', 1, 1, '2026-07-17 00:39:16', '2026-07-17 00:39:16'),
(4, 5, 'maria@example.com', '$2y$12$XzIPRr0bowyQsDOoY6O0Qupnk3vbLP05clKdvhtlcSvD5o9.A1Ppm', 'member', 1, 1, '2026-07-17 00:39:16', '2026-07-17 00:39:16'),
(5, 5, 'juan@example.com', '$2y$12$/3IYqbkuDa39LYoS1O1rxOB1a5PJcQv.hj4CpaTUDV0zNGSgN98jG', 'member', 1, 1, '2026-07-17 00:39:16', '2026-07-17 00:39:16'),
(6, 5, 'mateo@example.com', '$2y$12$4GkTX0WJdLT8/p61ZEexMeOUTbsbihMoWcKirvwM.dZ2l1ng4QI36', 'member', 0, 1, '2026-07-17 00:39:16', '2026-07-17 00:39:16'),
(7, 6, 'admin2@powerhouse.com', '$2y$12$Q33PBdFgu1ZqKqtPJz1rquL8/dsYpmxJzbfoa0qYPGaz23Xhw0Kdi', 'admin', 1, 1, '2026-07-17 00:39:17', '2026-07-17 00:39:17'),
(8, 6, 'support2@powerhouse.com', '$2y$12$0J05NF7r4ZyU1b534ns9Uu5ht/.D.P4CGydj69mLlOHta046h0H/S', 'superadmin', 1, 1, '2026-07-17 00:39:17', '2026-07-17 00:39:17'),
(9, 6, 'coach2@powerhouse.com', '$2y$12$qTUpSSBIW7XtfmxoNck7fuhc9dKSdod3zGTdiIcnGj72/aFu5Tcq.', 'trainer', 1, 1, '2026-07-17 00:39:17', '2026-07-17 00:39:17'),
(10, 6, 'sofia@example.com', '$2y$12$zLPIGIlIpQAmW84MCIsDD.IeebeWAV1UoNbjmMHkedoTFqq8IK6NW', 'member', 1, 1, '2026-07-17 00:39:17', '2026-07-17 00:39:17'),
(11, 6, 'andres@example.com', '$2y$12$oID.JN4fwozeOIYyDFj7Red8X5lQhuanlGUFvu9JBdInuc8PRiFRq', 'member', 1, 1, '2026-07-17 00:39:17', '2026-07-17 00:39:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_achievements`
--

CREATE TABLE `user_achievements` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `achievement_type` varchar(100) NOT NULL,
  `description` varchar(300) DEFAULT NULL,
  `achieved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `user_achievements`
--

INSERT INTO `user_achievements` (`id`, `user_id`, `achievement_type`, `description`, `achieved_at`) VALUES
(1, 4, 'first_workout', 'Completaste tu primera sesión de entrenamiento.', '2026-07-03 00:39:17'),
(2, 11, '10k_calories', 'Quemaste más de 10,000 kcal en sesiones registradas.', '2026-07-15 00:39:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_assigned_routines`
--

CREATE TABLE `user_assigned_routines` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `routine_id` int(11) NOT NULL,
  `assigned_by` int(11) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `createdAt` datetime NOT NULL,
  `updatedAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `user_assigned_routines`
--

INSERT INTO `user_assigned_routines` (`id`, `user_id`, `routine_id`, `assigned_by`, `start_date`, `end_date`, `is_active`, `createdAt`, `updatedAt`) VALUES
(1, 4, 1, 1, '2026-07-03', NULL, 1, '2026-07-17 00:39:16', '2026-07-17 00:39:16'),
(2, 11, 2, 2, '2026-07-16', NULL, 1, '2026-07-17 00:39:17', '2026-07-17 00:39:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_goals`
--

CREATE TABLE `user_goals` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `goal_type` enum('lose_weight','gain_muscle','gain_weight','maintain','improve_endurance','improve_flexibility') NOT NULL,
  `target_weight` decimal(5,2) DEFAULT NULL,
  `target_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `createdAt` datetime NOT NULL,
  `updatedAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `user_goals`
--

INSERT INTO `user_goals` (`id`, `user_id`, `goal_type`, `target_weight`, `target_date`, `is_active`, `createdAt`, `updatedAt`) VALUES
(1, 4, 'lose_weight', 60.00, '2026-08-28', 1, '2026-07-17 00:39:17', '2026-07-17 00:39:17'),
(2, 11, 'gain_muscle', 82.00, '2026-09-11', 1, '2026-07-17 00:39:17', '2026-07-17 00:39:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_meal_plans`
--

CREATE TABLE `user_meal_plans` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `meal_plan_id` int(11) NOT NULL,
  `assigned_by` int(11) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `createdAt` datetime NOT NULL,
  `updatedAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `user_meal_plans`
--

INSERT INTO `user_meal_plans` (`id`, `user_id`, `meal_plan_id`, `assigned_by`, `start_date`, `end_date`, `is_active`, `createdAt`, `updatedAt`) VALUES
(1, 4, 1, 1, '2026-07-03', NULL, 1, '2026-07-17 00:39:16', '2026-07-17 00:39:16'),
(2, 10, 2, 2, '2026-07-15', NULL, 1, '2026-07-17 00:39:17', '2026-07-17 00:39:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_memberships`
--

CREATE TABLE `user_memberships` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `gym_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('active','expired','suspended','cancelled') DEFAULT 'active',
  `payment_status` enum('paid','pending','overdue') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `createdAt` datetime NOT NULL,
  `updatedAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `user_memberships`
--

INSERT INTO `user_memberships` (`id`, `user_id`, `gym_id`, `plan_id`, `start_date`, `end_date`, `status`, `payment_status`, `notes`, `createdAt`, `updatedAt`) VALUES
(1, 4, 5, 1, '2026-07-02', '2026-08-01', 'active', 'paid', 'Atleta muy disciplinada.', '2026-07-17 00:39:17', '2026-07-17 00:39:17'),
(2, 5, 5, 2, '2026-06-22', '2026-07-22', 'active', 'pending', 'Pendiente pago del mes.', '2026-07-17 00:39:17', '2026-07-17 00:39:17'),
(3, 10, 6, 3, '2026-07-12', '2026-08-11', 'active', 'paid', NULL, '2026-07-17 00:39:17', '2026-07-17 00:39:17'),
(4, 11, 6, 3, '2026-06-12', '2026-07-12', 'expired', 'overdue', 'Recordatorio de pago enviado.', '2026-07-17 00:39:17', '2026-07-17 00:39:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_profiles`
--

CREATE TABLE `user_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(80) NOT NULL,
  `last_name` varchar(80) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `profile_photo` varchar(500) DEFAULT NULL,
  `createdAt` datetime NOT NULL,
  `updatedAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `user_profiles`
--

INSERT INTO `user_profiles` (`id`, `user_id`, `first_name`, `last_name`, `phone`, `birth_date`, `gender`, `profile_photo`, `createdAt`, `updatedAt`) VALUES
(1, 1, 'Carlos', 'Ruiz', '+34 600 111 222', '1990-05-15', 'male', 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop', '2026-07-17 00:39:15', '2026-07-17 00:39:15'),
(2, 2, 'Geraldo', 'Mendoza (Owner)', '+34 600 222 333', '1985-04-12', 'male', 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?q=80&w=150&auto=format&fit=crop', '2026-07-17 00:39:16', '2026-07-17 00:39:16'),
(3, 3, 'Soporte Técnica', 'GymFlow', '+34 600 999 999', '1998-01-01', 'other', 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?q=80&w=150&auto=format&fit=crop', '2026-07-17 00:39:16', '2026-07-17 00:39:16'),
(4, 4, 'María Inés', 'Silva', '+34 655 444 333', '1995-08-20', 'female', 'https://images.unsplash.com/photo-1548690312-e3b507d8c110?q=80&w=100&auto=format&fit=crop', '2026-07-17 00:39:16', '2026-07-17 00:39:16'),
(5, 5, 'Juan Pablo', 'Torres', '+34 677 888 999', '1992-03-12', 'male', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?q=80&w=100&auto=format&fit=crop', '2026-07-17 00:39:16', '2026-07-17 00:39:16'),
(6, 6, 'Mateo', 'Mendoza', '+34 699 000 111', '1988-09-02', 'male', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=100&auto=format&fit=crop', '2026-07-17 00:39:16', '2026-07-17 00:39:16'),
(7, 7, 'Eduardo', 'Valenzuela', '+34 622 333 444', '1980-08-30', 'male', 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?q=80&w=150&auto=format&fit=crop', '2026-07-17 00:39:17', '2026-07-17 00:39:17'),
(8, 8, 'Soporte Técnica 2', 'PowerHouse', '+34 600 888 888', '1998-01-01', 'other', 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?q=80&w=150&auto=format&fit=crop', '2026-07-17 00:39:17', '2026-07-17 00:39:17'),
(9, 9, 'Laura', 'Blanco', '+34 611 222 333', '1993-10-12', 'female', 'https://images.unsplash.com/photo-1548690312-e3b507d8c110?q=80&w=100&auto=format&fit=crop', '2026-07-17 00:39:17', '2026-07-17 00:39:17'),
(10, 10, 'Sofía', 'Vergara G.', '+34 688 555 444', '1997-11-25', 'female', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?q=80&w=100&auto=format&fit=crop', '2026-07-17 00:39:17', '2026-07-17 00:39:17'),
(11, 11, 'Andrés', 'Silva', '+34 600 999 888', '1994-01-20', 'male', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=100&auto=format&fit=crop', '2026-07-17 00:39:17', '2026-07-17 00:39:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_trainer_assignments`
--

CREATE TABLE `user_trainer_assignments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `trainer_id` int(11) NOT NULL,
  `assigned_at` datetime DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `notes` text DEFAULT NULL,
  `createdAt` datetime NOT NULL,
  `updatedAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `workout_routines`
--

CREATE TABLE `workout_routines` (
  `id` int(11) NOT NULL,
  `gym_id` int(11) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `goal_type` enum('lose_weight','gain_muscle','gain_weight','maintain','improve_endurance','improve_flexibility') NOT NULL,
  `bmi_min` decimal(4,2) DEFAULT NULL,
  `bmi_max` decimal(4,2) DEFAULT NULL,
  `bmi_category` enum('underweight','normal','overweight','obese','all') DEFAULT 'all',
  `difficulty` enum('beginner','intermediate','advanced') DEFAULT 'beginner',
  `duration_weeks` int(11) DEFAULT 4,
  `days_per_week` int(11) DEFAULT 3,
  `requires_gym` tinyint(1) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `createdAt` datetime NOT NULL,
  `updatedAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `workout_routines`
--

INSERT INTO `workout_routines` (`id`, `gym_id`, `name`, `description`, `goal_type`, `bmi_min`, `bmi_max`, `bmi_category`, `difficulty`, `duration_weeks`, `days_per_week`, `requires_gym`, `is_active`, `created_by`, `createdAt`, `updatedAt`) VALUES
(1, 5, 'Pierna & Glúteo Avanzado G1', 'Plan de alta intensidad RPE.', 'gain_muscle', NULL, NULL, 'all', 'advanced', 12, 2, 1, 1, 1, '2026-07-17 00:39:16', '2026-07-17 00:39:16'),
(2, 6, 'Hipertrofia Funcional G2', 'Plan de desarrollo muscular integral.', 'gain_muscle', NULL, NULL, 'all', 'intermediate', 8, 1, 1, 1, 2, '2026-07-17 00:39:17', '2026-07-17 00:39:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `workout_sessions`
--

CREATE TABLE `workout_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `routine_id` int(11) DEFAULT NULL,
  `routine_day_id` int(11) DEFAULT NULL,
  `session_date` date NOT NULL,
  `started_at` datetime DEFAULT NULL,
  `ended_at` datetime DEFAULT NULL,
  `duration_minutes` int(11) DEFAULT NULL,
  `calories_burned` decimal(7,2) DEFAULT NULL,
  `feeling` enum('bad','okay','good','great') DEFAULT 'good',
  `notes` text DEFAULT NULL,
  `createdAt` datetime NOT NULL,
  `updatedAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `workout_sessions`
--

INSERT INTO `workout_sessions` (`id`, `user_id`, `routine_id`, `routine_day_id`, `session_date`, `started_at`, `ended_at`, `duration_minutes`, `calories_burned`, `feeling`, `notes`, `createdAt`, `updatedAt`) VALUES
(1, 4, 1, NULL, '2026-07-13', '2026-07-13 08:00:00', '2026-07-13 09:00:00', 60, 400.00, 'good', NULL, '2026-07-17 00:39:17', '2026-07-17 00:39:17'),
(2, 4, 1, NULL, '2026-07-13', '2026-07-13 09:00:00', '2026-07-13 10:00:00', 60, 400.00, 'good', NULL, '2026-07-17 00:39:17', '2026-07-17 00:39:17'),
(3, 4, 1, NULL, '2026-07-13', '2026-07-13 10:00:00', '2026-07-13 11:00:00', 60, 400.00, 'good', NULL, '2026-07-17 00:39:17', '2026-07-17 00:39:17'),
(4, 4, 1, NULL, '2026-07-13', '2026-07-13 11:00:00', '2026-07-13 12:00:00', 60, 400.00, 'good', NULL, '2026-07-17 00:39:17', '2026-07-17 00:39:17'),
(5, 4, 1, NULL, '2026-07-13', '2026-07-13 12:00:00', '2026-07-13 13:00:00', 60, 400.00, 'good', NULL, '2026-07-17 00:39:17', '2026-07-17 00:39:17'),
(6, 4, 1, NULL, '2026-07-13', '2026-07-13 13:00:00', '2026-07-13 14:00:00', 60, 400.00, 'good', NULL, '2026-07-17 00:39:17', '2026-07-17 00:39:17'),
(7, 11, 2, NULL, '2026-07-14', '2026-07-14 09:00:00', '2026-07-14 10:00:00', 50, 350.00, 'good', NULL, '2026-07-17 00:39:17', '2026-07-17 00:39:17'),
(8, 11, 2, NULL, '2026-07-14', '2026-07-14 10:00:00', '2026-07-14 11:00:00', 50, 350.00, 'good', NULL, '2026-07-17 00:39:17', '2026-07-17 00:39:17'),
(9, 11, 2, NULL, '2026-07-14', '2026-07-14 11:00:00', '2026-07-14 12:00:00', 50, 350.00, 'good', NULL, '2026-07-17 00:39:17', '2026-07-17 00:39:17'),
(10, 11, 2, NULL, '2026-07-14', '2026-07-14 12:00:00', '2026-07-14 13:00:00', 50, 350.00, 'good', NULL, '2026-07-17 00:39:17', '2026-07-17 00:39:17'),
(11, 11, 2, NULL, '2026-07-14', '2026-07-14 13:00:00', '2026-07-14 14:00:00', 50, 350.00, 'good', NULL, '2026-07-17 00:39:17', '2026-07-17 00:39:17');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `body_measurements`
--
ALTER TABLE `body_measurements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indices de la tabla `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indices de la tabla `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `equipment_gym_fk` (`gym_id`);

--
-- Indices de la tabla `exercises`
--
ALTER TABLE `exercises`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `exercises_gym_fk` (`gym_id`);

--
-- Indices de la tabla `exercise_categories`
--
ALTER TABLE `exercise_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `excat_gym_fk` (`gym_id`);

--
-- Indices de la tabla `exercise_equipment`
--
ALTER TABLE `exercise_equipment`
  ADD PRIMARY KEY (`exercise_id`,`equipment_id`),
  ADD UNIQUE KEY `exercise_equipment_equipment_id_exercise_id_unique` (`exercise_id`,`equipment_id`),
  ADD KEY `equipment_id` (`equipment_id`);

--
-- Indices de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indices de la tabla `gyms`
--
ALTER TABLE `gyms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `gym_slug_unique` (`slug`);

--
-- Indices de la tabla `ingredients`
--
ALTER TABLE `ingredients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ingredients_gym_fk` (`gym_id`);

--
-- Indices de la tabla `inventory_movements`
--
ALTER TABLE `inventory_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indices de la tabla `inventory_products`
--
ALTER TABLE `inventory_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `gym_id` (`gym_id`),
  ADD KEY `category_id` (`category_id`);

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
-- Indices de la tabla `meal_plans`
--
ALTER TABLE `meal_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `meals_gym_fk` (`gym_id`);

--
-- Indices de la tabla `meal_plan_days`
--
ALTER TABLE `meal_plan_days`
  ADD PRIMARY KEY (`id`),
  ADD KEY `meal_plan_id` (`meal_plan_id`),
  ADD KEY `breakfast_recipe_id` (`breakfast_recipe_id`),
  ADD KEY `snack1_recipe_id` (`snack1_recipe_id`),
  ADD KEY `lunch_recipe_id` (`lunch_recipe_id`),
  ADD KEY `snack2_recipe_id` (`snack2_recipe_id`),
  ADD KEY `dinner_recipe_id` (`dinner_recipe_id`);

--
-- Indices de la tabla `membership_payments`
--
ALTER TABLE `membership_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `membership_id` (`membership_id`);

--
-- Indices de la tabla `membership_plans`
--
ALTER TABLE `membership_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `gym_id` (`gym_id`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indices de la tabla `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `prodcat_gym_fk` (`gym_id`);

--
-- Indices de la tabla `product_sales`
--
ALTER TABLE `product_sales`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `recipes`
--
ALTER TABLE `recipes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `recipes_gym_fk` (`gym_id`);

--
-- Indices de la tabla `recipe_categories`
--
ALTER TABLE `recipe_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reccat_gym_fk` (`gym_id`);

--
-- Indices de la tabla `recipe_ingredients`
--
ALTER TABLE `recipe_ingredients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recipe_id` (`recipe_id`),
  ADD KEY `ingredient_id` (`ingredient_id`);

--
-- Indices de la tabla `routine_days`
--
ALTER TABLE `routine_days`
  ADD PRIMARY KEY (`id`),
  ADD KEY `routine_id` (`routine_id`);

--
-- Indices de la tabla `routine_exercises`
--
ALTER TABLE `routine_exercises`
  ADD PRIMARY KEY (`id`),
  ADD KEY `routine_day_id` (`routine_day_id`),
  ADD KEY `exercise_id` (`exercise_id`);

--
-- Indices de la tabla `sale_items`
--
ALTER TABLE `sale_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indices de la tabla `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indices de la tabla `session_exercises`
--
ALTER TABLE `session_exercises`
  ADD PRIMARY KEY (`id`),
  ADD KEY `session_id` (`session_id`),
  ADD KEY `exercise_id` (`exercise_id`);

--
-- Indices de la tabla `trainers`
--
ALTER TABLE `trainers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `gym_id` (`gym_id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email_gym_unique` (`email`,`gym_id`),
  ADD KEY `users_gym_fk` (`gym_id`);

--
-- Indices de la tabla `user_achievements`
--
ALTER TABLE `user_achievements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `user_assigned_routines`
--
ALTER TABLE `user_assigned_routines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `routine_id` (`routine_id`);

--
-- Indices de la tabla `user_goals`
--
ALTER TABLE `user_goals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `user_meal_plans`
--
ALTER TABLE `user_meal_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `meal_plan_id` (`meal_plan_id`);

--
-- Indices de la tabla `user_memberships`
--
ALTER TABLE `user_memberships`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `plan_id` (`plan_id`);

--
-- Indices de la tabla `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indices de la tabla `user_trainer_assignments`
--
ALTER TABLE `user_trainer_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `trainer_id` (`trainer_id`);

--
-- Indices de la tabla `workout_routines`
--
ALTER TABLE `workout_routines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `routines_gym_fk` (`gym_id`);

--
-- Indices de la tabla `workout_sessions`
--
ALTER TABLE `workout_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `routine_id` (`routine_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `body_measurements`
--
ALTER TABLE `body_measurements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `equipment`
--
ALTER TABLE `equipment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `exercises`
--
ALTER TABLE `exercises`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `exercise_categories`
--
ALTER TABLE `exercise_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `gyms`
--
ALTER TABLE `gyms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `ingredients`
--
ALTER TABLE `ingredients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `inventory_movements`
--
ALTER TABLE `inventory_movements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `inventory_products`
--
ALTER TABLE `inventory_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `meal_plans`
--
ALTER TABLE `meal_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `meal_plan_days`
--
ALTER TABLE `meal_plan_days`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `membership_payments`
--
ALTER TABLE `membership_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `membership_plans`
--
ALTER TABLE `membership_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `product_categories`
--
ALTER TABLE `product_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `product_sales`
--
ALTER TABLE `product_sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `recipes`
--
ALTER TABLE `recipes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `recipe_categories`
--
ALTER TABLE `recipe_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `recipe_ingredients`
--
ALTER TABLE `recipe_ingredients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `routine_days`
--
ALTER TABLE `routine_days`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `routine_exercises`
--
ALTER TABLE `routine_exercises`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `session_exercises`
--
ALTER TABLE `session_exercises`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `trainers`
--
ALTER TABLE `trainers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `user_achievements`
--
ALTER TABLE `user_achievements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `user_assigned_routines`
--
ALTER TABLE `user_assigned_routines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `user_goals`
--
ALTER TABLE `user_goals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `user_meal_plans`
--
ALTER TABLE `user_meal_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `user_memberships`
--
ALTER TABLE `user_memberships`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `user_profiles`
--
ALTER TABLE `user_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `user_trainer_assignments`
--
ALTER TABLE `user_trainer_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `workout_routines`
--
ALTER TABLE `workout_routines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `workout_sessions`
--
ALTER TABLE `workout_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `body_measurements`
--
ALTER TABLE `body_measurements`
  ADD CONSTRAINT `body_measurements_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `equipment`
--
ALTER TABLE `equipment`
  ADD CONSTRAINT `equipment_gym_fk` FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `exercises`
--
ALTER TABLE `exercises`
  ADD CONSTRAINT `exercises_gym_fk` FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `exercises_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `exercise_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `exercise_categories`
--
ALTER TABLE `exercise_categories`
  ADD CONSTRAINT `excat_gym_fk` FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `exercise_equipment`
--
ALTER TABLE `exercise_equipment`
  ADD CONSTRAINT `exercise_equipment_ibfk_1` FOREIGN KEY (`exercise_id`) REFERENCES `exercises` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `exercise_equipment_ibfk_2` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `ingredients`
--
ALTER TABLE `ingredients`
  ADD CONSTRAINT `ingredients_gym_fk` FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `inventory_movements`
--
ALTER TABLE `inventory_movements`
  ADD CONSTRAINT `inventory_movements_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `inventory_products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `inventory_products`
--
ALTER TABLE `inventory_products`
  ADD CONSTRAINT `inventory_products_ibfk_1` FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `inventory_products_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `meal_plans`
--
ALTER TABLE `meal_plans`
  ADD CONSTRAINT `meals_gym_fk` FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `meal_plan_days`
--
ALTER TABLE `meal_plan_days`
  ADD CONSTRAINT `meal_plan_days_ibfk_1` FOREIGN KEY (`meal_plan_id`) REFERENCES `meal_plans` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `meal_plan_days_ibfk_2` FOREIGN KEY (`breakfast_recipe_id`) REFERENCES `recipes` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `meal_plan_days_ibfk_3` FOREIGN KEY (`snack1_recipe_id`) REFERENCES `recipes` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `meal_plan_days_ibfk_4` FOREIGN KEY (`lunch_recipe_id`) REFERENCES `recipes` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `meal_plan_days_ibfk_5` FOREIGN KEY (`snack2_recipe_id`) REFERENCES `recipes` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `meal_plan_days_ibfk_6` FOREIGN KEY (`dinner_recipe_id`) REFERENCES `recipes` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `membership_payments`
--
ALTER TABLE `membership_payments`
  ADD CONSTRAINT `membership_payments_ibfk_1` FOREIGN KEY (`membership_id`) REFERENCES `user_memberships` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `membership_plans`
--
ALTER TABLE `membership_plans`
  ADD CONSTRAINT `membership_plans_ibfk_1` FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `product_categories`
--
ALTER TABLE `product_categories`
  ADD CONSTRAINT `prodcat_gym_fk` FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `recipes`
--
ALTER TABLE `recipes`
  ADD CONSTRAINT `recipes_gym_fk` FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `recipes_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `recipe_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `recipe_categories`
--
ALTER TABLE `recipe_categories`
  ADD CONSTRAINT `reccat_gym_fk` FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `recipe_ingredients`
--
ALTER TABLE `recipe_ingredients`
  ADD CONSTRAINT `recipe_ingredients_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `recipe_ingredients_ibfk_2` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `routine_days`
--
ALTER TABLE `routine_days`
  ADD CONSTRAINT `routine_days_ibfk_1` FOREIGN KEY (`routine_id`) REFERENCES `workout_routines` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `routine_exercises`
--
ALTER TABLE `routine_exercises`
  ADD CONSTRAINT `routine_exercises_ibfk_1` FOREIGN KEY (`routine_day_id`) REFERENCES `routine_days` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `routine_exercises_ibfk_2` FOREIGN KEY (`exercise_id`) REFERENCES `exercises` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `sale_items`
--
ALTER TABLE `sale_items`
  ADD CONSTRAINT `sale_items_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `product_sales` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sale_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `inventory_products` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `session_exercises`
--
ALTER TABLE `session_exercises`
  ADD CONSTRAINT `session_exercises_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `workout_sessions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `session_exercises_ibfk_2` FOREIGN KEY (`exercise_id`) REFERENCES `exercises` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `trainers`
--
ALTER TABLE `trainers`
  ADD CONSTRAINT `trainers_ibfk_1` FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_gym_fk` FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `user_achievements`
--
ALTER TABLE `user_achievements`
  ADD CONSTRAINT `user_achievements_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `user_assigned_routines`
--
ALTER TABLE `user_assigned_routines`
  ADD CONSTRAINT `user_assigned_routines_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_assigned_routines_ibfk_2` FOREIGN KEY (`routine_id`) REFERENCES `workout_routines` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `user_goals`
--
ALTER TABLE `user_goals`
  ADD CONSTRAINT `user_goals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `user_meal_plans`
--
ALTER TABLE `user_meal_plans`
  ADD CONSTRAINT `user_meal_plans_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_meal_plans_ibfk_2` FOREIGN KEY (`meal_plan_id`) REFERENCES `meal_plans` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `user_memberships`
--
ALTER TABLE `user_memberships`
  ADD CONSTRAINT `user_memberships_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_memberships_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `membership_plans` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `user_trainer_assignments`
--
ALTER TABLE `user_trainer_assignments`
  ADD CONSTRAINT `user_trainer_assignments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_trainer_assignments_ibfk_2` FOREIGN KEY (`trainer_id`) REFERENCES `trainers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `workout_routines`
--
ALTER TABLE `workout_routines`
  ADD CONSTRAINT `routines_gym_fk` FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `workout_sessions`
--
ALTER TABLE `workout_sessions`
  ADD CONSTRAINT `workout_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `workout_sessions_ibfk_2` FOREIGN KEY (`routine_id`) REFERENCES `workout_routines` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
