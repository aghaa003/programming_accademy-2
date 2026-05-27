-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 27, 2026 at 06:37 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `programming_academy`
--
CREATE DATABASE IF NOT EXISTS `programming_academy` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `programming_academy`;

-- --------------------------------------------------------

--
-- Table structure for table `academy_reviews`
--

DROP TABLE IF EXISTS `academy_reviews`;
CREATE TABLE `academy_reviews` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` tinyint(1) NOT NULL COMMENT 'التقييم من 1 إلى 5',
  `review_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELATIONSHIPS FOR TABLE `academy_reviews`:
--   `user_id`
--       `users` -> `id`
--

--
-- Dumping data for table `academy_reviews`
--

INSERT INTO `academy_reviews` (`id`, `user_id`, `rating`, `review_text`, `created_at`) VALUES
(7, 7, 4, 'hello', '2025-10-07 21:39:13'),
(13, 11, 5, 'Great platform!', '2026-03-24 23:15:06'),
(14, 8, 3, 'test2', '2026-03-25 19:42:33'),
(21, 10, 3, 'assfafssfafs', '2026-03-29 21:37:50'),
(23, 1, 5, 'hhfxsgfdfgs', '2026-04-03 00:07:55');

-- --------------------------------------------------------

--
-- Table structure for table `admin_audit_logs`
--

DROP TABLE IF EXISTS `admin_audit_logs`;
CREATE TABLE `admin_audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `target_type` varchar(80) DEFAULT NULL,
  `target_id` bigint(20) UNSIGNED DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `ip` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELATIONSHIPS FOR TABLE `admin_audit_logs`:
--

--
-- Dumping data for table `admin_audit_logs`
--

INSERT INTO `admin_audit_logs` (`id`, `admin_id`, `action`, `target_type`, `target_id`, `payload`, `ip`, `created_at`, `updated_at`) VALUES
(1, 1, 'update_platform', 'Platform', 30, '{\"name\":\"Codewars\"}', '127.0.0.1', '2026-04-01 19:27:43', '2026-04-01 19:27:43'),
(2, 1, 'grade_challenge', 'Challenge', 7, '{\"user_id\":1,\"score\":39}', '127.0.0.1', '2026-04-02 16:18:46', '2026-04-02 16:18:46'),
(3, 1, 'toggle_admin', 'User', 8, '{\"new_state\":false}', '127.0.0.1', '2026-04-02 17:28:16', '2026-04-02 17:28:16'),
(4, 1, 'toggle_admin', 'User', 8, '{\"new_state\":true}', '127.0.0.1', '2026-04-02 17:29:11', '2026-04-02 17:29:11'),
(5, 1, 'toggle_admin', 'User', 8, '{\"new_state\":false}', '127.0.0.1', '2026-04-02 17:29:35', '2026-04-02 17:29:35'),
(6, 1, 'delete_user', 'User', 12, '{\"username\":\"turkmanytest\",\"email\":\"turkmany0@gmail.com\"}', '127.0.0.1', '2026-04-02 21:32:07', '2026-04-02 21:32:07'),
(7, 1, 'grade_challenge', 'Challenge', 10, '{\"user_id\":1,\"score\":90}', '127.0.0.1', '2026-04-03 13:52:02', '2026-04-03 13:52:02'),
(8, 1, 'grade_challenge', 'Challenge', 10, '{\"user_id\":1,\"score\":10}', '127.0.0.1', '2026-04-03 13:52:23', '2026-04-03 13:52:23'),
(9, 1, 'grade_challenge', 'Challenge', 10, '{\"user_id\":1,\"score\":100}', '127.0.0.1', '2026-04-03 13:52:43', '2026-04-03 13:52:43'),
(10, 1, 'grade_challenge', 'Challenge', 10, '{\"user_id\":1,\"score\":10}', '127.0.0.1', '2026-04-03 13:52:56', '2026-04-03 13:52:56'),
(11, 1, 'grade_challenge', 'Challenge', 10, '{\"user_id\":1,\"score\":100}', '127.0.0.1', '2026-04-03 14:22:02', '2026-04-03 14:22:02'),
(12, 1, 'grade_challenge', 'Challenge', 10, '{\"user_id\":1,\"score\":10}', '127.0.0.1', '2026-04-03 14:22:17', '2026-04-03 14:22:17'),
(13, 1, 'grade_challenge', 'Challenge', 10, '{\"user_id\":1,\"score\":100}', '127.0.0.1', '2026-04-03 14:22:41', '2026-04-03 14:22:41'),
(14, 1, 'update_lesson', 'Lesson', 52, '{\"title\":\"darth-vader-the-dark-lord-star-wars-moewalls-com\"}', '127.0.0.1', '2026-04-03 14:32:23', '2026-04-03 14:32:23'),
(15, 1, 'update_course', 'Course', 22, '{\"title\":\"efwfew\"}', '127.0.0.1', '2026-04-03 15:18:53', '2026-04-03 15:18:53'),
(16, 1, 'update_course', 'Course', 22, '{\"title\":\"efwfew\"}', '127.0.0.1', '2026-04-03 15:19:19', '2026-04-03 15:19:19'),
(17, 1, 'update_course', 'Course', 22, '{\"title\":\"efwfew\"}', '127.0.0.1', '2026-04-03 15:19:47', '2026-04-03 15:19:47'),
(18, 1, 'update_course', 'Course', 22, '{\"title\":\"efwfew\"}', '127.0.0.1', '2026-04-03 15:24:18', '2026-04-03 15:24:18'),
(19, 1, 'update_course', 'Course', 22, '{\"title\":\"efwfew\"}', '127.0.0.1', '2026-04-03 15:24:52', '2026-04-03 15:24:52'),
(20, 1, 'update_course', 'Course', 21, '{\"title\":\"c++\"}', '127.0.0.1', '2026-04-03 15:26:56', '2026-04-03 15:26:56'),
(21, 1, 'update_course', 'Course', 9, '{\"title\":\"C++\"}', '127.0.0.1', '2026-04-03 15:28:12', '2026-04-03 15:28:12'),
(22, 1, 'delete_course', 'Course', 9, '{\"title\":\"C++\"}', '127.0.0.1', '2026-04-03 15:28:32', '2026-04-03 15:28:32'),
(23, 1, 'delete_course', 'Course', 21, '{\"title\":\"c++\"}', '127.0.0.1', '2026-04-03 15:28:35', '2026-04-03 15:28:35'),
(24, 1, 'update_course', 'Course', 22, '{\"title\":\"efwfew\"}', '127.0.0.1', '2026-04-03 15:31:06', '2026-04-03 15:31:06'),
(25, 1, 'update_course', 'Course', 22, '{\"title\":\"efwfew\"}', '127.0.0.1', '2026-04-03 15:39:22', '2026-04-03 15:39:22'),
(26, 1, 'update_course', 'Course', 28, '{\"title\":\"++c\"}', '127.0.0.1', '2026-04-03 15:43:07', '2026-04-03 15:43:07'),
(27, 1, 'update_course', 'Course', 28, '{\"title\":\"++c\"}', '127.0.0.1', '2026-04-03 15:46:40', '2026-04-03 15:46:40'),
(28, 1, 'update_course', 'Course', 22, '{\"title\":\"c++\"}', '127.0.0.1', '2026-04-03 15:47:07', '2026-04-03 15:47:07'),
(29, 1, 'toggle_admin', 'User', 11, '{\"new_state\":true}', '127.0.0.1', '2026-04-03 15:53:19', '2026-04-03 15:53:19'),
(30, 1, 'toggle_admin', 'User', 11, '{\"new_state\":false}', '127.0.0.1', '2026-04-03 15:53:43', '2026-04-03 15:53:43'),
(31, 1, 'toggle_suspend', 'User', 8, '{\"suspended\":true}', '127.0.0.1', '2026-04-03 15:58:59', '2026-04-03 15:58:59'),
(32, 1, 'toggle_suspend', 'User', 10, '{\"suspended\":true}', '127.0.0.1', '2026-04-03 16:00:07', '2026-04-03 16:00:07'),
(33, 1, 'delete_course_logo', 'Course', 28, '[]', '127.0.0.1', '2026-04-03 16:00:37', '2026-04-03 16:00:37'),
(34, 1, 'update_course', 'Course', 28, '{\"title\":\"++c\"}', '127.0.0.1', '2026-04-03 16:00:40', '2026-04-03 16:00:40'),
(35, 1, 'delete_course', 'Course', 22, '{\"title\":\"c++\"}', '127.0.0.1', '2026-04-03 16:00:52', '2026-04-03 16:00:52'),
(36, 1, 'update_course', 'Course', 28, '{\"title\":\"c++\"}', '127.0.0.1', '2026-04-03 16:01:08', '2026-04-03 16:01:08'),
(37, 1, 'update_course', 'Course', 28, '{\"title\":\"++c\"}', '127.0.0.1', '2026-04-03 16:04:07', '2026-04-03 16:04:07'),
(38, 1, 'delete_course', 'Course', 28, '{\"title\":\"++c\"}', '127.0.0.1', '2026-04-03 16:22:59', '2026-04-03 16:22:59'),
(39, 1, 'delete_course', 'Course', 14, '{\"title\":\"ahmad\"}', '127.0.0.1', '2026-04-03 16:24:23', '2026-04-03 16:24:23'),
(40, 1, 'toggle_suspend', 'User', 10, '{\"suspended\":false}', '127.0.0.1', '2026-04-03 16:32:05', '2026-04-03 16:32:05'),
(41, 1, 'toggle_admin', 'User', 11, '{\"new_state\":true}', '127.0.0.1', '2026-04-03 16:48:15', '2026-04-03 16:48:15'),
(42, 1, 'toggle_admin', 'User', 14, '{\"new_state\":true}', '127.0.0.1', '2026-04-03 16:50:55', '2026-04-03 16:50:55'),
(43, 1, 'toggle_admin', 'User', 11, '{\"new_state\":false}', '127.0.0.1', '2026-04-03 16:51:00', '2026-04-03 16:51:00'),
(44, 1, 'toggle_admin', 'User', 14, '{\"new_state\":false}', '127.0.0.1', '2026-04-03 16:58:32', '2026-04-03 16:58:32'),
(45, 1, 'toggle_admin', 'User', 14, '{\"new_state\":true}', '127.0.0.1', '2026-04-03 16:58:40', '2026-04-03 16:58:40'),
(46, 1, 'create_course', 'Course', 29, '{\"title\":\"A\"}', '127.0.0.1', '2026-04-03 18:29:55', '2026-04-03 18:29:55'),
(47, 1, 'delete_course', 'Course', 29, '{\"title\":\"A\"}', '127.0.0.1', '2026-04-03 18:38:06', '2026-04-03 18:38:06'),
(48, 1, 'toggle_suspend', 'User', 8, '{\"suspended\":false}', '127.0.0.1', '2026-05-13 17:41:34', '2026-05-13 17:41:34');

-- --------------------------------------------------------

--
-- Table structure for table `ai_conversations`
--

DROP TABLE IF EXISTS `ai_conversations`;
CREATE TABLE `ai_conversations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELATIONSHIPS FOR TABLE `ai_conversations`:
--   `user_id`
--       `users` -> `id`
--

--
-- Dumping data for table `ai_conversations`
--

INSERT INTO `ai_conversations` (`id`, `user_id`, `title`, `created_at`, `updated_at`) VALUES
(10, 1, 'what are these', '2026-04-02 20:25:22', '2026-04-02 20:38:45'),
(11, 1, 'what is this', '2026-04-02 20:25:35', '2026-04-02 20:25:44');

-- --------------------------------------------------------

--
-- Table structure for table `ai_messages`
--

