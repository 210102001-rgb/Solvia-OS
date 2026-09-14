-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 14, 2026 at 07:17 AM
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
-- Database: `novaos`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `author_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `category` varchar(255) NOT NULL DEFAULT 'company',
  `priority` varchar(255) NOT NULL DEFAULT 'medium',
  `audience_type` varchar(255) NOT NULL DEFAULT 'all',
  `audience_target` varchar(255) DEFAULT NULL,
  `publish_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expiry_date` timestamp NULL DEFAULT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'published',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `announcement_reads`
--

CREATE TABLE `announcement_reads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `announcement_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `read_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assets`
--

CREATE TABLE `assets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `resource_id` bigint(20) UNSIGNED NOT NULL,
  `asset_tag` varchar(255) NOT NULL,
  `brand` varchar(255) NOT NULL,
  `model` varchar(255) NOT NULL,
  `serial_number` varchar(255) DEFAULT NULL,
  `specs` text DEFAULT NULL,
  `condition` varchar(255) NOT NULL DEFAULT 'good',
  `lifecycle_status` varchar(255) NOT NULL DEFAULT 'available',
  `purchase_cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `upgrade_cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `maintenance_cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `repair_cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `accumulated_cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `current_holder_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `assets`
--

INSERT INTO `assets` (`id`, `resource_id`, `asset_tag`, `brand`, `model`, `serial_number`, `specs`, `condition`, `lifecycle_status`, `purchase_cost`, `upgrade_cost`, `maintenance_cost`, `repair_cost`, `accumulated_cost`, `current_holder_id`, `created_at`, `updated_at`) VALUES
(3, 5, 'AST-PX4UGU', 'Asus', 'Asus vivobook', NULL, '4256', 'good', 'in_use', 0.00, 0.00, 0.00, 0.00, 0.00, 11, '2026-09-13 20:25:39', '2026-09-13 20:25:39');

-- --------------------------------------------------------

--
-- Table structure for table `asset_assignments`
--

CREATE TABLE `asset_assignments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `asset_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `assigned_date` date NOT NULL,
  `returned_date` date DEFAULT NULL,
  `condition_on_assignment` varchar(255) NOT NULL DEFAULT 'good',
  `condition_on_return` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `asset_assignments`
--

INSERT INTO `asset_assignments` (`id`, `asset_id`, `user_id`, `assigned_date`, `returned_date`, `condition_on_assignment`, `condition_on_return`, `notes`, `created_at`, `updated_at`) VALUES
(2, 3, 11, '2026-09-14', NULL, 'good', NULL, 'Initial assignment on registration', '2026-09-13 20:25:39', '2026-09-13 20:25:39'),
(7, 3, 11, '2026-09-14', NULL, 'good', NULL, 'Unit testing asset assignment notification', '2026-09-13 20:48:41', '2026-09-13 20:48:41');

-- --------------------------------------------------------

--
-- Table structure for table `asset_maintenances`
--

