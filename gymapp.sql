-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 17-07-2026 a las 05:15:02
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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `achievement_definitions`
--

CREATE TABLE `achievement_definitions` (
  `id` int(11) NOT NULL,
  `gym_id` int(11) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `xp_reward` int(11) DEFAULT 0,
  `token_reward` decimal(10,2) DEFAULT 0.00 COMMENT 'Moneda virtual canjeable en el gimnasio',
  `icon_url` varchar(500) DEFAULT NULL,
  `condition_type` varchar(100) NOT NULL COMMENT 'Ej: workouts_completed, consecutive_days',
  `target_value` int(11) NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `admin_audit_logs`
--

CREATE TABLE `admin_audit_logs` (
  `id` bigint(20) NOT NULL,
  `gym_id` int(11) DEFAULT NULL COMMENT 'NULL si el cambio lo hizo un superadmin global',
  `admin_id` int(11) NOT NULL COMMENT 'Quién hizo el cambio',
  `action_type` enum('INSERT','UPDATE','DELETE','LOGIN_FAILED','EXPORT_DATA') NOT NULL,
  `table_name` varchar(100) NOT NULL COMMENT 'En qué tabla ocurrió (ej. gyms, users, payments)',
  `record_id` varchar(255) DEFAULT NULL COMMENT 'ID del registro modificado',
  `old_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Datos antes del cambio',
  `new_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Datos después del cambio',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL COMMENT 'Navegador / Dispositivo usado',
  `createdAt` datetime NOT NULL DEFAULT current_timestamp()
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `attendance_logs`
--

CREATE TABLE `attendance_logs` (
  `id` int(11) NOT NULL,
  `gym_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `check_in` datetime NOT NULL,
  `check_out` datetime DEFAULT NULL,
  `entry_method` enum('biometric','app_manual','rfid','admin') DEFAULT 'biometric',
  `status` enum('valid','flagged') DEFAULT 'valid' COMMENT 'Flagged si marcó desde la app pero lejos del gym',
  `createdAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Disparadores `attendance_logs`
--
DELIMITER $$
CREATE TRIGGER `trg_validate_attendance_membership` BEFORE INSERT ON `attendance_logs` FOR EACH ROW BEGIN
    DECLARE v_has_active_membership INT;
    
    -- Busca si el usuario tiene al menos una membresía activa y que la fecha de check-in esté dentro del rango
    SELECT COUNT(*) INTO v_has_active_membership 
    FROM user_memberships 
    WHERE user_id = NEW.user_id 
      AND gym_id = NEW.gym_id 
      AND status = 'active' 
      AND DATE(NEW.check_in) BETWEEN start_date AND end_date;
      
    -- Si el conteo es 0, el usuario no tiene acceso válido
    IF v_has_active_membership = 0 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Acceso Denegado: El usuario no tiene una membresía activa y pagada para el día de hoy.';
    END IF;
END
$$
DELIMITER ;

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
-- Disparadores `body_measurements`
--
DELIMITER $$
CREATE TRIGGER `trg_calculate_bmi_insert` BEFORE INSERT ON `body_measurements` FOR EACH ROW BEGIN
    DECLARE height_m DECIMAL(5,4);
    SET height_m = NEW.height_cm / 100;
    
    IF height_m > 0 THEN
        SET NEW.bmi = NEW.weight_kg / (height_m * height_m);
        IF NEW.bmi < 18.5 THEN SET NEW.bmi_category = 'underweight';
        ELSEIF NEW.bmi < 25.0 THEN SET NEW.bmi_category = 'normal';
        ELSEIF NEW.bmi < 30.0 THEN SET NEW.bmi_category = 'overweight';
        ELSE SET NEW.bmi_category = 'obese';
        END IF;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_calculate_bmi_update` BEFORE UPDATE ON `body_measurements` FOR EACH ROW BEGIN
    DECLARE height_m DECIMAL(5,4);
    SET height_m = NEW.height_cm / 100;
    
    IF height_m > 0 THEN
        SET NEW.bmi = NEW.weight_kg / (height_m * height_m);
        IF NEW.bmi < 18.5 THEN SET NEW.bmi_category = 'underweight';
        ELSEIF NEW.bmi < 25.0 THEN SET NEW.bmi_category = 'normal';
        ELSEIF NEW.bmi < 30.0 THEN SET NEW.bmi_category = 'overweight';
        ELSE SET NEW.bmi_category = 'obese';
        END IF;
    END IF;
END
$$
DELIMITER ;

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
-- Estructura de tabla para la tabla `challenges`
--

CREATE TABLE `challenges` (
  `id` int(11) NOT NULL,
  `gym_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `xp_reward` int(11) DEFAULT 0,
  `token_reward` decimal(10,2) DEFAULT 0.00,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `class_bookings`
--

CREATE TABLE `class_bookings` (
  `id` int(11) NOT NULL,
  `class_schedule_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` enum('booked','waitlisted','cancelled','attended','no_show') DEFAULT 'booked',
  `booked_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Disparadores `class_bookings`
--
DELIMITER $$
CREATE TRIGGER `trg_class_capacity_check` BEFORE INSERT ON `class_bookings` FOR EACH ROW BEGIN
    DECLARE v_capacity INT;
    DECLARE v_booked_count INT;
    
    -- Obtener la capacidad máxima de la clase
    SELECT gc.capacity INTO v_capacity 
    FROM class_schedules cs 
    JOIN gym_classes gc ON cs.gym_class_id = gc.id 
    WHERE cs.id = NEW.class_schedule_id;
    
    -- Contar cuántos usuarios ya están confirmados
    SELECT COUNT(*) INTO v_booked_count 
    FROM class_bookings 
    WHERE class_schedule_id = NEW.class_schedule_id AND status = 'booked';
    
    -- Si está lleno y el usuario intenta entrar como 'booked', lo pasamos a espera
    IF v_booked_count >= v_capacity AND NEW.status = 'booked' THEN
        SET NEW.status = 'waitlisted';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `class_schedules`
--

CREATE TABLE `class_schedules` (
  `id` int(11) NOT NULL,
  `gym_id` int(11) NOT NULL,
  `gym_class_id` int(11) NOT NULL,
  `trainer_id` int(11) DEFAULT NULL,
  `scheduled_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` enum('scheduled','cancelled','completed') DEFAULT 'scheduled'
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
  `requires_gym` tinyint(1) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `is_active` tinyint(1) DEFAULT 1,
  `createdAt` datetime NOT NULL,
  `updatedAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Estructura de tabla para la tabla `fitness_assessments`
--

CREATE TABLE `fitness_assessments` (
  `id` int(11) NOT NULL,
  `gym_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `trainer_id` int(11) DEFAULT NULL,
  `assessment_date` date NOT NULL,
  `posture_notes` text DEFAULT NULL,
  `flexibility_rating` enum('poor','fair','good','excellent') DEFAULT 'good',
  `cardio_rating` enum('poor','fair','good','excellent') DEFAULT 'good',
  `strength_notes` text DEFAULT NULL,
  `general_recommendations` text DEFAULT NULL,
  `next_assessment_date` date DEFAULT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gyms`
--

CREATE TABLE `gyms` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `slug` varchar(50) DEFAULT NULL,
  `current_plan_id` int(11) DEFAULT NULL,
  `subscription_status` enum('active','past_due','canceled','trialing') DEFAULT 'trialing',
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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gym_classes`
--

CREATE TABLE `gym_classes` (
  `id` int(11) NOT NULL,
  `gym_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `duration_minutes` int(11) NOT NULL DEFAULT 60,
  `capacity` int(11) NOT NULL DEFAULT 15,
  `color_code` varchar(7) DEFAULT '#3b82f6',
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gym_subscriptions`
--

CREATE TABLE `gym_subscriptions` (
  `id` int(11) NOT NULL,
  `gym_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('active','past_due','canceled','trialing') DEFAULT 'active',
  `payment_method` varchar(50) DEFAULT NULL COMMENT 'Ej: Binance, Pago Móvil, Zelle',
  `reference_code` varchar(100) DEFAULT NULL COMMENT 'Referencia del pago',
  `createdAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `is_active` tinyint(1) DEFAULT 1,
  `createdAt` datetime NOT NULL,
  `updatedAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Disparadores `inventory_movements`
--
DELIMITER $$
CREATE TRIGGER `trg_update_stock_after_movement` AFTER INSERT ON `inventory_movements` FOR EACH ROW BEGIN
    IF NEW.movement_type = 'in' THEN
        UPDATE `inventory_products` SET stock_quantity = stock_quantity + NEW.quantity WHERE id = NEW.product_id;
    ELSEIF NEW.movement_type = 'out' THEN
        UPDATE `inventory_products` SET stock_quantity = stock_quantity - NEW.quantity WHERE id = NEW.product_id;
    ELSEIF NEW.movement_type = 'adjustment' THEN
        UPDATE `inventory_products` SET stock_quantity = NEW.new_stock WHERE id = NEW.product_id;
    END IF;
END
$$
DELIMITER ;

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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `membership_payments`
--

CREATE TABLE `membership_payments` (
  `id` int(11) NOT NULL,
  `membership_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `promo_code_id` int(11) DEFAULT NULL,
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

--
-- Disparadores `membership_payments`
--
DELIMITER $$
CREATE TRIGGER `trg_increment_promo_usage` AFTER INSERT ON `membership_payments` FOR EACH ROW BEGIN
    IF NEW.promo_code_id IS NOT NULL THEN
        UPDATE `promo_codes` 
        SET current_uses = current_uses + 1 
        WHERE id = NEW.promo_code_id;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_validate_promo_before_payment` BEFORE INSERT ON `membership_payments` FOR EACH ROW BEGIN
    DECLARE v_max_uses INT;
    DECLARE v_current_uses INT;
    DECLARE v_valid_until DATETIME;
    
    IF NEW.promo_code_id IS NOT NULL THEN
        SELECT max_uses, current_uses, valid_until INTO v_max_uses, v_current_uses, v_valid_until 
        FROM promo_codes WHERE id = NEW.promo_code_id;
        
        IF v_valid_until IS NOT NULL AND v_valid_until < NOW() THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El código promocional ha expirado.';
        END IF;
        
        IF v_max_uses IS NOT NULL AND v_current_uses >= v_max_uses THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El código promocional alcanzó su límite máximo de usos.';
        END IF;
    END IF;
END
$$
DELIMITER ;

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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `product_sales`
--

CREATE TABLE `product_sales` (
  `id` int(11) NOT NULL,
  `gym_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `promo_code_id` int(11) DEFAULT NULL,
  `sold_by` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','card','transfer','other') DEFAULT 'cash',
  `sale_date` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `createdAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `promo_codes`
--

CREATE TABLE `promo_codes` (
  `id` int(11) NOT NULL,
  `gym_id` int(11) DEFAULT NULL COMMENT 'NULL si es una promoción global de tu plataforma SaaS',
  `code` varchar(50) NOT NULL,
  `discount_type` enum('percentage','fixed') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `valid_from` datetime DEFAULT NULL,
  `valid_until` datetime DEFAULT NULL,
  `max_uses` int(11) DEFAULT NULL COMMENT 'Límite total de veces que se puede usar (NULL = ilimitado)',
  `current_uses` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `saas_modules`
--

CREATE TABLE `saas_modules` (
  `id` int(11) NOT NULL,
  `code_name` varchar(50) NOT NULL COMMENT 'Ej: store, nutrition, gamification, classes',
  `display_name` varchar(100) NOT NULL COMMENT 'Ej: Tienda Virtual, Módulo de Nutrición',
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `saas_modules`
--

INSERT INTO `saas_modules` (`id`, `code_name`, `display_name`, `description`) VALUES
(1, 'core', 'Gestión Básica (Usuarios y Rutinas)', NULL),
(2, 'nutrition', 'Planes de Alimentación', NULL),
(3, 'store', 'Tienda e Inventario', NULL),
(4, 'classes', 'Clases y Reservas', NULL),
(5, 'gamification', 'Retos, XP y Referidos', NULL),
(6, 'evaluations', 'Evaluaciones Físicas y Encuestas', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `saas_plan_modules`
--

CREATE TABLE `saas_plan_modules` (
  `plan_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `saas_subscription_plans`
--

CREATE TABLE `saas_subscription_plans` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL COMMENT 'Ej: Básico, Pro, Premium',
  `description` text DEFAULT NULL,
  `monthly_price` decimal(10,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'USD',
  `max_users` int(11) DEFAULT NULL COMMENT 'Límite de clientes por gym (NULL = ilimitado)',
  `max_trainers` int(11) DEFAULT NULL COMMENT 'Límite de entrenadores por gym',
  `is_active` tinyint(1) DEFAULT 1,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Disparadores `sale_items`
--
DELIMITER $$
CREATE TRIGGER `trg_prevent_negative_stock` BEFORE INSERT ON `sale_items` FOR EACH ROW BEGIN
    DECLARE v_current_stock INT;
    
    SELECT stock_quantity INTO v_current_stock 
    FROM inventory_products 
    WHERE id = NEW.product_id;
    
    IF v_current_stock < NEW.quantity THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Error: Stock insuficiente para procesar la venta de este producto.';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_sale_creates_movement` AFTER INSERT ON `sale_items` FOR EACH ROW BEGIN
    DECLARE v_sold_by INT;
    
    SELECT sold_by INTO v_sold_by 
    FROM product_sales WHERE id = NEW.sale_id;

    INSERT INTO `inventory_movements` 
        (product_id, movement_type, quantity, reason, reference_id, performed_by, createdAt) 
    VALUES 
        (NEW.product_id, 'out', NEW.quantity, CONCAT('Venta #', NEW.sale_id), NEW.sale_id, v_sold_by, NOW());
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `satisfaction_surveys`
--

CREATE TABLE `satisfaction_surveys` (
  `id` int(11) NOT NULL,
  `gym_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL COMMENT 'NULL si la encuesta se envía como anónima',
  `survey_date` date NOT NULL,
  `rating` int(11) NOT NULL COMMENT 'Escala NPS: del 1 al 10',
  `category` enum('facilities','trainers','classes','cleanliness','general') DEFAULT 'general',
  `feedback_text` text DEFAULT NULL,
  `status` enum('resolved','pending','ignored') DEFAULT 'pending' COMMENT 'Para seguimiento si hay quejas',
  `createdAt` datetime NOT NULL DEFAULT current_timestamp()
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
  `max_clients` int(11) DEFAULT 20,
  `hire_date` date DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `createdAt` datetime NOT NULL,
  `updatedAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Disparadores `trainers`
--
DELIMITER $$
CREATE TRIGGER `trg_free_users_on_trainer_deactivation` AFTER UPDATE ON `trainers` FOR EACH ROW BEGIN
    -- Si el estatus pasa de Activo (1) a Inactivo (0)
    IF NEW.is_active = 0 AND OLD.is_active = 1 THEN
        UPDATE `user_trainer_assignments` 
        SET is_active = 0, end_date = CURRENT_DATE() 
        WHERE trainer_id = NEW.id AND is_active = 1;
    END IF;
END
$$
DELIMITER ;

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
-- Disparadores `users`
--
DELIMITER $$
CREATE TRIGGER `trg_saas_block_defaulting_gyms` BEFORE INSERT ON `users` FOR EACH ROW BEGIN
    DECLARE v_gym_status VARCHAR(20);
    
    IF NEW.gym_id IS NOT NULL THEN
        SELECT subscription_status INTO v_gym_status 
        FROM gyms 
        WHERE id = NEW.gym_id;
        
        -- Si el gimnasio no está al día, se bloquea la inserción de nuevos clientes
        IF v_gym_status IN ('past_due', 'canceled') THEN
            SIGNAL SQLSTATE '45000' 
            SET MESSAGE_TEXT = 'Error SaaS: Operación bloqueada. El gimnasio presenta un estado de suscripción inactivo o con pagos pendientes.';
        END IF;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_saas_limit_users_before_insert` BEFORE INSERT ON `users` FOR EACH ROW BEGIN
    DECLARE v_max_users INT;
    DECLARE v_current_users INT;
    
    -- Solo validamos si el usuario pertenece a un gimnasio (ignoramos superadmins globales)
    IF NEW.gym_id IS NOT NULL THEN
        -- Obtenemos el límite del plan actual del gimnasio
        SELECT sp.max_users INTO v_max_users 
        FROM gyms g 
        JOIN saas_subscription_plans sp ON g.current_plan_id = sp.id 
        WHERE g.id = NEW.gym_id;
        
        -- Si hay un límite definido (no es NULL/Ilimitado)
        IF v_max_users IS NOT NULL THEN
            SELECT COUNT(*) INTO v_current_users 
            FROM users 
            WHERE gym_id = NEW.gym_id;
            
            IF v_current_users >= v_max_users THEN
                SIGNAL SQLSTATE '45000' 
                SET MESSAGE_TEXT = 'Error SaaS: El gimnasio ha alcanzado el límite máximo de usuarios para su plan actual. Debe hacer un upgrade.';
            END IF;
        END IF;
    END IF;
END
$$
DELIMITER ;

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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_challenges`
--

CREATE TABLE `user_challenges` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `challenge_id` int(11) NOT NULL,
  `progress_value` int(11) DEFAULT 0,
  `status` enum('active','completed','failed') DEFAULT 'active',
  `completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_food_logs`
--

CREATE TABLE `user_food_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `gym_id` int(11) NOT NULL,
  `log_date` date NOT NULL,
  `meal_type` enum('breakfast','lunch','dinner','snack','other') NOT NULL,
  `recipe_id` int(11) DEFAULT NULL COMMENT 'Si comió algo del plan nutricional',
  `custom_food_name` varchar(200) DEFAULT NULL COMMENT 'Si comió algo fuera del plan',
  `calories` decimal(7,2) DEFAULT 0.00,
  `protein_g` decimal(6,2) DEFAULT 0.00,
  `carbs_g` decimal(6,2) DEFAULT 0.00,
  `fat_g` decimal(6,2) DEFAULT 0.00,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_gamification_stats`
--

CREATE TABLE `user_gamification_stats` (
  `user_id` int(11) NOT NULL,
  `gym_id` int(11) NOT NULL,
  `total_xp` int(11) DEFAULT 0,
  `current_level` int(11) DEFAULT 1,
  `token_balance` decimal(10,2) DEFAULT 0.00,
  `current_streak_days` int(11) DEFAULT 0,
  `longest_streak_days` int(11) DEFAULT 0,
  `updatedAt` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Disparadores `user_gamification_stats`
--
DELIMITER $$
CREATE TRIGGER `trg_auto_level_up` BEFORE UPDATE ON `user_gamification_stats` FOR EACH ROW BEGIN
    -- Solo calculamos si la experiencia cambió
    IF NEW.total_xp <> OLD.total_xp THEN
        -- Fórmula: Nivel base (1) + 1 nivel por cada 1000 XP
        SET NEW.current_level = FLOOR(NEW.total_xp / 1000) + 1;
    END IF;
END
$$
DELIMITER ;

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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_medical_notes`
--

CREATE TABLE `user_medical_notes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `condition_type` enum('injury','surgery','chronic','pregnancy','other') NOT NULL,
  `description` text NOT NULL,
  `restricted_muscle_groups` varchar(255) DEFAULT NULL COMMENT 'Ej: Espalda baja, Rodilla derecha',
  `cleared_by_doctor` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Si la lesión ya sanó, se pasa a 0',
  `noted_by` int(11) DEFAULT NULL COMMENT 'Usuario (trainer/admin) que lo registró',
  `createdAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Disparadores `user_memberships`
--
DELIMITER $$
CREATE TRIGGER `trg_validate_membership_dates` BEFORE INSERT ON `user_memberships` FOR EACH ROW BEGIN
    -- La fecha de finalización debe ser estrictamente posterior a la de inicio
    IF NEW.end_date <= NEW.start_date THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Error de Coherencia: La fecha de finalización de la membresía no puede ser igual o anterior a la fecha de inicio.';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_validate_membership_dates_update` BEFORE UPDATE ON `user_memberships` FOR EACH ROW BEGIN
    IF NEW.end_date <= NEW.start_date THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Error de Coherencia: La fecha de finalización de la membresía no puede ser igual o anterior a la fecha de inicio.';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_profiles`
--

CREATE TABLE `user_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(80) NOT NULL,
  `last_name` varchar(80) NOT NULL,
  `dni` varchar(20) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `profile_photo` varchar(500) DEFAULT NULL,
  `createdAt` datetime NOT NULL,
  `updatedAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_referrals`
--

CREATE TABLE `user_referrals` (
  `id` int(11) NOT NULL,
  `gym_id` int(11) NOT NULL,
  `referrer_id` int(11) NOT NULL COMMENT 'Usuario que invitó',
  `referred_id` int(11) NOT NULL COMMENT 'El amigo nuevo que se registró',
  `status` enum('pending','completed') DEFAULT 'pending' COMMENT 'Pasa a completed cuando el amigo paga su 1ra mensualidad',
  `reward_granted` tinyint(1) DEFAULT 0 COMMENT 'Para saber si ya se le dio la XP/Logro al referrer',
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `completedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Disparadores `user_referrals`
--
DELIMITER $$
CREATE TRIGGER `trg_reward_referrer_on_completion` AFTER UPDATE ON `user_referrals` FOR EACH ROW BEGIN
    -- Si el estatus cambió a completed y no se ha entregado la recompensa
    IF NEW.status = 'completed' AND OLD.status = 'pending' AND OLD.reward_granted = 0 THEN
        
        -- Insertamos o actualizamos los stats del usuario que invitó.
        -- Ajusta los valores (ej. 500 XP y 10 Tokens) según la lógica de tu negocio.
        INSERT INTO `user_gamification_stats` (user_id, gym_id, total_xp, token_balance)
        VALUES (NEW.referrer_id, NEW.gym_id, 500, 10.00)
        ON DUPLICATE KEY UPDATE 
            total_xp = total_xp + 500,
            token_balance = token_balance + 10.00;
            
        -- Nota: Para mantener la limpieza, el UPDATE a reward_granted=1 
        -- debe hacerse en la API, o usando un BEFORE UPDATE trigger adicional para evitar bucles.
    END IF;
END
$$
DELIMITER ;

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

--
-- Disparadores `user_trainer_assignments`
--
DELIMITER $$
CREATE TRIGGER `trg_check_trainer_capacity` BEFORE INSERT ON `user_trainer_assignments` FOR EACH ROW BEGIN
    DECLARE v_max_clients INT;
    DECLARE v_current_clients INT;
    
    -- Solo verificamos si la nueva asignación se está marcando como activa
    IF NEW.is_active = 1 THEN
        -- Obtener el límite del entrenador
        SELECT max_clients INTO v_max_clients 
        FROM trainers 
        WHERE id = NEW.trainer_id;
        
        -- Contar cuántos clientes activos tiene actualmente
        SELECT COUNT(*) INTO v_current_clients 
        FROM user_trainer_assignments 
        WHERE trainer_id = NEW.trainer_id AND is_active = 1;
        
        -- Si ya llegó al límite (o lo superó), bloqueamos la acción
        IF v_current_clients >= v_max_clients THEN
            SIGNAL SQLSTATE '45000' 
            SET MESSAGE_TEXT = 'Error: Asignación rechazada. El entrenador ha alcanzado su límite máximo de clientes simultáneos.';
        END IF;
    END IF;
END
$$
DELIMITER ;

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
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `achievement_definitions`
--
ALTER TABLE `achievement_definitions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `gym_id` (`gym_id`);

--
-- Indices de la tabla `admin_audit_logs`
--
ALTER TABLE `admin_audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `gym_id` (`gym_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indices de la tabla `attendance_logs`
--
ALTER TABLE `attendance_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `gym_id` (`gym_id`),
  ADD KEY `user_id` (`user_id`);

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
-- Indices de la tabla `challenges`
--
ALTER TABLE `challenges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `gym_id` (`gym_id`);

--
-- Indices de la tabla `class_bookings`
--
ALTER TABLE `class_bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_booking` (`class_schedule_id`,`user_id`),
  ADD KEY `cbook_user_fk` (`user_id`);

--
-- Indices de la tabla `class_schedules`
--
ALTER TABLE `class_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `gym_class_id` (`gym_class_id`),
  ADD KEY `trainer_id` (`trainer_id`),
  ADD KEY `csched_gym_fk` (`gym_id`);

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
-- Indices de la tabla `fitness_assessments`
--
ALTER TABLE `fitness_assessments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `gym_id` (`gym_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fitass_trainer_fk` (`trainer_id`);

--
-- Indices de la tabla `gyms`
--
ALTER TABLE `gyms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `gym_slug_unique` (`slug`),
  ADD KEY `gym_current_plan_fk` (`current_plan_id`);

--
-- Indices de la tabla `gym_classes`
--
ALTER TABLE `gym_classes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `gym_id` (`gym_id`);

--
-- Indices de la tabla `gym_subscriptions`
--
ALTER TABLE `gym_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `gym_id` (`gym_id`),
  ADD KEY `gsub_plan_fk` (`plan_id`);

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
  ADD KEY `membership_id` (`membership_id`),
  ADD KEY `mpay_promo_fk` (`promo_code_id`);

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
  ADD PRIMARY KEY (`id`),
  ADD KEY `psale_promo_fk` (`promo_code_id`);

--
-- Indices de la tabla `promo_codes`
--
ALTER TABLE `promo_codes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `gym_code_unique` (`gym_id`,`code`);

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
-- Indices de la tabla `saas_modules`
--
ALTER TABLE `saas_modules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code_name` (`code_name`);

--
-- Indices de la tabla `saas_plan_modules`
--
ALTER TABLE `saas_plan_modules`
  ADD PRIMARY KEY (`plan_id`,`module_id`),
  ADD KEY `spm_module_fk` (`module_id`);

--
-- Indices de la tabla `saas_subscription_plans`
--
ALTER TABLE `saas_subscription_plans`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `sale_items`
--
ALTER TABLE `sale_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indices de la tabla `satisfaction_surveys`
--
ALTER TABLE `satisfaction_surveys`
  ADD PRIMARY KEY (`id`),
  ADD KEY `gym_id` (`gym_id`),
  ADD KEY `surv_user_fk` (`user_id`);

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
-- Indices de la tabla `user_challenges`
--
ALTER TABLE `user_challenges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `challenge_id` (`challenge_id`);

--
-- Indices de la tabla `user_food_logs`
--
ALTER TABLE `user_food_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `recipe_id` (`recipe_id`),
  ADD KEY `ufood_gym_fk` (`gym_id`);

--
-- Indices de la tabla `user_gamification_stats`
--
ALTER TABLE `user_gamification_stats`
  ADD PRIMARY KEY (`user_id`);

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
-- Indices de la tabla `user_medical_notes`
--
ALTER TABLE `user_medical_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `med_noted_by_fk` (`noted_by`);

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
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `unique_dni` (`dni`);

--
-- Indices de la tabla `user_referrals`
--
ALTER TABLE `user_referrals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `referred_unique` (`referred_id`) COMMENT 'Una persona no puede ser referida 2 veces',
  ADD KEY `ref_gym_fk` (`gym_id`),
  ADD KEY `ref_referrer_fk` (`referrer_id`);

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
-- AUTO_INCREMENT de la tabla `achievement_definitions`
--
ALTER TABLE `achievement_definitions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `admin_audit_logs`
--
ALTER TABLE `admin_audit_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `attendance_logs`
--
ALTER TABLE `attendance_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `body_measurements`
--
ALTER TABLE `body_measurements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `challenges`
--
ALTER TABLE `challenges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `class_bookings`
--
ALTER TABLE `class_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `class_schedules`
--
ALTER TABLE `class_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT de la tabla `fitness_assessments`
--
ALTER TABLE `fitness_assessments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `gyms`
--
ALTER TABLE `gyms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `gym_classes`
--
ALTER TABLE `gym_classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `gym_subscriptions`
--
ALTER TABLE `gym_subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT de la tabla `promo_codes`
--
ALTER TABLE `promo_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT de la tabla `saas_modules`
--
ALTER TABLE `saas_modules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `saas_subscription_plans`
--
ALTER TABLE `saas_subscription_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `satisfaction_surveys`
--
ALTER TABLE `satisfaction_surveys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT de la tabla `user_challenges`
--
ALTER TABLE `user_challenges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `user_food_logs`
--
ALTER TABLE `user_food_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT de la tabla `user_medical_notes`
--
ALTER TABLE `user_medical_notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT de la tabla `user_referrals`
--
ALTER TABLE `user_referrals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
-- Filtros para la tabla `achievement_definitions`
--
ALTER TABLE `achievement_definitions`
  ADD CONSTRAINT `achiev_def_gym_fk` FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `admin_audit_logs`
--
ALTER TABLE `admin_audit_logs`
  ADD CONSTRAINT `audit_admin_fk` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `audit_gym_fk` FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `attendance_logs`
--
ALTER TABLE `attendance_logs`
  ADD CONSTRAINT `att_gym_fk` FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `att_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `body_measurements`
--
ALTER TABLE `body_measurements`
  ADD CONSTRAINT `body_measurements_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `challenges`
--
ALTER TABLE `challenges`
  ADD CONSTRAINT `challenges_gym_fk` FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `class_bookings`
--
ALTER TABLE `class_bookings`
  ADD CONSTRAINT `cbook_sched_fk` FOREIGN KEY (`class_schedule_id`) REFERENCES `class_schedules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cbook_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `class_schedules`
--
ALTER TABLE `class_schedules`
  ADD CONSTRAINT `csched_class_fk` FOREIGN KEY (`gym_class_id`) REFERENCES `gym_classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `csched_gym_fk` FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `csched_trainer_fk` FOREIGN KEY (`trainer_id`) REFERENCES `trainers` (`id`) ON DELETE SET NULL;

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
-- Filtros para la tabla `fitness_assessments`
--
ALTER TABLE `fitness_assessments`
  ADD CONSTRAINT `fitass_gym_fk` FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fitass_trainer_fk` FOREIGN KEY (`trainer_id`) REFERENCES `trainers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fitass_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `gyms`
--
ALTER TABLE `gyms`
  ADD CONSTRAINT `gym_current_plan_fk` FOREIGN KEY (`current_plan_id`) REFERENCES `saas_subscription_plans` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `gym_classes`
--
ALTER TABLE `gym_classes`
  ADD CONSTRAINT `gclass_gym_fk` FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `gym_subscriptions`
--
ALTER TABLE `gym_subscriptions`
  ADD CONSTRAINT `gsub_gym_fk` FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `gsub_plan_fk` FOREIGN KEY (`plan_id`) REFERENCES `saas_subscription_plans` (`id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `membership_payments_ibfk_1` FOREIGN KEY (`membership_id`) REFERENCES `user_memberships` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `mpay_promo_fk` FOREIGN KEY (`promo_code_id`) REFERENCES `promo_codes` (`id`) ON DELETE SET NULL;

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
-- Filtros para la tabla `product_sales`
--
ALTER TABLE `product_sales`
  ADD CONSTRAINT `psale_promo_fk` FOREIGN KEY (`promo_code_id`) REFERENCES `promo_codes` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `promo_codes`
--
ALTER TABLE `promo_codes`
  ADD CONSTRAINT `promo_gym_fk` FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE;

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
-- Filtros para la tabla `saas_plan_modules`
--
ALTER TABLE `saas_plan_modules`
  ADD CONSTRAINT `spm_module_fk` FOREIGN KEY (`module_id`) REFERENCES `saas_modules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `spm_plan_fk` FOREIGN KEY (`plan_id`) REFERENCES `saas_subscription_plans` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `sale_items`
--
ALTER TABLE `sale_items`
  ADD CONSTRAINT `sale_items_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `product_sales` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sale_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `inventory_products` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `satisfaction_surveys`
--
ALTER TABLE `satisfaction_surveys`
  ADD CONSTRAINT `surv_gym_fk` FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `surv_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

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
-- Filtros para la tabla `user_challenges`
--
ALTER TABLE `user_challenges`
  ADD CONSTRAINT `uchal_chal_fk` FOREIGN KEY (`challenge_id`) REFERENCES `challenges` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `uchal_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `user_food_logs`
--
ALTER TABLE `user_food_logs`
  ADD CONSTRAINT `ufood_gym_fk` FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ufood_recipe_fk` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ufood_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `user_gamification_stats`
--
ALTER TABLE `user_gamification_stats`
  ADD CONSTRAINT `gamif_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
-- Filtros para la tabla `user_medical_notes`
--
ALTER TABLE `user_medical_notes`
  ADD CONSTRAINT `med_noted_by_fk` FOREIGN KEY (`noted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `med_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
-- Filtros para la tabla `user_referrals`
--
ALTER TABLE `user_referrals`
  ADD CONSTRAINT `ref_gym_fk` FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ref_referred_fk` FOREIGN KEY (`referred_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ref_referrer_fk` FOREIGN KEY (`referrer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
