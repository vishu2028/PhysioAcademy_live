-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 26, 2026 at 11:45 AM
-- Server version: 8.0.30
-- PHP Version: 8.3.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `physio_academy`
--

-- --------------------------------------------------------

--
-- Table structure for table `academic_years`
--

CREATE TABLE `academic_years` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `units_count` int NOT NULL DEFAULT '0',
  `topics_count` int NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `academic_years`
--

INSERT INTO `academic_years` (`id`, `name`, `slug`, `description`, `units_count`, `topics_count`, `status`, `order`, `created_at`, `updated_at`) VALUES
(1, 'First Year', 'first-year', 'Foundational subjects including Anatomy, Physiology and Biochemistry.', 8, 120, 1, 1, '2026-05-25 13:06:34', '2026-05-25 13:06:34'),
(2, 'Second Year', 'second-year', 'Introduction to core Physiotherapy skills, Pathology and Kinesiology.', 10, 150, 1, 2, '2026-05-25 13:06:34', '2026-05-25 13:06:34'),
(3, 'Third Year', 'third-year', 'Clinical subjects including Musculoskeletal and Electrotherapy.', 12, 200, 1, 3, '2026-05-25 13:06:34', '2026-05-25 13:06:34'),
(4, 'Fourth Year', 'fourth-year', 'Advanced Clinical Rehabilitation, Neurology and Research.', 15, 250, 1, 4, '2026-05-25 13:06:34', '2026-05-25 13:06:34'),
(5, 'Internship', 'internship', 'Hands-on clinical practice and masterclasses.', 5, 50, 1, 5, '2026-05-25 13:06:34', '2026-05-25 13:06:34');

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `module` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `properties` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `module`, `description`, `ip_address`, `user_agent`, `properties`, `created_at`, `updated_at`) VALUES
(1, 2, 'LOGIN', 'Auth', 'Admin session started', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-23 13:45:26', '2026-05-23 13:45:26'),
(2, 1, 'LOGIN', 'Auth', 'Admin session started', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-24 12:29:06', '2026-05-24 12:29:06'),
(3, 1, 'UPDATE', 'Page', 'UPDATE action on Page: \'About Us\'', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '{\"original\": {\"id\": 1, \"slug\": \"about-us\", \"title\": \"About Us\", \"status\": true, \"content\": \"<h2>About Physio Academy</h2><p>Physio Academy is a premier online platform dedicated to helping physiotherapy students excel in their academics. Founded by a team of experienced physiotherapy educators, our mission is to make quality study resources accessible to every BPT and MPT student.</p><p>We believe in structured learning, comprehensive coverage, and exam-focused preparation that builds both knowledge and confidence.</p>\", \"created_at\": \"2026-05-21T18:22:42.000000Z\", \"meta_title\": \"About Physio Academy - Your Academic Guide\", \"updated_at\": \"2026-05-21T18:22:42.000000Z\", \"meta_description\": \"Learn about Physio Academy, the premier online platform for physiotherapy students.\"}, \"attributes\": {\"id\": 1, \"slug\": \"about-us\", \"title\": \"About Us\", \"status\": true, \"content\": \"<h2>About Physio Academy</h2><p>Physio Academy is a premier online platform dedicated to helping physiotherapy students excel in their academics. Founded by a team of experienced physiotherapy educators, our mission is to make quality study resources accessible to every BPT and MPT student.</p><p>We believe in structured learning, comprehensive coverage, and exam-focused preparation that builds both knowledge and confidence.</p>\", \"created_at\": \"2026-05-21 18:22:42\", \"meta_title\": \"About Physio Academy - Your Academic Guide\", \"updated_at\": \"2026-05-24 17:47:00\", \"meta_description\": \"Learn about Physio Academy, the premier online platform for physiotherapy students.\"}}', '2026-05-24 12:47:00', '2026-05-24 12:47:00'),
(4, 1, 'UPDATE', 'Page', 'UPDATE action on Page: \'About Us\'', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '{\"original\": {\"id\": 1, \"slug\": \"about-us\", \"title\": \"About Us\", \"status\": true, \"content\": \"<h2>About Physio Academy</h2><p>Physio Academy is a premier online platform dedicated to helping physiotherapy students excel in their academics. Founded by a team of experienced physiotherapy educators, our mission is to make quality study resources accessible to every BPT and MPT student.</p><p>We believe in structured learning, comprehensive coverage, and exam-focused preparation that builds both knowledge and confidence.</p>\", \"created_at\": \"2026-05-21T18:22:42.000000Z\", \"meta_title\": \"About Physio Academy - Your Academic Guide\", \"updated_at\": \"2026-05-24T17:47:00.000000Z\", \"meta_description\": \"Learn about Physio Academy, the premier online platform for physiotherapy students.\"}, \"attributes\": {\"id\": 1, \"slug\": \"about-us\", \"title\": \"About Us\", \"status\": true, \"content\": \"<h2>About Physio Academy</h2><p>Physio Academy is a premier online platform dedicated to helping physiotherapy students excel in their academics. Founded by a team of experienced physiotherapy educators, our mission is to make quality study resources accessible to every BPT and MPT student.</p><p>We believe in structured learning, comprehensive coverage, and exam-focused preparation that builds both knowledge and confidence.</p>\", \"created_at\": \"2026-05-21 18:22:42\", \"meta_title\": \"About Physio Academy - Your Academic Guide\", \"updated_at\": \"2026-05-24 17:47:36\", \"meta_description\": \"Learn about Physio Academy, the premier online platform for physiotherapy students.\"}}', '2026-05-24 12:47:36', '2026-05-24 12:47:36'),
(5, 1, 'LOGIN', 'Auth', 'Admin session started', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-25 06:04:19', '2026-05-25 06:04:19'),
(6, 1, 'LOGIN', 'Auth', 'Admin session started', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-25 10:47:28', '2026-05-25 10:47:28'),
(7, 1, 'LOGIN', 'Auth', 'Admin session started', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-25 12:13:51', '2026-05-25 12:13:51'),
(8, 1, 'LOGIN', 'Auth', 'Admin session started', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 00:32:01', '2026-05-26 00:32:01'),
(9, 2, 'LOGIN', 'Auth', 'Admin session started', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 00:48:36', '2026-05-26 00:48:36'),
(10, 2, 'LOGOUT', 'Auth', 'Admin session ended', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 02:08:35', '2026-05-26 02:08:35'),
(11, 11, 'LOGIN', 'Auth', 'Admin session started', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 02:11:38', '2026-05-26 02:11:38'),
(12, 1, 'CREATE', 'Subject', 'CREATE action on Subject: \'Artificial Intelligence\'', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '{\"original\": [], \"attributes\": {\"id\": 11, \"icon\": \"🧠\", \"name\": \"Artificial Intelligence\", \"slug\": \"artificial-intelligence\", \"order\": \"5\", \"status\": 1, \"created_at\": \"2026-05-26 07:13:26\", \"updated_at\": \"2026-05-26 07:13:26\", \"description\": \"Study how machines can think and learn like humans.\\r\\nIncludes machine learning, data models, and automation.\"}}', '2026-05-26 02:13:26', '2026-05-26 02:13:26'),
(13, 1, 'UPDATE', 'Subject', 'UPDATE action on Subject: \'Artificial Intelligence\'', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '{\"original\": {\"id\": 11, \"icon\": \"🧠\", \"name\": \"Artificial Intelligence\", \"slug\": \"artificial-intelligence\", \"image\": null, \"order\": 5, \"status\": 1, \"created_at\": \"2026-05-26T07:13:26.000000Z\", \"updated_at\": \"2026-05-26T07:13:26.000000Z\", \"description\": \"Study how machines can think and learn like humans.\\r\\nIncludes machine learning, data models, and automation.\"}, \"attributes\": {\"id\": 11, \"icon\": \"🧠\", \"name\": \"Artificial Intelligence\", \"slug\": \"artificial-intelligence\", \"image\": null, \"order\": \"11\", \"status\": 1, \"created_at\": \"2026-05-26 07:13:26\", \"updated_at\": \"2026-05-26 07:13:44\", \"description\": \"Study how machines can think and learn like humans.\\r\\nIncludes machine learning, data models, and automation.\"}}', '2026-05-26 02:13:44', '2026-05-26 02:13:44'),
(14, 1, 'LOGIN', 'Auth', 'Admin session started', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 06:01:02', '2026-05-26 06:01:02'),
(15, 1, 'LOGIN', 'Auth', 'Admin session started', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 06:12:02', '2026-05-26 06:12:02');

-- --------------------------------------------------------

--
-- Table structure for table `banners`
--

CREATE TABLE `banners` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `banners`
--

INSERT INTO `banners` (`id`, `title`, `description`, `image_path`, `link`, `status`, `order`, `created_at`, `updated_at`) VALUES
(1, 'Limited Offer: Get 50% off on all Premium Plans!', NULL, 'banners/sale.jpg', '/register', 1, 0, '2026-05-21 13:22:42', '2026-05-21 13:22:42'),
(2, 'New: Complete Anatomy Study Pack Available', NULL, 'banners/anatomy.jpg', '#', 1, 0, '2026-05-21 13:22:42', '2026-05-21 13:22:42');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faqs`
--

CREATE TABLE `faqs` (
  `id` bigint UNSIGNED NOT NULL,
  `question` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `answer` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `faqs`
--

INSERT INTO `faqs` (`id`, `question`, `answer`, `category`, `status`, `order`, `created_at`, `updated_at`) VALUES
(1, 'What is Physio Academy?', 'Physio Academy is a comprehensive academic platform designed specifically for physiotherapy students. It provides syllabus navigation, exam preparation tools, answer writing guides, and clinical notes.', 'General', 1, 1, '2026-05-21 13:22:42', '2026-05-26 01:52:24'),
(2, 'Is this platform free to use?', 'Yes, the basic features are completely free. We offer premium plans for advanced features like video lectures, personalized mentoring, and downloadable study materials.', NULL, 1, 2, '2026-05-21 13:22:42', '2026-05-21 13:22:42'),
(3, 'How do I change the site theme?', 'Administrators can change primary colors, logos, and other visual settings from the Admin Dashboard under Site Settings.', NULL, 1, 3, '2026-05-21 13:22:42', '2026-05-21 13:22:42'),
(4, 'Can I download study materials?', 'Premium members can download PDFs, exam prep sheets, and clinical notes. Free members can access all online content.', NULL, 1, 4, '2026-05-21 13:22:42', '2026-05-21 13:22:42'),
(5, 'How can I contact support?', 'You can reach us via the Contact form on our website, or email us at contact@physioacademy.com. We typically respond within 24 hours.', NULL, 1, 5, '2026-05-21 13:22:42', '2026-05-21 13:22:42');

-- --------------------------------------------------------

--
-- Table structure for table `features`
--

CREATE TABLE `features` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `features`
--

INSERT INTO `features` (`id`, `title`, `description`, `icon`, `status`, `order`, `created_at`, `updated_at`) VALUES
(1, 'Secure Authentication', 'Role-based access with encrypted credentials and session management.', 'bi bi-shield-lock', 1, 0, '2026-05-21 13:22:42', '2026-05-21 13:22:42'),
(2, 'Smart Dashboard', 'Real-time analytics and personalized learning progress tracking.', 'bi bi-speedometer2', 1, 0, '2026-05-21 13:22:42', '2026-05-21 13:22:42'),
(3, 'Dynamic Content', 'Manage pages, heroes, and services from a powerful admin panel.', 'bi bi-gear-wide-connected', 1, 0, '2026-05-21 13:22:42', '2026-05-21 13:22:42'),
(4, 'Mobile Responsive', 'Optimized experience across all devices — desktop, tablet, and mobile.', 'bi bi-phone', 1, 0, '2026-05-21 13:22:42', '2026-05-23 04:43:42');

-- --------------------------------------------------------

--
-- Table structure for table `hero_sections`
--

CREATE TABLE `hero_sections` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subtitle` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `button_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `button_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order` int NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hero_sections`
--

INSERT INTO `hero_sections` (`id`, `title`, `subtitle`, `image_path`, `button_text`, `button_url`, `order`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Your Academic Guide for Physiotherapy', 'Navigate your syllabus, understand important topics, improve answer writing, and get academic support —all in one place.', 'hero/hwO0MgC57orWMrVRx3uGvHXrezFweKo3ipTwBOBL.jpg', 'Explore Topics', '#topics', 0, 1, '2026-05-21 13:22:42', '2026-05-26 01:07:53');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `learning_materials`
--

CREATE TABLE `learning_materials` (
  `id` bigint UNSIGNED NOT NULL,
  `topic_id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('pdf','video','link','note') COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `learning_materials`
--

INSERT INTO `learning_materials` (`id`, `topic_id`, `title`, `type`, `content`, `file_path`, `url`, `order`, `created_at`, `updated_at`) VALUES
(1, 1, 'Skeletal System PDF', 'pdf', NULL, NULL, 'https://example.com/skeletal-system.pdf', 1, '2026-05-25 13:06:34', '2026-05-25 13:06:34'),
(2, 1, 'Bone Classification Video', 'video', NULL, NULL, 'https://youtube.com/watch?v=example1', 2, '2026-05-25 13:06:34', '2026-05-25 13:06:34'),
(3, 1, 'Osteology Masterclass', 'link', NULL, NULL, 'https://physio-academy.com/osteology', 3, '2026-05-25 13:06:34', '2026-05-25 13:06:34'),
(4, 2, 'Plexus Diagram Download', 'pdf', NULL, NULL, 'https://example.com/brachial-plexus.pdf', 1, '2026-05-25 13:06:34', '2026-05-25 13:06:34'),
(5, 2, 'Clinical Correlations Note', 'note', 'Erb\'s Palsy and Klumpke\'s Paralysis are the most important clinical cases for this topic.', NULL, NULL, 2, '2026-05-25 13:06:34', '2026-05-25 13:06:34'),
(6, 2, 'Viva Voice Questions', 'link', NULL, NULL, 'https://example.com/viva-prep', 3, '2026-05-25 13:06:34', '2026-05-25 13:06:34'),
(7, 3, 'Cardiac Cycle Animation', 'video', NULL, NULL, 'https://youtube.com/watch?v=example2', 1, '2026-05-25 13:06:34', '2026-05-25 13:06:34'),
(8, 3, 'Heart Sounds Tutorial', 'link', NULL, NULL, 'https://example.com/heart-sounds', 2, '2026-05-25 13:06:34', '2026-05-25 13:06:34'),
(9, 4, 'Gait Parameters Guide', 'pdf', NULL, NULL, 'https://example.com/gait-analysis.pdf', 1, '2026-05-25 13:06:34', '2026-05-25 13:06:34'),
(10, 4, 'Abnormal Gait Patterns', 'video', NULL, NULL, 'https://youtube.com/watch?v=example3', 2, '2026-05-25 13:06:34', '2026-05-25 13:06:34'),
(11, 5, 'Rehab Protocol Stages', 'pdf', NULL, NULL, 'https://example.com/acl-rehab.pdf', 1, '2026-05-25 13:06:34', '2026-05-25 13:06:34'),
(12, 5, 'Special Tests for Knee', 'video', NULL, NULL, 'https://youtube.com/watch?v=example4', 2, '2026-05-25 13:06:34', '2026-05-25 13:06:34'),
(13, 6, 'Brunnstrom Stages Note', 'note', 'The 7 stages of recovery defined by Signe Brunnstrom are crucial for clinical assessment.', NULL, NULL, 1, '2026-05-25 13:06:34', '2026-05-25 13:06:34'),
(14, 6, 'Proprioceptive Neuromuscular Facilitation (PNF)', 'video', NULL, NULL, 'https://youtube.com/watch?v=example5', 2, '2026-05-25 13:06:34', '2026-05-25 13:06:34');

-- --------------------------------------------------------

--
-- Table structure for table `media`
--

CREATE TABLE `media` (
  `id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_id` bigint UNSIGNED DEFAULT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `folder` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `media`
--

INSERT INTO `media` (`id`, `model_type`, `model_id`, `file_name`, `file_path`, `file_size`, `file_type`, `folder`, `created_at`, `updated_at`) VALUES
(1, NULL, NULL, 'create a linkedin banner for my profile Laravel Developer with my name Hammad Khan.jpg', 'media/6OWdW8vRin56QXs8yPjWwxc2aT4SZSrznIcYl2ab.jpg', '153609', 'image/jpeg', 'general', '2026-05-23 05:02:42', '2026-05-23 05:02:42');

-- --------------------------------------------------------

--
-- Table structure for table `menus`
--

CREATE TABLE `menus` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` bigint UNSIGNED NOT NULL,
  `menu_id` bigint UNSIGNED NOT NULL,
  `parent_id` bigint UNSIGNED DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#',
  `target` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '_self',
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `css_class` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order` int NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `name`, `email`, `subject`, `message`, `is_read`, `created_at`, `updated_at`) VALUES
(1, 'Neha Gupta', 'neha@example.com', 'Question about Anatomy notes', 'Hi, I was looking for detailed notes on upper limb anatomy. Are they available on the platform? I am preparing for my 1st year exams.', 1, '2026-05-21 13:22:43', '2026-05-23 02:22:07'),
(2, 'Amit Singh', 'amit@example.com', 'Premium Plan Inquiry', 'I am interested in upgrading to the premium plan. Could you tell me what additional benefits I would get compared to the free tier?', 1, '2026-05-21 13:22:43', '2026-05-23 05:08:11'),
(3, 'Fatima Sheikh', 'fatima@example.com', 'Bug report on mobile', 'I noticed that on my Android phone the dashboard charts are not loading properly. Could you please look into this? Otherwise the platform is fantastic!', 1, '2026-05-21 13:22:43', '2026-05-25 10:57:10'),
(4, 'Rohan Joshi', 'rohan@example.com', 'Suggestion for new feature', 'It would be great if you could add a flashcard feature for quick revision. Many students in my class would find it very helpful.', 0, '2026-05-21 13:22:43', '2026-05-21 13:22:43'),
(5, 'Aditi Nair', 'aditi@example.com', 'Thank you!', 'I just wanted to say thank you for creating this platform. The answer writing guides helped me tremendously in my exams. Keep up the great work!', 0, '2026-05-21 13:22:43', '2026-05-21 13:22:43');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_05_21_101811_create_settings_table', 1),
(5, '2026_05_21_101812_create_hero_sections_table', 1),
(6, '2026_05_21_101813_create_features_table', 1),
(7, '2026_05_21_101813_create_services_table', 1),
(8, '2026_05_21_101814_create_testimonials_table', 1),
(9, '2026_05_21_101815_create_faqs_table', 1),
(10, '2026_05_21_101816_create_pages_table', 1),
(11, '2026_05_21_101817_create_media_table', 1),
(12, '2026_05_21_102252_create_permission_tables', 1),
(13, '2026_05_21_102343_add_firebase_uid_to_users_table', 1),
(14, '2026_05_21_102934_create_banners_table', 1),
(15, '2026_05_21_102934_create_sliders_table', 1),
(16, '2026_05_21_102935_create_messages_table', 1),
(17, '2026_05_21_104007_create_personal_access_tokens_table', 1),
(18, '2026_05_21_104337_create_notifications_table', 1),
(19, '2026_05_21_181931_add_year_to_services_table', 1),
(20, '2026_05_22_010000_create_menus_table', 2),
(21, '2026_05_22_010001_add_order_to_features_table', 2),
(22, '2026_05_23_071419_add_is_read_to_messages_table', 2),
(23, '2026_05_23_072327_add_status_to_features_table', 3),
(24, '2026_05_23_073026_add_status_to_faqs_table', 4),
(25, '2026_05_23_102433_create_activity_logs_table', 5),
(26, '2026_05_24_000000_create_page_sections_table', 6),
(27, '2026_05_24_000001_create_page_section_items_table', 6),
(28, '2026_05_25_110707_add_is_protected_to_pages_and_services_table', 7),
(29, '2026_05_25_175056_create_subjects_table', 8),
(30, '2026_05_25_175057_create_academic_years_table', 8),
(31, '2026_05_25_175059_create_semesters_table', 8),
(32, '2026_05_25_175101_create_topics_table', 8),
(33, '2026_05_25_175102_create_learning_materials_table', 8),
(34, '2026_05_25_175943_add_parent_id_to_topics_table', 9),
(35, '2026_05_26_063325_add_category_to_faqs_table', 10);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(2, 'App\\Models\\User', 2),
(2, 'App\\Models\\User', 3),
(2, 'App\\Models\\User', 4),
(2, 'App\\Models\\User', 5),
(2, 'App\\Models\\User', 6),
(2, 'App\\Models\\User', 7),
(2, 'App\\Models\\User', 8),
(2, 'App\\Models\\User', 9),
(2, 'App\\Models\\User', 10),
(1, 'App\\Models\\User', 11);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint UNSIGNED NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` text COLLATE utf8mb4_unicode_ci,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `is_protected` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `title`, `slug`, `content`, `meta_title`, `meta_description`, `status`, `is_protected`, `created_at`, `updated_at`) VALUES
(1, 'About Us', 'about-us', '<h2>About Physio Academy</h2><p>Physio Academy is a premier online platform dedicated to helping physiotherapy students excel in their academics. Founded by a team of experienced physiotherapy educators, our mission is to make quality study resources accessible to every BPT and MPT student.</p><p>We believe in structured learning, comprehensive coverage, and exam-focused preparation that builds both knowledge and confidence.</p>', 'About Physio Academy - Your Academic Guide', 'Learn about Physio Academy, the premier online platform for physiotherapy students.', 1, 1, '2026-05-21 13:22:42', '2026-05-26 01:23:17'),
(2, 'Contact Us', 'contact-us', '<h2>Get In Touch</h2><p>Have questions or feedback? We would love to hear from you! Reach out to us and our team will get back to you within 24 hours.</p><p>Email: contact@physioacademy.com</p><p>Phone: +91 98765 43210</p>', 'Contact Physio Academy', 'Contact the Physio Academy team for questions, support, or feedback.', 1, 1, '2026-05-21 13:22:42', '2026-05-26 01:23:17'),
(3, 'Privacy Policy', 'privacy-policy', '<h2>Privacy Policy</h2><p>Your privacy is important to us. This policy outlines how we collect, use, and protect your personal information when using Physio Academy.</p><p>We collect only the information necessary to provide our services and never share your data with third parties without your consent.</p>', 'Privacy Policy - Physio Academy', 'Read the Physio Academy privacy policy.', 1, 1, '2026-05-21 13:22:42', '2026-05-26 01:23:17'),
(4, 'Terms of Service', 'terms-of-service', '<h2>Terms of Service</h2><p>By using Physio Academy, you agree to these terms. Please read them carefully before using our platform.</p><p>All content on this platform is for educational purposes only and should not be used as a substitute for professional medical advice.</p>', 'Terms of Service - Physio Academy', 'Read the terms and conditions for using Physio Academy.', 1, 1, '2026-05-21 13:22:43', '2026-05-26 01:23:17'),
(5, 'Exam Aid', 'exam-aid', 'Comprehensive exam preparation resources for physiotherapy students.', 'Exam Aid - Physio Academy', 'Access mock papers, important questions, and study materials.', 1, 1, '2026-05-25 06:20:54', '2026-05-26 01:23:17');

-- --------------------------------------------------------

--
-- Table structure for table `page_sections`
--

CREATE TABLE `page_sections` (
  `id` bigint UNSIGNED NOT NULL,
  `page_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` json DEFAULT NULL,
  `order` int NOT NULL DEFAULT '0',
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `page_sections`
--

INSERT INTO `page_sections` (`id`, `page_id`, `name`, `slug`, `type`, `content`, `order`, `enabled`, `created_at`, `updated_at`) VALUES
(1, 1, 'Mission Section', 'mission-section', 'mission', '{\"body\": \"Physio Academy is a premier online platform dedicated to helping physiotherapy students excel in their academics. Founded by a team of experienced physiotherapy educators, our mission is to make quality study resources accessible to every BPT and MPT student. We believe in structured learning, comprehensive coverage, and exam-focused preparation that builds both knowledge and confidence.\", \"pills\": [\"Structured Learning\", \"Exam Guidance\", \"Topic Navigation\", \"Student Support\"], \"title\": \"About Physio Academy\", \"images\": {\"main\": \"https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=800&q=80\", \"thumb1\": \"https://images.unsplash.com/photo-1516549655169-df83a0774514?auto=format&fit=crop&w=400\", \"thumb2\": \"https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=400\", \"thumb3_text\": \"PT\"}, \"kicker\": \"Our Mission\"}', 1, 1, '2026-05-25 05:59:17', '2026-05-25 05:59:17'),
(4, 1, 'Our Vision', 'vision-section', 'vision', '{\"body\": \"To create a modern academic ecosystem for physiotherapy students that seamlessly combines structured learning, expert academic guidance, and competency-focused education in one accessible place.\", \"title\": \"Our Vision\", \"kicker\": \"Our Vision\"}', 4, 1, '2026-05-25 05:59:17', '2026-05-25 06:18:31'),
(5, 1, 'Closing Banner', 'closing-banner-section', 'closing', '{\"title\": \"Learn Smarter. Study with Clarity.\", \"kicker\": \"Final Step\", \"cta_url\": \"/topics-year\", \"cta_text\": \"Explore Topics\", \"cta_secondary_url\": \"/topics\", \"cta_secondary_text\": \"Ask a Doubt\"}', 5, 1, '2026-05-25 05:59:17', '2026-05-25 05:59:17'),
(6, 5, 'Exam Hero', 'exam-hero', 'exam_hero', '{\"stats\": {\"guides\": \"18\", \"papers\": \"42\", \"questions\": \"128\"}, \"title\": \"Prepare Smarter for Your Exams\", \"kicker\": \"Exam Aid Studio\", \"description\": \"Access mock papers, important questions, answer-writing support, and exam-focused learning resources in one organized place.\", \"quick_links\": [{\"url\": \"#exam-resources\", \"label\": \"Previous Year Papers\", \"icon_num\": \"01\"}, {\"url\": \"#exam-resources\", \"label\": \"Important Topics\", \"icon_num\": \"02\"}, {\"url\": \"#exam-resources\", \"label\": \"Viva Questions\", \"icon_num\": \"03\"}, {\"url\": \"#exam-resources\", \"label\": \"Answer Writing\", \"icon_num\": \"04\"}, {\"url\": \"#exam-resources\", \"label\": \"Mock Tests\", \"icon_num\": \"05\"}, {\"url\": \"#exam-resources\", \"label\": \"Video Tutorials\", \"icon_num\": \"06\"}], \"floating_cards\": [\"Mock test opens today\", \"Neurology viva trending\"], \"progress_items\": [{\"label\": \"Anatomy PYQs\", \"width\": \"82%\"}, {\"label\": \"Answer Writing\", \"width\": \"74%\"}, {\"label\": \"Viva Practice\", \"width\": \"68%\"}], \"primary_cta_url\": \"#exam-resources\", \"readiness_score\": \"88\", \"primary_cta_text\": \"Explore Resources\", \"secondary_cta_url\": \"#college-selector\", \"secondary_cta_text\": \"Select Semester\"}', 1, 1, '2026-05-25 06:20:54', '2026-05-25 06:20:54'),
(7, 5, 'Smart Filters', 'exam-filters', 'exam_filters', '{\"title\": \"Select Your Context\", \"eyebrow\": \"Smart Filters\", \"description\": \"Choose your academic context and the resource library adapts instantly.\"}', 2, 1, '2026-05-25 06:20:54', '2026-05-25 06:20:54'),
(8, 5, 'Resource Library', 'exam-resources', 'exam_resources', '{\"title\": \"Academic Question Bank\", \"eyebrow\": \"Resource Library\", \"description\": \"Dynamic library of clinical notes and examination frameworks based on your curriculum.\"}', 3, 1, '2026-05-25 06:20:54', '2026-05-25 06:20:54');

-- --------------------------------------------------------

--
-- Table structure for table `page_section_items`
--

CREATE TABLE `page_section_items` (
  `id` bigint UNSIGNED NOT NULL,
  `section_id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body` text COLLATE utf8mb4_unicode_ci,
  `meta` json DEFAULT NULL,
  `order` int NOT NULL DEFAULT '0',
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'manage settings', 'web', '2026-05-21 13:22:37', '2026-05-21 13:22:37'),
(2, 'manage users', 'web', '2026-05-21 13:22:37', '2026-05-21 13:22:37'),
(3, 'manage content', 'web', '2026-05-21 13:22:37', '2026-05-21 13:22:37'),
(4, 'manage pages', 'web', '2026-05-21 13:22:37', '2026-05-21 13:22:37'),
(5, 'manage media', 'web', '2026-05-21 13:22:37', '2026-05-21 13:22:37'),
(6, 'manage messages', 'web', '2026-05-21 13:22:37', '2026-05-21 13:22:37'),
(7, 'view dashboard', 'web', '2026-05-21 13:22:37', '2026-05-21 13:22:37'),
(8, 'view admin dashboard', 'web', '2026-05-21 13:22:37', '2026-05-21 13:22:37');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'web', '2026-05-21 13:22:37', '2026-05-21 13:22:37'),
(2, 'user', 'web', '2026-05-21 13:22:37', '2026-05-21 13:22:37');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(7, 2);

-- --------------------------------------------------------

--
-- Table structure for table `semesters`
--

CREATE TABLE `semesters` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `academic_year_id` bigint UNSIGNED NOT NULL,
  `order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `semesters`
--

INSERT INTO `semesters` (`id`, `name`, `academic_year_id`, `order`, `created_at`, `updated_at`) VALUES
(1, 'Semester 1', 1, 1, '2026-05-25 13:06:34', '2026-05-25 13:06:34'),
(2, 'Semester 2', 1, 2, '2026-05-25 13:06:34', '2026-05-25 13:06:34'),
(3, 'Semester 1', 2, 1, '2026-05-25 13:06:34', '2026-05-25 13:06:34'),
(4, 'Semester 2', 2, 2, '2026-05-25 13:06:34', '2026-05-25 13:06:34'),
(5, 'Semester 1', 3, 1, '2026-05-25 13:06:34', '2026-05-25 13:06:34'),
(6, 'Semester 2', 3, 2, '2026-05-25 13:06:34', '2026-05-25 13:06:34'),
(7, 'Semester 1', 4, 1, '2026-05-25 13:06:34', '2026-05-25 13:06:34'),
(8, 'Semester 2', 4, 2, '2026-05-25 13:06:34', '2026-05-25 13:06:34'),
(9, 'Semester 1', 5, 1, '2026-05-25 13:06:34', '2026-05-25 13:06:34'),
(10, 'Semester 2', 5, 2, '2026-05-25 13:06:34', '2026-05-25 13:06:34');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `year` int DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order` int NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `is_protected` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `title`, `description`, `year`, `subject`, `icon`, `order`, `status`, `is_protected`, `created_at`, `updated_at`) VALUES
(1, 'Topic Navigation', 'Browse the complete physiotherapy syllabus year-by-year, subject-by-subject. Find exactly what you need to study.', 1, 'Anatomy', 'bi bi-journal-bookmark', 1, 1, 0, '2026-05-21 13:22:42', '2026-05-23 04:57:57'),
(2, 'Question Bank', 'Thousands of exam questions organised by subject, year, and topic type. Practice with real exam patterns.', 2, 'Pathology', 'bi bi-question-circle', 2, 1, 0, '2026-05-21 13:22:42', '2026-05-21 13:22:42'),
(3, 'Answer Writing', 'Structured answer frameworks, model answers, and step-by-step writing guides for exam success.', 3, 'Orthopaedics', 'bi bi-pencil-square', 3, 1, 0, '2026-05-21 13:22:42', '2026-05-21 13:22:42'),
(4, 'Clinical Notes', 'Comprehensive clinical notes covering assessment, diagnosis, and treatment protocols.', 1, 'Physiology', 'bi bi-clipboard2-pulse', 4, 1, 0, '2026-05-21 13:22:42', '2026-05-21 13:22:42'),
(5, 'Video Lectures', 'Expert-recorded video lessons explaining complex topics in simple, visual formats.', 4, 'Sports PT', 'bi bi-play-circle', 5, 1, 0, '2026-05-21 13:22:42', '2026-05-21 13:22:42'),
(6, 'Doubt Resolution', 'Get your academic doubts cleared by qualified mentors within 24 hours.', 3, 'Neurology', 'bi bi-chat-dots', 6, 1, 0, '2026-05-21 13:22:42', '2026-05-21 13:22:42');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('2BSn7ko7fpIrxrTVgEPNj87Ppp6dRopaUfd7xKyl', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'ZXlKcGRpSTZJbVZPV25JNVowNHJaMll3U2pOR1VESnlPVGx5Wm5jOVBTSXNJblpoYkhWbElqb2lSRXRtZVhScVluUnJRMmxLY3pWV2RFWjFTMk42YzBOcEwzZzBNVzV1TWpOdE5VeFBURzlSYkN0YVZIVjZhM0l2TUVsS1lWZFpkMEp3VTFRclVGSkRibUZOZDJkR1Z5OHhOams1Tm1JMllVZHlRbVp6Y0ZoNlYyZDJkRkExVEZaV1FXVjZiWGN2UXpKVGVsaG1ha0ZuV25KaE1VVlRiVmhKTjA5aWVGaHlkVmx0VVc1TVoySndVSGxRWTA1dVpEaFZOamhFYlRSbllXdG5XbEpVUW01S00xQnBSSGgyZVdKa0t5OVdjV05ITWpSS1EyUjVjakJxYUhKQ1RFWmhUM2xFYkRSSGJGTTNTMFV5U0hKbmQzcFdWamhtUmxBMGRuUjZLM2RYVldSTUwxbHlNVnBpYkVacVpuZFhUMG92VldaNk0xaHVORzVrWlN0a1JYRTJjSEkxWXpGaFpHRTFaVGdyWVZwUVdXSkNiRTlRWkZwalNubGxTVTFLU0RsQ2QybG1lbFpTVTNWRFJYSnJSSGhZSzBsdlpWcHVORUZ2VUZwbmIxTXpZMVJhZDNGNFdXeHlXRk5CZDBNeE5tZE9OWEJoZHpRMVNXdHBUM1UzVlZoU2NIcERPV3BuV0ZjdlIzUjROMFZwY0dGYWRFa3phWEpWU3l0NFV5dDRSM1JKYW1GRVJsSTJXbFprYkdSdFoyWTNZVVJPTW5rdmFpOHpUWFZWVjNaQ01VczFXWEZVUW5WaldtczRiVEF3WXowaUxDSnRZV01pT2lJMk5EVmxOR1UyTkRFd1lUUmtaakk0TTJSaVpqSTBaVGsxT0RabE5EZzRNV1V4T1RkbU5HTmxabUl4Wm1GaU9EQXhNREZqWWpObE5tRTNPVFU1T0RVd0lpd2lkR0ZuSWpvaUluMD0=', 1779793314),
('gfQMCPWdstWe17DcltUkpyL6GSjHjoG0Lp4fBhST', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'ZXlKcGRpSTZJbkJRTUhkemEzcEhaRUpXZDNGc1VYbEJkRnBEUkZFOVBTSXNJblpoYkhWbElqb2lUR3RRVlRCellYVkpMMWxEUzA5NlkweDBTM056ZUhwNFF6QlZZbFJOWkZNclR6VlRSRUZ0SzBoelRFWnZkMFJKYTFGak4xcGphMk5OUkZKa1lUZ3phbmhWTTJoak9GaDBOVlYzVkRac0wybEpkbHB4TnpaV2IybzFUbTlDTm00cmRrbERSMWxpV2pGSFZtWlNNVmRKTDJaTFdHOUlObGhOZW1GaFpuWndSMUpPWkN0WlMwMUtTaXRDYURjMVRrUnRWVGxpYmpOalRVUk5lRlIxY0RWSU1EaGFlVlpsU1hkMmNFeEVOblprY1dGeVNWSlJhR2MzVnpkQlZqbHNabkpHUWpCRUsyOXpNMlJJUlhGREwzZzFNWHA2WlVocllWVTBXWGQ1TkhoclZIQndORWRhUmxwak4ydFdUeXRhTUdkSE1URXlTRkJTT1RWSFpVbFdPR2R2UzNSamVGaGtaWGhMVFdFclRGSjBaalF2YUd0c1RtZExTakZ0UWxCRU9HUmpNM2RVTjJ4cVFWQlVTVU13ZVdSV2JIRk1NQ3RVYVRGQmNURmxiRUppZG5JelF6ZzNUMnRqWlN0ek1HOUpaSEptWTJ0UmJHOU9UWEZ4WWtsTVZGVkNVbWhqZUU5RFZFZGliVU4xWkROWk9UZHNPVmR6VmpSSGVqQlVSMHBDTDBWRlRXdFBWelpKYlhZeVFsRnpSa016ZUdaSFl6QlZVVDA5SWl3aWJXRmpJam9pWWpObVlXSTJOemM0T0RBd01UUTNaVEZpWldZME1UbGtZemt3WW1NeU1UbGhZVFk0T1dZelltUTVPRE5qTkRsbE16SXlOakE0WXpKa1lURmhOelppWXlJc0luUmhaeUk2SWlKOQ==', 1779795549),
('msFrN6RQ0WVzBS4mMBqpacskafolkHrTqcQn2unk', 11, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'ZXlKcGRpSTZJa3c0V21sV09WTkhhMHh2VWtSek5taHhlV2hsTkZFOVBTSXNJblpoYkhWbElqb2laeXRoZVRVeldVZERNVmxOV1c0dk1YcDBZM3BtWjJScmNXaG9kRzVoZFdWRU9YVkNka3hXVDBGSFUzcENRVkZ6WlZGbk9FTjZjbGcxZUdGS09VUlJRbFkzV0U1TFVHOURWbVJYTVhnMUwyOWlRWGt5ZUhGck9IWkxiR1V6VjBaemNERTRUMHcwWjJwbVExcHFiV2N4V25STGRpczRSbmRaY1hoNlVrWk9OV1p0Vm1OQkx6QXdPRzVaTUZGNk9XVXJRell2YjFWb05uQmpWMVpJTDNWcmIzRnpOelJZV0V0WVdITm5NakZTV0hOMVJ6WmpkbkYwZUhaMVJUVTFWbEVyYjBaM1VYZE5TMVJsY2sxMGRGRkxNV2h5VW5GclNXRnFPR3QyZFZkSVoxRmtVV2g1THpoWlpYVmxXbVZYU21sbEwxaHhZbWxCVlU0clJWaE1TSFZ4UVVGV0x6Wk1ZbUZ1VVZOdmFqZE1hMk0xV2paMVVWTTVTMUJJTURkcmJ6VlBNM294Y25sVlMxSnRTbHBTTVhoalltWkZZMUYxVkRWTlpXeFpSMDVxWkVGVVkwUlVObFZEV1ZoeVFtbE5jMnN6UlhkS1lXSTRSVE5FTDNVeVdEZG9Ua2xUYmxZMGR6WXhXRm9yWmxKeU4wSmllVTlhS3pKR1NsRndObkpLY1VsU0lpd2liV0ZqSWpvaVpHSmhOV1ExWWpWaVpEVmlZVGszTXpnNU1qUXdZemcyWmpFNFpqSTRNRFF3WmpJNVptSTJNek01T0dSbFpEVmlZMlEwTURSbU9UVXlPRFEzTWpCaVlpSXNJblJoWnlJNklpSjk=', 1779779505),
('nRjMao7rzW4TlVnS8ZHS6GxSk1S8hYa6uQVr0Rfi', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'ZXlKcGRpSTZJbXgxWVhJek9VZG1ia2RwZVRSS1ZqUklVM1JCUkZFOVBTSXNJblpoYkhWbElqb2lXVVJ0UzFnclRrSmphSEJpYkdvemRHOUljSFJwZEhGMFZuRndjbE5CVjJKbU1WTmpOa001ZFhJclVYRTNibVIxVURGNFkxVnpObTVRVW5KS05XZDVZWGwxTlVRM05qbDFZV0ZyWjNCMVEwSjBRakpqWm5kcGVGQlNPVEUyVlRkS2IwbEpia1ZKZHpsSFVGcFJjekZIV0ZFMWRHWllORTFpU1ZsMFpHOVpZVEZsWlN0RVdpdGpZazlOU1RjMWRtNTBaM2xHZEVaRU1IUkZVelEyUW5Nek4zcEVSbUV5ZERaTVlsVk5OSHBtY0hwWWJVNTFRVlJXTkRRemRIWkRjU3RQT0cxMFMxQTRPR2xaVFM5alUxcEJhREZRU1M5WkwxVllOWHAwV2pOWlJYSXJXbXB6ZFd0a1EzUnhSMGwyWlhwa1kySXZlRzB4UkhWdWRGcFBOVE5oYjJONGNsRm9XRU5OTjNjMFJqaDJNQzlLYVU1S2REQXlUelF5UWpWM1pHbEdXRlEzUVZCblVYQXdUMUU5SWl3aWJXRmpJam9pTldZd05qY3dORE0yWldWaVkyUmtOakUwWlRObVptTTVZVEF4TW1Kak1HSTNaR015TURkaE16ZzBNVFU1TmpBM1ltUXlNMkppWWpNNU1HRXhaak15T1NJc0luUmhaeUk2SWlKOQ==', 1779795902),
('PKQQQPyNLNGtqsy2WQKrpmdcVxJBDv73C0w5euG3', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'ZXlKcGRpSTZJbTFRYTNkeGIyeEJjM0lyTUN0dlkyRnBiRGxMT0VFOVBTSXNJblpoYkhWbElqb2lUR00yUlhObk5FOTNaV05oVjFvNU5sUlhjVE4xUlU4dmNGaElOV2g1YjFGWFRrczRLMVJGT1VsSVdFUmhTRUZRU1V4T2JHMHZVVTlRY2pNeGVtUmhZa2QxVmpKa1JqUnNjbEF5VGpWMGRWaEdUM1ZQZVdaT1duVnNialpHTlRkeFMzTnhaSE16Y0dSbVZqbFVaV1U1YjNNd1YxWnJNVzkyU1ZKeWJGVnVZV2xEWTFsaFJuVTBTV0UyV0daelpGTkdjWFpGYkVaSk56TXdOakIxVFRKbmJISkRWVWxuYjI1amNYWlBkWE5SYUhSbFVqSXJXVkJQZFVoeFpIbGFZbVZES3paclMzSmxTWEV4ZGxwQ1lWY3laemRxYzIxTlZrMUJjMms0U1VSemNHOVZaMjFRZGtSSEwxVllVSFpHYjBwNFdsa3lSMWhKYlVoVGNGZzFMMU5WVDJ0clEwRk9Va05OTURWcFJGaEpRMDR5SzBSS1YwNHdielJOZFRkM2NUZE1VREYxWTJScE5uSndaekpLVGs1VE4xSndWMWxwUkhwNWVrNW5ORmxFUjJsU1R6QkhZVVJKWjJ4dE9UbEVlRzVQUVdoSVpIbERlVVl3WnpWYWRXaFhTV1E0TTNKT09IcEZjRU5yVDNNMmEyeG1PV2xxZHpOR1YwUnpRMjl0VEdNMFRUTkRPVTFVU0dKamFqWlBiMnROYWtSYVJ6VkNTVkJSV1had1RsSTRSbEpQZDBjelpVSXpSbk5oYXowaUxDSnRZV01pT2lJeE0yTmhNelk0WVRobFptUTVaRGMzT0RGbU1XTTRNR0kzT0RVNE1qazVPV0l5TlRsbU1qZ3pNV1F4T1RJeU1tVTVZV1JtWW1Rd1pEQTFOMkl3TVRWbElpd2lkR0ZuSWpvaUluMD0=', 1779783968);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint UNSIGNED NOT NULL,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `group` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `group`, `label`, `type`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'Physio Academy', 'general', 'Site name', 'text', '2026-05-21 13:22:42', '2026-05-26 02:01:15'),
(2, 'site_description', 'Your Academic Guide for Physiotherapy', 'general', 'Site description', 'text', '2026-05-21 13:22:42', '2026-05-23 01:45:12'),
(3, 'site_email', 'contact@physioacademy.com', 'general', 'Site Email', 'text', '2026-05-21 13:22:42', '2026-05-21 13:22:42'),
(4, 'site_phone', '+91 98765 43210', 'general', 'Site Phone', 'text', '2026-05-21 13:22:42', '2026-05-21 13:22:42'),
(5, 'primary_color', '#2563eb', 'appearance', 'Primary color', 'text', '2026-05-21 13:22:42', '2026-05-23 01:45:12'),
(6, 'secondary_color', '#38bdf8', 'appearance', 'Secondary color', 'text', '2026-05-21 13:22:42', '2026-05-23 01:45:12'),
(7, 'hero_badge', 'New Curriculum 2024 — Fully Updated', 'hero', 'Hero Badge', 'text', '2026-05-21 13:22:42', '2026-05-21 13:22:42'),
(8, 'hero_title', 'Your Academic Guide for Physiotherapy', 'hero', 'Hero Title', 'text', '2026-05-21 13:22:42', '2026-05-21 13:22:42'),
(9, 'hero_subtitle', 'Navigate your syllabus, understand important topics, improve answer writing, and get academic support — all in one place.', 'hero', 'Hero Subtitle', 'text', '2026-05-21 13:22:42', '2026-05-21 13:22:42'),
(10, 'footer_text', 'Empowering future physiotherapists with academic excellence and clinical insights.', 'general', 'Footer text', 'text', '2026-05-21 13:22:42', '2026-05-23 01:45:12'),
(11, 'facebook_url', '#', 'social', 'Facebook URL', 'text', '2026-05-21 13:22:42', '2026-05-21 13:22:42'),
(12, 'instagram_url', '#', 'social', 'Instagram URL', 'text', '2026-05-21 13:22:42', '2026-05-21 13:22:42'),
(13, 'linkedin_url', '#', 'social', 'LinkedIn URL', 'text', '2026-05-21 13:22:42', '2026-05-21 13:22:42'),
(14, 'contact_email', NULL, 'general', 'Contact email', 'text', '2026-05-23 01:45:12', '2026-05-23 01:45:12'),
(15, 'meta_keywords', NULL, 'general', 'Meta keywords', 'text', '2026-05-23 01:45:12', '2026-05-23 05:10:13'),
(16, 'about_title', 'The Study Companion You Need', 'general', 'About title', 'text', '2026-05-23 05:10:08', '2026-05-23 05:10:08'),
(17, 'about_badge', 'Why Choose Us', 'general', 'About badge', 'text', '2026-05-23 05:10:08', '2026-05-23 05:10:08'),
(18, 'about_content', NULL, 'general', 'About content', 'text', '2026-05-23 05:10:08', '2026-05-23 05:10:08'),
(19, 'copyright_text', '© 2026 Physio Academy. All rights reserved.', 'general', 'Copyright text', 'text', '2026-05-23 05:10:08', '2026-05-23 05:20:12'),
(20, 'contact_address', NULL, 'general', 'Contact address', 'text', '2026-05-23 05:10:08', '2026-05-23 05:10:08'),
(21, 'maintenance_mode', '0', 'general', 'Maintenance mode', 'text', '2026-05-23 05:18:25', '2026-05-23 05:18:54'),
(22, 'maintenance_message', NULL, 'general', 'Maintenance message', 'text', '2026-05-23 05:18:25', '2026-05-23 05:18:25'),
(23, 'enable_content_protection', '1', 'protection', 'Enable content protection', 'text', '2026-05-25 10:58:09', '2026-05-26 01:23:17'),
(24, 'protection_disable_right_click', '1', 'protection', 'Protection disable right click', 'text', '2026-05-25 10:58:09', '2026-05-26 01:23:17'),
(25, 'protection_disable_devtools', '1', 'protection', 'Protection disable devtools', 'text', '2026-05-25 10:58:09', '2026-05-26 01:23:17'),
(26, 'protection_disable_copy', '1', 'protection', 'Protection disable copy', 'text', '2026-05-25 10:58:09', '2026-05-26 01:23:17'),
(27, 'protection_disable_drag', '1', 'protection', 'Protection disable drag', 'text', '2026-05-25 10:58:09', '2026-05-26 01:23:17'),
(28, 'protection_enable_watermark', '1', 'protection', 'Protection enable watermark', 'text', '2026-05-25 10:58:09', '2026-05-26 01:23:17');

-- --------------------------------------------------------

--
-- Table structure for table `sliders`
--

CREATE TABLE `sliders` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subtitle` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `button_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `button_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sliders`
--

INSERT INTO `sliders` (`id`, `title`, `subtitle`, `image_path`, `button_text`, `button_url`, `status`, `order`, `created_at`, `updated_at`) VALUES
(1, 'Master Physiotherapy Today', 'Comprehensive study materials from 1st to final year', 'sliders/hero-1.jpg', 'Start Learning', '/register', 1, 0, '2026-05-21 13:22:43', '2026-05-21 13:22:43'),
(2, 'Exam Ready in 30 Days', 'Structured study plans designed by topper physiotherapists', 'sliders/hero-2.jpg', 'View Plans', '#', 1, 0, '2026-05-21 13:22:43', '2026-05-21 13:22:43');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `name`, `slug`, `description`, `icon`, `image`, `status`, `order`, `created_at`, `updated_at`) VALUES
(1, 'Anatomy', 'anatomy', 'Detailed study of human body structures, bones, muscles, and nerves.', '🦴', NULL, 1, 1, '2026-05-25 13:06:34', '2026-05-25 13:06:34'),
(2, 'Physiology', 'physiology', 'Study of how the human body and its systems function.', '🫀', NULL, 1, 2, '2026-05-25 13:06:34', '2026-05-25 13:06:34'),
(3, 'Biochemistry', 'biochemistry', 'Chemical processes within and relating to living organisms.', '🧪', NULL, 1, 3, '2026-05-25 13:06:34', '2026-05-25 13:06:34'),
(4, 'Kinesiology', 'kinesiology', 'Scientific study of human body movement.', '🏃', NULL, 1, 4, '2026-05-25 13:06:34', '2026-05-25 13:06:34'),
(5, 'Pathology', 'pathology', 'Study of the causes and effects of diseases or injuries.', '🔬', NULL, 1, 5, '2026-05-25 13:06:34', '2026-05-25 13:06:34'),
(6, 'Pharmacology', 'pharmacology', 'Study of drugs and their effect on the human body.', '💊', NULL, 1, 6, '2026-05-25 13:06:34', '2026-05-25 13:06:34'),
(7, 'Exercise Therapy', 'exercise-therapy', 'Techniques for therapeutic exercises and body rehabilitation.', '🧘', NULL, 1, 7, '2026-05-25 13:06:34', '2026-05-25 13:06:34'),
(8, 'Electrotherapy', 'electrotherapy', 'Use of electrical energy as medical treatment.', '⚡', NULL, 1, 8, '2026-05-25 13:06:34', '2026-05-25 13:06:34'),
(9, 'Musculoskeletal Physiotherapy', 'musculoskeletal-physiotherapy', 'Specialized treatment for muscle and bone injuries.', '💪', NULL, 1, 9, '2026-05-25 13:06:34', '2026-05-25 13:06:34'),
(10, 'Neurological Physiotherapy', 'neurological-physiotherapy', 'Rehabilitation for patients with neurological disorders.', '🧠', NULL, 1, 10, '2026-05-25 13:06:34', '2026-05-25 13:06:34'),
(11, 'Artificial Intelligence', 'artificial-intelligence', 'Study how machines can think and learn like humans.\r\nIncludes machine learning, data models, and automation.', '🧠', NULL, 1, 11, '2026-05-26 02:13:26', '2026-05-26 02:13:44');

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` bigint UNSIGNED NOT NULL,
  `client_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_designation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `rating` int NOT NULL DEFAULT '5',
  `client_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`id`, `client_name`, `client_designation`, `content`, `rating`, `client_image`, `status`, `order`, `created_at`, `updated_at`) VALUES
(1, 'Dr. Ananya Verma', 'BPT Graduate, AIIMS Delhi', 'Physio Academy was my go-to resource during university exams. The structured answer guides helped me score consistently well.', 5, NULL, 1, 0, '2026-05-21 13:22:42', '2026-05-21 13:22:42'),
(2, 'Rahul Mehta', 'Final Year BPT Student', 'The question bank is incredibly well-organized. It saved me hours of preparation time and I cleared my finals with flying colors.', 5, NULL, 1, 0, '2026-05-21 13:22:42', '2026-05-21 13:22:42'),
(3, 'Dr. Sneha Patil', 'MPT Orthopaedics', 'As a postgrad student, I still refer to Physio Academy for clinical notes. The content quality is outstanding and regularly updated.', 4, NULL, 1, 0, '2026-05-21 13:22:42', '2026-05-21 13:22:42'),
(4, 'Karthik Reddy', 'BPT, 3rd Year Student', 'The topic navigation feature is brilliant. I can easily find what I need to study for any subject or exam.', 5, NULL, 1, 0, '2026-05-21 13:22:42', '2026-05-21 13:22:42');

-- --------------------------------------------------------

--
-- Table structure for table `topics`
--

CREATE TABLE `topics` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `subject_id` bigint UNSIGNED NOT NULL,
  `academic_year_id` bigint UNSIGNED NOT NULL,
  `semester_id` bigint UNSIGNED DEFAULT NULL,
  `parent_id` bigint UNSIGNED DEFAULT NULL,
  `module_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `order` int NOT NULL DEFAULT '0',
  `is_protected` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `topics`
--

INSERT INTO `topics` (`id`, `title`, `slug`, `description`, `subject_id`, `academic_year_id`, `semester_id`, `parent_id`, `module_number`, `status`, `order`, `is_protected`, `created_at`, `updated_at`) VALUES
(1, 'Human Skeleton Overview', 'human-skeleton-overview-0', 'An in-depth look at the 206 bones of the human body, their structure, and classification.', 1, 1, 1, NULL, 'Unit I: Introduction', 1, 1, 1, '2026-05-25 13:06:34', '2026-05-26 01:23:17'),
(2, 'Brachial Plexus', 'brachial-plexus-1', 'Formation, branches, and clinical importance of the Brachial Plexus. Essential for neuro-assessment.', 1, 1, 1, NULL, 'Unit III: Upper Limb', 1, 2, 1, '2026-05-25 13:06:34', '2026-05-26 01:23:17'),
(3, 'Cardiovascular System', 'cardiovascular-system-2', 'Understanding heart sounds, cardiac cycle, and blood pressure regulation mechanism.', 2, 1, 1, NULL, 'Unit IV', 1, 3, 1, '2026-05-25 13:06:34', '2026-05-26 01:23:17'),
(4, 'Gait Cycle Analysis', 'gait-cycle-analysis-3', 'Detailed analysis of human walking patterns including stance phase and swing phase.', 4, 2, 3, NULL, 'Unit V: Biomechanics', 1, 4, 1, '2026-05-25 13:06:34', '2026-05-26 01:23:17'),
(5, 'ACL Tear & Rehabilitation', 'acl-tear-rehabilitation-4', 'Comprehensive protocol for Anterior Cruciate Ligament (ACL) injury assessment and post-surgical rehab.', 9, 3, 5, NULL, 'Unit II: Knee Complex', 1, 5, 1, '2026-05-25 13:06:34', '2026-05-26 01:23:17'),
(6, 'Stroke Management (Hemiplegia)', 'stroke-management-hemiplegia-5', 'Rehabilitation strategies for stroke patients focusing on neuroplasticity and functional recovery.', 10, 4, 7, NULL, 'Unit I', 1, 6, 1, '2026-05-25 13:06:34', '2026-05-26 01:23:17'),
(7, 'Axial Skeleton', 'axial-skeleton', 'Includes the skull, vertebral column, and rib cage.', 1, 1, 1, 1, 'Unit I: Introduction', 1, 1, 1, '2026-05-25 13:06:34', '2026-05-26 01:23:17'),
(8, 'Appendicular Skeleton', 'appendicular-skeleton', 'Includes the limbs and girdles.', 1, 1, 1, 1, 'Unit I: Introduction', 1, 2, 1, '2026-05-25 13:06:34', '2026-05-26 01:23:17');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `firebase_uid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `firebase_uid`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'admin@example.com', NULL, '2026-05-21 13:22:38', '$2y$12$jH8cisF.gSYTLjf.7KYOQ.Fnry3o6obQE.TN/XnHWQ6YrloeNP6JK', NULL, '2026-05-21 13:22:38', '2026-05-21 13:22:38'),
(2, 'John Student', 'user@example.com', NULL, '2026-05-21 13:22:38', '$2y$12$wyOgImKi8Nb.2svWYnMCDOHf79PmsLWfla9T9RWF3Mq1twQbDsK62', NULL, '2026-05-21 13:22:38', '2026-05-21 13:22:38'),
(3, 'Sarah Wilson', 'sarah@example.com', NULL, '2026-05-21 13:22:38', '$2y$12$8uUe9ilru8Y5.Uqq/aq86uaCLjxG3N/NReEGDLxypp2KdbXX4cBtG', NULL, '2026-05-21 13:22:38', '2026-05-21 13:22:38'),
(4, 'Michael Chen', 'michael@example.com', NULL, '2026-05-21 13:22:39', '$2y$12$yhEPVARaXN9ls5vLbaqlOOdbmUElz7m1Ht/zwwBWN5Br9wplG0th.', NULL, '2026-05-21 13:22:39', '2026-05-21 13:22:39'),
(5, 'Priya Sharma', 'priya@example.com', NULL, '2026-05-21 13:22:39', '$2y$12$EzGywB4kRYr24cWIiHumnuxu6L27AsW3PFAaMt/Izc93e7JYe/Pla', NULL, '2026-05-21 13:22:39', '2026-05-21 13:22:39'),
(6, 'David Taylor', 'david@example.com', NULL, '2026-05-21 13:22:40', '$2y$12$gRft8btYlemo9U9wWhH/i.VEXi0.EOPLx0X9HjGZe6SbpOZy.Pjyq', NULL, '2026-05-21 13:22:40', '2026-05-21 13:22:40'),
(7, 'Emily Rodriguez', 'emily@example.com', NULL, '2026-05-21 13:22:40', '$2y$12$2w.8ft6I1XLvW0OHoJCHEuEcyOGoIf16Qzqf6uwOy.oMS/Jmg2X4O', NULL, '2026-05-21 13:22:40', '2026-05-21 13:22:40'),
(8, 'James Patel', 'james@example.com', NULL, '2026-05-21 13:22:41', '$2y$12$0xBs.ocoF5vkigCrV1tND.s8ttLnoM.ybUZp9mkM467rwyf6Ja41e', NULL, '2026-05-21 13:22:41', '2026-05-21 13:22:41'),
(9, 'Ayesha Khan', 'ayesha@example.com', NULL, '2026-05-21 13:22:41', '$2y$12$nfMnsyePIjs4pjglACwfAeRTk.mnwFqV0YvWLgZqaVDONo4vln1ZW', NULL, '2026-05-21 13:22:41', '2026-05-21 13:22:41'),
(10, 'Robert Johnson', 'robert@example.com', NULL, '2026-05-21 13:22:42', '$2y$12$2MciQvhtSI3khhmU.dLTgu/kIswFtnp8r5NGVaK44dtwLHKHYzdI.', NULL, '2026-05-21 13:22:42', '2026-05-21 13:22:42'),
(11, 'Hammad Khan', 'hammad@gmail.com', NULL, NULL, '$2y$12$NXQnqBqgjrJwNZbmvyf6jeJF/NPIjeqUt/w9iaSj/R9h39yfUJ1Pu', NULL, '2026-05-26 02:11:38', '2026-05-26 02:11:38');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `academic_years`
--
ALTER TABLE `academic_years`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `academic_years_slug_unique` (`slug`);

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activity_logs_user_id_action_module_index` (`user_id`,`action`,`module`);

--
-- Indexes for table `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `faqs`
--
ALTER TABLE `faqs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `features`
--
ALTER TABLE `features`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hero_sections`
--
ALTER TABLE `hero_sections`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `learning_materials`
--
ALTER TABLE `learning_materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `learning_materials_topic_id_foreign` (`topic_id`);

--
-- Indexes for table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `menus_location_unique` (`location`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `menu_items_menu_id_foreign` (`menu_id`),
  ADD KEY `menu_items_parent_id_foreign` (`parent_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pages_slug_unique` (`slug`);

--
-- Indexes for table `page_sections`
--
ALTER TABLE `page_sections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `page_sections_page_id_foreign` (`page_id`);

--
-- Indexes for table `page_section_items`
--
ALTER TABLE `page_section_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `page_section_items_section_id_foreign` (`section_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `semesters`
--
ALTER TABLE `semesters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `semesters_academic_year_id_foreign` (`academic_year_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `settings_key_unique` (`key`);

--
-- Indexes for table `sliders`
--
ALTER TABLE `sliders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subjects_slug_unique` (`slug`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `topics`
--
ALTER TABLE `topics`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `topics_slug_unique` (`slug`),
  ADD KEY `topics_subject_id_foreign` (`subject_id`),
  ADD KEY `topics_academic_year_id_foreign` (`academic_year_id`),
  ADD KEY `topics_semester_id_foreign` (`semester_id`),
  ADD KEY `topics_parent_id_foreign` (`parent_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_firebase_uid_unique` (`firebase_uid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `academic_years`
--
ALTER TABLE `academic_years`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `banners`
--
ALTER TABLE `banners`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `faqs`
--
ALTER TABLE `faqs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `features`
--
ALTER TABLE `features`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `hero_sections`
--
ALTER TABLE `hero_sections`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `learning_materials`
--
ALTER TABLE `learning_materials`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `media`
--
ALTER TABLE `media`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `menus`
--
ALTER TABLE `menus`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `page_sections`
--
ALTER TABLE `page_sections`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `page_section_items`
--
ALTER TABLE `page_section_items`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `semesters`
--
ALTER TABLE `semesters`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `sliders`
--
ALTER TABLE `sliders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `topics`
--
ALTER TABLE `topics`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `learning_materials`
--
ALTER TABLE `learning_materials`
  ADD CONSTRAINT `learning_materials_topic_id_foreign` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD CONSTRAINT `menu_items_menu_id_foreign` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `menu_items_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `page_sections`
--
ALTER TABLE `page_sections`
  ADD CONSTRAINT `page_sections_page_id_foreign` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `page_section_items`
--
ALTER TABLE `page_section_items`
  ADD CONSTRAINT `page_section_items_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `page_sections` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `semesters`
--
ALTER TABLE `semesters`
  ADD CONSTRAINT `semesters_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `topics`
--
ALTER TABLE `topics`
  ADD CONSTRAINT `topics_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `topics_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `topics` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `topics_semester_id_foreign` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `topics_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