CREATE TABLE `asset_maintenances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `asset_id` bigint(20) UNSIGNED NOT NULL,
  `maintenance_type` varchar(255) NOT NULL,
  `scheduled_date` date NOT NULL,
  `completed_date` date DEFAULT NULL,
  `technician_vendor` varchar(255) DEFAULT NULL,
  `cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'scheduled',
  `expense_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `asset_maintenances`
--

INSERT INTO `asset_maintenances` (`id`, `asset_id`, `maintenance_type`, `scheduled_date`, `completed_date`, `technician_vendor`, `cost`, `notes`, `attachment_path`, `status`, `expense_id`, `created_at`, `updated_at`) VALUES
(1, 3, 'preventative', '2026-09-14', NULL, NULL, 0.00, NULL, NULL, 'scheduled', NULL, '2026-09-13 21:26:08', '2026-09-13 21:26:08');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `actor_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `entity_type` varchar(255) DEFAULT NULL,
  `entity_id` bigint(20) UNSIGNED DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `actor_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `description`, `created_at`, `updated_at`) VALUES
(1, 1, 'login', 'User', 1, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'User logged into Solvia.Nova OS', '2026-09-13 19:30:55', '2026-09-13 19:30:55'),
(2, 1, 'create', 'Infrastructure', 1, NULL, '{\"type\":\"vps\",\"name\":\"solvianova\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'New infrastructure solvianova registered', '2026-09-13 19:32:23', '2026-09-13 19:32:23'),
(3, 1, 'create', 'User', 8, NULL, '{\"name\":\"Demo Dev\",\"email\":\"demodev@solvia.id\",\"role\":\"frontend_developer\",\"team_id\":null,\"status\":\"active\",\"join_date\":\"2026-09-14T00:00:00.000000Z\",\"updated_at\":\"2026-09-14T02:39:57.000000Z\",\"created_at\":\"2026-09-14T02:39:57.000000Z\",\"id\":8}', '127.0.0.1', 'Symfony', 'New user created: Demo Dev (frontend_developer)', '2026-09-13 19:39:57', '2026-09-13 19:39:57'),
(4, 1, 'create', 'Client', 1, NULL, '{\"client_code\":\"CLT-TEST-001\",\"name\":\"PT Solvia Test Client\",\"contact_person\":\"Budi Santoso\",\"email\":\"budi@test.com\",\"phone\":\"08123456789\",\"address\":\"Jakarta\",\"website\":\"https:\\/\\/test.com\",\"notes\":\"Catatan penting\",\"status\":\"active\",\"updated_at\":\"2026-09-14T02:43:12.000000Z\",\"created_at\":\"2026-09-14T02:43:12.000000Z\",\"id\":1}', '127.0.0.1', 'Symfony', 'New client registered: PT Solvia Test Client', '2026-09-13 19:43:12', '2026-09-13 19:43:12'),
(5, 1, 'create', 'Client', 2, NULL, '{\"client_code\":\"CLT-AUDIT-1\",\"name\":\"Audit Client\",\"status\":\"active\",\"updated_at\":\"2026-09-14T02:43:33.000000Z\",\"created_at\":\"2026-09-14T02:43:33.000000Z\",\"id\":2}', '127.0.0.1', 'Symfony', 'New client registered: Audit Client', '2026-09-13 19:43:33', '2026-09-13 19:43:33'),
(6, 1, 'create', 'Project', 1, NULL, '{\"project_code\":\"PRJ-AUDIT-1\",\"name\":\"Audit Project\",\"client_id\":2,\"project_type\":\"Internal\",\"start_date\":\"2026-09-14T00:00:00.000000Z\",\"deadline\":\"2026-10-14T00:00:00.000000Z\",\"revenue\":\"10000000.00\",\"budget\":\"5000000.00\",\"status\":\"active\",\"actual_cost\":\"0.00\",\"profit\":\"10000000.00\",\"profit_margin\":\"100.00\",\"health\":\"on_track\",\"updated_at\":\"2026-09-14T02:43:33.000000Z\",\"created_at\":\"2026-09-14T02:43:33.000000Z\",\"id\":1}', '127.0.0.1', 'Symfony', 'New project created: Audit Project', '2026-09-13 19:43:33', '2026-09-13 19:43:33'),
(7, 1, 'create', 'Income', 1, NULL, '{\"income_number\":\"INC-AUDIT-1\",\"date\":\"2026-09-14T00:00:00.000000Z\",\"source\":\"Client Deposit\",\"amount\":\"5000000.00\",\"category\":\"project_payment\",\"account_id\":1,\"updated_at\":\"2026-09-14T02:43:34.000000Z\",\"created_at\":\"2026-09-14T02:43:34.000000Z\",\"id\":1}', '127.0.0.1', 'Symfony', 'Income recorded: INC-AUDIT-1 (Rp 5.000.000)', '2026-09-13 19:43:34', '2026-09-13 19:43:34'),
(8, 1, 'create', 'Expense', 1, NULL, '{\"expense_number\":\"EXP-AUDIT-1\",\"date\":\"2026-09-14T00:00:00.000000Z\",\"category\":\"infrastructure\",\"amount\":\"500000.00\",\"account_id\":1,\"updated_at\":\"2026-09-14T02:43:34.000000Z\",\"created_at\":\"2026-09-14T02:43:34.000000Z\",\"id\":1}', '127.0.0.1', 'Symfony', 'Expense recorded: EXP-AUDIT-1 (Rp 500.000)', '2026-09-13 19:43:34', '2026-09-13 19:43:34'),
(9, 1, 'create', 'DailyProgress', 1, NULL, '{\"project_id\":\"1\",\"task_id\":null,\"date\":\"2026-09-18T00:00:00.000000Z\",\"progress\":0,\"completed_work\":\"30\",\"next_plan\":\"30\",\"blocker\":\"dfsfs\",\"working_hours\":\"8.00\",\"user_id\":1,\"updated_at\":\"2026-09-14T02:44:41.000000Z\",\"created_at\":\"2026-09-14T02:44:41.000000Z\",\"id\":1}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'Daily progress submitted by Nanda Ariwahyu', '2026-09-13 19:44:41', '2026-09-13 19:44:41'),
(10, 1, 'create', 'Client', 3, NULL, '{\"client_code\":\"CLT-PTM-867\",\"name\":\"PT Mandiri Jaya\",\"contact_person\":\"Budi\",\"email\":\"budi@mandiri.com\",\"phone\":\"08111222333\",\"status\":\"active\",\"updated_at\":\"2026-09-14T02:46:22.000000Z\",\"created_at\":\"2026-09-14T02:46:22.000000Z\",\"id\":3}', '127.0.0.1', 'Symfony', 'New client registered: PT Mandiri Jaya', '2026-09-13 19:46:22', '2026-09-13 19:46:22'),
(11, 1, 'update', 'Client', 3, '{\"id\":3,\"client_code\":\"CLT-PTM-867\",\"name\":\"PT Mandiri Jaya\",\"contact_person\":\"Budi\",\"email\":\"budi@mandiri.com\",\"phone\":\"08111222333\",\"address\":null,\"website\":null,\"notes\":null,\"status\":\"active\",\"created_at\":\"2026-09-14T02:46:22.000000Z\",\"updated_at\":\"2026-09-14T02:46:22.000000Z\"}', '{\"id\":3,\"client_code\":\"CLT-PTM-867\",\"name\":\"PT Mandiri Jaya Sejahtera\",\"contact_person\":\"Budi Hendra\",\"email\":\"budi.h@mandiri.com\",\"phone\":\"08111222333\",\"address\":null,\"website\":null,\"notes\":null,\"status\":\"active\",\"created_at\":\"2026-09-14T02:46:22.000000Z\",\"updated_at\":\"2026-09-14T02:46:22.000000Z\"}', '127.0.0.1', 'Symfony', 'Client PT Mandiri Jaya Sejahtera updated', '2026-09-13 19:46:22', '2026-09-13 19:46:22'),
(12, 1, 'delete', 'Client', 3, '{\"id\":3,\"client_code\":\"CLT-PTM-867\",\"name\":\"PT Mandiri Jaya Sejahtera\",\"contact_person\":\"Budi Hendra\",\"email\":\"budi.h@mandiri.com\",\"phone\":\"08111222333\",\"address\":null,\"website\":null,\"notes\":null,\"status\":\"active\",\"created_at\":\"2026-09-14T02:46:22.000000Z\",\"updated_at\":\"2026-09-14T02:46:22.000000Z\"}', NULL, '127.0.0.1', 'Symfony', 'Client PT Mandiri Jaya Sejahtera deleted', '2026-09-13 19:46:22', '2026-09-13 19:46:22'),
(13, 1, 'create', 'Team', 1, NULL, '{\"name\":\"Backend Engineering\",\"code\":\"BACKEN\",\"description\":\"Core backend services team\",\"lead_id\":null,\"updated_at\":\"2026-09-14T02:49:13.000000Z\",\"created_at\":\"2026-09-14T02:49:13.000000Z\",\"id\":1}', '127.0.0.1', 'Symfony', 'New team created: Backend Engineering', '2026-09-13 19:49:13', '2026-09-13 19:49:13'),
(14, 1, 'create', 'Client', 4, NULL, '{\"client_code\":\"CLT-PTN-403\",\"name\":\"PT Nusantara Sejahtera\",\"contact_person\":\"Bambang\",\"email\":\"bambang@nusantara.id\",\"phone\":\"081234567890\",\"status\":\"active\",\"updated_at\":\"2026-09-14T02:49:14.000000Z\",\"created_at\":\"2026-09-14T02:49:14.000000Z\",\"id\":4}', '127.0.0.1', 'Symfony', 'New client registered: PT Nusantara Sejahtera', '2026-09-13 19:49:14', '2026-09-13 19:49:14'),
(15, 1, 'create', 'Project', 2, NULL, '{\"project_code\":\"PRJ-TEST-001\",\"name\":\"Enterprise IoT Portal\",\"client_id\":4,\"project_type\":\"IoT Engineering\",\"start_date\":\"2026-09-14T00:00:00.000000Z\",\"deadline\":\"2026-10-14T00:00:00.000000Z\",\"revenue\":\"10000000.00\",\"budget\":\"5000000.00\",\"status\":\"active\",\"actual_cost\":\"0.00\",\"profit\":\"10000000.00\",\"profit_margin\":\"100.00\",\"health\":\"on_track\",\"updated_at\":\"2026-09-14T02:49:14.000000Z\",\"created_at\":\"2026-09-14T02:49:14.000000Z\",\"id\":2}', '127.0.0.1', 'Symfony', 'New project created: Enterprise IoT Portal', '2026-09-13 19:49:14', '2026-09-13 19:49:14'),
(16, 1, 'create', 'Milestone', 1, NULL, '{\"name\":\"Milestone 1 - Initial Prototype\",\"start_date\":\"2026-09-14T00:00:00.000000Z\",\"deadline\":\"2026-09-24T00:00:00.000000Z\",\"status\":\"in_progress\",\"project_id\":2,\"progress\":0,\"updated_at\":\"2026-09-14T02:49:14.000000Z\",\"created_at\":\"2026-09-14T02:49:14.000000Z\",\"id\":1}', '127.0.0.1', 'Symfony', 'New milestone created: Milestone 1 - Initial Prototype', '2026-09-13 19:49:14', '2026-09-13 19:49:14'),
(17, 1, 'create', 'Task', 1, NULL, '{\"title\":\"Design MQTT schema\",\"description\":null,\"milestone_id\":1,\"assignee_id\":null,\"priority\":\"high\",\"status\":\"to_do\",\"progress\":0,\"start_date\":null,\"deadline\":null,\"estimated_hours\":\"12.00\",\"project_id\":2,\"updated_at\":\"2026-09-14T02:49:14.000000Z\",\"created_at\":\"2026-09-14T02:49:14.000000Z\",\"id\":1,\"milestone\":{\"id\":1,\"project_id\":2,\"name\":\"Milestone 1 - Initial Prototype\",\"description\":null,\"start_date\":\"2026-09-14T00:00:00.000000Z\",\"deadline\":\"2026-09-24T00:00:00.000000Z\",\"progress\":0,\"status\":\"in_progress\",\"created_at\":\"2026-09-14T02:49:14.000000Z\",\"updated_at\":\"2026-09-14T02:49:14.000000Z\"}}', '127.0.0.1', 'Symfony', 'New task created: Design MQTT schema', '2026-09-13 19:49:14', '2026-09-13 19:49:14'),
(18, 1, 'create', 'DailyProgress', 2, NULL, '{\"project_id\":2,\"task_id\":1,\"date\":\"2026-09-14T00:00:00.000000Z\",\"progress\":30,\"completed_work\":\"Drafted schema payload\",\"next_plan\":\"Validate with broker\",\"blocker\":null,\"working_hours\":\"4.00\",\"user_id\":1,\"updated_at\":\"2026-09-14T02:49:14.000000Z\",\"created_at\":\"2026-09-14T02:49:14.000000Z\",\"id\":2}', '127.0.0.1', 'Symfony', 'Daily progress submitted by Nanda Ariwahyu', '2026-09-13 19:49:14', '2026-09-13 19:49:14'),
(19, 1, 'create', 'Blocker', 2, NULL, '{\"project_id\":2,\"title\":\"MQTT Broker Port Closed\",\"description\":\"Waiting for IT firewall policy\",\"priority\":\"high\",\"type\":\"technical\",\"reporter_id\":1,\"status\":\"open\",\"updated_at\":\"2026-09-14T02:49:14.000000Z\",\"created_at\":\"2026-09-14T02:49:14.000000Z\",\"id\":2}', '127.0.0.1', 'Symfony', 'New blocker reported on project #2: MQTT Broker Port Closed', '2026-09-13 19:49:14', '2026-09-13 19:49:14'),
(20, 1, 'create', 'Income', 2, NULL, '{\"income_number\":\"INC-TEST-001\",\"date\":\"2026-09-14T00:00:00.000000Z\",\"source\":\"Consulting Payment\",\"client_id\":4,\"project_id\":null,\"amount\":\"5000000.00\",\"category\":\"consulting\",\"account_id\":1,\"notes\":null,\"updated_at\":\"2026-09-14T02:49:14.000000Z\",\"created_at\":\"2026-09-14T02:49:14.000000Z\",\"id\":2}', '127.0.0.1', 'Symfony', 'Income recorded: INC-TEST-001 (Rp 5.000.000)', '2026-09-13 19:49:14', '2026-09-13 19:49:14'),
(21, 1, 'create', 'Expense', 2, NULL, '{\"expense_number\":\"EXP-TEST-001\",\"date\":\"2026-09-14T00:00:00.000000Z\",\"category\":\"hosting\",\"project_id\":null,\"amount\":\"250000.00\",\"account_id\":1,\"vendor\":\"Cloud Provider\",\"notes\":null,\"updated_at\":\"2026-09-14T02:49:14.000000Z\",\"created_at\":\"2026-09-14T02:49:14.000000Z\",\"id\":2}', '127.0.0.1', 'Symfony', 'Expense recorded: EXP-TEST-001 (Rp 250.000)', '2026-09-13 19:49:14', '2026-09-13 19:49:14'),
(22, 1, 'create', 'Invoice', 1, NULL, '{\"invoice_number\":\"INV-TEST-001\",\"client_id\":4,\"project_id\":null,\"issue_date\":\"2026-09-14T00:00:00.000000Z\",\"due_date\":\"2026-09-28T00:00:00.000000Z\",\"subtotal\":\"5000000.00\",\"discount\":\"0.00\",\"tax\":\"0.00\",\"total\":\"5000000.00\",\"payment_status\":\"sent\",\"notes\":null,\"updated_at\":\"2026-09-14T02:49:14.000000Z\",\"created_at\":\"2026-09-14T02:49:14.000000Z\",\"id\":1}', '127.0.0.1', 'Symfony', 'Invoice #INV-TEST-001 created for Rp 5.000.000', '2026-09-13 19:49:14', '2026-09-13 19:49:14'),
(23, 1, 'create', 'Asset', 1, NULL, '{\"resource_id\":2,\"asset_tag\":\"AST-TEST-001\",\"brand\":\"Dell\",\"model\":\"UltraSharp 27\\\"\",\"serial_number\":null,\"specs\":null,\"condition\":\"excellent\",\"lifecycle_status\":\"available\",\"purchase_cost\":\"6000000.00\",\"accumulated_cost\":\"6000000.00\",\"current_holder_id\":null,\"updated_at\":\"2026-09-14T02:49:14.000000Z\",\"created_at\":\"2026-09-14T02:49:14.000000Z\",\"id\":1}', '127.0.0.1', 'Symfony', 'Registered asset AST-TEST-001 (Dell UltraSharp 27\")', '2026-09-13 19:49:14', '2026-09-13 19:49:14'),
(24, 1, 'create', 'Infrastructure', 2, NULL, '{\"type\":\"vps\",\"name\":\"Broker Server\"}', '127.0.0.1', 'Symfony', 'New infrastructure Broker Server registered', '2026-09-13 19:49:14', '2026-09-13 19:49:14'),
(25, 1, 'create', 'PurchaseRequest', 1, NULL, '{\"item_name\":\"ESP32 Development Boards x5\",\"category\":\"inventory\",\"quantity\":5,\"estimated_cost\":\"350000.00\",\"reason\":\"Required for testing smart telemetry firmware\",\"priority\":\"high\",\"request_number\":\"PR-6AA760AA2C4A4\",\"requester_id\":1,\"status\":\"submitted\",\"updated_at\":\"2026-09-14T02:49:14.000000Z\",\"created_at\":\"2026-09-14T02:49:14.000000Z\",\"id\":1}', '127.0.0.1', 'Symfony', 'Purchase request PR-6AA760AA2C4A4 submitted by Nanda Ariwahyu', '2026-09-13 19:49:14', '2026-09-13 19:49:14'),
(26, 1, 'logout', 'User', 1, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'User logged out', '2026-09-13 19:50:54', '2026-09-13 19:50:54'),
(27, 1, 'login', 'User', 1, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'User logged into Solvia.Nova OS', '2026-09-13 19:53:02', '2026-09-13 19:53:02'),
(28, 1, 'create', 'User', 9, NULL, '{\"name\":\"Testing User Form\",\"email\":\"testingform_1789354446@solvia.id\",\"role\":\"backend_developer\",\"department\":\"Engineering\",\"team_id\":null,\"phone\":\"+62812345678\",\"status\":\"active\",\"join_date\":\"2026-09-14T00:00:00.000000Z\",\"updated_at\":\"2026-09-14T02:54:07.000000Z\",\"created_at\":\"2026-09-14T02:54:07.000000Z\",\"id\":9}', '127.0.0.1', 'Symfony', 'New user created: Testing User Form (backend_developer)', '2026-09-13 19:54:07', '2026-09-13 19:54:07'),
(29, 1, 'create', 'User', 10, NULL, '{\"name\":\"Budi Santoso\",\"email\":\"test_engineer_1789354915@solvia.id\",\"role\":\"backend_developer\",\"department\":\"Backend Core\",\"team_id\":null,\"phone\":\"+62812345678\",\"status\":\"active\",\"join_date\":\"2026-09-14T00:00:00.000000Z\",\"updated_at\":\"2026-09-14T03:01:55.000000Z\",\"created_at\":\"2026-09-14T03:01:55.000000Z\",\"id\":10}', '127.0.0.1', 'Symfony', 'New user created: Budi Santoso (backend_developer)', '2026-09-13 20:01:55', '2026-09-13 20:01:55'),
(30, 1, 'update', 'User', 10, '{\"id\":10,\"name\":\"Budi Santoso\",\"email\":\"test_engineer_1789354915@solvia.id\",\"email_verified_at\":null,\"phone\":\"+62812345678\",\"role\":\"backend_developer\",\"department\":\"Backend Core\",\"team_id\":null,\"join_date\":\"2026-09-14T00:00:00.000000Z\",\"status\":\"active\",\"avatar_url\":null,\"profile_bio\":null,\"permissions\":null,\"created_at\":\"2026-09-14T03:01:55.000000Z\",\"updated_at\":\"2026-09-14T03:01:55.000000Z\"}', '{\"id\":10,\"name\":\"Budi Santoso Updated\",\"email\":\"test_engineer_1789354915@solvia.id\",\"email_verified_at\":null,\"phone\":\"+6289999999\",\"role\":\"backend_developer\",\"department\":\"DevOps & Backend\",\"team_id\":null,\"join_date\":\"2026-09-14T00:00:00.000000Z\",\"status\":\"active\",\"avatar_url\":null,\"profile_bio\":null,\"permissions\":null,\"created_at\":\"2026-09-14T03:01:55.000000Z\",\"updated_at\":\"2026-09-14T03:01:55.000000Z\"}', '127.0.0.1', 'Symfony', 'User Budi Santoso Updated updated', '2026-09-13 20:01:55', '2026-09-13 20:01:55'),
(31, 1, 'delete', 'User', 10, '{\"id\":10,\"name\":\"Budi Santoso Updated\",\"email\":\"test_engineer_1789354915@solvia.id\",\"email_verified_at\":null,\"phone\":\"+6289999999\",\"role\":\"backend_developer\",\"department\":\"DevOps & Backend\",\"team_id\":null,\"join_date\":\"2026-09-14T00:00:00.000000Z\",\"status\":\"active\",\"avatar_url\":null,\"profile_bio\":null,\"permissions\":null,\"created_at\":\"2026-09-14T03:01:55.000000Z\",\"updated_at\":\"2026-09-14T03:01:55.000000Z\"}', NULL, '127.0.0.1', 'Symfony', 'User Budi Santoso Updated deleted', '2026-09-13 20:01:55', '2026-09-13 20:01:55'),
(32, 1, 'create', 'DailyProgress', 4, NULL, '{\"project_id\":1,\"task_id\":null,\"date\":\"2026-09-14T00:00:00.000000Z\",\"progress\":85,\"completed_work\":\"Completed Redis caching layer and tested load latency.\",\"next_plan\":\"Deploy to staging server.\",\"blocker\":null,\"working_hours\":\"6.50\",\"user_id\":1,\"updated_at\":\"2026-09-14T03:01:55.000000Z\",\"created_at\":\"2026-09-14T03:01:55.000000Z\",\"id\":4}', '127.0.0.1', 'Symfony', 'Daily progress submitted by Nanda Ariwahyu', '2026-09-13 20:01:55', '2026-09-13 20:01:55'),
(33, 1, 'create', 'User', 11, NULL, '{\"name\":\"Putra\",\"email\":\"putra@gmail.com\",\"role\":\"designer\",\"department\":\"Solvia Nova\",\"team_id\":null,\"phone\":\"0868686868\",\"status\":\"active\",\"join_date\":\"2026-09-14T00:00:00.000000Z\",\"updated_at\":\"2026-09-14T03:08:02.000000Z\",\"created_at\":\"2026-09-14T03:08:02.000000Z\",\"id\":11}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'New user created: Putra (designer)', '2026-09-13 20:08:03', '2026-09-13 20:08:03'),
(34, 1, 'create', 'Team', 2, NULL, '{\"name\":\"Design\",\"code\":\"Des\",\"description\":null,\"lead_id\":\"11\",\"updated_at\":\"2026-09-14T03:08:29.000000Z\",\"created_at\":\"2026-09-14T03:08:29.000000Z\",\"id\":2}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'New team created: Design', '2026-09-13 20:08:29', '2026-09-13 20:08:29'),
(35, 1, 'logout', 'User', 1, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'User logged out', '2026-09-13 20:08:35', '2026-09-13 20:08:35'),
(36, 11, 'login', 'User', 11, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'User logged into Solvia.Nova OS', '2026-09-13 20:08:53', '2026-09-13 20:08:53'),
(37, 11, 'logout', 'User', 11, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'User logged out', '2026-09-13 20:20:39', '2026-09-13 20:20:39'),
(38, 1, 'login', 'User', 1, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'User logged into Solvia.Nova OS', '2026-09-13 20:21:08', '2026-09-13 20:21:08'),
(39, NULL, 'create', 'Asset', 2, NULL, '{\"resource_id\":4,\"asset_tag\":\"TEST-LAPTOP-9502\",\"brand\":\"Apple\",\"model\":\"M3 Pro\",\"serial_number\":null,\"specs\":null,\"condition\":\"excellent\",\"lifecycle_status\":\"in_use\",\"purchase_cost\":\"0.00\",\"accumulated_cost\":\"0.00\",\"current_holder_id\":12,\"updated_at\":\"2026-09-14T03:24:28.000000Z\",\"created_at\":\"2026-09-14T03:24:28.000000Z\",\"id\":2}', '127.0.0.1', 'Symfony', 'Registered asset TEST-LAPTOP-9502 (Apple M3 Pro)', '2026-09-13 20:24:28', '2026-09-13 20:24:28'),
(40, 1, 'logout', 'User', 1, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'User logged out', '2026-09-13 20:24:32', '2026-09-13 20:24:32'),
(41, 11, 'login', 'User', 11, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'User logged into Solvia.Nova OS', '2026-09-13 20:24:50', '2026-09-13 20:24:50'),
(42, 11, 'create', 'Asset', 3, NULL, '{\"resource_id\":5,\"asset_tag\":\"AST-PX4UGU\",\"brand\":\"Asus\",\"model\":\"Asus vivobook\",\"serial_number\":null,\"specs\":\"4256\",\"condition\":\"good\",\"lifecycle_status\":\"in_use\",\"purchase_cost\":\"0.00\",\"accumulated_cost\":\"0.00\",\"current_holder_id\":11,\"updated_at\":\"2026-09-14T03:25:39.000000Z\",\"created_at\":\"2026-09-14T03:25:39.000000Z\",\"id\":3}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'Registered asset AST-PX4UGU (Asus Asus vivobook)', '2026-09-13 20:25:39', '2026-09-13 20:25:39'),
(43, NULL, 'create', 'Asset', 4, NULL, '{\"resource_id\":6,\"asset_tag\":\"TEST-LAPTOP-7719\",\"brand\":\"Apple\",\"model\":\"M3 Pro\",\"serial_number\":null,\"specs\":null,\"condition\":\"excellent\",\"lifecycle_status\":\"in_use\",\"purchase_cost\":\"0.00\",\"accumulated_cost\":\"0.00\",\"current_holder_id\":12,\"updated_at\":\"2026-09-14T03:26:50.000000Z\",\"created_at\":\"2026-09-14T03:26:50.000000Z\",\"id\":4}', '127.0.0.1', 'Symfony', 'Registered asset TEST-LAPTOP-7719 (Apple M3 Pro)', '2026-09-13 20:26:50', '2026-09-13 20:26:50'),
(44, NULL, 'create', 'Asset', 5, NULL, '{\"resource_id\":7,\"asset_tag\":\"TEST-LAPTOP-7222\",\"brand\":\"Apple\",\"model\":\"M3 Pro\",\"serial_number\":null,\"specs\":null,\"condition\":\"excellent\",\"lifecycle_status\":\"in_use\",\"purchase_cost\":\"0.00\",\"accumulated_cost\":\"0.00\",\"current_holder_id\":12,\"updated_at\":\"2026-09-14T03:27:09.000000Z\",\"created_at\":\"2026-09-14T03:27:09.000000Z\",\"id\":5}', '127.0.0.1', 'Symfony', 'Registered asset TEST-LAPTOP-7222 (Apple M3 Pro)', '2026-09-13 20:27:09', '2026-09-13 20:27:09'),
(45, NULL, 'create', 'Project', 3, NULL, '{\"project_code\":\"PRJ-TEST-6268\",\"name\":\"Dev Test Project 782\",\"client_id\":2,\"description\":\"Internal operational project test\",\"project_type\":\"internal\",\"start_date\":\"2026-09-14T00:00:00.000000Z\",\"deadline\":\"2026-10-14T00:00:00.000000Z\",\"revenue\":\"10000000.00\",\"budget\":\"5000000.00\",\"status\":\"active\",\"actual_cost\":\"0.00\",\"profit\":\"10000000.00\",\"profit_margin\":\"100.00\",\"health\":\"on_track\",\"updated_at\":\"2026-09-14T03:27:09.000000Z\",\"created_at\":\"2026-09-14T03:27:09.000000Z\",\"id\":3}', '127.0.0.1', 'Symfony', 'New project created: Dev Test Project 782', '2026-09-13 20:27:09', '2026-09-13 20:27:09'),
(46, NULL, 'create', 'Task', 2, NULL, '{\"title\":\"Build Navigation Component\",\"description\":null,\"milestone_id\":null,\"assignee_id\":12,\"priority\":\"high\",\"status\":\"in_progress\",\"progress\":30,\"start_date\":null,\"deadline\":\"2026-09-17T00:00:00.000000Z\",\"estimated_hours\":\"8.00\",\"project_id\":3,\"updated_at\":\"2026-09-14T03:27:09.000000Z\",\"created_at\":\"2026-09-14T03:27:09.000000Z\",\"id\":2,\"milestone\":null}', '127.0.0.1', 'Symfony', 'New task created: Build Navigation Component', '2026-09-13 20:27:09', '2026-09-13 20:27:09'),
(47, NULL, 'create', 'Milestone', 2, NULL, '{\"name\":\"Sprint 1 Delivery\",\"start_date\":\"2026-09-14T00:00:00.000000Z\",\"deadline\":\"2026-09-21T00:00:00.000000Z\",\"status\":\"in_progress\",\"project_id\":3,\"progress\":0,\"updated_at\":\"2026-09-14T03:27:09.000000Z\",\"created_at\":\"2026-09-14T03:27:09.000000Z\",\"id\":2}', '127.0.0.1', 'Symfony', 'New milestone created: Sprint 1 Delivery', '2026-09-13 20:27:09', '2026-09-13 20:27:09'),
(48, NULL, 'create', 'Blocker', 3, NULL, '{\"project_id\":3,\"title\":\"Missing API endpoint for auth\",\"description\":\"Waiting for backend user auth endpoint\",\"priority\":\"urgent\",\"type\":\"technical\",\"reporter_id\":12,\"status\":\"open\",\"updated_at\":\"2026-09-14T03:27:09.000000Z\",\"created_at\":\"2026-09-14T03:27:09.000000Z\",\"id\":3}', '127.0.0.1', 'Symfony', 'New blocker reported on project #3: Missing API endpoint for auth', '2026-09-13 20:27:09', '2026-09-13 20:27:09'),
(49, NULL, 'create', 'Asset', 6, NULL, '{\"resource_id\":8,\"asset_tag\":\"TEST-LAPTOP-1871\",\"brand\":\"Apple\",\"model\":\"M3 Pro\",\"serial_number\":null,\"specs\":null,\"condition\":\"excellent\",\"lifecycle_status\":\"in_use\",\"purchase_cost\":\"0.00\",\"accumulated_cost\":\"0.00\",\"current_holder_id\":12,\"updated_at\":\"2026-09-14T03:27:21.000000Z\",\"created_at\":\"2026-09-14T03:27:21.000000Z\",\"id\":6}', '127.0.0.1', 'Symfony', 'Registered asset TEST-LAPTOP-1871 (Apple M3 Pro)', '2026-09-13 20:27:21', '2026-09-13 20:27:21'),
(50, NULL, 'create', 'Project', 4, NULL, '{\"project_code\":\"PRJ-TEST-3875\",\"name\":\"Dev Test Project 770\",\"client_id\":2,\"description\":\"Internal operational project test\",\"project_type\":\"internal\",\"start_date\":\"2026-09-14T00:00:00.000000Z\",\"deadline\":\"2026-10-14T00:00:00.000000Z\",\"revenue\":\"10000000.00\",\"budget\":\"5000000.00\",\"status\":\"active\",\"actual_cost\":\"0.00\",\"profit\":\"10000000.00\",\"profit_margin\":\"100.00\",\"health\":\"on_track\",\"updated_at\":\"2026-09-14T03:27:21.000000Z\",\"created_at\":\"2026-09-14T03:27:21.000000Z\",\"id\":4}', '127.0.0.1', 'Symfony', 'New project created: Dev Test Project 770', '2026-09-13 20:27:21', '2026-09-13 20:27:21'),
(51, NULL, 'create', 'Task', 3, NULL, '{\"title\":\"Build Navigation Component\",\"description\":null,\"milestone_id\":null,\"assignee_id\":12,\"priority\":\"high\",\"status\":\"in_progress\",\"progress\":30,\"start_date\":null,\"deadline\":\"2026-09-17T00:00:00.000000Z\",\"estimated_hours\":\"8.00\",\"project_id\":4,\"updated_at\":\"2026-09-14T03:27:21.000000Z\",\"created_at\":\"2026-09-14T03:27:21.000000Z\",\"id\":3,\"milestone\":null}', '127.0.0.1', 'Symfony', 'New task created: Build Navigation Component', '2026-09-13 20:27:21', '2026-09-13 20:27:21'),
(52, NULL, 'create', 'Milestone', 3, NULL, '{\"name\":\"Sprint 1 Delivery\",\"start_date\":\"2026-09-14T00:00:00.000000Z\",\"deadline\":\"2026-09-21T00:00:00.000000Z\",\"status\":\"in_progress\",\"project_id\":4,\"progress\":0,\"updated_at\":\"2026-09-14T03:27:21.000000Z\",\"created_at\":\"2026-09-14T03:27:21.000000Z\",\"id\":3}', '127.0.0.1', 'Symfony', 'New milestone created: Sprint 1 Delivery', '2026-09-13 20:27:21', '2026-09-13 20:27:21'),
(53, NULL, 'create', 'Blocker', 4, NULL, '{\"project_id\":4,\"title\":\"Missing API endpoint for auth\",\"description\":\"Waiting for backend user auth endpoint\",\"priority\":\"urgent\",\"type\":\"technical\",\"reporter_id\":12,\"status\":\"open\",\"updated_at\":\"2026-09-14T03:27:21.000000Z\",\"created_at\":\"2026-09-14T03:27:21.000000Z\",\"id\":4}', '127.0.0.1', 'Symfony', 'New blocker reported on project #4: Missing API endpoint for auth', '2026-09-13 20:27:21', '2026-09-13 20:27:21'),
(54, NULL, 'create', 'Reimbursement', 1, NULL, '{\"title\":\"Dev Team Coffee & Snacks\",\"amount\":\"75000.00\",\"date\":\"2026-09-14T00:00:00.000000Z\",\"project_id\":null,\"category\":\"meals\",\"notes\":\"Working overtime on sprint release\",\"user_id\":12,\"status\":\"submitted\",\"updated_at\":\"2026-09-14T03:27:21.000000Z\",\"created_at\":\"2026-09-14T03:27:21.000000Z\",\"id\":1}', '127.0.0.1', 'Symfony', 'Reimbursement submitted by Test Frontend Dev', '2026-09-13 20:27:21', '2026-09-13 20:27:21'),
(55, NULL, 'create', 'Asset', 7, NULL, '{\"resource_id\":9,\"asset_tag\":\"TEST-LAPTOP-6658\",\"brand\":\"Apple\",\"model\":\"M3 Pro\",\"serial_number\":null,\"specs\":null,\"condition\":\"excellent\",\"lifecycle_status\":\"in_use\",\"purchase_cost\":\"0.00\",\"accumulated_cost\":\"0.00\",\"current_holder_id\":12,\"updated_at\":\"2026-09-14T03:27:31.000000Z\",\"created_at\":\"2026-09-14T03:27:31.000000Z\",\"id\":7}', '127.0.0.1', 'Symfony', 'Registered asset TEST-LAPTOP-6658 (Apple M3 Pro)', '2026-09-13 20:27:31', '2026-09-13 20:27:31'),
(56, NULL, 'create', 'Project', 5, NULL, '{\"project_code\":\"PRJ-TEST-1017\",\"name\":\"Dev Test Project 736\",\"client_id\":2,\"description\":\"Internal operational project test\",\"project_type\":\"internal\",\"start_date\":\"2026-09-14T00:00:00.000000Z\",\"deadline\":\"2026-10-14T00:00:00.000000Z\",\"revenue\":\"10000000.00\",\"budget\":\"5000000.00\",\"status\":\"active\",\"actual_cost\":\"0.00\",\"profit\":\"10000000.00\",\"profit_margin\":\"100.00\",\"health\":\"on_track\",\"updated_at\":\"2026-09-14T03:27:31.000000Z\",\"created_at\":\"2026-09-14T03:27:31.000000Z\",\"id\":5}', '127.0.0.1', 'Symfony', 'New project created: Dev Test Project 736', '2026-09-13 20:27:31', '2026-09-13 20:27:31'),
(57, NULL, 'create', 'Task', 4, NULL, '{\"title\":\"Build Navigation Component\",\"description\":null,\"milestone_id\":null,\"assignee_id\":12,\"priority\":\"high\",\"status\":\"in_progress\",\"progress\":30,\"start_date\":null,\"deadline\":\"2026-09-17T00:00:00.000000Z\",\"estimated_hours\":\"8.00\",\"project_id\":5,\"updated_at\":\"2026-09-14T03:27:31.000000Z\",\"created_at\":\"2026-09-14T03:27:31.000000Z\",\"id\":4,\"milestone\":null}', '127.0.0.1', 'Symfony', 'New task created: Build Navigation Component', '2026-09-13 20:27:31', '2026-09-13 20:27:31'),
(58, NULL, 'create', 'Milestone', 4, NULL, '{\"name\":\"Sprint 1 Delivery\",\"start_date\":\"2026-09-14T00:00:00.000000Z\",\"deadline\":\"2026-09-21T00:00:00.000000Z\",\"status\":\"in_progress\",\"project_id\":5,\"progress\":0,\"updated_at\":\"2026-09-14T03:27:31.000000Z\",\"created_at\":\"2026-09-14T03:27:31.000000Z\",\"id\":4}', '127.0.0.1', 'Symfony', 'New milestone created: Sprint 1 Delivery', '2026-09-13 20:27:31', '2026-09-13 20:27:31'),
(59, NULL, 'create', 'Blocker', 5, NULL, '{\"project_id\":5,\"title\":\"Missing API endpoint for auth\",\"description\":\"Waiting for backend user auth endpoint\",\"priority\":\"urgent\",\"type\":\"technical\",\"reporter_id\":12,\"status\":\"open\",\"updated_at\":\"2026-09-14T03:27:31.000000Z\",\"created_at\":\"2026-09-14T03:27:31.000000Z\",\"id\":5}', '127.0.0.1', 'Symfony', 'New blocker reported on project #5: Missing API endpoint for auth', '2026-09-13 20:27:31', '2026-09-13 20:27:31'),
(60, NULL, 'create', 'Reimbursement', 2, NULL, '{\"title\":\"Dev Team Coffee & Snacks\",\"amount\":\"75000.00\",\"date\":\"2026-09-14T00:00:00.000000Z\",\"project_id\":null,\"category\":\"meals\",\"notes\":\"Working overtime on sprint release\",\"user_id\":12,\"status\":\"submitted\",\"updated_at\":\"2026-09-14T03:27:31.000000Z\",\"created_at\":\"2026-09-14T03:27:31.000000Z\",\"id\":2}', '127.0.0.1', 'Symfony', 'Reimbursement submitted by Test Frontend Dev', '2026-09-13 20:27:31', '2026-09-13 20:27:31'),
(61, 11, 'logout', 'User', 11, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'User logged out', '2026-09-13 20:29:26', '2026-09-13 20:29:26'),
(62, 1, 'login', 'User', 1, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'User logged into Solvia.Nova OS', '2026-09-13 20:30:56', '2026-09-13 20:30:56'),
(63, 1, 'create', 'Client', 5, NULL, '{\"client_code\":\"CLT-2026-214\",\"name\":\"Hydrone\",\"contact_person\":\"Zaidan Zikron\",\"email\":\"zaidan@gmail.com\",\"phone\":\"546456546546\",\"address\":\"sd\",\"website\":\"hydrone.id\",\"notes\":\"sdf\",\"status\":\"active\",\"updated_at\":\"2026-09-14T03:32:09.000000Z\",\"created_at\":\"2026-09-14T03:32:09.000000Z\",\"id\":5}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'New client registered: Hydrone', '2026-09-13 20:32:09', '2026-09-13 20:32:09'),
(64, 1, 'create', 'Project', 6, NULL, '{\"project_code\":\"PRJ-6AA76AC783FA4\",\"name\":\"Hydrone\",\"client_id\":\"5\",\"description\":null,\"project_type\":\"IoT Engineering\",\"start_date\":\"2026-09-14T00:00:00.000000Z\",\"deadline\":\"2026-10-29T00:00:00.000000Z\",\"revenue\":\"23000000.00\",\"budget\":\"15000000.00\",\"status\":\"active\",\"actual_cost\":\"0.00\",\"profit\":\"23000000.00\",\"profit_margin\":\"100.00\",\"health\":\"on_track\",\"updated_at\":\"2026-09-14T03:32:51.000000Z\",\"created_at\":\"2026-09-14T03:32:51.000000Z\",\"id\":6}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'New project created: Hydrone', '2026-09-13 20:32:51', '2026-09-13 20:32:51'),
(65, 1, 'create', 'Task', 5, NULL, '{\"title\":\"Design Website\",\"description\":null,\"milestone_id\":null,\"assignee_id\":\"11\",\"priority\":\"urgent\",\"status\":\"in_progress\",\"progress\":0,\"start_date\":null,\"deadline\":\"2026-09-21T00:00:00.000000Z\",\"estimated_hours\":\"30.00\",\"project_id\":6,\"updated_at\":\"2026-09-14T03:34:02.000000Z\",\"created_at\":\"2026-09-14T03:34:02.000000Z\",\"id\":5,\"milestone\":null}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'New task created: Design Website', '2026-09-13 20:34:02', '2026-09-13 20:34:02'),
(66, 1, 'logout', 'User', 1, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'User logged out', '2026-09-13 20:34:09', '2026-09-13 20:34:09'),
(67, 11, 'login', 'User', 11, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'User logged into Solvia.Nova OS', '2026-09-13 20:34:22', '2026-09-13 20:34:22'),
(68, NULL, 'create', 'Project', 7, NULL, '{\"project_code\":\"PRJ-MONITOR-1562\",\"name\":\"Monitoring Project 119\",\"client_id\":2,\"description\":\"A project managed purely for monitoring execution\",\"project_type\":\"internal\",\"start_date\":\"2026-09-14T00:00:00.000000Z\",\"deadline\":\"2026-10-14T00:00:00.000000Z\",\"revenue\":\"0.00\",\"budget\":\"0.00\",\"status\":\"active\",\"actual_cost\":\"0.00\",\"profit\":\"0.00\",\"profit_margin\":\"0.00\",\"health\":\"on_track\",\"updated_at\":\"2026-09-14T03:35:13.000000Z\",\"created_at\":\"2026-09-14T03:35:13.000000Z\",\"id\":7}', '127.0.0.1', 'Symfony', 'New project created: Monitoring Project 119', '2026-09-13 20:35:13', '2026-09-13 20:35:13'),
(69, NULL, 'create', 'Project', 8, NULL, '{\"project_code\":\"PRJ-MONITOR-3396\",\"name\":\"Monitoring Project 956\",\"client_id\":2,\"description\":\"A project managed purely for monitoring execution\",\"project_type\":\"internal\",\"start_date\":\"2026-09-14T00:00:00.000000Z\",\"deadline\":\"2026-10-14T00:00:00.000000Z\",\"revenue\":\"0.00\",\"budget\":\"0.00\",\"status\":\"active\",\"actual_cost\":\"0.00\",\"profit\":\"0.00\",\"profit_margin\":\"0.00\",\"health\":\"on_track\",\"updated_at\":\"2026-09-14T03:35:54.000000Z\",\"created_at\":\"2026-09-14T03:35:54.000000Z\",\"id\":8}', '127.0.0.1', 'Symfony', 'New project created: Monitoring Project 956', '2026-09-13 20:35:54', '2026-09-13 20:35:54'),
(70, 1, 'create', 'Task', 6, NULL, '{\"title\":\"Test Auto Notification Task 468\",\"description\":null,\"milestone_id\":null,\"assignee_id\":11,\"priority\":\"high\",\"status\":\"to_do\",\"progress\":0,\"start_date\":null,\"deadline\":null,\"estimated_hours\":\"4.00\",\"project_id\":1,\"updated_at\":\"2026-09-14T03:48:41.000000Z\",\"created_at\":\"2026-09-14T03:48:41.000000Z\",\"id\":6,\"milestone\":null}', '127.0.0.1', 'Symfony', 'New task created: Test Auto Notification Task 468', '2026-09-13 20:48:41', '2026-09-13 20:48:41'),
(71, 11, 'claim', 'Task', 6, '{\"assignee_id\":11}', '{\"assignee_id\":11,\"status\":\"in_progress\"}', '127.0.0.1', 'Symfony', 'User Putra claimed task #6 (Test Auto Notification Task 468)', '2026-09-13 20:48:41', '2026-09-13 20:48:41'),
(72, 1, 'assign', 'Asset', 3, NULL, '{\"user_id\":11}', '127.0.0.1', 'Symfony', 'Asset AST-PX4UGU assigned to user #11', '2026-09-13 20:48:41', '2026-09-13 20:48:41'),
(73, 1, 'create', 'Task', 7, NULL, '{\"title\":\"QA Audit Task 7503\",\"description\":\"Automated QA flow verification test\",\"milestone_id\":null,\"assignee_id\":11,\"priority\":\"urgent\",\"status\":\"to_do\",\"progress\":0,\"start_date\":null,\"deadline\":null,\"estimated_hours\":\"5.00\",\"project_id\":1,\"updated_at\":\"2026-09-14T03:52:01.000000Z\",\"created_at\":\"2026-09-14T03:52:01.000000Z\",\"id\":7,\"milestone\":null}', '127.0.0.1', 'Symfony', 'New task created: QA Audit Task 7503', '2026-09-13 20:52:01', '2026-09-13 20:52:01'),
(74, 11, 'claim', 'Task', 7, '{\"assignee_id\":11}', '{\"assignee_id\":11,\"status\":\"in_progress\"}', '127.0.0.1', 'Symfony', 'User Putra claimed task #7 (QA Audit Task 7503)', '2026-09-13 20:52:01', '2026-09-13 20:52:01'),
(75, 11, 'create', 'DailyProgress', 5, NULL, '{\"project_id\":1,\"task_id\":7,\"date\":\"2026-09-14T00:00:00.000000Z\",\"progress\":60,\"completed_work\":\"Completed initial QA audit and test suite execution\",\"next_plan\":\"Finalize system test reports\",\"blocker\":null,\"working_hours\":\"4.50\",\"user_id\":11,\"updated_at\":\"2026-09-14T03:52:01.000000Z\",\"created_at\":\"2026-09-14T03:52:01.000000Z\",\"id\":5}', '127.0.0.1', 'Symfony', 'Daily progress submitted by Putra', '2026-09-13 20:52:02', '2026-09-13 20:52:02'),
(76, 1, 'create', 'Task', 8, NULL, '{\"title\":\"QA Audit Task 4260\",\"description\":\"Automated QA flow verification test\",\"milestone_id\":null,\"assignee_id\":11,\"priority\":\"urgent\",\"status\":\"to_do\",\"progress\":0,\"start_date\":null,\"deadline\":null,\"estimated_hours\":\"5.00\",\"project_id\":1,\"updated_at\":\"2026-09-14T03:52:40.000000Z\",\"created_at\":\"2026-09-14T03:52:40.000000Z\",\"id\":8,\"milestone\":null}', '127.0.0.1', 'Symfony', 'New task created: QA Audit Task 4260', '2026-09-13 20:52:40', '2026-09-13 20:52:40'),
(77, 11, 'claim', 'Task', 8, '{\"assignee_id\":11}', '{\"assignee_id\":11,\"status\":\"in_progress\"}', '127.0.0.1', 'Symfony', 'User Putra claimed task #8 (QA Audit Task 4260)', '2026-09-13 20:52:40', '2026-09-13 20:52:40'),
(78, 11, 'create', 'DailyProgress', 6, NULL, '{\"project_id\":1,\"task_id\":8,\"date\":\"2026-09-14T00:00:00.000000Z\",\"progress\":60,\"completed_work\":\"Completed initial QA audit and test suite execution\",\"next_plan\":\"Finalize system test reports\",\"blocker\":null,\"working_hours\":\"4.50\",\"user_id\":11,\"updated_at\":\"2026-09-14T03:52:40.000000Z\",\"created_at\":\"2026-09-14T03:52:40.000000Z\",\"id\":6}', '127.0.0.1', 'Symfony', 'Daily progress submitted by Putra', '2026-09-13 20:52:40', '2026-09-13 20:52:40'),
(79, 11, 'create', 'Blocker', 6, NULL, '{\"project_id\":1,\"task_id\":8,\"title\":\"QA Mock Blocker 282\",\"description\":\"Testing blocker reporting flow and task status\",\"priority\":\"high\",\"type\":\"technical\",\"reporter_id\":11,\"status\":\"open\",\"updated_at\":\"2026-09-14T03:52:40.000000Z\",\"created_at\":\"2026-09-14T03:52:40.000000Z\",\"id\":6}', '127.0.0.1', 'Symfony', 'New blocker reported on project #1: QA Mock Blocker 282', '2026-09-13 20:52:40', '2026-09-13 20:52:40'),
(80, 1, 'create', 'Task', 9, NULL, '{\"title\":\"QA Audit Task 1997\",\"description\":\"Automated QA flow verification test\",\"milestone_id\":null,\"assignee_id\":11,\"priority\":\"urgent\",\"status\":\"to_do\",\"progress\":0,\"start_date\":null,\"deadline\":null,\"estimated_hours\":\"5.00\",\"project_id\":1,\"updated_at\":\"2026-09-14T03:53:03.000000Z\",\"created_at\":\"2026-09-14T03:53:03.000000Z\",\"id\":9,\"milestone\":null}', '127.0.0.1', 'Symfony', 'New task created: QA Audit Task 1997', '2026-09-13 20:53:03', '2026-09-13 20:53:03'),
(81, 11, 'claim', 'Task', 9, '{\"assignee_id\":11}', '{\"assignee_id\":11,\"status\":\"in_progress\"}', '127.0.0.1', 'Symfony', 'User Putra claimed task #9 (QA Audit Task 1997)', '2026-09-13 20:53:03', '2026-09-13 20:53:03'),
(82, 11, 'create', 'DailyProgress', 7, NULL, '{\"project_id\":1,\"task_id\":9,\"date\":\"2026-09-14T00:00:00.000000Z\",\"progress\":60,\"completed_work\":\"Completed initial QA audit and test suite execution\",\"next_plan\":\"Finalize system test reports\",\"blocker\":null,\"working_hours\":\"4.50\",\"user_id\":11,\"updated_at\":\"2026-09-14T03:53:03.000000Z\",\"created_at\":\"2026-09-14T03:53:03.000000Z\",\"id\":7}', '127.0.0.1', 'Symfony', 'Daily progress submitted by Putra', '2026-09-13 20:53:03', '2026-09-13 20:53:03'),
(83, 11, 'create', 'Blocker', 7, NULL, '{\"project_id\":1,\"task_id\":9,\"title\":\"QA Mock Blocker 999\",\"description\":\"Testing blocker reporting flow and task status\",\"priority\":\"high\",\"type\":\"technical\",\"reporter_id\":11,\"status\":\"open\",\"updated_at\":\"2026-09-14T03:53:03.000000Z\",\"created_at\":\"2026-09-14T03:53:03.000000Z\",\"id\":7}', '127.0.0.1', 'Symfony', 'New blocker reported on project #1: QA Mock Blocker 999', '2026-09-13 20:53:03', '2026-09-13 20:53:03'),
(84, 11, 'create', 'Reimbursement', 3, NULL, '{\"title\":\"QA Operational Reimbursement Claim\",\"amount\":\"75000.00\",\"date\":\"2026-09-14T00:00:00.000000Z\",\"project_id\":1,\"category\":\"materials\",\"notes\":null,\"user_id\":11,\"status\":\"submitted\",\"updated_at\":\"2026-09-14T03:53:03.000000Z\",\"created_at\":\"2026-09-14T03:53:03.000000Z\",\"id\":3}', '127.0.0.1', 'Symfony', 'Reimbursement submitted by Putra', '2026-09-13 20:53:03', '2026-09-13 20:53:03'),
(85, 11, 'logout', 'User', 11, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'User logged out', '2026-09-13 20:55:50', '2026-09-13 20:55:50'),
(86, 14, 'login', 'User', 14, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'User logged into Solvia.Nova OS', '2026-09-13 20:57:01', '2026-09-13 20:57:01'),
(87, 14, 'create', 'Income', 3, NULL, '{\"income_number\":\"INC-2026-6784\",\"date\":\"2026-09-14T00:00:00.000000Z\",\"source\":\"re\",\"client_id\":\"5\",\"project_id\":\"6\",\"amount\":\"25000000.00\",\"category\":\"Client Invoice Payment\",\"account_id\":\"1\",\"notes\":\"dbf db\",\"updated_at\":\"2026-09-14T03:58:22.000000Z\",\"created_at\":\"2026-09-14T03:58:22.000000Z\",\"id\":3,\"project\":{\"id\":6,\"project_code\":\"PRJ-6AA76AC783FA4\",\"name\":\"Hydrone\",\"client_id\":5,\"description\":null,\"project_type\":\"IoT Engineering\",\"start_date\":\"2026-09-14T00:00:00.000000Z\",\"deadline\":\"2026-10-29T00:00:00.000000Z\",\"revenue\":\"23000000.00\",\"budget\":\"15000000.00\",\"actual_cost\":\"0.00\",\"profit\":\"23000000.00\",\"profit_margin\":\"100.00\",\"status\":\"active\",\"health\":\"on_track\",\"closed_at\":null,\"closure_notes\":null,\"created_at\":\"2026-09-14T03:32:51.000000Z\",\"updated_at\":\"2026-09-14T03:32:51.000000Z\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'Income recorded: INC-2026-6784 (Rp 25.000.000)', '2026-09-13 20:58:22', '2026-09-13 20:58:22'),
(88, 14, 'create', 'Income', 4, NULL, '{\"income_number\":\"INC-2026-7609\",\"date\":\"2026-09-14T00:00:00.000000Z\",\"source\":\"fbgdfb\",\"client_id\":\"2\",\"project_id\":\"1\",\"amount\":\"500000.00\",\"category\":\"Project Milestone\",\"account_id\":\"1\",\"notes\":\"sdvsdv\",\"updated_at\":\"2026-09-14T03:58:43.000000Z\",\"created_at\":\"2026-09-14T03:58:43.000000Z\",\"id\":4,\"project\":{\"id\":1,\"project_code\":\"PRJ-AUDIT-1\",\"name\":\"Audit Project\",\"client_id\":2,\"description\":null,\"project_type\":\"Internal\",\"start_date\":\"2026-09-14T00:00:00.000000Z\",\"deadline\":\"2026-10-14T00:00:00.000000Z\",\"revenue\":\"10000000.00\",\"budget\":\"5000000.00\",\"actual_cost\":\"0.00\",\"profit\":\"10000000.00\",\"profit_margin\":\"100.00\",\"status\":\"active\",\"health\":\"at_risk\",\"closed_at\":null,\"closure_notes\":null,\"created_at\":\"2026-09-14T02:43:33.000000Z\",\"updated_at\":\"2026-09-14T02:44:41.000000Z\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'Income recorded: INC-2026-7609 (Rp 500.000)', '2026-09-13 20:58:43', '2026-09-13 20:58:43'),
(89, 14, 'create', 'Expense', 3, NULL, '{\"expense_number\":\"EXP-2026-6762\",\"date\":\"2026-09-14T00:00:00.000000Z\",\"category\":\"marketing\",\"project_id\":\"1\",\"amount\":\"5000000.00\",\"account_id\":\"1\",\"vendor\":\"nbv\",\"notes\":\"vbn\",\"updated_at\":\"2026-09-14T03:59:13.000000Z\",\"created_at\":\"2026-09-14T03:59:13.000000Z\",\"id\":3,\"project\":{\"id\":1,\"project_code\":\"PRJ-AUDIT-1\",\"name\":\"Audit Project\",\"client_id\":2,\"description\":null,\"project_type\":\"Internal\",\"start_date\":\"2026-09-14T00:00:00.000000Z\",\"deadline\":\"2026-10-14T00:00:00.000000Z\",\"revenue\":\"10000000.00\",\"budget\":\"5000000.00\",\"actual_cost\":\"5000000.00\",\"profit\":\"5000000.00\",\"profit_margin\":\"50.00\",\"status\":\"active\",\"health\":\"at_risk\",\"closed_at\":null,\"closure_notes\":null,\"created_at\":\"2026-09-14T02:43:33.000000Z\",\"updated_at\":\"2026-09-14T03:59:13.000000Z\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'Expense recorded: EXP-2026-6762 (Rp 5.000.000)', '2026-09-13 20:59:13', '2026-09-13 20:59:13'),
(90, 14, 'create', 'Invoice', 2, NULL, '{\"invoice_number\":\"INV-2026-957\",\"client_id\":\"2\",\"project_id\":\"1\",\"issue_date\":\"2026-09-14T00:00:00.000000Z\",\"due_date\":\"2026-09-28T00:00:00.000000Z\",\"subtotal\":\"25000000.00\",\"discount\":\"0.00\",\"tax\":\"0.00\",\"total\":\"25000000.00\",\"payment_status\":\"sent\",\"notes\":null,\"updated_at\":\"2026-09-14T03:59:33.000000Z\",\"created_at\":\"2026-09-14T03:59:33.000000Z\",\"id\":2}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'Invoice #INV-2026-957 created for Rp 25.000.000', '2026-09-13 20:59:33', '2026-09-13 20:59:33'),
(91, 14, 'pay', 'Invoice', 2, NULL, '{\"payment_status\":\"paid\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'Invoice #INV-2026-957 marked as PAID', '2026-09-13 20:59:35', '2026-09-13 20:59:35'),
(92, 14, 'create', 'Payroll', 1, NULL, '{\"user_id\":\"11\",\"total\":600000}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'Payroll generated for user #11 period 2026-09', '2026-09-13 21:00:07', '2026-09-13 21:00:07'),
(93, 14, 'create', 'Reimbursement', 4, NULL, '{\"title\":\"bnbv\",\"amount\":\"10000000.00\",\"date\":\"2026-09-14T00:00:00.000000Z\",\"project_id\":\"1\",\"category\":\"transportation\",\"notes\":null,\"user_id\":14,\"status\":\"submitted\",\"updated_at\":\"2026-09-14T04:01:10.000000Z\",\"created_at\":\"2026-09-14T04:01:10.000000Z\",\"id\":4}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'Reimbursement submitted by Nurul Arista', '2026-09-13 21:01:10', '2026-09-13 21:01:10'),
(94, 14, 'approved', 'Reimbursement', 4, NULL, '{\"status\":\"approved\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'Reimbursement #4 approved', '2026-09-13 21:01:14', '2026-09-13 21:01:14'),
(95, 14, 'create', 'Budget', 1, NULL, '{\"scope_type\":\"project\",\"project_id\":\"6\",\"category\":\"salary\",\"month\":1,\"year\":2026,\"budgeted_amount\":\"20000000.00\",\"actual_amount\":\"0.00\",\"updated_at\":\"2026-09-14T04:02:02.000000Z\",\"created_at\":\"2026-09-14T04:02:02.000000Z\",\"id\":1}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'Budget created for project', '2026-09-13 21:02:02', '2026-09-13 21:02:02'),
(96, 1, 'pay', 'Payroll', 1, NULL, '{\"payment_status\":\"paid\"}', '127.0.0.1', 'Symfony', 'Payroll #1 for Putra paid (Rp 600.000)', '2026-09-13 21:06:53', '2026-09-13 21:06:53'),
(97, 1, 'create', 'Payroll', 2, NULL, '{\"user_id\":1,\"total\":1750000}', '127.0.0.1', 'Symfony', 'Payroll generated for user #1 period 2026-09', '2026-09-13 21:06:53', '2026-09-13 21:06:53'),
(98, 1, 'pay', 'Payroll', 2, NULL, '{\"payment_status\":\"paid\"}', '127.0.0.1', 'Symfony', 'Payroll #2 for Nanda Ariwahyu paid (Rp 1.750.000)', '2026-09-13 21:06:53', '2026-09-13 21:06:53'),
(99, 1, 'create', 'Task', 10, NULL, '{\"title\":\"QA Audit Task 7863\",\"description\":\"Automated QA flow verification test\",\"milestone_id\":null,\"assignee_id\":11,\"priority\":\"urgent\",\"status\":\"to_do\",\"progress\":0,\"start_date\":null,\"deadline\":null,\"estimated_hours\":\"5.00\",\"project_id\":1,\"updated_at\":\"2026-09-14T04:07:40.000000Z\",\"created_at\":\"2026-09-14T04:07:40.000000Z\",\"id\":10,\"milestone\":null}', '127.0.0.1', 'Symfony', 'New task created: QA Audit Task 7863', '2026-09-13 21:07:40', '2026-09-13 21:07:40'),
(100, 11, 'claim', 'Task', 10, '{\"assignee_id\":11}', '{\"assignee_id\":11,\"status\":\"in_progress\"}', '127.0.0.1', 'Symfony', 'User Putra claimed task #10 (QA Audit Task 7863)', '2026-09-13 21:07:40', '2026-09-13 21:07:40'),
(101, 11, 'create', 'DailyProgress', 8, NULL, '{\"project_id\":1,\"task_id\":10,\"date\":\"2026-09-14T00:00:00.000000Z\",\"progress\":60,\"completed_work\":\"Completed initial QA audit and test suite execution\",\"next_plan\":\"Finalize system test reports\",\"blocker\":null,\"working_hours\":\"4.50\",\"user_id\":11,\"updated_at\":\"2026-09-14T04:07:40.000000Z\",\"created_at\":\"2026-09-14T04:07:40.000000Z\",\"id\":8}', '127.0.0.1', 'Symfony', 'Daily progress submitted by Putra', '2026-09-13 21:07:40', '2026-09-13 21:07:40'),
(102, 11, 'create', 'Blocker', 8, NULL, '{\"project_id\":1,\"task_id\":10,\"title\":\"QA Mock Blocker 271\",\"description\":\"Testing blocker reporting flow and task status\",\"priority\":\"high\",\"type\":\"technical\",\"reporter_id\":11,\"status\":\"open\",\"updated_at\":\"2026-09-14T04:07:40.000000Z\",\"created_at\":\"2026-09-14T04:07:40.000000Z\",\"id\":8}', '127.0.0.1', 'Symfony', 'New blocker reported on project #1: QA Mock Blocker 271', '2026-09-13 21:07:40', '2026-09-13 21:07:40'),
(103, 11, 'create', 'Reimbursement', 5, NULL, '{\"title\":\"QA Operational Reimbursement Claim\",\"amount\":\"75000.00\",\"date\":\"2026-09-14T00:00:00.000000Z\",\"project_id\":1,\"category\":\"materials\",\"notes\":null,\"user_id\":11,\"status\":\"submitted\",\"updated_at\":\"2026-09-14T04:07:40.000000Z\",\"created_at\":\"2026-09-14T04:07:40.000000Z\",\"id\":5}', '127.0.0.1', 'Symfony', 'Reimbursement submitted by Putra', '2026-09-13 21:07:40', '2026-09-13 21:07:40');
INSERT INTO `audit_logs` (`id`, `actor_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `description`, `created_at`, `updated_at`) VALUES
(104, 1, 'create', 'FinancialTransaction', 7, NULL, '{\"transaction_code\":\"TRX-202609-FYPCFI\",\"transaction_type\":\"CAPITAL_IN\",\"category\":\"Owner Equity Injection\",\"amount\":\"2000000.00\",\"transaction_date\":\"2026-09-14T00:00:00.000000Z\",\"financial_account_id\":1,\"to_account_id\":null,\"project_id\":null,\"invoice_id\":null,\"employee_id\":null,\"asset_id\":null,\"resource_id\":null,\"reference_type\":\"capital\",\"reference_id\":null,\"description\":\"Owner Capital Test Injection\",\"status\":\"posted\",\"created_by\":1,\"approved_by\":null,\"approved_at\":null,\"updated_at\":\"2026-09-14T04:24:09.000000Z\",\"created_at\":\"2026-09-14T04:24:09.000000Z\",\"id\":7}', '127.0.0.1', 'Symfony', 'Ledger CAPITAL_IN of Rp 2.000.000 recorded (TRX-202609-FYPCFI)', '2026-09-13 21:24:09', '2026-09-13 21:24:09'),
(105, 1, 'create', 'FinancialTransaction', 8, NULL, '{\"transaction_code\":\"TRX-202609-78QP30\",\"transaction_type\":\"INCOME\",\"category\":\"Project Revenue\",\"amount\":\"100000.00\",\"transaction_date\":\"2026-09-14T00:00:00.000000Z\",\"financial_account_id\":1,\"to_account_id\":null,\"project_id\":1,\"invoice_id\":2,\"employee_id\":null,\"asset_id\":null,\"resource_id\":null,\"reference_type\":\"invoice_payment\",\"reference_id\":2,\"description\":\"Client payment for Invoice #INV-2026-957 (Audit Client)\",\"status\":\"posted\",\"created_by\":1,\"approved_by\":null,\"approved_at\":null,\"updated_at\":\"2026-09-14T04:24:09.000000Z\",\"created_at\":\"2026-09-14T04:24:09.000000Z\",\"id\":8}', '127.0.0.1', 'Symfony', 'Ledger INCOME of Rp 100.000 recorded (TRX-202609-78QP30)', '2026-09-13 21:24:09', '2026-09-13 21:24:09'),
(106, 1, 'create', 'InvoicePayment', 2, NULL, '{\"payment_number\":\"PAY-INV-6AA776E92B2CD\",\"invoice_id\":2,\"financial_account_id\":1,\"amount\":\"100000.00\",\"payment_date\":\"2026-09-14T00:00:00.000000Z\",\"payment_method\":\"bank_transfer\",\"reference_number\":\"REF-TEST-99\",\"notes\":\"Partial payment test\",\"created_by\":1,\"updated_at\":\"2026-09-14T04:24:09.000000Z\",\"created_at\":\"2026-09-14T04:24:09.000000Z\",\"id\":2}', '127.0.0.1', 'Symfony', 'Invoice #INV-2026-957 received payment Rp 100.000', '2026-09-13 21:24:09', '2026-09-13 21:24:09'),
(107, 1, 'create', 'Payable', 1, NULL, '{\"payable_code\":\"PAY-2026-EXM2X\",\"title\":\"Server Cloud Verification Invoice\",\"vendor_name\":\"PT Cloud Hostindo Verification\",\"creditor_type\":\"vendor\",\"project_id\":null,\"category\":\"Infrastructure\",\"amount\":\"750000.00\",\"paid_amount\":\"0.00\",\"issue_date\":\"2026-09-14T00:00:00.000000Z\",\"due_date\":\"2026-09-29T00:00:00.000000Z\",\"status\":\"pending\",\"notes\":\"Test payable record\",\"updated_at\":\"2026-09-14T04:24:09.000000Z\",\"created_at\":\"2026-09-14T04:24:09.000000Z\",\"id\":1}', '127.0.0.1', 'Symfony', 'Payable created: Server Cloud Verification Invoice (Rp 750.000)', '2026-09-13 21:24:09', '2026-09-13 21:24:09'),
(108, 1, 'create', 'FinancialTransaction', 9, NULL, '{\"transaction_code\":\"TRX-202609-XN8HDX\",\"transaction_type\":\"EXPENSE\",\"category\":\"Infrastructure\",\"amount\":\"750000.00\",\"transaction_date\":\"2026-09-14T00:00:00.000000Z\",\"financial_account_id\":1,\"to_account_id\":null,\"project_id\":null,\"invoice_id\":null,\"employee_id\":null,\"asset_id\":null,\"resource_id\":null,\"reference_type\":\"payable\",\"reference_id\":1,\"description\":\"Payment of Payable PAY-2026-EXM2X to PT Cloud Hostindo Verification\",\"status\":\"posted\",\"created_by\":1,\"approved_by\":null,\"approved_at\":null,\"updated_at\":\"2026-09-14T04:24:09.000000Z\",\"created_at\":\"2026-09-14T04:24:09.000000Z\",\"id\":9}', '127.0.0.1', 'Symfony', 'Ledger EXPENSE of Rp 750.000 recorded (TRX-202609-XN8HDX)', '2026-09-13 21:24:09', '2026-09-13 21:24:09'),
(109, 1, 'create', 'Task', 11, NULL, '{\"title\":\"QA Audit Task 1929\",\"description\":\"Automated QA flow verification test\",\"milestone_id\":null,\"assignee_id\":11,\"priority\":\"urgent\",\"status\":\"to_do\",\"progress\":0,\"start_date\":null,\"deadline\":null,\"estimated_hours\":\"5.00\",\"project_id\":1,\"updated_at\":\"2026-09-14T04:24:37.000000Z\",\"created_at\":\"2026-09-14T04:24:37.000000Z\",\"id\":11,\"milestone\":null}', '127.0.0.1', 'Symfony', 'New task created: QA Audit Task 1929', '2026-09-13 21:24:38', '2026-09-13 21:24:38'),
(110, 11, 'claim', 'Task', 11, '{\"assignee_id\":11}', '{\"assignee_id\":11,\"status\":\"in_progress\"}', '127.0.0.1', 'Symfony', 'User Putra claimed task #11 (QA Audit Task 1929)', '2026-09-13 21:24:38', '2026-09-13 21:24:38'),
(111, 11, 'create', 'DailyProgress', 9, NULL, '{\"project_id\":1,\"task_id\":11,\"date\":\"2026-09-14T00:00:00.000000Z\",\"progress\":60,\"completed_work\":\"Completed initial QA audit and test suite execution\",\"next_plan\":\"Finalize system test reports\",\"blocker\":null,\"working_hours\":\"4.50\",\"user_id\":11,\"updated_at\":\"2026-09-14T04:24:38.000000Z\",\"created_at\":\"2026-09-14T04:24:38.000000Z\",\"id\":9}', '127.0.0.1', 'Symfony', 'Daily progress submitted by Putra', '2026-09-13 21:24:38', '2026-09-13 21:24:38'),
(112, 11, 'create', 'Blocker', 9, NULL, '{\"project_id\":1,\"task_id\":11,\"title\":\"QA Mock Blocker 711\",\"description\":\"Testing blocker reporting flow and task status\",\"priority\":\"high\",\"type\":\"technical\",\"reporter_id\":11,\"status\":\"open\",\"updated_at\":\"2026-09-14T04:24:38.000000Z\",\"created_at\":\"2026-09-14T04:24:38.000000Z\",\"id\":9}', '127.0.0.1', 'Symfony', 'New blocker reported on project #1: QA Mock Blocker 711', '2026-09-13 21:24:38', '2026-09-13 21:24:38'),
(113, 11, 'create', 'Reimbursement', 6, NULL, '{\"title\":\"QA Operational Reimbursement Claim\",\"amount\":\"75000.00\",\"date\":\"2026-09-14T00:00:00.000000Z\",\"project_id\":1,\"category\":\"materials\",\"notes\":null,\"user_id\":11,\"status\":\"submitted\",\"updated_at\":\"2026-09-14T04:24:38.000000Z\",\"created_at\":\"2026-09-14T04:24:38.000000Z\",\"id\":6}', '127.0.0.1', 'Symfony', 'Reimbursement submitted by Putra', '2026-09-13 21:24:38', '2026-09-13 21:24:38'),
(114, 14, 'create', 'AssetMaintenance', 1, NULL, '{\"maintenance_type\":\"preventative\",\"scheduled_date\":\"2026-09-14T00:00:00.000000Z\",\"technician_vendor\":null,\"cost\":\"0.00\",\"notes\":null,\"status\":\"scheduled\",\"asset_id\":3,\"completed_date\":null,\"expense_id\":null,\"updated_at\":\"2026-09-14T04:26:08.000000Z\",\"created_at\":\"2026-09-14T04:26:08.000000Z\",\"id\":1}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'Maintenance logged for asset AST-PX4UGU', '2026-09-13 21:26:08', '2026-09-13 21:26:08'),
(115, 14, 'delete', 'User', 15, '{\"id\":15,\"name\":\"Nurul Arista\",\"email\":\"nurul@solvia.id\",\"email_verified_at\":null,\"phone\":null,\"role\":\"super_admin\",\"department\":null,\"team_id\":null,\"join_date\":\"2026-09-14T00:00:00.000000Z\",\"status\":\"active\",\"avatar_url\":null,\"profile_bio\":null,\"permissions\":null,\"created_at\":\"2026-09-14T03:38:18.000000Z\",\"updated_at\":\"2026-09-14T03:38:18.000000Z\"}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'User Nurul Arista deleted', '2026-09-13 21:27:33', '2026-09-13 21:27:33'),
(116, 14, 'logout', 'User', 14, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'User logged out', '2026-09-13 21:28:06', '2026-09-13 21:28:06'),
(117, 11, 'login', 'User', 11, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'User logged into Solvia.Nova OS', '2026-09-13 21:28:16', '2026-09-13 21:28:16'),
(118, 11, 'update', 'User', 11, '{\"id\":11,\"name\":\"Putra\",\"email\":\"putra@gmail.com\",\"email_verified_at\":null,\"phone\":\"0868686868\",\"role\":\"designer\",\"department\":\"Solvia Nova\",\"team_id\":null,\"join_date\":\"2026-09-14T00:00:00.000000Z\",\"status\":\"active\",\"avatar_url\":null,\"profile_bio\":null,\"permissions\":null,\"created_at\":\"2026-09-14T03:08:02.000000Z\",\"updated_at\":\"2026-09-14T03:08:02.000000Z\"}', '{\"id\":11,\"name\":\"Putra Pratama (Senior Designer)\",\"email\":\"putra@gmail.com\",\"email_verified_at\":null,\"phone\":\"+62 812-9988-7766\",\"role\":\"designer\",\"department\":\"Creative & Product Design\",\"team_id\":null,\"join_date\":\"2026-09-14T00:00:00.000000Z\",\"status\":\"active\",\"avatar_url\":null,\"profile_bio\":\"Lead product designer specializing in dark mode interfaces and design systems.\",\"permissions\":null,\"created_at\":\"2026-09-14T03:08:02.000000Z\",\"updated_at\":\"2026-09-14T04:34:23.000000Z\"}', '127.0.0.1', 'Symfony', 'User Putra Pratama (Senior Designer) (designer) updated their profile without changing role', '2026-09-13 21:34:23', '2026-09-13 21:34:23'),
(119, 1, 'update', 'User', 1, '{\"id\":1,\"name\":\"Nanda Ariwahyu\",\"email\":\"nandaariwahyu07@gmail.com\",\"email_verified_at\":null,\"phone\":\"08111234567\",\"role\":\"super_admin\",\"department\":\"Executive Management\",\"team_id\":null,\"join_date\":\"2022-01-10T00:00:00.000000Z\",\"status\":\"active\",\"avatar_url\":null,\"profile_bio\":\"Founder & CEO of Solvia.Nova. Drives company vision, client strategy, and product direction.\",\"permissions\":null,\"created_at\":\"2026-09-12T04:43:53.000000Z\",\"updated_at\":\"2026-09-14T03:38:18.000000Z\"}', '{\"id\":1,\"name\":\"Nanda Ariwahyu\",\"email\":\"nandaariwahyu07@gmail.com\",\"email_verified_at\":null,\"phone\":null,\"role\":\"super_admin\",\"department\":\"Executive Office\",\"team_id\":null,\"join_date\":\"2022-01-10T00:00:00.000000Z\",\"status\":\"active\",\"avatar_url\":null,\"profile_bio\":\"Founder & Chief Executive Officer at Solvia Group.\",\"permissions\":null,\"created_at\":\"2026-09-12T04:43:53.000000Z\",\"updated_at\":\"2026-09-14T04:34:23.000000Z\"}', '127.0.0.1', 'Symfony', 'User Nanda Ariwahyu (super_admin) updated their profile without changing role', '2026-09-13 21:34:23', '2026-09-13 21:34:23'),
(120, 11, 'update', 'User', 11, '{\"id\":11,\"name\":\"Putra Pratama (Senior Designer)\",\"email\":\"putra@gmail.com\",\"email_verified_at\":null,\"phone\":\"+62 812-9988-7766\",\"role\":\"designer\",\"department\":\"Creative & Product Design\",\"team_id\":null,\"join_date\":\"2026-09-14T00:00:00.000000Z\",\"status\":\"active\",\"avatar_url\":null,\"profile_bio\":\"Lead product designer specializing in dark mode interfaces and design systems.\",\"permissions\":null,\"created_at\":\"2026-09-14T03:08:02.000000Z\",\"updated_at\":\"2026-09-14T04:34:23.000000Z\"}', '{\"id\":11,\"name\":\"Putra Pratama (Senior Designer)\",\"email\":\"putra@gmail.com\",\"email_verified_at\":null,\"phone\":\"+62 812-9988-7766\",\"role\":\"designer\",\"department\":\"Creative & Product Design\",\"team_id\":null,\"join_date\":\"2026-09-14T00:00:00.000000Z\",\"status\":\"active\",\"avatar_url\":null,\"profile_bio\":\"Lead product designer specializing in dark mode interfaces and design systems.\",\"permissions\":null,\"created_at\":\"2026-09-14T03:08:02.000000Z\",\"updated_at\":\"2026-09-14T04:34:23.000000Z\"}', '127.0.0.1', 'Symfony', 'User Putra Pratama (Senior Designer) (designer) updated their profile without changing role', '2026-09-13 21:34:46', '2026-09-13 21:34:46'),
(121, 1, 'update', 'User', 1, '{\"id\":1,\"name\":\"Nanda Ariwahyu\",\"email\":\"nandaariwahyu07@gmail.com\",\"email_verified_at\":null,\"phone\":null,\"role\":\"super_admin\",\"department\":\"Executive Office\",\"team_id\":null,\"join_date\":\"2022-01-10T00:00:00.000000Z\",\"status\":\"active\",\"avatar_url\":null,\"profile_bio\":\"Founder & Chief Executive Officer at Solvia Group.\",\"permissions\":null,\"created_at\":\"2026-09-12T04:43:53.000000Z\",\"updated_at\":\"2026-09-14T04:34:23.000000Z\"}', '{\"id\":1,\"name\":\"Nanda Ariwahyu\",\"email\":\"nandaariwahyu07@gmail.com\",\"email_verified_at\":null,\"phone\":null,\"role\":\"super_admin\",\"department\":\"Executive Office\",\"team_id\":null,\"join_date\":\"2022-01-10T00:00:00.000000Z\",\"status\":\"active\",\"avatar_url\":null,\"profile_bio\":\"Founder & Chief Executive Officer at Solvia Group.\",\"permissions\":null,\"created_at\":\"2026-09-12T04:43:53.000000Z\",\"updated_at\":\"2026-09-14T04:34:23.000000Z\"}', '127.0.0.1', 'Symfony', 'User Nanda Ariwahyu (super_admin) updated their profile without changing role', '2026-09-13 21:34:46', '2026-09-13 21:34:46'),
(122, 1, 'create', 'Task', 12, NULL, '{\"title\":\"QA Audit Task 1253\",\"description\":\"Automated QA flow verification test\",\"milestone_id\":null,\"assignee_id\":11,\"priority\":\"urgent\",\"status\":\"to_do\",\"progress\":0,\"start_date\":null,\"deadline\":null,\"estimated_hours\":\"5.00\",\"project_id\":1,\"updated_at\":\"2026-09-14T04:35:06.000000Z\",\"created_at\":\"2026-09-14T04:35:06.000000Z\",\"id\":12,\"milestone\":null}', '127.0.0.1', 'Symfony', 'New task created: QA Audit Task 1253', '2026-09-13 21:35:06', '2026-09-13 21:35:06'),
(123, 11, 'claim', 'Task', 12, '{\"assignee_id\":11}', '{\"assignee_id\":11,\"status\":\"in_progress\"}', '127.0.0.1', 'Symfony', 'User Putra Pratama (Senior Designer) claimed task #12 (QA Audit Task 1253)', '2026-09-13 21:35:06', '2026-09-13 21:35:06'),
(124, 11, 'create', 'DailyProgress', 10, NULL, '{\"project_id\":1,\"task_id\":12,\"date\":\"2026-09-14T00:00:00.000000Z\",\"progress\":60,\"completed_work\":\"Completed initial QA audit and test suite execution\",\"next_plan\":\"Finalize system test reports\",\"blocker\":null,\"working_hours\":\"4.50\",\"user_id\":11,\"updated_at\":\"2026-09-14T04:35:06.000000Z\",\"created_at\":\"2026-09-14T04:35:06.000000Z\",\"id\":10}', '127.0.0.1', 'Symfony', 'Daily progress submitted by Putra Pratama (Senior Designer)', '2026-09-13 21:35:06', '2026-09-13 21:35:06'),
(125, 11, 'create', 'Blocker', 10, NULL, '{\"project_id\":1,\"task_id\":12,\"title\":\"QA Mock Blocker 514\",\"description\":\"Testing blocker reporting flow and task status\",\"priority\":\"high\",\"type\":\"technical\",\"reporter_id\":11,\"status\":\"open\",\"updated_at\":\"2026-09-14T04:35:06.000000Z\",\"created_at\":\"2026-09-14T04:35:06.000000Z\",\"id\":10}', '127.0.0.1', 'Symfony', 'New blocker reported on project #1: QA Mock Blocker 514', '2026-09-13 21:35:06', '2026-09-13 21:35:06'),
(126, 11, 'create', 'Reimbursement', 7, NULL, '{\"title\":\"QA Operational Reimbursement Claim\",\"amount\":\"75000.00\",\"date\":\"2026-09-14T00:00:00.000000Z\",\"project_id\":1,\"category\":\"materials\",\"notes\":null,\"user_id\":11,\"status\":\"submitted\",\"updated_at\":\"2026-09-14T04:35:06.000000Z\",\"created_at\":\"2026-09-14T04:35:06.000000Z\",\"id\":7}', '127.0.0.1', 'Symfony', 'Reimbursement submitted by Putra Pratama (Senior Designer)', '2026-09-13 21:35:06', '2026-09-13 21:35:06'),
(127, 11, 'logout', 'User', 11, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', 'User logged out', '2026-09-13 21:52:59', '2026-09-13 21:52:59'),
(128, 1, 'login', 'User', 1, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', 'User logged into Solvia.Nova OS', '2026-09-13 21:54:33', '2026-09-13 21:54:33'),
(129, 1, 'login', 'User', 1, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36', 'User logged into Solvia.Nova OS', '2026-09-13 22:15:44', '2026-09-13 22:15:44');

-- --------------------------------------------------------

--
-- Table structure for table `automation_logs`
--

CREATE TABLE `automation_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `automation_rule_id` bigint(20) UNSIGNED NOT NULL,
  `trigger_event` varchar(255) NOT NULL,
  `context_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`context_data`)),
  `status` varchar(255) NOT NULL DEFAULT 'success',
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `automation_logs`
--

INSERT INTO `automation_logs` (`id`, `automation_rule_id`, `trigger_event`, `context_data`, `status`, `message`, `created_at`, `updated_at`) VALUES
(1, 1, 'domain_expiring', NULL, 'success', 'Generated 0 alerts for domains expiring in <= 30 days.', '2026-09-13 19:41:31', '2026-09-13 19:41:31'),
(2, 2, 'task_overdue', NULL, 'success', 'Notified assignees of 0 overdue tasks.', '2026-09-13 19:41:31', '2026-09-13 19:41:31'),
(3, 3, 'progress_missing', NULL, 'success', 'Trigger progress_missing evaluated without pending triggers.', '2026-09-13 19:41:31', '2026-09-13 19:41:31'),
(4, 4, 'inventory_low', NULL, 'success', 'Processed 0 low-stock inventory items, sent 0 notifications.', '2026-09-13 19:41:31', '2026-09-13 19:41:31'),
(5, 5, 'purchase_approved', NULL, 'success', 'Trigger purchase_approved evaluated without pending triggers.', '2026-09-13 19:41:31', '2026-09-13 19:41:31'),
(6, 6, 'domain_expiring', NULL, 'success', 'Generated 0 alerts for domains expiring in <= 30 days.', '2026-09-13 19:41:31', '2026-09-13 19:41:31');

-- --------------------------------------------------------

--
-- Table structure for table `automation_rules`
--

CREATE TABLE `automation_rules` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `trigger_event` varchar(255) NOT NULL,
  `condition_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`condition_config`)),
  `action_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`action_config`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_triggered_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `automation_rules`
--

INSERT INTO `automation_rules` (`id`, `name`, `trigger_event`, `condition_config`, `action_config`, `is_active`, `last_triggered_at`, `created_at`, `updated_at`) VALUES
(1, 'Domain Expiry Alert (30 days)', 'domain_expiring', '\"{\\\"days_before\\\":30}\"', '\"{\\\"type\\\":\\\"notify\\\",\\\"target\\\":\\\"super_admin\\\",\\\"level\\\":\\\"warning\\\",\\\"message\\\":\\\"Domain {{name}} expires in {{days}} days. Renew before {{expiry_date}}.\\\"}\"', 1, '2026-09-13 19:41:31', '2026-09-11 21:43:56', '2026-09-13 19:41:31'),
(2, 'Task Overdue Notification', 'task_overdue', '\"{\\\"hours_overdue\\\":24}\"', '\"{\\\"type\\\":\\\"notify\\\",\\\"target\\\":[\\\"assignee\\\",\\\"super_admin\\\"],\\\"level\\\":\\\"danger\\\",\\\"message\\\":\\\"Task \\\\\\\"{{task_title}}\\\\\\\" in {{project_name}} is overdue by {{days}} day(s).\\\"}\"', 1, '2026-09-13 19:41:31', '2026-09-11 21:43:56', '2026-09-13 19:41:31'),
(3, 'Daily Progress Missing Alert', 'progress_missing', '\"{\\\"check_time\\\":\\\"18:00\\\",\\\"grace_minutes\\\":30}\"', '\"{\\\"type\\\":\\\"notify\\\",\\\"target\\\":[\\\"user\\\",\\\"super_admin\\\"],\\\"level\\\":\\\"warning\\\",\\\"message\\\":\\\"{{user_name}} has not submitted daily progress for today.\\\"}\"', 1, '2026-09-13 19:41:31', '2026-09-11 21:43:56', '2026-09-13 19:41:31'),
(4, 'Low Inventory Stock Alert', 'inventory_low', '\"{\\\"trigger_at\\\":\\\"minimum_stock\\\"}\"', '\"{\\\"type\\\":\\\"notify\\\",\\\"target\\\":\\\"super_admin\\\",\\\"level\\\":\\\"warning\\\",\\\"message\\\":\\\"Inventory item \\\\\\\"{{item_name}}\\\\\\\" (SKU: {{sku}}) is below minimum stock. Current: {{current_stock}}, Minimum: {{minimum_stock}}.\\\"}\"', 1, '2026-09-13 19:41:31', '2026-09-11 21:43:56', '2026-09-13 19:41:31'),
(5, 'Purchase Request Approved Notification', 'purchase_approved', NULL, '\"{\\\"type\\\":\\\"notify\\\",\\\"target\\\":\\\"requester\\\",\\\"level\\\":\\\"success\\\",\\\"message\\\":\\\"Your purchase request \\\\\\\"{{item_name}}\\\\\\\" has been approved by Super Admin.\\\"}\"', 1, '2026-09-13 19:41:31', '2026-09-11 21:43:56', '2026-09-13 19:41:31'),
(6, 'Subscription Billing Due (7 days)', 'domain_expiring', '\"{\\\"days_before\\\":7,\\\"resource_type\\\":\\\"subscription\\\"}\"', '\"{\\\"type\\\":\\\"notify\\\",\\\"target\\\":\\\"super_admin\\\",\\\"level\\\":\\\"info\\\",\\\"message\\\":\\\"Subscription {{provider}} ({{plan}}) billing due in {{days}} days. Amount: Rp {{cost}}.\\\"}\"', 1, '2026-09-13 19:41:31', '2026-09-11 21:43:56', '2026-09-13 19:41:31');

-- --------------------------------------------------------

--
-- Table structure for table `blockers`
--

CREATE TABLE `blockers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `task_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `priority` varchar(255) NOT NULL DEFAULT 'high',
  `type` varchar(255) NOT NULL DEFAULT 'technical',
  `reporter_id` bigint(20) UNSIGNED NOT NULL,
  `responsible_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'open',
  `resolved_date` timestamp NULL DEFAULT NULL,
  `resolution_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `blockers`
--

INSERT INTO `blockers` (`id`, `project_id`, `task_id`, `title`, `description`, `priority`, `type`, `reporter_id`, `responsible_user_id`, `status`, `resolved_date`, `resolution_notes`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'Blocker: dfsfs', 'dfsfs', 'high', 'technical', 1, NULL, 'open', NULL, NULL, '2026-09-13 19:44:41', '2026-09-13 19:44:41');

-- --------------------------------------------------------

--
-- Table structure for table `budgets`
--

CREATE TABLE `budgets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `scope_type` varchar(255) NOT NULL DEFAULT 'company',
  `project_id` bigint(20) UNSIGNED DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `month` smallint(5) UNSIGNED DEFAULT NULL,
  `year` smallint(5) UNSIGNED NOT NULL,
  `budgeted_amount` decimal(15,2) NOT NULL,
  `actual_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `budgets`
--

INSERT INTO `budgets` (`id`, `scope_type`, `project_id`, `category`, `month`, `year`, `budgeted_amount`, `actual_amount`, `created_at`, `updated_at`) VALUES
(1, 'project', 6, 'salary', 1, 2026, 20000000.00, 0.00, '2026-09-13 21:02:02', '2026-09-13 21:02:02');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`id`, `client_code`, `name`, `contact_person`, `email`, `phone`, `address`, `website`, `notes`, `status`, `created_at`, `updated_at`) VALUES
(2, 'CLT-AUDIT-1', 'Audit Client', NULL, NULL, NULL, NULL, NULL, NULL, 'active', '2026-09-13 19:43:33', '2026-09-13 19:43:33'),
(5, 'CLT-2026-214', 'Hydrone', 'Zaidan Zikron', 'zaidan@gmail.com', '546456546546', 'sd', 'hydrone.id', 'sdf', 'active', '2026-09-13 20:32:09', '2026-09-13 20:32:09');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `commentable_type` varchar(255) NOT NULL,
  `commentable_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `content` text NOT NULL,
  `mentions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`mentions`)),
  `attachment_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `company_accounts`
--

CREATE TABLE `company_accounts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `resource_id` bigint(20) UNSIGNED NOT NULL,
  `platform` varchar(255) NOT NULL,
  `account_identifier` varchar(255) NOT NULL,
  `encrypted_credentials` text NOT NULL,
  `two_factor_status` tinyint(1) NOT NULL DEFAULT 0,
  `recovery_method` text DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `company_profiles`
