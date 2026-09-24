-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 23, 2026 at 09:57 PM
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
-- Database: `firesight_mobile_cs`
--

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

--
-- Indexes for dumped tables
--

--
-- Indexes for table `fire_education_content`
--
ALTER TABLE `fire_education_content`
  ADD PRIMARY KEY (`content_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `fire_education_content`
--
ALTER TABLE `fire_education_content`
  MODIFY `content_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
