-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 23, 2026 at 05:34 PM
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
-- Database: `firesight_db2`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcement`
--

CREATE TABLE `announcement` (
  `announcement_id` bigint(20) UNSIGNED NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(150) NOT NULL,
  `content` text NOT NULL,
  `announcement_type` enum('general','advisory','emergency','fire_safety_tip') NOT NULL DEFAULT 'general',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `announcement`
--

INSERT INTO `announcement` (`announcement_id`, `created_by`, `title`, `content`, `announcement_type`, `created_at`) VALUES
(1, 3, 'Fire Prevention Month Kick-off', 'The Bureau of Fire Protection Lian reminds all residents to check electrical wiring and avoid overloading outlets this Fire Prevention Month.', 'fire_safety_tip', '2026-06-04 22:26:30'),
(2, 8, 'Advisory: Dry Season Fire Risk', 'Due to the ongoing dry spell, residents are advised to exercise extra caution with open flames and unattended cooking.', 'advisory', '2026-05-29 02:04:40'),
(3, 7, 'Community Fire Drill Schedule', 'A community-wide fire drill will be conducted in select barangays this month. Please coordinate with your barangay office.', 'general', '2026-03-06 00:15:28'),
(4, 7, 'Emergency: Grass Fire Alert', 'A grass fire has been reported spreading near residential areas. Residents in the vicinity are advised to stay alert.', 'emergency', '2026-02-12 10:56:54'),
(5, 8, 'Safety Tip: Kitchen Fire Prevention', 'Never leave cooking unattended and keep a fire extinguisher accessible in your kitchen at all times.', 'fire_safety_tip', '2026-06-12 11:54:56'),
(6, 5, 'Barangay Fire Brigade Training', 'BFP Lian will conduct a basic fire brigade training for barangay volunteers next week.', 'general', '2026-01-17 16:56:16'),
(7, 6, 'Advisory: Fireworks Safety', 'With the upcoming holidays, residents are reminded to purchase only legal fireworks and use them in open, supervised areas.', 'advisory', '2026-03-30 09:06:00'),
(8, 8, 'Emergency: Structural Fire Response', 'BFP Lian responded to a structural fire incident. Residents nearby are advised to avoid the area until further notice.', 'emergency', '2026-06-26 16:51:56'),
(10, 2, 'Community Report App Reminder', 'Residents are encouraged to use the FireSight app to report fire incidents and hazards in real time.', 'general', '2026-05-27 15:48:35'),
(11, 6, 'Advisory: Summer Heat Precautions', 'High temperatures increase fire risk. Avoid burning dry leaves and grass during peak afternoon hours.', 'advisory', '2026-01-28 12:22:03'),
(12, 7, 'Fire Safety Seminar for Barangay Officials', 'A fire safety seminar will be held for barangay officials to strengthen local disaster preparedness.', 'general', '2026-04-22 03:24:05');

-- --------------------------------------------------------

--
-- Table structure for table `barangay`
--

CREATE TABLE `barangay` (
  `barangay_id` bigint(20) UNSIGNED NOT NULL,
  `barangay_name` varchar(100) NOT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `boundary` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`boundary`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `barangay`
--

INSERT INTO `barangay` (`barangay_id`, `barangay_name`, `latitude`, `longitude`, `boundary`) VALUES
(1, 'Poblacion 1', 14.0353356, 120.6511238, '[[120.64813,14.035274],[120.648017,14.034723],[120.649251,14.034173],[120.652548,14.033505],[120.653729,14.033628],[120.653577,14.035824],[120.653461,14.035905],[120.653152,14.035885],[120.652981,14.035948],[120.652646,14.037031],[120.650168,14.037276],[120.650209,14.036858],[120.648727,14.036851],[120.648839,14.03537],[120.64813,14.035274]]'),
(2, 'Poblacion 2', 14.0381440, 120.6512847, '[[120.649938,14.038911],[120.650174,14.037311],[120.652658,14.037054],[120.652081,14.039344],[120.649938,14.038911]]'),
(3, 'Poblacion 3', 14.0404400, 120.6506992, '[[120.649196,14.041778],[120.649479,14.041219],[120.649721,14.0407],[120.649924,14.038941],[120.650992,14.039147],[120.65207,14.039372],[120.651439,14.041789],[120.650625,14.0417],[120.649196,14.041778]]'),
(4, 'Poblacion 4', 14.0379271, 120.6536966, '[[120.652106,14.039373],[120.653029,14.035982],[120.654304,14.036004],[120.65494,14.036624],[120.65492,14.039096],[120.654161,14.039779],[120.652106,14.039373]]'),
(5, 'Poblacion 5', 14.0412322, 120.6529497, '[[120.652071,14.039429],[120.654137,14.039779],[120.653931,14.04132],[120.653827,14.043689],[120.652536,14.042511],[120.651557,14.041858],[120.652071,14.039429]]'),
(6, 'Balibago', 13.9422816, 120.6508579, '[[120.667735,13.959816],[120.658353,13.961007],[120.656288,13.962662],[120.65228,13.962228],[120.649339,13.960089],[120.646899,13.960708],[120.643927,13.960934],[120.643577,13.958503],[120.642829,13.956376],[120.643736,13.954094],[120.642988,13.951814],[120.641804,13.952743],[120.635887,13.953114],[120.638167,13.95075],[120.637732,13.949939],[120.638107,13.949615],[120.638803,13.949953],[120.639638,13.949987],[120.640021,13.949362],[120.639708,13.948517],[120.636598,13.945026],[120.633587,13.943962],[120.632456,13.943793],[120.631599,13.943519],[120.631721,13.942573],[120.630973,13.941289],[120.629058,13.940496],[120.626951,13.93893],[120.624393,13.938423],[120.621818,13.939048],[120.622183,13.938102],[120.621696,13.937038],[120.621957,13.935788],[120.621539,13.935214],[120.620565,13.935096],[120.619503,13.935788],[120.618894,13.934707],[120.617954,13.934572],[120.618059,13.933745],[120.617078,13.932851],[120.617503,13.932202],[120.618596,13.931613],[120.619204,13.930846],[120.620237,13.929844],[120.630989,13.931672],[120.633236,13.931436],[120.635605,13.932084],[120.638278,13.931672],[120.640586,13.931318],[120.642712,13.930257],[120.64417,13.93061],[120.650017,13.930453],[120.652079,13.927852],[120.656305,13.928452],[120.66115,13.926151],[120.66517,13.926651],[120.667438,13.926651],[120.672283,13.925951],[120.675169,13.926251],[120.674963,13.927952],[120.673417,13.931854],[120.669912,13.935656],[120.667129,13.943159],[120.667735,13.959816]]'),
(7, 'Bagong Pook', 14.0336630, 120.6806919, '[[120.67282,14.038994],[120.671985,14.031503],[120.669659,14.027137],[120.687154,14.022918],[120.688193,14.037118],[120.68874,14.038232],[120.69109,14.045391],[120.686061,14.044171],[120.684093,14.041785],[120.680868,14.039239],[120.67901,14.040141],[120.678573,14.043057],[120.6771,14.044487],[120.67282,14.038994]]'),
(8, 'Binubusan', 13.9687652, 120.6382428, '[[120.616608,13.968625],[120.618011,13.967548],[120.62016,13.967268],[120.620575,13.965334],[120.622348,13.964796],[120.624452,13.965414],[120.626245,13.965925],[120.628848,13.965791],[120.63038,13.964616],[120.630546,13.961687],[120.631902,13.959626],[120.631223,13.954348],[120.635784,13.953246],[120.640093,13.952799],[120.641868,13.952799],[120.642941,13.952045],[120.643681,13.954091],[120.642688,13.956352],[120.643442,13.958676],[120.643812,13.961045],[120.646919,13.960758],[120.649286,13.960183],[120.652245,13.962301],[120.651061,13.966321],[120.649938,13.97062],[120.650579,13.972134],[120.649938,13.973804],[120.650819,13.975512],[120.65498,13.977609],[120.65626,13.978851],[120.652339,13.977803],[120.644657,13.979939],[120.642536,13.979822],[120.638015,13.980016],[120.634974,13.98619],[120.630573,13.986267],[120.632293,13.98386],[120.631973,13.982734],[120.631573,13.982152],[120.62951,13.978976],[120.63051,13.977384],[120.63015,13.975481],[120.627109,13.973346],[120.627349,13.971832],[120.626811,13.970993],[120.625051,13.971576],[120.625731,13.969285],[120.624931,13.968819],[120.624411,13.969246],[120.624131,13.968741],[120.62161,13.968741],[120.62121,13.969168],[120.617929,13.969401],[120.616608,13.968625]]'),
(9, 'Bungahan', 14.0468166, 120.6380702, '[[120.624759,14.059033],[120.623804,14.055484],[120.623337,14.051174],[120.624766,14.049872],[120.625466,14.049298],[120.626362,14.048225],[120.627154,14.047273],[120.6282,14.046944],[120.628993,14.046175],[120.633596,14.046705],[120.634405,14.04452],[120.636557,14.044446],[120.640698,14.035289],[120.643226,14.035509],[120.6456,14.035722],[120.647996,14.034729],[120.648121,14.03529],[120.648815,14.03539],[120.648707,14.036864],[120.650193,14.036877],[120.649716,14.040348],[120.649688,14.040678],[120.649172,14.041781],[120.648247,14.041865],[120.647527,14.04238],[120.64697,14.043453],[120.646843,14.044715],[120.646762,14.046665],[120.646642,14.04766],[120.646406,14.048614],[120.645868,14.052133],[120.644774,14.05356],[120.643184,14.054505],[120.636904,14.053199],[120.63391,14.053253],[120.631121,14.05404],[120.628675,14.055266],[120.626321,14.056845],[120.624759,14.059033]]'),
(10, 'Cumba', 13.9724523, 120.6636961, '[[120.656368,13.978841],[120.655121,13.977569],[120.652301,13.976074],[120.650969,13.975459],[120.650014,13.973791],[120.650691,13.97217],[120.650008,13.970664],[120.652276,13.962337],[120.656325,13.962738],[120.658354,13.96109],[120.667716,13.959924],[120.669746,13.967445],[120.675011,13.974206],[120.675508,13.97915],[120.679826,13.985522],[120.670665,13.985022],[120.665845,13.98308],[120.664255,13.979342],[120.662162,13.978579],[120.659645,13.97839],[120.656368,13.978841]]'),
(11, 'Humayingan', 13.9967267, 120.6718058, '[[120.654686,14.01087],[120.657071,14.007338],[120.659176,14.003077],[120.658834,13.999617],[120.656778,13.997732],[120.65939,13.996215],[120.660909,13.986577],[120.664571,13.985557],[120.665661,13.983204],[120.67067,13.985202],[120.67983,13.985719],[120.685758,13.994114],[120.68613,13.999701],[120.685767,14.003736],[120.682042,14.008847],[120.677141,14.008913],[120.675193,14.00367],[120.666548,14.005978],[120.658648,14.008689],[120.654686,14.01087]]'),
(12, 'Malaruhatan', 14.0385226, 120.6653881, '[[120.653846,14.043736],[120.65396,14.041335],[120.654185,14.039806],[120.654971,14.03907],[120.654965,14.038571],[120.654971,14.037025],[120.657126,14.035212],[120.658289,14.033084],[120.658748,14.032356],[120.659708,14.031124],[120.661725,14.0289],[120.664971,14.02736],[120.665228,14.025705],[120.66963,14.024466],[120.672918,14.026331],[120.669631,14.027151],[120.672008,14.031567],[120.672213,14.033404],[120.672756,14.03897],[120.677037,14.044507],[120.676085,14.045826],[120.675678,14.050045],[120.673231,14.050176],[120.670649,14.047474],[120.669834,14.04543],[120.66698,14.045562],[120.666436,14.047474],[120.662223,14.048067],[120.657738,14.04688],[120.653846,14.043736]]'),
(13, 'Matabungkay', 13.9465891, 120.6252355, '[[120.618617,13.954853],[120.618855,13.952038],[120.616914,13.949176],[120.6149,13.947222],[120.614181,13.945338],[120.61472,13.944116],[120.614541,13.942755],[120.613606,13.942092],[120.614073,13.940138],[120.614972,13.940208],[120.616159,13.938707],[120.617219,13.937877],[120.617329,13.937325],[120.617054,13.936613],[120.616742,13.936382],[120.616833,13.935919],[120.616815,13.93558],[120.616668,13.934815],[120.61676,13.934121],[120.61687,13.933604],[120.61742,13.933587],[120.617732,13.933836],[120.617916,13.934708],[120.618814,13.934904],[120.61942,13.935954],[120.619676,13.935954],[120.620538,13.935189],[120.621419,13.935331],[120.621749,13.936026],[120.621566,13.937094],[120.622006,13.938126],[120.621731,13.938785],[120.621657,13.93923],[120.624491,13.938554],[120.626802,13.938999],[120.628967,13.940512],[120.630856,13.941366],[120.631608,13.942577],[120.631516,13.943574],[120.632323,13.943894],[120.633442,13.944054],[120.634946,13.944642],[120.636432,13.945069],[120.639678,13.948665],[120.639953,13.949359],[120.639568,13.949875],[120.638798,13.949786],[120.638101,13.949448],[120.637661,13.949911],[120.638064,13.950783],[120.63575,13.953196],[120.631222,13.954308],[120.631078,13.95432],[120.630081,13.955299],[120.624605,13.955684],[120.622345,13.955684],[120.619566,13.954704],[120.618689,13.954768],[120.618617,13.954853]]'),
(14, 'Prenza', 14.0124515, 120.6396729, '[[120.619201,14.023766],[120.618313,14.023579],[120.619471,14.022624],[120.6181,14.02032],[120.619886,14.01833],[120.615492,14.012735],[120.617415,14.008027],[120.619246,14.006428],[120.621809,14.006428],[120.626935,14.008116],[120.629865,14.006961],[120.635998,14.006339],[120.638104,13.992038],[120.649547,13.995769],[120.659251,13.996213],[120.656596,13.997635],[120.658702,13.999678],[120.659159,14.002875],[120.656962,14.007405],[120.654491,14.011047],[120.655772,14.012291],[120.656962,14.015666],[120.655498,14.017709],[120.656596,14.020728],[120.655864,14.022416],[120.656963,14.025953],[120.654986,14.026148],[120.654123,14.026076],[120.653036,14.026375],[120.650938,14.026991],[120.644694,14.026366],[120.64201,14.025622],[120.639119,14.024861],[120.639098,14.027424],[120.619606,14.025508],[120.619201,14.023766]]'),
(15, 'Lumaniag', 13.9952352, 120.6321466, '[[120.630167,13.98685],[120.630516,13.986452],[120.635189,13.986338],[120.638134,13.980203],[120.642651,13.979974],[120.641119,13.984013],[120.637899,13.992092],[120.635915,14.006203],[120.62987,14.006841],[120.626958,14.008048],[120.621814,14.006291],[120.623956,14.00522],[120.625429,14.004245],[120.626634,14.003206],[120.626701,14.002462],[120.625931,14.000156],[120.625362,13.999929],[120.624224,13.997006],[120.623019,13.996064],[120.622618,13.995155],[120.624124,13.994278],[120.624592,13.993044],[120.626232,13.991323],[120.627069,13.988952],[120.62891,13.988302],[120.630167,13.98685]]'),
(16, 'Luyahan', 13.9640345, 120.6176000, '[[120.63112,13.954353],[120.631783,13.959592],[120.630454,13.96163],[120.630266,13.96462],[120.628733,13.965673],[120.626289,13.965878],[120.622463,13.964687],[120.620456,13.965284],[120.620071,13.967166],[120.618061,13.967412],[120.61657,13.968514],[120.616781,13.968045],[120.614065,13.96784],[120.613823,13.967489],[120.612465,13.96825],[120.61156,13.968514],[120.611831,13.968865],[120.61156,13.969949],[120.608541,13.972497],[120.608119,13.973874],[120.607032,13.976451],[120.604678,13.977447],[120.604497,13.978706],[120.604135,13.979175],[120.603229,13.978238],[120.602867,13.974577],[120.602776,13.974196],[120.603622,13.973083],[120.603622,13.972351],[120.604135,13.971794],[120.604225,13.970388],[120.60489,13.968669],[120.60489,13.967308],[120.604816,13.966066],[120.60548,13.966185],[120.605923,13.965827],[120.606145,13.965349],[120.60644,13.965039],[120.607375,13.964943],[120.608089,13.964752],[120.608778,13.963869],[120.609639,13.963319],[120.611214,13.962651],[120.612745,13.962006],[120.613616,13.96202],[120.614753,13.961504],[120.616259,13.960587],[120.616363,13.959541],[120.616954,13.95795],[120.617648,13.956574],[120.618731,13.954799],[120.619572,13.954738],[120.622338,13.955728],[120.624568,13.955755],[120.630118,13.955319],[120.63112,13.954353]]'),
(17, 'Kapito', 14.0168316, 120.6704694, '[[120.65498,14.036953],[120.654982,14.036668],[120.655116,14.03612],[120.655305,14.035258],[120.655726,14.033587],[120.657283,14.03222],[120.657431,14.028336],[120.657022,14.025952],[120.65633,14.023784],[120.655932,14.02239],[120.65665,14.020792],[120.655839,14.01847],[120.655558,14.017727],[120.657049,14.015658],[120.656392,14.013894],[120.65581,14.012256],[120.654535,14.01103],[120.658172,14.009057],[120.666551,14.006107],[120.675152,14.003805],[120.677079,14.009057],[120.682121,14.008913],[120.685829,14.003805],[120.687083,14.022799],[120.673019,14.02623],[120.66976,14.024369],[120.665216,14.025674],[120.66492,14.027329],[120.661657,14.028911],[120.658691,14.032364],[120.656986,14.035313],[120.65498,14.036953]]'),
(18, 'San Diego', 14.0342584, 120.6338541, '[[120.654976,14.036599],[120.654329,14.035985],[120.653625,14.035951],[120.653866,14.033601],[120.65252,14.033459],[120.651138,14.033774],[120.649323,14.034077],[120.648018,14.034703],[120.645577,14.035694],[120.640694,14.035254],[120.636554,14.044462],[120.634392,14.044508],[120.633597,14.046711],[120.628998,14.04616],[120.628203,14.046931],[120.627146,14.047274],[120.625465,14.049288],[120.623334,14.051159],[120.622489,14.040955],[120.619557,14.035108],[120.614992,14.032976],[120.614654,14.033413],[120.614654,14.031664],[120.615781,14.030352],[120.615837,14.028329],[120.617246,14.028383],[120.618486,14.026579],[120.618599,14.025704],[120.619557,14.02554],[120.639168,14.027454],[120.639168,14.024939],[120.644804,14.02647],[120.651003,14.027071],[120.654159,14.026142],[120.655004,14.026196],[120.656977,14.025978],[120.657359,14.028281],[120.657305,14.030241],[120.657241,14.03127],[120.657216,14.03221],[120.655624,14.033632],[120.655117,14.035983],[120.654976,14.036599]]'),
(19, 'Puting Kahoy', 13.9867855, 120.6518731, '[[120.638116,13.991985],[120.64284,13.979905],[120.644824,13.980053],[120.648487,13.978868],[120.652608,13.977906],[120.65627,13.978942],[120.659704,13.978572],[120.662451,13.978794],[120.66413,13.979535],[120.665122,13.981386],[120.665503,13.982867],[120.665589,13.983253],[120.664509,13.98546],[120.660756,13.986563],[120.659352,13.996131],[120.64957,13.995674],[120.647722,13.995101],[120.638116,13.991985]]');

-- --------------------------------------------------------

--
-- Table structure for table `barangay_contact`
--

CREATE TABLE `barangay_contact` (
  `contact_id` bigint(20) UNSIGNED NOT NULL,
  `barangay_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `role` varchar(50) NOT NULL,
  `phone_number` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `barangay_contact`