DROP TABLE IF EXISTS `ai_messages`;
CREATE TABLE `ai_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `conversation_id` bigint(20) UNSIGNED NOT NULL,
  `role` varchar(20) NOT NULL,
  `content` mediumtext NOT NULL,
  `has_images` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELATIONSHIPS FOR TABLE `ai_messages`:
--   `conversation_id`
--       `ai_conversations` -> `id`
--

--
-- Dumping data for table `ai_messages`
--

INSERT INTO `ai_messages` (`id`, `conversation_id`, `role`, `content`, `has_images`, `created_at`, `updated_at`) VALUES
(65, 11, 'user', 'what is this', 1, '2026-04-02 20:25:36', '2026-04-02 20:25:36'),
(66, 11, 'assistant', 'هذا صورة شخصية لشخص يرتدي حجابًا أبيض وسترة داكنة وشالًا بُرْتُقاليًّا، مع خلفية تظهر فيها أضواء. كمساعد متخصص في البرمجة، لا أستطيع تحديد هوية الأشخاص في الصور، لكن إذا كانت لديك أسئلة متعلقة بمعالجة الصور أو البرمجة (مثل استخدام مكتبات مثل OpenCV)، فسأكون سعيدًا بمساعدتك!', 0, '2026-04-02 20:25:44', '2026-04-02 20:25:44'),
(67, 10, 'user', 'what are these\n\n```\n// === admin_assignments.php ===\r\n<?php\r\n// admin_assignments.php - Handle assignment management for admin\r\nsession_start();\r\nrequire \'db_connect.php\';\r\n\r\n// Admin authentication check\r\nif (!isset($_SESSION[\'user_id\']) || !isset($_SESSION[\'roles\']) || !in_array(\'admin\', $_SESSION[\'roles\'])) {\r\n    http_response_code(403);\r\n    echo json_encode([\'success\' => false, \'message\' => \'Unauthorized\']);\r\n    exit;\r\n}\r\n\r\n$method = $_SERVER[\'REQUEST_METHOD\'];\r\n\r\nswitch ($method) {\r\n    case \'GET\':\r\n        if (isset($_GET[\'id\'])) {\r\n            // Get single assignment\r\n            getAssignment($_GET[\'id\']);\r\n        } else {\r\n            // Get all assignments\r\n            getAllAssignments();\r\n        }\r\n        break;\r\n\r\n    case \'POST\':\r\n        // Create new assignment\r\n        createAssignment();\r\n        break;\r\n\r\n    case \'PUT\':\r\n        // Update assignment\r\n        updateAssignment();\r\n        break;\r\n\r\n    case \'DELETE\':\r\n        // Delete assignment\r\n        deleteAssignment($_GET[\'id\']);\r\n        break;\r\n\r\n    default:\r\n        http_response_code(405);\r\n        echo json_encode([\'success\' => false, \'message\' => \'Method not allowed\']);\r\n        break;\r\n}\r\n\r\nfunction getAllAssignments() {\r\n    global $pdo;\r\n\r\n    try {\r\n        $stmt = $pdo->query(\"SELECT a.id, a.course_id, a.question, a.difficulty, a.assignment_order, a.created_at, c.title as course_title, c.category FROM assignments a JOIN courses c ON a.course_id = c.id ORDER BY c.category, c.title, a.assignment_order\");\r\n        $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);\r\n\r\n        echo json_encode([\'success\' => true, \'assignments\' => $assignments]);\r\n    } catch (Exception $e) {\r\n        http_response_code(500);\r\n        echo json_encode([\'success\' => false, \'message\' => \'Database error\']);\r\n    }\r\n}\r\n\r\nfunction getAssignment($id) {\r\n    global $pdo;\r\n\r\n    try {\r\n        $stmt = $pdo->prepare(\"SELECT a.id, a.course_id, a.question, a.difficulty, a.assignment_order, a.created_at, c.title as course_title, c.category FROM assignments a JOIN courses c ON a.course_id = c.id WHERE a.id = ?\");\r\n        $stmt->execute([$id]);\r\n        $assignment = $stmt->fetch(PDO::FETCH_ASSOC);\r\n\r\n        if ($assignment) {\r\n            echo json_encode([\'success\' => true, \'assignment\' => $assignment]);\r\n        } else {\r\n            http_response_code(404);\r\n            echo json_encode([\'success\' => false, \'message\' => \'Assignment not found\']);\r\n        }\r\n    } catch (Exception $e) {\r\n        http_response_code(500);\r\n        echo json_encode([\'success\' => false, \'message\' => \'Database error\']);\r\n    }\r\n}\r\n\r\nfunction createAssignment() {\r\n    global $pdo;\r\n\r\n    $data = json_decode(file_get_contents(\'php://input\'), true);\r\n\r\n    if (!$data || !isset($data[\'course_id\']) || !isset($data[\'question\']) || !isset($data[\'difficulty\']) || !isset($data[\'assignment_order\'])) {\r\n        http_response_code(400);\r\n        echo json_encode([\'success\' => false, \'message\' => \'Missing required fields\']);\r\n        return;\r\n    }\r\n\r\n    try {\r\n        $stmt = $pdo->prepare(\"INSERT INTO assignments (course_id, question, difficulty, assignment_order) VALUES (?, ?, ?, ?)\");\r\n        $stmt->execute([$data[\'course_id\'], $data[\'question\'], $data[\'difficulty\'], $data[\'assignment_order\']]);\r\n\r\n        echo json_encode([\'success\' => true, \'message\' => \'Assignment created successfully\']);\r\n    } catch (Exception $e) {\r\n        http_response_code(500);\r\n        echo json_encode([\'success\' => false, \'message\' => \'Database error\']);\r\n    }\r\n}\r\n\r\nfunction updateAssignment() {\r\n    global $pdo;\r\n\r\n    $data = json_decode(file_get_contents(\'php://input\'), true);\r\n\r\n    if (!$data || !isset($data[\'id\']) || !isset($data[\'course_id\']) || !isset($data[\'question\']) || !isset($data[\'difficulty\']) || !isset($data[\'assignment_order\'])) {\r\n        http_response_code(400);\r\n        echo json_encode([\'success\' => false, \'message\' => \'Missing required fields\']);\r\n        return;\r\n    }\r\n\r\n    try {\r\n        $stmt = $pdo->prepare(\"UPDATE assignments SET course_id = ?, question = ?, difficulty = ?, assignment_order = ? WHERE id = ?\");\r\n        $stmt->execute([$data[\'course_id\'], $data[\'question\'], $data[\'difficulty\'], $data[\'assignment_order\'], $data[\'id\']]);\r\n\r\n        echo json_encode([\'success\' => true, \'message\' => \'Assignment updated successfully\']);\r\n    } catch (Exception $e) {\r\n        http_response_code(500);\r\n        echo json_encode([\'success\' => false, \'message\' => \'Database error\']);\r\n    }\r\n}\r\n\r\nfunction deleteAssignment($id) {\r\n    global $pdo;\r\n\r\n    if (!$id) {\r\n        http_response_code(400);\r\n        echo json_encode([\'success\' => false, \'message\' => \'Assignment ID required\']);\r\n        return;\r\n    }\r\n\r\n    try {\r\n        $stmt = $pdo->prepare(\"DELETE FROM assignments WHERE id = ?\");\r\n        $stmt->execute([$id]);\r\n\r\n        echo json_encode([\'success\' => true, \'message\' => \'Assignment deleted successfully\']);\r\n    } catch (Exception $e) {\r\n        http_response_code(500);\r\n        echo json_encode([\'success\' => false, \'message\' => \'Database error\']);\r\n    }\r\n}\r\n?>\r\n\r\n// === admin.php ===\r\n<!DOCTYPE html>\r\n<html lang=\"ar\" dir=\"rtl\">\r\n<head>\r\n    <meta charset=\"UTF-8\" />\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\" />\r\n    <title>لوحة الإدارة الرئيسية - أكاديمية البرمجة</title>\r\n    <link rel=\"stylesheet\" href=\"https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css\" />\r\n    <style>\r\n        :root {\r\n            --primary: #4361ee;\r\n            --secondary: #3a0ca3;\r\n            --success: #4ade80;\r\n            --danger: #ef4444;\r\n            --warning: #f59e0b;\r\n            --gray: #64748b;\r\n            --light: #f8fafc;\r\n            --dark: #1e293b;\r\n        }\r\n\r\n        * {\r\n            margin: 0;\r\n            padding: 0;\r\n            box-sizing: border-box;\r\n            font-family: \"Segoe UI\", Tahoma, Geneva, Verdana, sans-serif;\r\n        }\r\n\r\n        body {\r\n            background-color: var(--light);\r\n            color: var(--dark);\r\n            padding: 20px;\r\n        }\r\n\r\n        .container {\r\n            max-width: 1400px;\r\n            margin: 0 auto;\r\n        }\r\n\r\n        .header {\r\n            background: linear-gradient(135deg, var(--primary), var(--secondary));\r\n            color: white;\r\n            padding: 2rem;\r\n            border-radius: 15px;\r\n            margin-bottom: 2rem;\r\n            text-align: center;\r\n        }\r\n\r\n        .nav-tabs {\r\n            display: flex;\r\n            background: white;\r\n            border-radius: 10px;\r\n            margin-bottom: 2rem;\r\n            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);\r\n            overflow-x: auto;\r\n            overflow-y: hidden;\r\n            justify-content: space-between;\r\n        }\r\n\r\n        .nav-tab {\r\n            flex: 0 0 auto;\r\n            min-width: 100px;\r\n            padding: 1rem;\r\n            text-align: center;\r\n            cursor: pointer;\r\n            transition: all 0.3s ease;\r\n            font-weight: 500;\r\n            border-bottom: 3px solid transparent;\r\n            white-space: nowrap;\r\n            overflow: hidden;\r\n            text-overflow: ellipsis;\r\n        }\r\n\r\n        .nav-tab:hover {\r\n            background: #f8fafc;\r\n        }\r\n\r\n        .nav-tab.active {\r\n            background: var(--primary);\r\n            color: white;\r\n            border-bottom-color: var(--primary);\r\n        }\r\n\r\n        .tab-content {\r\n            display: none;\r\n        }\r\n\r\n        .tab-content.active {\r\n            display: block;\r\n        }\r\n\r\n        .add-platform-btn {\r\n            background: var(--success);\r\n            color: white;\r\n            border: none;\r\n            padding: 1rem 2rem;\r\n            border-radius: 10px;\r\n            font-size: 1.1rem;\r\n            cursor: pointer;\r\n            margin-bottom: 2rem;\r\n            transition: all 0.3s ease;\r\n        }\r\n\r\n        .add-platform-btn:hover {\r\n            background: #22c55e;\r\n            transform: translateY(-2px);\r\n        }\r\n\r\n        .modal {\r\n            display: none;\r\n            position: fixed;\r\n            top: 0;\r\n            left: 0;\r\n            width: 100%;\r\n            height: 100%;\r\n            background: rgba(0, 0, 0, 0.5);\r\n            z-index: 1000;\r\n        }\r\n\r\n        .modal-content {\r\n            background: white;\r\n            margin: 5% auto;\r\n            padding: 2rem;\r\n            border-radius: 15px;\r\n            width: 90%;\r\n            max-width: 600px;\r\n            max-height: 80vh;\r\n            overflow-y: auto;\r\n        }\r\n\r\n        .form-group {\r\n            margin-bottom: 1.5rem;\r\n        }\r\n\r\n        .form-group label {\r\n            display: block;\r\n            margin-bottom: 0.5rem;\r\n            font-weight: 600;\r\n            color: var(--dark);\r\n        }\r\n\r\n        .form-group input,\r\n        .form-group select,\r\n        .form-group textarea {\r\n            width: 100%;\r\n            padding: 0.8rem;\r\n            border: 2px solid #e2e8f0;\r\n            border-radius: 8px;\r\n            font-size: 1rem;\r\n            transition: border-color 0.3s ease;\r\n        }\r\n\r\n        .form-group input:focus,\r\n        .form-group select:focus,\r\n        .form-group textarea:focus {\r\n            outline: none;\r\n            border-color: var(--primary);\r\n        }\r\n\r\n        .form-group textarea {\r\n            resize: vertical;\r\n            min-height: 100px;\r\n        }\r\n\r\n        .features-input {\r\n            display: flex;\r\n            gap: 0.5rem;\r\n            align-items: center;\r\n        }\r\n\r\n        .features-list {\r\n            display: flex;\r\n            flex-wrap: wrap;\r\n            gap: 0.5rem;\r\n            margin-top: 0.5rem;\r\n        }\r\n\r\n        .feature-tag {\r\n            background: var(--primary);\r\n            color: white;\r\n            padding: 0.3rem 0.8rem;\r\n            border-radius: 20px;\r\n            font-size: 0.9rem;\r\n            display: flex;\r\n            align-items: center;\r\n            gap: 0.5rem;\r\n        }\r\n\r\n        .no-data {\r\n            text-align: center;\r\n            padding: 2rem;\r\n            color: var(--gray);\r\n            font-size: 1.1rem;\r\n        }\r\n\r\n        .video-preview {\r\n            border: 1px solid #ddd;\r\n            border-radius: 5px;\r\n            padding: 10px;\r\n            background: #f8f9fa;\r\n        }\r\n\r\n        .video-preview video {\r\n            width: 100%;\r\n            max-height: 200px;\r\n            border-radius: 3px;\r\n        }\r\n\r\n        .feature-tag .remove {\r\n            cursor: pointer;\r\n            font-weight: bold;\r\n        }\r\n\r\n        .btn-group {\r\n            display: flex;\r\n            gap: 1rem;\r\n            justify-content: flex-end;\r\n            margin-top: 2rem;\r\n        }\r\n\r\n        .btn {\r\n            padding: 0.8rem 1.5rem;\r\n            border: none;\r\n            border-radius: 8px;\r\n            font-size: 1rem;\r\n            cursor: pointer;\r\n            transition: all 0.3s ease;\r\n        }\r\n\r\n        .btn-primary {\r\n            background: var(--primary);\r\n            color: white;\r\n        }\r\n\r\n        .btn-secondary {\r\n            background: var(--gray);\r\n            color: white;\r\n        }\r\n\r\n        .btn:hover {\r\n            transform: translateY(-2px);\r\n        }\r\n\r\n        .platforms-grid {\r\n            display: grid;\r\n            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));\r\n            gap: 1.5rem;\r\n        }\r\n\r\n        .platform-card {\r\n            background: white;\r\n            border-radius: 10px;\r\n            padding: 1.5rem;\r\n            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);\r\n            border: 1px solid #e2e8f0;\r\n            display: flex;\r\n            flex-direction: column;\r\n            height: 280px;\r\n        }\r\n\r\n        .platform-header {\r\n            display: flex;\r\n            justify-content: space-between;\r\n            align-items: flex-start;\r\n            margin-bottom: 1rem;\r\n            flex-shrink: 0;\r\n        }\r\n\r\n        .platform-title {\r\n            font-size: 1.2rem;\r\n            font-weight: 600;\r\n            color: var(--dark);\r\n            line-height: 1.3;\r\n            margin: 0;\r\n            display: -webkit-box;\r\n            -webkit-line-clamp: 2;\r\n            -webkit-box-orient: vertical;\r\n            overflow: hidden;\r\n            text-overflow: ellipsis;\r\n        }\r\n\r\n        .platform-status {\r\n            padding: 0.3rem 0.8rem;\r\n            border-radius: 20px;\r\n            font-size: 0.8rem;\r\n            font-weight: 500;\r\n            flex-shrink: 0;\r\n        }\r\n\r\n        .status-active {\r\n            background: var(--success);\r\n            color: white;\r\n        }\r\n\r\n        .status-inactive {\r\n            background: var(--danger);\r\n            color: white;\r\n        }\r\n\r\n        .platform-stats {\r\n            display: grid;\r\n            grid-template-columns: 1fr 1fr;\r\n            gap: 0.5rem;\r\n            margin-bottom: 1rem;\r\n            font-size: 0.9rem;\r\n            color: var(--gray);\r\n            flex-grow: 1;\r\n        }\r\n\r\n        .platform-stats > div {\r\n            display: flex;\r\n            align-items: center;\r\n            gap: 0.3rem;\r\n            min-height: 1.5rem;\r\n        }\r\n\r\n        .platform-actions {\r\n            display: flex;\r\n            gap: 0.5rem;\r\n            flex-shrink: 0;\r\n            margin-top: auto;\r\n        }\r\n\r\n        .action-btn {\r\n            flex: 1;\r\n            padding: 0.5rem;\r\n            border: none;\r\n            border-radius: 5px;\r\n            cursor: pointer;\r\n            font-size: 0.9rem;\r\n            transition: all 0.3s ease;\r\n            text-align: center;\r\n            white-space: nowrap;\r\n            overflow: hidden;\r\n            text-overflow: ellipsis;\r\n        }\r\n\r\n        .edit-btn {\r\n            background: var(--warning);\r\n            color: white;\r\n        }\r\n\r\n        .delete-btn {\r\n            background: var(--danger);\r\n            color: white;\r\n        }\r\n\r\n        .toggle-btn {\r\n            background: var(--gray);\r\n            color: white;\r\n        }\r\n\r\n        .deactivate-btn {\r\n            background: #f59e0b;\r\n        }\r\n\r\n        .activate-btn {\r\n            background: var(--success);\r\n        }\r\n\r\n        .notification {\r\n            position: fixed;\r\n            top: 20px;\r\n            right: 20px;\r\n            padding: 1rem;\r\n            border-radius: 8px;\r\n            color: white;\r\n            z-index: 1001;\r\n            display: none;\r\n            max-width: 400px;\r\n        }\r\n\r\n        .notification.success {\r\n            background: var(--success);\r\n        }\r\n\r\n        .notification.error {\r\n            background: var(--danger);\r\n        }\r\n\r\n        .close-modal {\r\n            position: absolute;\r\n            top: 1rem;\r\n            left: 1rem;\r\n            background: none;\r\n            border: none;\r\n            font-size: 1.5rem;\r\n            cursor: pointer;\r\n            color: var(--gray);\r\n        }\r\n\r\n        .checkbox-label {\r\n            display: flex;\r\n            align-items: center;\r\n            gap: 0.5rem;\r\n            cursor: pointer;\r\n            font-weight: normal;\r\n        }\r\n\r\n        .checkbox-label input[type=\"checkbox\"] {\r\n            width: auto;\r\n            margin: 0;\r\n        }\r\n\r\n        /* Upload Section Styles */\r\n        .form-container {\r\n            max-width: 850px;\r\n            margin: auto;\r\n            padding: 30px;\r\n            border-radius: 16px;\r\n            background-color: #ffffff;\r\n            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);\r\n        }\r\n\r\n        .form-container h2 {\r\n            text-align: center;\r\n            color: #1e3a8a;\r\n            margin-bottom: 30px;\r\n            border-bottom: 3px solid #3b82f6;\r\n            padding-bottom: 15px;\r\n        }\r\n\r\n        .form-container .form-group {\r\n            margin-bottom: 25px;\r\n        }\r\n\r\n        .form-container label {\r\n            display: block;\r\n            margin-bottom: 8px;\r\n            font-weight: 600;\r\n            color: #4b5563;\r\n        }\r\n\r\n        .form-container input[type=\"text\"],\r\n        .form-container input[type=\"file\"],\r\n        .form-container select,\r\n        .form-container textarea {\r\n            width: 100%;\r\n            padding: 12px;\r\n            border: 1px solid #d1d5db;\r\n            border-radius: 8px;\r\n            box-sizing: border-box;\r\n            transition: border-color 0.3s, box-shadow 0.3s;\r\n            background-color: #f9fafb;\r\n        }\r\n\r\n        .form-container input:focus,\r\n        .form-container select:focus,\r\n        .form-container textarea:focus {\r\n            border-color: #3b82f6;\r\n            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);\r\n            outline: none;\r\n        }\r\n\r\n        .form-container textarea {\r\n            resize: vertical;\r\n            min-height: 100px;\r\n        }\r\n\r\n        #new-course-fields {\r\n            display: none;\r\n            border: 2px solid #a5b4fc;\r\n            padding: 25px;\r\n            margin-top: 20px;\r\n            border-radius: 12px;\r\n            background-color: #f5f7ff;\r\n        }\r\n\r\n        #new-course-fields h4 {\r\n            color: #4f46e5;\r\n            margin-bottom: 15px;\r\n            border-bottom: 1px solid #c7d2fe;\r\n            padding-bottom: 8px;\r\n        }\r\n\r\n        #courseId {\r\n            cursor: pointer;\r\n        }\r\n\r\n        .loader {\r\n            border: 4px solid #f3f3f3;\r\n            border-top: 4px solid #3b82f6;\r\n            border-radius: 50%;\r\n            width: 20px;\r\n            height: 20px;\r\n            animation: spin 1s linear infinite;\r\n            display: none;\r\n            vertical-align: middle;\r\n            margin-right: 10px;\r\n        }\r\n\r\n        @keyframes spin {\r\n            0% { transform: rotate(0deg); }\r\n            100% { transform: rotate(360deg); }\r\n        }\r\n\r\n        #file-list {\r\n            margin-top: 30px;\r\n            padding-top: 20px;\r\n            border-top: 1px solid #e5e7eb;\r\n        }\r\n\r\n        .file-entry {\r\n            display: flex;\r\n            align-items: flex-start;\r\n            gap: 20px;\r\n            border: 1px solid #e5e7eb;\r\n            padding: 20px;\r\n            margin-bottom: 20px;\r\n            border-radius: 10px;\r\n            background-color: #fff;\r\n            transition: box-shadow 0.3s;\r\n        }\r\n\r\n        .file-entry:hover {\r\n            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);\r\n        }\r\n\r\n        .file-info {\r\n            flex-grow: 1;\r\n        }\r\n\r\n        .file-preview {\r\n            width: 150px;\r\n            min-width: 150px;\r\n            height: 100px;\r\n            overflow: hidden;\r\n            border-radius: 8px;\r\n            background-color: #e0e7ff;\r\n            display: flex;\r\n            justify-content: center;\r\n            align-items: center;\r\n        }\r\n\r\n        .file-preview img,\r\n        .file-preview video {\r\n            width: 100%;\r\n            height: 100%;\r\n            object-fit: cover;\r\n        }\r\n\r\n        .file-preview .fa-icon {\r\n            font-size: 40px;\r\n            color: #3b82f6;\r\n        }\r\n\r\n        .file-preview p {\r\n            margin: 0;\r\n            font-size: 14px;\r\n            color: #6b7280;\r\n        }\r\n\r\n        .submit-btn {\r\n            background-color: #10b981;\r\n            color: white;\r\n            padding: 14px 25px;\r\n            border: none;\r\n            border-radius: 8px;\r\n            cursor: pointer;\r\n            font-size: 18px;\r\n            width: 100%;\r\n            transition: background-color 0.3s;\r\n            margin-top: 20px;\r\n        }\r\n\r\n        .submit-btn:hover {\r\n            background-color: #059669;\r\n        }\r\n\r\n        .submit-btn:disabled {\r\n            background-color: #94a3b8;\r\n            cursor: not-allowed;\r\n        }\r\n\r\n        #upload-progress-container {\r\n            display: none;\r\n            margin-top: 20px;\r\n            padding: 15px;\r\n            background-color: #f0f9ff;\r\n            border-radius: 8px;\r\n            border: 1px solid #bae6fd;\r\n        }\r\n\r\n        .progress-bar-container {\r\n            width: 100%;\r\n            height: 20px;\r\n            background-color: #e2e8f0;\r\n            border-radius: 10px;\r\n            margin: 10px 0;\r\n            overflow: hidden;\r\n        }\r\n\r\n        .progress-bar {\r\n            height: 100%;\r\n            background-color: #3b82f6;\r\n            width: 0%;\r\n            transition: width 0.3s;\r\n            border-radius: 10px;\r\n            display: flex;\r\n            align-items: center;\r\n            justify-content: center;\r\n            color: white;\r\n            font-size: 12px;\r\n            font-weight: bold;\r\n        }\r\n    </style>\r\n    <script src=\"site-notification.js\"></script>\r\n</head>\r\n<body>\r\n    <div class=\"container\">\r\n        <div class=\"header\">\r\n            <h1><i class=\"fas fa-cogs\"></i> لوحة الإدارة الرئيسية</h1>\r\n            <p>إدارة المنصات ورفع الدروس</p>\r\n        </div>\r\n\r\n        <!-- Tab Navigation -->\r\n        <div class=\"nav-tabs\">\r\n            <div class=\"nav-tab active\" data-tab=\"platforms\">\r\n                <i class=\"fas fa-code\"></i> إدارة المنصات\r\n            </div>\r\n            <div class=\"nav-tab\" data-tab=\"examples\">\r\n                <i class=\"fas fa-book\"></i> إدارة الأمثلة\r\n            </div>\r\n            <div class=\"nav-tab\" data-tab=\"courses\">\r\n                <i class=\"fas fa-graduation-cap\"></i> إدارة الكورسات\r\n            </div>\r\n            <div class=\"nav-tab\" data-tab=\"videos\">\r\n                <i class=\"fas fa-film\"></i> إدارة فيديوهات الكورسات\r\n            </div>\r\n            <div class=\"nav-tab\" data-tab=\"upload\">\r\n                <i class=\"fas fa-upload\"></i> رفع الدروس\r\n            </div>\r\n            <div class=\"nav-tab\" data-tab=\"assignments\">\r\n                <i class=\"fas fa-tasks\"></i> إدارة التكليفات\r\n            </div>\r\n            <div class=\"nav-tab\" data-tab=\"challenges\">\r\n                <i class=\"fas fa-puzzle-piece\"></i> إدارة التحديات\r\n            </div>\r\n        </div>\r\n\r\n        <!-- Platforms Tab -->\r\n        <div class=\"tab-content active\" id=\"platformsTab\">\r\n            <button class=\"add-platform-btn\" onclick=\"openAddModal()\">\r\n                <i class=\"fas fa-plus\"></i> إضافة منصة جديدة\r\n            </button>\r\n\r\n            <div class=\"platforms-grid\" id=\"platformsGrid\">\r\n                <!-- Platforms will be loaded here -->\r\n            </div>\r\n        </div>\r\n\r\n        <!-- Examples Tab -->\r\n        <div class=\"tab-content\" id=\"examplesTab\">\r\n            <button class=\"add-platform-btn\" onclick=\"openAddExampleModal()\">\r\n                <i class=\"fas fa-plus\"></i> إضافة مثال جديد\r\n            </button>\r\n\r\n            <div class=\"platforms-grid\" id=\"examplesGrid\">\r\n                <!-- Examples will be loaded here -->\r\n            </div>\r\n        </div>\r\n\r\n        <!-- Courses Tab -->\r\n        <div class=\"tab-content\" id=\"coursesTab\">\r\n            <div class=\"platforms-grid\" id=\"coursesGrid\">\r\n                <!-- Courses will be loaded here -->\r\n            </div>\r\n        </div>\r\n\r\n        <!-- Videos Tab -->\r\n        <div class=\"tab-content\" id=\"videosTab\">\r\n            <div class=\"form-container\">\r\n                <h2><i class=\"fas fa-film\"></i> إدارة فيديوهات الكورس</h2>\r\n\r\n                <div class=\"form-group\">\r\n                    <label for=\"videoCourseSelect\">اختر الكورس</label>\r\n                    <select id=\"videoCourseSelect\">\r\n                        <option value=\"\">-- اختر كورساً --</option>\r\n                    </select>\r\n                </div>\r\n\r\n                <div id=\"lessonsList\">\r\n                    <!-- Lessons will render here -->\r\n                </div>\r\n            </div>\r\n        </div>\r\n\r\n        <!-- Upload Tab -->\r\n        <div class=\"tab-content\" id=\"uploadTab\">\r\n            <div class=\"form-container\">\r\n                <h2><i class=\"fas fa-video\"></i> لوحة رفع الدروس الجديدة</h2>\r\n\r\n                <form id=\"uploadForm\" action=\"http://localhost:8000/api/admin/upload\" method=\"POST\" enctype=\"multipart/form-data\">\r\n                    <div class=\"form-group\">\r\n                        <label for=\"courseId\"><i class=\"fas fa-book\"></i> تحديد الكورس</label>\r\n                        <div style=\"display: flex; align-items: center;\">\r\n                            <select id=\"courseId\" name=\"course_id\" required>\r\n                                <option value=\"\">-- Loading Courses... --</option>\r\n                                <option value=\"new\">-- إنشاء كورس جديد --</option>\r\n                            </select>\r\n                            <div id=\"course-loader\" class=\"loader\"></div>\r\n                        </div>\r\n                    </div>\r\n\r\n                    <div id=\"new-course-fields\">\r\n                        <h4>تفاصيل الكورس الجديد</h4>\r\n                        <div class=\"form-group\">\r\n                            <label for=\"newCourseTitle\"><i class=\"fas fa-pencil-alt\"></i> عنوان الكورس</label>\r\n                            <input type=\"text\" id=\"newCourseTitle\" name=\"new_course_title\" placeholder=\"مثال: تطوير الويب باستخدام PHP\" />\r\n                        </div>\r\n                        <div class=\"form-group\">\r\n                            <label for=\"newCourseCategory\"><i class=\"fas fa-tags\"></i\n```', 0, '2026-04-02 20:26:19', '2026-04-02 20:26:19'),
(68, 10, 'assistant', 'هذان الملفان يمثلان جزءاً مهماً من نظام إدارة محتوى أكاديمية البرمجة:\n\n## الملف الأول: `admin_assignments.php`\n\n**وظيفة الملف:**\n- ملف PHP خلفي (Backend) لإدارة التكليفات/الواجبات البرمجية\n- يعمل كـ API للتعامل مع قاعدة البيانات\n\n**المميزات الأساسية:**\n- **الأمان**: يتحقق من أن المستخدم مسؤول قبل السماح بالوصول\n- **العمليات المتاحة**:\n  - عرض جميع التكليفات (`GET`)\n  - عرض تكليف محدد (`GET` مع ID)\n  - إنشاء تكليف جديد (`POST`)\n  - تحديث تكليف (`PUT`)\n  - حذف تكليف (`DELETE`)\n\n## الملف الثاني: `admin.php`\n\n**وظيفة الملف:**\n- لوحة الإدارة الرئيسية (Frontend) باستخدام HTML/CSS/JavaScript\n- واجهة رسومية لإدارة كاملة للموقع\n\n**الأقسام المتوفرة:**\n1. **إدارة المنصات** - للتحكم في منصات البرمجة\n2. **إدارة الأمثلة** - أمثلة برمجية\n3. **إدارة الكورسات** - الدروس والكورسات\n4. **إدارة الفيديوهات** - فيديوهات الدروس\n5. **رفع الدروس** - رفع محتوى جديد\n6. **إدارة التكليفات** - الواجبات والممارسات\n7. **إدارة التحديات** - تحديات برمجية\n\n**التقنيات المستخدمة:**\n- HTML5 و CSS3 لتصميم الواجهة\n- JavaScript للتفاعل\n- Font Awesome للأيقونات\n- تصميم متجاوب يعمل على جميع الأجهزة\n\nهذا النظام يسمح للمسؤولين بإدارة كامل محتوى الأكاديمية من مكان واحد بشكل م organized وسهل الاستخدام.', 0, '2026-04-02 20:26:36', '2026-04-02 20:26:36'),
(69, 10, 'user', 'what is this image', 1, '2026-04-02 20:27:01', '2026-04-02 20:27:01'),
(70, 10, 'assistant', 'هذه صورة شخصية (Avatar) م cortée بشكل دائري، تظهر شخصًا يرتدي حجابًا أبيض وشالًا أحمر داكن مع سترة سوداء. \n\nمن الناحية التقنية:\n- الصورة م cortée (مقطوعة) بشكل دائري، وهي شكل شائع لصور الملف الشخصي في المواقع والتطبيقات\n- تُستخدم عادةً كصورة تعريفية (Profile Picture) في أنظمة إدارة المحتوى أو منصات التواصل الاجتماعي\n- في سياق البرمجة، قد تكون جزءًا من واجهة مستخدم (UI) لموقع أو تطبيق، خاصة في أقسام \"الملف الشخصي\" أو \"الإعدادات\"\n\nملاحظة: كمساعد برمجي، لا أستطيع تحديد هوية الشخص في الصورة، وأركز فقط على الوصف التقني للصورة وسياق استخدامها في التطبيقات البرمجية.', 0, '2026-04-02 20:27:12', '2026-04-02 20:27:12'),
(71, 10, 'user', 'hi', 0, '2026-04-02 20:38:27', '2026-04-02 20:38:27'),
(72, 10, 'assistant', 'مرحباً! كيف يمكنني مساعدتك اليوم؟ 😊', 0, '2026-04-02 20:38:34', '2026-04-02 20:38:34'),
(73, 10, 'user', 'how are you', 0, '2026-04-02 20:38:39', '2026-04-02 20:38:39'),
(74, 10, 'assistant', 'أنا بخير، شكرًا لسؤالك! 😊 كيف يمكنني مساعدتك اليوم؟', 0, '2026-04-02 20:38:45', '2026-04-02 20:38:45');

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

