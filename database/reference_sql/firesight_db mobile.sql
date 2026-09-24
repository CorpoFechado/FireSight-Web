-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 22, 2026 at 05:42 AM
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
-- Database: `firesight_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcement`
--

CREATE TABLE `announcement` (
  `announcement_id` int(11) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `announcement_type` enum('emergency','general') DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcement`
--

INSERT INTO `announcement` (`announcement_id`, `created_by`, `title`, `content`, `announcement_type`, `created_at`) VALUES
(1, 1, 'Dry Season Fire Safety Advisory', 'The Bureau of Fire Protection - Lian reminds all residents and barangay officials to strictly observe fire prevention measures during the dry season (March-May). Open burning of trash, dried leaves, and agricultural waste is strictly prohibited.', 'general', '2026-07-15 08:00:00'),
(2, 1, 'Mandatory Fire Safety Inspection - All Establishments', 'All commercial establishments in Lian are required to undergo annual fire safety inspection. Schedule your inspection with the BFP Lian station. Operating without a valid Fire Safety Inspection Certificate (FSIC) is penalized under RA 9514.', 'general', '2026-07-11 10:00:00'),
(3, 1, 'Fire Drill Scheduled - Barangay Poblacion', 'A mandatory fire drill will be conducted at Barangay Poblacion covered courts on July 20, 2026 at 9:00 AM. All households are encouraged to participate. Facilitated by BFP Lian in coordination with the MDRRMO.', 'general', '2026-07-08 09:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `auth_token`
--

CREATE TABLE `auth_token` (
  `token_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `device_info` varchar(255) DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `auth_token`
--

INSERT INTO `auth_token` (`token_id`, `user_id`, `token`, `device_info`, `expires_at`, `created_at`) VALUES
(1, 9, '8fa8fb88a4187ffc11d299b224396829515ba1ea80c1628a0a838a21efead5dc', 'okhttp/4.12.0', '2026-10-16 15:52:04', '2026-07-18 15:52:04'),
(3, 10, '737d412107622c7d01f10cac07f20ea9a3f5ac90faf6fb6e50a8c6d364760e93', 'okhttp/4.12.0', '2026-10-18 11:10:44', '2026-07-20 11:10:44'),
(72, 2, '0b7c5b7e880aa46890979bd5cab3bd368cac230513d3045a666dd67a705f8c39', 'okhttp/4.12.0', '2026-12-14 07:31:01', '2026-09-15 07:31:01'),
(93, 2, '2247a47b8232e735db8547bf6a08265732145c2b72627ac72faed8dc910f15f7', 'okhttp/4.12.0', '2026-12-20 08:01:58', '2026-09-21 08:01:58'),
(134, 10, 'e684b9227134294f6b349d88613ae0dbfe1d34c5594390f7f35b5a1f13ae280c', 'okhttp/4.12.0', '2026-12-21 11:36:27', '2026-09-22 11:36:27');

-- --------------------------------------------------------

--
-- Table structure for table `barangay`
--

CREATE TABLE `barangay` (
  `barangay_id` int(11) NOT NULL,
  `barangay_name` varchar(100) DEFAULT NULL,
  `centroid_lat` decimal(10,8) DEFAULT NULL,
  `centroid_lng` decimal(11,8) DEFAULT NULL,
  `boundary_geojson` longtext DEFAULT NULL COMMENT 'GeoJSON polygon for risk map overlay',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barangay`
--

INSERT INTO `barangay` (`barangay_id`, `barangay_name`, `centroid_lat`, `centroid_lng`, `boundary_geojson`, `created_at`) VALUES
(1, 'Barangay 1 (Poblacion)', 14.03920000, 120.63890000, NULL, '2026-07-18 02:38:41'),
(2, 'Barangay 2 (Poblacion)', 14.04050000, 120.64010000, NULL, '2026-07-18 02:38:41'),
(3, 'Barangay 3 (Poblacion)', 14.03780000, 120.64100000, NULL, '2026-07-18 02:38:41'),
(4, 'Barangay 4 (Poblacion)', 14.03650000, 120.63950000, NULL, '2026-07-18 02:38:41'),
(5, 'Barangay 5 (Poblacion)', 14.04100000, 120.63750000, NULL, '2026-07-18 02:38:41'),
(6, 'Bagong Pook', 14.04520000, 120.63200000, NULL, '2026-07-18 02:38:41'),
(7, 'Balibago', 14.03000000, 120.64500000, NULL, '2026-07-18 02:38:41'),
(8, 'Binubusan', 14.05100000, 120.62800000, NULL, '2026-07-18 02:38:41'),
(9, 'Bungahan', 14.02500000, 120.65000000, NULL, '2026-07-18 02:38:41'),
(10, 'Cumba', 14.04800000, 120.64400000, NULL, '2026-07-18 02:38:41'),
(11, 'Humayingan', 14.02100000, 120.63600000, NULL, '2026-07-18 02:38:41'),
(12, 'Kapito', 14.05600000, 120.63500000, NULL, '2026-07-18 02:38:41'),
(13, 'Lumaniag', 14.03300000, 120.62800000, NULL, '2026-07-18 02:38:41'),
(14, 'Luyahan', 14.01800000, 120.64200000, NULL, '2026-07-18 02:38:41'),
(15, 'Malaruhatan', 14.04300000, 120.64700000, NULL, '2026-07-18 02:38:41'),
(16, 'Matabungkay', 14.01600000, 120.61800000, NULL, '2026-07-18 02:38:41'),
(17, 'Prenza', 14.03500000, 120.65200000, NULL, '2026-07-18 02:38:41'),
(18, 'Puting Kahoy', 14.02900000, 120.62100000, NULL, '2026-07-18 02:38:41'),
(19, 'San Diego', 14.02300000, 120.62500000, NULL, '2026-07-18 02:38:41');

-- --------------------------------------------------------

--
-- Table structure for table `barangay_contact`
--

CREATE TABLE `barangay_contact` (
  `contact_id` int(11) NOT NULL,
  `barangay_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `role` varchar(50) NOT NULL,
  `phone_number` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barangay_contact`
--

INSERT INTO `barangay_contact` (`contact_id`, `barangay_id`, `name`, `role`, `phone_number`) VALUES
(1, 1, 'Hon. Captain Barangay 1 (Poblacion)', 'Barangay Captain', '09941614944'),
(2, 1, 'Kagawad Barangay 1 (Poblacion)', 'Barangay Councilor', '09804957679'),
(3, 2, 'Hon. Captain Barangay 2 (Poblacion)', 'Barangay Captain', '09653057935'),
(4, 2, 'Kagawad Barangay 2 (Poblacion)', 'Barangay Councilor', '09452624164'),
(5, 3, 'Hon. Captain Barangay 3 (Poblacion)', 'Barangay Captain', '09563768225'),
(6, 3, 'Kagawad Barangay 3 (Poblacion)', 'Barangay Councilor', '09222164528'),
(7, 4, 'Hon. Captain Barangay 4 (Poblacion)', 'Barangay Captain', '09227847309'),
(8, 4, 'Kagawad Barangay 4 (Poblacion)', 'Barangay Councilor', '09636981392'),
(9, 5, 'Hon. Captain Barangay 5 (Poblacion)', 'Barangay Captain', '09469597425'),
(10, 5, 'Kagawad Barangay 5 (Poblacion)', 'Barangay Councilor', '09441081504'),
(11, 6, 'Hon. Captain Bagong Pook', 'Barangay Captain', '09162314220'),
(12, 6, 'Kagawad Bagong Pook', 'Barangay Councilor', '09993712332'),
(13, 7, 'Hon. Captain Balibago', 'Barangay Captain', '09640012116'),
(14, 7, 'Kagawad Balibago', 'Barangay Councilor', '09687303418'),
(15, 8, 'Hon. Captain Binubusan', 'Barangay Captain', '09915598512'),
(16, 8, 'Kagawad Binubusan', 'Barangay Councilor', '09811341929'),
(17, 9, 'Hon. Captain Bungahan', 'Barangay Captain', '09179222521'),
(18, 9, 'Kagawad Bungahan', 'Barangay Councilor', '09786350508'),
(19, 10, 'Hon. Captain Cumba', 'Barangay Captain', '09840584298'),
(20, 10, 'Kagawad Cumba', 'Barangay Councilor', '09910533529'),
(21, 11, 'Hon. Captain Humayingan', 'Barangay Captain', '09389768922'),
(22, 11, 'Kagawad Humayingan', 'Barangay Councilor', '09100592007'),
(23, 12, 'Hon. Captain Kapito', 'Barangay Captain', '09682209737'),
(24, 12, 'Kagawad Kapito', 'Barangay Councilor', '09608125782'),
(25, 13, 'Hon. Captain Lumaniag', 'Barangay Captain', '09363704389'),
(26, 13, 'Kagawad Lumaniag', 'Barangay Councilor', '09352577395'),
(27, 14, 'Hon. Captain Luyahan', 'Barangay Captain', '09162205713'),
(28, 14, 'Kagawad Luyahan', 'Barangay Councilor', '09675147340'),
(29, 15, 'Hon. Captain Malaruhatan', 'Barangay Captain', '09620645876'),
(30, 15, 'Kagawad Malaruhatan', 'Barangay Councilor', '09903434467'),
(31, 16, 'Hon. Captain Matabungkay', 'Barangay Captain', '09210531004'),
(32, 16, 'Kagawad Matabungkay', 'Barangay Councilor', '09779616772'),
(33, 17, 'Hon. Captain Prenza', 'Barangay Captain', '09903441220'),
(34, 17, 'Kagawad Prenza', 'Barangay Councilor', '09932711263'),
(35, 18, 'Hon. Captain Puting Kahoy', 'Barangay Captain', '09585961787'),
(36, 18, 'Kagawad Puting Kahoy', 'Barangay Councilor', '09626279540'),
(37, 19, 'Hon. Captain San Diego', 'Barangay Captain', '09641251768'),
(38, 19, 'Kagawad San Diego', 'Barangay Councilor', '09801778301');

-- --------------------------------------------------------

--
-- Table structure for table `bfp_personnel_details`
--

CREATE TABLE `bfp_personnel_details` (
  `details_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `rank` varchar(50) DEFAULT NULL,
  `station_assigned` varchar(100) DEFAULT NULL,
  `employee_number` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bfp_personnel_details`
--

INSERT INTO `bfp_personnel_details` (`details_id`, `user_id`, `rank`, `station_assigned`, `employee_number`) VALUES
(1, 2, 'Fire Officer II', 'BFP Lian Fire Station', 'BFP-LIAN-2019-014'),
(2, 3, 'Senior Fire Officer I', 'BFP Lian Fire Station', 'BFP-LIAN-2016-007');

-- --------------------------------------------------------

--
-- Table structure for table `community_report`
--

CREATE TABLE `community_report` (
  `report_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `reporter_name` varchar(100) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `report_image` varchar(255) DEFAULT NULL COMMENT 'Relative path/URL to captured photo',
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `location_accuracy_m` int(11) DEFAULT NULL,
  `device_latitude` decimal(10,8) DEFAULT NULL,
  `device_longitude` decimal(11,8) DEFAULT NULL,
  `barangay_id` int(11) DEFAULT NULL,
  `status` enum('pending','accepted','dispatched','resolved','invalid') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `community_report`
--

INSERT INTO `community_report` (`report_id`, `user_id`, `reporter_name`, `contact_number`, `description`, `report_image`, `latitude`, `longitude`, `location_accuracy_m`, `device_latitude`, `device_longitude`, `barangay_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 4, 'Juan Dela Cruz', '09201234567', 'Electrical fire at a residential house, resolved by responding unit within 12 minutes.', 'uploads/reports/report_1.jpg', 14.03920000, 120.63890000, 8, NULL, NULL, 1, 'resolved', '2026-07-14 09:32:00', '2026-07-18 02:38:41'),
(2, 4, 'Juan Dela Cruz', '09201234567', 'Grass fire near a residential area, currently under review by BFP.', 'uploads/reports/report_2.jpg', 14.04520000, 120.63200000, 12, NULL, NULL, 6, 'accepted', '2026-07-10 15:47:00', '2026-09-21 23:48:37'),
(3, 5, 'Maria Gonzales', '09211234567', 'Small kitchen fire, smoke visible from the street.', 'uploads/reports/report_3.jpg', 14.03000000, 120.64500000, 10, NULL, NULL, 7, 'pending', '2026-07-16 20:05:00', '2026-07-18 02:38:41'),
(4, 6, 'Pedro Aquino', '09221234567', 'Possible fire near a sari-sari store, unconfirmed.', 'uploads/reports/report_4.jpg', 14.04800000, 120.64400000, 15, NULL, NULL, 10, 'invalid', '2026-07-08 13:20:00', '2026-07-18 02:38:41'),
(5, 8, 'Carlo Fernandez', '09241234567', 'Fire reported near coastal residential cluster.', 'uploads/reports/report_5.jpg', 14.01600000, 120.61800000, 9, NULL, NULL, 16, 'resolved', '2026-07-02 06:15:00', '2026-07-18 02:38:41'),
(6, 10, 'Hey Lo', '09964521369', 'Home button', 'uploads/reports/report_10_1784468871_1f38095d.jpg', 13.99561830, 120.74927090, 16, NULL, NULL, 12, 'resolved', '2026-07-19 21:47:51', '2026-08-01 02:27:24'),
(7, 12, 'New Person', '09652349685', '', 'uploads/reports/report_12_1784538270_380b5447.jpg', 14.06727450, 120.62676910, 20, NULL, NULL, 8, 'invalid', '2026-07-20 17:04:30', '2026-08-01 00:55:11'),
(8, 12, 'New Person', '09652349685', 'Fire near', 'uploads/reports/report_12_1784562047_12902655.jpg', 13.99559960, 120.74925250, 18, NULL, NULL, 8, 'dispatched', '2026-07-20 23:40:47', '2026-09-15 06:14:47'),
(9, 12, 'New Person', '09652349685', '', 'uploads/reports/report_12_1784563540_1feaca3e.jpg', 13.99560430, 120.74926300, 20, NULL, NULL, 8, 'pending', '2026-07-21 00:05:40', '2026-07-21 00:05:40'),
(10, 10, 'Hey Lo', '09964521369', 'Fire', 'uploads/reports/report_10_1785513247_8cb1d258.jpg', 13.99574730, 120.74900530, 29, NULL, NULL, 12, 'invalid', '2026-07-31 23:54:07', '2026-09-15 07:49:08'),
(12, 10, 'Ren Adrias', '09964521369', 'House', 'uploads/reports/report_10_1785522568_df9833a8.jpg', 13.99558620, 120.74923670, 9, NULL, NULL, 12, 'dispatched', '2026-08-01 02:29:28', '2026-09-15 07:50:10'),
(13, 10, 'Ren Adrias', '09964521369', 'Fire', 'uploads/reports/report_10_1785523113_830ceb95.jpg', 13.99581850, 120.74904240, 21, NULL, NULL, 12, 'pending', '2026-08-01 02:38:33', '2026-08-01 02:38:33'),
(14, 11, 'Test Ariza', '09562635894', '', 'uploads/reports/report_11_1785523265_ee958ab5.jpg', 13.99576440, 120.74910650, 26, NULL, NULL, 7, 'dispatched', '2026-08-01 02:41:05', '2026-09-15 07:46:32'),
(15, 11, 'Test Ariza', '09562635894', 'Dark fire', 'uploads/reports/report_11_1785525981_78a70b82.jpg', 13.99571400, 120.74910410, 24, NULL, NULL, 7, 'dispatched', '2026-08-01 03:26:21', '2026-09-20 18:38:12'),
(16, 10, 'Ren Adrias', '09964521369', '', 'uploads/reports/report_10_1785530197_509bfea5.jpg', 13.99579590, 120.74903590, 34, NULL, NULL, 12, 'pending', '2026-08-01 04:36:37', '2026-08-01 04:36:37'),
(17, 10, 'Ren Adrias', '09964521369', 'House fire', 'uploads/reports/report_10_1785530796_e04d7c76.jpg', 13.99582190, 120.74899690, 17, NULL, NULL, 12, 'resolved', '2026-08-01 04:46:36', '2026-09-15 06:29:27'),
(18, 10, 'Ren Adrias', '09123456789', 'Fire incident near store', 'uploads/reports/report_10_1789425106_d138512b.jpg', 13.99559110, 120.74924180, 24, NULL, NULL, 12, 'resolved', '2026-09-15 06:31:46', '2026-09-15 06:32:15'),
(19, 10, 'Ren Adrias', '09123456789', 'Fire in Lian', 'uploads/reports/report_10_1789441872_63ccf612.jpg', 14.06635010, 120.62550240, 14, NULL, NULL, 12, 'resolved', '2026-09-15 11:11:12', '2026-09-21 15:21:55'),
(20, 10, 'Ren Adrias', '09123456789', 'Hala may sunog sa likod ng bahay namin', 'uploads/reports/report_10_1789953856_258dd0de.jpg', 14.06713960, 120.62665470, 18, NULL, NULL, 12, 'dispatched', '2026-09-21 09:24:16', '2026-09-21 09:25:31'),
(21, 10, 'Ren Adrias', '09123456789', 'Try', 'uploads/reports/report_10_1790008779_1e4c99d4.jpg', 13.99901106, 120.71096726, 16, 13.99561820, 120.74923920, 12, 'pending', '2026-09-22 00:39:39', '2026-09-22 00:39:39'),
(22, 10, 'Ren Adrias', '09123456789', 'Yes', 'uploads/reports/report_10_1790008931_59942a6d.jpg', 13.98796962, 120.74031472, 22, 13.99559470, 120.74923480, 12, 'pending', '2026-09-22 00:42:11', '2026-09-22 00:42:11'),
(23, 10, 'Ren Adrias', '09123456789', '', 'uploads/reports/report_10_1790008946_66650a14.jpg', 13.99559012, 120.74921966, 22, 13.99557830, 120.74923100, 12, 'pending', '2026-09-22 00:42:26', '2026-09-22 00:42:26'),
(24, 10, 'Ren Adrias', '09123456789', '', 'uploads/reports/report_10_1790008974_332eeba8.jpg', 13.99563176, 120.74921966, 17, 13.99562070, 120.74923520, 12, 'pending', '2026-09-22 00:42:54', '2026-09-22 00:42:54'),
(25, 10, 'Ren Adrias', '09123456789', '', 'uploads/reports/report_10_1790009017_25f4f1eb.jpg', 13.99563176, 120.74921966, 31, 13.99561300, 120.74923070, 12, 'pending', '2026-09-22 00:43:37', '2026-09-22 00:43:37'),
(26, 10, 'Ren Adrias', '09123456789', 'Another test', 'uploads/reports/report_10_1790009246_a8aec4c2.jpg', 13.99563176, 120.74921966, 18, 13.99562150, 120.74924030, 12, 'pending', '2026-09-22 00:47:26', '2026-09-22 00:47:26'),
(27, 10, 'Ren Adrias', '09123456789', 'Hey', 'uploads/reports/report_10_1790009419_56d4b176.jpg', 13.99563176, 120.74921966, 16, 13.99562150, 120.74923650, 12, 'pending', '2026-09-22 00:50:19', '2026-09-22 00:50:19'),
(28, 10, 'Ren Adrias', '09123456789', '', 'uploads/reports/report_10_1790009557_c30b95c9.jpg', 13.99561094, 120.74921966, 20, 13.99560340, 120.74923490, 12, 'pending', '2026-09-22 00:52:37', '2026-09-22 00:52:37'),
(29, 10, 'Ren Adrias', '09123456789', 'Ge', 'uploads/reports/report_10_1790009879_47533b23.jpg', 13.99560180, 120.74923680, 17, 13.99560180, 120.74923680, 12, 'pending', '2026-09-22 00:57:59', '2026-09-22 00:57:59'),
(30, 10, 'Ren Adrias', '09123456789', '', 'uploads/reports/report_10_1790010304_539641c1.jpg', 13.99563176, 120.74921966, 16, 13.99561720, 120.74923920, 12, 'pending', '2026-09-22 01:05:04', '2026-09-22 01:05:04'),
(31, 10, 'Ren Adrias', '09123456789', '', 'uploads/reports/report_10_1790010330_992fb78d.jpg', 13.99563176, 120.74921966, 19, 13.99562220, 120.74923720, 12, 'pending', '2026-09-22 01:05:30', '2026-09-22 01:05:30'),
(32, 10, 'Ren Adrias', '09123456789', 'Hey', 'uploads/reports/report_10_1790012578_e1b842bd.jpg', 13.99528888, 120.74852151, 30, 13.99563820, 120.74920250, 12, 'pending', '2026-09-22 01:42:58', '2026-09-22 01:42:58'),
(41, 10, 'Ren Adrias', '09123456789', '', 'uploads/reports/report_10_1790043291_ae040342.jpg', 13.98884277, 120.65262902, 22, 13.99577590, 120.74908990, 18, 'dispatched', '2026-09-22 10:14:51', '2026-09-22 10:37:58'),
(42, 10, 'Ren Adrias', '09123456789', 'Bunu', 'uploads/reports/report_10_1790043941_d82df4ef.jpg', 13.96215738, 120.63712747, 19, 13.99560290, 120.74923150, 8, 'resolved', '2026-09-22 10:25:41', '2026-09-22 10:51:32'),
(43, 10, 'Ren Adrias', '09123456789', 'Sd', 'uploads/reports/report_10_1790044495_dc7be8fe.jpg', 14.04169366, 120.62530571, 17, 13.99561900, 120.74923860, 19, 'pending', '2026-09-22 10:34:55', '2026-09-22 10:34:55'),
(44, 10, 'Ren Adrias', '09123456789', '', 'uploads/reports/report_10_1790044626_54e526ea.jpg', 13.99560574, 120.74923575, 19, 13.99559740, 120.74923850, NULL, 'resolved', '2026-09-22 10:37:06', '2026-09-22 11:26:16'),
(45, 10, 'Ren Adrias', '09123456789', '', 'uploads/reports/report_10_1790048096_e8869cbd.jpg', 13.99559533, 120.74914992, 27, 13.99558620, 120.74915880, NULL, 'resolved', '2026-09-22 11:34:56', '2026-09-22 11:36:07');

-- --------------------------------------------------------

--
-- Table structure for table `emergency_contact`
--

CREATE TABLE `emergency_contact` (
  `contact_id` int(11) NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `category` enum('fire','police','medical','disaster','other') DEFAULT 'other',
  `phone_number` varchar(20) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT 0 COMMENT 'Shown in the top "In Case of Fire" quick-dial card',
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `emergency_contact`
--

INSERT INTO `emergency_contact` (`contact_id`, `name`, `category`, `phone_number`, `description`, `is_primary`, `sort_order`) VALUES
(1, 'Bureau of Fire Protection', 'fire', '160', 'National fire emergency hotline', 1, 1),
(2, 'National Emergency Hotline', 'disaster', '911', 'Police, fire, medical emergencies', 0, 2),
(3, 'Philippine Red Cross', 'medical', '143', '24/7 emergency response', 0, 3),
(4, 'NDRRMC Hotline', 'disaster', '(02) 8911-5061', 'Disaster risk reduction', 0, 4),
(5, 'BFP Lian Fire Station', 'fire', '(043) 778-1234', 'Lian, Batangas local BFP station', 0, 5),
(6, 'Lian Municipal Police Station', 'police', '(043) 778-5678', 'Lian, Batangas local PNP station', 0, 6),
(7, 'Lian Rural Health Unit', 'medical', '(043) 778-9012', 'Lian, Batangas local health unit', 0, 7),
(8, 'Lian MDRRMO', 'disaster', '(043) 778-3456', 'Municipal Disaster Risk Reduction and Management Office', 0, 8);

-- --------------------------------------------------------

--
-- Table structure for table `fire_education_content`
--

CREATE TABLE `fire_education_content` (
  `content_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `category` enum('prevention','emergency_response','awareness') DEFAULT 'prevention',
  `summary` varchar(255) DEFAULT NULL,
  `body` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `read_minutes` int(11) DEFAULT 3,
  `is_featured` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fire_education_content`
--

INSERT INTO `fire_education_content` (`content_id`, `title`, `category`, `summary`, `body`, `image_path`, `read_minutes`, `is_featured`, `created_at`) VALUES
(1, 'Philippines Fire Season Safety Guide 2026', 'prevention', 'Essential fire prevention practices for Filipino households during the dry season.', 'Detailed guidance on preparing homes and communities for the dry season, covering electrical safety, cooking safety, and community-level prevention measures relevant to Lian, Batangas households.', NULL, 8, 1, '2026-07-18 02:38:41'),
(2, 'Kitchen Fire Safety', 'prevention', 'Most house fires start in the kitchen. Learn how to prevent cooking fires.', 'Never leave cooking unattended, keep flammable materials away from the stove, and know how to respond if a small grease fire starts (do not use water).', NULL, 4, 0, '2026-07-18 02:38:41'),
(3, 'Electrical Safety at Home', 'prevention', 'Overloaded circuits and faulty wiring are leading causes of residential fires.', 'Avoid overloading outlets, replace frayed cords, and have an electrician inspect wiring in older homes.', NULL, 5, 0, '2026-07-18 02:38:41'),
(4, 'What To Do During a Fire', 'emergency_response', 'Know the R.A.C.E. and P.A.S.S. procedures for fire emergencies.', 'R.A.C.E. (Rescue, Alarm, Contain, Extinguish/Evacuate) and P.A.S.S. (Pull, Aim, Squeeze, Sweep) are core procedures every household should memorize.', NULL, 5, 0, '2026-07-18 02:38:41'),
(5, 'Understanding Barangay Fire Risk Levels', 'awareness', 'How FireSight calculates Low, Moderate, and High risk levels for your barangay.', 'Risk levels are generated from historical incident data and demographic density, helping residents understand why their barangay is flagged at a particular risk level.', NULL, 4, 0, '2026-07-18 02:38:41');

-- --------------------------------------------------------

--
-- Table structure for table `incident_record`
--

CREATE TABLE `incident_record` (
  `incident_id` int(11) NOT NULL,
  `report_id` int(11) DEFAULT NULL,
  `barangay_id` int(11) DEFAULT NULL,
  `data_time` datetime DEFAULT NULL,
  `incident_type` enum('residential_fire','commercial_fire','vehicular_fire','storage_fire','rubbish_fire','others') NOT NULL DEFAULT 'residential_fire',
  `severity_level` enum('low','medium','high','critical') DEFAULT NULL,
  `cause_of_fire` varchar(255) DEFAULT NULL,
  `casualties` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `incident_record`
--

INSERT INTO `incident_record` (`incident_id`, `report_id`, `barangay_id`, `data_time`, `incident_type`, `severity_level`, `cause_of_fire`, `casualties`, `notes`) VALUES
(1, 1, 1, '2026-07-14 09:32:00', 'residential_fire', 'low', 'Faulty electrical wiring', 0, 'Resolved within 12 minutes, no injuries reported.'),
(2, 5, 16, '2026-07-02 06:15:00', 'residential_fire', 'medium', 'Unattended cooking', 0, 'Contained before spreading to adjacent structures.'),
(3, 8, NULL, NULL, 'residential_fire', 'low', NULL, NULL, NULL),
(4, 6, NULL, NULL, 'residential_fire', 'low', NULL, NULL, NULL),
(6, 16, NULL, NULL, 'residential_fire', 'low', 'HEY', 1, 'Wala'),
(7, 17, NULL, NULL, 'residential_fire', 'low', NULL, NULL, NULL),
(8, 18, NULL, NULL, 'residential_fire', 'low', NULL, NULL, NULL),
(9, 15, NULL, NULL, 'residential_fire', 'low', NULL, NULL, NULL),
(10, 14, NULL, NULL, 'residential_fire', 'low', NULL, NULL, NULL),
(11, 12, NULL, NULL, 'residential_fire', 'low', NULL, NULL, NULL),
(12, 19, NULL, NULL, 'residential_fire', 'low', NULL, NULL, NULL),
(13, 20, NULL, NULL, 'residential_fire', 'low', NULL, NULL, NULL),
(14, 41, NULL, NULL, 'residential_fire', 'low', NULL, NULL, NULL),
(15, 42, 8, '2026-09-22 10:25:41', 'residential_fire', 'low', 'G', 0, 'Gg'),
(16, 44, NULL, '2026-09-22 10:37:06', 'vehicular_fire', 'low', 'No', 0, 'Tr'),
(17, 45, NULL, '2026-09-22 11:34:56', 'residential_fire', 'low', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `notification_type` enum('alert','update','system') DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification`
--

INSERT INTO `notification` (`notification_id`, `user_id`, `title`, `message`, `notification_type`, `is_read`, `created_at`) VALUES
(1, 4, 'Report Resolved', 'Your fire report submitted on July 14 has been resolved. Thank you for reporting.', 'update', 0, '2026-07-14 09:44:00'),
(2, 4, 'Dry Season Fire Advisory', 'BFP Lian reminds all residents to strictly observe fire prevention measures during the dry season.', 'alert', 0, '2026-07-15 08:00:00'),
(3, 4, 'Safety Reminder - Open Burning Ban', 'Open burning is prohibited in all residential areas per RA 9514. Violators may face penalties.', 'system', 1, '2026-07-11 10:00:00'),
(4, 10, 'Report Submitted', 'Your fire report has been received and is under review by BFP Lian.', 'update', 1, '2026-07-19 21:47:51'),
(5, 12, 'Report Submitted', 'Your fire report has been received and is under review by BFP Lian.', 'update', 1, '2026-07-20 17:04:30'),
(6, 12, 'Report Submitted', 'Your fire report has been received and is under review by BFP Lian.', 'update', 1, '2026-07-20 23:40:47'),
(7, 12, 'Report Submitted', 'Your fire report has been received and is under review by BFP Lian.', 'update', 1, '2026-07-21 00:05:40'),
(8, 10, 'Report Submitted', 'Your fire report has been received and is under review by BFP Lian.', 'update', 1, '2026-07-31 23:54:07'),
(9, 10, 'Report Submitted', 'Your fire report has been received and is under review by BFP Lian.', 'update', 1, '2026-08-01 02:29:28'),
(10, 10, 'Report Submitted', 'Your fire report has been received and is under review by BFP Lian.', 'update', 1, '2026-08-01 02:38:33'),
(11, 11, 'Report Submitted', 'Your fire report has been received and is under review by BFP Lian.', 'update', 1, '2026-08-01 02:41:05'),
(12, 11, 'Report Submitted', 'Your fire report has been received and is under review by BFP Lian.', 'update', 1, '2026-08-01 03:26:21'),
(13, 10, 'Report Submitted', 'Your fire report has been received and is under review by BFP Lian.', 'update', 1, '2026-08-01 04:36:37'),
(14, 10, 'Report Submitted', 'Your fire report has been received and is under review by BFP Lian.', 'update', 1, '2026-08-01 04:46:37'),
(15, 10, 'Report Submitted', 'Your fire report has been received and is under review by BFP Lian.', 'update', 1, '2026-09-15 06:31:46'),
(16, 10, 'Report Submitted', 'Your fire report has been received and is under review by BFP Lian.', 'update', 1, '2026-09-15 11:11:12'),
(17, 10, 'Report Submitted', 'Your fire report has been received and is under review by BFP Lian.', 'update', 1, '2026-09-21 09:24:16'),
(18, 10, 'Report Submitted', 'Your fire report has been received and is under review by BFP Lian.', 'update', 1, '2026-09-22 00:39:39'),
(19, 10, 'Report Submitted', 'Your fire report has been received and is under review by BFP Lian.', 'update', 1, '2026-09-22 00:42:11'),
(20, 10, 'Report Submitted', 'Your fire report has been received and is under review by BFP Lian.', 'update', 1, '2026-09-22 00:42:26'),
(21, 10, 'Report Submitted', 'Your fire report has been received and is under review by BFP Lian.', 'update', 1, '2026-09-22 00:42:54'),
(22, 10, 'Report Submitted', 'Your fire report has been received and is under review by BFP Lian.', 'update', 1, '2026-09-22 00:43:37'),
(23, 10, 'Report Submitted', 'Your fire report has been received and is under review by BFP Lian.', 'update', 1, '2026-09-22 00:47:26'),
(24, 10, 'Report Submitted', 'Your fire report has been received and is under review by BFP Lian.', 'update', 1, '2026-09-22 00:50:19'),
(25, 10, 'Report Submitted', 'Your fire report has been received and is under review by BFP Lian.', 'update', 1, '2026-09-22 00:52:37'),
(26, 10, 'Report Submitted', 'Your fire report has been received and is under review by BFP Lian.', 'update', 1, '2026-09-22 00:57:59'),
(27, 10, 'Report Submitted', 'Your fire report has been received and is under review by BFP Lian.', 'update', 1, '2026-09-22 01:05:04'),
(28, 10, 'Report Submitted', 'Your fire report has been received and is under review by BFP Lian.', 'update', 1, '2026-09-22 01:05:30'),
(29, 10, 'Report Submitted', 'Your fire report has been received and is under review by BFP Lian.', 'update', 1, '2026-09-22 01:42:58'),
(30, 10, 'Report Submitted', 'Your fire report has been received and is under review by BFP Lian.', 'update', 1, '2026-09-22 10:14:51'),
(31, 10, 'Report Submitted', 'Your fire report has been received and is under review by BFP Lian.', 'update', 1, '2026-09-22 10:25:41'),
(32, 10, 'Report Submitted', 'Your fire report has been received and is under review by BFP Lian.', 'update', 1, '2026-09-22 10:34:55'),
(33, 10, 'Report Submitted', 'Your fire report has been received and is under review by BFP Lian.', 'update', 1, '2026-09-22 10:37:06'),
(34, 10, 'Report Submitted', 'Your fire report has been received and is under review by BFP Lian.', 'update', 1, '2026-09-22 11:34:56'),
(35, 10, 'Report Status Updated', 'Your fire report has been accepted by BFP personnel.', 'update', 1, '2026-09-22 11:35:16'),
(36, 10, 'Report Status Updated', 'Your fire report has been dispatched by BFP personnel.', 'update', 1, '2026-09-22 11:35:59'),
(37, 10, 'Report Status Updated', 'Your fire report has been resolved by BFP personnel.', 'update', 1, '2026-09-22 11:36:07');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset`
--

CREATE TABLE `password_reset` (
  `reset_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  `expires_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_reset`
--

INSERT INTO `password_reset` (`reset_id`, `user_id`, `token`, `is_used`, `expires_at`, `created_at`) VALUES
(1, 11, '6f714cb92019a30a9b4d94e3830ebddc05069e0d5b6e59d3266f446525ed619d', 0, '2026-09-15 03:50:08', '2026-09-15 02:50:08');

-- --------------------------------------------------------

--
-- Table structure for table `report_evidence`
--

CREATE TABLE `report_evidence` (
  `evidence_id` int(11) NOT NULL,
  `report_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `uploaded_by` int(11) DEFAULT NULL COMMENT 'FK to user (BFP personnel who added this evidence)',
  `caption` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `report_evidence`
--

INSERT INTO `report_evidence` (`evidence_id`, `report_id`, `image_path`, `uploaded_by`, `caption`, `created_at`) VALUES
(1, 2, 'uploads/evidence/evidence_2_1785517657_f0de46cd.jpg', 2, NULL, '2026-08-01 01:07:37'),
(2, 3, 'uploads/evidence/evidence_3_1785517676_cc22036f.jpg', 2, NULL, '2026-08-01 01:07:56'),
(3, 3, 'uploads/evidence/evidence_3_1785517680_81224c5a.jpg', 2, NULL, '2026-08-01 01:08:00'),
(4, 3, 'uploads/evidence/evidence_3_1785517685_e3a6d97c.jpg', 2, NULL, '2026-08-01 01:08:05'),
(5, 3, 'uploads/evidence/evidence_3_1785517706_83b6f7c5.jpg', 2, NULL, '2026-08-01 01:08:26'),
(6, 3, 'uploads/evidence/evidence_3_1785517716_2830426a.jpg', 2, NULL, '2026-08-01 01:08:36'),
(7, 8, 'uploads/evidence/evidence_8_1785518804_633c7116.jpg', 2, NULL, '2026-08-01 01:26:44'),
(8, 8, 'uploads/evidence/evidence_8_1785518815_3c45de0f.jpg', 2, NULL, '2026-08-01 01:26:55'),
(9, 9, 'uploads/evidence/evidence_9_1785518833_4113f275.jpg', 2, NULL, '2026-08-01 01:27:13'),
(10, 3, 'uploads/evidence/evidence_3_1785518894_d67b61f5.jpg', 2, NULL, '2026-08-01 01:28:14'),
(11, 3, 'uploads/evidence/evidence_3_1785518908_5c396bb3.jpg', 2, NULL, '2026-08-01 01:28:28'),
(12, 10, 'uploads/evidence/evidence_10_1785518916_8fc90b89.jpg', 2, NULL, '2026-08-01 01:28:36'),
(13, 7, 'uploads/evidence/evidence_7_1785518923_394dc04c.jpg', 2, NULL, '2026-08-01 01:28:43'),
(14, 3, 'uploads/evidence/evidence_3_1785518943_9a07d474.jpg', 2, NULL, '2026-08-01 01:29:03'),
(15, 10, 'uploads/evidence/evidence_10_1785519414_085c7380.jpg', 2, NULL, '2026-08-01 01:36:54');

-- --------------------------------------------------------

--
-- Table structure for table `report_link`
--

CREATE TABLE `report_link` (
  `link_id` int(11) NOT NULL,
  `main_report_id` int(11) DEFAULT NULL,
  `related_report_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `report_status_history`
--

CREATE TABLE `report_status_history` (
  `history_id` int(11) NOT NULL,
  `report_id` int(11) DEFAULT NULL,
  `status` enum('pending','accepted','dispatched','resolved','invalid') DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `changed_by` int(11) DEFAULT NULL COMMENT 'FK to user (BFP personnel), NULL if system-generated',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `report_status_history`
--

INSERT INTO `report_status_history` (`history_id`, `report_id`, `status`, `notes`, `changed_by`, `created_at`) VALUES
(1, 1, 'pending', 'Report submitted by resident.', NULL, '2026-07-14 09:32:00'),
(2, 1, 'accepted', 'Confirmed by barangay official.', 2, '2026-07-14 09:40:00'),
(3, 1, 'resolved', 'Fire extinguished, no casualties.', 2, '2026-07-14 09:44:00'),
(4, 2, 'pending', 'Report submitted by resident.', NULL, '2026-07-10 15:47:00'),
(5, 2, 'accepted', 'Unit dispatched to assess grass fire.', 3, '2026-07-10 16:02:00'),
(6, 3, 'pending', 'Report submitted by resident.', NULL, '2026-07-16 20:05:00'),
(7, 4, 'pending', 'Report submitted by resident.', NULL, '2026-07-08 13:20:00'),
(8, 4, 'invalid', 'Unable to confirm active fire at location.', 2, '2026-07-08 13:50:00'),
(9, 5, 'pending', 'Report submitted by resident.', NULL, '2026-07-02 06:15:00'),
(10, 5, 'accepted', 'Confirmed active fire, units dispatched.', 3, '2026-07-02 06:20:00'),
(11, 5, 'resolved', 'Fire contained and extinguished.', 3, '2026-07-02 07:05:00'),
(12, 6, 'pending', 'Report submitted by resident.', NULL, '2026-07-19 21:47:51'),
(13, 7, 'pending', 'Report submitted by resident.', NULL, '2026-07-20 17:04:30'),
(14, 8, 'pending', 'Report submitted by resident.', NULL, '2026-07-20 23:40:47'),
(15, 9, 'pending', 'Report submitted by resident.', NULL, '2026-07-21 00:05:40'),
(16, 10, 'pending', 'Report submitted by resident.', NULL, '2026-07-31 23:54:07'),
(17, 7, 'invalid', 'Unable to confirm active incident.', 2, '2026-08-01 00:55:11'),
(18, 8, 'accepted', NULL, 2, '2026-08-01 00:55:31'),
(19, 6, 'accepted', NULL, 2, '2026-08-01 01:00:21'),
(20, 6, 'dispatched', NULL, 2, '2026-08-01 01:27:42'),
(22, 6, 'resolved', NULL, 2, '2026-08-01 02:27:24'),
(23, 12, 'pending', 'Report submitted by resident.', NULL, '2026-08-01 02:29:28'),
(24, 13, 'pending', 'Report submitted by resident.', NULL, '2026-08-01 02:38:33'),
(25, 14, 'pending', 'Report submitted by resident.', NULL, '2026-08-01 02:41:05'),
(26, 15, 'pending', 'Report submitted by resident.', NULL, '2026-08-01 03:26:21'),
(27, 16, 'pending', 'Report submitted by resident.', NULL, '2026-08-01 04:36:37'),
(28, 17, 'pending', 'Report submitted by resident.', NULL, '2026-08-01 04:46:37'),
(29, 8, 'dispatched', NULL, 2, '2026-09-15 06:14:47'),
(30, 17, 'accepted', NULL, 2, '2026-09-15 06:29:23'),
(31, 17, 'dispatched', NULL, 2, '2026-09-15 06:29:25'),
(32, 17, 'resolved', NULL, 2, '2026-09-15 06:29:27'),
(33, 18, 'pending', 'Report submitted by resident.', NULL, '2026-09-15 06:31:46'),
(34, 18, 'accepted', NULL, 2, '2026-09-15 06:32:12'),
(35, 18, 'dispatched', NULL, 2, '2026-09-15 06:32:14'),
(36, 18, 'resolved', NULL, 2, '2026-09-15 06:32:15'),
(37, 15, 'accepted', NULL, 2, '2026-09-15 07:33:17'),
(38, 14, 'accepted', NULL, 2, '2026-09-15 07:46:30'),
(39, 14, 'dispatched', NULL, 2, '2026-09-15 07:46:32'),
(40, 10, 'invalid', 'Unable to confirm active incident.', 2, '2026-09-15 07:49:08'),
(41, 12, 'accepted', NULL, 2, '2026-09-15 07:50:06'),
(42, 12, 'dispatched', NULL, 2, '2026-09-15 07:50:10'),
(43, 19, 'pending', 'Report submitted by resident.', NULL, '2026-09-15 11:11:12'),
(44, 19, 'accepted', NULL, 2, '2026-09-15 11:19:09'),
(45, 19, 'dispatched', NULL, 2, '2026-09-15 11:19:15'),
(46, 15, 'dispatched', NULL, 2, '2026-09-20 18:38:12'),
(47, 20, 'pending', 'Report submitted by resident.', NULL, '2026-09-21 09:24:16'),
(48, 20, 'accepted', NULL, 2, '2026-09-21 09:25:28'),
(49, 20, 'dispatched', NULL, 2, '2026-09-21 09:25:31'),
(50, 19, 'resolved', NULL, 2, '2026-09-21 15:21:55'),
(51, 21, 'pending', 'Report submitted by resident.', NULL, '2026-09-22 00:39:39'),
(52, 22, 'pending', 'Report submitted by resident.', NULL, '2026-09-22 00:42:11'),
(53, 23, 'pending', 'Report submitted by resident.', NULL, '2026-09-22 00:42:26'),
(54, 24, 'pending', 'Report submitted by resident.', NULL, '2026-09-22 00:42:54'),
(55, 25, 'pending', 'Report submitted by resident.', NULL, '2026-09-22 00:43:37'),
(56, 26, 'pending', 'Report submitted by resident.', NULL, '2026-09-22 00:47:26'),
(57, 27, 'pending', 'Report submitted by resident.', NULL, '2026-09-22 00:50:19'),
(58, 28, 'pending', 'Report submitted by resident.', NULL, '2026-09-22 00:52:37'),
(59, 29, 'pending', 'Report submitted by resident.', NULL, '2026-09-22 00:57:59'),
(60, 30, 'pending', 'Report submitted by resident.', NULL, '2026-09-22 01:05:04'),
(61, 31, 'pending', 'Report submitted by resident.', NULL, '2026-09-22 01:05:30'),
(62, 32, 'pending', 'Report submitted by resident.', NULL, '2026-09-22 01:42:58'),
(63, 41, 'pending', 'Report submitted by resident.', NULL, '2026-09-22 10:14:51'),
(64, 41, 'accepted', NULL, 2, '2026-09-22 10:16:44'),
(65, 41, 'dispatched', NULL, 2, '2026-09-22 10:16:47'),
(66, 42, 'pending', 'Report submitted by resident.', NULL, '2026-09-22 10:25:41'),
(67, 43, 'pending', 'Report submitted by resident.', NULL, '2026-09-22 10:34:55'),
(68, 44, 'pending', 'Report submitted by resident.', NULL, '2026-09-22 10:37:06'),
(69, 42, 'accepted', NULL, 2, '2026-09-22 10:51:09'),
(70, 42, 'dispatched', NULL, 2, '2026-09-22 10:51:14'),
(71, 42, 'resolved', NULL, 2, '2026-09-22 10:51:32'),
(72, 44, 'accepted', NULL, 2, '2026-09-22 11:26:09'),
(73, 44, 'dispatched', NULL, 2, '2026-09-22 11:26:12'),
(74, 44, 'resolved', NULL, 2, '2026-09-22 11:26:16'),
(75, 45, 'pending', 'Report submitted by resident.', NULL, '2026-09-22 11:34:56'),
(76, 45, 'accepted', NULL, 2, '2026-09-22 11:35:16'),
(77, 45, 'dispatched', NULL, 2, '2026-09-22 11:35:59'),
(78, 45, 'resolved', NULL, 2, '2026-09-22 11:36:07');

-- --------------------------------------------------------

--
-- Table structure for table `resident_address`
--

CREATE TABLE `resident_address` (
  `address_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `house_no_street` varchar(150) DEFAULT NULL,
  `barangay_id` int(11) DEFAULT NULL,
  `municipality` varchar(100) DEFAULT 'Lian',
  `province` varchar(100) DEFAULT 'Batangas'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resident_address`
--

INSERT INTO `resident_address` (`address_id`, `user_id`, `house_no_street`, `barangay_id`, `municipality`, `province`) VALUES
(1, 4, '123 Rizal Street', 1, 'Lian', 'Batangas'),
(2, 5, '45 Mabini Street', 7, 'Lian', 'Batangas'),
(3, 6, '78 Bonifacio Street', 6, 'Lian', 'Batangas'),
(4, 7, '12 Del Pilar Street', 16, 'Lian', 'Batangas'),
(5, 8, '9 Luna Street', 10, 'Lian', 'Batangas'),
(6, 9, '145', 1, 'Lian', 'Batangas'),
(7, 10, '7376', 12, 'Lian', 'Batangas'),
(8, 11, '125 Rizal', 7, 'Lian', 'Batangas'),
(9, 12, 'Hzhsb', 8, 'Lian', 'Batangas');

-- --------------------------------------------------------

--
-- Table structure for table `risk_assessment`
--

CREATE TABLE `risk_assessment` (
  `risk_id` int(11) NOT NULL,
  `barangay_id` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `prediction_score` decimal(5,2) DEFAULT NULL,
  `risk_level` enum('low','moderate','high') DEFAULT NULL,
  `generated_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `risk_assessment`
--

INSERT INTO `risk_assessment` (`risk_id`, `barangay_id`, `date`, `prediction_score`, `risk_level`, `generated_at`) VALUES
(1, 1, '2026-07-01', 78.40, 'high', '2026-07-18 02:38:41'),
(2, 2, '2026-07-01', 52.10, 'moderate', '2026-07-18 02:38:41'),
(3, 3, '2026-07-01', 45.30, 'moderate', '2026-07-18 02:38:41'),
(4, 4, '2026-07-01', 30.00, 'low', '2026-07-18 02:38:41'),
(5, 5, '2026-07-01', 35.60, 'low', '2026-07-18 02:38:41'),
(6, 6, '2026-07-01', 61.20, 'moderate', '2026-07-18 02:38:41'),
(7, 7, '2026-07-01', 40.90, 'moderate', '2026-07-18 02:38:41'),
(8, 8, '2026-07-01', 25.10, 'low', '2026-07-18 02:38:41'),
(9, 9, '2026-07-01', 22.00, 'low', '2026-07-18 02:38:41'),
(10, 10, '2026-07-01', 70.30, 'high', '2026-07-18 02:38:41'),
(11, 11, '2026-07-01', 18.50, 'low', '2026-07-18 02:38:41'),
(12, 12, '2026-07-01', 27.80, 'low', '2026-07-18 02:38:41'),
(13, 13, '2026-07-01', 33.40, 'low', '2026-07-18 02:38:41'),
(14, 14, '2026-07-01', 20.10, 'low', '2026-07-18 02:38:41'),
(15, 15, '2026-07-01', 48.60, 'moderate', '2026-07-18 02:38:41'),
(16, 16, '2026-07-01', 82.90, 'high', '2026-07-18 02:38:41'),
(17, 17, '2026-07-01', 29.30, 'low', '2026-07-18 02:38:41'),
(18, 18, '2026-07-01', 24.70, 'low', '2026-07-18 02:38:41'),
(19, 19, '2026-07-01', 31.20, 'low', '2026-07-18 02:38:41');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `role` enum('admin','personnel','resident') DEFAULT 'resident',
  `first_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `suffix` varchar(10) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL COMMENT 'PHP password_hash() output; NULL if Google-only account',
  `google_id` varchar(255) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `role`, `first_name`, `middle_name`, `last_name`, `suffix`, `contact_number`, `email`, `username`, `password`, `google_id`, `profile_image`, `is_verified`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'Ramon', 'Diaz', 'Villareal', NULL, '09171234567', 'admin@firesight.gov.ph', 'admin', '$2y$10$abcdefghijklmnopqrstuv.abcdefghijklmnopqrstuvwxyzABCDE', NULL, NULL, 1, 1, '2026-07-18 02:38:41', '2026-07-18 02:38:41'),
(2, 'personnel', 'Andres', 'Cruz', 'Panganiban', NULL, '09181234567', 'panganiban@gmail.com', 'apanganiban', '$2y$10$KkDmuxQbhn8UlbSZo4y5ZuyPb.47nob4ynufhz7K//37xii8x4IzG', NULL, NULL, 1, 1, '2026-07-18 02:38:41', '2026-09-20 19:23:24'),
(3, 'personnel', 'Liza', 'Reyes', 'Manalo', NULL, '09191234567', 'l.manalo@bfplian.gov.ph', 'lmanalo', '$2y$10$abcdefghijklmnopqrstuv.abcdefghijklmnopqrstuvwxyzABCDE', NULL, NULL, 1, 1, '2026-07-18 02:38:41', '2026-07-18 02:38:41'),
(4, 'resident', 'Juan', 'Santos', 'Dela Cruz', 'Jr.', '09201234567', 'juan.delacruz@gmail.com', 'juandelacruz', '$2y$10$abcdefghijklmnopqrstuv.abcdefghijklmnopqrstuvwxyzABCDE', NULL, NULL, 1, 1, '2026-07-18 02:38:41', '2026-07-18 02:38:41'),
(5, 'resident', 'Maria', 'Lopez', 'Gonzales', NULL, '09211234567', 'maria.gonzales@gmail.com', 'mgonzales', '$2y$10$abcdefghijklmnopqrstuv.abcdefghijklmnopqrstuvwxyzABCDE', NULL, NULL, 1, 1, '2026-07-18 02:38:41', '2026-07-18 02:38:41'),
(6, 'resident', 'Pedro', 'Ramos', 'Aquino', NULL, '09221234567', 'pedro.aquino@gmail.com', 'paquino', '$2y$10$abcdefghijklmnopqrstuv.abcdefghijklmnopqrstuvwxyzABCDE', NULL, NULL, 1, 1, '2026-07-18 02:38:41', '2026-07-18 02:38:41'),
(7, 'resident', 'Ana', 'Bautista', 'Torres', NULL, '09231234567', 'ana.torres@gmail.com', 'atorres', '$2y$10$abcdefghijklmnopqrstuv.abcdefghijklmnopqrstuvwxyzABCDE', NULL, NULL, 1, 1, '2026-07-18 02:38:41', '2026-07-18 02:38:41'),
(8, 'resident', 'Carlo', 'Mendoza', 'Fernandez', NULL, '09241234567', 'carlo.fernandez@gmail.com', 'cfernandez', '$2y$10$KkDmuxQbhn8UlbSZo4y5ZuyPb.47nob4ynufhz7K//37xii8x4IzG', NULL, NULL, 1, 1, '2026-07-18 02:38:41', '2026-07-31 19:45:04'),
(9, 'resident', 'Hey', NULL, 'Bro', NULL, '09152643867', 'hello@gmail.com', NULL, '$2y$10$6JSake40C/aIKJXMMkMnq.sCpo70Kc9xXdg2t6ERmgBmJxNmkLj16', NULL, NULL, 0, 1, '2026-07-18 15:52:04', '2026-07-18 15:52:04'),
(10, 'resident', 'Ren', 'Cer', 'Adrias', NULL, '09123456789', 'test@gmail.com', NULL, '$2y$10$D7Tp3UOpl/xWcxdJKxp7K..pS5Tms1RjNlrFErpk6OJUJwoRSmUWK', NULL, NULL, 0, 1, '2026-07-19 21:40:55', '2026-09-15 06:06:57'),
(11, 'resident', 'Test', 'Fox', 'Ariza', 'Jr', '09562635894', 'clarence.adrias@gmail.com', NULL, '$2y$10$KkDmuxQbhn8UlbSZo4y5ZuyPb.47nob4ynufhz7K//37xii8x4IzG', NULL, NULL, 0, 1, '2026-07-20 16:57:22', '2026-09-15 02:49:24'),
(12, 'resident', 'New', NULL, 'Person', NULL, '09652349685', 'new@gmail.com', NULL, '$2y$10$h.E6tmhQwdgVYIeHRVYzi.Qds4GJh/ohRJUlgAgyPUdtCZ/wUt5pa', NULL, NULL, 0, 1, '2026-07-20 17:01:04', '2026-07-20 17:01:04');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcement`
--
ALTER TABLE `announcement`
  ADD PRIMARY KEY (`announcement_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `auth_token`
--
ALTER TABLE `auth_token`
  ADD PRIMARY KEY (`token_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `barangay`
--
ALTER TABLE `barangay`
  ADD PRIMARY KEY (`barangay_id`);

--
-- Indexes for table `barangay_contact`
--
ALTER TABLE `barangay_contact`
  ADD PRIMARY KEY (`contact_id`),
  ADD KEY `barangay_id` (`barangay_id`);

--
-- Indexes for table `bfp_personnel_details`
--
ALTER TABLE `bfp_personnel_details`
  ADD PRIMARY KEY (`details_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `community_report`
--
ALTER TABLE `community_report`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `barangay_id` (`barangay_id`);

--
-- Indexes for table `emergency_contact`
--
ALTER TABLE `emergency_contact`
  ADD PRIMARY KEY (`contact_id`);

--
-- Indexes for table `fire_education_content`
--
ALTER TABLE `fire_education_content`
  ADD PRIMARY KEY (`content_id`);

--
-- Indexes for table `incident_record`
--
ALTER TABLE `incident_record`
  ADD PRIMARY KEY (`incident_id`),
  ADD KEY `report_id` (`report_id`),
  ADD KEY `barangay_id` (`barangay_id`);

--
-- Indexes for table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `password_reset`
--
ALTER TABLE `password_reset`
  ADD PRIMARY KEY (`reset_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `report_evidence`
--
ALTER TABLE `report_evidence`
  ADD PRIMARY KEY (`evidence_id`),
  ADD KEY `report_id` (`report_id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `report_link`
--
ALTER TABLE `report_link`
  ADD PRIMARY KEY (`link_id`),
  ADD KEY `main_report_id` (`main_report_id`),
  ADD KEY `related_report_id` (`related_report_id`);

--
-- Indexes for table `report_status_history`
--
ALTER TABLE `report_status_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `report_id` (`report_id`),
  ADD KEY `changed_by` (`changed_by`);

--
-- Indexes for table `resident_address`
--
ALTER TABLE `resident_address`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `barangay_id` (`barangay_id`);

--
-- Indexes for table `risk_assessment`
--
ALTER TABLE `risk_assessment`
  ADD PRIMARY KEY (`risk_id`),
  ADD KEY `barangay_id` (`barangay_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcement`
--
ALTER TABLE `announcement`
  MODIFY `announcement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `auth_token`
--
ALTER TABLE `auth_token`
  MODIFY `token_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=135;

--
-- AUTO_INCREMENT for table `barangay`
--
ALTER TABLE `barangay`
  MODIFY `barangay_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `barangay_contact`
--
ALTER TABLE `barangay_contact`
  MODIFY `contact_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `bfp_personnel_details`
--
ALTER TABLE `bfp_personnel_details`
  MODIFY `details_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `community_report`
--
ALTER TABLE `community_report`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `emergency_contact`
--
ALTER TABLE `emergency_contact`
  MODIFY `contact_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `fire_education_content`
--
ALTER TABLE `fire_education_content`
  MODIFY `content_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `incident_record`
--
ALTER TABLE `incident_record`
  MODIFY `incident_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `password_reset`
--
ALTER TABLE `password_reset`
  MODIFY `reset_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `report_evidence`
--
ALTER TABLE `report_evidence`
  MODIFY `evidence_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `report_link`
--
ALTER TABLE `report_link`
  MODIFY `link_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `report_status_history`
--
ALTER TABLE `report_status_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `resident_address`
--
ALTER TABLE `resident_address`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `risk_assessment`
--
ALTER TABLE `risk_assessment`
  MODIFY `risk_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcement`
--
ALTER TABLE `announcement`
  ADD CONSTRAINT `announcement_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `auth_token`
--
ALTER TABLE `auth_token`
  ADD CONSTRAINT `auth_token_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `barangay_contact`
--
ALTER TABLE `barangay_contact`
  ADD CONSTRAINT `barangay_contact_ibfk_1` FOREIGN KEY (`barangay_id`) REFERENCES `barangay` (`barangay_id`) ON DELETE CASCADE;

--
-- Constraints for table `bfp_personnel_details`
--
ALTER TABLE `bfp_personnel_details`
  ADD CONSTRAINT `bfp_personnel_details_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `community_report`
--
ALTER TABLE `community_report`
  ADD CONSTRAINT `community_report_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `community_report_ibfk_2` FOREIGN KEY (`barangay_id`) REFERENCES `barangay` (`barangay_id`);

--
-- Constraints for table `incident_record`
--
ALTER TABLE `incident_record`
  ADD CONSTRAINT `incident_record_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `community_report` (`report_id`),
  ADD CONSTRAINT `incident_record_ibfk_2` FOREIGN KEY (`barangay_id`) REFERENCES `barangay` (`barangay_id`);

--
-- Constraints for table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `notification_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `password_reset`
--
ALTER TABLE `password_reset`
  ADD CONSTRAINT `password_reset_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `report_evidence`
--
ALTER TABLE `report_evidence`
  ADD CONSTRAINT `report_evidence_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `community_report` (`report_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `report_evidence_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `report_link`
--
ALTER TABLE `report_link`
  ADD CONSTRAINT `report_link_ibfk_1` FOREIGN KEY (`main_report_id`) REFERENCES `community_report` (`report_id`),
  ADD CONSTRAINT `report_link_ibfk_2` FOREIGN KEY (`related_report_id`) REFERENCES `community_report` (`report_id`);

--
-- Constraints for table `report_status_history`
--
ALTER TABLE `report_status_history`
  ADD CONSTRAINT `report_status_history_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `community_report` (`report_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `report_status_history_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `resident_address`
--
ALTER TABLE `resident_address`
  ADD CONSTRAINT `resident_address_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `resident_address_ibfk_2` FOREIGN KEY (`barangay_id`) REFERENCES `barangay` (`barangay_id`);

--
-- Constraints for table `risk_assessment`
--
ALTER TABLE `risk_assessment`
  ADD CONSTRAINT `risk_assessment_ibfk_1` FOREIGN KEY (`barangay_id`) REFERENCES `barangay` (`barangay_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