--

INSERT INTO `barangay_contact` (`contact_id`, `barangay_id`, `name`, `role`, `phone_number`) VALUES
(1, 1, 'Hon. Captain Poblacion 1', 'Barangay Captain', '09941614944'),
(2, 1, 'Kagawad Poblacion 1', 'Barangay Councilor', '09804957679'),
(3, 2, 'Hon. Captain Poblacion 2', 'Barangay Captain', '09653057935'),
(4, 2, 'Kagawad Poblacion 2', 'Barangay Councilor', '09452624164'),
(5, 3, 'Hon. Captain Poblacion 3', 'Barangay Captain', '09563768225'),
(6, 3, 'Kagawad Poblacion 3', 'Barangay Councilor', '09222164528'),
(7, 4, 'Hon. Captain Poblacion 4', 'Barangay Captain', '09227847309'),
(8, 4, 'Kagawad Poblacion 4', 'Barangay Councilor', '09636981392'),
(9, 5, 'Hon. Captain Poblacion 5', 'Barangay Captain', '09469597425'),
(10, 5, 'Kagawad Poblacion 5', 'Barangay Councilor', '09441081504'),
(11, 7, 'Hon. Captain Bagong Pook', 'Barangay Captain', '09162314220'),
(12, 7, 'Kagawad Bagong Pook', 'Barangay Councilor', '09993712332'),
(13, 6, 'Hon. Captain Balibago', 'Barangay Captain', '09640012116'),
(14, 6, 'Kagawad Balibago', 'Barangay Councilor', '09687303418'),
(15, 8, 'Hon. Captain Binubusan', 'Barangay Captain', '09915598512'),
(16, 8, 'Kagawad Binubusan', 'Barangay Councilor', '09811341929'),
(17, 9, 'Hon. Captain Bungahan', 'Barangay Captain', '09179222521'),
(18, 9, 'Kagawad Bungahan', 'Barangay Councilor', '09786350508'),
(19, 10, 'Hon. Captain Cumba', 'Barangay Captain', '09840584298'),
(20, 10, 'Kagawad Cumba', 'Barangay Councilor', '09910533529'),
(21, 11, 'Hon. Captain Humayingan', 'Barangay Captain', '09389768922'),
(22, 11, 'Kagawad Humayingan', 'Barangay Councilor', '09100592007'),
(23, 17, 'Hon. Captain Kapito', 'Barangay Captain', '09682209737'),
(24, 17, 'Kagawad Kapito', 'Barangay Councilor', '09608125782'),
(25, 15, 'Hon. Captain Lumaniag', 'Barangay Captain', '09363704389'),
(26, 15, 'Kagawad Lumaniag', 'Barangay Councilor', '09352577395'),
(27, 16, 'Hon. Captain Luyahan', 'Barangay Captain', '09162205713'),
(28, 16, 'Kagawad Luyahan', 'Barangay Councilor', '09675147340'),
(29, 12, 'Hon. Captain Malaruhatan', 'Barangay Captain', '09620645876'),
(30, 12, 'Kagawad Malaruhatan', 'Barangay Councilor', '09903434467'),
(31, 13, 'Hon. Captain Matabungkay', 'Barangay Captain', '09210531004'),
(32, 13, 'Kagawad Matabungkay', 'Barangay Councilor', '09779616772'),
(33, 14, 'Hon. Captain Prenza', 'Barangay Captain', '09903441220'),
(34, 14, 'Kagawad Prenza', 'Barangay Councilor', '09932711263'),
(35, 19, 'Hon. Captain Puting Kahoy', 'Barangay Captain', '09585961787'),
(36, 19, 'Kagawad Puting Kahoy', 'Barangay Councilor', '09626279540'),
(37, 18, 'Hon. Captain San Diego', 'Barangay Captain', '09641251768'),
(38, 18, 'Kagawad San Diego', 'Barangay Councilor', '09801778301');

-- --------------------------------------------------------

--
-- Table structure for table `bfp_personnel_details`
--