--

CREATE TABLE `company_profiles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT 'Solvia.Nova',
  `legal_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `tax_number` varchar(255) DEFAULT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'IDR',
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `company_profiles`
--

INSERT INTO `company_profiles` (`id`, `name`, `legal_name`, `email`, `phone`, `address`, `website`, `tax_number`, `currency`, `settings`, `created_at`, `updated_at`) VALUES
(1, 'Solvia.Nova', 'PT Solvia Nova Teknologi', 'contact@solvia.id', '+62 21 8899 0011', 'Jakarta, Indonesia', 'https://solvia.id', NULL, 'IDR', NULL, '2026-09-13 19:27:35', '2026-09-13 19:27:35');

-- --------------------------------------------------------

--
-- Table structure for table `contracts`
--

CREATE TABLE `contracts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `resource_id` bigint(20) UNSIGNED NOT NULL,
  `contract_number` varchar(255) NOT NULL,
  `party_name` varchar(255) NOT NULL,
  `party_type` varchar(255) NOT NULL DEFAULT 'client',
  `start_date` date NOT NULL,
  `expiry_date` date NOT NULL,
  `contract_value` decimal(15,2) NOT NULL DEFAULT 0.00,
  `document_path` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `daily_progress`
--

CREATE TABLE `daily_progress` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `task_id` bigint(20) UNSIGNED DEFAULT NULL,
  `date` date NOT NULL,
  `progress` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `completed_work` text NOT NULL,
  `next_plan` text NOT NULL,
  `blocker` text DEFAULT NULL,
  `working_hours` decimal(5,2) NOT NULL DEFAULT 8.00,
  `attachment_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `daily_progress`
