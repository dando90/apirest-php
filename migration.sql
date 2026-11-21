-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Created: May 4, 2022 at 00:16
-- Server version: 10.4.22-MariaDB
-- PHP version: 8.1.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `apirest_dtb`
--

-- --------------------------------------------------------

--
-- Structure of table `apirest_courses`
--

CREATE TABLE `apirest_courses` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(30) NOT NULL,
  `places` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Seed data for table `apirest_courses`
--

INSERT INTO `apirest_courses` (`id`, `name`, `places`) VALUES
(1, 'First course', 30),
(2, 'Second course', 10);

-- --------------------------------------------------------

--
-- Structure of table `apirest_course_subject`
--

CREATE TABLE `apirest_course_subject` (
  `course_id` int(10) UNSIGNED NOT NULL,
  `subject_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Seed data for table `apirest_course_subject`
--

INSERT INTO `apirest_course_subject` (`course_id`, `subject_id`) VALUES
(1, 1),
(1, 2),
(2, 1),
(2, 3);

-- --------------------------------------------------------

--
-- Structure of table `apirest_subjects`
--

CREATE TABLE `apirest_subjects` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Seed data for table `apirest_subjects`
--

INSERT INTO `apirest_subjects` (`id`, `name`) VALUES
(1, 'Mathematics'),
(2, 'Italian'),
(3, 'Philosophy');

--
-- Indexes
--

--
-- Indexes for `apirest_courses`
--
ALTER TABLE `apirest_courses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for `apirest_course_subject`
--
ALTER TABLE `apirest_course_subject`
  ADD PRIMARY KEY (`course_id`,`subject_id`),
  ADD KEY `apirest_subject_id` (`subject_id`);

--
-- Indexes for `apirest_subjects`
--
ALTER TABLE `apirest_subjects`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT values
--

--
-- AUTO_INCREMENT for `apirest_courses`
--
ALTER TABLE `apirest_courses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for `apirest_subjects`
--
ALTER TABLE `apirest_subjects`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints
--

--
-- Foreign keys for `apirest_course_subject`
--
ALTER TABLE `apirest_course_subject`
  ADD CONSTRAINT `FK_apirest_course` FOREIGN KEY (`course_id`) REFERENCES `apirest_courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_apirest_subject` FOREIGN KEY (`subject_id`) REFERENCES `apirest_subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