CREATE TABLE `bfp_personnel_details` (
  `details_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `rank` varchar(100) NOT NULL,
  `station_assigned` varchar(150) NOT NULL DEFAULT 'BFP Lian Fire Station',
  `employee_number` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bfp_personnel_details`
--

INSERT INTO `bfp_personnel_details` (`details_id`, `user_id`, `rank`, `station_assigned`, `employee_number`) VALUES
(1, 1, 'Senior Fire Officer 4', 'BFP Lian Fire Station', 'EMP-001'),
(2, 2, 'Fire Officer 2', 'BFP Lian Fire Station', 'EMP-002'),
(3, 3, 'Inspector', 'BFP Lian Fire Station', 'EMP-003'),
(5, 5, 'Fire Officer 3', 'BFP Lian Fire Station', 'EMP-005'),
(6, 6, 'Inspector', 'BFP Lian Fire Station', 'EMP-006'),
(7, 7, 'Senior Fire Officer 3', 'BFP Lian Fire Station', 'EMP-007'),
(8, 8, 'Senior Fire Officer 2', 'BFP Lian Fire Station', 'EMP-008');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-08c34ff5ef350c8bbcfd5345c1c2e1f8', 'i:1;', 1787123339),
('laravel-cache-08c34ff5ef350c8bbcfd5345c1c2e1f8:timer', 'i:1787123339;', 1787123339),
('laravel-cache-19d85cfb89d91761fdc697cba09af487', 'i:1;', 1790126238),
('laravel-cache-19d85cfb89d91761fdc697cba09af487:timer', 'i:1790126238;', 1790126238),
('laravel-cache-2d66006fc3db4c44ea6c51aa97c541e4', 'i:2;', 1789123952),
('laravel-cache-2d66006fc3db4c44ea6c51aa97c541e4:timer', 'i:1789123952;', 1789123952),
('laravel-cache-356a192b7913b04c54574d18c28d46e6395428ab', 'i:1;', 1785302966),
('laravel-cache-356a192b7913b04c54574d18c28d46e6395428ab:timer', 'i:1785302966;', 1785302966),
('laravel-cache-4e5978c51af77583594afad541bd1cef', 'i:1;', 1789123962),
('laravel-cache-4e5978c51af77583594afad541bd1cef:timer', 'i:1789123962;', 1789123962),
('laravel-cache-admin@firesight.bfp.lian|127.0.0.1', 'i:2;', 1787123193),
('laravel-cache-admin@firesight.bfp.lian|127.0.0.1:timer', 'i:1787123193;', 1787123193),
('laravel-cache-admin@lian.gov.ph|127.0.0.1', 'i:2;', 1789123952),
('laravel-cache-admin@lian.gov.ph|127.0.0.1:timer', 'i:1789123952;', 1789123952),
('laravel-cache-admin2@gmail.com|127.0.0.1', 'i:1;', 1789123962),
('laravel-cache-admin2@gmail.com|127.0.0.1:timer', 'i:1789123962;', 1789123962),
('laravel-cache-aeaf0eb954cb0052ae501fe09997e56e', 'i:2;', 1789123933),
('laravel-cache-aeaf0eb954cb0052ae501fe09997e56e:timer', 'i:1789123933;', 1789123933);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `community_report`
--

CREATE TABLE `community_report` (
  `report_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `reporter_name` varchar(150) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `description` text DEFAULT NULL,
  `report_image` varchar(255) DEFAULT NULL COMMENT 'Storage disk path, not a raw blob',
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `status` enum('pending','verified','rejected','dispatched','resolved','completed') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `community_report`
--

INSERT INTO `community_report` (`report_id`, `user_id`, `reporter_name`, `contact_number`, `description`, `report_image`, `latitude`, `longitude`, `status`, `created_at`) VALUES
(1, 28, 'Leonora Malabanan', '09218212356', 'A parked vehicle caught fire in front of a small sari-sari store.', NULL, 13.97493455, 120.66218831, 'completed', '2026-07-24 22:06:17'),
(2, 28, 'Leonora Malabanan', '09218212356', 'Neighbors reported flames from a kitchen area of a residential house.', 'report_images/report_2.jpg', 13.99675937, 120.61339008, 'rejected', '2026-07-03 06:51:03'),
(4, 26, 'Manuel Pascual', '09679908599', 'Reported sparking from an electrical post, possible fire hazard.', 'report_images/report_4.jpg', 13.97334545, 120.64064618, 'dispatched', '2026-04-01 18:35:25'),
(5, 28, 'Leonora Malabanan', '09218212356', 'Smoke coming from a warehouse storing agricultural supplies.', NULL, 14.02768359, 120.67466851, 'resolved', '2026-06-22 23:00:25'),
(6, 21, 'Jennifer Villanueva', '09437484583', 'Reported burning smell and visible smoke near a tricycle terminal.', 'report_images/report_6.jpg', 14.00724207, 120.63785751, 'dispatched', '2026-02-23 04:50:34'),
(7, 11, 'Ricardo Malabanan', '09434027113', 'Neighbors reported flames from a kitchen area of a residential house.', 'report_images/report_7.jpg', 13.96636723, 120.64654411, 'dispatched', '2026-01-23 06:29:56'),
(8, 29, 'Alfredo Pascual', '09419953851', 'Thick black smoke seen coming from a residential structure near the main road.', 'report_images/report_8.jpg', 13.97988122, 120.67290650, 'resolved', '2026-02-05 23:26:57'),
(9, 17, 'Ernesto Umali', '09436665249', 'Reported burning smell and visible smoke near a tricycle terminal.', 'report_images/report_9.jpg', 13.99655194, 120.64544254, 'resolved', '2026-07-23 01:43:21'),
(10, 24, 'Manuel Flores', '09949925830', 'A parked vehicle caught fire in front of a small sari-sari store.', NULL, 14.01600026, 120.66556227, 'verified', '2026-05-05 20:39:34'),
(11, 20, 'Kristine Aguilar', '09210062915', 'Smoke coming from a warehouse storing agricultural supplies.', 'report_images/report_11.jpg', 13.99172165, 120.66978776, 'pending', '2026-04-23 01:32:34'),
(13, 14, 'Ana Umali', '09394703907', 'Fire reported at a backyard where dry leaves were being burned, spreading to a fence.', NULL, 13.99507082, 120.67058645, 'resolved', '2026-07-14 20:07:09'),
(14, 26, 'Manuel Pascual', '09679908599', 'Grass fire spreading quickly along the roadside, close to nearby houses.', NULL, 13.96828824, 120.67994619, 'resolved', '2026-02-27 09:58:22'),
(15, 16, 'Michael Fernandez', '09541735568', 'Reported sparking from an electrical post, possible fire hazard.', 'report_images/report_15.jpg', 13.99141691, 120.64110188, 'resolved', '2026-03-20 13:18:23'),
(16, 9, 'Jennifer Rivera', '09316944844', 'Smoke coming from a warehouse storing agricultural supplies.', NULL, 13.98223820, 120.66380530, 'dispatched', '2026-06-19 18:13:37'),
(17, 32, 'Juan Corpuz', '09922751234', 'Short circuit suspected as source of small fire inside a sari-sari store.', NULL, 14.00802164, 120.64150036, 'verified', '2026-05-29 13:31:23'),
(18, 27, 'Rosario Bautista', '09823715057', 'Possible faulty wiring causing sparks in an old residential building.', 'report_images/report_18.jpg', 14.00024269, 120.66109817, 'verified', '2026-06-29 13:36:18'),
(19, 25, 'Leonora Navarro', '09776563708', 'Reported sparking from an electrical post, possible fire hazard.', 'report_images/report_19.jpg', 13.96869717, 120.64308714, 'resolved', '2026-06-12 10:30:56'),
(20, 30, 'Rosario Umali', '09726567501', 'A parked vehicle caught fire in front of a small sari-sari store.', NULL, 13.99124541, 120.67561971, 'dispatched', '2026-04-26 08:12:01'),
(21, 22, 'Mark Navarro', '09920854579', 'Reported burning smell and visible smoke near a tricycle terminal.', NULL, 14.00293870, 120.64420870, 'resolved', '2026-05-27 00:58:07'),
(22, 19, 'Rodrigo Rivera', '09837194506', 'A parked vehicle caught fire in front of a small sari-sari store.', 'report_images/report_22.jpg', 13.98226494, 120.61481587, 'resolved', '2026-06-28 01:46:57'),
(23, 33, 'Kristine Mendoza', '09695119047', 'Grass fire spreading quickly along the roadside, close to nearby houses.', 'report_images/report_23.jpg', 13.96560550, 120.65158694, 'dispatched', '2026-03-06 17:46:01'),
(24, 15, 'Angelica Rosales', '09258449460', 'Short circuit suspected as source of small fire inside a sari-sari store.', 'report_images/report_24.jpg', 13.98223803, 120.67499445, 'resolved', '2026-06-25 08:15:25'),
(25, 14, 'Ana Umali', '09394703907', 'Open burning of garbage that spread to nearby vegetation.', NULL, 14.01919342, 120.65776549, 'resolved', '2026-07-14 17:58:13'),
(26, 9, 'Jennifer Rivera', '09316944844', 'Reported burning smell and visible smoke near a tricycle terminal.', 'report_images/report_26.jpg', 14.01811177, 120.62745341, 'resolved', '2026-04-24 16:42:41'),
(27, 12, 'Erlinda Garcia', '09556448196', 'Possible faulty wiring causing sparks in an old residential building.', 'report_images/report_27.jpg', 14.00095954, 120.62542612, 'resolved', '2026-06-23 22:47:09'),
(28, 30, 'Rosario Umali', '09726567501', 'Reported burning smell and visible smoke near a tricycle terminal.', 'report_images/report_28.jpg', 14.02043933, 120.64290568, 'verified', '2026-04-28 11:43:51'),
(29, 22, 'Mark Navarro', '09920854579', 'Fire reported at a backyard where dry leaves were being burned, spreading to a fence.', 'report_images/report_29.jpg', 13.96786564, 120.63001674, 'verified', '2026-06-01 15:24:37'),
(30, 23, 'Rolando Villanueva', '09840256940', 'Short circuit suspected as source of small fire inside a sari-sari store.', NULL, 14.01086838, 120.64706801, 'completed', '2026-07-25 03:20:28'),
(31, 13, 'Marilou Malabanan', '09538302652', 'Short circuit suspected as source of small fire inside a sari-sari store.', 'report_images/report_31.jpg', 13.96734183, 120.63269120, 'dispatched', '2026-06-12 03:12:38'),
(32, 18, 'Josefina Marasigan', '09932893941', 'Neighbors reported flames from a kitchen area of a residential house.', 'report_images/report_32.jpg', 14.02343380, 120.66470917, 'resolved', '2026-06-18 13:18:53'),
(33, 16, 'Michael Fernandez', '09541735568', 'Reported burning smell and visible smoke near a tricycle terminal.', NULL, 13.97168159, 120.66350872, 'resolved', '2026-05-27 01:54:06'),
(34, 32, 'Juan Corpuz', '09922751234', 'Reported sparking from an electrical post, possible fire hazard.', NULL, 14.02517465, 120.62275530, 'verified', '2026-05-21 16:05:14'),
(35, 9, 'Jennifer Rivera', '09316944844', 'Reported sparking from an electrical post, possible fire hazard.', 'report_images/report_35.jpg', 14.01287669, 120.62991391, 'resolved', '2026-05-16 19:24:45'),
(37, 23, 'Rolando Villanueva', '09840256940', 'Neighbors reported flames from a kitchen area of a residential house.', NULL, 13.98483741, 120.66465091, 'verified', '2026-07-02 18:35:40'),
(38, 21, 'Jennifer Villanueva', '09437484583', 'Neighbors reported flames from a kitchen area of a residential house.', NULL, 13.98310920, 120.61020656, 'resolved', '2026-04-08 21:22:55'),
(39, 21, 'Jennifer Villanueva', '09437484583', 'Reported burning smell and visible smoke near a tricycle terminal.', NULL, 14.02877473, 120.64521618, 'resolved', '2026-02-22 02:27:14'),
(40, 17, 'Ernesto Umali', '09436665249', 'Reported sparking from an electrical post, possible fire hazard.', 'report_images/report_40.jpg', 14.00273833, 120.67876629, 'completed', '2026-05-28 13:44:34'),
(41, 32, 'Juan Corpuz', '09922751234', 'Neighbors reported flames from a kitchen area of a residential house.', 'report_images/report_41.jpg', 13.99336560, 120.67881769, 'pending', '2026-05-24 04:47:41'),
(43, 22, 'Mark Navarro', '09920854579', 'Grass fire spreading quickly along the roadside, close to nearby houses.', NULL, 14.02667684, 120.67405781, 'resolved', '2026-07-10 14:48:54'),
(44, 32, 'Juan Corpuz', '09922751234', 'Fire reported at a backyard where dry leaves were being burned, spreading to a fence.', 'report_images/report_44.jpg', 13.99796296, 120.65501697, 'verified', '2026-06-13 16:49:00'),
(45, 15, 'Angelica Rosales', '09258449460', 'Smoke coming from a warehouse storing agricultural supplies.', NULL, 14.00822041, 120.63343590, 'resolved', '2026-06-18 12:32:46'),
(47, 28, 'Leonora Malabanan', '09218212356', 'Reported burning smell and visible smoke near a tricycle terminal.', 'report_images/report_47.jpg', 13.98594620, 120.63813395, 'resolved', '2026-07-23 01:24:46'),
(48, 32, 'Juan Corpuz', '09922751234', 'Reported sparking from an electrical post, possible fire hazard.', 'report_images/report_48.jpg', 14.00782530, 120.62628398, 'resolved', '2026-06-21 09:03:14'),
(49, 33, 'Kristine Mendoza', '09695119047', 'A parked vehicle caught fire in front of a small sari-sari store.', 'report_images/report_49.jpg', 14.00873172, 120.62184805, 'verified', '2026-02-20 01:39:04'),
(50, 24, 'Manuel Flores', '09949925830', 'Fire broke out near a cooking area during a community event.', NULL, 14.02023295, 120.61046212, 'rejected', '2026-05-24 19:03:46'),
(51, 21, 'Jennifer Villanueva', '09437484583', 'Open burning of garbage that spread to nearby vegetation.', NULL, 13.99097744, 120.61606785, 'resolved', '2026-07-23 01:01:07'),
(52, 33, 'Kristine Mendoza', '09695119047', 'Grass fire spreading quickly along the roadside, close to nearby houses.', 'report_images/report_52.jpg', 14.01559733, 120.62141309, 'resolved', '2026-06-30 11:00:16'),
(53, 10, 'Jennifer Garcia', '09249585092', 'Short circuit suspected as source of small fire inside a sari-sari store.', NULL, 13.98119789, 120.66862523, 'dispatched', '2026-07-16 15:52:33'),
(54, 26, 'Manuel Pascual', '09679908599', 'Fire broke out near a cooking area during a community event.', NULL, 13.98562437, 120.66164375, 'rejected', '2026-07-17 13:32:33'),
(55, 25, 'Leonora Navarro', '09776563708', 'Smoke coming from a warehouse storing agricultural supplies.', NULL, 14.01898700, 120.64609040, 'resolved', '2026-07-15 05:06:13'),
(56, 23, 'Rolando Villanueva', '09840256940', 'Fire reported at a backyard where dry leaves were being burned, spreading to a fence.', 'report_images/report_56.jpg', 14.01386549, 120.66798972, 'verified', '2026-07-21 01:50:15'),
(58, 26, 'Manuel Pascual', '09679908599', 'Grass fire spreading quickly along the roadside, close to nearby houses.', 'report_images/report_58.jpg', 13.97399431, 120.62234461, 'resolved', '2026-06-26 11:29:19'),
(59, 22, 'Mark Navarro', '09920854579', 'Smoke coming from a warehouse storing agricultural supplies.', 'report_images/report_59.jpg', 13.98650729, 120.63994432, 'resolved', '2026-06-17 00:00:56'),
(60, 33, 'Kristine Mendoza', '09695119047', 'Fire broke out near a cooking area during a community event.', 'report_images/report_60.jpg', 14.02649403, 120.60646490, 'resolved', '2026-06-07 02:43:55');

-- --------------------------------------------------------

--
-- Table structure for table `duty_schedule`
--

CREATE TABLE `duty_schedule` (
  `schedule_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `duty_date` date NOT NULL,
  `time_start` time NOT NULL,
  `time_end` time NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `duty_schedule`
--

INSERT INTO `duty_schedule` (`schedule_id`, `user_id`, `duty_date`, `time_start`, `time_end`, `created_by`, `created_at`, `updated_at`) VALUES
(2, 6, '2026-09-19', '18:00:00', '06:00:00', 34, '2026-09-19 13:55:25', '2026-09-19 13:55:25'),
(3, 5, '2026-09-19', '18:00:00', '06:00:00', 34, '2026-09-19 13:55:41', '2026-09-19 13:55:49'),
(4, 2, '2026-09-19', '18:00:00', '06:00:00', 34, '2026-09-19 13:55:57', '2026-09-19 13:55:57'),
(5, 6, '2026-09-20', '06:00:00', '18:00:00', 34, '2026-09-19 13:56:08', '2026-09-19 13:56:08'),
(6, 6, '2026-09-21', '18:00:00', '06:00:00', 34, '2026-09-19 13:56:20', '2026-09-19 13:56:20'),
(7, 6, '2026-09-22', '06:00:00', '18:00:00', 34, '2026-09-19 13:56:22', '2026-09-19 13:56:22'),
(8, 6, '2026-09-23', '06:00:00', '18:00:00', 34, '2026-09-19 13:56:24', '2026-09-19 13:56:24'),
(9, 6, '2026-09-24', '06:00:00', '18:00:00', 34, '2026-09-19 13:56:26', '2026-09-19 13:56:26'),
(10, 6, '2026-09-25', '06:00:00', '18:00:00', 34, '2026-09-19 13:56:28', '2026-09-19 13:56:28'),
(11, 6, '2026-09-26', '06:00:00', '18:00:00', 34, '2026-09-19 13:56:30', '2026-09-19 13:56:30'),
(12, 6, '2026-09-27', '18:00:00', '06:00:00', 34, '2026-09-19 13:56:33', '2026-09-19 13:56:33');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` varchar(255) NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `incident_record`
--

CREATE TABLE `incident_record` (
  `incident_id` bigint(20) UNSIGNED NOT NULL,
  `report_id` bigint(20) UNSIGNED NOT NULL,
  `barangay_id` bigint(20) UNSIGNED NOT NULL,
  `data_time` datetime NOT NULL,
  `incident_type` enum('structural','grass','electrical','vehicular','other') DEFAULT NULL,
  `severity_level` enum('low','moderate','high','critical') DEFAULT NULL,
  `cause_of_fire` varchar(150) DEFAULT NULL,
  `casualties` int(10) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `incident_record`
--

INSERT INTO `incident_record` (`incident_id`, `report_id`, `barangay_id`, `data_time`, `incident_type`, `severity_level`, `cause_of_fire`, `casualties`, `notes`) VALUES
(1, 1, 10, '2026-07-25 06:33:17', 'electrical', 'high', NULL, 0, NULL),
(3, 4, 8, '2026-04-02 03:09:25', 'grass', 'critical', 'Gas leak', 0, 'Fire was extinguished using indigenous methods before BFP arrival.'),
(4, 5, 7, '2026-06-23 07:41:25', 'structural', 'low', 'Candle left unattended', 0, 'Follow-up inspection scheduled for the affected property.'),
(5, 6, 14, '2026-02-23 12:58:34', 'electrical', 'moderate', 'Lit cigarette', 0, 'Barangay tanod assisted in crowd control during the response.'),
(6, 7, 8, '2026-01-23 14:51:56', 'vehicular', 'low', 'Faulty electrical wiring', 0, 'Fire was contained before spreading to adjacent structures.'),
(7, 8, 10, '2026-02-06 07:45:57', 'structural', 'high', 'Unattended cooking', 0, 'Minor property damage reported, no injuries recorded.'),
(8, 9, 14, '2026-07-23 09:49:21', 'other', 'low', 'Open burning of trash', 0, 'Residents were evacuated safely before responders arrived.'),
(9, 10, 17, '2026-05-06 04:57:34', 'vehicular', 'moderate', 'Overheated appliance', 0, 'Residents were evacuated safely before responders arrived.'),
(11, 14, 13, '2026-02-27 18:15:22', 'structural', 'moderate', 'Lit cigarette', 0, 'Investigation ongoing to determine exact cause.'),
(12, 15, 19, '2026-03-20 21:59:23', 'structural', 'low', 'Candle left unattended', 0, 'Residents were evacuated safely before responders arrived.'),
(13, 16, 19, '2026-06-20 02:39:37', 'structural', 'high', 'Firecracker mishandling', 0, 'Barangay tanod assisted in crowd control during the response.'),
(14, 17, 14, '2026-05-29 21:45:23', 'vehicular', 'low', 'Gas leak', 1, 'Investigation ongoing to determine exact cause.'),
(15, 19, 8, '2026-06-12 19:05:56', 'vehicular', 'moderate', 'Undetermined', 0, 'Minor property damage reported, no injuries recorded.'),
(16, 20, 11, '2026-04-26 16:34:01', 'vehicular', 'low', 'Firecracker mishandling', 0, 'Fire was extinguished using indigenous methods before BFP arrival.'),
(17, 21, 14, '2026-05-27 09:04:07', 'vehicular', 'high', 'Open burning of trash', 0, 'Barangay tanod assisted in crowd control during the response.'),
(18, 22, 9, '2026-06-28 10:12:57', 'electrical', 'high', 'Electrical short circuit', 0, 'Minor property damage reported, no injuries recorded.'),
(19, 23, 10, '2026-03-07 02:06:01', 'vehicular', 'moderate', 'Lit cigarette', 0, 'Follow-up inspection scheduled for the affected property.'),
(20, 24, 10, '2026-06-25 16:21:25', 'structural', 'low', 'Candle left unattended', 0, 'Investigation ongoing to determine exact cause.'),
(21, 25, 17, '2026-07-15 02:26:13', 'vehicular', 'moderate', 'Overheated appliance', 0, 'Barangay tanod assisted in crowd control during the response.'),
(22, 26, 14, '2026-04-25 01:16:41', 'electrical', 'low', 'Lit cigarette', 0, 'Minor property damage reported, no injuries recorded.'),
(23, 27, 11, '2026-06-24 06:59:09', 'other', 'critical', 'Open burning of trash', 0, 'Follow-up inspection scheduled for the affected property.'),
(24, 28, 14, '2026-04-28 20:25:51', 'other', 'moderate', 'Faulty electrical wiring', 0, 'Investigation ongoing to determine exact cause.'),
(25, 31, 8, '2026-06-12 11:40:38', 'grass', 'low', 'Gas leak', 0, 'Fire was contained before spreading to adjacent structures.'),
(26, 32, 17, '2026-06-18 21:58:53', 'electrical', 'moderate', 'Open burning of trash', 0, 'Follow-up inspection scheduled for the affected property.'),
(27, 33, 10, '2026-05-27 09:59:06', 'other', 'low', 'Firecracker mishandling', 0, 'Fire truck from BFP Lian responded within 15 minutes.'),
(28, 34, 14, '2026-05-22 00:26:14', 'vehicular', 'low', 'Faulty electrical wiring', 0, 'Residents were evacuated safely before responders arrived.'),
(29, 35, 14, '2026-05-17 04:09:45', 'structural', 'low', 'Undetermined', 1, 'Residents were evacuated safely before responders arrived.'),
(31, 37, 19, '2026-07-03 03:04:40', 'vehicular', 'critical', 'Electrical short circuit', 1, 'Fire was extinguished using indigenous methods before BFP arrival.'),
(32, 38, 10, '2026-04-09 06:03:55', 'other', 'low', 'Faulty electrical wiring', 1, 'Minor property damage reported, no injuries recorded.'),
(33, 39, 18, '2026-02-22 10:48:14', 'structural', 'low', 'Open burning of trash', 0, 'Fire truck from BFP Lian responded within 15 minutes.'),
(35, 43, 7, '2026-07-10 23:11:54', 'vehicular', 'low', 'Lit cigarette', 1, 'Minor property damage reported, no injuries recorded.'),
(36, 44, 14, '2026-06-14 01:01:00', 'other', 'low', 'Open burning of trash', 1, 'Fire truck from BFP Lian responded within 15 minutes.'),
(37, 45, 14, '2026-06-18 20:40:46', 'grass', 'high', 'Undetermined', 0, 'Investigation ongoing to determine exact cause.'),
(39, 47, 15, '2026-07-23 09:34:46', 'other', 'low', 'Candle left unattended', 0, 'Investigation ongoing to determine exact cause.'),
(40, 48, 1, '2026-06-21 17:13:14', 'grass', 'critical', 'Undetermined', 0, 'Fire was contained before spreading to adjacent structures.'),
(41, 49, 14, '2026-02-20 10:20:04', 'structural', 'high', 'Open burning of trash', 0, 'Follow-up inspection scheduled for the affected property.'),
(42, 51, 9, '2026-07-23 09:17:07', 'other', 'moderate', 'Firecracker mishandling', 1, 'Follow-up inspection scheduled for the affected property.'),
(43, 52, 14, '2026-06-30 19:31:16', 'electrical', 'low', 'Faulty electrical wiring', 1, 'Barangay tanod assisted in crowd control during the response.'),
(44, 53, 10, '2026-07-17 00:28:33', 'electrical', 'moderate', 'Candle left unattended', 0, 'Fire was contained before spreading to adjacent structures.'),
(45, 55, 14, '2026-07-15 13:16:13', 'electrical', 'low', 'Faulty electrical wiring', 2, 'Fire was extinguished using indigenous methods before BFP arrival.'),
(46, 56, 17, '2026-07-21 09:55:15', 'other', 'moderate', 'Unattended cooking', 0, 'Barangay tanod assisted in crowd control during the response.'),
(48, 58, 10, '2026-06-26 20:02:19', 'vehicular', 'low', 'Undetermined', 0, 'Fire truck from BFP Lian responded within 15 minutes.'),
(49, 60, 10, '2026-06-07 11:23:55', 'structural', 'moderate', 'Faulty electrical wiring', 0, 'Residents were evacuated safely before responders arrived.'),
(50, 30, 14, '2026-07-27 11:06:20', 'grass', 'low', 'asd', 0, NULL),
(51, 13, 11, '2026-07-28 15:12:17', 'other', 'low', NULL, 0, NULL),
(52, 18, 11, '2026-07-29 05:35:26', 'electrical', 'moderate', NULL, 0, NULL),
(53, 59, 15, '2026-07-29 07:32:20', 'other', 'moderate', NULL, 0, NULL),
(54, 29, 8, '2026-09-15 03:12:19', NULL, NULL, NULL, NULL, NULL),
(55, 40, 11, '2026-09-23 09:18:23', 'vehicular', 'moderate', NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` smallint(5) UNSIGNED NOT NULL,
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
(4, '2024_01_01_000000_create_passkeys_table', 1),
(5, '2025_08_14_170933_add_two_factor_columns_to_users_table', 1),
(6, '2026_07_26_100000_create_barangays_table', 1),
(7, '2026_07_26_100001_update_users_table_for_firesight', 1),
(8, '2026_07_26_100002_create_bfp_personnel_details_table', 1),
(9, '2026_07_26_100003_create_community_reports_table', 1),
(10, '2026_07_26_100004_create_incident_records_table', 1),
(11, '2026_07_26_100005_create_report_links_table', 1),
(12, '2026_07_26_100006_create_risk_assessments_table', 1),
(13, '2026_07_26_100007_create_announcements_table', 1),
(14, '2026_07_26_100008_create_notifications_table', 1),
(15, '2026_07_26_100009_add_coordinates_to_barangay_table', 1),
(16, '2026_07_27_000001_add_boundary_to_barangay_table', 2),
(17, '2026_07_27_000002_add_status_to_users_table', 3),
(18, '2026_09_12_000000_add_completed_status_to_incident_workflow', 4),
(19, '2026_09_16_130000_create_duty_schedules_table', 5),
(20, '2026_09_23_000000_create_barangay_contact_table', 6);

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `notification_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `notification_type` enum('incident_alert','status_update','system','reminder') NOT NULL DEFAULT 'system',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notification`
--

INSERT INTO `notification` (`notification_id`, `user_id`, `title`, `message`, `notification_type`, `is_read`, `created_at`) VALUES
(1, 28, 'Report Status Update', 'Fire responders have been dispatched to the location you reported.', 'status_update', 1, '2026-07-24 22:47:17'),
(2, 28, 'Report Status Update', 'Your fire incident report could not be verified and has been closed.', 'status_update', 0, '2026-07-03 07:55:03'),
(3, 16, 'Report Status Update', 'Fire responders have been dispatched to the location you reported.', 'status_update', 0, '2026-03-22 16:10:58'),
(4, 28, 'Report Status Update', 'The fire incident you reported has been marked as resolved.', 'status_update', 0, '2026-06-23 00:02:25'),
(5, 11, 'Report Status Update', 'Fire responders have been dispatched to the location you reported.', 'status_update', 0, '2026-01-23 09:09:56'),
(6, 17, 'Report Status Update', 'The fire incident you reported has been marked as resolved.', 'status_update', 0, '2026-07-23 05:11:21'),
(7, 24, 'Report Status Update', 'Your fire incident report has been verified by BFP Lian personnel.', 'status_update', 0, '2026-05-05 23:21:34'),
(8, 32, 'Report Status Update', 'Your fire incident report has been verified by BFP Lian personnel.', 'status_update', 0, '2026-07-13 10:26:15'),
(9, 26, 'Report Status Update', 'The fire incident you reported has been marked as resolved.', 'status_update', 0, '2026-02-27 13:32:22'),
(10, 16, 'Report Status Update', 'The fire incident you reported has been marked as resolved.', 'status_update', 0, '2026-03-20 17:09:23'),
(11, 9, 'Report Status Update', 'Fire responders have been dispatched to the location you reported.', 'status_update', 0, '2026-06-19 19:54:37'),
(12, 32, 'Report Status Update', 'Your fire incident report has been verified by BFP Lian personnel.', 'status_update', 0, '2026-05-29 13:45:23'),
(13, 25, 'Report Status Update', 'The fire incident you reported has been marked as resolved.', 'status_update', 1, '2026-06-12 11:12:56'),
(14, 30, 'Report Status Update', 'Fire responders have been dispatched to the location you reported.', 'status_update', 0, '2026-04-26 11:32:01'),
(15, 22, 'Report Status Update', 'The fire incident you reported has been marked as resolved.', 'status_update', 1, '2026-05-27 04:27:07'),
(16, 19, 'Report Status Update', 'The fire incident you reported has been marked as resolved.', 'status_update', 1, '2026-06-28 02:23:57'),
(17, 33, 'Report Status Update', 'Fire responders have been dispatched to the location you reported.', 'status_update', 0, '2026-03-06 20:33:01'),
(18, 15, 'Report Status Update', 'The fire incident you reported has been marked as resolved.', 'status_update', 1, '2026-06-25 12:06:25'),
(19, 14, 'Report Status Update', 'The fire incident you reported has been marked as resolved.', 'status_update', 0, '2026-07-14 18:15:13'),
(20, 30, 'Report Status Update', 'Your fire incident report has been verified by BFP Lian personnel.', 'status_update', 1, '2026-04-28 12:20:51'),
(21, 13, 'Report Status Update', 'Fire responders have been dispatched to the location you reported.', 'status_update', 0, '2026-06-12 05:15:38'),
(22, 16, 'Report Status Update', 'The fire incident you reported has been marked as resolved.', 'status_update', 0, '2026-05-27 02:41:06'),
(23, 32, 'Report Status Update', 'Your fire incident report has been verified by BFP Lian personnel.', 'status_update', 1, '2026-05-21 18:54:14'),
(24, 9, 'Report Status Update', 'The fire incident you reported has been marked as resolved.', 'status_update', 1, '2026-05-16 22:06:45'),
(25, 31, 'Report Status Update', 'Fire responders have been dispatched to the location you reported.', 'status_update', 1, '2026-07-02 15:33:30'),
(26, 23, 'Report Status Update', 'Your fire incident report has been verified by BFP Lian personnel.', 'status_update', 0, '2026-07-02 21:44:40'),
(27, 21, 'Report Status Update', 'The fire incident you reported has been marked as resolved.', 'status_update', 0, '2026-02-22 05:53:14'),
(28, 31, 'Report Status Update', 'Fire responders have been dispatched to the location you reported.', 'status_update', 0, '2026-07-25 13:35:40'),
(29, 22, 'Report Status Update', 'The fire incident you reported has been marked as resolved.', 'status_update', 1, '2026-07-10 18:29:54'),
(30, 32, 'Report Status Update', 'Your fire incident report has been verified by BFP Lian personnel.', 'status_update', 0, '2026-06-13 20:05:00'),
(31, 15, 'Report Status Update', 'The fire incident you reported has been marked as resolved.', 'status_update', 1, '2026-06-18 14:02:46'),
(32, 18, 'Report Status Update', 'Your fire incident report has been verified by BFP Lian personnel.', 'status_update', 1, '2026-07-02 14:02:37'),
(33, 28, 'Report Status Update', 'The fire incident you reported has been marked as resolved.', 'status_update', 0, '2026-07-23 05:06:46'),
(34, 32, 'Report Status Update', 'The fire incident you reported has been marked as resolved.', 'status_update', 1, '2026-06-21 09:37:14'),
(35, 24, 'Report Status Update', 'Your fire incident report could not be verified and has been closed.', 'status_update', 1, '2026-05-24 21:43:46'),
(36, 21, 'Report Status Update', 'The fire incident you reported has been marked as resolved.', 'status_update', 1, '2026-07-23 02:56:07'),
(37, 26, 'Report Status Update', 'Your fire incident report could not be verified and has been closed.', 'status_update', 0, '2026-07-17 16:46:33'),
(38, 23, 'Report Status Update', 'Your fire incident report has been verified by BFP Lian personnel.', 'status_update', 0, '2026-07-21 03:30:15'),
(39, 29, 'Report Status Update', 'Your fire incident report has been verified by BFP Lian personnel.', 'status_update', 0, '2026-04-25 00:38:45'),
(40, 26, 'Report Status Update', 'The fire incident you reported has been marked as resolved.', 'status_update', 0, '2026-06-26 12:36:19'),
(41, 33, 'Report Status Update', 'The fire incident you reported has been marked as resolved.', 'status_update', 1, '2026-06-07 05:15:55'),
(42, 5, 'New Incident Report', 'A new community fire report has been submitted near coordinates 13.97493455, 120.66218831.', 'incident_alert', 0, '2026-07-24 22:25:17'),
(43, 5, 'New Incident Report', 'A new community fire report has been submitted near coordinates 13.97988122, 120.6729065.', 'incident_alert', 0, '2026-02-05 23:27:57'),
(44, 5, 'New Incident Report', 'A new community fire report has been submitted near coordinates 13.99655194, 120.64544254.', 'incident_alert', 0, '2026-07-23 02:13:21'),
(45, 3, 'New Incident Report', 'A new community fire report has been submitted near coordinates 14.01600026, 120.66556227.', 'incident_alert', 0, '2026-05-05 21:07:34'),
(46, 3, 'New Incident Report', 'A new community fire report has been submitted near coordinates 14.01703851, 120.61319518.', 'incident_alert', 1, '2026-07-13 10:03:15'),
(48, 3, 'New Incident Report', 'A new community fire report has been submitted near coordinates 13.99141691, 120.64110188.', 'incident_alert', 0, '2026-03-20 13:30:23'),
(49, 1, 'New Incident Report', 'A new community fire report has been submitted near coordinates 13.96869717, 120.64308714.', 'incident_alert', 1, '2026-06-12 10:35:56'),
(50, 2, 'New Incident Report', 'A new community fire report has been submitted near coordinates 14.0029387, 120.6442087.', 'incident_alert', 0, '2026-05-27 01:07:07'),
(51, 8, 'New Incident Report', 'A new community fire report has been submitted near coordinates 13.9656055, 120.65158694.', 'incident_alert', 0, '2026-03-06 18:00:01'),
(52, 2, 'New Incident Report', 'A new community fire report has been submitted near coordinates 13.98223803, 120.67499445.', 'incident_alert', 0, '2026-06-25 08:27:25'),
(53, 8, 'New Incident Report', 'A new community fire report has been submitted near coordinates 14.01919342, 120.65776549.', 'incident_alert', 1, '2026-07-14 18:15:13'),
(56, 2, 'New Incident Report', 'A new community fire report has been submitted near coordinates 14.01086838, 120.64706801.', 'incident_alert', 1, '2026-07-25 03:21:28'),
(57, 7, 'New Incident Report', 'A new community fire report has been submitted near coordinates 14.0234338, 120.66470917.', 'incident_alert', 1, '2026-06-18 13:36:53'),
(58, 6, 'New Incident Report', 'A new community fire report has been submitted near coordinates 13.97168159, 120.66350872.', 'incident_alert', 0, '2026-05-27 01:57:06'),
(59, 1, 'New Incident Report', 'A new community fire report has been submitted near coordinates 14.01287669, 120.62991391.', 'incident_alert', 1, '2026-05-16 19:39:45'),
(60, 2, 'New Incident Report', 'A new community fire report has been submitted near coordinates 14.00273833, 120.67876629.', 'incident_alert', 0, '2026-05-28 13:57:34'),
(61, 8, 'New Incident Report', 'A new community fire report has been submitted near coordinates 13.96510993, 120.67441814.', 'incident_alert', 1, '2026-07-25 10:05:40'),
(62, 2, 'New Incident Report', 'A new community fire report has been submitted near coordinates 13.99796296, 120.65501697.', 'incident_alert', 0, '2026-06-13 16:57:00'),
(63, 5, 'New Incident Report', 'A new community fire report has been submitted near coordinates 13.9859462, 120.63813395.', 'incident_alert', 0, '2026-07-23 01:48:46'),
(64, 3, 'New Incident Report', 'A new community fire report has been submitted near coordinates 14.0078253, 120.62628398.', 'incident_alert', 1, '2026-06-21 09:06:14'),
(65, 6, 'New Incident Report', 'A new community fire report has been submitted near coordinates 13.99097744, 120.61606785.', 'incident_alert', 1, '2026-07-23 01:13:07'),
(66, 2, 'New Incident Report', 'A new community fire report has been submitted near coordinates 13.98562437, 120.66164375.', 'incident_alert', 1, '2026-07-17 13:37:33'),
(67, 3, 'New Incident Report', 'A new community fire report has been submitted near coordinates 14.018987, 120.6460904.', 'incident_alert', 1, '2026-07-15 05:31:13'),
(68, 8, 'New Incident Report', 'A new community fire report has been submitted near coordinates 14.01386549, 120.66798972.', 'incident_alert', 1, '2026-07-21 02:05:15'),
(69, 6, 'New Incident Report', 'A new community fire report has been submitted near coordinates 13.97399431, 120.62234461.', 'incident_alert', 1, '2026-06-26 11:57:19'),
(70, 8, 'New Incident Report', 'A new community fire report has been submitted near coordinates 13.98650729, 120.63994432.', 'incident_alert', 0, '2026-06-17 00:03:56'),
(71, 5, 'New Incident Report', 'A new community fire report has been submitted near coordinates 14.02649403, 120.6064649.', 'incident_alert', 0, '2026-06-07 03:09:55'),
(72, 10, 'Welcome to FireSight', 'Thank you for registering with FireSight. Stay updated on fire safety advisories in your barangay.', 'system', 1, '2026-07-01 16:28:53'),
(73, 12, 'Fire Safety Reminder', 'Remember to inspect your home\'s electrical outlets and keep flammable materials away from heat sources.', 'reminder', 0, '2026-02-19 12:03:47'),
(74, 22, 'Fire Safety Reminder', 'Remember to inspect your home\'s electrical outlets and keep flammable materials away from heat sources.', 'reminder', 0, '2026-06-24 21:16:50'),
(75, 28, 'Welcome to FireSight', 'Thank you for registering with FireSight. Stay updated on fire safety advisories in your barangay.', 'system', 0, '2026-06-07 05:29:55'),
(76, 33, 'Fire Safety Reminder', 'Remember to inspect your home\'s electrical outlets and keep flammable materials away from heat sources.', 'reminder', 1, '2026-05-01 13:18:45'),
(78, 14, 'Report Status Update', 'Your fire incident report has been verified by BFP Lian personnel.', 'status_update', 0, '2026-07-28 07:12:17'),
(79, 14, 'Report Status Update', 'Fire responders have been dispatched to the location you reported.', 'status_update', 0, '2026-07-28 07:12:31'),
(80, 14, 'Report Status Update', 'The fire incident you reported has been marked as resolved.', 'status_update', 0, '2026-07-28 07:12:46'),
(81, 23, 'Report Status Update', 'The fire incident you reported has been marked as resolved.', 'status_update', 0, '2026-07-28 07:13:44'),
(82, 27, 'Report Status Update', 'Your fire incident report has been verified by BFP Lian personnel.', 'status_update', 0, '2026-07-28 21:35:26'),
(83, 28, 'Report Status Update', 'The fire incident you reported has been marked as resolved.', 'status_update', 0, '2026-07-28 21:40:43'),
(84, 22, 'Report Status Update', 'Your fire incident report has been verified by BFP Lian personnel.', 'status_update', 0, '2026-07-28 23:32:20'),
(85, 22, 'Report Status Update', 'Fire responders have been dispatched to the location you reported.', 'status_update', 0, '2026-07-28 23:32:36'),
(86, 22, 'Report Status Update', 'The fire incident you reported has been marked as resolved.', 'status_update', 0, '2026-07-28 23:32:46'),
(87, 23, 'Report Status Update', 'The fire incident you reported has been marked as complete.', 'status_update', 0, '2026-09-13 06:56:31'),
(88, 22, 'Report Status Update', 'Your fire incident report has been verified by BFP Lian personnel.', 'status_update', 0, '2026-09-14 19:12:19'),
(89, 28, 'Report Status Update', 'The fire incident you reported has been marked as complete.', 'status_update', 0, '2026-09-21 13:35:12'),
(90, 17, 'Report Status Update', 'Your fire incident report has been verified by BFP Lian personnel.', 'status_update', 0, '2026-09-23 01:18:23'),
(91, 17, 'Report Status Update', 'Fire responders have been dispatched to the location you reported.', 'status_update', 0, '2026-09-23 01:18:39'),
(92, 17, 'Report Status Update', 'The fire incident you reported has been marked as resolved.', 'status_update', 0, '2026-09-23 01:18:56'),
(93, 17, 'Report Status Update', 'The fire incident you reported has been marked as complete.', 'status_update', 0, '2026-09-23 01:19:08');

-- --------------------------------------------------------

--
-- Table structure for table `passkeys`
--

CREATE TABLE `passkeys` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `credential_id` varchar(255) NOT NULL,
  `credential` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`credential`)),
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Table structure for table `report_link`
--