--

INSERT INTO `daily_progress` (`id`, `user_id`, `project_id`, `task_id`, `date`, `progress`, `completed_work`, `next_plan`, `blocker`, `working_hours`, `attachment_path`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, '2026-09-18', 0, '30', '30', 'dfsfs', 8.00, NULL, '2026-09-13 19:44:41', '2026-09-13 19:44:41'),
(5, 11, 1, 7, '2026-09-14', 60, 'Completed initial QA audit and test suite execution', 'Finalize system test reports', NULL, 4.50, NULL, '2026-09-13 20:52:01', '2026-09-13 20:52:01'),
(6, 11, 1, 8, '2026-09-14', 60, 'Completed initial QA audit and test suite execution', 'Finalize system test reports', NULL, 4.50, NULL, '2026-09-13 20:52:40', '2026-09-13 20:52:40');

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL DEFAULT 'general',
  `file_path` varchar(255) NOT NULL,
  `file_size` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `file_type` varchar(255) DEFAULT NULL,
  `uploader_id` bigint(20) UNSIGNED NOT NULL,
  `documentable_type` varchar(255) DEFAULT NULL,
  `documentable_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_restricted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `expense_number` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `category` varchar(255) NOT NULL,
  `project_id` bigint(20) UNSIGNED DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `account_id` bigint(20) UNSIGNED NOT NULL,
  `vendor` varchar(255) DEFAULT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`id`, `expense_number`, `date`, `category`, `project_id`, `amount`, `account_id`, `vendor`, `attachment_path`, `notes`, `created_at`, `updated_at`) VALUES
(3, 'EXP-2026-6762', '2026-09-14', 'marketing', 1, 5000000.00, 1, 'nbv', NULL, 'vbn', '2026-09-13 20:59:13', '2026-09-13 20:59:13'),
(4, 'EXP-PAY-6AA772DD316B6', '2026-09-14', 'salary', NULL, 600000.00, 1, 'Putra', NULL, 'Salary payment for Putra period 2026-09', '2026-09-13 21:06:53', '2026-09-13 21:06:53'),
(6, 'EXP-PAYABLE-6AA776E93CAF2', '2026-09-14', 'Infrastructure', NULL, 750000.00, 1, 'PT Cloud Hostindo Verification', NULL, 'Settlement test', '2026-09-13 21:24:09', '2026-09-13 21:24:09');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
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
-- Table structure for table `financial_accounts`
--

CREATE TABLE `financial_accounts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `account_code` varchar(255) NOT NULL,
  `account_name` varchar(255) NOT NULL,
  `account_type` varchar(255) NOT NULL DEFAULT 'BANK',
  `provider` varchar(255) DEFAULT NULL,
  `owner` varchar(255) DEFAULT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'bank',
  `account_number` varchar(255) DEFAULT NULL,
  `bank_name` varchar(255) DEFAULT NULL,
  `balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `opening_balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(10) NOT NULL DEFAULT 'IDR',
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `financial_accounts`
--

