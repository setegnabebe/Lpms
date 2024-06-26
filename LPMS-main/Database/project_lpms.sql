-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 22, 2023 at 05:50 AM
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

DELIMITER $$
--
-- Functions
--
DROP FUNCTION IF EXISTS `timetocalculate`$$
CREATE DEFINER=`root`@`localhost` FUNCTION `timetocalculate` (`coll` TIMESTAMP, `ass` TIMESTAMP) RETURNS DOUBLE  BEGIN
  DECLARE dutyhour DECIMAL(10,4);
  DECLARE day_start TIME;
  DECLARE day_end TIME;
  DECLARE date_jumped INT DEFAULT 0;
  DECLARE realass TIMESTAMP; 
  DECLARE realcoll TIMESTAMP;
  DECLARE tempdate TIME;
  DECLARE diff_hours DECIMAL(10,4);
  DECLARE diff_min DECIMAL(10,4);
  DECLARE diff_sec DECIMAL(10,4);

  DECLARE datediff INT DEFAULT 0;
  DECLARE inbetween DECIMAL(10,4) DEFAULT 0;
  DECLARE saturday INT DEFAULT 0;
  DECLARE sunday INT DEFAULT 0;

  DECLARE day VARCHAR(10);
  SET day = DAYNAME(ass);
  SET realass = DATE_FORMAT(ass, "%Y-%m-%d"); 
  SET realcoll = DATE_FORMAT(coll, "%Y-%m-%d");
    SET day_start = "08:00:00";
  IF day = "Saturday" THEN
    SET day_end = "12:30:00";
  ELSE
    SET day_end = "17:00:00";
  END IF; 
  IF realass = realcoll THEN  
    SET tempdate  =  TIMEDIFF(coll, ass);       
    SET diff_hours = DATE_FORMAT(tempdate, "%H");
    SET diff_min = DATE_FORMAT(tempdate, "%i");
    SET diff_sec = DATE_FORMAT(tempdate, "%s");
  ELSE
    SET datediff = DATEDIFF(realcoll, realass);   
    SET tempdate  =  TIMEDIFF(day_end, DATE_FORMAT(ass, "%H:%i:%s"));
    SET diff_hours = DATE_FORMAT(tempdate, "%H");
    SET diff_min = DATE_FORMAT(tempdate, "%i");
    SET diff_sec = DATE_FORMAT(tempdate, "%s");
    
    SET tempdate  =  TIMEDIFF(DATE_FORMAT(coll, "%H:%i:%s"), day_start);
    SET diff_hours = diff_hours + DATE_FORMAT(tempdate, "%H");
    SET diff_min = diff_min + DATE_FORMAT(tempdate, "%i");
    SET diff_sec = diff_sec + DATE_FORMAT(tempdate, "%s");
      IF datediff > 1 THEN 
          SET saturday = FLOOR(datediff / 7) ;
          SET sunday = saturday;
          IF WEEKDAY(ass) >= WEEKDAY(coll) OR datediff > 6 THEN
            IF saturday = 0 THEN
              SET saturday = saturday + 1;
              SET sunday = sunday + 1;
            END IF;
            IF WEEKDAY(ass) = 5 THEN
              SET saturday = saturday - 1;
            END IF;
          END IF;
          SET inbetween = ((datediff - (saturday + sunday + 1)) * 8) + (saturday * 4.5); -- For Adding inbetween days
      END IF;
  END IF;
  IF day != "Saturday" AND ((datediff = 0 AND DATE_FORMAT(ass, "%H:%i:%s") < "13:00:00" AND DATE_FORMAT(coll, "%H:%i:%s") >= "13:00:00") OR (datediff > 0 AND (DATE_FORMAT(ass, "%H:%i:%s") < "13:00:00" OR DATE_FORMAT(coll, "%H:%i:%s") >= "13:00:00"))) THEN
  IF (DATE_FORMAT(ass, "%H:%i:%s") >= "12:00:00" AND DATE_FORMAT(ass, "%H:%i:%s") < "13:00:00") THEN
    SET tempdate  =  TIMEDIFF("12:00:00", DATE_FORMAT(ass, "%H:%i:%s"));
    SET diff_hours = diff_hours - DATE_FORMAT(tempdate, "%H");
    SET diff_min = diff_min - DATE_FORMAT(tempdate, "%i");
    SET diff_sec = diff_sec - DATE_FORMAT(tempdate, "%s");
  ELSE 
    SET diff_hours = diff_hours - 1;
  END IF;
    IF (datediff > 0 AND DATE_FORMAT(ass, "%H:%i:%s") < "13:00:00" AND DATE_FORMAT(coll, "%H:%i:%s") >= "13:00:00") THEN
      SET diff_hours = diff_hours - 1;
    END IF;
  END IF;
  SET dutyhour = diff_hours + diff_min/60 + diff_sec/3600 + inbetween;     
RETURN dutyhour;     
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `account`
--

DROP TABLE IF EXISTS `account`;
CREATE TABLE IF NOT EXISTS `account` (
  `unique_id` int NOT NULL AUTO_INCREMENT,
  `Username` varchar(100) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `phone` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `company` varchar(100) NOT NULL,
  `department` varchar(100) NOT NULL,
  `type` varchar(100) NOT NULL DEFAULT 'user',
  `position` varchar(100) DEFAULT NULL,
  `role` varchar(100) NOT NULL,
  `additional_role` varchar(1) DEFAULT NULL,
  `managing` varchar(255) DEFAULT NULL,
  `status` varchar(100) NOT NULL DEFAULT 'waiting',
  `cheque_percent` varchar(200) NOT NULL DEFAULT 'not_0',
  `creation_date` timestamp NOT NULL,
  `created_by` varchar(100) NOT NULL DEFAULT 'Dagem.Adugna',
  `update_date` timestamp NULL DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  `user_status` varchar(12) NOT NULL DEFAULT 'Offline',
  PRIMARY KEY (`Username`),
  UNIQUE KEY `unique_id` (`unique_id`)
) ENGINE=InnoDB AUTO_INCREMENT=281 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `account`
--