CREATE TABLE `report_link` (
  `link_id` bigint(20) UNSIGNED NOT NULL,
  `main_report_id` bigint(20) UNSIGNED NOT NULL,
  `related_report_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `report_link`
--

INSERT INTO `report_link` (`link_id`, `main_report_id`, `related_report_id`) VALUES
(1, 8, 30),
(3, 10, 32),
(8, 16, 31),
(5, 19, 33),
(7, 27, 54);

-- --------------------------------------------------------

--
-- Table structure for table `risk_assessment`
--

CREATE TABLE `risk_assessment` (
  `risk_id` bigint(20) UNSIGNED NOT NULL,
  `barangay_id` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `prediction_score` decimal(5,4) NOT NULL,
  `risk_level` enum('low','moderate','high','severe') NOT NULL,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `risk_assessment`
--

INSERT INTO `risk_assessment` (`risk_id`, `barangay_id`, `date`, `prediction_score`, `risk_level`, `generated_at`) VALUES
(1, 1, '2026-04-01', 0.4611, 'moderate', '2026-04-02 04:00:00'),
(2, 1, '2026-05-01', 0.2215, 'low', '2026-05-01 23:00:00'),
(3, 1, '2026-06-01', 0.2986, 'low', '2026-06-04 03:00:00'),
(4, 1, '2026-07-01', 0.8908, 'severe', '2026-07-02 22:00:00'),
(5, 2, '2026-04-01', 0.3046, 'low', '2026-04-03 07:00:00'),
(6, 2, '2026-05-01', 0.5719, 'moderate', '2026-05-04 00:00:00'),
(7, 2, '2026-06-01', 0.4519, 'moderate', '2026-06-04 03:00:00'),
(8, 2, '2026-07-01', 0.3492, 'low', '2026-07-04 05:00:00'),
(9, 3, '2026-04-01', 0.8896, 'severe', '2026-04-02 09:00:00'),
(10, 3, '2026-05-01', 0.2649, 'low', '2026-05-04 01:00:00'),
(11, 3, '2026-06-01', 0.8204, 'severe', '2026-06-03 22:00:00'),
(12, 3, '2026-07-01', 0.3364, 'low', '2026-07-04 09:00:00'),
(13, 4, '2026-04-01', 0.8721, 'severe', '2026-04-04 04:00:00'),
(14, 4, '2026-05-01', 0.9396, 'severe', '2026-05-02 05:00:00'),
(15, 4, '2026-06-01', 0.9183, 'severe', '2026-06-02 06:00:00'),
(16, 4, '2026-07-01', 0.9188, 'severe', '2026-07-02 23:00:00'),
(17, 5, '2026-04-01', 0.8368, 'severe', '2026-04-03 23:00:00'),
(18, 5, '2026-05-01', 0.5234, 'moderate', '2026-05-03 22:00:00'),
(19, 5, '2026-06-01', 0.7002, 'high', '2026-06-04 08:00:00'),
(20, 5, '2026-07-01', 0.9230, 'severe', '2026-07-01 05:00:00'),
(21, 6, '2026-04-01', 0.7534, 'high', '2026-04-03 03:00:00'),
(22, 6, '2026-05-01', 0.6109, 'high', '2026-05-04 08:00:00'),
(23, 6, '2026-06-01', 0.1223, 'low', '2026-06-03 08:00:00'),
(24, 6, '2026-07-01', 0.8234, 'severe', '2026-07-04 03:00:00'),
(25, 7, '2026-04-01', 0.6141, 'high', '2026-04-04 06:00:00'),
(26, 7, '2026-05-01', 0.0823, 'low', '2026-05-01 01:00:00'),
(27, 7, '2026-06-01', 0.6180, 'high', '2026-06-03 01:00:00'),
(28, 7, '2026-07-01', 0.7221, 'high', '2026-07-03 23:00:00'),
(29, 8, '2026-04-01', 0.7343, 'high', '2026-04-01 05:00:00'),
(30, 8, '2026-05-01', 0.1997, 'low', '2026-05-02 22:00:00'),
(31, 8, '2026-06-01', 0.0914, 'low', '2026-06-01 02:00:00'),
(32, 8, '2026-07-01', 0.3726, 'moderate', '2026-07-04 00:00:00'),
(33, 9, '2026-04-01', 0.2698, 'low', '2026-04-04 07:00:00'),
(34, 9, '2026-05-01', 0.6636, 'high', '2026-05-02 00:00:00'),
(35, 9, '2026-06-01', 0.2076, 'low', '2026-06-04 07:00:00'),
(36, 9, '2026-07-01', 0.6648, 'high', '2026-07-04 07:00:00'),
(37, 10, '2026-04-01', 0.1788, 'low', '2026-04-04 08:00:00'),
(38, 10, '2026-05-01', 0.2786, 'low', '2026-05-03 08:00:00'),
(39, 10, '2026-06-01', 0.0585, 'low', '2026-06-04 02:00:00'),
(40, 10, '2026-07-01', 0.6598, 'high', '2026-07-01 23:00:00'),
(41, 11, '2026-04-01', 0.4476, 'moderate', '2026-04-03 07:00:00'),
(42, 11, '2026-05-01', 0.3192, 'low', '2026-05-04 09:00:00'),
(43, 11, '2026-06-01', 0.2751, 'low', '2026-06-03 01:00:00'),
(44, 11, '2026-07-01', 0.9494, 'severe', '2026-07-03 23:00:00'),
(45, 12, '2026-04-01', 0.2635, 'low', '2026-04-03 07:00:00'),
(46, 12, '2026-05-01', 0.3163, 'low', '2026-05-02 22:00:00'),
(47, 12, '2026-06-01', 0.9302, 'severe', '2026-06-04 02:00:00'),
(48, 12, '2026-07-01', 0.0573, 'low', '2026-07-01 07:00:00'),
(49, 13, '2026-04-01', 0.7207, 'high', '2026-04-03 10:00:00'),
(50, 13, '2026-05-01', 0.7690, 'high', '2026-05-03 01:00:00'),
(51, 13, '2026-06-01', 0.6230, 'high', '2026-06-03 08:00:00'),
(52, 13, '2026-07-01', 0.7300, 'high', '2026-07-02 08:00:00'),
(53, 14, '2026-04-01', 0.1374, 'low', '2026-04-01 02:00:00'),
(54, 14, '2026-05-01', 0.7597, 'high', '2026-05-01 07:00:00'),
(55, 14, '2026-06-01', 0.3784, 'moderate', '2026-06-01 23:00:00'),
(56, 14, '2026-07-01', 0.8687, 'severe', '2026-07-03 09:00:00'),
(57, 15, '2026-04-01', 0.4240, 'moderate', '2026-04-02 00:00:00'),
(58, 15, '2026-05-01', 0.7579, 'high', '2026-05-03 06:00:00'),
(59, 15, '2026-06-01', 0.5016, 'moderate', '2026-06-03 00:00:00'),
(60, 15, '2026-07-01', 0.2813, 'low', '2026-07-04 10:00:00'),
(61, 16, '2026-04-01', 0.3156, 'low', '2026-04-03 10:00:00'),
(62, 16, '2026-05-01', 0.1537, 'low', '2026-05-01 00:00:00'),
(63, 16, '2026-06-01', 0.7287, 'high', '2026-06-02 08:00:00'),
(64, 16, '2026-07-01', 0.7018, 'high', '2026-07-04 10:00:00'),
(65, 17, '2026-04-01', 0.5516, 'moderate', '2026-04-01 10:00:00'),
(66, 17, '2026-05-01', 0.4051, 'moderate', '2026-05-03 06:00:00'),
(67, 17, '2026-06-01', 0.1612, 'low', '2026-06-03 08:00:00'),
(68, 17, '2026-07-01', 0.7241, 'high', '2026-07-03 07:00:00'),
(69, 18, '2026-04-01', 0.3929, 'moderate', '2026-04-02 23:00:00'),
(70, 18, '2026-05-01', 0.6574, 'high', '2026-05-03 22:00:00'),
(71, 18, '2026-06-01', 0.6076, 'high', '2026-06-03 07:00:00'),
(72, 18, '2026-07-01', 0.2492, 'low', '2026-07-01 08:00:00'),
(73, 19, '2026-04-01', 0.7915, 'high', '2026-04-03 08:00:00'),
(74, 19, '2026-05-01', 0.4175, 'moderate', '2026-05-01 22:00:00'),
(75, 19, '2026-06-01', 0.9016, 'severe', '2026-06-03 05:00:00'),
(76, 19, '2026-07-01', 0.1545, 'low', '2026-07-02 06:00:00');

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

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('gsYOdtSRW0nXVJWWy1ooeD6AscM6zIb17uc7PvO4', 34, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJ0WlhTQndSQXFuNHpnU0JHS3lwMnJUTjZCb2lqYkVGN0FXNkZRWTh5IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2ZpcmVzaWdodC13ZWItdjIudGVzdFwvYmFyYW5nYXktY29udGFjdHMiLCJyb3V0ZSI6ImJhcmFuZ2F5Q29udGFjdHMifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MzR9', 1790128122);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role` enum('resident','bfp_personnel','bfp_admin') NOT NULL DEFAULT 'resident',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `two_factor_secret` text DEFAULT NULL,
  `two_factor_recovery_codes` text DEFAULT NULL,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role`, `status`, `first_name`, `last_name`, `email`, `contact_number`, `username`, `email_verified_at`, `password`, `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'bfp_admin', 'active', 'Ricardo', 'Dela Cruz', 'admin@firesight.bfp.lian', '09171234567', 'rdelacruz', '2026-07-26 03:37:46', 'password', NULL, NULL, NULL, NULL, '2026-07-26 03:37:46', '2026-07-28 21:28:28'),
