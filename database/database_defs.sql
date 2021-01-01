-- phpMyAdmin SQL Dump
-- version 4.9.5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 01, 2021 at 11:49 AM
-- Server version: 5.7.32
-- PHP Version: 7.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `qfgavcxt_beagairbheagdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `programmes`
--

CREATE TABLE `programmes` (
  `series_num` int(4) NOT NULL,
  `episode_nam` varchar(100) NOT NULL,
  `firston_datestring` varchar(10) DEFAULT NULL,
  `finish_time` char(8) DEFAULT NULL,
  `learner_of_the_week` varchar(45) NOT NULL,
  `bbc_programme_url` varchar(100) DEFAULT NULL,
  `bbc_download_filename` varchar(100) DEFAULT NULL,
  `splash_screen_filename` varchar(50) NOT NULL,
  `splash_screen_title` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- Table structure for table `programme_texts`
--

CREATE TABLE `programme_texts` (
  `series_num` int(4) NOT NULL,
  `episode_nam` varchar(100) COLLATE utf8_bin NOT NULL,
  `start_time_in_programme` int(4) NOT NULL,
  `finish_time_in_programme` int(4) DEFAULT NULL,
  `text_title` varchar(100) COLLATE utf8_bin NOT NULL DEFAULT '',
  `text_url` varchar(500) COLLATE utf8_bin DEFAULT NULL,
  `text_type` varchar(10) COLLATE utf8_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Table structure for table `system`
--

CREATE TABLE `system` (
  `system_key` varchar(4) NOT NULL DEFAULT 'bab',
  `version_number` decimal(4,2) NOT NULL,
  `download_count` int(10) NOT NULL DEFAULT '0',
  `backup_count` int(10) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `text_types`
--

CREATE TABLE `text_types` (
  `text_type` varchar(10) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `text_color` varchar(10) DEFAULT NULL,
  `text_header` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `text_types`
--

INSERT INTO `text_types` (`text_type`, `text_color`, `text_header`) VALUES
('aithrisn', 'silver', 'Aithris Naidheachd'),
('blasadbeag', 'blue', 'Blasad Beag'),
('muthim', 'hotpink', 'Mu Thimcheall'),
('oiseanagr', 'fuchsia', 'Oisean aʼ Ghràmair'),
('suilairamh', 'purple', 'Sùil air a&rsquo; Mhapa'),
('tarscriobh', 'lightgreen', 'Tar-sgrìobhadh');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `programmes`
--
ALTER TABLE `programmes`
  ADD PRIMARY KEY (`series_num`,`episode_nam`);

--
-- Indexes for table `programme_texts`
--
ALTER TABLE `programme_texts`
  ADD PRIMARY KEY (`series_num`,`episode_nam`,`text_title`);

--
-- Indexes for table `system`
--
ALTER TABLE `system`
  ADD PRIMARY KEY (`system_key`);

--
-- Indexes for table `text_types`
--
ALTER TABLE `text_types`
  ADD PRIMARY KEY (`text_type`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
