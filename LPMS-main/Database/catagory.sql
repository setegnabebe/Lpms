-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Aug 15, 2023 at 08:46 AM
-- Server version: 8.0.31
-- PHP Version: 8.2.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `project_lpms`
--

-- --------------------------------------------------------

--
-- Table structure for table `catagory`
--

DROP TABLE IF EXISTS `catagory`;
CREATE TABLE IF NOT EXISTS `catagory` (
  `catagory` varchar(100) NOT NULL,
  `display_name` varchar(50) NOT NULL,
  `image` varchar(100) NOT NULL,
  `path` varchar(100) NOT NULL DEFAULT 'requests/request_form.php',
  `replacements` tinyint(1) NOT NULL DEFAULT '0',
  `privilege` varchar(1000) NOT NULL DEFAULT 'All',
  PRIMARY KEY (`catagory`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `catagory`
--

INSERT INTO `catagory` (`catagory`, `display_name`, `image`, `path`, `replacements`, `privilege`) VALUES
('Consumer Goods', 'Consumer Goods', 'ConsumerGoods.jpg', 'requests/projectList.php', 0, 'All'),
('Spare and Lubricant', 'Spare and Lubricant', 'SpareandLubricant.jpeg', 'requests/spareJobs.php', 0, 'All'),
('Stationary and Toiletaries', 'Stationary and Toiletaries', 'StationaryandToiletaries.jfif', 'requests/requestForm.php', 0, 'All'),
('Tyre and Battery', 'Tyre, Inner Tube and Battery', 'TyreandBattery.png', 'requests/tyreForm.php', 1, 'All'),
('Fixed Assets', 'Fixed Assets', 'FixedAssets.jpg', 'requests/requestForm.php', 1, 'All'),
('Miscellaneous', 'Miscellaneous', 'Miscellaneous.webp', 'requests/requestForm.php', 0, 'All');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