(2, 'bfp_personnel', 'active', 'Maria', 'Santos', 'personnel@firesight.bfp.lian', '09181234568', 'msantos', '2026-07-26 03:37:46', '$2y$12$nU5s8sXeLsR1SQr1qk35nOF1n7gwpHqp9Nm/RZj431C7N8YPRwISC', NULL, NULL, NULL, NULL, '2026-07-26 03:37:46', '2026-07-26 03:37:46'),
(3, 'bfp_personnel', 'active', 'Maria', 'Corpuz', 'mcorpuz@firesight.bfp.lian', '09445310485', 'mcorpuz', '2026-01-24 10:38:21', '$2y$12$ECAcrg7IKPib5ADz.umdYeugxkDurf9uEIkhpSkTukZBPRG/RlJ9a', NULL, NULL, NULL, NULL, '2026-01-24 10:38:21', '2026-01-24 10:38:21'),
(5, 'bfp_personnel', 'active', 'Bernardo', 'Santos', 'bsantos@firesight.bfp.lian', '09181994523', 'bsantos', '2026-01-09 18:19:32', '$2y$12$ECAcrg7IKPib5ADz.umdYeugxkDurf9uEIkhpSkTukZBPRG/RlJ9a', NULL, NULL, NULL, NULL, '2026-01-09 18:19:32', '2026-01-09 18:19:32'),
(6, 'bfp_personnel', 'active', 'Arnel', 'Malabanan', 'amalabanan@firesight.bfp.lian', '09178492780', 'amalabanan', '2026-02-24 03:47:44', '$2y$12$ECAcrg7IKPib5ADz.umdYeugxkDurf9uEIkhpSkTukZBPRG/RlJ9a', NULL, NULL, NULL, NULL, '2026-02-24 03:47:44', '2026-02-24 03:47:44'),
(7, 'bfp_personnel', 'active', 'Michael', 'Mercado', 'mmercado@firesight.bfp.lian', '09735126461', 'mmercado', '2026-02-10 09:33:07', '$2y$12$ECAcrg7IKPib5ADz.umdYeugxkDurf9uEIkhpSkTukZBPRG/RlJ9a', NULL, NULL, NULL, NULL, '2026-02-10 09:33:07', '2026-02-10 09:33:07'),
(8, 'bfp_personnel', 'active', 'Mark', 'Rivera', 'mrivera@firesight.bfp.lian', '09156977991', 'mrivera', '2026-01-16 04:01:59', '$2y$12$ECAcrg7IKPib5ADz.umdYeugxkDurf9uEIkhpSkTukZBPRG/RlJ9a', NULL, NULL, NULL, NULL, '2026-01-16 04:01:59', '2026-01-16 04:01:59'),
(9, 'resident', 'active', 'Jennifer', 'Rivera', 'jrivera@gmail.com', '09316944844', 'jrivera', '2026-03-29 14:52:10', '$2y$12$ECAcrg7IKPib5ADz.umdYeugxkDurf9uEIkhpSkTukZBPRG/RlJ9a', NULL, NULL, NULL, NULL, '2026-03-29 14:52:10', '2026-03-29 14:52:10'),
(10, 'resident', 'active', 'Jennifer', 'Garcia', 'jgarcia@gmail.com', '09249585092', 'jgarcia', '2026-06-01 13:10:44', '$2y$12$ECAcrg7IKPib5ADz.umdYeugxkDurf9uEIkhpSkTukZBPRG/RlJ9a', NULL, NULL, NULL, NULL, '2026-06-01 13:10:44', '2026-06-01 13:10:44'),
(11, 'resident', 'active', 'Ricardo', 'Malabanan', 'rmalabanan@gmail.com', '09434027113', 'rmalabanan', '2026-01-21 20:59:14', '$2y$12$ECAcrg7IKPib5ADz.umdYeugxkDurf9uEIkhpSkTukZBPRG/RlJ9a', NULL, NULL, NULL, NULL, '2026-01-21 20:59:14', '2026-01-21 20:59:14'),
(12, 'resident', 'active', 'Erlinda', 'Garcia', 'egarcia@gmail.com', '09556448196', 'egarcia', '2026-02-04 14:28:15', '$2y$12$ECAcrg7IKPib5ADz.umdYeugxkDurf9uEIkhpSkTukZBPRG/RlJ9a', NULL, NULL, NULL, NULL, '2026-02-04 14:28:15', '2026-02-04 14:28:15'),
(13, 'resident', 'active', 'Marilou', 'Malabanan', 'mmalabanan@gmail.com', '09538302652', 'mmalabanan', '2026-03-20 16:15:34', '$2y$12$ECAcrg7IKPib5ADz.umdYeugxkDurf9uEIkhpSkTukZBPRG/RlJ9a', NULL, NULL, NULL, NULL, '2026-03-20 16:15:34', '2026-03-20 16:15:34'),
(14, 'resident', 'active', 'Ana', 'Umali', 'aumali@gmail.com', '09394703907', 'aumali', '2026-04-27 09:17:28', '$2y$12$ECAcrg7IKPib5ADz.umdYeugxkDurf9uEIkhpSkTukZBPRG/RlJ9a', NULL, NULL, NULL, NULL, '2026-04-27 09:17:28', '2026-04-27 09:17:28'),
(15, 'resident', 'active', 'Angelica', 'Rosales', 'arosales@gmail.com', '09258449460', 'arosales', '2026-06-01 15:01:59', '$2y$12$ECAcrg7IKPib5ADz.umdYeugxkDurf9uEIkhpSkTukZBPRG/RlJ9a', NULL, NULL, NULL, NULL, '2026-06-01 15:01:59', '2026-06-01 15:01:59'),
(16, 'resident', 'active', 'Michael', 'Fernandez', 'mfernandez@gmail.com', '09541735568', 'mfernandez', '2026-03-09 04:02:45', '$2y$12$ECAcrg7IKPib5ADz.umdYeugxkDurf9uEIkhpSkTukZBPRG/RlJ9a', NULL, NULL, NULL, NULL, '2026-03-09 04:02:45', '2026-03-09 04:02:45'),
(17, 'resident', 'active', 'Ernesto', 'Umali', 'eumali@gmail.com', '09436665249', 'eumali', '2026-02-01 17:31:10', '$2y$12$ECAcrg7IKPib5ADz.umdYeugxkDurf9uEIkhpSkTukZBPRG/RlJ9a', NULL, NULL, NULL, NULL, '2026-02-01 17:31:10', '2026-02-01 17:31:10'),
(18, 'resident', 'active', 'Josefina', 'Marasigan', 'jmarasigan@gmail.com', '09932893941', 'jmarasigan', '2026-04-10 01:48:11', '$2y$12$ECAcrg7IKPib5ADz.umdYeugxkDurf9uEIkhpSkTukZBPRG/RlJ9a', NULL, NULL, NULL, NULL, '2026-04-10 01:48:11', '2026-04-10 01:48:11'),
(19, 'resident', 'active', 'Rodrigo', 'Rivera', 'rrivera@gmail.com', '09837194506', 'rrivera', '2026-03-31 06:57:42', '$2y$12$ECAcrg7IKPib5ADz.umdYeugxkDurf9uEIkhpSkTukZBPRG/RlJ9a', NULL, NULL, NULL, NULL, '2026-03-31 06:57:42', '2026-03-31 06:57:42'),
(20, 'resident', 'active', 'Kristine', 'Aguilar', 'kaguilar@gmail.com', '09210062915', 'kaguilar', '2026-04-03 22:52:57', '$2y$12$ECAcrg7IKPib5ADz.umdYeugxkDurf9uEIkhpSkTukZBPRG/RlJ9a', NULL, NULL, NULL, NULL, '2026-04-03 22:52:57', '2026-04-03 22:52:57'),
(21, 'resident', 'active', 'Jennifer', 'Villanueva', 'jvillanueva@gmail.com', '09437484583', 'jvillanueva', '2026-01-30 16:55:21', '$2y$12$ECAcrg7IKPib5ADz.umdYeugxkDurf9uEIkhpSkTukZBPRG/RlJ9a', NULL, NULL, NULL, NULL, '2026-01-30 16:55:21', '2026-01-30 16:55:21'),
(22, 'resident', 'active', 'Mark', 'Navarro', 'mnavarro@gmail.com', '09920854579', 'mnavarro', '2026-05-07 05:00:37', '$2y$12$ECAcrg7IKPib5ADz.umdYeugxkDurf9uEIkhpSkTukZBPRG/RlJ9a', NULL, NULL, NULL, NULL, '2026-05-07 05:00:37', '2026-05-07 05:00:37'),
(23, 'resident', 'active', 'Rolando', 'Villanueva', 'rvillanueva@gmail.com', '09840256940', 'rvillanueva', '2026-07-02 04:48:33', '$2y$12$ECAcrg7IKPib5ADz.umdYeugxkDurf9uEIkhpSkTukZBPRG/RlJ9a', NULL, NULL, NULL, NULL, '2026-07-02 04:48:33', '2026-07-02 04:48:33'),
(24, 'resident', 'active', 'Manuel', 'Flores', 'mflores@gmail.com', '09949925830', 'mflores', '2026-04-17 00:55:45', '$2y$12$ECAcrg7IKPib5ADz.umdYeugxkDurf9uEIkhpSkTukZBPRG/RlJ9a', NULL, NULL, NULL, NULL, '2026-04-17 00:55:45', '2026-04-17 00:55:45'),
(25, 'resident', 'active', 'Leonora', 'Navarro', 'lnavarro@gmail.com', '09776563708', 'lnavarro', '2026-06-09 02:40:57', '$2y$12$ECAcrg7IKPib5ADz.umdYeugxkDurf9uEIkhpSkTukZBPRG/RlJ9a', NULL, NULL, NULL, NULL, '2026-06-09 02:40:57', '2026-06-09 02:40:57'),
(26, 'resident', 'active', 'Manuel', 'Pascual', 'mpascual@gmail.com', '09679908599', 'mpascual', '2026-02-09 07:20:13', '$2y$12$ECAcrg7IKPib5ADz.umdYeugxkDurf9uEIkhpSkTukZBPRG/RlJ9a', NULL, NULL, NULL, NULL, '2026-02-09 07:20:13', '2026-02-09 07:20:13'),
(27, 'resident', 'active', 'Rosario', 'Bautista', 'rbautista@gmail.com', '09823715057', 'rbautista', '2026-03-08 03:08:25', '$2y$12$ECAcrg7IKPib5ADz.umdYeugxkDurf9uEIkhpSkTukZBPRG/RlJ9a', NULL, NULL, NULL, NULL, '2026-03-08 03:08:25', '2026-03-08 03:08:25'),
(28, 'resident', 'active', 'Leonora', 'Malabanan', 'lmalabanan@gmail.com', '09218212356', 'lmalabanan', '2026-06-03 10:17:28', '$2y$12$ECAcrg7IKPib5ADz.umdYeugxkDurf9uEIkhpSkTukZBPRG/RlJ9a', NULL, NULL, NULL, NULL, '2026-06-03 10:17:28', '2026-06-03 10:17:28'),
(29, 'resident', 'active', 'Alfredo', 'Pascual', 'apascual@gmail.com', '09419953851', 'apascual', '2026-01-09 11:00:39', '$2y$12$ECAcrg7IKPib5ADz.umdYeugxkDurf9uEIkhpSkTukZBPRG/RlJ9a', NULL, NULL, NULL, NULL, '2026-01-09 11:00:39', '2026-01-09 11:00:39'),
(30, 'resident', 'active', 'Rosario', 'Umali', 'rumali@gmail.com', '09726567501', 'rumali', '2026-04-18 14:59:27', '$2y$12$ECAcrg7IKPib5ADz.umdYeugxkDurf9uEIkhpSkTukZBPRG/RlJ9a', NULL, NULL, NULL, NULL, '2026-04-18 14:59:27', '2026-04-18 14:59:27'),
(31, 'resident', 'active', 'Jennifer', 'Garcia', 'jgarcia2@gmail.com', '09465143362', 'jgarcia2', '2026-06-22 20:18:21', '$2y$12$ECAcrg7IKPib5ADz.umdYeugxkDurf9uEIkhpSkTukZBPRG/RlJ9a', NULL, NULL, NULL, NULL, '2026-06-22 20:18:21', '2026-06-22 20:18:21'),
(32, 'resident', 'active', 'Juan', 'Corpuz', 'jcorpuz@gmail.com', '09922751234', 'jcorpuz', '2026-04-17 06:57:49', '$2y$12$ECAcrg7IKPib5ADz.umdYeugxkDurf9uEIkhpSkTukZBPRG/RlJ9a', NULL, NULL, NULL, NULL, '2026-04-17 06:57:49', '2026-04-17 06:57:49'),
(33, 'resident', 'active', 'Kristine', 'Mendoza', 'kmendoza@gmail.com', '09695119047', 'kmendoza', '2026-02-15 07:49:14', '$2y$12$ECAcrg7IKPib5ADz.umdYeugxkDurf9uEIkhpSkTukZBPRG/RlJ9a', NULL, NULL, NULL, NULL, '2026-02-15 07:49:14', '2026-02-15 07:49:14'),
(34, 'bfp_admin', 'active', 'Admin', 'User', 'admin2@firesight.bfp.lian', '09171234568', 'admin2', '2026-08-19 07:10:23', '$2y$12$ECAcrg7IKPib5ADz.umdYeugxkDurf9uEIkhpSkTukZBPRG/RlJ9a', NULL, NULL, NULL, NULL, '2026-08-19 07:10:23', '2026-08-19 07:10:23');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcement`
--
ALTER TABLE `announcement`
  ADD PRIMARY KEY (`announcement_id`),
  ADD KEY `announcement_created_by_foreign` (`created_by`);

--
-- Indexes for table `barangay`
--
ALTER TABLE `barangay`
  ADD PRIMARY KEY (`barangay_id`),
  ADD UNIQUE KEY `barangay_barangay_name_unique` (`barangay_name`);

--
-- Indexes for table `barangay_contact`
--
ALTER TABLE `barangay_contact`
  ADD PRIMARY KEY (`contact_id`),
  ADD KEY `idx_barangay_contact_barangay` (`barangay_id`);

--
-- Indexes for table `bfp_personnel_details`
--
ALTER TABLE `bfp_personnel_details`
  ADD PRIMARY KEY (`details_id`),
  ADD UNIQUE KEY `bfp_personnel_details_user_id_unique` (`user_id`),
  ADD UNIQUE KEY `bfp_personnel_details_employee_number_unique` (`employee_number`);

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
-- Indexes for table `community_report`
--
ALTER TABLE `community_report`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `community_report_user_id_foreign` (`user_id`),
  ADD KEY `idx_report_status` (`status`),
  ADD KEY `idx_report_location` (`latitude`,`longitude`);

--
-- Indexes for table `duty_schedule`
--
ALTER TABLE `duty_schedule`
  ADD PRIMARY KEY (`schedule_id`),
  ADD UNIQUE KEY `uniq_user_duty_slot` (`user_id`,`duty_date`,`time_start`),
  ADD KEY `duty_schedule_created_by_foreign` (`created_by`),
  ADD KEY `idx_duty_window` (`duty_date`,`time_start`,`time_end`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  ADD KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`);