INSERT INTO `financial_accounts` (`id`, `account_code`, `account_name`, `account_type`, `provider`, `owner`, `type`, `account_number`, `bank_name`, `balance`, `opening_balance`, `currency`, `description`, `is_active`, `status`, `created_at`, `updated_at`) VALUES
(1, 'ACC-001', 'Kas Operasional Utama', 'BANK', 'BCA', 'Solvia Nova', 'bank', '123-456-7890', 'BCA', 55500000.00, 9250000.00, 'IDR', NULL, 1, 'active', '2026-09-13 19:27:35', '2026-09-13 21:24:09');

-- --------------------------------------------------------

--
-- Table structure for table `financial_transactions`
--

CREATE TABLE `financial_transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `transaction_code` varchar(255) NOT NULL,
  `transaction_type` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL DEFAULT 'general',
  `amount` decimal(15,2) NOT NULL,
  `transaction_date` date NOT NULL,
  `financial_account_id` bigint(20) UNSIGNED DEFAULT NULL,
  `to_account_id` bigint(20) UNSIGNED DEFAULT NULL,
  `project_id` bigint(20) UNSIGNED DEFAULT NULL,
  `invoice_id` bigint(20) UNSIGNED DEFAULT NULL,
  `employee_id` bigint(20) UNSIGNED DEFAULT NULL,
  `asset_id` bigint(20) UNSIGNED DEFAULT NULL,
  `resource_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reference_type` varchar(255) DEFAULT NULL,
  `reference_id` bigint(20) UNSIGNED DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'posted',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `financial_transactions`
--

INSERT INTO `financial_transactions` (`id`, `transaction_code`, `transaction_type`, `category`, `amount`, `transaction_date`, `financial_account_id`, `to_account_id`, `project_id`, `invoice_id`, `employee_id`, `asset_id`, `resource_id`, `reference_type`, `reference_id`, `description`, `status`, `created_by`, `approved_by`, `approved_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'TRX-OPN-1', 'OPENING_BALANCE', 'Initial Capital / Opening Balance', 9250000.00, '2026-01-01', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Initial opening balance for Kas Operasional Utama', 'posted', NULL, NULL, NULL, '2026-09-13 21:13:23', '2026-09-13 21:13:23', NULL),
(2, 'TRX-INC-3', 'INCOME', 'Client Invoice Payment', 25000000.00, '2026-09-14', 1, NULL, 6, NULL, NULL, NULL, NULL, 'income', 3, 're', 'posted', NULL, NULL, NULL, '2026-09-13 21:13:23', '2026-09-13 21:13:23', NULL),
(3, 'TRX-INC-4', 'INCOME', 'Project Milestone', 500000.00, '2026-09-14', 1, NULL, 1, NULL, NULL, NULL, NULL, 'income', 4, 'fbgdfb', 'posted', NULL, NULL, NULL, '2026-09-13 21:13:23', '2026-09-13 21:13:23', NULL),
(4, 'TRX-INC-5', 'INCOME', 'Client Invoice Payment', 25000000.00, '2026-09-14', 1, NULL, 1, NULL, NULL, NULL, NULL, 'income', 5, 'Invoice Payment #INV-2026-957', 'posted', NULL, NULL, NULL, '2026-09-13 21:13:23', '2026-09-13 21:13:23', NULL),
(5, 'TRX-EXP-3', 'EXPENSE', 'marketing', 5000000.00, '2026-09-14', 1, NULL, 1, NULL, NULL, NULL, NULL, 'expense', 3, 'vbn', 'posted', NULL, NULL, NULL, '2026-09-13 21:13:23', '2026-09-13 21:13:23', NULL),
(6, 'TRX-EXP-4', 'EXPENSE', 'salary', 600000.00, '2026-09-14', 1, NULL, NULL, NULL, NULL, NULL, NULL, 'expense', 4, 'Salary payment for Putra period 2026-09', 'posted', NULL, NULL, NULL, '2026-09-13 21:13:23', '2026-09-13 21:13:23', NULL),
(7, 'TRX-202609-FYPCFI', 'CAPITAL_IN', 'Owner Equity Injection', 2000000.00, '2026-09-14', 1, NULL, NULL, NULL, NULL, NULL, NULL, 'capital', NULL, 'Owner Capital Test Injection', 'posted', 1, NULL, NULL, '2026-09-13 21:24:09', '2026-09-13 21:24:09', NULL),
(8, 'TRX-202609-78QP30', 'INCOME', 'Project Revenue', 100000.00, '2026-09-14', 1, NULL, 1, 2, NULL, NULL, NULL, 'invoice_payment', 2, 'Client payment for Invoice #INV-2026-957 (Audit Client)', 'posted', 1, NULL, NULL, '2026-09-13 21:24:09', '2026-09-13 21:24:09', NULL),
(9, 'TRX-202609-XN8HDX', 'EXPENSE', 'Infrastructure', 750000.00, '2026-09-14', 1, NULL, NULL, NULL, NULL, NULL, NULL, 'payable', 1, 'Payment of Payable PAY-2026-EXM2X to PT Cloud Hostindo Verification', 'posted', 1, NULL, NULL, '2026-09-13 21:24:09', '2026-09-13 21:24:09', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `incomes`
--

CREATE TABLE `incomes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `income_number` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `source` varchar(255) NOT NULL,
  `client_id` bigint(20) UNSIGNED DEFAULT NULL,
  `project_id` bigint(20) UNSIGNED DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `category` varchar(255) NOT NULL DEFAULT 'Client Project Payment',
  `account_id` bigint(20) UNSIGNED NOT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `incomes`
--

INSERT INTO `incomes` (`id`, `income_number`, `date`, `source`, `client_id`, `project_id`, `amount`, `category`, `account_id`, `attachment_path`, `notes`, `created_at`, `updated_at`) VALUES
(3, 'INC-2026-6784', '2026-09-14', 're', 5, 6, 25000000.00, 'Client Invoice Payment', 1, NULL, 'dbf db', '2026-09-13 20:58:22', '2026-09-13 20:58:22'),
(4, 'INC-2026-7609', '2026-09-14', 'fbgdfb', 2, 1, 500000.00, 'Project Milestone', 1, NULL, 'sdvsdv', '2026-09-13 20:58:43', '2026-09-13 20:58:43'),
(5, 'INC-6AA77127A1F7A', '2026-09-14', 'Invoice Payment #INV-2026-957', 2, 1, 25000000.00, 'Client Invoice Payment', 1, NULL, 'Automatic payment confirmation for invoice INV-2026-957', '2026-09-13 20:59:35', '2026-09-13 20:59:35');

-- --------------------------------------------------------

--
-- Table structure for table `infrastructures`
--

CREATE TABLE `infrastructures` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `resource_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(255) NOT NULL,
  `provider` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `hostname` varchar(255) DEFAULT NULL,
  `ip_address` varchar(255) DEFAULT NULL,
  `os` varchar(255) DEFAULT NULL,
  `specs` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `environment` varchar(255) NOT NULL DEFAULT 'production',
  `purpose` text DEFAULT NULL,
  `encrypted_credentials` text DEFAULT NULL,
  `billing_account` varchar(255) DEFAULT NULL,
  `monthly_cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `yearly_cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `start_date` date DEFAULT NULL,
  `next_billing_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `auto_renewal` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `infrastructures`
--

INSERT INTO `infrastructures` (`id`, `resource_id`, `type`, `provider`, `name`, `hostname`, `ip_address`, `os`, `specs`, `location`, `environment`, `purpose`, `encrypted_credentials`, `billing_account`, `monthly_cost`, `yearly_cost`, `start_date`, `next_billing_date`, `expiry_date`, `auto_renewal`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'vps', 'Tencent', 'solvianova', 'solvianova.id', '234234', NULL, NULL, NULL, 'development', NULL, NULL, NULL, 150000.00, 0.00, NULL, '2026-09-12', '2027-09-12', 0, 'active', '2026-09-13 19:32:23', '2026-09-13 19:32:23');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_items`
--

CREATE TABLE `inventory_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sku` varchar(255) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL DEFAULT 'Hardware',
  `unit` varchar(255) NOT NULL DEFAULT 'pcs',
  `current_stock` int(11) NOT NULL DEFAULT 0,
  `minimum_stock` int(11) NOT NULL DEFAULT 5,
  `location` varchar(255) DEFAULT NULL,
  `supplier` varchar(255) DEFAULT NULL,
  `unit_cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` varchar(255) NOT NULL DEFAULT 'in_stock',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_transactions`
--

CREATE TABLE `inventory_transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `inventory_item_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `transaction_type` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `balance_after` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `invoice_number` varchar(255) NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED DEFAULT NULL,
  `issue_date` date NOT NULL,
  `due_date` date NOT NULL,
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `payment_status` varchar(255) NOT NULL DEFAULT 'draft',
  `attachment_path` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `invoice_number`, `client_id`, `project_id`, `issue_date`, `due_date`, `subtotal`, `discount`, `tax`, `total`, `payment_status`, `attachment_path`, `notes`, `paid_at`, `created_at`, `updated_at`) VALUES
(2, 'INV-2026-957', 2, 1, '2026-09-14', '2026-09-28', 25000000.00, 0.00, 0.00, 25000000.00, 'paid', NULL, NULL, '2026-09-13 21:24:09', '2026-09-13 20:59:33', '2026-09-13 21:24:09');

-- --------------------------------------------------------

--
-- Table structure for table `invoice_items`
--

CREATE TABLE `invoice_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `invoice_id` bigint(20) UNSIGNED NOT NULL,
  `description` varchar(255) NOT NULL,
  `quantity` decimal(8,2) NOT NULL DEFAULT 1.00,
  `unit_price` decimal(15,2) NOT NULL,
  `total_price` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoice_items`
--

INSERT INTO `invoice_items` (`id`, `invoice_id`, `description`, `quantity`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES
(2, 2, 'Development Services', 1.00, 25000000.00, 25000000.00, '2026-09-13 20:59:33', '2026-09-13 20:59:33');

-- --------------------------------------------------------

--
-- Table structure for table `invoice_payments`
--

CREATE TABLE `invoice_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `payment_number` varchar(255) NOT NULL,
  `invoice_id` bigint(20) UNSIGNED NOT NULL,
  `financial_account_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_method` varchar(255) NOT NULL DEFAULT 'bank_transfer',
  `reference_number` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoice_payments`
--

INSERT INTO `invoice_payments` (`id`, `payment_number`, `invoice_id`, `financial_account_id`, `amount`, `payment_date`, `payment_method`, `reference_number`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'PAY-INV-2', 2, 1, 25000000.00, '2026-09-14', 'bank_transfer', NULL, 'Historical invoice payment sync', NULL, '2026-09-13 21:13:23', '2026-09-13 21:13:23'),
(2, 'PAY-INV-6AA776E92B2CD', 2, 1, 100000.00, '2026-09-14', 'bank_transfer', 'REF-TEST-99', 'Partial payment test', 1, '2026-09-13 21:24:09', '2026-09-13 21:24:09');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
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
-- Table structure for table `job_batches`
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
-- Table structure for table `knowledge_bases`
--

CREATE TABLE `knowledge_bases` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL DEFAULT 'sop',
  `content` longtext NOT NULL,
  `author_id` bigint(20) UNSIGNED NOT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `knowledge_bases`
--

INSERT INTO `knowledge_bases` (`id`, `title`, `slug`, `category`, `content`, `author_id`, `is_published`, `created_at`, `updated_at`) VALUES
(1, 'SOP: Daily Progress Submission', 'sop-daily-progress-submission-RP22', 'sop', '## SOP Daily Progress Submission\n\n### Tujuan\nMemastikan setiap anggota tim melaporkan progres pekerjaan harian secara konsisten dan tepat waktu.\n\n### Waktu Submission\n- **Batas waktu**: Setiap hari kerja pukul **18:00 WIB**\n- **Tidak ada submission di hari libur nasional**\n\n### Format Wajib\n1. **Task yang dikerjakan** — pilih task yang relevan dari sistem\n2. **Progress (%)** — update persentase penyelesaian\n3. **Pekerjaan yang diselesaikan hari ini** — deskripsi konkret\n4. **Rencana esok hari** — apa yang akan dikerjakan\n5. **Blocker** — jika ada hambatan, deskripsikan dengan detail\n6. **Jam kerja** — default 8 jam, sesuaikan jika berbeda\n\n### Sanksi\nProgress yang tidak disubmit sebelum 18:00 akan menghasilkan notifikasi otomatis ke Super Admin. Tiga kali miss berturut-turut akan menjadi catatan evaluasi performa.\n\n### Tips\n- Submit progress secara real-time, jangan menunggu akhir hari\n- Jika ada blocker, laporkan segera — jangan tunggu submission harian\n- Progress harus akurat dan dapat diverifikasi', 1, 1, '2026-09-11 21:43:56', '2026-09-11 21:43:56'),
(2, 'Development Guide: Laravel Project Setup', 'development-guide-laravel-project-setup-Wuvm', 'development_guide', '## Laravel Project Setup Guide\n\n### Prerequisites\n- PHP 8.2+\n- Composer 2.x\n- MySQL 8.0+ atau PostgreSQL 15+\n- Node.js 20+ dan npm\n\n### Setup Steps\n\n```bash\n# Clone repository\ngit clone https://github.com/solvia-nova/[project-name].git\ncd [project-name]\n\n# Install dependencies\ncomposer install\nnpm install\n\n# Environment setup\ncp .env.example .env\nphp artisan key:generate\n\n# Database\nphp artisan migrate\nphp artisan db:seed\n\n# Build assets\nnpm run build\n\n# Start development server\nphp artisan serve\n```\n\n### Branch Convention\n- `main` — production-ready code only\n- `develop` — integration branch\n- `feature/[ticket-id]-[short-desc]` — feature branches\n- `fix/[ticket-id]-[short-desc]` — bug fixes\n\n### Commit Message Format\n```\nfeat: add user authentication module\nfix: resolve invoice calculation bug\ndocs: update API documentation\nrefactor: extract payment service layer\n```\n\n### Code Standards\n- PSR-12 coding style\n- Run `php artisan pint` before committing\n- All business logic goes in Service layer\n- Controllers should be thin — max 30 lines per method', 2, 1, '2026-09-11 21:43:56', '2026-09-11 21:43:56'),
(3, 'Brand Guideline: Solvia.Nova Visual Identity', 'brand-guideline-solvianova-visual-identity-ylx5', 'brand_guideline', '## Solvia.Nova Brand Guidelines\n\n### Brand Personality\nSolvia.Nova adalah perusahaan teknologi yang **modern, profesional, dan dapat dipercaya**. Tone komunikasi: langsung, technical-friendly, tanpa jargon berlebihan.\n\n### Color Palette\n| Color | Hex | Usage |\n|-------|-----|-------|\n| Indigo Primary | `#6366f1` | Primary actions, links, highlights |\n| Slate Dark | `#0f172a` | Backgrounds (dark mode) |\n| Slate Medium | `#1e293b` | Cards, panels |\n| White | `#f8fafc` | Primary text |\n| Emerald | `#10b981` | Success, income, positive |\n| Rose | `#f43f5e` | Errors, danger, expenses |\n| Amber | `#f59e0b` | Warnings, at-risk |\n\n### Typography\n- **Headings**: Plus Jakarta Sans, weight 700–800\n- **Body**: Plus Jakarta Sans, weight 400–500\n- **Code/Mono**: JetBrains Mono, weight 400–600\n\n### Logo Usage\n- Minimum size: 24px height for digital, 10mm for print\n- Never distort proportions\n- Never use on busy backgrounds without contrast\n- Approved variations: Full logo, Monogram (SN)\n\n### Tone of Voice\n- **Do**: \'Your project is at risk\' / \'Invoice overdue\'\n- **Don\'t**: \'Uh oh! Something went wrong!\' / \'Oops!\'\n- Keep error messages actionable: tell the user what to do next', 5, 1, '2026-09-11 21:43:56', '2026-09-11 21:43:56'),
(4, 'SOP: Asset Management Lifecycle', 'sop-asset-management-lifecycle-zetb', 'sop', '## Asset Management Lifecycle SOP\n\n### Lifecycle Stages\n```\nPurchased → Available → Assigned → In Use → Maintenance → Returned → Retired → Disposed\n```\n\n### Receiving New Assets\n1. Catat di Solvia.Nova OS: Resources → Assets → Register Asset\n2. Input: Asset Tag (format: [TYPE]-[NUM], e.g. LAPTOP-005)\n3. Foto kondisi awal dan simpan sebagai dokumen\n4. Jika langsung assign: pilih user penerima\n\n### Assignment\n- Semua assignment harus tercatat di sistem\n- User yang menerima asset bertanggung jawab atas kondisinya\n- Assignment harus ada catatan kondisi saat diterima\n\n### Maintenance\n- Maintenance terjadwal: input di sistem minimal 1 minggu sebelumnya\n- Biaya maintenance otomatis tercatat sebagai expense kategori \'equipment\'\n- Setelah maintenance selesai, update status dan accumulated cost\n\n### Return Procedure\n1. User melaporkan ke Super Admin\n2. Super Admin memverifikasi kondisi asset\n3. Update di sistem: Resources → Assets → Return\n4. Input kondisi saat dikembalikan\n5. Asset kembali ke status \'Available\'\n\n### Disposal\n- Hanya Super Admin yang dapat mengubah status ke \'Retired\' atau \'Disposed\'\n- Diperlukan dokumentasi alasan disposal\n- Asset yang di-dispose tidak dihapus dari sistem (audit trail)', 1, 1, '2026-09-11 21:43:56', '2026-09-11 21:43:56'),
(5, 'FAQ: Solvia.Nova OS — Pertanyaan Umum', 'faq-solvianova-os-pertanyaan-umum-T4uZ', 'faq', '## FAQ Solvia.Nova OS\n\n### Q: Bagaimana cara submit daily progress?\n**A:** Dashboard → My Tasks → Update Progress, atau gunakan menu Daily Progress di sidebar. Pastikan submit sebelum 18:00 WIB.\n\n### Q: Saya tidak bisa melihat data finance. Kenapa?\n**A:** Akses Finance dibatasi untuk Super Admin. Jika Anda memerlukan akses, hubungi Rian Pratama untuk pemberian permission khusus.\n\n### Q: Bagaimana cara melaporkan blocker?\n**A:** Ada dua cara:\n1. Saat submit daily progress — isi field \'Blocker\'\n2. Menu Blockers → Report New Blocker (untuk blocker yang perlu tracking khusus)\n\n### Q: Credential akun perusahaan di mana?\n**A:** Resources → Company Accounts. Akses hanya untuk Super Admin dan diaudit setiap akses. Hubungi Super Admin jika membutuhkan credential tertentu.\n\n### Q: Bagaimana cara request pembelian barang?\n**A:** Menu Purchasing → New Request. Isi detail item, estimasi harga, dan alasan. Request akan direview oleh Super Admin.\n\n### Q: Bagaimana cara submit reimbursement?\n**A:** Finance → Reimbursements → Submit Claim. Isi detail, lampirkan bukti jika ada. Akan di-review dan disetujui oleh Super Admin.\n\n### Q: Apakah data salary saya bisa dilihat oleh rekan kerja?\n**A:** Tidak. Data payroll hanya dapat diakses oleh Super Admin. Role lain tidak memiliki akses ke halaman payroll.', 1, 1, '2026-09-11 21:43:56', '2026-09-11 21:43:56');

-- --------------------------------------------------------

--
-- Table structure for table `licenses`
--

CREATE TABLE `licenses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `resource_id` bigint(20) UNSIGNED NOT NULL,
  `software_name` varchar(255) NOT NULL,
  `license_key_encrypted` text NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `device_name` varchar(255) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_09_12_000001_create_company_teams_and_clients_tables', 1),
(5, '2026_09_12_000002_create_projects_milestones_tasks_tables', 1),
(6, '2026_09_12_000003_create_finance_and_payroll_tables', 1),
(7, '2026_09_12_000004_create_resources_assets_and_infrastructure_tables', 1),
(8, '2026_09_12_000005_create_inventory_and_purchasing_tables', 1),
(9, '2026_09_12_000006_create_schedule_communication_and_system_tables', 1),
(10, '2026_09_14_041105_create_financial_control_system_tables', 2);

-- --------------------------------------------------------

--
-- Table structure for table `milestones`
--

CREATE TABLE `milestones` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `start_date` date NOT NULL,
  `deadline` date NOT NULL,
  `progress` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `action_url` varchar(255) DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `level` varchar(255) NOT NULL DEFAULT 'info',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `action_url`, `icon`, `level`, `is_read`, `read_at`, `created_at`, `updated_at`) VALUES
(1, 11, 'task', 'Tugas Baru Ditugaskan: Design Website', 'Super Admin menugaskan tugas \'Design Website\' pada proyek \'Hydrone\' kepada Anda.', 'http://localhost:8000/projects/6?tab=tasks', NULL, 'info', 1, '2026-09-13 21:28:56', '2026-09-13 20:47:59', '2026-09-13 21:28:56'),
(2, 11, 'asset', 'Penugasan Perangkat: AST-PX4UGU', 'Perangkat Asus Asus vivobook (AST-PX4UGU) telah ditugaskan kepada Anda.', 'http://localhost:8000/resources/assets', NULL, 'info', 1, '2026-09-13 21:28:56', '2026-09-13 20:47:59', '2026-09-13 21:28:56'),
(5, 11, 'asset', 'Penugasan Perangkat: AST-PX4UGU', 'Nanda Ariwahyu menugaskan perangkat Asus Asus vivobook (AST-PX4UGU) kepada Anda.', 'http://localhost:8000/resources/assets', NULL, 'info', 1, '2026-09-13 21:28:56', '2026-09-13 20:48:41', '2026-09-13 21:28:56'),
(12, 11, 'payroll', 'Gaji Telah Dicairkan', 'Gaji periode 2026-09 sebesar Rp 600.000 telah dicairkan.', 'http://localhost:8000/dashboard', 'banknotes', 'success', 1, '2026-09-13 21:28:56', '2026-09-13 21:06:53', '2026-09-13 21:28:56'),
(13, 1, 'payroll', 'Gaji Telah Dicairkan', 'Gaji periode 2026-09 sebesar Rp 1.750.000 telah dicairkan.', 'http://localhost:8000/dashboard', 'banknotes', 'success', 1, '2026-09-13 22:15:58', '2026-09-13 21:06:53', '2026-09-13 22:15:58');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payables`
--

CREATE TABLE `payables` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `payable_code` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `vendor_name` varchar(255) NOT NULL,
  `creditor_type` varchar(255) NOT NULL DEFAULT 'vendor',
  `project_id` bigint(20) UNSIGNED DEFAULT NULL,
  `category` varchar(255) NOT NULL DEFAULT 'operational',
  `amount` decimal(15,2) NOT NULL,
  `paid_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `issue_date` date NOT NULL,
  `due_date` date NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `financial_account_id` bigint(20) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payables`
--

INSERT INTO `payables` (`id`, `payable_code`, `title`, `vendor_name`, `creditor_type`, `project_id`, `category`, `amount`, `paid_amount`, `issue_date`, `due_date`, `status`, `financial_account_id`, `notes`, `paid_at`, `created_at`, `updated_at`) VALUES
(1, 'PAY-2026-EXM2X', 'Server Cloud Verification Invoice', 'PT Cloud Hostindo Verification', 'vendor', NULL, 'Infrastructure', 750000.00, 750000.00, '2026-09-14', '2026-09-29', 'paid', 1, 'Test payable record', '2026-09-13 21:24:09', '2026-09-13 21:24:09', '2026-09-13 21:24:09');

-- --------------------------------------------------------

--
-- Table structure for table `payrolls`
--

CREATE TABLE `payrolls` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `period` varchar(7) NOT NULL,
  `base_salary` decimal(15,2) NOT NULL,
  `allowance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `bonus` decimal(15,2) NOT NULL DEFAULT 0.00,
  `deduction` decimal(15,2) NOT NULL DEFAULT 0.00,
  `reimbursement` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total` decimal(15,2) NOT NULL,
  `payment_status` varchar(255) NOT NULL DEFAULT 'draft',
  `paid_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payrolls`
--

INSERT INTO `payrolls` (`id`, `user_id`, `period`, `base_salary`, `allowance`, `bonus`, `deduction`, `reimbursement`, `total`, `payment_status`, `paid_at`, `notes`, `created_at`, `updated_at`) VALUES
(1, 11, '2026-09', 600000.00, 0.00, 0.00, 0.00, 0.00, 600000.00, 'paid', '2026-09-13 21:06:53', NULL, '2026-09-13 21:00:07', '2026-09-13 21:06:53');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `description` text DEFAULT NULL,
  `project_type` varchar(255) NOT NULL DEFAULT 'Software Development',
  `start_date` date NOT NULL,
  `deadline` date NOT NULL,
  `revenue` decimal(15,2) NOT NULL DEFAULT 0.00,
  `budget` decimal(15,2) NOT NULL DEFAULT 0.00,
  `actual_cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `profit` decimal(15,2) NOT NULL DEFAULT 0.00,
  `profit_margin` decimal(5,2) NOT NULL DEFAULT 0.00,
  `status` varchar(255) NOT NULL DEFAULT 'planning',
  `health` varchar(255) NOT NULL DEFAULT 'on_track',
  `closed_at` timestamp NULL DEFAULT NULL,
  `closure_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `project_code`, `name`, `client_id`, `description`, `project_type`, `start_date`, `deadline`, `revenue`, `budget`, `actual_cost`, `profit`, `profit_margin`, `status`, `health`, `closed_at`, `closure_notes`, `created_at`, `updated_at`) VALUES
