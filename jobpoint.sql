-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 22, 2024 at 07:14 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `jobpoint`
--

-- --------------------------------------------------------

--
-- Table structure for table `address`
--

CREATE TABLE `address` (
  `address_id` int(11) NOT NULL,
  `building` varchar(255) NOT NULL,
  `street` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `country` varchar(100) NOT NULL,
  `pincode` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `address`
--

INSERT INTO `address` (`address_id`, `building`, `street`, `city`, `state`, `country`, `pincode`) VALUES
(569, 'abc', '32', 'baroda', 'gujarat', 'india', '391760'),
(570, 'abc', '32', 'baroda', 'gujarat', 'india', '391760'),
(571, 'ntr', 'ntrn', 'nt', 'nt', 'nrt', '123456');

-- --------------------------------------------------------

--
-- Table structure for table `employers_individual`
--

CREATE TABLE `employers_individual` (
  `employer_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `address_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employers_individual`
--

INSERT INTO `employers_individual` (`employer_id`, `user_id`, `first_name`, `middle_name`, `last_name`, `address_id`) VALUES
(4, 18, 'a', 'b', 'c', 569),
(5, 20, 'ntr', 'nrt', 'ntr', 571);

-- --------------------------------------------------------

--
-- Table structure for table `employers_organization`
--

CREATE TABLE `employers_organization` (
  `organization_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `registration_number` varchar(100) NOT NULL,
  `address_id` int(11) NOT NULL,
  `banner_photo` varchar(255) NOT NULL,
  `recruiter_name` varchar(255) NOT NULL,
  `company_contact_no` varchar(15) NOT NULL,
  `company_website` varchar(255) NOT NULL,
  `company_description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employers_organization`
--

INSERT INTO `employers_organization` (`organization_id`, `user_id`, `company_name`, `registration_number`, `address_id`, `banner_photo`, `recruiter_name`, `company_contact_no`, `company_website`, `company_description`) VALUES
(2, 19, 'a', '132133231', 570, '', 'aac', '9012345678', '', 'this is out company');

-- --------------------------------------------------------

--
-- Table structure for table `job_applications`
--

CREATE TABLE `job_applications` (
  `application_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `seeker_id` int(11) NOT NULL,
  `status` enum('Pending','Approved','Rejected') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_posts`
--

CREATE TABLE `job_posts` (
  `job_id` int(11) NOT NULL,
  `job_title` varchar(255) NOT NULL,
  `job_type` enum('Full-Time','Part-Time','Internship','Freelance','Contract','Temporary') NOT NULL,
  `job_description` text NOT NULL,
  `required_qualification` text NOT NULL,
  `skills_required` text NOT NULL,
  `application_deadline` date NOT NULL,
  `vacancy` int(11) NOT NULL,
  `post_date` date NOT NULL,
  `salary` decimal(10,2) NOT NULL,
  `employer_id` int(11) NOT NULL,
  `address_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_seekers`
--

CREATE TABLE `job_seekers` (
  `seeker_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `service_type` varchar(100) NOT NULL,
  `education` text NOT NULL,
  `date_of_birth` date NOT NULL,
  `experience` text NOT NULL,
  `rating` decimal(3,2) NOT NULL,
  `job_status` enum('Active','Not Active') NOT NULL DEFAULT 'Not Active',
  `bio` text DEFAULT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `resume` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_seekers`
--

INSERT INTO `job_seekers` (`seeker_id`, `user_id`, `first_name`, `middle_name`, `last_name`, `service_type`, `education`, `date_of_birth`, `experience`, `rating`, `job_status`, `bio`, `gender`, `resume`) VALUES
(5, 6, 'Gunjan', 'Jivanbhai', 'Chavda', 'Designing', '12th Pass', '2000-11-29', '0-1', 0.00, 'Not Active', '', 'Female', NULL),
(6, 7, 'A', 'B', 'C', 'Other', 'Below 10th', '2006-12-22', '0-1', 0.00, 'Not Active', '', 'Male', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `user_type` enum('Job Seeker','Employer Individual','Employer Organization') NOT NULL,
  `profile_photo` varchar(255) NOT NULL DEFAULT 'default profile photo.png',
  `email` varchar(255) NOT NULL,
  `contact_no` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `user_type`, `profile_photo`, `email`, `contact_no`, `password`) VALUES
(6, 'Job Seeker', 'default profile photo.png', 'gorfadvirendra1@gmail.com', '9537587291', '$2y$10$7pXD77in1AlV9C4/8TxPGulxLIk0xcXuiNG3plz2I60.fujrU7jr.'),
(7, 'Job Seeker', 'default profile photo.png', 'abc@blackmail.com', '1234567890', '$2y$10$7S.TWJiGFxc1Ct5ALpZr.Ob79KAXe3.yiqiU3XMj4D.Yy1JhX6Iu6'),
(18, '', 'default profile photo.png', 'abcde@gmail.com', '1212321852', '$2y$10$p8L/JXzbXHcZePAL2HjNO.Zybcs8b6oWZxtLViAE7QHmYk.AByaS.'),
(19, 'Employer Organization', 'default profile photo.png', 'myemail@email.com', '9012345678', '$2y$10$9H.Rswvzl0NvUeG72QbRE.ADpx9LYVkbfgm5KnIl0ZWf24wldo8em'),
(20, 'Employer Individual', 'default profile photo.png', 'aa@bb.com', '1472589630', '$2y$10$SSzM5AKBvzCYWkb9UJBpx.E6aP.nRzUxBJ/ZdSaGtSotCkr47rTs.');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `address`
--
ALTER TABLE `address`
  ADD PRIMARY KEY (`address_id`);

--
-- Indexes for table `employers_individual`
--
ALTER TABLE `employers_individual`
  ADD PRIMARY KEY (`employer_id`),
  ADD KEY `user relation with Employers_Individual` (`user_id`),
  ADD KEY `address` (`address_id`);

--
-- Indexes for table `employers_organization`
--
ALTER TABLE `employers_organization`
  ADD PRIMARY KEY (`organization_id`),
  ADD KEY `user relation with Employers_Organization` (`user_id`),
  ADD KEY `address relation` (`address_id`);

--
-- Indexes for table `job_applications`
--
ALTER TABLE `job_applications`
  ADD PRIMARY KEY (`application_id`),
  ADD KEY `job_id` (`job_id`),
  ADD KEY `seeker_id` (`seeker_id`);

--
-- Indexes for table `job_posts`
--
ALTER TABLE `job_posts`
  ADD PRIMARY KEY (`job_id`),
  ADD KEY `Employers_Individual` (`employer_id`),
  ADD KEY `address_id` (`address_id`);

--
-- Indexes for table `job_seekers`
--
ALTER TABLE `job_seekers`
  ADD PRIMARY KEY (`seeker_id`),
  ADD KEY `user relation with job_seekers` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`,`contact_no`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `address`
--
ALTER TABLE `address`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=572;

--
-- AUTO_INCREMENT for table `employers_individual`
--
ALTER TABLE `employers_individual`
  MODIFY `employer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `employers_organization`
--
ALTER TABLE `employers_organization`
  MODIFY `organization_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `job_applications`
--
ALTER TABLE `job_applications`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `job_posts`
--
ALTER TABLE `job_posts`
  MODIFY `job_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `job_seekers`
--
ALTER TABLE `job_seekers`
  MODIFY `seeker_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `employers_individual`
--
ALTER TABLE `employers_individual`
  ADD CONSTRAINT `address` FOREIGN KEY (`address_id`) REFERENCES `address` (`address_id`),
  ADD CONSTRAINT `user relation with Employers_Individual` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `employers_organization`
--
ALTER TABLE `employers_organization`
  ADD CONSTRAINT `address relation` FOREIGN KEY (`address_id`) REFERENCES `address` (`address_id`),
  ADD CONSTRAINT `user relation with Employers_Organization` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `job_applications`
--
ALTER TABLE `job_applications`
  ADD CONSTRAINT `job_applications_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `job_posts` (`job_id`),
  ADD CONSTRAINT `job_applications_ibfk_2` FOREIGN KEY (`seeker_id`) REFERENCES `job_seekers` (`seeker_id`);

--
-- Constraints for table `job_posts`
--
ALTER TABLE `job_posts`
  ADD CONSTRAINT `Employers_Individual` FOREIGN KEY (`employer_id`) REFERENCES `employers_individual` (`employer_id`),
  ADD CONSTRAINT `job_posts_ibfk_1` FOREIGN KEY (`address_id`) REFERENCES `address` (`address_id`);

--
-- Constraints for table `job_seekers`
--
ALTER TABLE `job_seekers`
  ADD CONSTRAINT `user relation with job_seekers` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