--
-- Indexes for table `incident_record`
--
ALTER TABLE `incident_record`
  ADD PRIMARY KEY (`incident_id`),
  ADD KEY `incident_record_report_id_foreign` (`report_id`),
  ADD KEY `idx_incident_barangay` (`barangay_id`),
  ADD KEY `idx_incident_datetime` (`data_time`);

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
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `idx_notification_user_read` (`user_id`,`is_read`);

--
-- Indexes for table `passkeys`
--
ALTER TABLE `passkeys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `passkeys_credential_id_unique` (`credential_id`),
  ADD KEY `passkeys_user_id_index` (`user_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `report_link`
--
ALTER TABLE `report_link`
  ADD PRIMARY KEY (`link_id`),
  ADD UNIQUE KEY `uq_report_link` (`main_report_id`,`related_report_id`),
  ADD KEY `report_link_related_report_id_foreign` (`related_report_id`);

--
-- Indexes for table `risk_assessment`
--
ALTER TABLE `risk_assessment`
  ADD PRIMARY KEY (`risk_id`),
  ADD KEY `idx_risk_barangay_date` (`barangay_id`,`date`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_username_unique` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcement`
--
ALTER TABLE `announcement`
  MODIFY `announcement_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `barangay`
--
ALTER TABLE `barangay`
  MODIFY `barangay_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `barangay_contact`
--
ALTER TABLE `barangay_contact`
  MODIFY `contact_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `bfp_personnel_details`
--
ALTER TABLE `bfp_personnel_details`
  MODIFY `details_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `community_report`
--
ALTER TABLE `community_report`
  MODIFY `report_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `duty_schedule`
--
ALTER TABLE `duty_schedule`
  MODIFY `schedule_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `incident_record`
--
ALTER TABLE `incident_record`
  MODIFY `incident_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `notification_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;

--
-- AUTO_INCREMENT for table `passkeys`
--
ALTER TABLE `passkeys`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `report_link`
--
ALTER TABLE `report_link`
  MODIFY `link_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `risk_assessment`
--
ALTER TABLE `risk_assessment`
  MODIFY `risk_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcement`
--
ALTER TABLE `announcement`
  ADD CONSTRAINT `announcement_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `barangay_contact`
--
ALTER TABLE `barangay_contact`
  ADD CONSTRAINT `barangay_contact_barangay_id_foreign` FOREIGN KEY (`barangay_id`) REFERENCES `barangay` (`barangay_id`) ON DELETE CASCADE;

--
-- Constraints for table `bfp_personnel_details`
--
ALTER TABLE `bfp_personnel_details`
  ADD CONSTRAINT `bfp_personnel_details_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `community_report`
--
ALTER TABLE `community_report`
  ADD CONSTRAINT `community_report_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `duty_schedule`
--
ALTER TABLE `duty_schedule`
  ADD CONSTRAINT `duty_schedule_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `duty_schedule_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `incident_record`
--
ALTER TABLE `incident_record`
  ADD CONSTRAINT `incident_record_barangay_id_foreign` FOREIGN KEY (`barangay_id`) REFERENCES `barangay` (`barangay_id`),
  ADD CONSTRAINT `incident_record_report_id_foreign` FOREIGN KEY (`report_id`) REFERENCES `community_report` (`report_id`) ON DELETE CASCADE;

--
-- Constraints for table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `notification_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `passkeys`
--
ALTER TABLE `passkeys`
  ADD CONSTRAINT `passkeys_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `report_link`
--
ALTER TABLE `report_link`
  ADD CONSTRAINT `report_link_main_report_id_foreign` FOREIGN KEY (`main_report_id`) REFERENCES `community_report` (`report_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `report_link_related_report_id_foreign` FOREIGN KEY (`related_report_id`) REFERENCES `community_report` (`report_id`) ON DELETE CASCADE;

--
-- Constraints for table `risk_assessment`
--
ALTER TABLE `risk_assessment`
  ADD CONSTRAINT `risk_assessment_barangay_id_foreign` FOREIGN KEY (`barangay_id`) REFERENCES `barangay` (`barangay_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