(1, 'PRJ-AUDIT-1', 'Audit Project', 2, NULL, 'Internal', '2026-09-14', '2026-10-14', 10000000.00, 5000000.00, 5000000.00, 5000000.00, 50.00, 'active', 'at_risk', NULL, NULL, '2026-09-13 19:43:33', '2026-09-13 20:59:13'),
(6, 'PRJ-6AA76AC783FA4', 'Hydrone', 5, NULL, 'IoT Engineering', '2026-09-14', '2026-10-29', 23000000.00, 15000000.00, 0.00, 23000000.00, 100.00, 'active', 'on_track', NULL, NULL, '2026-09-13 20:32:51', '2026-09-13 20:32:51'),
(7, 'PRJ-MONITOR-1562', 'Monitoring Project 119', 2, 'A project managed purely for monitoring execution', 'internal', '2026-09-14', '2026-10-14', 0.00, 0.00, 0.00, 0.00, 0.00, 'active', 'on_track', NULL, NULL, '2026-09-13 20:35:13', '2026-09-13 20:35:13');

-- --------------------------------------------------------

--
-- Table structure for table `project_members`
--

CREATE TABLE `project_members` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role` varchar(255) NOT NULL,
  `responsibility` text DEFAULT NULL,
  `assigned_date` date NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `project_members`
--

INSERT INTO `project_members` (`id`, `project_id`, `user_id`, `role`, `responsibility`, `assigned_date`, `status`, `created_at`, `updated_at`) VALUES
(4, 6, 1, 'super_admin', 'Project Initiator', '2026-09-14', 'active', '2026-09-13 20:32:51', '2026-09-13 20:32:51'),
(7, 6, 11, 'designer', 'Task Contributor', '2026-09-14', 'active', '2026-09-13 20:47:59', '2026-09-13 20:47:59'),
(8, 1, 11, 'designer', 'Task Contributor', '2026-09-14', 'active', '2026-09-13 20:48:41', '2026-09-13 20:48:41');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_requests`
--