DROP TABLE IF EXISTS `assignments`;
CREATE TABLE `assignments` (
  `id` int(11) NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `question` text NOT NULL,
  `difficulty` int(11) NOT NULL DEFAULT 1,
  `assignment_order` int(11) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELATIONSHIPS FOR TABLE `assignments`:
--   `course_id`
--       `courses` -> `id`
--

--
-- Dumping data for table `assignments`
--

INSERT INTO `assignments` (`id`, `course_id`, `question`, `difficulty`, `assignment_order`, `is_active`, `created_at`) VALUES
(4, 10, 'اكتب كود CSS لتصميم قائمة تنقل أفقية مع تأثيرات hover.', 2, 1, 1, '2025-12-07 20:43:57'),
(5, 10, 'أنشئ تصميم متجاوب باستخدام CSS Grid لصفحة شخصية.', 3, 2, 1, '2025-12-07 20:43:57'),
(6, 8, 'أنشئ صفحة HTML تحتوي على نموذج تسجيل دخول مع جميع الحقول المطلوبة.', 1, 1, 1, '2025-12-07 20:43:57'),
(7, 26, 'أنشئ صفحة HTML لمعرض صور مع استخدام semantic elements.', 2, 2, 1, '2025-12-07 20:43:57'),
(8, 6, 'أنشئ مكون React لعرض قائمة مهام قابلة للإضافة والحذف.', 3, 1, 1, '2025-12-07 20:43:57');

-- --------------------------------------------------------

--
-- Table structure for table `challenges`
--

DROP TABLE IF EXISTS `challenges`;
CREATE TABLE `challenges` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `category` enum('algorithms','data-structures','web','database') NOT NULL,
  `difficulty` enum('easy','medium','hard') NOT NULL,
  `points` int(11) NOT NULL DEFAULT 0,
  `starter_code` text DEFAULT NULL,
  `code_language` varchar(50) DEFAULT NULL,
  `test_cases` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`test_cases`)),
  `solution_template` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELATIONSHIPS FOR TABLE `challenges`:
--

--
-- Dumping data for table `challenges`
--

INSERT INTO `challenges` (`id`, `title`, `description`, `category`, `difficulty`, `points`, `starter_code`, `code_language`, `test_cases`, `solution_template`, `is_active`, `created_at`, `updated_at`) VALUES
(2, 'شجرة ثنائية بحث', 'صمم وطبق شجرة ثنائية بحث مع عمليات الإدراج والحذف والبحث. تأكد من الحفاظ على خصائص شجرة البحث الثنائية.', 'data-structures', 'medium', 100, NULL, 'python', NULL, NULL, 1, '2025-12-17 19:13:05', '2025-12-17 19:13:05'),
(3, 'آلة حاسبة تفاعلية', 'أنشئ آلة حاسبة تفاعلية باستخدام HTML CSS وJavaScript. يجب أن تدعم العمليات الأساسية والعمليات المتقدمة.', 'web', 'easy', 75, NULL, 'javascript', NULL, NULL, 1, '2025-12-17 19:13:05', '2025-12-17 19:13:05'),
(7, 'مقلوب المصفوفة', 'اكتب دالة تقوم بقلب عناصر المصفوفة بدون استخدام دوال جاهزة. يجب أن تعيد المصفوفة المقلوبة.', 'algorithms', 'easy', 40, NULL, 'javascript', NULL, NULL, 1, '2025-12-17 19:45:34', '2025-12-17 19:45:34'),
(8, 'جمع الأرقام في مصفوفة', 'اكتب دالة تحسب مجموع جميع الأرقام في مصفوفة معينة وتعيد النتيجة.', 'algorithms', 'easy', 35, NULL, 'javascript', NULL, NULL, 1, '2025-12-17 19:45:34', '2025-12-17 19:45:34'),
(9, 'ترتيب بالفقاعات', 'طبق خوارزمية ترتيب الفقاعات (Bubble Sort) لترتيب مصفوفة من الأرقام تصاعدياً.', 'algorithms', 'medium', 70, NULL, 'javascript', NULL, NULL, 1, '2025-12-17 19:45:34', '2025-12-17 19:45:34'),
(10, 'أكبر عدد مشترك', 'اكتب دالة تحسب أكبر عدد مشترك (GCD) بين عددين باستخدام خوارزمية إقليدس.', 'algorithms', 'medium', 65, NULL, 'javascript', NULL, NULL, 1, '2025-12-17 19:45:34', '2025-12-17 19:45:34'),
(11, 'أسرع طريق', 'طبق خوارزمية دايكسترا لإيجاد أقصر طريق في رسم بياني مع أوزان.', 'algorithms', 'hard', 150, NULL, 'javascript', NULL, NULL, 1, '2025-12-17 19:45:34', '2025-12-17 19:45:34'),
(12, 'قائمة مرتبطة بسيطة', 'أنشئ فئة قائمة مرتبطة بسيطة مع عمليات الإدراج في البداية والنهاية والحذف.', 'data-structures', 'easy', 60, NULL, 'javascript', NULL, NULL, 1, '2025-12-17 19:45:34', '2025-12-17 19:45:34'),
(13, 'مكدس باستخدام مصفوفة', 'طبق بنية مكدس (Stack) باستخدام مصفوفة مع عمليات push و pop و peek.', 'data-structures', 'medium', 80, NULL, 'javascript', NULL, NULL, 1, '2025-12-17 19:45:34', '2025-12-17 19:45:34'),
(14, 'طابور باستخدام قائمة مرتبطة', 'طبق بنية طابور (Queue) باستخدام قائمة مرتبطة مع عمليات enqueue و dequeue.', 'data-structures', 'medium', 85, NULL, 'javascript', NULL, NULL, 1, '2025-12-17 19:45:34', '2025-12-17 19:45:34'),
(15, 'شجرة AVL', 'طبق شجرة AVL مع عمليات الإدراج والحذف مع الحفاظ على توازن الشجرة.', 'data-structures', 'hard', 180, NULL, 'javascript', NULL, NULL, 1, '2025-12-17 19:45:34', '2025-12-17 19:45:34'),
(16, 'تحقق من صحة البريد الإلكتروني', 'اكتب دالة JavaScript تتحقق من صحة عنوان البريد الإلكتروني باستخدام تعبيرات منتظمة.', 'web', 'easy', 45, NULL, 'javascript', NULL, NULL, 1, '2025-12-17 19:45:34', '2025-12-17 19:45:34'),
(17, 'تغيير ألوان العناصر', 'أنشئ صفحة HTML بأزرار تتيح تغيير ألوان عناصر الصفحة باستخدام JavaScript.', 'web', 'easy', 50, NULL, 'javascript', NULL, NULL, 1, '2025-12-17 19:45:34', '2025-12-17 19:45:34'),
(18, 'قائمة مهام تفاعلية', 'أنشئ تطبيق قائمة مهام باستخدام HTML، CSS، وJavaScript مع إمكانية إضافة وحذف المهام.', 'web', 'medium', 90, NULL, 'javascript', NULL, NULL, 1, '2025-12-17 19:45:34', '2025-12-17 19:45:34'),
(19, 'ساعة رقمية', 'أنشئ ساعة رقمية تُظهر الوقت الحالي وتتحدث كل ثانية باستخدام JavaScript.', 'web', 'medium', 75, NULL, 'javascript', NULL, NULL, 1, '2025-12-17 19:45:34', '2025-12-17 19:45:34'),
(20, 'لعبة الذاكرة', 'أنشئ لعبة الذاكرة (Memory Game) باستخدام JavaScript مع بطاقات قابلة للقلب ونظام نقاط.', 'web', 'hard', 140, NULL, 'javascript', NULL, NULL, 1, '2025-12-17 19:45:34', '2025-12-17 19:45:34'),
(21, 'استعلام بسيط للطلاب', 'اكتب استعلام SQL لاستخراج أسماء جميع الطلاب من جدول الطلاب مرتبة أبجدياً.', 'database', 'easy', 30, NULL, 'sql', NULL, NULL, 1, '2025-12-17 19:45:34', '2025-12-17 19:45:34'),
(22, 'انضمام جداول الطلاب والكورسات', 'اكتب استعلام SQL يجمع بين جداول الطلاب والكورسات لإظهار الطلاب المسجلين في كل كورس.', 'database', 'medium', 55, NULL, 'sql', NULL, NULL, 1, '2025-12-17 19:45:34', '2025-12-17 19:45:34'),
(23, 'إحصائيات المبيعات', 'اكتب استعلام SQL يحسب إجمالي المبيعات لكل منتج ويظهر أعلى 5 منتجات مبيعاً.', 'database', 'medium', 70, NULL, 'sql', NULL, NULL, 1, '2025-12-17 19:45:34', '2025-12-17 19:45:34'),
(24, 'تقرير معقد للمخزون', 'اكتب استعلامات SQL معقدة لإنشاء تقرير شامل عن حالة المخزون مع التنبؤات والإنذارات.', 'database', 'hard', 120, NULL, 'sql', NULL, NULL, 1, '2025-12-17 19:45:34', '2025-12-17 19:45:34'),
(25, 'Test Challenge', 'Test description', 'algorithms', 'easy', 50, NULL, 'javascript', NULL, NULL, 1, '2025-12-17 19:50:04', '2025-12-17 19:50:04'),
(26, 'Test Challenge API', 'Test description for API', 'algorithms', 'easy', 50, NULL, NULL, NULL, NULL, 1, '2025-12-17 19:51:16', '2025-12-17 19:51:16'),
(27, 'tTest', 'Test', 'algorithms', 'easy', 10, NULL, NULL, NULL, NULL, 0, '2025-12-17 19:58:48', '2026-03-26 19:52:36');

-- --------------------------------------------------------

--
-- Table structure for table `challenge_attempts`
--