INSERT INTO `account` (`unique_id`, `Username`, `name`, `phone`, `email`, `password`, `company`, `department`, `type`, `position`, `role`, `additional_role`, `managing`, `status`, `cheque_percent`, `creation_date`, `created_by`, `update_date`, `updated_by`, `user_status`) VALUES
(1, 'Abdilkadir.Mohammed', 'Abdilkadir.Mohammed', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'IT', 'Admin', 'Junior Software Engineer', 'Admin', NULL, '', 'active', '', '2022-12-27 10:46:39', 'Dagem.Adugna', NULL, NULL, 'Online'),
(250, 'Abdurehman.Hussen', 'Abdurehman.Hussen', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Tender', 'user', NULL, 'user', '', '', 'active', '', '2023-04-27 05:40:46', 'Dagem.Adugna', NULL, NULL, 'Online'),
(2, 'Abel.Aemero', 'Abel.Aemero', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'KHMSC', 'Spare Part And Warranty', 'manager,Branch Committee', 'Spare Part And Warranty manager', 'manager', NULL, '', 'active', '', '2022-12-15 08:12:39', 'Dagem.Adugna', NULL, NULL, 'Online'),
(3, 'Abel.G_mariam', 'Abel.G_mariam', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Debrezit Food Ind.', 'Marketing and Sales', 'manager', 'Marketing and Sales manager', 'manager', NULL, '', 'active', '', '2022-10-19 11:28:28', 'Dagem.Adugna', NULL, NULL, 'Online'),
(268, 'Adane.Girma', NULL, '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Audit', 'user', NULL, 'user', '', '', 'active', '', '2023-06-06 05:20:59', 'Dagem.Adugna', NULL, NULL, 'Online'),
(4, 'Addis.Getaneh', 'Addis.Getaneh', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Ultimate Motors', 'Workshop', 'user', 'Workshop user', 'user', NULL, '', 'active', '', '2023-01-05 08:51:08', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(5, 'Addisu.Belay', 'Addisu.Belay', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Beza Ind.', 'Property', 'manager', 'Property manager', 'manager', NULL, '', 'waiting', '', '2022-10-19 08:22:25', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(6, 'Admin.Account', 'Admin.Account', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'IT', 'Admin', 'Procurement Senior Purchase officer', 'Admin', NULL, '', 'active', '', '2023-01-16 06:06:17', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(7, 'Aklilu.Gashaw', 'Aklilu.Gashaw', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'HMAESM', 'manager', 'HMAESM manager', 'manager', NULL, '', 'active', '', '2022-10-04 06:51:03', 'Dagem.Adugna', NULL, NULL, 'Online'),
(8, 'Aklilu.Nigatu', 'Aklilu.Nigatu', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Atlas Copco', 'Director', 'Branch Committee,manager', 'General Manager', 'Director', NULL, 'All Departments', 'active', '', '2022-10-13 10:49:10', 'Dagem.Adugna', NULL, NULL, 'Online'),
(246, 'Alebachew.Mekete', 'Alebachew.Mekete', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Property', 'user', NULL, 'user', '', '', 'active', '', '2023-04-13 06:43:51', 'Dagem.Adugna', NULL, NULL, 'Online'),
(9, 'Alemayeh.Demissie', 'Alemayeh.Demissie', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'KHMSC', 'Property', 'manager', 'Property manager', 'manager', NULL, '', 'active', '', '2023-01-20 07:03:51', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(225, 'Alemayehu.Mekonnen', 'Alemayehu.Mekonnen', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Ultimate Motors', 'CAD', 'manager', 'CAD manager', 'manager', NULL, '', 'active', '', '2023-02-21 10:21:50', 'Dagem.Adugna', NULL, NULL, 'Online'),
(10, 'Alemayehu.Wendafrash', 'Alemayehu.Wendafrash', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Atikilt Tera', 'GM', 'manager,Branch Committee', 'General Manager', 'Director', NULL, 'All Departments', 'active', '', '2022-10-20 12:58:52', 'Dagem.Adugna', NULL, NULL, 'Online'),
(11, 'Alemnesh.Wondmgezahu', 'Alemnesh.Wondmgezahu', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Sevita', 'Finance', 'user', 'Finance Cashier', 'Cashier', NULL, '', 'active', '', '2023-01-13 06:20:36', 'Dagem.Adugna', NULL, NULL, 'Online'),
(244, 'Alemnesh.Wondmgezahu2', 'Alemnesh.Wondmgezahu2', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Setavi', 'Finance', 'user', NULL, 'Cashier', '', '', 'active', '', '2023-04-06 12:44:10', 'Dagem.Adugna', NULL, NULL, 'Online'),
(12, 'alemtsehay.aklilu', 'alemtsehay.aklilu', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Abakoran', 'GM', 'user', 'GM user', 'user', NULL, '', 'active', '', '2023-01-24 12:03:51', 'Dagem.Adugna', NULL, NULL, 'Online'),
(13, 'Alemu.Degefa', 'Alemu.Degefa', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Finance', 'user', 'Finance Disbursement', 'Disbursement', NULL, '', 'active', '', '2022-10-03 13:32:25', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(14, 'Almaz.Bacha', 'Almaz.Bacha', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Ultimate Motors', 'Procurement', 'user', 'Procurement Purchase officer', 'Purchase officer', NULL, '', 'active', '', '2022-12-19 12:21:59', 'Dagem.Adugna', NULL, NULL, 'Online'),
(235, 'Amanuael.Kumsa', 'Amanuael.Kumsa', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Import', 'user', NULL, 'user', '', '', 'active', '', '2023-03-31 08:53:00', 'Dagem.Adugna', NULL, NULL, 'Online'),
(15, 'Amanuel.Fikadu', 'Amanuel.Fikadu', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'KHMSC', 'Spare Part And Warranty', 'user', 'Spare Part And Warranty user', 'user', NULL, '', 'active', '', '2023-01-05 08:57:11', 'Dagem.Adugna', NULL, NULL, 'Online'),
(276, 'Amanuel.Gezahegn', NULL, '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'IT', 'user', NULL, 'user', '', '', 'active', '', '2023-06-15 08:14:55', 'Dagem.Adugna', NULL, NULL, 'Online'),
(16, 'Amarech.Tessema', 'Amarech.Tessema', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Finance', 'user', 'Finance Cashier', 'Cashier', NULL, '', 'active', '', '2022-10-03 13:37:37', 'Dagem.Adugna', NULL, NULL, 'Online'),
(17, 'Amtachew.Reta', 'Amtachew.Reta', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Ultimate Motors', 'Procurement', 'manager', 'Procurement manager', 'manager', NULL, '', 'active', '', '2022-12-19 12:19:26', 'Dagem.Adugna', NULL, NULL, 'Online'),
(231, 'Andualem.Workineh', 'Andualem.Workineh', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Ultimate Motors', 'Construction', 'manager', 'Construction manager', 'manager', '', '', 'active', '', '2023-03-16 06:21:20', 'Dagem.Adugna', NULL, NULL, 'Online'),
(18, 'Ani.Tilbian', 'Ani.Tilbian', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Disbursement', 'manager,Cheque Signatory,Petty Cash Approver', 'Disbursement manager', 'manager', NULL, '', 'active', 'not_50', '2022-10-03 13:55:59', 'Dagem.Adugna', NULL, NULL, 'Online'),
(19, 'Anteneh.Belay', 'Anteneh.Belay', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'KHMSC', 'Technic', 'manager,Branch Committee', 'Technic manager', 'manager', NULL, '', 'active', '', '2023-01-05 07:48:59', 'Dagem.Adugna', NULL, NULL, 'Online'),
(20, 'Argata.Bedesso', 'Argata.Bedesso', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Debrezit Food Ind.', 'HR', 'manager', 'HR manager', 'manager', NULL, '', 'inactive', '', '2022-10-19 11:56:12', 'Dagem.Adugna', NULL, NULL, 'Online'),
(21, 'Ashagre.Mekuria', 'Ashagre.Mekuria', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Ultimate Motors', 'Finance', 'user', 'Finance Disbursement', 'Disbursement', NULL, '', 'active', '', '2022-12-19 12:37:52', 'Dagem.Adugna', NULL, NULL, 'Online'),
(279, 'Ashenafi.Kahsay', NULL, '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Import', 'user', NULL, 'user', '', '', 'active', '', '2023-07-01 03:59:46', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(22, 'Ashenafi.Woldu ', 'Ashenafi.Woldu ', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'KHMSC', 'HR', 'manager,Branch Committee', 'HR manager', 'manager', NULL, '', 'active', '', '2023-01-16 12:28:12', 'Dagem.Adugna', NULL, NULL, 'Online'),
(23, 'Askale.G_Giorgis', 'Askale.G_Giorgis', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Debrezit Food Ind.', 'Plant', 'manager', 'Plant Director', 'Director', NULL, 'All Departments', 'active', '', '2022-10-19 11:54:46', 'Dagem.Adugna', NULL, NULL, 'Online'),
(24, 'Aster.Eshete', 'Aster.Eshete', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Finance', 'Cheque Signatory,Perdiem', 'Finance user', 'user', NULL, '', 'active', 'p_50', '2022-10-03 13:58:46', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(25, 'Aynalem.Delssa', 'Aynalem.Delssa', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Debrezit Food Ind.', 'Property', 'manager', 'Property manager', 'manager', NULL, '', 'active', '', '2022-10-19 11:58:38', 'Dagem.Adugna', NULL, NULL, 'Online'),
(228, 'Aynalem.Hunde', 'Aynalem.Hunde', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Property', 'manager', 'Property manager', 'manager', '', '', 'active', '', '2023-03-06 11:20:49', 'Dagem.Adugna', NULL, NULL, 'Online'),
(216, 'Aynu.Feseha', 'Aynu.Feseha', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Finance', 'user', 'Finance Cashier', 'Cashier', NULL, '', 'inactive', '', '2023-02-07 11:37:10', 'Dagem.Adugna', NULL, NULL, 'Online'),
(26, 'Bazezew.Zeleke', 'Bazezew.Zeleke', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Ultimate Motors', 'Workshop', 'user', 'Workshop user', 'user', NULL, '', 'active', '', '2023-01-05 07:29:28', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(207, 'Behailu.Mered', 'Behailu.Mered', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Atikilt Tera', 'Finance', 'manager', 'Finance manager', 'manager', NULL, '', 'active', '', '2023-02-01 11:16:37', 'Dagem.Adugna', NULL, NULL, 'Online'),
(249, 'Bekele.Wubie', 'Bekele.Wubie', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Agricultre ', 'user', NULL, 'user', '', '', 'active', '', '2023-04-26 08:37:50', 'Dagem.Adugna', NULL, NULL, 'Online'),
(27, 'Belay.Asmare', 'Belay.Asmare', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Debrezit Food Ind.', 'Finance', 'manager', 'Finance manager', 'manager', NULL, '', 'active', '', '2022-10-19 11:25:47', 'Dagem.Adugna', NULL, NULL, 'Online'),
(263, 'Belay.Welelaw', 'Belay.Welelaw', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Ultimate Motors', 'Metal Workshop', 'user', NULL, 'Store', '', '', 'active', '', '2023-05-24 08:01:01', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(28, 'Belay.Wolalew', 'Belay.Wolalew', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Ultimate Motors', 'Property', 'user', 'Property Store', 'Store', NULL, '', 'waiting', '', '2022-12-19 12:48:43', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(29, 'Berhan.Kassahun', 'Berhan.Kassahun', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Procurement', 'user', 'Procurement Purchase officer', 'Purchase officer', NULL, '', 'active', '', '2022-10-03 13:21:21', 'Dagem.Adugna', NULL, NULL, 'Online'),
(30, 'Berhanu.Jijo', 'Berhanu.Jijo', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Operation Manager', 'user', 'Operation Manager user', 'user', NULL, '', 'active', '', '2022-10-04 06:51:03', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(31, 'Betel.Tensay', 'Betel.Tensay', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Operation Manager', 'user', 'Operation Manager user', 'user', NULL, '', 'active', '', '2022-12-24 06:20:00', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(206, 'Betelhem.Melaku', 'Betelhem.Melaku', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Atikilt Tera', 'Finance', 'user', 'Finance Cashier', 'Cashier', NULL, '', 'active', '', '2023-02-01 11:04:31', 'Dagem.Adugna', NULL, NULL, 'Online'),
(32, 'Betelhem.Mengistu', 'Betelhem.Mengistu', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Property', 'user', 'Property user', 'user', NULL, '', 'waiting', '', '2022-10-04 06:51:03', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(44, 'Bezuneh.Assefa', 'Bezuneh.Assefa', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Operation Manager', 'HO Committee,manager', 'Operation Manager', 'Director', NULL, 'Sales,EMW,Tender,Agricultre,EMP,HMAESM', 'active', '', '2022-10-03 13:49:15', 'Dagem.Adugna', NULL, NULL, 'Online'),
(45, 'Birknesh.Tsegaye', 'Birknesh.Tsegaye', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Logistics', 'user', 'Logistics user', 'user', NULL, '', 'active', '', '2022-10-04 06:51:03', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(46, 'Biruk.Belete', 'Biruk.Belete', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'KHMSC', 'Procurement', 'user', 'Procurement Purchase officer', 'Purchase officer', NULL, '', 'active', '', '2023-01-20 07:10:03', 'Dagem.Adugna', NULL, NULL, 'Online'),
(232, 'Biruk.Kebede', 'Biruk.Kebede', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Ultimate Motors', 'Construction', 'user', 'Construction user', 'user', '', '', 'active', '', '2023-03-16 06:22:40', 'Dagem.Adugna', NULL, NULL, 'Online'),
(47, 'Bitania.Abeje', 'Bitania.Abeje', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Abakoran', 'HR', 'user', 'HR user', 'user', NULL, '', 'active', '', '2023-01-12 13:19:34', 'Dagem.Adugna', NULL, NULL, 'Online'),
(205, 'Bizunesh.Sewit', 'Bizunesh.Sewit', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Atikilt Tera', 'Sales', 'manager', 'Sales manager', 'manager', NULL, '', 'active', '', '2023-02-01 10:54:32', 'Dagem.Adugna', NULL, NULL, 'Online'),
(234, 'Bonsa.Mulissa', 'Bonsa.Mulissa', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Ultimate Motors', 'Construction', 'user', 'Construction user', 'user', '', '', 'active', '', '2023-03-16 06:24:15', 'Dagem.Adugna', NULL, NULL, 'Online'),
(239, 'cashier', 'cashier', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'KHMSC', 'Finance', 'manager,Branch Committee,Petty Cash Approver,Cheque Signatory,Perdiem', 'Finance Cashier', 'manager', NULL, '', 'active', 'p_100', '2022-10-03 13:37:37', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(242, 'Controller', 'Controller', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Finance', 'user,Finance controller', 'Finance Cashier', 'user', NULL, '', 'inactive', '', '2022-10-03 13:39:04', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(48, 'Dagem.Adugna', 'Dagem.Adugna', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'IT', 'Admin', 'IT', 'Admin', NULL, '', 'active', '', '2022-07-15 07:02:34', 'Dagem.Adugna', NULL, NULL, 'Online'),
(49, 'Dagemawit.Bogale', 'Dagemawit.Bogale', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Debrezit Food Ind.', 'Quality', 'manager', 'Quality manager', 'manager', NULL, '', 'active', '', '2022-10-19 12:06:49', 'Dagem.Adugna', NULL, NULL, 'Online'),
(50, 'Daniel.Matiwos', 'Daniel.Matiwos', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Abakoran', 'Procurement', 'user', 'Procurement Senior Purchase officer', 'Senior Purchase officer', NULL, '', 'active', '', '2023-01-12 12:08:51', 'Dagem.Adugna', '2023-05-26 05:24:17', 'Daniel.Matiwos', 'Online'),
(51, 'Daniel.Mekonnen', 'Daniel.Mekonnen', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'KHMSC', 'Spare Part And Warranty', 'user', 'Spare Part And Warranty user', 'user', NULL, '', 'active', '', '2023-01-05 08:37:30', 'Dagem.Adugna', NULL, NULL, 'Online'),
(260, 'dawit.getanehe', 'dawit.getanehe', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'HMAESM', 'user', NULL, 'user', '', '', 'active', '', '2023-05-20 06:38:14', 'Dagem.Adugna', NULL, NULL, 'Online'),
(200, 'dawit.guale', 'dawit.guale', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Ultimate Motors', 'Import', 'manager', 'Import manager', 'manager', NULL, '', 'active', '', '2023-02-01 05:46:54', 'Dagem.Adugna', NULL, NULL, 'Online'),
(52, 'Dawit.Melaku', 'Dawit.Melaku', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Abakoran', 'Finance', 'user', 'Finance user', 'user', NULL, '', 'active', '', '2023-01-12 11:56:31', 'Dagem.Adugna', NULL, NULL, 'Online'),
(53, 'Dereje.Behailu', 'Dereje.Behailu', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Procurement', 'user', 'Procurement Purchase officer', 'Purchase officer', NULL, '', 'active', '', '2023-01-04 07:55:36', 'Dagem.Adugna', NULL, NULL, 'Online'),
(54, 'Dereje.Bhailu', 'Dereje.Bhailu', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Ultimate Motors', 'Procurement', 'user', 'Procurement Purchase officer', 'Purchase officer', NULL, '', 'active', '', '2022-12-19 12:24:56', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(55, 'Desalegn.Whawariat', 'Desalegn.Whawariat', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Procurement and Adminstration', 'HO Committee,Cheque Signatory,manager', 'Procurement and Adminstration', 'Director', NULL, 'IT,Procurement,Audit,Property,HR,Import,Logistics,Legal,Sokkia,Procurement and Adminstration,DAP,GPS,Director of operation', 'active', 'not_50', '2022-10-03 13:51:51', 'Dagem.Adugna', NULL, NULL, 'Online'),
(238, 'Disbursement', 'Disbursement', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Finance', 'user', 'Finance Disbursement', 'Disbursement', NULL, '', 'inactive', '', '2022-10-03 13:32:25', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(56, 'DZ.Production', 'DZ.Production', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Debrezit Food Ind.', 'Production and Technic', 'user', 'Production and Technic user', 'user', NULL, '', 'waiting', '', '2022-10-18 08:25:13', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(278, 'Elsabet.Mezgebu', NULL, '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Import', 'user', NULL, 'user', '', '', 'active', '', '2023-07-01 03:55:46', 'Dagem.Adugna', NULL, NULL, 'Online'),
(57, 'Emebet.Homa', 'Emebet.Homa', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Property', 'user', 'Property Store', 'Store', NULL, '', 'active', '', '2022-10-03 13:24:30', 'Dagem.Adugna', NULL, NULL, 'Online'),
(58, 'Emebet.Solomon', 'Emebet.Solomon', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Ultimate Motors', 'Procurement', 'user', 'Procurement Senior Purchase officer', 'Senior Purchase officer', NULL, '', 'active', '', '2022-12-19 12:30:16', 'Dagem.Adugna', NULL, NULL, 'Online'),
(204, 'Endale.Girma', 'Endale.Girma', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Atikilt Tera', 'Sales', 'user', 'Sales user', 'user', NULL, '', 'active', '', '2023-02-01 10:45:17', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(59, 'Engdawork.Bekele', 'Engdawork.Bekele', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Abakoran', 'Property', 'manager,Branch Committee', 'Property manager', 'manager', NULL, '', 'active', '', '2023-01-12 12:28:42', 'Dagem.Adugna', NULL, NULL, 'Online'),
(264, 'Ephrem.Asmare', 'Ephrem.Asmare', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'KHMSC', 'Finance', 'user', NULL, 'Cashier', '', '', 'active', '', '2023-05-25 12:56:05', 'Dagem.Adugna', NULL, NULL, 'Online'),
(208, 'Ephrem.Tilahun', 'Ephrem.Tilahun', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Atikilt Tera', 'Finance', 'user', 'Finance Cashier', 'Cashier', NULL, '', 'active', '', '2023-02-01 11:24:52', 'Dagem.Adugna', NULL, NULL, 'Online'),
(252, 'Ephrem.Yemane', 'Ephrem.Yemane', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'EMW', 'user', NULL, 'user', '', '', 'active', '', '2023-04-27 05:49:23', 'Dagem.Adugna', NULL, NULL, 'Online'),
(60, 'Ermias.Bogale', 'Ermias.Bogale', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'CAD', 'manager', 'CAD manager', 'manager', NULL, '', 'waiting', '', '2022-10-04 06:51:03', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(61, 'Ermias.Feliche', 'Ermias.Feliche', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Atlas Copco', 'After_Market', 'manager', 'After_Market manager', 'manager', NULL, '', 'active', '', '2022-10-13 10:44:43', 'Dagem.Adugna', NULL, NULL, 'Online'),
(62, 'ermiyas.ambaye', 'ermiyas.ambaye', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Ultimate Motors', 'Metal Workshop', 'manager', 'Metal Workshop manager', 'manager', NULL, '', 'active', '', '2023-01-04 11:56:12', 'Dagem.Adugna', NULL, NULL, 'Online'),
(266, 'Etsegenet.Tamiru', NULL, '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Procurement', 'user', NULL, 'Senior Purchase officer', '', '', 'active', '', '2023-06-01 03:57:55', 'Dagem.Adugna', NULL, NULL, 'Online'),
(202, 'Eyerusalem.Getachew', 'Eyerusalem.Getachew', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Somali Tera', 'Sales', 'user', 'Sales user', 'user', NULL, '', 'active', '', '2023-02-01 08:31:33', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(63, 'Eyerusalem.Tariku', 'Eyerusalem.Tariku', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Procurement', 'user', 'Procurement Senior Purchase officer', 'Senior Purchase officer', NULL, '', 'inactive', '', '2023-01-10 06:08:02', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(64, 'Eyob.Assefa', 'Eyob.Assefa', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Abakoran', 'GM', 'Branch Committee,Cheque Signatory,manager', 'General Manager', 'Director', '1', 'All Departments', 'active', 'p_100', '2022-10-11 13:07:51', 'Dagem.Adugna', NULL, NULL, 'Online'),
(65, 'eyob.assefa1', 'eyob.assefa1', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Abakoran', 'Procurement', 'manager', 'Procurement manager', 'manager', NULL, '', 'inactive', '', '2023-01-24 10:57:55', 'Dagem.Adugna', NULL, NULL, 'Online'),
(66, 'Eyoel.Habtamu', 'Eyoel.Habtamu', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'IT', 'user', 'IT user', 'user', NULL, '', 'waiting', '', '2022-10-20 07:00:32', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(67, 'Fasika.abera', 'Fasika.abera', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'IT', 'Admin', 'IT user', 'Admin', NULL, '', 'active', '', '2022-10-13 06:04:46', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(68, 'Fasika.Tamiru', 'Fasika.Tamiru', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Procurement', 'user', 'Procurement Senior Purchase officer', 'Senior Purchase officer', NULL, '', 'active', '', '2023-01-25 11:51:21', 'Dagem.Adugna', NULL, NULL, 'Online'),
(236, 'Fasika.Tsegaye', 'Fasika.Tsegaye', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'IT', 'Admin', NULL, 'Admin', '', '', 'active', '', '2023-03-31 12:21:05', 'Dagem.Adugna', NULL, NULL, 'Online'),
(69, 'Fasil.Getu', 'Fasil.Getu', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Somali Tera', 'Sales', 'manager', 'General Manager', 'Director', NULL, 'All Departments', 'active', '', '2022-10-20 12:57:28', 'Dagem.Adugna', NULL, NULL, 'Online'),
(70, 'Ferewein.Dawit', 'Ferewein.Dawit', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Abakoran', 'Finance', 'user', 'Finance Cashier', 'Cashier', NULL, '', 'active', '', '2023-01-13 09:03:47', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(71, 'Fikade.Sime', 'Fikade.Sime', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Director', 'manager', 'General Manager', 'Director', NULL, 'Store,Finance,Sales,Procurement', 'waiting', '', '2022-10-19 08:24:29', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(72, 'Fikirte.Mekonnen', 'Fikirte.Mekonnen', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Atikilt Tera', 'GM', 'user', 'GM user', 'user', NULL, '', 'active', '', '2023-01-11 08:20:41', 'Dagem.Adugna', NULL, NULL, 'Online'),
(73, 'Fikirte.mengiste', 'Fikirte.mengiste', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Import', 'user', 'Import user', 'user', NULL, '', 'waiting', '', '2023-01-25 11:40:45', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(215, 'Fikirte.Wmedhin', 'Fikirte.Wmedhin', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Procurement', 'user', 'Procurement Senior Purchase officer', 'Senior Purchase officer', NULL, '', 'active', '', '2023-02-07 06:34:22', 'Dagem.Adugna', NULL, NULL, 'Online'),
(273, 'Firehiwot.Hailu', NULL, '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Sokkia', 'user', NULL, 'user', '', '', 'active', '', '2023-06-08 08:13:53', 'Dagem.Adugna', NULL, NULL, 'Online'),
(199, 'Firtuna.zegeye', 'Firtuna.zegeye', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Ultimate Motors', 'Import', 'user', 'Import user', 'user', NULL, '', 'active', '', '2023-01-31 07:38:44', 'Dagem.Adugna', NULL, NULL, 'Online'),
(253, 'Fitsum.Solomon', 'Fitsum.Solomon', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Procurement', 'user', NULL, 'Senior Purchase officer', '', '', 'active', '', '2023-05-02 08:11:42', 'Dagem.Adugna', NULL, NULL, 'Online'),
(201, 'Frehiwot.Tamene', 'Frehiwot.Tamene', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Somali Tera', 'Finance', 'user', 'Finance Cashier', 'Cashier', NULL, '', 'active', '', '2023-02-01 08:11:00', 'Dagem.Adugna', NULL, NULL, 'Online'),
(255, 'Fresh.Kebede', 'Fresh.Kebede', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'HR', 'user', NULL, 'user', '', '', 'active', '', '2023-05-04 10:27:47', 'Dagem.Adugna', NULL, NULL, 'Online'),
(74, 'Fufa.Jebesa', 'Fufa.Jebesa', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'EMP', 'manager', 'EMP manager', 'manager', NULL, '', 'active', '', '2022-10-04 06:51:03', 'Dagem.Adugna', NULL, NULL, 'Online'),
(75, 'Garo.Tilbian', 'Garo.Tilbian', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Finance', 'user', 'Finance user', 'user', NULL, '', 'waiting', '', '2022-10-19 08:26:57', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(76, 'Gashaw.Alie', 'Gashaw.Alie', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'KHMSC', 'Property', 'user', 'Property Store', 'Store', NULL, '', 'active', '', '2023-01-20 07:07:56', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(77, 'Gashaw.Kebede', 'Gashaw.Kebede', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'KHMSC', 'GM', 'Branch Committee,manager,Petty Cash Approver,Cheque Signatory', 'General Manager', 'Director', '1', 'All Departments', 'active', 'p_100', '2022-12-15 08:09:43', 'Dagem.Adugna', NULL, NULL, 'Online'),
(78, 'Gashaw.Kebede1', 'Gashaw.Kebede1', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'KHMSC', 'Procurement', 'manager', 'Procurement manager', 'manager', NULL, '', 'inactive', '', '2023-01-20 07:20:26', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(79, 'Gashu.Wendawke', 'Gashu.Wendawke', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'IT', 'Admin', 'Head of Software Development', 'Admin', NULL, '', 'active', '', '2022-10-03 14:04:46', 'Dagem.Adugna', '2023-05-27 06:53:48', 'Gashu.Wendawke', 'Online'),
(274, 'Gebremeskel.Walelign', NULL, '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Sokkia', 'user', NULL, 'user', '', '', 'active', '', '2023-06-08 08:14:43', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(80, 'Genene.Alemu', 'Genene.Alemu', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Atlas Copco', 'Compressor_Technic', 'manager', 'Compressor_Technic manager', 'manager', NULL, '', 'active', '', '2023-01-09 12:31:29', 'Dagem.Adugna', NULL, NULL, 'Online'),
(227, 'Genet.Abera', 'Genet.Abera', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Atikilt Tera', 'Property', 'manager', 'Property manager', 'manager', '', '', 'active', '', '2023-02-28 10:30:55', 'Dagem.Adugna', NULL, NULL, 'Online'),
(81, 'Getachew.Berhanu', 'Getachew.Berhanu', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Import', 'manager', 'Import manager', 'manager', NULL, '', 'active', '', '2022-10-04 06:51:03', 'Dagem.Adugna', NULL, NULL, 'Online'),
(82, 'Getnet.Minale', 'Getnet.Minale', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'CAD', 'manager', 'CAD manager', 'manager', NULL, '', 'waiting', '', '2022-10-04 06:51:03', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(83, 'Getu.Bahiru', 'Getu.Bahiru', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Ultimate Motors', 'GM', 'manager,Branch Committee', 'General Manager', 'Director', NULL, 'All Departments', 'active', '', '2022-12-19 12:34:07', 'Dagem.Adugna', NULL, NULL, 'Online'),
(196, 'girma.abe', 'girma.abe', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Ultimate Motors', 'Transport', 'manager', 'Transport manager', 'manager', NULL, '', 'active', '', '2023-01-27 08:23:07', 'Dagem.Adugna', NULL, NULL, 'Online'),
(84, 'Girma.Abebe', 'Girma.Abebe', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Audit', 'manager', 'Audit Manager', 'manager', NULL, '', 'active', '', '2022-10-04 06:51:03', 'Dagem.Adugna', NULL, NULL, 'Online'),
(85, 'Girmawi.Melaku', 'Girmawi.Melaku', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Atlas Copco', 'Finance', 'manager', 'Finance Manager', 'manager', NULL, '', 'active', '', '2023-01-09 13:00:27', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(258, 'girum.wendmagegn', 'girum.wendmagegn', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Ultimate Motors', 'Finance', 'manager', NULL, 'manager', '', '', 'active', '', '2023-05-12 12:39:14', 'Dagem.Adugna', NULL, NULL, 'Online'),
(237, 'Habtamu.Legasu', 'Habtamu.Legasu', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'IT', 'Admin', NULL, 'Admin', '', '', 'active', '', '2023-03-31 12:21:52', 'Dagem.Adugna', NULL, NULL, 'Online'),
(86, 'Hagbes.Agriculture', 'Hagbes.Agriculture', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Agricultre ', 'user', 'Agricultre  user', 'user', NULL, '', 'inactive', '', '2022-10-04 06:51:03', 'Dagem.Adugna', NULL, NULL, 'Online'),
(87, 'HAGBES.ASCS', 'HAGBES.ASCS', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'ASCS', 'user', 'ASCS user', 'user', NULL, '', 'inactive', '', '2022-10-04 06:51:03', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(88, 'Hagbes.Audit', 'Hagbes.Audit', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Audit', 'user', 'Audit user', 'user', NULL, '', 'inactive', '', '2022-10-04 06:51:03', 'Dagem.Adugna', NULL, NULL, 'Online'),
(89, 'Hagbes.CAD', 'Hagbes.CAD', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'CAD', 'user', 'CAD user', 'user', NULL, '', 'inactive', '', '2022-10-04 06:51:03', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(90, 'Hagbes.DAP', 'Hagbes.DAP', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Procurement and Adminstration', 'user', 'Procurement and Adminstration user', 'user', NULL, '', 'inactive', '', '2022-10-04 06:51:03', 'Dagem.Adugna', NULL, NULL, 'Online'),
(91, 'Hagbes.EMP', 'Hagbes.EMP', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'EMP', 'user', 'EMP user', 'user', NULL, '', 'inactive', '', '2022-10-04 06:51:03', 'Dagem.Adugna', NULL, NULL, 'Online'),
(92, 'Hagbes.EMW', 'Hagbes.EMW', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'EMW', 'user', 'EMW user', 'user', NULL, '', 'inactive', '', '2022-10-04 06:51:03', 'Dagem.Adugna', NULL, NULL, 'Online'),
(93, 'Hagbes.Finance', 'Hagbes.Finance', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Finance', 'user', 'Finance user', 'user', NULL, '', 'inactive', '', '2022-10-04 06:51:03', 'Dagem.Adugna', NULL, NULL, 'Online'),
(94, 'Hagbes.GPS', 'Hagbes.GPS', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'GPS', 'user', 'GPS user', 'user', NULL, '', 'inactive', '', '2022-10-04 06:51:03', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(95, 'HAGBES.HMAESM', 'HAGBES.HMAESM', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'HMAESM', 'user', 'HMAESM user', 'user', NULL, '', 'inactive', '', '2022-10-04 06:51:03', 'Dagem.Adugna', NULL, NULL, 'Online'),
(96, 'Hagbes.HR', 'Hagbes.HR', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'HR', 'user', 'HR user', 'user', NULL, '', 'inactive', '', '2022-10-04 06:51:03', 'Dagem.Adugna', NULL, NULL, 'Online'),
(97, 'Hagbes.Import', 'Hagbes.Import', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Import', 'user', 'Import user', 'user', NULL, '', 'inactive', '', '2022-10-04 06:51:03', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(98, 'Hagbes.IT', 'Hagbes.IT', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'IT', 'user', 'IT user', 'user', NULL, '', 'inactive', '', '2022-10-04 06:51:03', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(99, 'Hagbes.Legal', 'Hagbes.Legal', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Legal', 'user', 'Legal user', 'user', NULL, '', 'inactive', '', '2022-10-04 06:51:03', 'Dagem.Adugna', NULL, NULL, 'Online'),
(100, 'Hagbes.ProLocal', 'Hagbes.ProLocal', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Procurement (Local)', 'user', 'Procurement (Local) user', 'user', NULL, '', 'inactive', '', '2022-10-04 06:51:03', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(101, 'hagbes.property', 'hagbes.property', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Property', 'user', 'Property user', 'user', NULL, '', 'inactive', '', '2022-10-04 06:51:03', 'Dagem.Adugna', NULL, NULL, 'Online'),
(102, 'Hagbes.Sales', 'Hagbes.Sales', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Sales', 'user', 'Sales user', 'user', NULL, '', 'inactive', '', '2022-10-04 06:51:03', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(103, 'Hagbes.Sokkia', 'Hagbes.Sokkia', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Sokkia', 'user', 'Sokkia user', 'user', NULL, '', 'inactive', '', '2022-10-04 06:51:03', 'Dagem.Adugna', NULL, NULL, 'Online'),
(104, 'Hagbes.Tender', 'Hagbes.Tender', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Tender', 'user', 'Tender user', 'user', NULL, '', 'inactive', '', '2022-10-04 06:51:03', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(105, 'Hagbes.Transit', 'Hagbes.Transit', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Transit', 'user', 'Transit user', 'user', NULL, '', 'inactive', '', '2022-10-04 06:51:03', 'Dagem.Adugna', NULL, NULL, 'Online'),
(106, 'Hailegebrel.Abebe', 'Hailegebrel.Abebe', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'IT', 'Admin', 'IT Admin', 'Admin', NULL, '', 'active', '', '2023-01-26 05:28:29', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(107, 'Hailegebriel.Tilahun', 'Hailegebriel.Tilahun', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Atlas Copco', 'After_Market', 'user', 'After_Market user', 'user', NULL, '', 'waiting', '', '2022-10-13 10:41:19', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(108, 'Hana.Amde', 'Hana.Amde', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Abakoran', 'Property', 'user', 'Property Store', 'Store', NULL, '', 'active', '', '2023-01-12 12:30:45', 'Dagem.Adugna', NULL, NULL, 'Online'),
(109, 'Hanna.Haile', 'Hanna.Haile', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Abakoran', 'Procurement', 'user', 'Procurement Purchase officer', 'Purchase officer', NULL, '', 'active', '', '2023-01-24 11:18:25', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(211, 'helen.asefa', 'helen.asefa', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Ultimate Motors', 'SP', 'user', 'SP user', 'user', NULL, '', 'active', '', '2023-02-03 11:32:27', 'Dagem.Adugna', NULL, NULL, 'Online'),
(110, 'Helen.Tesfaye', 'Helen.Tesfaye', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Debrezit Food Ind.', 'Finance', 'user', 'Finance user', 'user', NULL, '', 'active', '', '2023-01-04 08:27:00', 'Dagem.Adugna', NULL, NULL, 'Online'),
(111, 'Helen.Tilahun', 'Helen.Tilahun', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Procurement and Adminstration', 'user', 'Procurement and Adminstration user', 'user', NULL, '', 'active', '', '2023-01-25 11:53:45', 'Dagem.Adugna', NULL, NULL, 'Online'),
(112, 'Henok.Kassie', 'Henok.Kassie', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Finance', 'user', 'Finance user', 'user', NULL, '', 'active', '', '2022-12-27 13:25:56', 'Dagem.Adugna', NULL, NULL, 'Online'),
(113, 'Hiwot.Haileyesus', 'Hiwot.Haileyesus', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'KHMSC', 'Procurement', 'user', 'Procurement Purchase officer', 'Purchase officer', NULL, '', 'active', '', '2023-01-20 07:10:58', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(272, 'Hiwot.Kifle', NULL, '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Sokkia', 'user', NULL, 'user', '', '', 'waiting', '', '2023-06-08 08:13:08', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(114, 'Hlawit.Mulu', 'Hlawit.Mulu', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Import', 'user', 'Import user', 'user', NULL, '', 'waiting', '', '2023-01-25 11:43:36', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(115, 'Ibralem.Fikadu', 'Ibralem.Fikadu', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Debrezit Food Ind.', 'General Service', 'manager', 'General Service manager', 'manager', NULL, '', 'waiting', '', '2022-10-19 12:07:48', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(116, 'Israel.Taddesse', 'Israel.Taddesse', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Procurement', 'manager,HO Committee', 'Procurement Manager', 'manager', NULL, '', 'active', '', '2022-10-03 13:18:24', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(117, 'Kassahun.Bahiru ', 'Kassahun.Bahiru ', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'KHMSC', 'Technic', 'user', 'Technic user', 'user', NULL, '', 'active', '', '2023-01-05 07:47:38', 'Dagem.Adugna', NULL, NULL, 'Online'),
(118, 'Kebede.H/Mariam   ', 'Kebede.H/Mariam   ', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'KHMSC', 'Technic', 'user', 'Technic user', 'user', NULL, '', 'active', '', '2023-01-16 12:33:05', 'Dagem.Adugna', NULL, NULL, 'Online'),
(262, 'Kebede.Yimer', 'Kebede.Yimer', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Ultimate Motors', 'Metal Workshop', 'user', NULL, 'user', '', '', 'active', '', '2023-05-24 07:58:30', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(119, 'Kemila.Bahiru', 'Kemila.Bahiru', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Debrezit Food Ind.', 'Production and Technic', 'user', 'Production and Technic user', 'user', NULL, '', 'active', '', '2023-01-04 08:22:03', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(120, 'Kibkab.Wubie', 'Kibkab.Wubie', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Setavi', 'Director', 'manager,Branch Committee', 'General Manager', 'Director', '', 'All Departments', 'active', '', '2022-10-19 08:09:40', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(121, 'Legesse.Shenkute', 'Legesse.Shenkute', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Ultimate Motors', 'GM', 'user', 'GM user', 'user', NULL, '', 'active', '', '2023-01-10 05:35:45', 'Dagem.Adugna', NULL, NULL, 'Online'),
(122, 'Lemi.Eniyew', 'Lemi.Eniyew', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Ultimate Motors', 'Customer Service', 'manager', 'Customer Service manager', 'manager', NULL, '', 'inactive', '', '2023-01-10 11:11:01', 'Dagem.Adugna', NULL, NULL, 'Online'),
(197, 'leul.yekoye', 'leul.yekoye', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Ultimate Motors', 'Transport', 'user', 'Transport user', 'user', NULL, '', 'active', '', '2023-01-27 08:25:23', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(123, 'Leulseged.Mulugeta', 'Leulseged.Mulugeta', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'KHMSC', 'EMW', 'manager', 'EMW manager', 'manager', NULL, '', 'active', '', '2023-01-10 13:18:27', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(233, 'Lina.Dera', 'Lina.Dera', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Ultimate Motors', 'Construction', 'user', 'Construction user', 'user', '', '', 'active', '', '2023-03-16 06:24:04', 'Dagem.Adugna', NULL, NULL, 'Online'),
(124, 'Mahlet.Kebotal', 'Mahlet.Kebotal', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'KHMSC', 'Procurement', 'Branch Committee,user', 'Procurement Senior Purchase officer', 'Senior Purchase officer', NULL, '', 'active', '', '2023-01-12 06:24:36', 'Dagem.Adugna', NULL, NULL, 'Online'),
(125, 'Mahlet.Nigussie', 'Mahlet.Nigussie', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'IT', 'Admin', 'IT Admin', 'Admin', NULL, '', 'active', '', '2022-10-03 11:47:46', 'Dagem.Adugna', NULL, NULL, 'Online'),
(126, 'Markos.Rafat', 'Markos.Rafat', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Legal', 'user', 'Legal user', 'user', NULL, '', 'active', '', '2022-12-21 06:21:16', 'Dagem.Adugna', NULL, NULL, 'Online'),
(257, 'Megersa.Desalegn', 'Megersa.Desalegn', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Audit', 'user', NULL, 'user', '', '', 'active', '', '2023-05-11 05:40:50', 'Dagem.Adugna', NULL, NULL, 'Online'),
(127, 'Meharu.Abuye', 'Meharu.Abuye', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Abakoran', 'Workshop', 'manager,Branch Committee', 'Workshop manager', 'manager', NULL, '', 'active', '', '2022-10-11 12:36:22', 'Dagem.Adugna', NULL, NULL, 'Online'),
(128, 'mehbuba.abera', 'mehbuba.abera', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'IT', 'Admin', 'IT user', 'Admin', NULL, '', 'active', '', '2023-01-13 11:05:54', 'Dagem.Adugna', NULL, NULL, 'Online'),
(209, 'Mekdes.Melaku', 'Mekdes.Melaku', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Atikilt Tera', 'Tender', 'user', 'Tender user', 'user', NULL, '', 'active', '', '2023-02-01 11:42:17', 'Dagem.Adugna', NULL, NULL, 'Online'),
(129, 'Mekonen.Hadis', 'Mekonen.Hadis', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Debrezit Food Ind.', 'Production and Technic', 'manager', 'Production and Technic manager', 'manager', NULL, '', 'active', '', '2022-10-18 08:27:16', 'Dagem.Adugna', NULL, NULL, 'Online'),
(259, 'Melaku.Kebede', 'Melaku.Kebede', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Property', 'user', NULL, 'user', '', '', 'active', '', '2023-05-15 06:15:57', 'Dagem.Adugna', '2023-07-20 08:55:02', 'Melaku.Kebede', 'Online'),
(243, 'Meles.debase', 'Meles.debase', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Ultimate Motors', 'EMW', 'manager', NULL, 'manager', '', '', 'active', '', '2023-04-06 11:20:06', 'Dagem.Adugna', NULL, NULL, 'Online'),
(280, 'melkam.alemnew', NULL, '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'IT', 'user', NULL, 'user', '', '', 'active', '', '2023-07-06 10:10:35', 'Dagem.Adugna', NULL, NULL, 'Online'),
(130, 'Meneyamer.Seneshaw', 'Meneyamer.Seneshaw', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'HR', 'manager', 'HR manager', 'manager', NULL, '', 'waiting', '', '2022-10-04 06:51:03', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(219, 'Meseret.Ayalew', 'Meseret.Ayalew', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'HR', 'manager', 'HR manager', 'manager', NULL, '', 'active', '', '2023-02-14 13:42:51', 'Dagem.Adugna', NULL, NULL, 'Online'),
(131, 'Mesfin.Tilahun', 'Mesfin.Tilahun', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Atlas Copco', 'After_Market', 'user', 'After_Market user', 'user', NULL, '', 'active', '', '2022-10-13 10:42:54', 'Dagem.Adugna', NULL, NULL, 'Online'),
(132, 'Mestawot.Berta', 'Mestawot.Berta', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Finance', 'user', 'Finance Cashier', 'Cashier', NULL, '', 'waiting', '', '2022-10-03 13:39:04', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(133, 'Milkiyas.Duguma', 'Milkiyas.Duguma', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'IT', 'user', 'IT user', 'user', NULL, '', 'inactive', '', '2022-10-04 06:51:03', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(134, 'mintesenot.zewdu', 'mintesenot.zewdu', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'KHMSC', 'Technic', 'user', 'Technic user', 'user', NULL, '', 'active', '', '2023-01-05 07:33:57', 'Dagem.Adugna', NULL, NULL, 'Online'),
(135, 'Moges.Berihun', 'Moges.Berihun', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Import', 'user', 'Import user', 'user', NULL, '', 'waiting', '', '2023-01-25 11:45:16', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(136, 'Mohammed.Yimam', 'Mohammed.Yimam', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Ultimate Motors', 'Workshop', 'manager', 'Workshop manager', 'manager', NULL, '', 'active', '', '2022-12-19 12:51:03', 'Dagem.Adugna', NULL, NULL, 'Online'),
(137, 'Natnael.Girma', 'Natnael.Girma', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'KHMSC', 'Spare Part And Warranty', 'user', 'Spare Part And Warranty user', 'user', NULL, '', 'active', '', '2023-01-05 08:45:28', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(138, 'Natnael.Tadesse', 'Natnael.Tadesse', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'IT', 'Admin', 'IT Admin', 'Admin', NULL, '', 'active', '', '2022-10-13 05:22:49', 'Dagem.Adugna', NULL, NULL, 'Online'),
(139, 'Nebyou.Sereke', 'Nebyou.Sereke', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'HR', 'manager', 'HR manager', 'manager', NULL, '', 'waiting', '', '2022-10-04 06:51:03', 'Dagem.Adugna', NULL, NULL, 'Offline');
INSERT INTO `account` (`unique_id`, `Username`, `name`, `phone`, `email`, `password`, `company`, `department`, `type`, `position`, `role`, `additional_role`, `managing`, `status`, `cheque_percent`, `creation_date`, `created_by`, `update_date`, `updated_by`, `user_status`) VALUES
(140, 'Negash.Mamo', 'Negash.Mamo', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Ultimate Motors', 'Procurement', 'user', 'Procurement Purchase officer', 'Purchase officer', NULL, '', 'active', '', '2023-01-16 12:20:20', 'Dagem.Adugna', NULL, NULL, 'Online'),
(141, 'Nimona.Gadissa', 'Nimona.Gadissa', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'EMW', 'manager', 'EMW manager', 'manager', NULL, '', 'active', '', '2022-10-04 06:51:03', 'Dagem.Adugna', NULL, NULL, 'Online'),
(142, 'Rahel.Befkadu', 'Rahel.Befkadu', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Property', 'user', 'Property user', 'user', NULL, '', 'inactive', '', '2022-10-04 06:51:03', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(143, 'Rahel.Bezuayew', 'Rahel.Bezuayew', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Ultimate Motors', 'Finance', 'manager,Petty Cash Approver', 'Finance manager', 'manager', NULL, '', 'active', '', '2022-12-19 12:36:47', 'Dagem.Adugna', NULL, NULL, 'Online'),
(275, 'Ribika.bahiru', NULL, '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Ultimate Motors', 'EMW', 'user', NULL, 'user', '', '', 'active', '', '2023-06-10 05:33:50', 'Dagem.Adugna', NULL, NULL, 'Online'),
(144, 'Seblewongel.Tamire', 'Seblewongel.Tamire', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Procurement', 'user', 'Procurement Purchase officer', 'Purchase officer', NULL, '', 'active', '', '2022-10-03 13:23:10', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(145, 'Seblework.Kebede', 'Seblework.Kebede', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Property', 'user', 'Property user', 'user', NULL, '', 'active', '', '2022-10-04 06:51:03', 'Dagem.Adugna', NULL, NULL, 'Online'),
(203, 'Seifu.Damte', 'Seifu.Damte', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Atikilt Tera', 'Sales', 'user', 'Sales user', 'user', NULL, '', 'active', '', '2023-02-01 10:03:21', 'Dagem.Adugna', NULL, NULL, 'Online'),
(230, 'Selamawit.Ababu', 'Selamawit.Ababu', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Procurement', 'user', 'Procurement Senior Purchase officer', 'Senior Purchase officer', '', '', 'active', '', '2023-03-14 13:05:00', 'Dagem.Adugna', NULL, NULL, 'Online'),
(261, 'selamawit.ayele', 'selamawit.ayele', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Finance', 'Finance controller', NULL, 'user', '', '', 'active', '', '2023-05-24 07:45:09', 'Dagem.Adugna', NULL, NULL, 'Online'),
(212, 'selamawit.belachew', 'selamawit.belachew', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Atlas Copco', 'After_Market', 'manager', 'After_Market manager', 'manager', NULL, '', 'waiting', '', '2023-02-04 09:12:49', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(146, 'Selamawit.Metasebia', 'Selamawit.Metasebia', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Finance', 'user', 'Finance Cashier', 'Cashier', NULL, '', 'active', '', '2023-01-10 09:02:56', 'Dagem.Adugna', NULL, NULL, 'Online'),
(147, 'Selamawit.Tezera', 'Selamawit.Tezera', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Procurement', 'user', 'Procurement Senior Purchase officer', 'Senior Purchase officer', NULL, '', 'inactive', '', '2022-10-03 13:20:22', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(148, 'Semira.Sied', 'Semira.Sied', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Logistics', 'user', 'Logistics user', 'user', NULL, '', 'active', '', '2022-12-30 07:42:12', 'Dagem.Adugna', NULL, NULL, 'Online'),
(149, 'Sevag.Behesnilian', 'Sevag.Behesnilian', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Owner', 'HO Committee,Owner,Cheque Signatory', 'Maniging Director', 'Owner', NULL, '', 'active', 'p_100', '2022-10-03 13:54:58', 'Dagem.Adugna', NULL, NULL, 'Online'),
(277, 'Shimelash.Wondale', NULL, '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Legal', 'user', NULL, 'user', '', '', 'active', '', '2023-06-30 10:32:29', 'Dagem.Adugna', NULL, NULL, 'Online'),
(267, 'shiwa.minale', NULL, '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Audit', 'user', NULL, 'user', '', '', 'active', '', '2023-06-06 05:16:54', 'Dagem.Adugna', NULL, NULL, 'Online'),
(150, 'Sibhat.Zelalem', 'Sibhat.Zelalem', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Ultimate Motors', 'Property', 'manager', 'Property manager', 'manager', NULL, '', 'active', '', '2022-12-19 12:53:07', 'Dagem.Adugna', NULL, NULL, 'Online'),
(240, 'Signatory', 'Signatory', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Owner', 'HO Committee,Owner,Cheque Signatory', 'Disbursement manager', 'Owner', NULL, '', 'inactive', 'p_100', '2022-10-03 13:55:59', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(241, 'signatory_perdiem', 'signatory_perdiem', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Finance', 'Cheque Signatory,Perdiem', 'Finance user', 'user', NULL, '', 'inactive', 'p_50', '2022-10-03 13:58:46', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(247, 'Sileshi.Addisu', 'Sileshi.Addisu', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Abakoran', 'Finance', 'manager,Branch Committee', NULL, 'manager', '', '', 'active', '', '2023-04-25 08:07:06', 'Dagem.Adugna', NULL, NULL, 'Online'),
(151, 'Sileshi.Bokona', 'Sileshi.Bokona', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Finance', 'user,Finance controller', 'Finance user', 'user', NULL, '', 'active', '', '2022-10-03 13:34:50', 'Dagem.Adugna', NULL, NULL, 'Online'),
(265, 'Simegn.Merga', 'Simegn.Merga', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'KHMSC', 'Finance', 'user', NULL, 'Disbursement', '', '', 'active', '', '2023-05-25 13:02:37', 'Dagem.Adugna', NULL, NULL, 'Online'),
(152, 'Simeneh.Tadesse', 'Simeneh.Tadesse', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Magdalinos', 'Director', 'manager,Branch Committee', 'General Manager', 'Director', NULL, 'All Departments', 'waiting', '', '2022-10-20 07:13:53', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(153, 'Sisay.Abera', 'Sisay.Abera', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Property', 'user', 'Property Store', 'Store', NULL, '', 'waiting', '', '2022-12-30 06:48:34', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(154, 'Sisay.Feleke', 'Sisay.Feleke', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Atlas Copco', 'Sales', 'manager', 'Sales manager', 'manager', NULL, '', 'active', '', '2023-01-09 12:47:41', 'Dagem.Adugna', NULL, NULL, 'Online'),
(155, 'Solomon.Bekele', 'Solomon.Bekele', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Director', 'HO Committee,Cheque Signatory,manager', 'Finance Director', 'Director', NULL, 'Finance,CAD', 'active', 'p_100', '2022-10-03 13:36:20', 'Dagem.Adugna', NULL, NULL, 'Online'),
(156, 'Solomon.Eshetu', 'Solomon.Eshetu', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Finance', 'manager', 'Finance', 'manager', NULL, '', 'active', '', '2022-10-03 13:33:56', 'Dagem.Adugna', NULL, NULL, 'Online'),
(157, 'Solomon.Yeshiwas', 'Solomon.Yeshiwas', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'IT', 'user', 'IT', 'user', NULL, '', 'active', '', '2023-01-11 08:59:34', 'Dagem.Adugna', NULL, NULL, 'Online'),
(198, 'Surafel.Kebede', 'Surafel.Kebede', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Gofa', 'GM', 'manager', 'General Manager', 'Director', NULL, 'All Departments', 'active', '', '2023-01-30 10:22:52', 'Dagem.Adugna', NULL, NULL, 'Online'),
(158, 'Tadesse.Gebre', 'Tadesse.Gebre', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'KHMSC', 'Property', 'manager', 'Property manager', 'manager', NULL, '', 'active', '', '2023-01-20 07:06:00', 'Dagem.Adugna', NULL, NULL, 'Online'),
(159, 'Tahir.Mamo', 'Tahir.Mamo', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Finance', 'user', 'Finance user', 'user', NULL, '', 'waiting', '', '2022-10-04 06:51:03', 'Dagem.Adugna', NULL, NULL, 'Online'),
(160, 'Talar.Behesnilian', 'Talar.Behesnilian', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Owner', 'Owner,Cheque Signatory', 'Assistant Maniging Director', 'Owner', NULL, '', 'active', 'p_100', '2022-10-03 14:03:26', 'Dagem.Adugna', NULL, NULL, 'Online'),
(161, 'Tariku.Shewatatek', 'Tariku.Shewatatek', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Magdalinos', 'Export', 'manager', 'Export manager', 'manager', NULL, '', 'waiting', '', '2022-10-20 07:15:17', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(162, 'Tegenu.Matewos', 'Tegenu.Matewos', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'IT', 'manager', 'IT Manager', 'manager', NULL, '', 'active', '', '2022-10-03 12:34:02', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(195, 'Tekalegn.Berako', 'Tekalegn.Berako', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'IT', 'user', 'IT user', 'user', NULL, '', 'active', '', '2023-01-26 13:56:40', 'Dagem.Adugna', NULL, NULL, 'Online'),
(163, 'Teklemariam.Yirgu', 'Teklemariam.Yirgu', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Sokkia', 'manager', 'Sokkia manager', 'manager', NULL, '', 'active', '', '2022-10-04 06:51:03', 'Dagem.Adugna', NULL, NULL, 'Online'),
(256, 'tempperdiem.signatory', 'tempperdiem.signatory', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Finance', 'Perdiem,Cheque Signatory', NULL, 'user', '', '', 'inactive', 'p_50', '2023-05-08 12:22:36', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(164, 'temp_account', 'temp_account', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Finance', 'user', 'IT Admin', 'Cashier', '0', '', 'active', '', '2022-12-30 07:38:21', 'Dagem.Adugna', NULL, NULL, 'Online'),
(165, 'Tesfaye.Geleso', 'Tesfaye.Geleso', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Somali Tera', 'Sales', 'manager', 'Sales user', 'user', NULL, '', 'waiting', '', '2022-10-20 12:55:19', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(210, 'Tesfaye.Tilahun', 'Tesfaye.Tilahun', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'GPS', 'user', 'GPS user', 'user', NULL, '', 'active', '', '2023-02-03 07:35:33', 'Dagem.Adugna', NULL, NULL, 'Online'),
(166, 'test_dis', 'test_dis', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Abakoran', 'Disbursement', 'manager', 'Disbursement manager', 'manager', NULL, '', 'waiting', '', '2022-11-12 07:23:58', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(167, 'test_finance', 'test_finance', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Abakoran', 'Finance', 'manager', 'Finance manager', 'manager', NULL, '', 'waiting', '', '2022-11-12 07:26:45', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(168, 'test_store', 'test_store', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Abakoran', 'Property', 'user', 'Property Store', 'Store', NULL, '', 'waiting', '', '2022-11-09 12:37:48', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(169, 'Tewodros.Dejene', 'Tewodros.Dejene', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'KHMSC', 'Technic', 'user', 'Technic user', 'user', NULL, '', 'active', '', '2023-01-05 09:33:32', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(269, 'Tigist.Haile', NULL, '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Audit', 'user', NULL, 'user', '', '', 'active', '', '2023-06-06 05:24:42', 'Dagem.Adugna', NULL, NULL, 'Online'),
(170, 'Tigist.Hailu', 'Tigist.Hailu', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Abakoran', 'Property', 'user', 'Property Store', 'Store', NULL, '', 'active', '', '2023-01-12 12:53:19', 'Dagem.Adugna', NULL, NULL, 'Online'),
(171, 'Tilahun.Abebe', 'Tilahun.Abebe', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Agricultre ', 'manager', 'Agricultre  manager', 'manager', NULL, '', 'active', '', '2022-10-04 06:51:03', 'Dagem.Adugna', NULL, NULL, 'Online'),
(172, 'Tolossa.Angassa', 'Tolossa.Angassa', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Legal', 'user', 'Legal user', 'user', NULL, '', 'active', '', '2022-12-21 06:26:28', 'Dagem.Adugna', NULL, NULL, 'Online'),
(173, 'Tsedale.H/Mariam', 'Tsedale.H/Mariam', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'KHMSC', 'Property', 'user', 'Property Store', 'Store', NULL, '', 'active', '', '2023-01-20 07:09:00', 'Dagem.Adugna', NULL, NULL, 'Online'),
(174, 'Tsega.Mamo', 'Tsega.Mamo', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Finance', 'user', 'Finance user', 'user', NULL, '', 'waiting', '', '2022-10-04 06:51:03', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(175, 'Tsegaye.Mekasha', 'Tsegaye.Mekasha', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Legal', 'manager', 'Legal manager', 'manager', NULL, '', 'active', '', '2022-10-04 06:51:03', 'Dagem.Adugna', NULL, NULL, 'Online'),
(176, 'Tsegaye.Tibebu', 'Tsegaye.Tibebu', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Abakoran', 'Finance', 'manager,Branch Committee', 'Finance manager', 'manager', NULL, '', 'inactive', '', '2023-01-12 11:37:00', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(177, 'Tsige.Tesfu', 'Tsige.Tesfu', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'HR', 'manager', 'HR manager', 'manager', NULL, '', 'waiting', '', '2022-10-04 06:51:03', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(178, 'Tsilat.Gmeskel', 'Tsilat.Gmeskel', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Atlas Copco', 'Admin', 'user', 'Admin user', 'user', NULL, '', 'active', '', '2022-10-13 10:39:17', 'Dagem.Adugna', NULL, NULL, 'Online'),
(179, 'Vighen.Behesnilian', 'Vighen.Behesnilian', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Owner', 'Owner,Cheque Signatory', 'Assistant Maniging Director', 'Owner', NULL, '', 'active', 'p_100', '2022-10-03 14:00:31', 'Dagem.Adugna', NULL, NULL, 'Online'),
(180, 'Wondwosen.Lakew', 'Wondwosen.Lakew', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Transit', 'manager', 'Transit manager', 'manager', NULL, '', 'inactive', '', '2022-10-04 06:51:03', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(254, 'wondwossen.Abate', 'wondwossen.Abate', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Yohannes', 'Manager', 'manager', NULL, 'manager', '', '', 'active', '', '2023-05-03 12:30:29', 'Dagem.Adugna', NULL, NULL, 'Online'),
(181, 'wondwossen.gedle', 'wondwossen.gedle', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'YAMAHA', 'GM', 'manager,Branch Committee', 'General Manager', 'Director', NULL, 'All Departments', 'active', '', '2023-01-06 08:06:09', 'Dagem.Adugna', NULL, NULL, 'Online'),
(182, 'Wondwossen.Lakew', 'Wondwossen.Lakew', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Logistics', 'manager', 'Logistics manager', 'manager', NULL, '', 'active', '', '2022-12-19 10:23:07', 'Dagem.Adugna', NULL, NULL, 'Online'),
(183, 'Wubishet.Getachew', 'Wubishet.Getachew', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Tender', 'manager', 'Tender manager', 'manager', NULL, '', 'active', '', '2022-10-04 06:51:03', 'Dagem.Adugna', NULL, NULL, 'Online'),
(245, 'Wubit.Delil', 'Wubit.Delil', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Director of operation', 'user', NULL, 'user', '', '', 'active', '', '2023-04-08 05:32:33', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(251, 'Yafet.Tewodros', 'Yafet.Tewodros', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Tender', 'user', NULL, 'user', '', '', 'active', '', '2023-04-27 05:45:22', 'Dagem.Adugna', NULL, NULL, 'Online'),
(213, 'yalelet.yohannes', 'yalelet.yohannes', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Atlas Copco', 'Epiroc', 'manager', 'Epiroc manager', 'manager', NULL, '', 'active', '', '2023-02-04 09:15:20', 'Dagem.Adugna', NULL, NULL, 'Online'),
(184, 'Yalelet.Yohannis', 'Yalelet.Yohannis', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Atlas Copco', 'Sales', 'user', 'Sales user', 'user', NULL, '', 'active', '', '2023-01-09 12:17:15', 'Dagem.Adugna', NULL, NULL, 'Online'),
(271, 'Yalemwork.Tafesswork', NULL, '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Sokkia', 'user', NULL, 'user', '', '', 'waiting', '', '2023-06-08 08:07:14', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(185, 'Yared.Kinfe', 'Yared.Kinfe', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'KHMSC', 'Finance', 'manager,Branch Committee,Petty Cash Approver,Cheque Signatory,Perdiem', 'Finance manager', 'manager', NULL, '', 'active', 'p_100', '2023-01-20 07:01:43', 'Dagem.Adugna', NULL, NULL, 'Online'),
(226, 'Yehualawerk.Beyene', 'Yehualawerk.Beyene', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Ultimate Motors', 'Customer Service', 'manager', 'Customer Service manager', 'manager', NULL, '', 'active', '', '2023-02-24 07:49:08', 'Dagem.Adugna', NULL, NULL, 'Online'),
(186, 'Yekoye.Nebie', 'Yekoye.Nebie', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'GPS', 'manager', 'GPS manager', 'manager', NULL, '', 'active', '', '2022-10-04 06:51:03', 'Dagem.Adugna', NULL, NULL, 'Online'),
(187, 'Yibralem.Fikadu', 'Yibralem.Fikadu', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Debrezit Food Ind.', 'HR', 'user', 'HR user', 'user', NULL, '', 'active', '', '2023-01-04 11:17:39', 'Dagem.Adugna', NULL, NULL, 'Online'),
(229, 'Yiwag.Abebe', 'Yiwag.Abebe', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Ultimate Motors', 'Finance', 'user', 'Finance Cashier', 'Cashier', '', '', 'waiting', '', '2023-03-09 13:32:24', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(188, 'Yohannes.Fulas', 'Yohannes.Fulas', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Debrezit Food Ind.', 'Director', 'manager,Branch Committee', 'General Manager', 'Director', NULL, 'All Departments', 'active', '', '2022-10-18 08:29:28', 'Dagem.Adugna', NULL, NULL, 'Online'),
(189, 'Zeinba.Mohammed', 'Zeinba.Mohammed', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Ultimate Motors', 'Procurement', 'user', 'Procurement user', 'user', NULL, '', 'active', '', '2023-01-25 07:28:38', 'Dagem.Adugna', NULL, NULL, 'Online'),
(190, 'Zeineba.Abdulkadir', 'Zeineba.Abdulkadir', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Atlas Copco', 'Compressor_Technic', 'user', 'Compressor_Technic user', 'user', NULL, '', 'active', '', '2023-01-09 12:26:39', 'Dagem.Adugna', NULL, NULL, 'Offline'),
(191, 'Zewde.Woldeyes', 'Zewde.Woldeyes', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Procurement', 'user', 'Procurement Purchase officer', 'Purchase officer', NULL, '', 'active', '', '2022-10-03 13:22:17', 'Dagem.Adugna', NULL, NULL, 'Online'),
(192, 'Zewdu.Tesfaye', 'Zewdu.Tesfaye', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Ultimate Motors', 'Property', 'manager', 'Property manager', 'manager', NULL, '', 'inactive', '', '2022-10-03 13:30:05', 'Dagem.Adugna', NULL, NULL, 'Online'),
(193, 'Zewengel.Berhane', 'Zewengel.Berhane', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Hagbes HQ.', 'Property', 'user', 'Property Store', 'Store', NULL, '', 'active', '', '2022-10-03 13:28:46', 'Dagem.Adugna', NULL, NULL, 'Online'),
(194, 'Zinash.Abayneh', 'Zinash.Abayneh', '+251911474028', 'dagem.adugna@hagbes.com', '202cb962ac59075b964b07152d234b70', 'Ultimate Motors', 'Procurement', 'user', 'Procurement Purchase officer', 'Purchase officer', NULL, '', 'active', '', '2022-12-19 12:27:09', 'Dagem.Adugna', NULL, NULL, 'Online');

-- --------------------------------------------------------

--
-- Table structure for table `account_types`
--

DROP TABLE IF EXISTS `account_types`;
CREATE TABLE IF NOT EXISTS `account_types` (
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `account_types`
--

INSERT INTO `account_types` (`name`) VALUES
('Admin'),
('Branch Committee'),
('Cheque Signatory'),
('Finance controller'),
('HO Committee'),
('manager'),
('Owner'),
('Perdiem'),
('Petty Cash Approver'),
('user');

-- --------------------------------------------------------

--
-- Table structure for table `admin_settings`
--

DROP TABLE IF EXISTS `admin_settings`;
CREATE TABLE IF NOT EXISTS `admin_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `logout_time_min` int NOT NULL,
  `month_limit_consumer_good` int NOT NULL,
  `pms_auto_request` int NOT NULL,
  `surveyLimit` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `admin_settings`
--

INSERT INTO `admin_settings` (`id`, `logout_time_min`, `month_limit_consumer_good`, `pms_auto_request`, `surveyLimit`) VALUES
(1, 30, 2, 21, NULL),
(8, 120, 2, 21, NULL),
(9, 120, 2, 21, '2023-07-08');

-- --------------------------------------------------------

--
-- Table structure for table `auto_request`
--

DROP TABLE IF EXISTS `auto_request`;
CREATE TABLE IF NOT EXISTS `auto_request` (
  `id` int NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `requests` varchar(2000) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `banks`
--

DROP TABLE IF EXISTS `banks`;
CREATE TABLE IF NOT EXISTS `banks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bank` varchar(500) NOT NULL,
  `added_by` varchar(500) NOT NULL,
  `added_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `catagory`
--

DROP TABLE IF EXISTS `catagory`;
CREATE TABLE IF NOT EXISTS `catagory` (
  `catagory` varchar(100) NOT NULL,
  `display_name` varchar(50) NOT NULL,
  `image` varchar(100) NOT NULL,
  `path` varchar(100) NOT NULL DEFAULT 'requests/requestForm.php',
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

-- --------------------------------------------------------

--
-- Table structure for table `cheque_info`
--

DROP TABLE IF EXISTS `cheque_info`;
CREATE TABLE IF NOT EXISTS `cheque_info` (
  `cpv_no` int NOT NULL AUTO_INCREMENT,
  `cheque_no` varchar(255) NOT NULL,
  `providing_company` varchar(500) NOT NULL,
  `cluster_id` int NOT NULL,
  `purchase_order_ids` varchar(400) NOT NULL,
  `bank` varchar(400) NOT NULL,
  `cheque_amount` double NOT NULL,
  `withholding` double NOT NULL,
  `prepared_percent` int NOT NULL,
  `created_by` varchar(400) NOT NULL,
  `status` varchar(200) NOT NULL DEFAULT 'pending',
  `cheque_company` varchar(200) DEFAULT NULL,
  `signatory` varchar(500) DEFAULT NULL,
  `cheque_percent` varchar(100) DEFAULT NULL,
  `company` varchar(300) NOT NULL,
  `creation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `void` tinyint(1) NOT NULL DEFAULT '0',
  `final` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`cpv_no`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `cluster`
--

DROP TABLE IF EXISTS `cluster`;
CREATE TABLE IF NOT EXISTS `cluster` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` varchar(300) NOT NULL,
  `status` varchar(100) NOT NULL DEFAULT 'Pending',
  `price` double DEFAULT NULL,
  `Remarks` varchar(2000) DEFAULT NULL,
  `company` varchar(100) NOT NULL,
  `processing_company` varchar(200) DEFAULT NULL,
  `procurement_company` varchar(255) DEFAULT NULL,
  `finance_company` varchar(255) DEFAULT NULL,
  `cheque_company` varchar(255) DEFAULT NULL,
  `compiled_by` varchar(100) NOT NULL,
  `Checked_by` varchar(255) DEFAULT NULL,
  `Finance_approved` varchar(255) DEFAULT NULL,
  `cashier` varchar(255) DEFAULT NULL,
  `cheque_signatories` varchar(500) DEFAULT NULL,
  `cheque_percent` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `cluster_deleted`
--

DROP TABLE IF EXISTS `cluster_deleted`;
CREATE TABLE IF NOT EXISTS `cluster_deleted` (
  `id` int NOT NULL,
  `type` varchar(300) NOT NULL,
  `status` varchar(100) NOT NULL,
  `price` double DEFAULT NULL,
  `company` varchar(100) NOT NULL,
  `compiled_by` varchar(100) NOT NULL,
  `timestamp` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `committee_approval`
--

DROP TABLE IF EXISTS `committee_approval`;
CREATE TABLE IF NOT EXISTS `committee_approval` (
  `id` int NOT NULL AUTO_INCREMENT,
  `committee_member` varchar(100) NOT NULL,
  `status` varchar(100) NOT NULL,
  `remark` varchar(30000) NOT NULL,
  `cluster_id` int NOT NULL,
  `timestamp` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `committee_member` (`committee_member`),
  KEY `cluster_id` (`cluster_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `comp`
--

DROP TABLE IF EXISTS `comp`;
CREATE TABLE IF NOT EXISTS `comp` (
  `Name` varchar(100) NOT NULL,
  `type` varchar(30) NOT NULL,
  `main` varchar(100) NOT NULL DEFAULT 'Hagbes HQ.',
  `logo` varchar(1000) NOT NULL,
  `IT` tinyint(1) NOT NULL DEFAULT '0',
  `property` tinyint(1) NOT NULL DEFAULT '0',
  `procurement` tinyint(1) NOT NULL DEFAULT '0',
  `finance` tinyint(1) NOT NULL DEFAULT '0',
  `cheque_signatory` tinyint(1) NOT NULL DEFAULT '0',
  `perdiem` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`Name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `comp`
--

INSERT INTO `comp` (`Name`, `type`, `main`, `logo`, `IT`, `property`, `procurement`, `finance`, `cheque_signatory`, `perdiem`) VALUES
('Abakoran', 'Branch', 'Hagbes HQ.', 'Hagbeslogo.jpg', 0, 0, 0, 0, 0, 0),
('Akaki', 'Sister', 'Hagbes HQ.', 'Hagbeslogo.jpg', 0, 0, 0, 0, 0, 0),
('Atikilt Tera', 'Branch', 'Hagbes HQ.', 'Hagbeslogo.jpg', 0, 0, 0, 0, 0, 0),
('Atlas Copco', 'Branch', 'Hagbes HQ.', 'atlascopco.png', 0, 0, 0, 0, 1, 0),
('Besco', 'Sister', 'Hagbes HQ.', 'Hagbeslogo.jpg', 0, 0, 0, 0, 0, 0),
('Bestav Tech', 'Sister', 'Hagbes HQ.', 'Hagbeslogo.jpg', 0, 0, 0, 0, 0, 0),
('Beza Ind.', 'Sister', 'Hagbes HQ.', 'bezalogo.png', 0, 1, 1, 1, 1, 0),
('Burayu Store', 'Branch', 'Hagbes HQ.', 'Hagbeslogo.jpg', 0, 0, 0, 0, 0, 0),
('CN-Makris', 'Sister', 'Hagbes HQ.', 'Hagbeslogo.jpg', 0, 0, 0, 0, 0, 0),
('Debrezit Food Ind.', 'Sister', 'Hagbes HQ.', 'dz-logo.gif', 0, 0, 0, 0, 0, 0),
('Gofa', 'Branch', 'Hagbes HQ.', 'Hagbeslogo.jpg', 0, 0, 0, 0, 0, 0),
('Hagbes HQ.', 'Sister', 'Hagbes HQ.', 'Hagbeslogo.jpg', 1, 1, 1, 1, 1, 1),
('HWM Bole', 'Sister', 'Hagbes HQ.', 'hwmlogo.jpg', 0, 0, 0, 0, 0, 0),
('HWM Piaza', 'Sister', 'Hagbes HQ.', 'hwmlogo.jpg', 0, 0, 0, 0, 0, 0),
('KHMSC', 'Branch', 'Hagbes HQ.', 'Hagbeslogo.jpg', 0, 0, 0, 0, 0, 1),
('KLR Water', 'Sister', 'Hagbes HQ.', 'Hagbeslogo.jpg', 0, 0, 0, 0, 0, 0),
('Leons', 'Sister', 'Hagbes HQ.', 'leons.png', 0, 0, 0, 0, 0, 0),
('Leons Senga-tera', 'Sister', 'Hagbes HQ.', 'Hagbeslogo.jpg', 0, 0, 0, 0, 0, 0),
('Magdalinos', 'Sister', 'Hagbes HQ.', 'magdalinos.gif', 0, 0, 0, 0, 0, 0),
('Sevita', 'Sister', 'Hagbes HQ.', 'Hagbeslogo.jpg', 0, 0, 0, 0, 0, 0),
('Somali Tera', 'Branch', 'Hagbes HQ.', 'Hagbeslogo.jpg', 0, 0, 0, 0, 0, 0),
('Ultimate Motors', 'Sister', 'Hagbes HQ.', 'ultimate_logo.gif', 0, 0, 0, 0, 0, 1),
('Ultimate Piaza', 'Branch', 'Hagbes HQ.', 'ultimate_logo.gif', 0, 0, 0, 0, 0, 0),
('YAMAHA', 'Branch', 'Hagbes HQ.', 'yamahalogo.png', 0, 0, 0, 0, 0, 0),
('Yohannes', 'Branch', 'Hagbes HQ.', 'Hagbeslogo.jpg', 0, 0, 0, 0, 0, 0),
('Setavi', 'Sister', 'Hagbes HQ.', 'setavilogo.jpg', 0, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `crone_errors`
--

DROP TABLE IF EXISTS `crone_errors`;
CREATE TABLE IF NOT EXISTS `crone_errors` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email_id` int DEFAULT NULL,
  `data` varchar(400) NOT NULL,
  `date_registered` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12919 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `dbs_edits`
--

DROP TABLE IF EXISTS `dbs_edits`;
CREATE TABLE IF NOT EXISTS `dbs_edits` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user` varchar(300) NOT NULL,
  `dbs` varchar(400) NOT NULL,
  `tbl` varchar(300) NOT NULL,
  `pri-value` varchar(1000) NOT NULL,
  `att` varchar(300) NOT NULL,
  `value` varchar(600) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `department`
--

DROP TABLE IF EXISTS `department`;
CREATE TABLE IF NOT EXISTS `department` (
  `Name` varchar(100) NOT NULL,
  `date_inserted` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `department`
--

INSERT INTO `department` (`Name`, `date_inserted`) VALUES
('Admin', '2022-10-19 05:41:46'),
('After_Market', '2022-10-19 05:41:46'),
('Agricultre ', '2022-10-19 05:41:46'),
('ASCS', '2022-10-19 05:41:46'),
('Audit', '2022-10-19 05:41:46'),
('AutoEW', '2022-10-19 05:41:46'),
('Automotive', '2022-10-19 05:41:46'),
('BS', '2022-10-19 05:41:46'),
('BT', '2022-10-19 05:41:46'),
('CAD', '2022-10-19 05:41:46'),
('Compressor_Construction', '2022-10-19 05:41:46'),
('Compressor_Technic', '2022-10-19 05:41:46'),
('Construction', '2022-10-19 05:41:46'),
('CS', '2022-10-19 05:41:46'),
('Cummins', '2022-10-19 05:41:46'),
('Customer Service', '2022-10-19 05:41:46'),
('DAP', '2022-10-19 05:41:46'),
('Director', '2022-10-19 05:41:46'),
('Director of operation', '2023-05-19 07:53:52'),
('Disbursement', '2022-10-19 05:41:46'),
('EM', '2022-10-19 05:41:46'),
('EMP', '2022-10-19 05:41:46'),
('EMW', '2022-10-19 05:41:46'),
('EMWshop', '2022-10-19 05:41:46'),
('Epiroc', '2023-02-04 09:14:35'),
('Export', '2022-10-20 07:11:59'),
('Finance', '2022-10-19 05:41:46'),
('General Service', '2022-10-19 11:29:43'),
('GM', '2022-10-19 05:41:46'),
('GPS', '2022-10-19 05:41:46'),
('HMAESM', '2022-10-19 05:41:46'),
('HR', '2022-10-19 05:41:46'),
('Import', '2022-10-19 05:41:46'),
('IT', '2022-10-19 05:41:46'),
('Legal', '2022-10-19 05:41:46'),
('Logistics', '2022-12-19 10:21:35'),
('Manager', '2022-10-19 05:41:46'),
('Marketing and Sales', '2022-10-19 11:27:41'),
('Metal Workshop', '2022-10-19 05:41:46'),
('Operation Manager', '2022-10-19 05:41:46'),
('Owner', '2022-10-19 05:41:46'),
('Plant', '2022-10-19 11:53:29'),
('Procurement', '2022-10-19 05:41:46'),
('Procurement (Local)', '2022-10-19 05:41:46'),
('Procurement and Adminstration', '2022-12-23 12:37:34'),
('Production and Technic', '2022-10-19 05:41:46'),
('Property', '2022-10-19 05:41:46'),
('Quality', '2022-10-19 11:57:31'),
('Rock_Drilling_Tools', '2022-10-19 05:41:46'),
('Sales', '2022-10-19 05:41:46'),
('SC', '2022-10-19 05:41:46'),
('Secretary', '2022-10-19 05:41:46'),
('Sokkia', '2022-10-19 05:41:46'),
('SP', '2022-10-19 05:41:46'),
('Spare Part And Warranty', '2022-12-15 08:08:25'),
('Store', '2022-10-19 05:41:46'),
('Surface _Waterwell_Drilling', '2022-10-19 05:41:46'),
('Technic', '2022-10-19 05:41:46'),
('Tender', '2022-10-19 05:41:46'),
('Training', '2022-10-19 05:41:46'),
('Transit', '2022-10-19 05:41:46'),
('Transport', '2022-10-19 05:41:46'),
('Workshop', '2022-10-19 05:41:46');

-- --------------------------------------------------------

--
-- Table structure for table `documentations`
--

DROP TABLE IF EXISTS `documentations`;
CREATE TABLE IF NOT EXISTS `documentations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `file` varchar(250) NOT NULL,
  `uploaded_by` varchar(200) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(15) NOT NULL DEFAULT 'open',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `emails`
--

DROP TABLE IF EXISTS `emails`;
CREATE TABLE IF NOT EXISTS `emails` (
  `id` int NOT NULL AUTO_INCREMENT,
  `project` varchar(1000) NOT NULL,
  `send_to` varchar(2000) NOT NULL,
  `cc` varchar(2000) DEFAULT NULL,
  `bcc` varchar(2000) DEFAULT NULL,
  `subject` varchar(2000) NOT NULL,
  `data` varchar(20000) NOT NULL,
  `tag` varchar(400) DEFAULT NULL,
  `company_logo` varchar(255) NOT NULL,
  `status` varchar(100) NOT NULL DEFAULT 'waiting',
  `reason` varchar(500) DEFAULT 'closed',
  `to_page` varchar(100) DEFAULT NULL,
  `copy_of` varchar(100) DEFAULT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `sent_from` varchar(200) DEFAULT NULL,
  `email_type` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=135498 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `emails`
--

INSERT INTO `emails` (`id`, `project`, `send_to`, `cc`, `bcc`, `subject`, `data`, `tag`, `company_logo`, `status`, `reason`, `to_page`, `copy_of`, `time`, `sent_from`, `email_type`) VALUES
(135495, 'LPMS', 'dagem.adugna@hagbes.com,Eyob.Assefa', '', '', 'Purchase orders have passed committee aprroval', '\r\n                        <strong>Purchase orders have passed committee aprroval please review and sent to finance for further processing</strong><br><br><br>\r\n                        ', 'Eyob.Assefa', 'Setavi,setavilogo.jpg', 'waiting', 'open_clust_921_committee_approved', 'Procurement/manager/additional_information.php', NULL, '2023-07-21 07:19:29', 'Israel Taddesse:-:(committee members)', NULL),
(135496, 'LPMS', 'dagem.adugna@hagbes.com,Gashaw.Kebede', '', '', 'Purchase orders have passed committee aprroval', '\r\n                        <strong>Purchase orders have passed committee aprroval please review and sent to finance for further processing</strong><br><br><br>\r\n                        ', 'Gashaw.Kebede', 'Setavi,setavilogo.jpg', 'waiting', 'open_clust_921_committee_approved', 'Procurement/manager/additional_information.php', NULL, '2023-07-21 07:19:29', 'Israel Taddesse:-:(committee members)', NULL),
(135497, 'LPMS', 'dagem.adugna@hagbes.com,Israel.Taddesse', '', '', 'Purchase orders have passed committee aprroval', '\r\n                        <strong>Purchase orders have passed committee aprroval please review and sent to finance for further processing</strong><br><br><br>\r\n                        ', 'Israel.Taddesse', 'Setavi,setavilogo.jpg', 'waiting', 'open_clust_921_committee_approved', 'Procurement/manager/additional_information.php', NULL, '2023-07-21 07:19:29', 'Israel Taddesse:-:(committee members)', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

DROP TABLE IF EXISTS `feedback`;
CREATE TABLE IF NOT EXISTS `feedback` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user` varchar(100) NOT NULL,
  `rating` float DEFAULT NULL,
  `feedback` varchar(400) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fleet_requests`
--

DROP TABLE IF EXISTS `fleet_requests`;
CREATE TABLE IF NOT EXISTS `fleet_requests` (
  `id` varchar(500) NOT NULL,
  `request_ids` varchar(200) NOT NULL,
  `requested_by` varchar(200) NOT NULL,
  `no_of_travelers` int NOT NULL,
  `travelers` varchar(400) NOT NULL,
  `date_departure` timestamp NOT NULL,
  `estimate_date_return` timestamp NOT NULL,
  `purpose` varchar(400) NOT NULL,
  `destination` varchar(400) NOT NULL,
  `date_recorded` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `forgot_password_links`
--

DROP TABLE IF EXISTS `forgot_password_links`;
CREATE TABLE IF NOT EXISTS `forgot_password_links` (
  `Id` int NOT NULL AUTO_INCREMENT,
  `Requested_by` varchar(100) NOT NULL,
  `Sent_to` varchar(200) NOT NULL,
  `Link` varchar(1000) NOT NULL,
  `date` varchar(700) NOT NULL,
  `status` varchar(100) DEFAULT 'new',
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `history_jacket`
--

DROP TABLE IF EXISTS `history_jacket`;
CREATE TABLE IF NOT EXISTS `history_jacket` (
  `id` int NOT NULL AUTO_INCREMENT,
  `vehicle` varchar(500) NOT NULL,
  `item` varchar(500) NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `serial` varchar(500) NOT NULL,
  `date_purchased` timestamp NOT NULL,
  `kilometer` bigint NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `km_diff` int NOT NULL,
  `time_diff` varchar(50) NOT NULL,
  `date_inserted` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `inserted_by` varchar(500) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `issues`
--

DROP TABLE IF EXISTS `issues`;
CREATE TABLE IF NOT EXISTS `issues` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title_id` varchar(50) NOT NULL,
  `user` varchar(100) NOT NULL,
  `type` varchar(50) NOT NULL,
  `issue` varchar(400) NOT NULL,
  `company` varchar(100) NOT NULL,
  `status` varchar(10) NOT NULL,
  `thread` int DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `closed_by` varchar(150) DEFAULT NULL,
  `close_timestamp` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `limit_ho`
--

DROP TABLE IF EXISTS `limit_ho`;
CREATE TABLE IF NOT EXISTS `limit_ho` (
  `id` int NOT NULL AUTO_INCREMENT,
  `company` varchar(100) NOT NULL,
  `amount_limit` int NOT NULL,
  `amount_limit_top` int NOT NULL,
  `Vat` double NOT NULL,
  `minimum_approval` double NOT NULL,
  `petty_cash` int NOT NULL DEFAULT '2500',
  `perdiem_pettycash` double DEFAULT '1000',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `limit_ho`
--

INSERT INTO `limit_ho` (`id`, `company`, `amount_limit`, `amount_limit_top`, `Vat`, `minimum_approval`, `petty_cash`, `perdiem_pettycash`, `date`) VALUES
(1, 'Others', 30000, 60000, 0.15, 75, 2500, 1000, '2022-10-29 09:10:33'),
(2, 'Abakoran', 30000, 60000, 0.15, 75, 1500, 1000, '2023-06-27 10:55:55'),
(3, 'Ultimate Motors', 30000, 60000, 0.15, 75, 1500, 1000, '2023-06-27 10:56:25'),
(4, 'KHMSC', 30000, 60000, 0.15, 75, 1500, 1000, '2023-06-27 10:57:08');

-- --------------------------------------------------------

--
-- Table structure for table `log`
--

DROP TABLE IF EXISTS `log`;
CREATE TABLE IF NOT EXISTS `log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `operation` varchar(100) NOT NULL,
  `prev_id` int DEFAULT NULL,
  `user` varchar(100) NOT NULL,
  `ipaddress` varchar(50) DEFAULT NULL,
  `time` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
CREATE TABLE IF NOT EXISTS `messages` (
  `msg_id` int NOT NULL AUTO_INCREMENT,
  `incoming_msg_id` int NOT NULL,
  `outgoing_msg_id` int NOT NULL,
  `msg` text NOT NULL,
  `message_status` int NOT NULL DEFAULT '0',
  `req_id` int NOT NULL DEFAULT '0',
  `group_id` varchar(23) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`msg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `performa`
--

DROP TABLE IF EXISTS `performa`;
CREATE TABLE IF NOT EXISTS `performa` (
  `id` int NOT NULL AUTO_INCREMENT,
  `files` varchar(2000) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=713 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `prefered_vendors`
--

DROP TABLE IF EXISTS `prefered_vendors`;
CREATE TABLE IF NOT EXISTS `prefered_vendors` (
  `id` int NOT NULL AUTO_INCREMENT,
  `catagory` varchar(200) NOT NULL,
  `vendor` varchar(100) NOT NULL,
  `business_type` varchar(255) DEFAULT NULL,
  `contact` varchar(100) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `address` varchar(500) NOT NULL,
  `items` varchar(1000) NOT NULL,
  `details` varchar(1000) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(100) NOT NULL,
  `rank` int NOT NULL,
  `status` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `price_information`
--

DROP TABLE IF EXISTS `price_information`;
CREATE TABLE IF NOT EXISTS `price_information` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cluster_id` int NOT NULL,
  `purchase_order_id` int NOT NULL,
  `providing_company` varchar(100) NOT NULL,
  `quantity` double NOT NULL,
  `price` double NOT NULL,
  `vat` float NOT NULL DEFAULT '0.15',
  `total_price` double NOT NULL,
  `after_vat` double DEFAULT NULL,
  `specification` varchar(1000) DEFAULT NULL,
  `selected` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `cluster_id` (`cluster_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `price_information_deleted`
--

DROP TABLE IF EXISTS `price_information_deleted`;
CREATE TABLE IF NOT EXISTS `price_information_deleted` (
  `id` int NOT NULL,
  `cluster_id` int NOT NULL,
  `purchase_order_id` int NOT NULL,
  `providing_company` varchar(100) NOT NULL,
  `quantity` double NOT NULL,
  `price` double NOT NULL,
  `total_price` double NOT NULL,
  `after_vat` double DEFAULT NULL,
  `specification` varchar(1000) DEFAULT NULL,
  `selected` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `privilege`
--

DROP TABLE IF EXISTS `privilege`;
CREATE TABLE IF NOT EXISTS `privilege` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` varchar(200) NOT NULL,
  `company` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `privilege`
--

INSERT INTO `privilege` (`id`, `type`, `company`) VALUES
(1, 'Spare and Lubricant', 'All'),
(2, 'Consumer Goods', 'All'),
(3, 'Stationary and Toiletaries', 'All'),
(4, 'Tyre and Battery', 'All'),
(5, 'Fixed Assets', 'All'),
(6, 'Miscellaneous', 'All');

-- --------------------------------------------------------

--
-- Table structure for table `project`
--

DROP TABLE IF EXISTS `project`;
CREATE TABLE IF NOT EXISTS `project` (
  `Name` varchar(100) NOT NULL,
  `project_id` int NOT NULL AUTO_INCREMENT,
  `status` varchar(100) NOT NULL DEFAULT 'open',
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `project`
--

INSERT INTO `project` (`Name`, `project_id`, `status`, `date_created`) VALUES
('General', 0, 'open', '2022-10-04 08:11:13');

-- --------------------------------------------------------

--
-- Table structure for table `purchase history`
--

DROP TABLE IF EXISTS `purchase history`;
CREATE TABLE IF NOT EXISTS `purchase history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `request_id` int NOT NULL,
  `type` varchar(100) NOT NULL,
  `request_for` varchar(300) DEFAULT NULL,
  `item` varchar(255) NOT NULL,
  `purchased` tinyint(1) NOT NULL DEFAULT '1',
  `amount` int NOT NULL,
  `customer` varchar(100) NOT NULL,
  `department` varchar(100) NOT NULL,
  `Serial` varchar(255) NOT NULL,
  `data` varchar(2000) DEFAULT NULL,
  `date` timestamp NOT NULL,
  `kilometer` varchar(100) DEFAULT NULL,
  `company` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order`
--

DROP TABLE IF EXISTS `purchase_order`;
CREATE TABLE IF NOT EXISTS `purchase_order` (
  `purchase_order_id` int NOT NULL AUTO_INCREMENT,
  `request_type` varchar(100) NOT NULL,
  `request_id` int NOT NULL,
  `scale` varchar(30) NOT NULL,
  `purchase_officer` varchar(100) NOT NULL,
  `collector` varchar(100) DEFAULT NULL,
  `assigned_by` varchar(100) NOT NULL,
  `finance_sent_by` varchar(100) DEFAULT NULL,
  `performa_opened` varchar(255) DEFAULT NULL,
  `status` varchar(100) NOT NULL DEFAULT 'pending',
  `settlement` varchar(100) DEFAULT NULL,
  `cluster_id` int DEFAULT NULL,
  `company` varchar(100) NOT NULL,
  `processing_company` varchar(100) NOT NULL,
  `property_company` varchar(200) NOT NULL,
  `procurement_company` varchar(200) NOT NULL,
  `finance_company` varchar(200) NOT NULL,
  `payment_provider` varchar(500) DEFAULT NULL,
  `timestamp` timestamp NOT NULL,
  `performa_id` int DEFAULT NULL,
  `priority` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`purchase_order_id`) USING BTREE,
  KEY `purchase_officer` (`purchase_officer`),
  KEY `assigned_by` (`assigned_by`),
  KEY `cluster_id` (`cluster_id`),
  KEY `collector` (`collector`),
  KEY `performa_id` (`performa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_recollection_failed`
--

DROP TABLE IF EXISTS `purchase_order_recollection_failed`;
CREATE TABLE IF NOT EXISTS `purchase_order_recollection_failed` (
  `id` int NOT NULL AUTO_INCREMENT,
  `purchase_order_id` int NOT NULL,
  `request_type` varchar(100) NOT NULL,
  `request_id` int NOT NULL,
  `scale` varchar(30) NOT NULL,
  `purchase_officer` varchar(100) NOT NULL,
  `collector` varchar(100) DEFAULT NULL,
  `assigned_by` varchar(100) NOT NULL,
  `performa_opened` varchar(255) DEFAULT NULL,
  `status` varchar(100) NOT NULL DEFAULT 'pending',
  `settlement` varchar(100) DEFAULT NULL,
  `cluster_id` int DEFAULT NULL,
  `company` varchar(100) NOT NULL,
  `processing_company` varchar(100) NOT NULL,
  `property_company` varchar(200) NOT NULL,
  `procurement_company` varchar(200) NOT NULL,
  `finance_company` varchar(200) NOT NULL,
  `timestamp` timestamp NOT NULL,
  `performa_id` int DEFAULT NULL,
  `priority` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `purchase_officer` (`purchase_officer`),
  KEY `assigned_by` (`assigned_by`),
  KEY `cluster_id` (`cluster_id`),
  KEY `collector` (`collector`),
  KEY `performa_id` (`performa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_unrecived`
--

DROP TABLE IF EXISTS `purchase_order_unrecived`;
CREATE TABLE IF NOT EXISTS `purchase_order_unrecived` (
  `id` int NOT NULL AUTO_INCREMENT,
  `purchase_order_id` int NOT NULL,
  `request_type` varchar(100) NOT NULL,
  `request_id` int NOT NULL,
  `scale` varchar(30) NOT NULL,
  `purchase_officer` varchar(100) NOT NULL,
  `collector` varchar(100) DEFAULT NULL,
  `assigned_by` varchar(100) NOT NULL,
  `performa_opened` varchar(255) DEFAULT NULL,
  `status` varchar(100) NOT NULL DEFAULT 'pending',
  `settlement` varchar(100) DEFAULT NULL,
  `cluster_id` int DEFAULT NULL,
  `company` varchar(100) NOT NULL,
  `processing_company` varchar(100) NOT NULL,
  `property_company` varchar(200) NOT NULL,
  `procurement_company` varchar(200) NOT NULL,
  `finance_company` varchar(200) NOT NULL,
  `timestamp` timestamp NOT NULL,
  `performa_id` int DEFAULT NULL,
  `priority` double NOT NULL DEFAULT '0',
  `date_reset` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `purchase_officer` (`purchase_officer`),
  KEY `assigned_by` (`assigned_by`),
  KEY `cluster_id` (`cluster_id`),
  KEY `collector` (`collector`),
  KEY `performa_id` (`performa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `recorded_time`
--

DROP TABLE IF EXISTS `recorded_time`;
CREATE TABLE IF NOT EXISTS `recorded_time` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user` varchar(300) NOT NULL,
  `database_name` varchar(500) NOT NULL,
  `for_id` int NOT NULL,
  `opperation` varchar(500) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `remarks`
--

DROP TABLE IF EXISTS `remarks`;
CREATE TABLE IF NOT EXISTS `remarks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `request_id` int NOT NULL,
  `remark` varchar(2000) NOT NULL,
  `user` varchar(255) NOT NULL,
  `level` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `replacements_collected`
--

DROP TABLE IF EXISTS `replacements_collected`;
CREATE TABLE IF NOT EXISTS `replacements_collected` (
  `id` int NOT NULL AUTO_INCREMENT,
  `request_id` int NOT NULL,
  `status` varchar(300) NOT NULL,
  `remarks` varchar(500) NOT NULL,
  `collected_by` varchar(300) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `replacements_collected`
--

INSERT INTO `replacements_collected` (`id`, `request_id`, `status`, `remarks`, `collected_by`, `date`) VALUES
(1, 27, 'Collected', 'GRN number 0718', 'Zewdu.Tesfaye', '2023-01-16 06:54:40');

-- --------------------------------------------------------

--
-- Table structure for table `report`
--

DROP TABLE IF EXISTS `report`;
CREATE TABLE IF NOT EXISTS `report` (
  `id` int NOT NULL AUTO_INCREMENT,
  `request_id` int NOT NULL,
  `type` varchar(100) NOT NULL,
  `request_date` timestamp NOT NULL,
  `sent_for_spec` timestamp NULL DEFAULT NULL,
  `spec_recieved` timestamp NULL DEFAULT NULL,
  `manager_approval_date` timestamp NULL DEFAULT NULL,
  `Director_approval_date` timestamp NULL DEFAULT NULL,
  `Owner_approval_date` timestamp NULL DEFAULT NULL,
  `property_approval_date` timestamp NULL DEFAULT NULL,
  `stock_check_date` timestamp NULL DEFAULT NULL,
  `officer_assigned_date` timestamp NULL DEFAULT NULL,
  `collector_assigned_date` timestamp NULL DEFAULT NULL,
  `officer_acceptance_date` timestamp NULL DEFAULT NULL,
  `performa_generated_date` timestamp NULL DEFAULT NULL,
  `performa_confirm_date` timestamp NULL DEFAULT NULL,
  `compsheet_generated_date` timestamp NULL DEFAULT NULL,
  `sent_to_committee_date` timestamp NULL DEFAULT NULL,
  `committee_approval_date` timestamp NULL DEFAULT NULL,
  `sent_to_finance_date` timestamp NULL DEFAULT NULL,
  `Disbursement_review_date` timestamp NULL DEFAULT NULL,
  `finance_approval_date` timestamp NULL DEFAULT NULL,
  `cheque_prepared_date` timestamp NULL DEFAULT NULL,
  `cheque_signed_date` timestamp NULL DEFAULT NULL,
  `collection_date` timestamp NULL DEFAULT NULL,
  `dep_check_date` timestamp NULL DEFAULT NULL,
  `recollection_date` timestamp NULL DEFAULT NULL,
  `handover_comfirmed` timestamp NULL DEFAULT NULL,
  `final_instock_recieved_date` timestamp NULL DEFAULT NULL,
  `settlement_date` timestamp NULL DEFAULT NULL,
  `final_recieved_date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

DROP TABLE IF EXISTS `requests`;
CREATE TABLE IF NOT EXISTS `requests` (
  `request_id` int NOT NULL AUTO_INCREMENT,
  `purchase_requisition` int DEFAULT NULL,
  `request_for` varchar(100) DEFAULT NULL,
  `request_type` varchar(100) NOT NULL,
  `customer` varchar(100) NOT NULL,
  `item` varchar(100) NOT NULL,
  `requested_quantity` float NOT NULL,
  `unit` varchar(30) NOT NULL DEFAULT 'pcs',
  `date_requested` timestamp NOT NULL,
  `date_needed_by` date NOT NULL,
  `type` varchar(100) NOT NULL DEFAULT 'new',
  `to_replace` varchar(100) DEFAULT NULL,
  `Remark` varchar(500) NOT NULL DEFAULT '#',
  `description` varchar(1000) NOT NULL,
  `manager_description` varchar(1000) DEFAULT NULL,
  `specification` int DEFAULT NULL,
  `spec_dep` varchar(100) DEFAULT NULL,
  `department` varchar(1000) NOT NULL,
  `stock_info` int DEFAULT NULL,
  `current_km` int DEFAULT NULL,
  `prev_req` int DEFAULT NULL,
  `status` varchar(100) NOT NULL DEFAULT 'waiting',
  `manager` varchar(100) DEFAULT NULL,
  `director` varchar(100) DEFAULT NULL,
  `owner` varchar(255) DEFAULT NULL,
  `property` varchar(100) DEFAULT NULL,
  `recieved` varchar(100) NOT NULL DEFAULT 'not',
  `company` varchar(100) NOT NULL,
  `processing_company` varchar(100) NOT NULL,
  `property_company` varchar(200) NOT NULL,
  `procurement_company` varchar(200) NOT NULL,
  `finance_company` varchar(200) NOT NULL,
  `next_step` varchar(100) NOT NULL DEFAULT 'Manager',
  `reason_instock` varchar(1000) DEFAULT NULL,
  `reason_purchased` varchar(1000) DEFAULT NULL,
  `purchased_amount` int DEFAULT NULL,
  `additional` varchar(1000) DEFAULT NULL,
  `mode` varchar(100) DEFAULT NULL,
  `replaced_items` int DEFAULT NULL,
  `flag` int NOT NULL DEFAULT '0',
  `vendor` varchar(24) DEFAULT NULL,
  PRIMARY KEY (`request_id`),
  KEY `customer` (`customer`),
  KEY `specification` (`specification`),
  KEY `stock_info` (`stock_info`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `requests_nolonger`
--

DROP TABLE IF EXISTS `requests_nolonger`;
CREATE TABLE IF NOT EXISTS `requests_nolonger` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` varchar(100) NOT NULL,
  `request_id` int NOT NULL,
  `requested_for` varchar(100) NOT NULL,
  `requested_by` varchar(100) NOT NULL,
  `item` varchar(100) NOT NULL,
  `date_requested` timestamp NOT NULL,
  `date_needed_by` date NOT NULL,
  `quantity` varchar(100) NOT NULL,
  `company` varchar(100) NOT NULL,
  `Remark` varchar(500) NOT NULL DEFAULT '#',
  `changed_by` varchar(100) NOT NULL,
  `date` timestamp NOT NULL,
  `operation` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `requested_by` (`requested_by`,`changed_by`),
  KEY `changed_by` (`changed_by`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `request_stages`
--

DROP TABLE IF EXISTS `request_stages`;
CREATE TABLE IF NOT EXISTS `request_stages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `stage` varchar(400) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `request_stages`
--

INSERT INTO `request_stages` (`id`, `stage`) VALUES
(1, 'Performa collection'),
(2, 'Comparsion Sheet Creation'),
(3, 'Committee Approval'),
(4, 'Collection');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`name`) VALUES
('Admin'),
('Cashier'),
('Director'),
('Disbursement'),
('manager'),
('Owner'),
('Purchase officer'),
('Senior Purchase officer'),
('Store'),
('user');

-- --------------------------------------------------------

--
-- Table structure for table `selections`
--

DROP TABLE IF EXISTS `selections`;
CREATE TABLE IF NOT EXISTS `selections` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user` varchar(200) NOT NULL,
  `cluster_id` int NOT NULL,
  `selection` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`,`cluster_id`),
  KEY `cluster_id` (`cluster_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `specification`
--

DROP TABLE IF EXISTS `specification`;
CREATE TABLE IF NOT EXISTS `specification` (
  `id` int NOT NULL AUTO_INCREMENT,
  `request_id` int NOT NULL,
  `type` varchar(255) NOT NULL,
  `details` varchar(30000) NOT NULL,
  `pictures` varchar(2000) NOT NULL,
  `date` timestamp NOT NULL,
  `given_by` varchar(1000) NOT NULL,
  `department` varchar(1000) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `stock`
--

DROP TABLE IF EXISTS `stock`;
CREATE TABLE IF NOT EXISTS `stock` (
  `id` int NOT NULL AUTO_INCREMENT,
  `request_id` int NOT NULL,
  `type` varchar(100) NOT NULL,
  `check_by` varchar(200) NOT NULL,
  `requested_quantity` float NOT NULL,
  `in-stock` float NOT NULL,
  `for_purchase` float NOT NULL,
  `average_price` bigint NOT NULL,
  `total_price` bigint NOT NULL,
  `status` varchar(100) NOT NULL DEFAULT 'not approved',
  `remark` varchar(20000) DEFAULT NULL,
  `flag` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `store`
--

DROP TABLE IF EXISTS `store`;
CREATE TABLE IF NOT EXISTS `store` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_partno` varchar(200) NOT NULL,
  `product_descr` varchar(200) NOT NULL,
  `stock_level` varchar(200) NOT NULL,
  `minimum_stock_level` bigint DEFAULT NULL,
  `warehouse` varchar(200) NOT NULL,
  `lastupdate_onerp` varchar(200) NOT NULL,
  `lastsynchdate` varchar(200) NOT NULL,
  `byy` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=92075 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tax`
--

DROP TABLE IF EXISTS `tax`;
CREATE TABLE IF NOT EXISTS `tax` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tax_name` varchar(33) NOT NULL,
  `value` double NOT NULL,
  `dateCreated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `createdBy` varchar(34) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `createdBy` (`createdBy`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
COMMIT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `committee_approval`
--
ALTER TABLE `committee_approval`
  ADD CONSTRAINT `committee_approval_ibfk_1` FOREIGN KEY (`committee_member`) REFERENCES `account` (`Username`),
  ADD CONSTRAINT `committee_approval_ibfk_2` FOREIGN KEY (`cluster_id`) REFERENCES `cluster` (`id`);

--
-- Constraints for table `price_information`
--
ALTER TABLE `price_information`
  ADD CONSTRAINT `price_information_ibfk_2` FOREIGN KEY (`cluster_id`) REFERENCES `cluster` (`id`);

--
-- Constraints for table `purchase_order`
--
ALTER TABLE `purchase_order`
  ADD CONSTRAINT `purchase_order_ibfk_1` FOREIGN KEY (`purchase_officer`) REFERENCES `account` (`Username`),
  ADD CONSTRAINT `purchase_order_ibfk_2` FOREIGN KEY (`assigned_by`) REFERENCES `account` (`Username`),
  ADD CONSTRAINT `purchase_order_ibfk_3` FOREIGN KEY (`cluster_id`) REFERENCES `cluster` (`id`),
  ADD CONSTRAINT `purchase_order_ibfk_4` FOREIGN KEY (`collector`) REFERENCES `account` (`Username`),
  ADD CONSTRAINT `purchase_order_ibfk_5` FOREIGN KEY (`performa_id`) REFERENCES `performa` (`id`);

--
-- Constraints for table `requests_nolonger`
--
ALTER TABLE `requests_nolonger`
  ADD CONSTRAINT `requests_nolonger_ibfk_1` FOREIGN KEY (`changed_by`) REFERENCES `account` (`Username`),
  ADD CONSTRAINT `requests_nolonger_ibfk_2` FOREIGN KEY (`requested_by`) REFERENCES `account` (`Username`);

--
-- Constraints for table `selections`
--
ALTER TABLE `selections`
  ADD CONSTRAINT `selections_ibfk_1` FOREIGN KEY (`cluster_id`) REFERENCES `cluster` (`id`),
  ADD CONSTRAINT `selections_ibfk_2` FOREIGN KEY (`user`) REFERENCES `account` (`Username`);
COMMIT;

--
-- Updates by Dagem
--

ALTER TABLE `requests` ADD `directors` VARCHAR(40) NULL AFTER `vendor`;
ALTER TABLE `report` ADD `Directors_approval_date` TIMESTAMP NULL DEFAULT NULL AFTER `Director_approval_date`;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