CREATE TABLE `purchase_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `request_number` varchar(255) NOT NULL,
  `requester_id` bigint(20) UNSIGNED NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `estimated_cost` decimal(15,2) NOT NULL,
  `actual_cost` decimal(15,2) DEFAULT NULL,
  `reason` text NOT NULL,
  `priority` varchar(255) NOT NULL DEFAULT 'medium',
  `status` varchar(255) NOT NULL DEFAULT 'submitted',
  `approver_id` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `created_asset_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_inventory_id` bigint(20) UNSIGNED DEFAULT NULL,
  `expense_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reimbursements`
--

CREATE TABLE `reimbursements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `date` date NOT NULL,
  `category` varchar(255) NOT NULL DEFAULT 'operational',
  `status` varchar(255) NOT NULL DEFAULT 'submitted',
  `approver_id` bigint(20) UNSIGNED DEFAULT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reimbursements`
--

INSERT INTO `reimbursements` (`id`, `user_id`, `project_id`, `title`, `amount`, `date`, `category`, `status`, `approver_id`, `attachment_path`, `notes`, `created_at`, `updated_at`) VALUES
(4, 14, 1, 'bnbv', 10000000.00, '2026-09-14', 'transportation', 'approved', 14, NULL, NULL, '2026-09-13 21:01:10', '2026-09-13 21:01:14');

-- --------------------------------------------------------

--
-- Table structure for table `resources`
--

CREATE TABLE `resources` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `resource_code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `owner_id` bigint(20) UNSIGNED DEFAULT NULL,
  `responsible_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `project_id` bigint(20) UNSIGNED DEFAULT NULL,
  `team_id` bigint(20) UNSIGNED DEFAULT NULL,
  `cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `purchase_date` date DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `location` varchar(255) DEFAULT NULL,
  `relevant_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `document_path` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `resources`
--

INSERT INTO `resources` (`id`, `resource_code`, `name`, `category`, `owner_id`, `responsible_user_id`, `project_id`, `team_id`, `cost`, `purchase_date`, `status`, `location`, `relevant_date`, `expiry_date`, `document_path`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'RES-INF-6AA75CB779F0B', 'solvianova', 'infrastructure', NULL, NULL, NULL, NULL, 150000.00, NULL, 'active', NULL, '2026-09-12', '2027-09-12', NULL, NULL, '2026-09-13 19:32:23', '2026-09-13 19:32:23'),
(2, 'RES-AST-TEST-001', 'Dell UltraSharp 27\"', 'asset', NULL, NULL, NULL, NULL, 6000000.00, '2026-09-14', 'available', NULL, NULL, NULL, NULL, NULL, '2026-09-13 19:49:14', '2026-09-13 19:49:14'),
(3, 'RES-INF-6AA760AA29D64', 'Broker Server', 'infrastructure', NULL, NULL, NULL, NULL, 300000.00, NULL, 'active', NULL, NULL, NULL, NULL, NULL, '2026-09-13 19:49:14', '2026-09-13 19:49:14'),
(4, 'RES-TEST-LAPTOP-9502', 'Apple M3 Pro', 'asset', NULL, NULL, NULL, NULL, 0.00, '2026-09-14', 'in_use', NULL, NULL, NULL, NULL, NULL, '2026-09-13 20:24:28', '2026-09-13 20:24:28'),
(5, 'RES-AST-PX4UGU', 'Asus Asus vivobook', 'asset', NULL, NULL, NULL, NULL, 0.00, '2026-09-14', 'in_use', NULL, NULL, NULL, NULL, NULL, '2026-09-13 20:25:39', '2026-09-13 20:25:39'),
(6, 'RES-TEST-LAPTOP-7719', 'Apple M3 Pro', 'asset', NULL, NULL, NULL, NULL, 0.00, '2026-09-14', 'in_use', NULL, NULL, NULL, NULL, NULL, '2026-09-13 20:26:50', '2026-09-13 20:26:50'),
(7, 'RES-TEST-LAPTOP-7222', 'Apple M3 Pro', 'asset', NULL, NULL, NULL, NULL, 0.00, '2026-09-14', 'in_use', NULL, NULL, NULL, NULL, NULL, '2026-09-13 20:27:09', '2026-09-13 20:27:09'),
(8, 'RES-TEST-LAPTOP-1871', 'Apple M3 Pro', 'asset', NULL, NULL, NULL, NULL, 0.00, '2026-09-14', 'in_use', NULL, NULL, NULL, NULL, NULL, '2026-09-13 20:27:21', '2026-09-13 20:27:21'),
(9, 'RES-TEST-LAPTOP-6658', 'Apple M3 Pro', 'asset', NULL, NULL, NULL, NULL, 0.00, '2026-09-14', 'in_use', NULL, NULL, NULL, NULL, NULL, '2026-09-13 20:27:31', '2026-09-13 20:27:31');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
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
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `resource_id` bigint(20) UNSIGNED NOT NULL,
  `provider` varchar(255) NOT NULL,
  `plan_name` varchar(255) NOT NULL,
  `cost` decimal(15,2) NOT NULL,
  `billing_cycle` varchar(255) NOT NULL DEFAULT 'monthly',
  `next_billing_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `auto_renewal` tinyint(1) NOT NULL DEFAULT 1,
  `max_users` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `milestone_id` bigint(20) UNSIGNED DEFAULT NULL,
  `assignee_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `priority` varchar(255) NOT NULL DEFAULT 'medium',
  `status` varchar(255) NOT NULL DEFAULT 'to_do',
  `progress` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `start_date` date DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `estimated_hours` decimal(8,2) NOT NULL DEFAULT 0.00,
  `actual_hours` decimal(8,2) NOT NULL DEFAULT 0.00,
  `attachment_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `project_id`, `milestone_id`, `assignee_id`, `title`, `description`, `priority`, `status`, `progress`, `start_date`, `deadline`, `estimated_hours`, `actual_hours`, `attachment_path`, `created_at`, `updated_at`) VALUES
(5, 6, NULL, 11, 'Design Website', NULL, 'urgent', 'in_progress', 0, NULL, '2026-09-21', 30.00, 0.00, NULL, '2026-09-13 20:34:02', '2026-09-13 20:34:02'),
(7, 1, NULL, 11, 'QA Audit Task 7503', 'Automated QA flow verification test', 'urgent', 'in_progress', 60, NULL, NULL, 5.00, 4.50, NULL, '2026-09-13 20:52:01', '2026-09-13 20:52:01'),
(8, 1, NULL, 11, 'QA Audit Task 4260', 'Automated QA flow verification test', 'urgent', 'blocked', 60, NULL, NULL, 5.00, 4.50, NULL, '2026-09-13 20:52:40', '2026-09-13 20:52:40');

-- --------------------------------------------------------

--
-- Table structure for table `task_dependencies`
--

CREATE TABLE `task_dependencies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `task_id` bigint(20) UNSIGNED NOT NULL,
  `depends_on_task_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'blocked_by',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `lead_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teams`
--