DROP TABLE IF EXISTS `challenge_attempts`;
CREATE TABLE `challenge_attempts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `challenge_id` int(11) NOT NULL,
  `code` text NOT NULL,
  `completed` tinyint(1) DEFAULT 0,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELATIONSHIPS FOR TABLE `challenge_attempts`:
--   `user_id`
--       `users` -> `id`
--   `challenge_id`
--       `challenges` -> `id`
--

--
-- Dumping data for table `challenge_attempts`
--

INSERT INTO `challenge_attempts` (`id`, `user_id`, `challenge_id`, `code`, `completed`, `completed_at`, `created_at`) VALUES
(1, 11, 2, 'print(1)', 1, '2026-03-24 20:17:29', '2026-03-24 23:17:29'),
(3, 1, 2, 'test_code_1774722211', 0, NULL, '2026-03-28 18:23:31'),
(4, 1, 27, 'SELECT 1', 0, NULL, '2026-03-28 18:37:05'),
(5, 1, 8, 'function countNumbersInArray(array) {\n    let count = 0;\n\n    // Check if the array exists in the matrix\n    if (!Array.isArray(array)) {\n        throw new Error(\"The specified array does not exist.\");\n    }\n\n    // Loop through each element in the array and increment the count\n    for (let i = 0; i < array.length; i++) {\n        count++;\n    }\n\n    return count;\n}\n\nconst students = [\n    { id: 1, name: \"Alice\", course: \"Math\" },\n    { id: 2, name: \"Bob\", course: \"Science\" },\n    { id: 3, name: \"Charlie\", course: \"Art\" }\n];\n\n// Example usage of the countNumbersInArray function\nconst numberOfStudents = countNumbersInArray(students);\nconsole.log(numberOfStudents); // Output: 3', 1, '2026-03-28 15:39:56', '2026-03-28 18:39:23'),
(7, 1, 7, 'def reverse_array(arr):\n    n = len(arr)\n    # نمر على نصف المصفوفة فقط ونبدل العناصر\n    for i in range(n // 2):\n        arr[i], arr[n - 1 - i] = arr[n - 1 - i], arr[i]\n    return arr\n\n# مثال:\nprint(reverse_array([10, 20, 30, 40])) # المخرج: [40, 30, 20, 10]', 1, '2026-03-29 18:20:13', '2026-03-29 20:31:53'),
(8, 1, 10, 'def gcd(a, b):\n    while b != 0:\n        a, b = b, a % b\n    return a', 1, '2026-04-03 14:22:41', '2026-03-29 20:33:22'),
(9, 1, 19, '/**\n * كائن الساعة الرقمية - تطبيق برمجى بدقة عالية\n */\nconst DigitalClock = {\n    // دالة لبدء تشغيل الساعة\n    start: function() {\n        // استخدام setInterval لضمان التحديث البرمجي المستمر\n        this.timer = setInterval(() => {\n            this.update();\n        }, 1000);\n        \n        // التحديث الأول فوراً\n        this.update();\n    },\n\n    // الدالة البرمجية التي تحسب وتستخرج الوقت الحالي بدقة\n    update: function() {\n        const now = new Date();\n        \n        // تخزين الوقت في كائن برمجى (Object) بدلاً من نص عادي\n        const timeData = {\n            hours: String(now.getHours()).padStart(2, \'0\'),\n            minutes: String(now.getMinutes()).padStart(2, \'0\'),\n            seconds: String(now.getSeconds()).padStart(2, \'0\'),\n            timestamp: now.getTime() // الطابع الزمني الدقيق بالملي ثانية\n        };\n\n        // معالجة البيانات برمجياً (مثلاً طباعتها أو إرسالها لدالة أخرى)\n        this.logTime(timeData);\n    },\n\n    // دالة لمعالجة المخرجات برمجياً\n    logTime: function(data) {\n        // عرض الوقت بصيغة برمجية محددة (Formatted Logic)\n        const formattedTime = `${data.hours}:${data.minutes}:${data.seconds}`;\n        console.clear(); // تنظيف القنصل لإظهار الوقت الحالي فقط\n        console.log(`[برمجة الساعة] الوقت الحالي: ${formattedTime}`);\n        console.log(`[البيانات الخام]:`, data);\n    },\n\n    // دالة لإيقاف الساعة برمجياً\n    stop: function() {\n        clearInterval(this.timer);\n        console.log(\"تم إيقاف الساعة برمجياً.\");\n    }\n};\n\n// تشغيل الساعة برمجياً\nDigitalClock.start();', 0, NULL, '2026-03-29 20:35:53'),
(10, 1, 23, 'SELECT \n    p.product_id,\n    p.product_name,\n    -- الخطوة 2 (مستوى المجموعة): جمع نتائج الضرب (الكمية × السعر) لكل منتج\n    SUM(s.quantity * s.unit_price) AS total_revenue\nFROM \n    products p\n-- استخدام INNER JOIN لربط جدول المنتجات بجدول المبيعات (العمليات)\nINNER JOIN \n    sales_operations s ON p.product_id = s.product_id\nGROUP BY \n    -- تجميع البيانات بناءً على هوية المنتج لضمان دقة المجموعة\n    p.product_id, \n    p.product_name\nORDER BY \n    -- ترتيب النتائج تنازلياً لإظهار أعلى القيم في المقدمة\n    total_revenue DESC\n-- الخطوة النهائية: عرض أعلى 5 منتجات مبيعاً فقط\nLIMIT 5;', 0, NULL, '2026-03-29 21:01:15'),
(11, 1, 3, '<!DOCTYPE html>\n<html lang=\"ar\" dir=\"rtl\">\n<head>\n    <meta charset=\"UTF-8\">\n    <style>\n        :root { --primary: #2563eb; --bg: #0f172a; --btn: #1e293b; --text: #f8fafc; }\n        body { background: var(--bg); display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; font-family: sans-serif; }\n        .calc-box { background: #1e293b; padding: 25px; border-radius: 24px; box-shadow: 0 20px 50px rgba(0,0,0,0.5); width: 350px; }\n        #screen { width: 100%; height: 70px; background: #0f172a; border: none; border-radius: 12px; color: var(--text); font-size: 1.8rem; text-align: left; padding: 15px; margin-bottom: 20px; box-sizing: border-box; }\n        .grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }\n        button { padding: 20px; border: none; border-radius: 12px; background: var(--btn); color: var(--text); font-size: 1.2rem; cursor: pointer; transition: 0.2s; }\n        button:hover { background: #334155; }\n        .op { background: var(--primary); }\n        .adv { background: #7c3aed; }\n        .clear { background: #ef4444; grid-column: span 2; }\n        .equal { background: #10b981; grid-column: span 2; }\n    </style>\n</head>\n<body>\n\n<div class=\"calc-box\">\n    <input type=\"text\" id=\"screen\" readonly placeholder=\"0\">\n    <div class=\"grid\">\n        <button class=\"clear\" onclick=\"Calculator.clear()\">AC</button>\n        <button class=\"adv\" onclick=\"Calculator.append(\'^\')\">^</button>\n        <button class=\"op\" onclick=\"Calculator.append(\'/\')\">÷</button>\n\n        <button onclick=\"Calculator.append(\'7\')\">7</button>\n        <button onclick=\"Calculator.append(\'8\')\">8</button>\n        <button onclick=\"Calculator.append(\'9\')\">9</button>\n        <button class=\"op\" onclick=\"Calculator.append(\'*\')\">×</button>\n\n        <button onclick=\"Calculator.append(\'4\')\">4</button>\n        <button onclick=\"Calculator.append(\'5\')\">5</button>\n        <button onclick=\"Calculator.append(\'6\')\">6</button>\n        <button class=\"op\" onclick=\"Calculator.append(\'-\')\">-</button>\n\n        <button onclick=\"Calculator.append(\'1\')\">1</button>\n        <button onclick=\"Calculator.append(\'2\')\">2</button>\n        <button onclick=\"Calculator.append(\'3\')\">3</button>\n        <button class=\"op\" onclick=\"Calculator.append(\'+\')\">+</button>\n\n        <button onclick=\"Calculator.append(\'0\')\">0</button>\n        <button onclick=\"Calculator.append(\'.\')\">.</button>\n        <button class=\"adv\" onclick=\"Calculator.sqrt()\">√</button>\n        <button class=\"adv\" onclick=\"Calculator.append(\'%\')\">%</button>\n\n        <button class=\"equal\" onclick=\"Calculator.solve()\">=</button>\n    </div>\n</div>\n\n<script>\n/**\n * كائن الآلة الحاسبة البرمجي - يفصل بين المنطق والعرض\n */\nconst Calculator = {\n    currentValue: \"\",\n    screen: document.getElementById(\'screen\'),\n\n    append(char) {\n        this.currentValue += char;\n        this.updateScreen();\n    },\n\n    clear() {\n        this.currentValue = \"\";\n        this.updateScreen();\n    },\n\n    updateScreen() {\n        this.screen.value = this.currentValue || \"0\";\n    },\n\n    // الخطوة 1 والخطوة 2: معالجة العمليات المتقدمة والأساسية برمجياً\n    solve() {\n        try {\n            let expression = this.currentValue;\n\n            // الخطوة 1: مستوى العملية (Row-level Processing)\n            // تحويل الرموز النصية إلى معاملات برمجية محددة\n            expression = expression.replace(/\\^/g, \'**\'); // الأس\n            expression = expression.replace(/%/g, \'/100\'); // النسبة المئوية\n\n            // الخطوة 2: مستوى الحساب (Group/Logic Aggregation)\n            // استخدام Function بدلاً من eval لضمان بيئة تشغيل معزولة وأكثر أماناً\n            const result = new Function(`return ${expression}`)();\n\n            if (isNaN(result) || !isFinite(result)) {\n                throw new Error(\"Invalid Math\");\n            }\n\n            this.currentValue = result.toString();\n            this.updateScreen();\n        } catch (e) {\n            this.screen.value = \"Error\";\n            this.currentValue = \"\";\n        }\n    },\n\n    sqrt() {\n        try {\n            // معالجة الجذر كعملية منفصلة لضمان الدقة قبل التجميع\n            const val = parseFloat(this.currentValue);\n            if (val < 0) throw new Error();\n            this.currentValue = Math.sqrt(val).toString();\n            this.updateScreen();\n        } catch (e) {\n            this.screen.value = \"Error\";\n            this.currentValue = \"\";\n        }\n    }\n};\n</script>\n</body>\n</html>', 1, '2026-03-29 18:10:52', '2026-03-29 21:03:45'),
(12, 1, 26, '<html>', 0, NULL, '2026-03-29 21:12:23');

-- --------------------------------------------------------

--
-- Table structure for table `community_posts`
--

DROP TABLE IF EXISTS `community_posts`;
CREATE TABLE `community_posts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags`)),
  `likes_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `comments_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELATIONSHIPS FOR TABLE `community_posts`:
--   `user_id`
--       `users` -> `id`
--

-- --------------------------------------------------------

--
-- Table structure for table `community_post_comments`
--

DROP TABLE IF EXISTS `community_post_comments`;
CREATE TABLE `community_post_comments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `post_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELATIONSHIPS FOR TABLE `community_post_comments`:
--   `post_id`
--       `community_posts` -> `id`
--   `user_id`
--       `users` -> `id`
--

-- --------------------------------------------------------

--
-- Table structure for table `community_post_likes`
--

DROP TABLE IF EXISTS `community_post_likes`;
CREATE TABLE `community_post_likes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `post_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELATIONSHIPS FOR TABLE `community_post_likes`:
--   `post_id`
--       `community_posts` -> `id`
--   `user_id`
--       `users` -> `id`
--

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
CREATE TABLE `courses` (
  `id` int(11) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `main_points` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `category` varchar(100) DEFAULT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `level` varchar(50) DEFAULT 'Beginner',
  `total_enrollments` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELATIONSHIPS FOR TABLE `courses`:
--

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `title`, `description`, `main_points`, `updated_at`, `category`, `logo_path`, `created_at`, `level`, `total_enrollments`, `is_active`) VALUES
(6, 'React', 'مكتبة JavaScript لبناء واجهات المستخدم، تعلم بناء تطبيقات ويب معقدة وقابلة للتطوير.\r\n\r\nfrontend', NULL, '2026-01-24 15:17:46', 'frontend', NULL, '2025-10-11 20:27:44', 'Beginner', 0, 1),
(8, 'html', 'لغة ترميز النصوص التشعبية، العمود الفقري لأي موقع ويب. تعلم بناء الهيكل الأساسي للصفحات.', NULL, '2026-05-24 22:12:52', 'frontend', NULL, '2025-10-11 21:10:33', 'Beginner', 3, 1),
(10, 'css', 'sadadsa', 'asda sda assdadas\r\nasdassdasd\r\ndassadsasadas', '2026-05-24 22:12:52', 'basics', NULL, '2025-10-12 21:38:40', 'Beginner', 2, 1),
(23, 'java', 'kewdlkjelk', 'kjr3lf3r\r\nref\r\nr4f4ef\r\n4rfg', '2026-05-24 22:12:52', 'frontend', NULL, '2026-01-23 15:59:44', 'مبتدئ-متوسط', 1, 1),
(26, 'php', 'ffff', 'fff', '2026-03-26 18:54:55', 'backend', NULL, '2026-03-26 18:54:55', 'أساسي', 0, 1);

-- --------------------------------------------------------

--
-- Stand-in structure for view `course_stats`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `course_stats`;
CREATE TABLE `course_stats` (
`id` int(11) unsigned
,`title` varchar(255)
,`category` varchar(100)
,`level` varchar(50)
,`is_active` tinyint(1)
,`created_at` timestamp
,`lesson_count` bigint(21)
,`enrolled_users` bigint(21)
,`completed_users` bigint(21)
,`avg_completion_rate` decimal(13,2)
,`total_views` decimal(32,0)
,`assignment_count` bigint(21)
,`last_activity` timestamp
);

-- --------------------------------------------------------

--
-- Table structure for table `examples`
--

DROP TABLE IF EXISTS `examples`;
CREATE TABLE `examples` (
  `id` int(11) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `category` enum('frontend','backend','mobile','algorithms') NOT NULL,
  `difficulty` enum('beginner','intermediate','advanced') NOT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `code_snippet` text NOT NULL,
  `code_language` varchar(50) NOT NULL,
  `technologies` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`technologies`)),
  `demo_url` varchar(500) DEFAULT NULL,
  `requires_special_env` tinyint(1) DEFAULT 0,
  `special_env_message` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELATIONSHIPS FOR TABLE `examples`:
--

--
-- Dumping data for table `examples`
--

INSERT INTO `examples` (`id`, `title`, `description`, `category`, `difficulty`, `image_url`, `code_snippet`, `code_language`, `technologies`, `demo_url`, `requires_special_env`, `special_env_message`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'آلة حاسبة تفاعلية', 'آلة حاسبة تفاعلية باستخدام HTML, CSS و JavaScript مع تصميم عصري', 'frontend', 'beginner', 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80', '// دالة لإضافة الأرقام\r\nfunction addNumber(num) {\r\n    document.getElementById(\'display\').value += num;\r\n}\r\n\r\n// دالة لإجراء العمليات الحسابية\r\nfunction calculate() {\r\n    let display = document.getElementById(\'display\');\r\n    try {\r\n        display.value = eval(display.value);\r\n    } catch (e) {\r\n        display.value = \'Error\';\r\n    }\r\n}\r\n\r\n// دالة مسح الشاشة\r\nfunction clearDisplay() {\r\n    document.getElementById(\'display\').value = \'\';\r\n}', 'javascript', '[\"HTML\",\"CSS\",\"JavaScript\"]', NULL, 0, NULL, 1, '2025-11-27 17:06:54', '2025-11-27 17:06:54'),
(2, 'نظام إدارة المستخدمين', 'نظام لإدارة المستخدمين باستخدام Node.js و Express مع قاعدة بيانات MongoDB', 'backend', 'intermediate', 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80', '// نموذج المستخدم في MongoDB\r\nconst userSchema = new mongoose.Schema({\r\n    name: { type: String, required: true },\r\n    email: { type: String, required: true, unique: true },\r\n    password: { type: String, required: true },\r\n    role: { type: String, default: \'user\' }\r\n});\r\n\r\n// إنشاء مستخدم جديد\r\napp.post(\'/api/users\', async (req, res) => {\r\n    try {\r\n        const user = new User(req.body);\r\n        await user.save();\r\n        res.status(201).json(user);\r\n    } catch (error) {\r\n        res.status(400).json({ error: error.message });\r\n    }\r\n});', 'javascript', '[\"Node.js\",\"Express\",\"MongoDB\"]', NULL, 0, NULL, 1, '2025-11-27 17:06:54', '2025-11-27 17:06:54'),
(3, 'تطبيق قائمة المهام', 'تطبيق Flutter لإدارة المهام اليومية مع إمكانية الإضافة والحذف والتعديل', 'mobile', 'beginner', 'https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80', '// نموذج المهمة\r\nclass Task {\r\n  int id;\r\n  String title;\r\n  bool isCompleted;\r\n\r\n  Task({this.id, this.title, this.isCompleted = false});\r\n\r\n  Map<String, dynamic> toMap() {\r\n    return {\r\n      \'id\': id,\r\n      \'title\': title,\r\n      \'isCompleted\': isCompleted ? 1 : 0,\r\n    };\r\n  }\r\n}\r\n\r\n// إضافة مهمة جديدة\r\nvoid addTask(String taskTitle) {\r\n  final task = Task(title: taskTitle);\r\n  _tasks.add(task);\r\n  notifyListeners();\r\n}', 'dart', '[\"Flutter\",\"Dart\",\"SQLite\"]', NULL, 0, NULL, 1, '2025-11-27 17:06:54', '2025-11-27 17:06:54'),
(4, 'خوارزمية البحث الثنائي', 'تنفيذ خوارزمية البحث الثنائي في JavaScript مع شرح مفصل لكيفية عملها', 'algorithms', 'intermediate', 'https://images.unsplash.com/photo-1509228468518-180dd4864904?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80', '// خوارزمية البحث الثنائي\r\nfunction binarySearch(arr, target) {\r\n    let left = 0;\r\n    let right = arr.length - 1;\r\n\r\n    while (left <= right) {\r\n        // حساب منتصف المصفوفة\r\n        let mid = Math.floor((left + right) / 2);\r\n\r\n        // إذا كان العنصر في المنتصف هو الهدف\r\n        if (arr[mid] === target) {\r\n            return mid;\r\n        }\r\n\r\n        // إذا كان الهدف أصغر، نبحث في النصف الأيسر\r\n        if (target < arr[mid]) {\r\n            right = mid - 1;\r\n        }\r\n        // إذا كان الهدف أكبر، نبحث في النصف الأيمن\r\n        else {\r\n            left = mid + 1;\r\n        }\r\n    }\r\n\r\n    // إذا لم يتم العثور على العنصر\r\n    return -1;\r\n}', 'javascript', '[\"JavaScript\",\"\\u0627\\u0644\\u062e\\u0648\\u0627\\u0631\\u0632\\u0645\\u064a\\u0627\\u062a\"]', NULL, 0, NULL, 1, '2025-11-27 17:06:54', '2025-11-27 17:06:54'),
(5, 'test', 'test', 'frontend', '', '', '<!DOCTYPE html>\n<html lang=\"ar\" dir=\"rtl\">\n  <head>\n    <meta charset=\"UTF-8\" />\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\" />\n    <title>Navbar Only - Programming Academy</title>\n    <link\n      rel=\"stylesheet\"\n      href=\"https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css\"\n    />\n    <style>\n      :root {\n        --primary: #4361ee;\n        --secondary: #3a0ca3;\n        --accent: #4cc9f0;\n        --success: #4ade80;\n        --warning: #f59e0b;\n        --danger: #ef4444;\n        --dark: #1e293b;\n        --light: #f8fafc;\n        --gray: #64748b;\n        --card-bg: #ffffff;\n        --shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);\n        --transition: all 0.3s ease;\n        --border-radius-small: 5px;\n      }\n\n      * {\n        margin: 0;\n        padding: 0;\n        box-sizing: border-box;\n        font-family: \"Segoe UI\", Tahoma, Geneva, Verdana, sans-serif;\n      }\n\n      body {\n        background-color: var(--light);\n        color: var(--dark);\n        line-height: 1.6;\n        min-height: 100vh;\n        padding-top: 80px;\n      }\n\n      .container {\n        width: 100%;\n        max-width: 1200px;\n        margin: 0 auto;\n        padding: 0 20px;\n      }\n\n      /* Header & Navbar */\n      header {\n        background: linear-gradient(135deg, var(--primary), var(--secondary));\n        color: white;\n        position: fixed;\n        width: 100%;\n        top: 0;\n        z-index: 1000;\n        box-shadow: var(--shadow);\n      }\n\n      .navbar {\n        display: flex;\n        justify-content: space-between;\n        align-items: center;\n        padding: 1rem 2rem;\n      }\n\n      .logo {\n        display: flex;\n        align-items: center;\n        gap: 10px;\n      }\n\n      .logo-img {\n        width: 50px;\n        height: 50px;\n        background: linear-gradient(45deg, var(--accent), var(--primary));\n        border-radius: 50%;\n        display: flex;\n        align-items: center;\n        justify-content: center;\n        color: white;\n        font-size: 1.5rem;\n        animation: rotate 10s linear infinite;\n      }\n\n      @keyframes rotate {\n        0% {\n          transform: rotate(0deg);\n        }\n        100% {\n          transform: rotate(360deg);\n        }\n      }\n\n      .logo h1 {\n        font-size: 1.5rem;\n        white-space: nowrap;\n      }\n\n      .nav-links {\n        display: flex;\n        list-style: none;\n        gap: 1.5rem;\n        align-items: center;\n      }\n\n      .nav-link {\n        color: white;\n        text-decoration: none;\n        font-weight: 500;\n        padding: 0.5rem 1rem;\n        border-radius: 5px;\n        transition: var(--transition);\n        position: relative;\n        display: block;\n      }\n\n      .nav-link:hover {\n        background-color: rgba(255, 255, 255, 0.1);\n      }\n\n      .dropdown {\n        position: relative;\n      }\n\n      .dropdown-menu {\n        position: absolute;\n        top: 100%;\n        right: 0;\n        background: white;\n        min-width: 200px;\n        box-shadow: var(--shadow);\n        border-radius: 8px;\n        opacity: 0;\n        visibility: hidden;\n        transform: translateY(10px);\n        transition: var(--transition);\n        z-index: 100;\n      }\n\n      .dropdown:hover .dropdown-menu {\n        opacity: 1;\n        visibility: visible;\n        transform: translateY(0);\n      }\n\n      .dropdown-menu a {\n        display: block;\n        padding: 0.8rem 1rem;\n        color: var(--dark);\n        text-decoration: none;\n        border-bottom: 1px solid #eee;\n        transition: var(--transition);\n      }\n\n      .dropdown-menu a:hover {\n        background-color: #f5f5f5;\n        color: var(--primary);\n      }\n\n      .dropdown-menu a:last-child {\n        border-bottom: none;\n      }\n\n      .login-btn {\n        background-color: var(--accent);\n        border-radius: 50px;\n        padding: 0.5rem 1.5rem;\n      }\n\n      .login-btn:hover {\n        background-color: #3ab0d9;\n      }\n\n      /* User Profile Dropdown */\n      .user-profile {\n        position: relative;\n        display: flex;\n        align-items: center;\n        gap: 0.5rem;\n        cursor: pointer;\n      }\n\n      .user-avatar-small {\n        width: 40px;\n        height: 40px;\n        border-radius: 50%;\n        background: linear-gradient(135deg, var(--accent), var(--primary));\n        display: flex;\n        align-items: center;\n        justify-content: center;\n        color: white;\n        font-weight: bold;\n        border: 2px solid white;\n      }\n\n      .user-dropdown {\n        position: absolute;\n        top: 100%;\n        left: 0;\n        background: white;\n        min-width: 200px;\n        box-shadow: var(--shadow);\n        border-radius: 10px;\n        padding: 1rem;\n        opacity: 0;\n        visibility: hidden;\n        transform: translateY(10px);\n        transition: var(--transition);\n        z-index: 1000;\n      }\n\n      .user-profile:hover .user-dropdown {\n        opacity: 1;\n        visibility: visible;\n        transform: translateY(0);\n      }\n\n      .user-info {\n        display: flex;\n        align-items: center;\n        gap: 0.5rem;\n        padding-bottom: 0.5rem;\n        border-bottom: 1px solid #eee;\n        margin-bottom: 0.5rem;\n      }\n\n      .user-name {\n        font-weight: 600;\n        color: var(--dark);\n      }\n\n      .user-email {\n        font-size: 0.8rem;\n        color: var(--gray);\n      }\n\n      .logout-btn {\n        background: var(--danger);\n        color: white;\n        border: none;\n        padding: 0.5rem 1rem;\n        border-radius: var(--border-radius-small);\n        cursor: pointer;\n        width: 100%;\n        transition: var(--transition);\n      }\n\n      .logout-btn:hover {\n        background: #dc2626;\n      }\n\n      /* ============================================\n         MOBILE MENU STYLES\n         ============================================ */\n\n      .mobile-menu-btn {\n        display: none;\n        flex-direction: column;\n        cursor: pointer;\n        z-index: 1001;\n      }\n\n      .mobile-menu-btn span {\n        width: 25px;\n        height: 3px;\n        background: white;\n        margin: 3px 0;\n        transition: var(--transition);\n      }\n\n      .mobile-menu-btn.active span:nth-child(1) {\n        transform: rotate(45deg) translate(5px, 5px);\n      }\n\n      .mobile-menu-btn.active span:nth-child(2) {\n        opacity: 0;\n      }\n\n      .mobile-menu-btn.active span:nth-child(3) {\n        transform: rotate(-45deg) translate(7px, -6px);\n      }\n\n      /* Mobile Menu Overlay */\n      .mobile-menu-overlay {\n        position: fixed;\n        top: 0;\n        right: 0;\n        width: 100%;\n        height: 100%;\n        background: rgba(0, 0, 0, 0.7);\n        z-index: 999;\n        opacity: 0;\n        visibility: hidden;\n        transition: var(--transition);\n      }\n\n      .mobile-menu-overlay.active {\n        opacity: 1;\n        visibility: visible;\n      }\n\n      .mobile-nav-links {\n        position: fixed;\n        top: 0;\n        right: -100%;\n        width: 80%;\n        max-width: 300px;\n        height: 100%;\n        background: white;\n        padding: 2rem;\n        overflow-y: auto;\n        transition: var(--transition);\n        z-index: 1000;\n        box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1);\n      }\n\n      .mobile-nav-links.active {\n        right: 0;\n      }\n\n      .mobile-nav-header {\n        display: flex;\n        justify-content: space-between;\n        align-items: center;\n        margin-bottom: 2rem;\n        padding-bottom: 1rem;\n        border-bottom: 1px solid #eee;\n      }\n\n      .mobile-nav-header h2 {\n        color: var(--primary);\n      }\n\n      .close-mobile-menu {\n        background: none;\n        border: none;\n        font-size: 1.5rem;\n        color: var(--dark);\n        cursor: pointer;\n      }\n\n      .mobile-nav-links ul {\n        list-style: none;\n      }\n\n      .mobile-nav-links li {\n        margin-bottom: 1rem;\n      }\n\n      .mobile-nav-links a {\n        display: block;\n        padding: 0.8rem 1rem;\n        color: var(--dark);\n        text-decoration: none;\n        border-radius: 5px;\n        transition: var(--transition);\n      }\n\n      .mobile-nav-links a:hover {\n        background-color: #f0f4ff;\n        color: var(--primary);\n      }\n\n      .mobile-dropdown {\n        position: relative;\n      }\n\n      .mobile-dropdown-toggle {\n        display: flex;\n        justify-content: space-between;\n        align-items: center;\n        cursor: pointer;\n        padding: 0.8rem 1rem;\n        color: var(--dark);\n        text-decoration: none;\n        border-radius: 5px;\n        transition: var(--transition);\n      }\n\n      .mobile-dropdown-toggle:hover {\n        background-color: #f0f4ff;\n        color: var(--primary);\n      }\n\n      .mobile-dropdown-menu {\n        max-height: 0;\n        overflow: hidden;\n        transition: max-height 0.3s ease;\n        padding-right: 1rem;\n      }\n\n      .mobile-dropdown-menu.active {\n        max-height: 500px;\n      }\n\n      .mobile-dropdown-menu a {\n        padding: 0.6rem 1rem;\n        font-size: 0.9rem;\n        border-bottom: 1px solid #f0f0f0;\n        color: var(--dark);\n      }\n\n      .user-profile-mobile {\n        display: flex;\n        flex-direction: column;\n        gap: 1rem;\n        padding: 1rem;\n\n        border-radius: 10px;\n        margin-top: 1rem;\n      }\n\n      .user-info-mobile {\n        display: flex;\n        align-items: center;\n        gap: 0.5rem;\n      }\n\n      /* Responsive Design */\n      @media (max-width: 992px) {\n        .nav-links {\n          display: none;\n        }\n\n        .mobile-menu-btn {\n          display: flex;\n        }\n\n        .mobile-nav-links.active {\n          right: 0;\n        }\n\n        .dropdown-content {\n          position: static;\n          opacity: 1;\n          visibility: visible;\n          transform: none;\n          box-shadow: none;\n          background: rgba(67, 97, 238, 0.05);\n          margin-top: 0.5rem;\n          border-radius: 8px;\n        }\n      }\n\n      @media (max-width: 768px) {\n        .navbar {\n          padding: 1rem;\n        }\n\n        .logo h1 {\n          font-size: 1rem;\n        }\n      }\n\n      /* Demo Content */\n      .demo-content {\n        text-align: center;\n        padding: 2rem;\n      }\n\n      .demo-content h1 {\n        color: var(--primary);\n        margin-bottom: 1rem;\n      }\n\n      .demo-content p {\n        font-size: 1.1rem;\n        color: var(--dark);\n        margin-bottom: 2rem;\n      }\n\n      .demo-info {\n        background: white;\n        border-radius: 10px;\n        padding: 2rem;\n        box-shadow: var(--shadow);\n        max-width: 600px;\n        margin: 0 auto;\n      }\n\n      .demo-info h2 {\n        color: var(--primary);\n        margin-bottom: 1rem;\n      }\n\n      .demo-info ul {\n        text-align: right;\n        list-style: none;\n      }\n\n      .demo-info li {\n        padding: 0.5rem 0;\n        border-bottom: 1px solid #eee;\n      }\n\n      .demo-info li:last-child {\n        border-bottom: none;\n      }\n\n      .demo-info li i {\n        margin-left: 10px;\n        color: var(--accent);\n      }\n    </style>\n  </head>\n  <body>\n    <!-- Navbar -->\n    <header>\n      <nav class=\"navbar\">\n        <div class=\"logo\">\n          <div class=\"logo-img\">\n            <i class=\"fas fa-code\"></i>\n          </div>\n          <h1>أكاديمية البرمجة المتكاملة</h1>\n        </div>\n\n        <ul class=\"nav-links\">\n          <li><a href=\"index.html\" class=\"nav-link\">الرئيسية</a></li>\n          <li class=\"dropdown\">\n            <a href=\"#\" class=\"nav-link\"\n              >تعلم الآن <i class=\"fas fa-chevron-down\"></i\n            ></a>\n            <div class=\"dropdown-menu\">\n              <a href=\"examples.html\">أمثلة وتطبيقات وشروحات</a>\n              <a href=\"#\">التحديات البرمجية</a>\n              <a href=\"proplemsolving.html\">مواقع حل المشاكل البرمجية</a>\n            </div>\n          </li>\n          <li><a href=\"./roadmap.html\" class=\"nav-link\">خارطة الطريق</a></li>\n          <li class=\"dropdown\">\n            <a href=\"#\" class=\"nav-link\"\n              >الكورسات <i class=\"fas fa-chevron-down\"></i\n            ></a>\n            <div class=\"dropdown-menu\">\n              <a href=\"path.php?path=basics\">مسار أساسيات البرمجة</a>\n              <a href=\"path.php?path=frontend\">مسار Frontend Developer</a>\n              <a href=\"path.php?path=backend\">مسار Backend Developer</a>\n            </div>\n          </li>\n          <li>\n            <a href=\"./projects.html\" class=\"nav-link\">التكليفات والمشاريع</a>\n          </li>\n          <li class=\"user-profile\" id=\"userProfile\" style=\"display: none\">\n            <div class=\"user-avatar-small\" id=\"userAvatarSmall\">م</div>\n            <div class=\"user-dropdown\">\n              <div class=\"user-info\">\n                <div class=\"user-avatar-small\" id=\"dropdownAvatar\">م</div>\n                <div>\n                  <div class=\"user-name\" id=\"dropdownUserName\"></div>\n                  <div class=\"user-email\" id=\"dropdownUserEmail\"></div>\n                </div>\n              </div>\n              <a\n                href=\"profile.html\"\n                class=\"nav-link\"\n                style=\"\n                  display: block;\n                  text-align: right;\n                  margin-bottom: 0.5rem;\n                  color: black;\n                  font-size: large;\n                \"\n                ><i\n                  class=\"fas fa-user-circle profile-icon\"\n                  style=\"margin-left: 10%\"\n                ></i\n                >الملف الشخصي</a\n              >\n              <button class=\"logout-btn\" id=\"logoutBtn\">تسجيل الخروج</button>\n            </div>\n          </li>\n          <li id=\"loginButton\">\n            <a href=\"./login1.html\" class=\"nav-link login-btn\">تسجيل الدخول</a>\n          </li>\n        </ul>\n\n        <div class=\"mobile-menu-btn\" id=\"mobileMenuBtn\">\n          <span></span>\n          <span></span>\n          <span></span>\n        </div>\n      </nav>\n    </header>\n\n    <!-- Mobile Menu Overlay -->\n    <div class=\"mobile-menu-overlay\" id=\"mobileMenuOverlay\"></div>\n\n    <!-- Mobile Navigation -->\n    <div class=\"mobile-nav-links\" id=\"mobileNavLinks\">\n      <div class=\"mobile-nav-header\">\n        <h2>القائمة</h2>\n      </div>\n      <ul>\n        <li><a href=\"index.html\" class=\"nav-link\">الرئيسية</a></li>\n        <li class=\"mobile-dropdown\">\n          <div class=\"mobile-dropdown-toggle\">\n            <a href=\"#\" class=\"nav-link\">تعلم الآن</a>\n            <i class=\"fas fa-chevron-down\"></i>\n          </div>\n          <div class=\"mobile-dropdown-menu\">\n            <a href=\"examples.html\">أمثلة وتطبيقات وشروحات</a>\n            <a href=\"#\">التحديات البرمجية</a>\n            <a href=\"proplemsolving.html\">مواقع حل المشاكل البرمجية</a>\n          </div>\n        </li>\n        <li><a href=\"./roadmap.html\" class=\"nav-link\">خارطة الطريق</a></li>\n        <li class=\"mobile-dropdown\">\n          <div class=\"mobile-dropdown-toggle\">\n            <a href=\"#\" class=\"nav-link\">الكورسات</a>\n            <i class=\"fas fa-chevron-down\"></i>\n          </div>\n          <div class=\"mobile-dropdown-menu\">\n            <a href=\"path.php?path=basics\">مسار أساسيات البرمجة</a>\n            <a href=\"path.php?path=frontend\">مسار Frontend Developer</a>\n            <a href=\"path.php?path=backend\">مسار Backend Developer</a>\n          </div>\n        </li>\n        <li><a href=\"#\" class=\"nav-link\">التكليفات والمشاريع</a></li>\n        <li\n          class=\"user-profile-mobile\"\n          id=\"userProfileMobile\"\n          style=\"display: none\"\n        >\n          <div class=\"user-info-mobile\">\n            <div class=\"user-avatar-small\" id=\"mobileAvatar\"></div>\n            <div>\n              <div class=\"user-name\" id=\"mobileUserName\"></div>\n              <div class=\"user-email\" id=\"mobileUserEmail\"></div>\n            </div>\n          </div>\n          <a href=\"profile.html\" class=\"nav-link\">\n            <i class=\"fas fa-user-circle\"></i> الملف الشخصي\n          </a>\n          <button class=\"logout-btn\" id=\"mobileLogoutBtn\">تسجيل الخروج</button>\n        </li>\n        <li id=\"loginButtonMobile\">\n          <a href=\"login1.html\" class=\"nav-link login-btn\">تسجيل الدخول</a>\n        </li>\n      </ul>\n    </div>\n\n    <!-- Demo Content -->\n    <div class=\"container\">\n      <div class=\"demo-content\">\n        <h1>Navbar Demo</h1>\n        <p>هذه الصفحة تحتوي على navbar فقط من صفحة حل المشاكل البرمجية</p>\n\n        <div class=\"demo-info\">\n          <h2>المميزات:</h2>\n          <ul>\n            <li><i class=\"fas fa-desktop\"></i> تصميم متجاوب للشاشات الكبيرة</li>\n            <li>\n              <i class=\"fas fa-mobile-alt\"></i> قائمة متحركة للأجهزة المحمولة\n            </li>\n            <li><i class=\"fas fa-user\"></i> نظام إدارة المستخدمين</li>\n            <li><i class=\"fas fa-bars\"></i> قوائم منسدلة تفاعلية</li>\n            <li><i class=\"fas fa-language\"></i> دعم اللغة العربية (RTL)</li>\n            <li><i class=\"fas fa-palette\"></i> تصميم حديث وأنيق</li>\n          </ul>\n        </div>\n      </div>\n    </div>\n\n    <script>\n      // Initialize user interface on page load\n      document.addEventListener(\"DOMContentLoaded\", function () {\n        updateUserInterface();\n\n        // Mobile Menu Toggle\n        const mobileMenuBtn = document.getElementById(\"mobileMenuBtn\");\n        const mobileMenuOverlay = document.getElementById(\"mobileMenuOverlay\");\n        const mobileNavLinks = document.getElementById(\"mobileNavLinks\");\n\n        function toggleMobileMenu() {\n          const isActive = mobileMenuBtn.classList.contains(\"active\");\n          if (isActive) {\n            closeMobileMenuFunc();\n          } else {\n            openMobileMenu();\n          }\n        }\n\n        function openMobileMenu() {\n          mobileMenuBtn.classList.add(\"active\");\n          mobileMenuOverlay.classList.add(\"active\");\n          mobileNavLinks.classList.add(\"active\");\n          document.body.style.overflow = \"hidden\";\n        }\n\n        function closeMobileMenuFunc() {\n          mobileMenuBtn.classList.remove(\"active\");\n          mobileMenuOverlay.classList.remove(\"active\");\n          mobileNavLinks.classList.remove(\"active\");\n          document.body.style.overflow = \"auto\";\n        }\n\n        if (mobileMenuBtn) {\n          mobileMenuBtn.addEventListener(\"click\", toggleMobileMenu);\n        }\n        if (mobileMenuOverlay) {\n          mobileMenuOverlay.addEventListener(\"click\", closeMobileMenuFunc);\n        }\n\n        // Mobile Dropdown Toggle\n        document\n          .querySelectorAll(\".mobile-dropdown-toggle\")\n          .forEach((toggle) => {\n            toggle.addEventListener(\"click\", function (e) {\n              e.preventDefault();\n              const menu = this.nextElementSibling;\n              const icon = this.querySelector(\"i\");\n              menu.classList.toggle(\"active\");\n              if (menu.classList.contains(\"active\")) {\n                icon.classList.remove(\"fa-chevron-down\");\n                icon.classList.add(\"fa-chevron-up\");\n              } else {\n                icon.classList.remove(\"fa-chevron-up\");\n                icon.classList.add(\"fa-chevron-down\");\n              }\n            });\n          });\n\n        // Mobile Logout\n        const mobileLogoutBtn = document.getElementById(\"mobileLogoutBtn\");\n        if (mobileLogoutBtn) {\n          mobileLogoutBtn.addEventListener(\"click\", function () {\n            closeMobileMenuFunc(); // Close menu first\n            const currentUser = JSON.parse(localStorage.getItem(\"currentUser\"));\n            if (currentUser) {\n              currentUser.isLoggedIn = false;\n              localStorage.setItem(\"currentUser\", JSON.stringify(currentUser));\n            }\n            localStorage.removeItem(\"userData\");\n            updateUserInterface();\n            showNotification(\"تم تسجيل الخروج بنجاح!\", \"success\");\n          });\n        }\n\n        // Dropdown hover functionality\n        const userProfile = document.getElementById(\"userProfile\");\n        const userDropdown = document.querySelector(\".user-dropdown\");\n        let dropdownTimeout;\n\n        function showDropdown() {\n          clearTimeout(dropdownTimeout);\n          if (userDropdown) {\n            userDropdown.style.opacity = \"1\";\n            userDropdown.style.visibility = \"visible\";\n            userDropdown.style.transform = \"translateY(0)\";\n          }\n        }\n\n        function hideDropdown() {\n          dropdownTimeout = setTimeout(() => {\n            if (userDropdown) {\n              userDropdown.style.opacity = \"0\";\n              userDropdown.style.visibility = \"hidden\";\n              userDropdown.style.transform = \"translateY(10px)\";\n            }\n          }, 150); // Small delay to allow moving between elements\n        }\n\n        if (userProfile) {\n          userProfile.addEventListener(\"mouseenter\", showDropdown);\n          userProfile.addEventListener(\"mouseleave\", hideDropdown);\n        }\n\n        if (userDropdown) {\n          userDropdown.addEventListener(\"mouseenter\", showDropdown);\n          userDropdown.addEventListener(\"mouseleave\", hideDropdown);\n        }\n\n        // Logout functionality\n        const logoutBtn = document.getElementById(\"logoutBtn\");\n        if (logoutBtn) {\n          logoutBtn.addEventListener(\"click\", function () {\n            const currentUser = JSON.parse(localStorage.getItem(\"currentUser\"));\n            if (currentUser) {\n              currentUser.isLoggedIn = false;\n              localStorage.setItem(\"currentUser\", JSON.stringify(currentUser));\n            }\n            localStorage.removeItem(\"userData\");\n            updateUserInterface();\n            showNotification(\"تم تسجيل الخروج بنجاح!\", \"success\");\n          });\n        }\n      });\n    </script>\n  </body>\n</html>\n', 'html', '[\"html\",\"css\"]', '', 0, '', 1, '2025-11-27 18:19:11', '2025-12-06 21:07:55'),
(9, 'sasad', 'sdasasd', 'frontend', '', '', 'fqfwqqw', 'dsads', '[]', 'https://www.youtube.com/playlist?list=PLknwEmKsW8Ov6JLhkIO1pOdHHCjdCq-PJ', 0, '', 1, '2025-12-06 21:12:00', '2026-03-26 18:23:32');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELATIONSHIPS FOR TABLE `failed_jobs`:
--

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELATIONSHIPS FOR TABLE `jobs`:
--

-- --------------------------------------------------------

--
-- Table structure for table `lessons`
--

DROP TABLE IF EXISTS `lessons`;
CREATE TABLE `lessons` (
  `id` int(11) NOT NULL,
  `course_id` int(11) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `video_path` varchar(500) DEFAULT NULL,
  `video_mime_type` varchar(100) DEFAULT NULL,
  `resources_code` text DEFAULT NULL COMMENT 'Code snippets and resources for this lesson',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `views` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELATIONSHIPS FOR TABLE `lessons`:
--   `course_id`
--       `courses` -> `id`
--

--
-- Dumping data for table `lessons`
--

INSERT INTO `lessons` (`id`, `course_id`, `title`, `description`, `sort_order`, `video_path`, `video_mime_type`, `resources_code`, `created_at`, `updated_at`, `views`) VALUES
(6, 6, '16_ ال Authentication(720P_60FPS)', ' 1Authentication', 1, 'videos/frontend/react/lesson_6_6934950221ffc.mp4', 'video/mp4', 'wqddddffqw', '2025-10-11 21:08:33', '2025-12-06 20:41:38', 0),
(7, 6, '17.1_ تحدي ال Authentication(720P_60FPS)', ' تحدي الAuthentication', 2, 'videos\\frontend\\react\\video_68eac751686462.99648633.mp4', 'video/mp4', 'dddd', '2025-10-11 21:08:33', '2025-12-06 20:27:09', 0),
(8, 8, '17.2_ حل تحدي ال Authentication(720P_60FPS)', '', 1, 'videos\\frontend\\html\\video_68eac7c9747733.56253928.mp4', 'video/mp4', 'this is where the code will be shown', '2025-10-11 21:10:33', '2026-01-24 17:10:46', 0),
(9, 8, '17_ ال Authentication في جافاسكريبت(720P_60FPS)', '', 2, 'videos\\frontend\\html\\video_68eac7c974d3e7.54987292.mp4', 'video/mp4', NULL, '2025-10-11 21:10:33', '2025-12-06 20:25:46', 0),
(10, 8, '18_ المشروع النهائي _ مقدمة _ ماهو المشروع؟ _ طريقة العمل(720P_60FPS)', '', 3, 'videos\\frontend\\html\\video_68eac7c9755033.33876624.mp4', 'video/mp4', NULL, '2025-10-11 21:10:33', '2025-12-06 20:25:46', 0),
(11, 8, '16_ ال Authentication(720P_60FPS)', '', 4, 'videos\\frontend\\html\\video_68eaca0d9e3ab9.81027761.mp4', 'video/mp4', NULL, '2025-10-11 21:20:13', '2025-12-06 20:25:46', 0),
(12, 8, '17.1_ تحدي ال Authentication(720P_60FPS)', '', 5, 'videos\\frontend\\html\\video_68eaca0d9e9d12.78883036.mp4', 'video/mp4', NULL, '2025-10-11 21:20:13', '2025-12-06 20:25:46', 0),
(15, 10, 'darth-vader-the-dark-lord-star-wars-moewalls-com', 'darth-vader-the-dark-lord-star-wars-moewalls-com', 1, 'videos\\basics\\10\\lesson_68ec1fe0cf1fc0.20635941.mp4', 'video/mp4', NULL, '2025-10-12 21:38:40', '2026-04-03 19:31:18', 1),
(36, 10, 'darth-vader-the-dark-lord-star-wars-moewalls-com', 'afafafa', 2, 'videos\\basics\\10\\lesson_68fbdb0dd218e5.12108151.mp4', 'video/mp4', 'afafdasfa', '2025-10-24 20:01:17', '2025-12-06 20:25:46', 0),
(37, 10, 'darth-vader-the-dark-lord-star-wars-moewalls-com', 'afafafa', 3, 'videos\\basics\\10\\lesson_68fbe4280d4308.87124867.mp4', 'video/mp4', 'afafdasfa', '2025-10-24 20:40:08', '2025-12-06 20:25:46', 0),
(38, 10, 'video_68eac7c9755033.33876624', '', 4, 'videos\\basics\\10\\lesson_68fbe46c9462c9.25494618.mp4', 'video/mp4', 'jkjjkjlklk\r\nll;', '2025-10-24 20:41:16', '2026-04-03 19:43:33', 2),
(45, 6, 'video_68eac7c9755033.33876624', 'ewewge', 0, 'videos\\frontend\\6\\lesson_6934967576f462.74422397.mp4', 'video/mp4', 'gewgew', '2025-12-06 20:47:49', '2025-12-06 20:47:49', 0),
(46, 6, '[Witanime.com] D2S EP 01 FHD', 'fqw', 3, 'videos\\frontend\\6\\lesson_693497391f2ac7.78166929.mp4', 'video/mp4', 'wqfwqf', '2025-12-06 20:51:05', '2025-12-06 20:51:05', 0),
(48, 8, 'video_68eac7c9755033.33876624', 'asfas', 6, 'videos\\frontend\\8\\lesson_6948486131c5b3.18275395.mp4', 'video/mp4', 'fasfasasf', '2025-12-21 19:20:01', '2025-12-21 19:20:01', 0),
(49, 8, 'darth-vader-the-dark-lord-star-wars-moewalls-com', 'sadsadasd', 7, 'videos\\frontend\\8\\lesson_6948486cbb5269.29613256.mp4', 'video/mp4', 'sadasd', '2025-12-21 19:20:12', '2025-12-21 19:20:12', 0),
(50, 8, 'asdsa', 'sadasdasd', 8, 'videos\\frontend\\8\\lesson_694848900f2fc6.94284709.mp4', 'video/mp4', 'asdasddas', '2025-12-21 19:20:48', '2025-12-21 19:20:48', 0),
(51, 10, 'darth-vader-the-dark-lord-star-wars-moewalls-com', '', 5, 'videos\\basics\\10\\lesson_697391c6af94a7.44751697.mp4', 'video/mp4', '', '2026-01-23 15:20:38', '2026-01-23 15:20:38', 0),
(52, 23, 'darth-vader-the-dark-lord-star-wars-moewalls-com', 'dwscecwecwddwwcw', 1, 'videos/frontend/23/lesson_69cff9a723308.mp4', 'video/mp4', 'ewfd3erf3', '2026-01-23 15:59:44', '2026-04-03 14:32:23', 0),
(59, 23, 'darth-vader-the-dark-lord-star-wars-moewalls-com', NULL, 2, 'videos\\frontend\\23\\lesson_69c5c9d20aa7a9.74832366.mp4', 'video/mp4', NULL, '2026-03-26 21:05:38', '2026-03-26 21:06:04', 0);

-- --------------------------------------------------------

--
-- Table structure for table `lesson_comments`
--

DROP TABLE IF EXISTS `lesson_comments`;
CREATE TABLE `lesson_comments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `lesson_id` int(11) NOT NULL,
  `course_id` int(10) UNSIGNED DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELATIONSHIPS FOR TABLE `lesson_comments`:
--   `course_id`
--       `courses` -> `id`
--   `lesson_id`
--       `lessons` -> `id`
--   `user_id`
--       `users` -> `id`
--

-- --------------------------------------------------------

--
-- Table structure for table `lesson_likes`
--

DROP TABLE IF EXISTS `lesson_likes`;
CREATE TABLE `lesson_likes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `lesson_id` int(11) NOT NULL,
  `course_id` int(10) UNSIGNED DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELATIONSHIPS FOR TABLE `lesson_likes`:
--   `course_id`
--       `courses` -> `id`
--   `lesson_id`
--       `lessons` -> `id`
--   `user_id`
--       `users` -> `id`
--

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELATIONSHIPS FOR TABLE `migrations`:
--

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2026_03_24_205150_create_personal_access_tokens_table', 1),
(2, '2026_03_26_222424_add_is_active_to_assignments_table', 2),
(3, '2026_03_26_231114_add_avatar_path_to_users_table', 3),
(4, '2026_03_26_232923_drop_avatar_blob_columns_from_users_table', 4),
(5, '2026_03_26_235003_rename_video_columns_in_lessons_table', 5),
(6, '2026_03_27_100000_drop_is_admin_from_users_table', 6),
(7, '2026_03_27_100001_create_admin_audit_logs_table', 6),
(8, '2026_03_29_202620_add_remember_token_to_users_table', 6),
(9, '2026_04_01_232852_create_jobs_table', 7),
(10, '2026_04_01_232853_create_failed_jobs_table', 8),
(11, '2026_04_01_000001_create_users_table', 9),
(12, '2026_04_01_000002_create_roles_table', 9),
(13, '2026_04_01_000003_create_courses_table', 9),
(14, '2026_04_01_000004_create_platforms_table', 9),
(15, '2026_04_01_000005_create_challenges_table', 9),
(16, '2026_04_01_000006_create_examples_table', 9),
(17, '2026_04_01_000007_create_password_resets_table', 9),
(18, '2026_04_01_000008_create_admin_audit_logs_table', 9),
(19, '2026_04_01_000009_create_academy_reviews_table', 9),
(20, '2026_04_01_000010_create_user_roles_table', 9),
(21, '2026_04_01_000011_create_user_preferences_table', 9),
(22, '2026_04_01_000012_create_lessons_table', 9),
(23, '2026_04_01_000013_create_assignments_table', 9),
(24, '2026_04_01_000014_create_user_challenges_table', 9),
(25, '2026_04_01_000015_create_challenge_attempts_table', 9),
(26, '2026_04_01_000016_create_platform_bookmarks_table', 9),
(27, '2026_04_01_000017_create_platform_ratings_table', 9),
(28, '2026_04_01_000018_create_user_lesson_progress_table', 9),
(29, '2026_04_01_000019_create_user_course_progress_table', 9),
(30, '2026_04_01_000020_create_user_assignments_table', 9),
(31, '2026_04_02_000001_add_performance_indexes', 10),
(32, '2026_04_02_000002_add_soft_deletes_to_users_and_courses', 10),
(33, '2026_04_03_000001_create_ai_conversations_table', 11),
(34, '2026_04_03_000002_create_ai_messages_table', 11),
(35, '2026_04_03_000003_upgrade_ai_messages_content_and_index', 12),
(36, '2026_04_03_000004_fix_user_preferences_enum_columns', 12),
(37, '2026_04_03_000005_add_is_suspended_to_users_table', 13),
(38, '2026_04_03_000006_drop_deleted_at_from_courses_table', 14),
(39, '2026_04_03_000007_add_social_login_to_users_table', 15),
(40, '2026_05_22_100000_create_repositories_table', 16),
(41, '2026_05_22_100001_create_repo_likes_table', 16),
(42, '2026_05_22_100002_create_community_posts_table', 16),
(43, '2026_05_22_100003_create_community_post_comments_table', 16),
(44, '2026_05_22_100004_create_community_post_likes_table', 16),
(45, '2026_05_22_100005_create_lesson_comments_table', 17),
(46, '2026_05_22_100006_create_lesson_likes_table', 17),
(47, '2026_05_22_100007_create_notifications_table', 17),
(48, '2026_05_22_100010_create_uploads_table', 17),
(49, '2026_05_22_100011_add_total_enrollments_to_courses_table', 17),
(50, '2026_05_22_100012_populate_total_enrollments', 17);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `from_user_id` int(11) DEFAULT NULL,
  `from_user_name` varchar(255) NOT NULL DEFAULT '',
  `type` enum('post_like','post_comment','comment_reply','lesson_like','lesson_comment','course_like','course_comment','course_rating') NOT NULL,
  `entity_id` bigint(20) UNSIGNED DEFAULT NULL,
  `entity_title` varchar(255) NOT NULL DEFAULT '',
  `message` text NOT NULL,
  `read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELATIONSHIPS FOR TABLE `notifications`:
--   `from_user_id`
--       `users` -> `id`
--   `user_id`
--       `users` -> `id`
--

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELATIONSHIPS FOR TABLE `password_resets`:
--

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires_at`, `created_at`) VALUES
(12, 'aghaa003@gmail.com', 'd69da38fd1af01e8c71678149a6df93360378b436dc2c707812a0061df08a45c', '2026-04-03 20:17:50', '2026-04-03 18:17:50');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELATIONSHIPS FOR TABLE `personal_access_tokens`:
--

-- --------------------------------------------------------

--
-- Table structure for table `platforms`
--

DROP TABLE IF EXISTS `platforms`;
CREATE TABLE `platforms` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `url` varchar(255) NOT NULL,
  `category` enum('global','arabic') DEFAULT 'global',
  `level` enum('beginner','intermediate','advanced') DEFAULT 'beginner',
  `language` enum('english','arabic','both') DEFAULT 'english',
  `rating` decimal(3,2) DEFAULT 0.00,
  `user_count` int(11) DEFAULT 0,
  `problem_count` int(11) DEFAULT 0,
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features`)),
  `logo_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELATIONSHIPS FOR TABLE `platforms`:
--

--
-- Dumping data for table `platforms`
--

INSERT INTO `platforms` (`id`, `name`, `description`, `url`, `category`, `level`, `language`, `rating`, `user_count`, `problem_count`, `features`, `logo_url`, `is_active`, `created_at`) VALUES
(3, 'برمج', 'منصة عربية تهدف إلى تطوير مهارات البرمجة لدى الناطقين بالعربية من خلال التحديات البرمجية والمسابقات.', 'https://barmej.com', 'arabic', 'beginner', 'arabic', 4.00, 100000, 500, '[\"عربي\", \"مسابقات\", \"مبتدئين\"]', '', 1, '2025-10-26 19:33:25'),
(6, 'مدرسة', 'منصة عربية تقدم محتوى تعليمي مجاني في البرمجة وتتضمن تحديات برمجية للمبتدئين باللغة العربية.', 'https://madrasa.org', 'arabic', 'beginner', 'arabic', 0.00, 500000, 300, '[\"عربي\", \"مجاني\", \"مبتدئين\"]', '', 1, '2025-10-26 19:33:25'),
(26, 'LeeetCode', 'منصة رائدة في تحضير مقابلات العمل التقنية، تحتوي على آلاف المشاكل البرمجية مع حلول مفصلة ومناقشات.', 'https://leetcode.com', 'global', 'advanced', 'english', 4.00, 5000000, 2000, '[\"\\u0645\\u0642\\u0627\\u0628\\u0644\\u0627\\u062a \\u0627\\u0644\\u0639\\u0645\\u0644\",\"\\u062e\\u0648\\u0627\\u0631\\u0632\\u0645\\u064a\\u0627\\u062a\",\"\\u0647\\u064a\\u0627\\u0643\\u0644 \\u0627\\u0644\\u0628\\u064a\\u0627\\u0646\\u0627\\u062a\"]', '', 1, '2025-10-29 19:38:13'),
(27, 'HackerRank', 'منصة شاملة للمبرمجين من جميع المستويات، تقدم تحديات برمجية ومسابقات وفرص عمل مع الشركات العالمية.', 'https://hackerrank.com', 'global', 'beginner', 'english', 4.00, 7000000, 1500, '[\"مسابقات\", \"تحديات\", \"فرص عمل\"]', '', 1, '2025-10-29 19:38:13'),
(29, 'Codeforces', 'منصة تنافسية للمبرمجين المحترفين، تشتهر بمسابقاتها المنتظمة ومجتمعها النشط في حل المشكلات الخوارزمية.', 'https://codeforces.com', 'global', 'advanced', 'english', 0.00, 1000000, 3000, '[\"مسابقات\", \"خوارزميات\", \"متقدمين\"]', '', 1, '2025-10-29 19:38:13'),
(30, 'Codewars', 'منصة فريدة تقدم تحديات برمجية تسمى \"كاتا\" لتحسين مهاراتك من خلال الممارسة والتعلم من حلول الآخرين.', 'https://codewars.com', 'global', 'intermediate', 'english', 0.00, 3000000, 2000, '[\"\\u0643\\u0627\\u062a\\u0627\",\"\\u0645\\u062c\\u062a\\u0645\\u0639\",\"\\u0645\\u0633\\u062a\\u0648\\u064a\\u0627\\u062a\"]', '/uploads/logos/logo_69c81cc98c6ca_Screenshot_2025-05-21_205750.png', 0, '2025-10-29 19:38:13');

-- --------------------------------------------------------

--
-- Table structure for table `platform_bookmarks`
--

DROP TABLE IF EXISTS `platform_bookmarks`;
CREATE TABLE `platform_bookmarks` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `platform_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELATIONSHIPS FOR TABLE `platform_bookmarks`:
--   `user_id`
--       `users` -> `id`
--   `platform_id`
--       `platforms` -> `id`
--

