-- phpMyAdmin SQL Dump
-- version 4.7.9
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: 2018-04-08 16:25:43
-- 服务器版本： 10.2.13-MariaDB
-- PHP Version: 7.2.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `video_server`
--

-- --------------------------------------------------------

--
-- 表的结构 `screenshot`
--

CREATE TABLE IF NOT EXISTS `screenshot` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `video_id` int(11) NOT NULL,
  `type` int(11) NOT NULL COMMENT '1=JPEG 2=GIF',
  `files` text NOT NULL COMMENT 'file JSON',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `setting`
--

CREATE TABLE IF NOT EXISTS `setting` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `data` text NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `setting`
--

INSERT INTO `setting` (`ID`, `name`, `data`) VALUES
(2, 'encode_bitrate_video', '10000'),
(3, 'encode_bitrate_audio', '320'),
(4, 'encode_ts_time', '2'),
(5, 'encode_ts_frame', '60'),
(6, 'worker_thread', '1'),
(7, 'api_key', ''),
(8, 'video_port', '12345'),
(9, 'play_secure', '0'),
(10, 'allow_domain', 'localhost'),
(11, 'jump_link', ''),
(12, 'nginx_worker', '4'),
(13, 'video_domain', '127.0.0.1'),
(14, 'sc_jpeg', '1'),
(15, 'sc_gif', '1'),
(16, 'sc_jpeg_start_time', '00:01'),
(17, 'sc_jpeg_number', '5'),
(18, 'sc_jpeg_res', '1280x720'),
(19, 'sc_gif_start_time', '00:15'),
(20, 'sc_gif_time', '5'),
(21, 'sc_gif_res', '1280x720'),
(22, 'sc_jpeg_int', '5'),
(23, 'sc_gif_framerate', '5'),
(24, 'encode_framerate', '30'),
(25, 'encode_res', '1280x720');

-- --------------------------------------------------------

--
-- 表的结构 `video_list`
--

CREATE TABLE IF NOT EXISTS `video_list` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `filename` text NOT NULL,
  `random` text NOT NULL,
  `day` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `md5` text NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