INSERT INTO `teams` (`id`, `name`, `code`, `description`, `lead_id`, `created_at`, `updated_at`) VALUES
(2, 'Design', 'Des', NULL, 11, '2026-09-13 20:08:29', '2026-09-13 20:08:29');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'backend_developer',
  `department` varchar(255) DEFAULT NULL,
  `team_id` bigint(20) UNSIGNED DEFAULT NULL,
  `join_date` date DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `avatar_url` varchar(255) DEFAULT NULL,
  `profile_bio` text DEFAULT NULL,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissions`)),
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `phone`, `role`, `department`, `team_id`, `join_date`, `status`, `avatar_url`, `profile_bio`, `permissions`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Nanda Ariwahyu', 'nandaariwahyu07@gmail.com', NULL, '$2y$12$4k3NN0VyhgqSmS/vRnKVV.yyBobd4mWsxIc5Ui7WylvbvLVgh9JwO', NULL, 'super_admin', 'Executive Office', NULL, '2022-01-10', 'active', NULL, 'Founder & Chief Executive Officer at Solvia Group.', NULL, 'uSvjrUqFx8VpaSwS21rx4eRF1Ef7WwXs0ac3talrVmQLzk1WYRPw2NQgBg65', '2026-09-11 21:43:53', '2026-09-13 21:34:23'),
(11, 'Putra Pratama (Senior Designer)', 'putra@gmail.com', NULL, '$2y$12$p5u.F6ECujQI1zE/jeNCvulpFEw1nFCafs3d61Vz5fuaXlcY5AJxq', '+62 812-9988-7766', 'designer', 'Creative & Product Design', NULL, '2026-09-14', 'active', NULL, 'Lead product designer specializing in dark mode interfaces and design systems.', NULL, NULL, '2026-09-13 20:08:02', '2026-09-13 21:34:23'),
(14, 'Nurul Arista', 'nurularista@gmail.com', NULL, '$2y$12$4k3NN0VyhgqSmS/vRnKVV.yyBobd4mWsxIc5Ui7WylvbvLVgh9JwO', NULL, 'super_admin', NULL, NULL, '2026-09-14', 'active', NULL, NULL, NULL, NULL, '2026-09-13 20:38:18', '2026-09-13 20:38:18');

-- --------------------------------------------------------

--
-- Table structure for table `warranties`
--

CREATE TABLE `warranties` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `asset_id` bigint(20) UNSIGNED NOT NULL,
  `provider` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `expiry_date` date NOT NULL,
  `document_path` varchar(255) DEFAULT NULL,
  `terms` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `warranties`
--

INSERT INTO `warranties` (`id`, `asset_id`, `provider`, `start_date`, `expiry_date`, `document_path`, `terms`, `created_at`, `updated_at`) VALUES
(1, 3, 'Asus vivobook', '2026-09-14', '2029-12-12', NULL, NULL, '2026-09-13 20:25:39', '2026-09-13 20:25:39');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `announcements_author_id_foreign` (`author_id`);

--
-- Indexes for table `announcement_reads`
--
ALTER TABLE `announcement_reads`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `announcement_reads_announcement_id_user_id_unique` (`announcement_id`,`user_id`),
  ADD KEY `announcement_reads_user_id_foreign` (`user_id`);

--
-- Indexes for table `assets`
--
ALTER TABLE `assets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `assets_asset_tag_unique` (`asset_tag`),
  ADD KEY `assets_resource_id_foreign` (`resource_id`),
  ADD KEY `assets_current_holder_id_foreign` (`current_holder_id`);

--
-- Indexes for table `asset_assignments`
--
ALTER TABLE `asset_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `asset_assignments_asset_id_foreign` (`asset_id`),
  ADD KEY `asset_assignments_user_id_foreign` (`user_id`);

--
-- Indexes for table `asset_maintenances`
--
ALTER TABLE `asset_maintenances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `asset_maintenances_asset_id_foreign` (`asset_id`),
  ADD KEY `asset_maintenances_expense_id_foreign` (`expense_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `audit_logs_actor_id_foreign` (`actor_id`);

--
-- Indexes for table `automation_logs`
--
ALTER TABLE `automation_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `automation_logs_automation_rule_id_foreign` (`automation_rule_id`);

--
-- Indexes for table `automation_rules`
--
ALTER TABLE `automation_rules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `blockers`
--
ALTER TABLE `blockers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `blockers_project_id_foreign` (`project_id`),
  ADD KEY `blockers_task_id_foreign` (`task_id`),
  ADD KEY `blockers_reporter_id_foreign` (`reporter_id`),
  ADD KEY `blockers_responsible_user_id_foreign` (`responsible_user_id`);

--
-- Indexes for table `budgets`
--
ALTER TABLE `budgets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `budgets_project_id_foreign` (`project_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `clients_client_code_unique` (`client_code`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `comments_commentable_type_commentable_id_index` (`commentable_type`,`commentable_id`),
  ADD KEY `comments_user_id_foreign` (`user_id`);

--
-- Indexes for table `company_accounts`
--
ALTER TABLE `company_accounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_accounts_resource_id_foreign` (`resource_id`);

--
-- Indexes for table `company_profiles`
--
ALTER TABLE `company_profiles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contracts`
--
ALTER TABLE `contracts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contracts_resource_id_foreign` (`resource_id`);

--
-- Indexes for table `daily_progress`
--
ALTER TABLE `daily_progress`
  ADD PRIMARY KEY (`id`),
  ADD KEY `daily_progress_user_id_foreign` (`user_id`),
  ADD KEY `daily_progress_project_id_foreign` (`project_id`),
  ADD KEY `daily_progress_task_id_foreign` (`task_id`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `documents_uploader_id_foreign` (`uploader_id`),
  ADD KEY `documents_documentable_type_documentable_id_index` (`documentable_type`,`documentable_id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `expenses_expense_number_unique` (`expense_number`),
  ADD KEY `expenses_project_id_foreign` (`project_id`),
  ADD KEY `expenses_account_id_foreign` (`account_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `financial_accounts`
--
ALTER TABLE `financial_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `financial_accounts_account_code_unique` (`account_code`);

--
-- Indexes for table `financial_transactions`
--
ALTER TABLE `financial_transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `financial_transactions_transaction_code_unique` (`transaction_code`),
  ADD KEY `financial_transactions_financial_account_id_foreign` (`financial_account_id`),
  ADD KEY `financial_transactions_to_account_id_foreign` (`to_account_id`),
  ADD KEY `financial_transactions_project_id_foreign` (`project_id`),
  ADD KEY `financial_transactions_invoice_id_foreign` (`invoice_id`),
  ADD KEY `financial_transactions_employee_id_foreign` (`employee_id`),
  ADD KEY `financial_transactions_asset_id_foreign` (`asset_id`),
  ADD KEY `financial_transactions_resource_id_foreign` (`resource_id`),
  ADD KEY `financial_transactions_created_by_foreign` (`created_by`),
  ADD KEY `financial_transactions_approved_by_foreign` (`approved_by`),
  ADD KEY `financial_transactions_transaction_type_index` (`transaction_type`),
  ADD KEY `financial_transactions_category_index` (`category`),
  ADD KEY `financial_transactions_transaction_date_index` (`transaction_date`),
  ADD KEY `financial_transactions_reference_id_index` (`reference_id`),
  ADD KEY `financial_transactions_status_index` (`status`);

--
-- Indexes for table `incomes`
--
ALTER TABLE `incomes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `incomes_income_number_unique` (`income_number`),
  ADD KEY `incomes_client_id_foreign` (`client_id`),
  ADD KEY `incomes_project_id_foreign` (`project_id`),
  ADD KEY `incomes_account_id_foreign` (`account_id`);

--
-- Indexes for table `infrastructures`
--
ALTER TABLE `infrastructures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `infrastructures_resource_id_foreign` (`resource_id`);

--
-- Indexes for table `inventory_items`
--
ALTER TABLE `inventory_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `inventory_items_sku_unique` (`sku`);

--
-- Indexes for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inventory_transactions_inventory_item_id_foreign` (`inventory_item_id`),
  ADD KEY `inventory_transactions_user_id_foreign` (`user_id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoices_invoice_number_unique` (`invoice_number`),
  ADD KEY `invoices_client_id_foreign` (`client_id`),
  ADD KEY `invoices_project_id_foreign` (`project_id`);

--
-- Indexes for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_items_invoice_id_foreign` (`invoice_id`);

--
-- Indexes for table `invoice_payments`
--
ALTER TABLE `invoice_payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_payments_payment_number_unique` (`payment_number`),
  ADD KEY `invoice_payments_invoice_id_foreign` (`invoice_id`),
  ADD KEY `invoice_payments_financial_account_id_foreign` (`financial_account_id`),
  ADD KEY `invoice_payments_created_by_foreign` (`created_by`),
  ADD KEY `invoice_payments_payment_date_index` (`payment_date`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `knowledge_bases`
--
ALTER TABLE `knowledge_bases`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `knowledge_bases_slug_unique` (`slug`),
  ADD KEY `knowledge_bases_author_id_foreign` (`author_id`);

--
-- Indexes for table `licenses`
--
ALTER TABLE `licenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `licenses_resource_id_foreign` (`resource_id`),
  ADD KEY `licenses_user_id_foreign` (`user_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `milestones`
--
ALTER TABLE `milestones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `milestones_project_id_foreign` (`project_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_user_id_foreign` (`user_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payables`
--
ALTER TABLE `payables`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payables_payable_code_unique` (`payable_code`),
  ADD KEY `payables_project_id_foreign` (`project_id`),
  ADD KEY `payables_financial_account_id_foreign` (`financial_account_id`),
  ADD KEY `payables_due_date_index` (`due_date`),
  ADD KEY `payables_status_index` (`status`);

--
-- Indexes for table `payrolls`
--
ALTER TABLE `payrolls`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payrolls_user_id_foreign` (`user_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `projects_project_code_unique` (`project_code`),
  ADD KEY `projects_client_id_foreign` (`client_id`);

--
-- Indexes for table `project_members`
--
ALTER TABLE `project_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_members_project_id_foreign` (`project_id`),
  ADD KEY `project_members_user_id_foreign` (`user_id`);

--
-- Indexes for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `purchase_requests_request_number_unique` (`request_number`),
  ADD KEY `purchase_requests_requester_id_foreign` (`requester_id`),
  ADD KEY `purchase_requests_approver_id_foreign` (`approver_id`),
  ADD KEY `purchase_requests_created_asset_id_foreign` (`created_asset_id`),
  ADD KEY `purchase_requests_created_inventory_id_foreign` (`created_inventory_id`),
  ADD KEY `purchase_requests_expense_id_foreign` (`expense_id`);

--
-- Indexes for table `reimbursements`
--
ALTER TABLE `reimbursements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reimbursements_user_id_foreign` (`user_id`),
  ADD KEY `reimbursements_project_id_foreign` (`project_id`),
  ADD KEY `reimbursements_approver_id_foreign` (`approver_id`);

--
-- Indexes for table `resources`
--
ALTER TABLE `resources`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `resources_resource_code_unique` (`resource_code`),
  ADD KEY `resources_owner_id_foreign` (`owner_id`),
  ADD KEY `resources_responsible_user_id_foreign` (`responsible_user_id`),
  ADD KEY `resources_project_id_foreign` (`project_id`),
  ADD KEY `resources_team_id_foreign` (`team_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subscriptions_resource_id_foreign` (`resource_id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tasks_project_id_foreign` (`project_id`),
  ADD KEY `tasks_milestone_id_foreign` (`milestone_id`),
  ADD KEY `tasks_assignee_id_foreign` (`assignee_id`);

--
-- Indexes for table `task_dependencies`
--
ALTER TABLE `task_dependencies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_dependencies_task_id_foreign` (`task_id`),
  ADD KEY `task_dependencies_depends_on_task_id_foreign` (`depends_on_task_id`);

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `teams_code_unique` (`code`),
  ADD KEY `teams_lead_id_foreign` (`lead_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `warranties`
--
ALTER TABLE `warranties`
  ADD PRIMARY KEY (`id`),
  ADD KEY `warranties_asset_id_foreign` (`asset_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `announcement_reads`
--
ALTER TABLE `announcement_reads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `assets`
--
ALTER TABLE `assets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `asset_assignments`
--
ALTER TABLE `asset_assignments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `asset_maintenances`
--
ALTER TABLE `asset_maintenances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=130;

--
-- AUTO_INCREMENT for table `automation_logs`
--
ALTER TABLE `automation_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `automation_rules`
--
ALTER TABLE `automation_rules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `blockers`
--
ALTER TABLE `blockers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `budgets`
--
ALTER TABLE `budgets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `company_accounts`
--
ALTER TABLE `company_accounts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `company_profiles`
--
ALTER TABLE `company_profiles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `contracts`
--
ALTER TABLE `contracts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `daily_progress`
--
ALTER TABLE `daily_progress`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `financial_accounts`
--
ALTER TABLE `financial_accounts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `financial_transactions`
--
ALTER TABLE `financial_transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `incomes`
--
ALTER TABLE `incomes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `infrastructures`
--
ALTER TABLE `infrastructures`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `inventory_items`
--
ALTER TABLE `inventory_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `invoice_payments`
--
ALTER TABLE `invoice_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `knowledge_bases`
--
ALTER TABLE `knowledge_bases`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `licenses`
--
ALTER TABLE `licenses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `milestones`
--
ALTER TABLE `milestones`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `payables`
--
ALTER TABLE `payables`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payrolls`
--
ALTER TABLE `payrolls`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `project_members`
--
ALTER TABLE `project_members`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `reimbursements`
--
ALTER TABLE `reimbursements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `resources`
--
ALTER TABLE `resources`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `task_dependencies`
--
ALTER TABLE `task_dependencies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `warranties`
--
ALTER TABLE `warranties`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `announcement_reads`
--
ALTER TABLE `announcement_reads`
  ADD CONSTRAINT `announcement_reads_announcement_id_foreign` FOREIGN KEY (`announcement_id`) REFERENCES `announcements` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `announcement_reads_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `assets`
--
ALTER TABLE `assets`
  ADD CONSTRAINT `assets_current_holder_id_foreign` FOREIGN KEY (`current_holder_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `assets_resource_id_foreign` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `asset_assignments`
--
ALTER TABLE `asset_assignments`
  ADD CONSTRAINT `asset_assignments_asset_id_foreign` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `asset_assignments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `asset_maintenances`
--
ALTER TABLE `asset_maintenances`
  ADD CONSTRAINT `asset_maintenances_asset_id_foreign` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `asset_maintenances_expense_id_foreign` FOREIGN KEY (`expense_id`) REFERENCES `expenses` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_actor_id_foreign` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `automation_logs`
--
ALTER TABLE `automation_logs`
  ADD CONSTRAINT `automation_logs_automation_rule_id_foreign` FOREIGN KEY (`automation_rule_id`) REFERENCES `automation_rules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `blockers`
--
ALTER TABLE `blockers`
  ADD CONSTRAINT `blockers_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `blockers_reporter_id_foreign` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `blockers_responsible_user_id_foreign` FOREIGN KEY (`responsible_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `blockers_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `budgets`
--
ALTER TABLE `budgets`
  ADD CONSTRAINT `budgets_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `company_accounts`
--
ALTER TABLE `company_accounts`
  ADD CONSTRAINT `company_accounts_resource_id_foreign` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `contracts`
--
ALTER TABLE `contracts`
  ADD CONSTRAINT `contracts_resource_id_foreign` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `daily_progress`
--
ALTER TABLE `daily_progress`
  ADD CONSTRAINT `daily_progress_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `daily_progress_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `daily_progress_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_uploader_id_foreign` FOREIGN KEY (`uploader_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `expenses_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `financial_accounts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `expenses_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `financial_transactions`
--
ALTER TABLE `financial_transactions`
  ADD CONSTRAINT `financial_transactions_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `financial_transactions_asset_id_foreign` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `financial_transactions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `financial_transactions_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `financial_transactions_financial_account_id_foreign` FOREIGN KEY (`financial_account_id`) REFERENCES `financial_accounts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `financial_transactions_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `financial_transactions_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `financial_transactions_resource_id_foreign` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `financial_transactions_to_account_id_foreign` FOREIGN KEY (`to_account_id`) REFERENCES `financial_accounts` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `incomes`
--
ALTER TABLE `incomes`
  ADD CONSTRAINT `incomes_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `financial_accounts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `incomes_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `incomes_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `infrastructures`
--
ALTER TABLE `infrastructures`
  ADD CONSTRAINT `infrastructures_resource_id_foreign` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD CONSTRAINT `inventory_transactions_inventory_item_id_foreign` FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoices_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD CONSTRAINT `invoice_items_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invoice_payments`
--
ALTER TABLE `invoice_payments`
  ADD CONSTRAINT `invoice_payments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `invoice_payments_financial_account_id_foreign` FOREIGN KEY (`financial_account_id`) REFERENCES `financial_accounts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoice_payments_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `knowledge_bases`
--
ALTER TABLE `knowledge_bases`
  ADD CONSTRAINT `knowledge_bases_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `licenses`
--
ALTER TABLE `licenses`
  ADD CONSTRAINT `licenses_resource_id_foreign` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `licenses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `milestones`
--
ALTER TABLE `milestones`
  ADD CONSTRAINT `milestones_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payables`
--
ALTER TABLE `payables`
  ADD CONSTRAINT `payables_financial_account_id_foreign` FOREIGN KEY (`financial_account_id`) REFERENCES `financial_accounts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payables_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payrolls`
--
ALTER TABLE `payrolls`
  ADD CONSTRAINT `payrolls_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_members`
--
ALTER TABLE `project_members`
  ADD CONSTRAINT `project_members_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_members_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  ADD CONSTRAINT `purchase_requests_approver_id_foreign` FOREIGN KEY (`approver_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchase_requests_created_asset_id_foreign` FOREIGN KEY (`created_asset_id`) REFERENCES `assets` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchase_requests_created_inventory_id_foreign` FOREIGN KEY (`created_inventory_id`) REFERENCES `inventory_items` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchase_requests_expense_id_foreign` FOREIGN KEY (`expense_id`) REFERENCES `expenses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchase_requests_requester_id_foreign` FOREIGN KEY (`requester_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reimbursements`
--
ALTER TABLE `reimbursements`
  ADD CONSTRAINT `reimbursements_approver_id_foreign` FOREIGN KEY (`approver_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `reimbursements_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `reimbursements_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `resources`
--
ALTER TABLE `resources`
  ADD CONSTRAINT `resources_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `resources_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `resources_responsible_user_id_foreign` FOREIGN KEY (`responsible_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `resources_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_resource_id_foreign` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_assignee_id_foreign` FOREIGN KEY (`assignee_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tasks_milestone_id_foreign` FOREIGN KEY (`milestone_id`) REFERENCES `milestones` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tasks_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `task_dependencies`
--
ALTER TABLE `task_dependencies`
  ADD CONSTRAINT `task_dependencies_depends_on_task_id_foreign` FOREIGN KEY (`depends_on_task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `task_dependencies_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `teams`
--
ALTER TABLE `teams`
  ADD CONSTRAINT `teams_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `warranties`
--
ALTER TABLE `warranties`
  ADD CONSTRAINT `warranties_asset_id_foreign` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