--
-- Dumping data for table `platform_bookmarks`
--

INSERT INTO `platform_bookmarks` (`id`, `user_id`, `platform_id`, `created_at`) VALUES
(13, 1, 26, '2025-10-29 20:01:30'),
(18, 1, 29, '2025-12-22 20:12:30'),
(19, 1, 27, '2026-01-24 17:06:21');

-- --------------------------------------------------------

--
-- Table structure for table `platform_ratings`
--

DROP TABLE IF EXISTS `platform_ratings`;
CREATE TABLE `platform_ratings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `platform_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELATIONSHIPS FOR TABLE `platform_ratings`:
--   `user_id`
--       `users` -> `id`
--   `platform_id`
--       `platforms` -> `id`
--

--
-- Dumping data for table `platform_ratings`
--

INSERT INTO `platform_ratings` (`id`, `user_id`, `platform_id`, `rating`, `created_at`) VALUES
(4, 1, 26, 4, '2025-11-28 21:21:23'),
(9, 1, 27, 4, '2025-11-30 19:23:30'),
(12, 11, 3, 4, '2026-03-24 23:11:25');

-- --------------------------------------------------------

--
-- Stand-in structure for view `platform_statistics`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `platform_statistics`;
CREATE TABLE `platform_statistics` (
`id` int(11)
,`name` varchar(100)
,`description` text
,`url` varchar(255)
,`category` enum('global','arabic')
,`level` enum('beginner','intermediate','advanced')
,`language` enum('english','arabic','both')
,`logo_url` varchar(255)
,`is_active` tinyint(1)
,`bookmark_count` bigint(21)
,`rating_count` bigint(21)
,`avg_rating` decimal(13,2)
,`problem_count` int(11)
,`user_count` int(11)
,`features` longtext
);

-- --------------------------------------------------------

--
-- Table structure for table `repositories`
--

DROP TABLE IF EXISTS `repositories`;
CREATE TABLE `repositories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `technologies` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`technologies`)),
  `repo_url` varchar(255) DEFAULT NULL,
  `live_demo_url` varchar(255) DEFAULT NULL,
  `file_url` varchar(255) DEFAULT NULL,
  `code_files_urls` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`code_files_urls`)),
  `pdf_files_urls` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`pdf_files_urls`)),
  `cover_image_url` varchar(255) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `likes` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_public` tinyint(1) NOT NULL DEFAULT 1,
  `is_draft` tinyint(1) NOT NULL DEFAULT 0,
  `source_project` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELATIONSHIPS FOR TABLE `repositories`:
--   `user_id`
--       `users` -> `id`
--

-- --------------------------------------------------------

--
-- Table structure for table `repo_likes`
--

DROP TABLE IF EXISTS `repo_likes`;
CREATE TABLE `repo_likes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `repository_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELATIONSHIPS FOR TABLE `repo_likes`:
--   `repository_id`
--       `repositories` -> `id`
--   `user_id`
--       `users` -> `id`
--

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELATIONSHIPS FOR TABLE `roles`:
--

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`) VALUES
(2, 'admin'),
(3, 'contentCreator'),
(1, 'student');

-- --------------------------------------------------------

--
-- Table structure for table `uploads`
--

DROP TABLE IF EXISTS `uploads`;
CREATE TABLE `uploads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `filename` varchar(255) NOT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `mime_type` varchar(255) DEFAULT NULL,
  `size` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `path` varchar(255) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELATIONSHIPS FOR TABLE `uploads`:
--   `user_id`
--       `users` -> `id`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `firstName` varchar(50) NOT NULL,
  `lastName` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `preferred_language` varchar(5) DEFAULT 'ar',
  `phone` varchar(25) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `experience` varchar(50) DEFAULT NULL,
  `goal` varchar(50) DEFAULT NULL,
  `interest` varchar(50) DEFAULT NULL,
  `joinDate` datetime NOT NULL DEFAULT current_timestamp(),
  `avatar_path` varchar(255) DEFAULT NULL,
  `provider` varchar(50) DEFAULT NULL,
  `provider_id` varchar(255) DEFAULT NULL,
  `is_suspended` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELATIONSHIPS FOR TABLE `users`:
--

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `firstName`, `lastName`, `email`, `preferred_language`, `phone`, `username`, `password`, `remember_token`, `country`, `experience`, `goal`, `interest`, `joinDate`, `avatar_path`, `provider`, `provider_id`, `is_suspended`, `deleted_at`) VALUES
(1, 'ahmada', 'aghaaaa', 'aghaa003@gmail.com', 'ar', NULL, 'ahmadaghaa003', '$2y$12$LY3IcH2jne1DjYW/UWhaQ.2j1.cX0JZ11cmDu441.rlcJUAtrz8Zy', 'iJEGmnYmLWqZBid3BKbm58wjJDzNvHpJSOxE9VeaQrw3TNrBawGQpo839UB0', 'EG', 'beginner', NULL, 'web', '2025-10-03 00:47:49', '/uploads/avatars/avatar_1_69c98e317107a.png', 'github', '211641712', 0, NULL),
(7, 'test', 'test', 'test@gmail.com', 'ar', '', 'test', '$2y$10$8VyqNXr7NQh2Qzaq0HwI6.IbJTY.S9GU0qBzISrYXbWpFbl5uoi.m', NULL, 'SA', 'intermediate', 'job', 'mobile', '2025-10-05 10:56:38', '/uploads/avatars/avatar_7.png', NULL, NULL, 0, NULL),
(8, 'sanaa', 'hamo', 'sanaa19702012@gmail.com', 'ar', '+963930882851', 'sanaa1970', '$2y$10$g8xoghXC7kFMFkJeb/T4LOcWXnVxlPjSR.99wrza5RGt5DnjkSgC6', NULL, 'EG', 'intermediate', 'freelance', 'web', '2025-12-26 17:19:46', NULL, NULL, NULL, 0, NULL),
(10, 'deniz', 'aghaa', 'deniz@gmail.com', 'ar', NULL, 'deniz', '$2y$10$HsT60BqtxMcXOd83w4OfpOKOrYq50/8L2AqeO2ARXJYG9XLzhLq5O', 'Aeo5GA3Es6j0BnpYTyLPDHgAKFv16jMOStjKZFGbmdiejblGKa4ZMB4fss8z', 'OM', 'beginner', 'job', 'mobile', '2026-01-24 18:01:21', NULL, NULL, NULL, 0, NULL),
(11, 'TestFirst', 'TestLast', 'testuser@test.com', 'ar', NULL, 'testuser', '$2y$12$OC0dWXAaGg5O0/kXT.5ORe8iLAnizTva8EDXG36sq6bPRSTgctjSi', NULL, NULL, NULL, NULL, 'web', '2026-03-25 01:56:27', NULL, NULL, NULL, 0, NULL),
(12, 'turkmanyy', 'aabodeaa', 'turkmany0@gmail.com', 'ar', '+963930882851', 'turkmanytest', '$2y$12$OqxP3zMmwu3XOP06SiBggukYF4Xybj.mDEaoH5v6HXCOVSWof0QqO', NULL, 'SA', 'beginner', 'job', 'web', '2026-03-26 16:48:10', '/uploads/avatars/avatar_12.png', NULL, NULL, 0, '2026-04-02 21:32:07'),
(14, '123', '123', '123@gmail.com', 'ar', NULL, '123', '$2y$12$CX0gz.R65aLsZlsjXVx4zekqWMfXeFM.rmACcfxzyNz5l4xEmHJIK', 'zL7bZOnx907xaTROEBSzstXZM6HvxkSjmeqj988XEAMByLzouMB42EJAwshQ', 'OM', 'beginner', 'job', 'mobile', '2026-04-03 22:49:58', NULL, NULL, NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_assignments`
--

DROP TABLE IF EXISTS `user_assignments`;
CREATE TABLE `user_assignments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `assignment_id` int(11) NOT NULL,
  `solution` text DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `score` int(11) DEFAULT NULL,
  `status` enum('submitted','graded') DEFAULT 'submitted',
  `is_completed` tinyint(1) NOT NULL DEFAULT 0,
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELATIONSHIPS FOR TABLE `user_assignments`:
--   `assignment_id`
--       `assignments` -> `id`
--   `user_id`
--       `users` -> `id`
--

-- --------------------------------------------------------

--
-- Table structure for table `user_challenges`
--

DROP TABLE IF EXISTS `user_challenges`;
CREATE TABLE `user_challenges` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `challenge_id` int(11) NOT NULL,
  `attempts` int(11) DEFAULT 0,
  `completed` tinyint(1) DEFAULT 0,
  `best_score` int(11) DEFAULT 0,
  `last_attempted` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELATIONSHIPS FOR TABLE `user_challenges`:
--   `challenge_id`
--       `challenges` -> `id`
--   `user_id`
--       `users` -> `id`
--

--
-- Dumping data for table `user_challenges`
--

INSERT INTO `user_challenges` (`id`, `user_id`, `challenge_id`, `attempts`, `completed`, `best_score`, `last_attempted`) VALUES
(97, 1, 10, 5, 1, 100, '2026-04-03 14:22:41');

-- --------------------------------------------------------

--
-- Stand-in structure for view `user_challenge_summary`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `user_challenge_summary`;
CREATE TABLE `user_challenge_summary` (
`user_id` int(11)
,`username` varchar(50)
,`firstName` varchar(50)
,`lastName` varchar(50)
,`category` enum('algorithms','data-structures','web','database')
,`total_challenges_in_category` bigint(21)
,`completed_count` bigint(21)
,`attempted_count` bigint(21)
,`total_points` decimal(32,0)
,`success_rate` decimal(25,1)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `user_course_overview`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `user_course_overview`;
CREATE TABLE `user_course_overview` (
`course_id` int(11) unsigned
,`title` varchar(255)
,`description` text
,`category` varchar(100)
,`level` varchar(50)
,`logo_path` varchar(255)
,`main_points` text
,`user_id` int(11)
,`percentage_completed` int(11)
,`last_lesson_id` int(11)
,`started_at` timestamp
,`last_accessed` timestamp
,`total_lessons` bigint(21)
,`completed_lessons` bigint(21)
,`total_views` decimal(32,0)
);

-- --------------------------------------------------------

--
-- Table structure for table `user_course_progress`
--

DROP TABLE IF EXISTS `user_course_progress`;
CREATE TABLE `user_course_progress` (
  `user_id` int(11) NOT NULL,
  `course_id` int(11) UNSIGNED NOT NULL,
  `percentage_completed` int(11) DEFAULT 0 COMMENT 'Overall course completion 0-100',
  `last_lesson_id` int(11) DEFAULT NULL COMMENT 'Last watched lesson for resume',
  `started_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_accessed` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELATIONSHIPS FOR TABLE `user_course_progress`:
--   `user_id`
--       `users` -> `id`
--   `course_id`
--       `courses` -> `id`
--   `last_lesson_id`
--       `lessons` -> `id`
--

--
-- Dumping data for table `user_course_progress`
--

INSERT INTO `user_course_progress` (`user_id`, `course_id`, `percentage_completed`, `last_lesson_id`, `started_at`, `last_accessed`) VALUES
(1, 8, 88, 9, '2026-01-24 17:12:03', '2026-04-02 16:42:26'),
(1, 10, 40, 38, '2026-04-03 16:31:22', '2026-04-03 16:43:33'),
(8, 8, 13, 8, '2025-12-26 14:21:24', '2025-12-26 14:21:24'),
(10, 8, 13, 8, '2026-03-27 20:05:57', '2026-03-27 20:05:57'),
(10, 10, 100, 38, '2026-01-24 15:13:34', '2026-04-03 16:42:38'),
(10, 23, 100, 52, '2026-01-24 15:17:05', '2026-01-24 15:17:05');

-- --------------------------------------------------------

--
-- Stand-in structure for view `user_learning_dashboard`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `user_learning_dashboard`;
CREATE TABLE `user_learning_dashboard` (
`user_id` int(11)
,`username` varchar(50)
,`firstName` varchar(50)
,`lastName` varchar(50)
,`email` varchar(100)
,`joinDate` datetime
,`enrolled_courses` bigint(21)
,`completed_courses` bigint(21)
,`avg_course_completion` decimal(13,2)
,`total_lessons_started` bigint(21)
,`lessons_completed` bigint(21)
,`challenges_attempted` bigint(21)
,`challenges_completed` bigint(21)
,`total_challenge_points` decimal(32,0)
,`assignments_attempted` bigint(21)
,`assignments_completed` bigint(21)
,`avg_assignment_score` decimal(13,2)
,`bookmarked_platforms` bigint(21)
,`last_course_access` timestamp
,`last_challenge_attempt` timestamp
,`last_assignment_submission` timestamp
);

-- --------------------------------------------------------

--
-- Table structure for table `user_lesson_progress`
--

DROP TABLE IF EXISTS `user_lesson_progress`;
CREATE TABLE `user_lesson_progress` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `lesson_id` int(11) NOT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_position` int(11) DEFAULT 0 COMMENT 'Video position in seconds'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELATIONSHIPS FOR TABLE `user_lesson_progress`:
--   `user_id`
--       `users` -> `id`
--   `lesson_id`
--       `lessons` -> `id`
--

--
-- Dumping data for table `user_lesson_progress`
--

INSERT INTO `user_lesson_progress` (`id`, `user_id`, `lesson_id`, `completed_at`, `updated_at`, `last_position`) VALUES
(235, 1, 6, NULL, '2025-12-06 20:42:03', 1),
(263, 1, 8, NULL, '2026-04-02 16:42:13', 949),
(271, 8, 8, '2025-12-26 14:21:24', '2025-12-26 14:21:24', 0),
(275, 10, 51, '2026-01-24 15:13:34', '2026-01-24 15:13:34', 0),
(276, 10, 52, '2026-01-24 15:17:05', '2026-01-24 15:17:05', 0),
(280, 1, 9, '2026-03-27 00:03:28', '2026-04-02 16:42:26', 1160),
(290, 11, 6, NULL, '2026-03-24 23:05:44', 120),
(296, 1, 52, NULL, '2026-03-26 21:03:15', 0),
(308, 10, 8, '2026-03-27 20:05:57', '2026-03-27 20:05:57', 0),
(310, 1, 10, '2026-03-27 21:48:17', '2026-03-27 21:48:17', 0),
(311, 1, 11, '2026-03-27 21:48:18', '2026-03-27 21:48:18', 0),
(312, 1, 12, '2026-03-27 21:48:19', '2026-03-27 21:48:19', 0),
(313, 1, 48, '2026-03-27 21:48:32', '2026-03-27 21:48:32', 0),
(314, 1, 49, '2026-03-27 21:48:34', '2026-03-27 21:48:34', 0),
(315, 1, 50, '2026-03-27 21:48:35', '2026-03-27 21:48:35', 0),
(348, 1, 15, '2026-04-03 16:31:22', '2026-04-03 16:31:24', 15),
(351, 10, 15, '2026-04-03 16:32:46', '2026-04-03 16:32:58', 17),
(355, 10, 36, '2026-04-03 16:33:08', '2026-04-03 16:33:41', 17),
(359, 10, 37, '2026-04-03 16:34:24', '2026-04-03 16:34:24', 17),
(363, 10, 38, '2026-04-03 16:42:38', '2026-04-03 16:42:38', 0),
(364, 1, 38, '2026-04-03 16:43:33', '2026-04-03 16:43:33', 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_preferences`
--

DROP TABLE IF EXISTS `user_preferences`;
CREATE TABLE `user_preferences` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `preferred_level` varchar(50) DEFAULT NULL,
  `preferred_language` varchar(10) DEFAULT NULL,
  `goals` varchar(255) DEFAULT NULL,
  `time_commitment` enum('يومياً','أسبوعياً','شهرياً') DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELATIONSHIPS FOR TABLE `user_preferences`:
--   `user_id`
--       `users` -> `id`
--

--
-- Dumping data for table `user_preferences`
--

INSERT INTO `user_preferences` (`id`, `user_id`, `preferred_level`, `preferred_language`, `goals`, `time_commitment`, `updated_at`) VALUES
(24, 1, 'مبتدئ', 'الإنجليزية', 'تحسين المهارات الخوارزمية', 'أسبوعياً', '2026-05-13 20:36:35');

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

DROP TABLE IF EXISTS `user_roles`;
CREATE TABLE `user_roles` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELATIONSHIPS FOR TABLE `user_roles`:
--   `user_id`
--       `users` -> `id`
--   `role_id`
--       `roles` -> `id`
--

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`user_id`, `role_id`) VALUES
(1, 2),
(10, 2),
(11, 1),
(14, 2);

-- --------------------------------------------------------

--
-- Structure for view `course_stats` exported as a table
--
DROP TABLE IF EXISTS `course_stats`;
CREATE TABLE`course_stats`(
    `id` int(11) unsigned NOT NULL DEFAULT '0',
    `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
    `category` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
    `level` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'Beginner',
    `is_active` tinyint(1) NOT NULL DEFAULT '1',
    `created_at` timestamp NOT NULL DEFAULT 'current_timestamp()',
    `lesson_count` bigint(21) NOT NULL DEFAULT '0',
    `enrolled_users` bigint(21) NOT NULL DEFAULT '0',
    `completed_users` bigint(21) NOT NULL DEFAULT '0',
    `avg_completion_rate` decimal(13,2) DEFAULT NULL,
    `total_views` decimal(32,0) DEFAULT NULL,
    `assignment_count` bigint(21) NOT NULL DEFAULT '0',
    `last_activity` timestamp DEFAULT 'current_timestamp()'
);

-- --------------------------------------------------------

--
-- Structure for view `platform_statistics` exported as a table
--
DROP TABLE IF EXISTS `platform_statistics`;
CREATE TABLE`platform_statistics`(
    `id` int(11) NOT NULL DEFAULT '0',
    `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
    `description` text COLLATE utf8mb4_general_ci DEFAULT NULL,
    `url` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
    `category` enum('global','arabic') COLLATE utf8mb4_general_ci DEFAULT 'global',
    `level` enum('beginner','intermediate','advanced') COLLATE utf8mb4_general_ci DEFAULT 'beginner',
    `language` enum('english','arabic','both') COLLATE utf8mb4_general_ci DEFAULT 'english',
    `logo_url` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
    `is_active` tinyint(1) DEFAULT '1',
    `bookmark_count` bigint(21) NOT NULL DEFAULT '0',
    `rating_count` bigint(21) NOT NULL DEFAULT '0',
    `avg_rating` decimal(13,2) DEFAULT NULL,
    `problem_count` int(11) DEFAULT '0',
    `user_count` int(11) DEFAULT '0',
    `features` longtext COLLATE utf8mb4_bin DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure for view `user_challenge_summary` exported as a table
--
DROP TABLE IF EXISTS `user_challenge_summary`;
CREATE TABLE`user_challenge_summary`(
    `user_id` int(11) NOT NULL DEFAULT '0',
    `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
    `firstName` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
    `lastName` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
    `category` enum('algorithms','data-structures','web','database') COLLATE utf8mb4_general_ci NOT NULL,
    `total_challenges_in_category` bigint(21) NOT NULL DEFAULT '0',
    `completed_count` bigint(21) NOT NULL DEFAULT '0',
    `attempted_count` bigint(21) NOT NULL DEFAULT '0',
    `total_points` decimal(32,0) DEFAULT NULL,
    `success_rate` decimal(25,1) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure for view `user_course_overview` exported as a table
--
DROP TABLE IF EXISTS `user_course_overview`;
CREATE TABLE`user_course_overview`(
    `course_id` int(11) unsigned NOT NULL DEFAULT '0',
    `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
    `description` text COLLATE utf8mb4_general_ci DEFAULT NULL,
    `category` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
    `level` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'Beginner',
    `logo_path` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
    `main_points` text COLLATE utf8mb4_general_ci DEFAULT NULL,
    `user_id` int(11) NOT NULL,
    `percentage_completed` int(11) DEFAULT NULL,
    `last_lesson_id` int(11) DEFAULT NULL COMMENT 'Last watched lesson for resume',
    `started_at` timestamp NOT NULL DEFAULT 'current_timestamp()',
    `last_accessed` timestamp NOT NULL DEFAULT 'current_timestamp()',
    `total_lessons` bigint(21) NOT NULL DEFAULT '0',
    `completed_lessons` bigint(21) NOT NULL DEFAULT '0',
    `total_views` decimal(32,0) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure for view `user_learning_dashboard` exported as a table
--
DROP TABLE IF EXISTS `user_learning_dashboard`;
CREATE TABLE`user_learning_dashboard`(
    `user_id` int(11) NOT NULL DEFAULT '0',
    `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
    `firstName` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
    `lastName` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
    `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
    `joinDate` datetime NOT NULL DEFAULT 'current_timestamp()',
    `enrolled_courses` bigint(21) NOT NULL DEFAULT '0',
    `completed_courses` bigint(21) NOT NULL DEFAULT '0',
    `avg_course_completion` decimal(13,2) DEFAULT NULL,
    `total_lessons_started` bigint(21) NOT NULL DEFAULT '0',
    `lessons_completed` bigint(21) NOT NULL DEFAULT '0',
    `challenges_attempted` bigint(21) NOT NULL DEFAULT '0',
    `challenges_completed` bigint(21) NOT NULL DEFAULT '0',
    `total_challenge_points` decimal(32,0) DEFAULT NULL,
    `assignments_attempted` bigint(21) NOT NULL DEFAULT '0',
    `assignments_completed` bigint(21) NOT NULL DEFAULT '0',
    `avg_assignment_score` decimal(13,2) DEFAULT NULL,
    `bookmarked_platforms` bigint(21) NOT NULL DEFAULT '0',
    `last_course_access` timestamp DEFAULT 'current_timestamp()',
    `last_challenge_attempt` timestamp DEFAULT 'current_timestamp()',
    `last_assignment_submission` timestamp DEFAULT 'current_timestamp()'
);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `academy_reviews`
--
ALTER TABLE `academy_reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_review` (`user_id`),
  ADD KEY `idx_reviews_recent` (`created_at`),
  ADD KEY `idx_reviews_rating` (`rating`,`created_at`),
  ADD KEY `idx_reviews_user_id` (`user_id`);

--
-- Indexes for table `admin_audit_logs`
--
ALTER TABLE `admin_audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_audit_logs_admin_id_index` (`admin_id`),
  ADD KEY `idx_audit_admin_created` (`admin_id`,`created_at`);

--
-- Indexes for table `ai_conversations`
--
ALTER TABLE `ai_conversations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ai_conversations_user_id_index` (`user_id`);

--
-- Indexes for table `ai_messages`
--
ALTER TABLE `ai_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ai_messages_conv_created_at_idx` (`conversation_id`,`created_at`);

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_course_assignments` (`course_id`,`assignment_order`),
  ADD KEY `idx_assignment_difficulty` (`difficulty`,`course_id`);

--
-- Indexes for table `challenges`
--
ALTER TABLE `challenges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_challenge_filter` (`category`,`difficulty`,`is_active`),
  ADD KEY `idx_challenge_points` (`points`),
  ADD KEY `idx_challenges_is_active` (`is_active`);
ALTER TABLE `challenges` ADD FULLTEXT KEY `idx_challenge_search` (`title`,`description`);

--
-- Indexes for table `challenge_attempts`
--
ALTER TABLE `challenge_attempts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_attempt` (`user_id`,`challenge_id`),
  ADD KEY `challenge_id` (`challenge_id`);

--
-- Indexes for table `community_posts`
--
ALTER TABLE `community_posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `community_posts_user_id_index` (`user_id`);

--
-- Indexes for table `community_post_comments`
--
ALTER TABLE `community_post_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `community_post_comments_user_id_foreign` (`user_id`),
  ADD KEY `community_post_comments_post_id_user_id_index` (`post_id`,`user_id`),
  ADD KEY `community_post_comments_parent_id_index` (`parent_id`);

--
-- Indexes for table `community_post_likes`
--
ALTER TABLE `community_post_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_post_like` (`post_id`,`user_id`),
  ADD KEY `community_post_likes_user_id_foreign` (`user_id`),
  ADD KEY `community_post_likes_post_id_user_id_index` (`post_id`,`user_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category_active` (`category`,`is_active`,`created_at`),
  ADD KEY `idx_level_active` (`level`,`is_active`);
ALTER TABLE `courses` ADD FULLTEXT KEY `idx_course_search` (`title`,`description`);

--
-- Indexes for table `examples`
--
ALTER TABLE `examples`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_example_filter` (`category`,`difficulty`,`is_active`),
  ADD KEY `idx_example_tech` (`code_language`,`is_active`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `lessons`
--
ALTER TABLE `lessons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_course_lessons` (`course_id`,`sort_order`),
  ADD KEY `idx_lesson_views` (`views`),
  ADD KEY `idx_course_active` (`course_id`,`created_at`);

--
-- Indexes for table `lesson_comments`
--
ALTER TABLE `lesson_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lesson_comments_course_id_foreign` (`course_id`),
  ADD KEY `lesson_comments_user_id_foreign` (`user_id`),
  ADD KEY `lesson_comments_lesson_id_course_id_user_id_index` (`lesson_id`,`course_id`,`user_id`),
  ADD KEY `lesson_comments_parent_id_index` (`parent_id`);

--
-- Indexes for table `lesson_likes`
--
ALTER TABLE `lesson_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_lesson_like` (`lesson_id`,`user_id`),
  ADD KEY `lesson_likes_course_id_foreign` (`course_id`),
  ADD KEY `lesson_likes_user_id_foreign` (`user_id`),
  ADD KEY `lesson_likes_lesson_id_course_id_user_id_index` (`lesson_id`,`course_id`,`user_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_from_user_id_foreign` (`from_user_id`),
  ADD KEY `notifications_user_id_read_index` (`user_id`,`read`),
  ADD KEY `notifications_type_index` (`type`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_reset_expiry` (`expires_at`),
  ADD KEY `idx_reset_email` (`email`,`expires_at`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `platforms`
--
ALTER TABLE `platforms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_platform_filter` (`category`,`level`,`language`,`is_active`),
  ADD KEY `idx_platform_rating` (`rating`,`user_count`);

--
-- Indexes for table `platform_bookmarks`
--
ALTER TABLE `platform_bookmarks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_bookmark` (`user_id`,`platform_id`),
  ADD KEY `platform_id` (`platform_id`);

--
-- Indexes for table `platform_ratings`
--
ALTER TABLE `platform_ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_platform` (`user_id`,`platform_id`),
  ADD KEY `idx_platform_avg_rating` (`platform_id`,`rating`);

--
-- Indexes for table `repositories`
--
ALTER TABLE `repositories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `repositories_user_id_index` (`user_id`),
  ADD KEY `repositories_is_public_index` (`is_public`),
  ADD KEY `repositories_is_draft_index` (`is_draft`);

--
-- Indexes for table `repo_likes`
--
ALTER TABLE `repo_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_repo_like` (`repository_id`,`user_id`),
  ADD KEY `repo_likes_user_id_foreign` (`user_id`),
  ADD KEY `repo_likes_repository_id_user_id_index` (`repository_id`,`user_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `uploads`
--
ALTER TABLE `uploads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uploads_user_id_index` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `users_provider_provider_id_unique` (`provider`,`provider_id`),
  ADD KEY `idx_user_join_date` (`joinDate`),
  ADD KEY `idx_admin_users` (`id`),
  ADD KEY `idx_user_experience` (`experience`);

--
-- Indexes for table `user_assignments`
--
ALTER TABLE `user_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_assignment` (`user_id`,`assignment_id`),
  ADD KEY `idx_user_assignment_status` (`user_id`,`status`,`is_completed`),
  ADD KEY `idx_assignment_score` (`assignment_id`,`score`),
  ADD KEY `idx_submission_date` (`user_id`,`submitted_at`);

--
-- Indexes for table `user_challenges`
--
ALTER TABLE `user_challenges`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_challenge` (`user_id`,`challenge_id`),
  ADD KEY `idx_user_challenge_complete` (`user_id`,`completed`,`best_score`),
  ADD KEY `idx_challenge_recent` (`challenge_id`,`last_attempted`),
  ADD KEY `idx_leaderboard` (`user_id`,`best_score`),
  ADD KEY `idx_user_challenges_user_completed` (`user_id`,`completed`),
  ADD KEY `idx_user_challenges_challenge_id` (`challenge_id`);

--
-- Indexes for table `user_course_progress`
--
ALTER TABLE `user_course_progress`
  ADD PRIMARY KEY (`user_id`,`course_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `last_lesson_id` (`last_lesson_id`),
  ADD KEY `idx_last_accessed` (`last_accessed`),
  ADD KEY `idx_user_completion` (`user_id`,`percentage_completed`),
  ADD KEY `idx_active_courses` (`user_id`,`course_id`,`last_accessed`);

--
-- Indexes for table `user_lesson_progress`
--
ALTER TABLE `user_lesson_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_lesson_unique` (`user_id`,`lesson_id`),
  ADD KEY `lesson_id` (`lesson_id`),
  ADD KEY `idx_completed` (`completed_at`),
  ADD KEY `idx_user_incomplete` (`user_id`,`completed_at`),
  ADD KEY `idx_user_lesson_position` (`user_id`,`lesson_id`,`last_position`),
  ADD KEY `idx_updated` (`updated_at`),
  ADD KEY `idx_ulp_user_lesson` (`user_id`,`lesson_id`);

--
-- Indexes for table `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_prefs` (`user_id`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `academy_reviews`
--
ALTER TABLE `academy_reviews`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `admin_audit_logs`
--
ALTER TABLE `admin_audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `ai_conversations`
--
ALTER TABLE `ai_conversations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `ai_messages`
--
ALTER TABLE `ai_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `challenges`
--
ALTER TABLE `challenges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `challenge_attempts`
--
ALTER TABLE `challenge_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `community_posts`
--
ALTER TABLE `community_posts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `community_post_comments`
--
ALTER TABLE `community_post_comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `community_post_likes`
--
ALTER TABLE `community_post_likes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `examples`
--
ALTER TABLE `examples`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lessons`
--
ALTER TABLE `lessons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `lesson_comments`
--
ALTER TABLE `lesson_comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lesson_likes`
--
ALTER TABLE `lesson_likes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `platforms`
--
ALTER TABLE `platforms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `platform_bookmarks`
--
ALTER TABLE `platform_bookmarks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `platform_ratings`
--
ALTER TABLE `platform_ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `repositories`
--
ALTER TABLE `repositories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `repo_likes`
--
ALTER TABLE `repo_likes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `uploads`
--
ALTER TABLE `uploads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `user_assignments`
--
ALTER TABLE `user_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `user_challenges`
--
ALTER TABLE `user_challenges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98;

--
-- AUTO_INCREMENT for table `user_lesson_progress`
--
ALTER TABLE `user_lesson_progress`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=365;

--
-- AUTO_INCREMENT for table `user_preferences`
--
ALTER TABLE `user_preferences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `academy_reviews`
--
ALTER TABLE `academy_reviews`
  ADD CONSTRAINT `academy_reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ai_conversations`
--
ALTER TABLE `ai_conversations`
  ADD CONSTRAINT `ai_conversations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ai_messages`
--
ALTER TABLE `ai_messages`
  ADD CONSTRAINT `ai_messages_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `ai_conversations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `assignments_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `challenge_attempts`
--
ALTER TABLE `challenge_attempts`
  ADD CONSTRAINT `challenge_attempts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `challenge_attempts_ibfk_2` FOREIGN KEY (`challenge_id`) REFERENCES `challenges` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `community_posts`
--
ALTER TABLE `community_posts`
  ADD CONSTRAINT `community_posts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `community_post_comments`
--
ALTER TABLE `community_post_comments`
  ADD CONSTRAINT `community_post_comments_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `community_posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `community_post_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `community_post_likes`
--
ALTER TABLE `community_post_likes`
  ADD CONSTRAINT `community_post_likes_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `community_posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `community_post_likes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lessons`
--
ALTER TABLE `lessons`
  ADD CONSTRAINT `lessons_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lesson_comments`
--
ALTER TABLE `lesson_comments`
  ADD CONSTRAINT `lesson_comments_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `lesson_comments_lesson_id_foreign` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lesson_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lesson_likes`
--
ALTER TABLE `lesson_likes`
  ADD CONSTRAINT `lesson_likes_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `lesson_likes_lesson_id_foreign` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lesson_likes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_from_user_id_foreign` FOREIGN KEY (`from_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `platform_bookmarks`
--
ALTER TABLE `platform_bookmarks`
  ADD CONSTRAINT `platform_bookmarks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `platform_bookmarks_ibfk_2` FOREIGN KEY (`platform_id`) REFERENCES `platforms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `platform_ratings`
--
ALTER TABLE `platform_ratings`
  ADD CONSTRAINT `platform_ratings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `platform_ratings_ibfk_2` FOREIGN KEY (`platform_id`) REFERENCES `platforms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `repositories`
--
ALTER TABLE `repositories`
  ADD CONSTRAINT `repositories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `repo_likes`
--
ALTER TABLE `repo_likes`
  ADD CONSTRAINT `repo_likes_repository_id_foreign` FOREIGN KEY (`repository_id`) REFERENCES `repositories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `repo_likes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `uploads`
--
ALTER TABLE `uploads`
  ADD CONSTRAINT `uploads_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_assignments`
--
ALTER TABLE `user_assignments`
  ADD CONSTRAINT `user_assignments_assignment_id_foreign` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_assignments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_challenges`
--
ALTER TABLE `user_challenges`
  ADD CONSTRAINT `user_challenges_challenge_id_foreign` FOREIGN KEY (`challenge_id`) REFERENCES `challenges` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_challenges_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_course_progress`
--
ALTER TABLE `user_course_progress`
  ADD CONSTRAINT `user_course_progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_course_progress_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_course_progress_ibfk_3` FOREIGN KEY (`last_lesson_id`) REFERENCES `lessons` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_lesson_progress`
--
ALTER TABLE `user_lesson_progress`
  ADD CONSTRAINT `user_lesson_progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_lesson_progress_ibfk_2` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD CONSTRAINT `user_preferences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;


--
-- Metadata
--
USE `phpmyadmin`;

--
-- Metadata for table academy_reviews
--

--
-- Metadata for table admin_audit_logs
--

--
-- Metadata for table ai_conversations
--

--
-- Metadata for table ai_messages
--

--
-- Metadata for table assignments
--

--
-- Metadata for table challenges
--

--
-- Metadata for table challenge_attempts
--

--
-- Metadata for table community_posts
--

--
-- Metadata for table community_post_comments
--

--
-- Metadata for table community_post_likes
--

--
-- Metadata for table courses
--

--
-- Metadata for table course_stats
--

--
-- Metadata for table examples
--

--
-- Metadata for table failed_jobs
--

--
-- Metadata for table jobs
--

--
-- Metadata for table lessons
--

--
-- Metadata for table lesson_comments
--

--
-- Metadata for table lesson_likes
--

--
-- Metadata for table migrations
--

--
-- Metadata for table notifications
--

--
-- Metadata for table password_resets
--

--
-- Metadata for table personal_access_tokens
--

--
-- Metadata for table platforms
--

--
-- Metadata for table platform_bookmarks
--

--
-- Metadata for table platform_ratings
--

--
-- Metadata for table platform_statistics
--

--
-- Metadata for table repositories
--

--
-- Metadata for table repo_likes
--

--
-- Metadata for table roles
--

--
-- Metadata for table uploads
--

--
-- Metadata for table users
--

--
-- Metadata for table user_assignments
--

--
-- Metadata for table user_challenges
--

--
-- Metadata for table user_challenge_summary
--

--
-- Metadata for table user_course_overview
--

--
-- Metadata for table user_course_progress
--

--
-- Metadata for table user_learning_dashboard
--

--
-- Metadata for table user_lesson_progress
--

--
-- Metadata for table user_preferences
--

--
-- Metadata for table user_roles
--

--
-- Metadata for database programming_academy
--
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
