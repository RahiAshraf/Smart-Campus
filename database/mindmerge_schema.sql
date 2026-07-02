-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 28, 2026 at 03:09 PM
-- Server version: 10.4.25-MariaDB
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mindmerge`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `attendance_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `attendance_date` date NOT NULL,
  `attendance_time` time DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `teacher_name` varchar(120) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `period_id` int(11) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `teacher_assignment_id` int(11) DEFAULT NULL,
  `attendance_day` varchar(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `attendance_mode` enum('daily','period') NOT NULL DEFAULT 'period'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`attendance_id`, `class_id`, `section_id`, `attendance_date`, `attendance_time`, `remarks`, `created_by`, `teacher_id`, `teacher_name`, `created_at`, `period_id`, `subject_id`, `teacher_assignment_id`, `attendance_day`, `updated_at`, `attendance_mode`) VALUES
(10, 12, 11, '2026-06-11', NULL, 'All present', 12, NULL, NULL, '2026-06-11 06:38:40', NULL, NULL, NULL, 'thursday', NULL, 'daily'),
(11, 12, 11, '2026-06-10', NULL, '', 12, NULL, NULL, '2026-06-11 13:54:48', NULL, NULL, NULL, 'wednesday', NULL, 'daily'),
(12, 12, 11, '2026-06-09', NULL, '', 12, NULL, NULL, '2026-06-11 13:55:03', NULL, NULL, NULL, 'tuesday', NULL, 'daily'),
(13, 12, 11, '2026-06-08', NULL, '', 12, NULL, NULL, '2026-06-11 13:55:23', NULL, NULL, NULL, 'monday', NULL, 'daily'),
(14, 12, 11, '2026-06-07', NULL, '', 12, NULL, NULL, '2026-06-11 13:55:44', NULL, NULL, NULL, 'sunday', NULL, 'daily'),
(15, 12, 11, '2026-06-06', NULL, '', 12, NULL, NULL, '2026-06-11 13:56:24', NULL, NULL, NULL, 'saturday', NULL, 'daily'),
(16, 13, 13, '2026-06-11', NULL, '', 12, NULL, NULL, '2026-06-11 13:59:03', NULL, NULL, NULL, 'thursday', NULL, 'daily'),
(17, 13, 13, '2026-06-10', NULL, '', 12, NULL, NULL, '2026-06-11 14:05:53', NULL, NULL, NULL, 'wednesday', NULL, 'daily');

-- --------------------------------------------------------

--
-- Table structure for table `attendance_records`
--

CREATE TABLE `attendance_records` (
  `record_id` int(11) NOT NULL,
  `attendance_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `status` enum('present','absent','late','leave','medical_leave','od') DEFAULT 'present',
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `attendance_records`
--

INSERT INTO `attendance_records` (`record_id`, `attendance_id`, `student_id`, `status`, `remarks`, `created_at`) VALUES
(15, 10, 6, 'present', NULL, '2026-06-11 06:38:40'),
(16, 10, 7, 'present', NULL, '2026-06-11 06:38:40'),
(17, 11, 6, 'present', NULL, '2026-06-11 13:54:48'),
(18, 11, 7, 'present', NULL, '2026-06-11 13:54:48'),
(19, 12, 6, 'present', NULL, '2026-06-11 13:55:03'),
(20, 12, 7, 'present', NULL, '2026-06-11 13:55:03'),
(21, 13, 6, 'absent', NULL, '2026-06-11 13:55:23'),
(22, 13, 7, 'absent', NULL, '2026-06-11 13:55:23'),
(23, 14, 6, 'late', NULL, '2026-06-11 13:55:44'),
(24, 14, 7, 'late', NULL, '2026-06-11 13:55:44'),
(25, 15, 6, 'present', NULL, '2026-06-11 13:56:24'),
(26, 15, 7, 'medical_leave', NULL, '2026-06-11 13:56:24'),
(27, 16, 10, 'present', NULL, '2026-06-11 13:59:03'),
(28, 17, 10, 'present', NULL, '2026-06-11 14:05:53');

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `class_id` int(11) NOT NULL,
  `class_code` varchar(20) DEFAULT NULL,
  `class_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`class_id`, `class_code`, `class_name`, `description`, `status`, `created_at`) VALUES
(12, '10', 'Tenth', '', 'active', '2026-06-11 05:25:04'),
(13, '11', 'Inter first year', '', 'active', '2026-06-11 05:26:06');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `message`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 'R.Baba', 'babafake2008@gmail.com', 'hi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-27 11:48:38');

-- --------------------------------------------------------

--
-- Table structure for table `exams`
--

CREATE TABLE `exams` (
  `exam_id` int(11) NOT NULL,
  `exam_name` varchar(100) NOT NULL,
  `exam_type` enum('unit_test','mid_exam','semester','annual','custom') DEFAULT 'custom',
  `class_id` int(11) DEFAULT NULL,
  `section_id` int(11) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `custom_subject` varchar(100) DEFAULT NULL,
  `total_marks` int(11) DEFAULT 100,
  `exam_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('upcoming','ongoing','completed') DEFAULT 'upcoming',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `exams`
--

INSERT INTO `exams` (`exam_id`, `exam_name`, `exam_type`, `class_id`, `section_id`, `subject_id`, `custom_subject`, `total_marks`, `exam_date`, `start_time`, `end_time`, `description`, `status`, `created_at`) VALUES
(4, 'Maths', 'annual', 12, 11, NULL, 'Maths', 100, '2026-06-28', '09:30:00', '10:30:00', '', 'upcoming', '2026-06-23 12:41:02'),
(6, 'Physics', 'annual', NULL, NULL, NULL, 'Physics', 100, '2026-06-27', '10:30:00', '12:30:00', '', 'completed', '2026-06-26 00:52:41');

-- --------------------------------------------------------

--
-- Table structure for table `exam_subjects`
--

CREATE TABLE `exam_subjects` (
  `exam_subject_id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `exam_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `max_marks` int(11) DEFAULT 100,
  `pass_marks` int(11) DEFAULT 35,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `fee_payments`
--

CREATE TABLE `fee_payments` (
  `payment_id` int(11) NOT NULL,
  `receipt_no` varchar(50) DEFAULT NULL,
  `student_fee_id` int(11) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_method` enum('cash','upi','card','bank_transfer','cheque') DEFAULT 'cash',
  `transaction_ref` varchar(100) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `fee_payments`
--

INSERT INTO `fee_payments` (`payment_id`, `receipt_no`, `student_fee_id`, `amount_paid`, `payment_date`, `payment_method`, `transaction_ref`, `remarks`, `created_by`, `created_at`) VALUES
(1, 'MMF202606244600', 3, '4500.00', '2026-06-24', 'upi', 'UTR000001', '', 26, '2026-06-24 10:03:12'),
(2, 'MMF202606251914', 4, '3500.00', '2026-06-25', 'cash', 'UTR000002', '', 26, '2026-06-25 03:49:53'),
(3, 'MMF202606288727', 2, '4500.00', '2026-06-28', 'cash', '', '', 26, '2026-06-28 10:20:47');

-- --------------------------------------------------------

--
-- Table structure for table `fee_structures`
--

CREATE TABLE `fee_structures` (
  `fee_structure_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `fee_name` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `due_date` date DEFAULT NULL,
  `academic_year` varchar(20) DEFAULT '2026-27',
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `fee_structures`
--

INSERT INTO `fee_structures` (`fee_structure_id`, `class_id`, `fee_name`, `amount`, `due_date`, `academic_year`, `description`, `status`, `created_at`) VALUES
(1, 13, 'Library Fee', '4500.00', '2026-06-30', '2026-27', '', 'active', '2026-06-23 15:22:22'),
(2, 12, 'Library Fee', '4500.00', '2026-06-30', '2026-27', '', 'active', '2026-06-23 15:22:39');

-- --------------------------------------------------------

--
-- Table structure for table `grading_system`
--

CREATE TABLE `grading_system` (
  `grading_id` int(11) NOT NULL,
  `grade_name` varchar(20) NOT NULL,
  `min_marks` decimal(6,2) NOT NULL,
  `max_marks` decimal(6,2) NOT NULL,
  `grade_point` decimal(4,2) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `type` enum('general','exam','fee','attendance','result','announcement','holiday','event','transport','emergency') NOT NULL DEFAULT 'general',
  `source_module` varchar(50) DEFAULT NULL,
  `source_ref` varchar(100) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `title`, `message`, `type`, `source_module`, `source_ref`, `created_by`, `created_at`) VALUES
(1, 'All students should attend to class properly', 'Message to all students', 'attendance', NULL, NULL, 24, '2026-06-12 12:42:31'),
(2, 'Results Published', 'Examination results are now available. Log in to the portal to view your detailed mark sheet and performance summary.', 'result', NULL, NULL, 26, '2026-06-13 06:27:57'),
(3, 'Exam Schedule Published', 'The examination schedule has been published. Please check the exam dates, timings, and venue details on the portal.', 'exam', NULL, NULL, 26, '2026-06-13 06:32:24'),
(4, 'Holiday Announcement', 'The campus will remain closed on the announced holiday. Regular classes and activities will resume on the next working day.', 'holiday', NULL, NULL, 26, '2026-06-14 10:40:44'),
(5, 'All Drivers are Requested', 'All Avaliable Drivers kindly Report to Auditorium Immediately', 'general', NULL, NULL, 26, '2026-06-21 15:05:02'),
(6, 'Fee Payment Reminder', 'This is a reminder that your fee payment is pending. Please complete the payment before the due date to avoid late penalties.', 'fee', NULL, NULL, 26, '2026-06-26 12:19:10');

-- --------------------------------------------------------

--
-- Table structure for table `notification_reads`
--

CREATE TABLE `notification_reads` (
  `read_id` int(11) NOT NULL,
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `read_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `notification_reads`
--

INSERT INTO `notification_reads` (`read_id`, `notification_id`, `user_id`, `read_at`) VALUES
(1, 1, 26, '2026-06-13 06:25:59'),
(2, 2, 26, '2026-06-13 06:27:57'),
(3, 2, 16, '2026-06-13 06:31:43'),
(4, 3, 26, '2026-06-13 06:32:24'),
(5, 1, 24, '2026-06-13 06:32:56'),
(6, 2, 24, '2026-06-13 06:33:08'),
(7, 3, 24, '2026-06-13 06:33:15'),
(8, 4, 26, '2026-06-14 10:40:44'),
(9, 4, 16, '2026-06-14 10:41:34'),
(10, 5, 26, '2026-06-21 15:05:02'),
(11, 5, 28, '2026-06-21 15:28:28'),
(12, 6, 26, '2026-06-26 12:19:11'),
(13, 6, 27, '2026-06-26 22:28:23'),
(14, 5, 29, '2026-06-28 15:46:06');

-- --------------------------------------------------------

--
-- Table structure for table `notification_targets`
--

CREATE TABLE `notification_targets` (
  `target_id` int(11) NOT NULL,
  `notification_id` int(11) NOT NULL,
  `target_type` enum('role','class','section','student','teacher') DEFAULT NULL,
  `target_value` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `notification_targets`
--

INSERT INTO `notification_targets` (`target_id`, `notification_id`, `target_type`, `target_value`) VALUES
(1, 1, 'student', 'STU00007'),
(2, 2, 'section', '11'),
(3, 3, 'role', 'teacher'),
(4, 4, 'role', 'student'),
(5, 5, 'role', 'driver'),
(6, 6, 'role', 'parent');

-- --------------------------------------------------------

--
-- Table structure for table `notification_templates`
--

CREATE TABLE `notification_templates` (
  `template_id` int(11) NOT NULL,
  `template_name` varchar(100) NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `parents`
--

CREATE TABLE `parents` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `parent_id` varchar(50) DEFAULT NULL,
  `student_id` varchar(50) DEFAULT NULL,
  `relationship_name` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `parents`
--

INSERT INTO `parents` (`id`, `user_id`, `parent_id`, `student_id`, `relationship_name`, `created_at`) VALUES
(1, 27, 'PAR0001', 'STU00001', 'Father', '2026-06-19 05:54:31'),
(2, 27, 'PAR0002', 'STU00002', 'Mother', '2026-06-26 00:26:33');

-- --------------------------------------------------------

--
-- Table structure for table `periods`
--

CREATE TABLE `periods` (
  `period_id` int(11) NOT NULL,
  `template_id` int(11) NOT NULL,
  `period_name` varchar(100) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `period_type` enum('regular','break','lunch','lab','exam','activity') DEFAULT 'regular',
  `attendance_allowed` enum('yes','no') DEFAULT 'yes',
  `is_teaching_period` enum('yes','no') DEFAULT 'yes',
  `display_color` varchar(20) DEFAULT '#3b82f6',
  `room_required` enum('yes','no') DEFAULT 'yes',
  `notes` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `periods`
--

INSERT INTO `periods` (`period_id`, `template_id`, `period_name`, `start_time`, `end_time`, `period_type`, `attendance_allowed`, `is_teaching_period`, `display_color`, `room_required`, `notes`, `sort_order`, `status`, `created_at`) VALUES
(10, 7, 'P1', '09:10:00', '10:00:00', 'regular', 'yes', 'yes', '#ffd500', 'yes', '', 1, 'active', '2026-06-09 09:59:39'),
(11, 7, 'P2', '10:00:00', '10:50:00', 'regular', 'yes', 'yes', '#3b82f6', 'yes', '', 2, 'active', '2026-06-09 10:00:16'),
(12, 7, 'P3', '10:50:00', '11:40:00', 'regular', 'yes', 'yes', '#3b82f6', 'yes', '', 3, 'active', '2026-06-09 10:01:33'),
(13, 7, 'P4', '11:40:00', '12:30:00', 'regular', 'yes', 'yes', '#3b82f6', 'yes', '', 4, 'active', '2026-06-09 10:02:14'),
(15, 7, 'P5', '13:30:00', '14:30:00', 'regular', 'yes', 'yes', '#3b82f6', 'yes', '', 6, 'active', '2026-06-09 10:05:27'),
(16, 7, 'P6', '14:30:00', '15:30:00', 'regular', 'yes', 'yes', '#3b82f6', 'yes', '', 7, 'active', '2026-06-09 10:06:20'),
(17, 7, 'P7', '15:30:00', '16:30:00', 'regular', 'yes', 'yes', '#3b82f6', 'yes', '', 8, 'active', '2026-06-09 10:07:18');

-- --------------------------------------------------------

--
-- Table structure for table `period_templates`
--

CREATE TABLE `period_templates` (
  `template_id` int(11) NOT NULL,
  `template_code` varchar(50) DEFAULT NULL,
  `template_name` varchar(100) NOT NULL,
  `template_type` enum('regular','exam','lab','hostel','custom') DEFAULT 'regular',
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `period_templates`
--

INSERT INTO `period_templates` (`template_id`, `template_code`, `template_name`, `template_type`, `description`, `status`, `created_at`) VALUES
(7, 'REGULAR', 'Classes', 'regular', '', 'active', '2026-06-09 09:58:39');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `permission_id` int(11) NOT NULL,
  `module_key` varchar(50) NOT NULL,
  `action_key` varchar(20) NOT NULL,
  `label` varchar(120) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`permission_id`, `module_key`, `action_key`, `label`, `sort_order`) VALUES
(1, 'dashboard', 'view', 'View Dashboard', 10),
(2, 'profile', 'view', 'View Profile', 20),
(3, 'profile', 'edit', 'Edit Profile', 21),
(4, 'classes', 'view', 'View Classes', 100),
(5, 'classes', 'create', 'Create Classes', 101),
(6, 'classes', 'edit', 'Edit Classes', 102),
(7, 'classes', 'delete', 'Delete Classes', 103),
(8, 'sections', 'view', 'View Sections', 110),
(9, 'sections', 'create', 'Create Sections', 111),
(10, 'sections', 'edit', 'Edit Sections', 112),
(11, 'sections', 'delete', 'Delete Sections', 113),
(12, 'students', 'view', 'View Students', 120),
(13, 'students', 'create', 'Create Students', 121),
(14, 'students', 'edit', 'Edit Students', 122),
(15, 'students', 'delete', 'Delete Students', 123),
(16, 'teachers', 'view', 'View Teachers', 130),
(17, 'teachers', 'create', 'Create Teachers', 131),
(18, 'teachers', 'edit', 'Edit Teachers', 132),
(19, 'teachers', 'delete', 'Delete Teachers', 133),
(20, 'schedules', 'view', 'View Schedules', 140),
(21, 'schedules', 'create', 'Create Schedules', 141),
(22, 'schedules', 'edit', 'Edit Schedules', 142),
(23, 'schedules', 'delete', 'Delete Schedules', 143),
(24, 'timetables', 'view', 'View Timetables', 150),
(25, 'timetables', 'create', 'Create Timetables', 151),
(26, 'timetables', 'edit', 'Edit Timetables', 152),
(27, 'timetables', 'delete', 'Delete Timetables', 153),
(28, 'attendance', 'view', 'View Attendance', 160),
(29, 'attendance', 'create', 'Create Attendance', 161),
(30, 'attendance', 'edit', 'Edit Attendance', 162),
(31, 'attendance', 'delete', 'Delete Attendance', 163),
(32, 'teacher_attendance', 'view', 'View Teacher Attendance', 170),
(33, 'teacher_attendance', 'create', 'Create Teacher Attendance', 171),
(34, 'teacher_attendance', 'edit', 'Edit Teacher Attendance', 172),
(35, 'teacher_attendance', 'delete', 'Delete Teacher Attendance', 173),
(36, 'notifications', 'view', 'View Notifications', 180),
(37, 'notifications', 'create', 'Create Notifications', 181),
(38, 'notifications', 'edit', 'Edit Notifications', 182),
(39, 'notifications', 'delete', 'Delete Notifications', 183),
(40, 'exams', 'view', 'View Exams', 190),
(41, 'exams', 'create', 'Create Exams', 191),
(42, 'exams', 'edit', 'Edit Exams', 192),
(43, 'exams', 'delete', 'Delete Exams', 193),
(44, 'permissions', 'view', 'View Permission Settings', 200),
(45, 'permissions', 'edit', 'Edit Permission Settings', 201),
(46, 'results', 'view', 'View Results', 185),
(47, 'results', 'create', 'Create Results', 186),
(48, 'results', 'edit', 'Edit Results', 187),
(49, 'results', 'delete', 'Delete Results', 188),
(50, 'transport', 'view', 'View Transport', 195),
(51, 'transport', 'create', 'Create Transport', 196),
(52, 'transport', 'edit', 'Edit Transport', 197),
(53, 'transport', 'delete', 'Delete Transport', 198),
(99, 'fees', 'view', 'View Fees', 500),
(100, 'fees', 'create', 'Create Fees', 501),
(101, 'fees', 'edit', 'Edit Fees', 502),
(102, 'fees', 'delete', 'Delete Fees', 503),
(124, 'salaries', 'view', 'View Salaries', 910),
(125, 'salaries', 'create', 'Create Salaries', 911),
(126, 'salaries', 'edit', 'Edit/Pay Salaries', 912),
(127, 'salaries', 'delete', 'Delete Salaries', 913),
(128, 'billing', 'view', 'View Billing', 920);

-- --------------------------------------------------------

--
-- Table structure for table `results`
--

CREATE TABLE `results` (
  `result_id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `class_id` int(11) DEFAULT NULL,
  `section_id` int(11) DEFAULT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `status` enum('draft','published') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `results`
--

INSERT INTO `results` (`result_id`, `exam_id`, `class_id`, `section_id`, `published_at`, `status`, `created_at`) VALUES
(3, 4, 12, 11, '2026-06-23 12:41:49', 'published', '2026-06-23 12:41:11'),
(5, 6, NULL, NULL, '2026-06-26 01:53:44', 'published', '2026-06-26 01:52:47');

-- --------------------------------------------------------

--
-- Table structure for table `result_marks`
--

CREATE TABLE `result_marks` (
  `mark_id` int(11) NOT NULL,
  `result_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `marks_obtained` decimal(6,2) DEFAULT 0.00,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `result_marks`
--

INSERT INTO `result_marks` (`mark_id`, `result_id`, `student_id`, `marks_obtained`, `remarks`, `created_at`) VALUES
(1, 3, 6, '98.00', 'Good', '2026-06-23 12:41:41'),
(2, 3, 7, '97.00', 'Good', '2026-06-23 12:41:41'),
(3, 3, 13, '37.00', 'Border Pass', '2026-06-23 12:41:41'),
(4, 5, 6, '30.00', '', '2026-06-26 01:53:16'),
(5, 5, 7, '100.00', '', '2026-06-26 01:53:16'),
(6, 5, 8, '90.00', '', '2026-06-26 01:53:16'),
(7, 5, 9, '80.00', '', '2026-06-26 01:53:16'),
(8, 5, 12, '70.00', '', '2026-06-26 01:53:16'),
(9, 5, 10, '60.00', '', '2026-06-26 01:53:16'),
(10, 5, 13, '50.00', '', '2026-06-26 01:53:16'),
(11, 5, 11, '40.00', '', '2026-06-26 01:53:16');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_permission_id` int(11) NOT NULL,
  `role` enum('admin','teacher','student','parent','driver') NOT NULL,
  `permission_id` int(11) NOT NULL,
  `granted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`role_permission_id`, `role`, `permission_id`, `granted`) VALUES
(1, 'admin', 28, 1),
(2, 'admin', 29, 1),
(3, 'admin', 30, 1),
(4, 'admin', 31, 1),
(5, 'admin', 4, 1),
(6, 'admin', 5, 1),
(7, 'admin', 6, 1),
(8, 'admin', 7, 1),
(9, 'admin', 1, 1),
(10, 'admin', 40, 1),
(11, 'admin', 41, 1),
(12, 'admin', 42, 1),
(13, 'admin', 43, 1),
(14, 'admin', 36, 1),
(15, 'admin', 37, 1),
(16, 'admin', 38, 1),
(17, 'admin', 39, 1),
(18, 'admin', 44, 1),
(19, 'admin', 45, 1),
(20, 'admin', 2, 1),
(21, 'admin', 3, 1),
(22, 'admin', 20, 1),
(23, 'admin', 21, 1),
(24, 'admin', 22, 1),
(25, 'admin', 23, 1),
(26, 'admin', 8, 1),
(27, 'admin', 9, 1),
(28, 'admin', 10, 1),
(29, 'admin', 11, 1),
(30, 'admin', 12, 1),
(31, 'admin', 13, 1),
(32, 'admin', 14, 1),
(33, 'admin', 15, 1),
(34, 'admin', 16, 1),
(35, 'admin', 17, 1),
(36, 'admin', 18, 1),
(37, 'admin', 19, 1),
(38, 'admin', 32, 1),
(39, 'admin', 33, 1),
(40, 'admin', 34, 1),
(41, 'admin', 35, 1),
(42, 'admin', 24, 1),
(43, 'admin', 25, 1),
(44, 'admin', 26, 1),
(45, 'admin', 27, 1),
(64, 'teacher', 29, 1),
(65, 'teacher', 31, 0),
(66, 'teacher', 30, 1),
(67, 'teacher', 28, 1),
(68, 'teacher', 5, 0),
(69, 'teacher', 7, 0),
(70, 'teacher', 6, 0),
(71, 'teacher', 4, 1),
(72, 'teacher', 1, 1),
(73, 'teacher', 41, 0),
(74, 'teacher', 43, 0),
(75, 'teacher', 42, 0),
(76, 'teacher', 40, 1),
(77, 'teacher', 37, 1),
(78, 'teacher', 39, 0),
(79, 'teacher', 38, 0),
(80, 'teacher', 36, 1),
(81, 'teacher', 45, 0),
(82, 'teacher', 44, 0),
(83, 'teacher', 3, 1),
(84, 'teacher', 2, 1),
(85, 'teacher', 21, 0),
(86, 'teacher', 23, 0),
(87, 'teacher', 22, 0),
(88, 'teacher', 20, 1),
(89, 'teacher', 9, 0),
(90, 'teacher', 11, 0),
(91, 'teacher', 10, 0),
(92, 'teacher', 8, 1),
(93, 'teacher', 13, 0),
(94, 'teacher', 15, 0),
(95, 'teacher', 14, 0),
(96, 'teacher', 12, 1),
(97, 'teacher', 17, 0),
(98, 'teacher', 19, 0),
(99, 'teacher', 18, 0),
(100, 'teacher', 16, 1),
(101, 'teacher', 33, 0),
(102, 'teacher', 35, 0),
(103, 'teacher', 34, 0),
(104, 'teacher', 32, 1),
(105, 'teacher', 25, 0),
(106, 'teacher', 27, 0),
(107, 'teacher', 26, 0),
(108, 'teacher', 24, 1),
(127, 'student', 29, 0),
(128, 'student', 31, 0),
(129, 'student', 30, 0),
(130, 'student', 28, 1),
(131, 'student', 5, 0),
(132, 'student', 7, 0),
(133, 'student', 6, 0),
(134, 'student', 4, 0),
(135, 'student', 1, 1),
(136, 'student', 41, 0),
(137, 'student', 43, 0),
(138, 'student', 42, 0),
(139, 'student', 40, 1),
(140, 'student', 37, 0),
(141, 'student', 39, 0),
(142, 'student', 38, 0),
(143, 'student', 36, 1),
(144, 'student', 45, 0),
(145, 'student', 44, 0),
(146, 'student', 3, 1),
(147, 'student', 2, 1),
(148, 'student', 21, 0),
(149, 'student', 23, 0),
(150, 'student', 22, 0),
(151, 'student', 20, 0),
(152, 'student', 9, 0),
(153, 'student', 11, 0),
(154, 'student', 10, 0),
(155, 'student', 8, 0),
(156, 'student', 13, 0),
(157, 'student', 15, 0),
(158, 'student', 14, 0),
(159, 'student', 12, 0),
(160, 'student', 17, 0),
(161, 'student', 19, 0),
(162, 'student', 18, 0),
(163, 'student', 16, 0),
(164, 'student', 33, 0),
(165, 'student', 35, 0),
(166, 'student', 34, 0),
(167, 'student', 32, 0),
(168, 'student', 25, 0),
(169, 'student', 27, 0),
(170, 'student', 26, 0),
(171, 'student', 24, 1),
(190, 'parent', 29, 0),
(191, 'parent', 31, 0),
(192, 'parent', 30, 0),
(193, 'parent', 28, 1),
(194, 'parent', 5, 0),
(195, 'parent', 7, 0),
(196, 'parent', 6, 0),
(197, 'parent', 4, 1),
(198, 'parent', 1, 1),
(199, 'parent', 41, 0),
(200, 'parent', 43, 0),
(201, 'parent', 42, 0),
(202, 'parent', 40, 1),
(203, 'parent', 37, 0),
(204, 'parent', 39, 0),
(205, 'parent', 38, 0),
(206, 'parent', 36, 1),
(207, 'parent', 45, 0),
(208, 'parent', 44, 0),
(209, 'parent', 3, 1),
(210, 'parent', 2, 1),
(211, 'parent', 21, 0),
(212, 'parent', 23, 0),
(213, 'parent', 22, 0),
(214, 'parent', 20, 0),
(215, 'parent', 9, 0),
(216, 'parent', 11, 0),
(217, 'parent', 10, 0),
(218, 'parent', 8, 1),
(219, 'parent', 13, 0),
(220, 'parent', 15, 0),
(221, 'parent', 14, 0),
(222, 'parent', 12, 0),
(223, 'parent', 17, 0),
(224, 'parent', 19, 0),
(225, 'parent', 18, 0),
(226, 'parent', 16, 0),
(227, 'parent', 33, 0),
(228, 'parent', 35, 0),
(229, 'parent', 34, 0),
(230, 'parent', 32, 0),
(231, 'parent', 25, 0),
(232, 'parent', 27, 0),
(233, 'parent', 26, 0),
(234, 'parent', 24, 1),
(433, 'admin', 46, 1),
(434, 'admin', 47, 1),
(435, 'admin', 48, 1),
(436, 'admin', 49, 1),
(437, 'admin', 50, 1),
(438, 'admin', 51, 1),
(439, 'admin', 52, 1),
(440, 'admin', 53, 1),
(448, 'teacher', 47, 1),
(449, 'teacher', 49, 0),
(450, 'teacher', 48, 1),
(451, 'teacher', 46, 1),
(452, 'teacher', 51, 0),
(453, 'teacher', 53, 0),
(454, 'teacher', 52, 0),
(455, 'teacher', 50, 1),
(463, 'student', 47, 0),
(464, 'student', 49, 0),
(465, 'student', 48, 0),
(466, 'student', 46, 1),
(467, 'student', 51, 0),
(468, 'student', 53, 0),
(469, 'student', 52, 0),
(470, 'student', 50, 1),
(478, 'parent', 47, 0),
(479, 'parent', 49, 0),
(480, 'parent', 48, 0),
(481, 'parent', 46, 1),
(482, 'parent', 51, 0),
(483, 'parent', 53, 0),
(484, 'parent', 52, 0),
(485, 'parent', 50, 1),
(811, '', 1, 1),
(812, '', 36, 1),
(813, '', 2, 1),
(814, '', 50, 1),
(818, 'driver', 1, 1),
(819, 'driver', 36, 1),
(820, 'driver', 2, 1),
(821, 'driver', 50, 1),
(827, 'driver', 29, 0),
(828, 'driver', 31, 0),
(829, 'driver', 30, 0),
(830, 'driver', 28, 0),
(831, 'driver', 5, 0),
(832, 'driver', 7, 0),
(833, 'driver', 6, 0),
(834, 'driver', 4, 0),
(835, 'driver', 41, 0),
(836, 'driver', 43, 0),
(837, 'driver', 42, 0),
(838, 'driver', 40, 0),
(839, 'driver', 37, 0),
(840, 'driver', 39, 0),
(841, 'driver', 38, 0),
(842, 'driver', 45, 0),
(843, 'driver', 44, 0),
(844, 'driver', 3, 0),
(845, 'driver', 47, 0),
(846, 'driver', 49, 0),
(847, 'driver', 48, 0),
(848, 'driver', 46, 0),
(849, 'driver', 21, 0),
(850, 'driver', 23, 0),
(851, 'driver', 22, 0),
(852, 'driver', 20, 0),
(853, 'driver', 9, 0),
(854, 'driver', 11, 0),
(855, 'driver', 10, 0),
(856, 'driver', 8, 0),
(857, 'driver', 13, 0),
(858, 'driver', 15, 0),
(859, 'driver', 14, 0),
(860, 'driver', 12, 0),
(861, 'driver', 17, 0),
(862, 'driver', 19, 0),
(863, 'driver', 18, 0),
(864, 'driver', 16, 0),
(865, 'driver', 33, 0),
(866, 'driver', 35, 0),
(867, 'driver', 34, 0),
(868, 'driver', 32, 0),
(869, 'driver', 25, 0),
(870, 'driver', 27, 0),
(871, 'driver', 26, 0),
(872, 'driver', 24, 0),
(873, 'driver', 51, 0),
(874, 'driver', 53, 0),
(875, 'driver', 52, 0),
(956, 'parent', 99, 1),
(957, 'parent', 100, 0),
(958, 'parent', 101, 0),
(959, 'parent', 102, 0),
(1057, 'admin', 128, 1),
(1058, 'admin', 124, 1),
(1059, 'admin', 125, 1),
(1060, 'admin', 126, 1),
(1061, 'admin', 127, 1);

-- --------------------------------------------------------

--
-- Table structure for table `salary_payments`
--

CREATE TABLE `salary_payments` (
  `payment_id` int(11) NOT NULL,
  `receipt_no` varchar(50) DEFAULT NULL,
  `salary_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `pay_period` varchar(20) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_method` enum('cash','upi','card','bank_transfer','cheque') DEFAULT 'cash',
  `transaction_ref` varchar(100) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `salary_payments`
--

INSERT INTO `salary_payments` (`payment_id`, `receipt_no`, `salary_id`, `teacher_id`, `pay_period`, `amount_paid`, `payment_date`, `payment_method`, `transaction_ref`, `remarks`, `created_by`, `created_at`) VALUES
(1, 'MMS202606288336', 1, 8, '2026-06', '5000.00', '2026-06-28', 'cash', '', '', 26, '2026-06-28 10:19:41');

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `section_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `section_code` varchar(50) NOT NULL,
  `section_name` varchar(100) NOT NULL,
  `capacity` int(11) DEFAULT 50,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`section_id`, `class_id`, `section_code`, `section_name`, `capacity`, `status`, `created_at`) VALUES
(11, 12, 'A', 'A Section', 66, 'active', '2026-06-11 05:26:53'),
(12, 12, 'B', 'B Section', 66, 'active', '2026-06-11 05:27:05'),
(13, 13, 'A', 'A Section', 66, 'active', '2026-06-11 05:27:20'),
(14, 13, 'B', 'B Section', 66, 'active', '2026-06-11 05:28:26');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `student_id` varchar(50) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `section_id` int(11) DEFAULT NULL,
  `class_name` varchar(30) DEFAULT NULL,
  `section_name` varchar(30) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `parent_phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `user_id`, `student_id`, `class_id`, `section_id`, `class_name`, `section_name`, `dob`, `gender`, `address`, `parent_phone`, `created_at`) VALUES
(6, 16, 'STU00001', 12, 11, 'Computer Science & Engineering', 'A Section', '2026-06-11', 'male', '', '9000322870', '2026-06-11 05:30:45'),
(7, 17, 'STU00002', 12, 11, 'Computer Science & Engineering', 'A Section', '2026-06-11', 'male', '', '', '2026-06-11 05:31:26'),
(8, 18, 'STU00003', 12, 12, 'Computer Science & Engineering', 'B Section', '2008-10-10', 'male', '', '9000322870', '2026-06-11 05:32:15'),
(9, 19, 'STU00004', 12, 12, 'Computer Science & Engineering', 'B Section', '2026-06-11', 'male', '', '8790844365', '2026-06-11 05:33:02'),
(10, 20, 'STU00005', 13, 13, 'Computer Science & Engineering', 'A Section', '2026-06-30', 'male', '9-87, nagoor colongy, kuntrapakam(post)\r\ntirupati rural', '9000322870', '2026-06-11 05:33:34'),
(11, 21, 'STU00006', 13, 14, 'Computer Science & Engineering', 'B Section', '2026-12-31', 'male', '', '8790844365', '2026-06-11 05:34:20'),
(12, 22, 'STU00007', 13, 14, 'Computer Science & Engineering', 'B Section', '2023-11-30', 'male', '', '997934', '2026-06-11 05:35:29'),
(13, 31, 'STU00008', 12, 11, 'Computer Science & Engineering', 'A Section', '2000-02-20', 'male', '9-87, nagoor colongy, kuntrapakam(post)\r\ntirupati rural', '9192320232', '2026-06-22 00:05:33');

-- --------------------------------------------------------

--
-- Table structure for table `student_fees`
--

CREATE TABLE `student_fees` (
  `student_fee_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `fee_structure_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `paid_amount` decimal(10,2) DEFAULT 0.00,
  `balance_amount` decimal(10,2) NOT NULL,
  `payment_status` enum('unpaid','partial','paid') DEFAULT 'unpaid',
  `last_payment_date` date DEFAULT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `student_fees`
--

INSERT INTO `student_fees` (`student_fee_id`, `student_id`, `fee_structure_id`, `amount`, `paid_amount`, `balance_amount`, `payment_status`, `last_payment_date`, `assigned_at`) VALUES
(1, 12, 1, '4500.00', '0.00', '4500.00', 'unpaid', NULL, '2026-06-24 09:37:46'),
(2, 10, 1, '4500.00', '4500.00', '0.00', 'paid', '2026-06-28', '2026-06-24 09:37:46'),
(3, 11, 1, '4500.00', '4500.00', '0.00', 'paid', '2026-06-24', '2026-06-24 09:37:46'),
(4, 6, 2, '4500.00', '3500.00', '1000.00', 'partial', '2026-06-25', '2026-06-24 09:38:25'),
(5, 7, 2, '4500.00', '0.00', '4500.00', 'unpaid', NULL, '2026-06-24 09:38:25'),
(6, 8, 2, '4500.00', '0.00', '4500.00', 'unpaid', NULL, '2026-06-24 09:38:25'),
(7, 9, 2, '4500.00', '0.00', '4500.00', 'unpaid', NULL, '2026-06-24 09:38:25'),
(8, 13, 2, '4500.00', '0.00', '4500.00', 'unpaid', NULL, '2026-06-24 09:38:25');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `subject_id` int(11) NOT NULL,
  `subject_code` varchar(30) NOT NULL,
  `subject_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`subject_id`, `subject_code`, `subject_name`, `description`, `status`, `created_at`) VALUES
(7, 'CSE001', 'DBMS', '', 'active', '2026-06-11 05:29:09'),
(8, 'CSE002', 'OS', '', 'active', '2026-06-11 05:29:19'),
(9, 'CSE003', 'Java', '', 'active', '2026-06-11 05:29:31'),
(10, 'CSE004', 'Python', '', 'active', '2026-06-11 05:29:41');

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `teacher_id` varchar(50) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `section_id` int(11) DEFAULT NULL,
  `subject_name` varchar(100) DEFAULT NULL,
  `qualification` varchar(100) DEFAULT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`id`, `user_id`, `teacher_id`, `class_id`, `section_id`, `subject_name`, `qualification`, `specialization`, `salary`, `created_at`) VALUES
(7, 23, 'TCH00001', NULL, NULL, NULL, 'M.tech.,', NULL, NULL, '2026-06-11 05:36:14'),
(8, 24, 'TCH00002', NULL, NULL, NULL, 'M.Tech.,', NULL, NULL, '2026-06-11 05:36:55'),
(9, 30, 'TCH00003', NULL, NULL, NULL, 'M.Tech.,', NULL, NULL, '2026-06-22 00:03:49');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_assignments`
--

CREATE TABLE `teacher_assignments` (
  `assignment_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `assignment_role` enum('primary','co_primary','lab_incharge','lab_faculty','lab_assistant') NOT NULL DEFAULT 'primary',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `teacher_assignments`
--

INSERT INTO `teacher_assignments` (`assignment_id`, `teacher_id`, `subject_id`, `class_id`, `section_id`, `assignment_role`, `created_at`) VALUES
(12, 7, 7, 12, 11, 'primary', '2026-06-11 05:37:14'),
(13, 8, 7, 12, 11, 'co_primary', '2026-06-11 05:37:25'),
(14, 7, 9, 12, 11, 'co_primary', '2026-06-11 05:37:39'),
(15, 8, 9, 12, 11, 'primary', '2026-06-11 05:38:46'),
(16, 7, 7, 12, 12, 'lab_incharge', '2026-06-11 05:39:07'),
(17, 7, 10, 13, 13, 'lab_incharge', '2026-06-11 05:39:20'),
(18, 7, 8, 13, 14, 'lab_faculty', '2026-06-11 05:39:36'),
(19, 8, 8, 12, 12, 'lab_incharge', '2026-06-11 05:39:50'),
(20, 8, 9, 13, 14, 'lab_faculty', '2026-06-11 05:40:12');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_attendance`
--

CREATE TABLE `teacher_attendance` (
  `attendance_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `attendance_date` date NOT NULL,
  `status` enum('present','absent','leave','medical_leave','od','half_day') NOT NULL DEFAULT 'present',
  `remarks` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `teacher_attendance`
--

INSERT INTO `teacher_attendance` (`attendance_id`, `teacher_id`, `attendance_date`, `status`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 7, '2026-06-12', 'present', '', 12, '2026-06-12 05:22:36', '2026-06-12 05:32:25'),
(2, 8, '2026-06-12', 'present', '', 12, '2026-06-12 05:22:36', '2026-06-12 05:32:25'),
(3, 7, '2026-06-11', 'od', '', 12, '2026-06-12 05:25:51', NULL),
(4, 8, '2026-06-11', 'absent', '', 12, '2026-06-12 05:25:51', NULL),
(5, 7, '2026-06-13', 'present', '', 26, '2026-06-13 05:33:22', NULL),
(6, 8, '2026-06-13', 'present', '', 26, '2026-06-13 05:33:22', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `teacher_salaries`
--

CREATE TABLE `teacher_salaries` (
  `salary_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `monthly_salary` decimal(10,2) NOT NULL,
  `effective_from` date NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `teacher_salaries`
--

INSERT INTO `teacher_salaries` (`salary_id`, `teacher_id`, `monthly_salary`, `effective_from`, `status`, `notes`, `created_by`, `created_at`) VALUES
(1, 8, '5000.00', '2026-06-28', 'active', 'Good work', 26, '2026-06-28 10:19:25');

-- --------------------------------------------------------

--
-- Table structure for table `timetables`
--

CREATE TABLE `timetables` (
  `timetable_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `template_id` int(11) NOT NULL,
  `academic_year` varchar(20) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `effective_from` date DEFAULT NULL,
  `effective_to` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `timetables`
--

INSERT INTO `timetables` (`timetable_id`, `class_id`, `section_id`, `template_id`, `academic_year`, `status`, `created_at`, `effective_from`, `effective_to`) VALUES
(12, 12, 11, 7, '2026-27', 'active', '2026-06-11 05:42:10', '2026-06-11', NULL),
(13, 13, 13, 7, '2025-26', 'active', '2026-06-11 13:58:34', '2026-06-11', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `timetable_entries`
--

CREATE TABLE `timetable_entries` (
  `entry_id` int(11) NOT NULL,
  `timetable_id` int(11) NOT NULL,
  `day_of_week` enum('monday','tuesday','wednesday','thursday','friday','saturday','sunday') NOT NULL,
  `period_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `teacher_assignment_id` int(11) NOT NULL,
  `room_no` varchar(50) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `timetable_entries`
--

INSERT INTO `timetable_entries` (`entry_id`, `timetable_id`, `day_of_week`, `period_id`, `subject_id`, `teacher_assignment_id`, `room_no`, `remarks`, `created_at`) VALUES
(17, 12, 'monday', 10, 7, 12, 'NPN 202', '', '2026-06-11 05:42:43'),
(18, 12, 'tuesday', 10, 9, 15, '', '', '2026-06-11 05:42:53'),
(19, 12, 'wednesday', 10, 7, 15, '', '', '2026-06-11 05:43:00'),
(20, 12, 'thursday', 12, 7, 12, '', '', '2026-06-11 05:43:06'),
(21, 12, 'thursday', 17, 7, 12, '', '', '2026-06-11 05:43:14'),
(22, 12, 'monday', 16, 7, 12, '', '', '2026-06-13 17:11:16');

-- --------------------------------------------------------

--
-- Table structure for table `transport_buses`
--

CREATE TABLE `transport_buses` (
  `bus_id` int(11) NOT NULL,
  `bus_number` varchar(50) NOT NULL,
  `bus_name` varchar(100) DEFAULT NULL,
  `capacity` int(11) DEFAULT 40,
  `driver_id` int(11) DEFAULT NULL,
  `helper_id` int(11) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `transport_buses`
--

INSERT INTO `transport_buses` (`bus_id`, `bus_number`, `bus_name`, `capacity`, `driver_id`, `helper_id`, `start_time`, `end_time`, `status`) VALUES
(3, 'AP03 1234', 'Madanapalle Bus', 45, 6, NULL, '07:30:00', '08:30:00', 'active'),
(4, 'AP03 143143', 'Pungunur Bus', 44, 7, NULL, '00:00:00', '00:00:00', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `transport_bus_routes`
--

CREATE TABLE `transport_bus_routes` (
  `id` int(11) NOT NULL,
  `bus_id` int(11) NOT NULL,
  `route_id` int(11) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `transport_live_location`
--

CREATE TABLE `transport_live_location` (
  `location_id` int(11) NOT NULL,
  `bus_id` int(11) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('not_started','running','completed') DEFAULT 'not_started'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `transport_live_location`
--

INSERT INTO `transport_live_location` (`location_id`, `bus_id`, `latitude`, `longitude`, `updated_at`, `status`) VALUES
(1, 3, '13.63055900', '78.48040600', '2026-06-28 10:15:06', 'not_started'),
(10, 4, '13.63055900', '78.48040600', '2026-06-28 10:15:33', 'not_started');

-- --------------------------------------------------------

--
-- Table structure for table `transport_routes`
--

CREATE TABLE `transport_routes` (
  `route_id` int(11) NOT NULL,
  `bus_id` int(11) NOT NULL,
  `route_name` varchar(100) NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `route_description` text DEFAULT NULL,
  `route_color` varchar(20) DEFAULT '#2563eb'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `transport_staff`
--

CREATE TABLE `transport_staff` (
  `staff_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `staff_type` enum('driver','helper') NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `license_number` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `address` text DEFAULT NULL,
  `emergency_contact` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `transport_staff`
--

INSERT INTO `transport_staff` (`staff_id`, `user_id`, `profile_photo`, `staff_type`, `full_name`, `phone`, `license_number`, `status`, `created_at`, `address`, `emergency_contact`) VALUES
(6, 28, NULL, 'driver', 'Jai Ballaya', '8790844365', 'LIC0001', 'active', '2026-06-21 05:34:39', '9-87, nagoor colongy, kuntrapakam(post)\r\ntirupati rural', '9191001912'),
(7, 29, NULL, 'driver', 'Muthu', '9704290783', 'LIC0002', 'active', '2026-06-21 15:42:29', '9-87, nagoor colongy, kuntrapakam(post)\r\ntirupati rural', '9191001912');

-- --------------------------------------------------------

--
-- Table structure for table `transport_stops`
--

CREATE TABLE `transport_stops` (
  `stop_id` int(11) NOT NULL,
  `stop_name` varchar(100) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `stop_order` int(11) DEFAULT NULL,
  `route_id` int(11) NOT NULL,
  `is_start` tinyint(1) DEFAULT 0,
  `is_end` tinyint(1) DEFAULT 0,
  `arrival_time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `transport_student_assignments`
--

CREATE TABLE `transport_student_assignments` (
  `assignment_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `bus_id` int(11) DEFAULT NULL,
  `stop_id` int(11) DEFAULT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `transport_student_assignments`
--

INSERT INTO `transport_student_assignments` (`assignment_id`, `student_id`, `bus_id`, `stop_id`, `assigned_at`) VALUES
(1, 10, 4, NULL, '2026-06-22 01:14:03'),
(2, 12, 3, NULL, '2026-06-22 01:14:13'),
(4, 6, 3, NULL, '2026-06-22 02:12:03'),
(5, 7, 4, NULL, '2026-06-26 07:05:59');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','teacher','student','parent','driver','helper') NOT NULL,
  `profile_photo` varchar(255) DEFAULT 'default.png',
  `theme_preference` varchar(20) DEFAULT 'light',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `admin_id` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `password`, `role`, `profile_photo`, `theme_preference`, `created_at`, `admin_id`) VALUES
(16, 'Abdul Karim S', 'adbul@gmail.com', '9121212121', '$2y$10$FdCqTIOuk8LagT9sj8MuyOrW1jyQcBU9I6Xrhp3QxkR2gh0tGm0Ze', 'student', 'time_1782088877.jpg', 'light', '2026-06-11 05:30:45', NULL),
(17, 'Akash S', 'akash@gmail.com', '9704290782', '$2y$10$taXNmdTqTfpGljHuRwI37.bznLJB/w.9kluXVhRJvUCnOaLknVnDm', 'student', 'default.png', 'light', '2026-06-11 05:31:26', NULL),
(18, 'Akshaya G', 'akshaya@gmail.com', '9704290782', '$2y$10$VMxngb68vOnq0JdeZNKN1.RdzbHyvd19wW8ROpoBSh0Al8bxQIz92', 'student', 'default.png', 'light', '2026-06-11 05:32:15', NULL),
(19, 'Aruna N', 'aruna@gmail.com', '8790844365', '$2y$10$jJ3lhIZTOrr4uRlr1meWpuYFQ2YInTBO4ej2pDFvmix5rxgS1FJ7.', 'student', 'default.png', 'light', '2026-06-11 05:33:02', NULL),
(20, 'Baba', 'babafake2008@gmail.com', '9704290782', '$2y$10$.fCQxp33B10Axznqz8ZFLe3WTi9Hie/5hRltuGt03EKyKugETp3Mu', 'student', 'default.png', 'light', '2026-06-11 05:33:34', NULL),
(21, 'Sravanthi', 'swifttech000@gmail.com', '8790844365', '$2y$10$Kfe1Rm8rRpEwEEyuh4Fq5upSIvmTZhyAXw1xi4ivjHsULk21snl2e', 'student', 'default.png', 'light', '2026-06-11 05:34:20', NULL),
(22, 'Ashraf Vali', 'ashraf@gmail.com', '6281025228', '$2y$10$4rxxlZrJ472wPw29.HDTEOU91Mc9dCA/ISq/MmupjUpAQqTAOqn4.', 'admin', 'default.png', 'light', '2026-06-11 05:35:29', 'ADM0002'),
(23, 'E. Rajesh', 'rajeshsir@gmail.com', '6281025228', '$2y$10$m8uALctogvVtBU7G7tF/aOat9nys35bJJ26ydR94lAWOqtyu8yUsy', 'teacher', 'default.png', 'light', '2026-06-11 05:36:14', NULL),
(24, 'Gayatri Mam', 'gayatri@gmail.com', '9704290782', '$2y$10$XlIpngx6lyvjyOFQjkI2/OVGzisYUOO7aKlqRFwuLX13.0RlBoLwm', 'teacher', 'default.png', 'light', '2026-06-11 05:36:55', NULL),
(26, 'R.Baba', 'toearnviv@gmail.com', '9704290783', '$2y$10$CgTFCNe2TTPjIbKRofQnIuPdFlGmxXXp5yjDJIJcuOT9Tdi2gRmk2', 'admin', 'default.png', 'light', '2026-06-13 00:55:33', 'ADM0001'),
(27, 'Shaik Abdul Father ', 'rbabafakhrudhin@gmail.com', '9709238333', '$2y$10$ja3fXSPibiH4UBxmWZwyEOWRgCxPX1FTr1xdurxhdS6Lp0xEwZhL6', 'parent', 'default.png', 'light', '2026-06-19 05:54:31', ''),
(28, 'Jai Ballaya', 'driver@gmail.com', '8790844365', '$2y$10$S376JFIqmFPSCU6a0B6svOZgORL/aimhS2mlpo33Y9SAgHiTxWFma', 'driver', 'default.png', 'light', '2026-06-21 05:34:39', NULL),
(29, 'Muthu', 'driver2@gmail.com', '9704290783', '$2y$10$.McnKlrxXx.FryW7dFVRReHURJ3kOAQvdIUuW9jiFkI5YuuwCLAay', 'driver', 'default.png', 'light', '2026-06-21 15:42:29', NULL),
(30, 'R.Baba', 'teacher@gmail.com', '9100911919', '$2y$10$PrSjKr2PqrtK9lhwfFey/OfdP.RwDhWQzGloAew9yzYRPvcpvllfa', 'teacher', 'default.png', 'light', '2026-06-22 00:03:49', NULL),
(31, 'Raju', 'raju@gmail.com', '9191000000', '$2y$10$bOJJGFi4MNxO04cwJr/LL.Q3Zbf1PiPZrMrVvuPoVVLum3ux79eZi', 'student', 'default.png', 'light', '2026-06-22 00:05:33', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_permissions`
--

CREATE TABLE `user_permissions` (
  `user_permission_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `granted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD UNIQUE KEY `unique_attendance` (`class_id`,`section_id`,`attendance_date`,`attendance_mode`,`period_id`),
  ADD KEY `idx_date` (`attendance_date`),
  ADD KEY `idx_class` (`class_id`),
  ADD KEY `idx_section` (`section_id`),
  ADD KEY `idx_subject` (`subject_id`),
  ADD KEY `idx_period` (`period_id`),
  ADD KEY `idx_teacher_assignment` (`teacher_assignment_id`);

--
-- Indexes for table `attendance_records`
--
ALTER TABLE `attendance_records`
  ADD PRIMARY KEY (`record_id`),
  ADD UNIQUE KEY `unique_student_attendance` (`attendance_id`,`student_id`),
  ADD KEY `idx_student` (`student_id`),
  ADD KEY `idx_attendance` (`attendance_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`class_id`),
  ADD UNIQUE KEY `class_code` (`class_code`),
  ADD UNIQUE KEY `unique_class_code` (`class_code`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_contact_messages_created_at` (`created_at`),
  ADD KEY `idx_contact_messages_email` (`email`);

--
-- Indexes for table `exams`
--
ALTER TABLE `exams`
  ADD PRIMARY KEY (`exam_id`);

--
-- Indexes for table `exam_subjects`
--
ALTER TABLE `exam_subjects`
  ADD PRIMARY KEY (`exam_subject_id`),
  ADD KEY `exam_id` (`exam_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `fee_payments`
--
ALTER TABLE `fee_payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_fee_payments_date` (`payment_date`),
  ADD KEY `idx_fee_payments_studentfee` (`student_fee_id`);

--
-- Indexes for table `fee_structures`
--
ALTER TABLE `fee_structures`
  ADD PRIMARY KEY (`fee_structure_id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `grading_system`
--
ALTER TABLE `grading_system`
  ADD PRIMARY KEY (`grading_id`),
  ADD KEY `idx_grade_range` (`min_marks`,`max_marks`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_type_created` (`type`,`created_at`);

--
-- Indexes for table `notification_reads`
--
ALTER TABLE `notification_reads`
  ADD PRIMARY KEY (`read_id`),
  ADD UNIQUE KEY `unique_read` (`notification_id`,`user_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_notification_id` (`notification_id`);

--
-- Indexes for table `notification_targets`
--
ALTER TABLE `notification_targets`
  ADD PRIMARY KEY (`target_id`),
  ADD KEY `idx_notification_id` (`notification_id`),
  ADD KEY `idx_target_lookup` (`target_type`,`target_value`);

--
-- Indexes for table `notification_templates`
--
ALTER TABLE `notification_templates`
  ADD PRIMARY KEY (`template_id`);

--
-- Indexes for table `parents`
--
ALTER TABLE `parents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `parent_id` (`parent_id`),
  ADD UNIQUE KEY `parent_id_2` (`parent_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `periods`
--
ALTER TABLE `periods`
  ADD PRIMARY KEY (`period_id`),
  ADD UNIQUE KEY `unique_template_order` (`template_id`,`sort_order`),
  ADD KEY `idx_period_template` (`template_id`);

--
-- Indexes for table `period_templates`
--
ALTER TABLE `period_templates`
  ADD PRIMARY KEY (`template_id`),
  ADD UNIQUE KEY `template_code` (`template_code`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`permission_id`),
  ADD UNIQUE KEY `unique_module_action` (`module_key`,`action_key`),
  ADD KEY `idx_module` (`module_key`);

--
-- Indexes for table `results`
--
ALTER TABLE `results`
  ADD PRIMARY KEY (`result_id`);

--
-- Indexes for table `result_marks`
--
ALTER TABLE `result_marks`
  ADD PRIMARY KEY (`mark_id`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_permission_id`),
  ADD UNIQUE KEY `unique_role_permission` (`role`,`permission_id`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_permission` (`permission_id`);

--
-- Indexes for table `salary_payments`
--
ALTER TABLE `salary_payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `idx_salary_payments_salary` (`salary_id`),
  ADD KEY `idx_salary_payments_teacher` (`teacher_id`),
  ADD KEY `idx_salary_payments_date` (`payment_date`),
  ADD KEY `idx_salary_payments_period` (`pay_period`),
  ADD KEY `idx_salary_payments_created_by` (`created_by`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`section_id`),
  ADD UNIQUE KEY `unique_section_per_class` (`class_id`,`section_code`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`),
  ADD UNIQUE KEY `student_id_2` (`student_id`),
  ADD UNIQUE KEY `student_id_3` (`student_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `student_fees`
--
ALTER TABLE `student_fees`
  ADD PRIMARY KEY (`student_fee_id`),
  ADD KEY `fee_structure_id` (`fee_structure_id`),
  ADD KEY `idx_student_fees_student` (`student_id`),
  ADD KEY `idx_student_fees_status` (`payment_status`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`subject_id`),
  ADD UNIQUE KEY `subject_code` (`subject_code`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `teacher_id` (`teacher_id`),
  ADD UNIQUE KEY `teacher_id_2` (`teacher_id`),
  ADD UNIQUE KEY `teacher_id_3` (`teacher_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `teacher_assignments`
--
ALTER TABLE `teacher_assignments`
  ADD PRIMARY KEY (`assignment_id`),
  ADD KEY `idx_assignment_lookup` (`teacher_id`,`class_id`,`section_id`,`subject_id`);

--
-- Indexes for table `teacher_attendance`
--
ALTER TABLE `teacher_attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD UNIQUE KEY `teacher_id` (`teacher_id`,`attendance_date`),
  ADD KEY `idx_teacher_created` (`created_at`),
  ADD KEY `idx_teacher_date` (`teacher_id`,`attendance_date`),
  ADD KEY `idx_attendance_date` (`attendance_date`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `teacher_salaries`
--
ALTER TABLE `teacher_salaries`
  ADD PRIMARY KEY (`salary_id`),
  ADD KEY `idx_teacher_salaries_teacher` (`teacher_id`),
  ADD KEY `idx_teacher_salaries_status` (`status`),
  ADD KEY `idx_teacher_salaries_created_by` (`created_by`);

--
-- Indexes for table `timetables`
--
ALTER TABLE `timetables`
  ADD PRIMARY KEY (`timetable_id`),
  ADD UNIQUE KEY `unique_class_section` (`class_id`,`section_id`);

--
-- Indexes for table `timetable_entries`
--
ALTER TABLE `timetable_entries`
  ADD PRIMARY KEY (`entry_id`);

--
-- Indexes for table `transport_buses`
--
ALTER TABLE `transport_buses`
  ADD PRIMARY KEY (`bus_id`),
  ADD KEY `driver_id` (`driver_id`),
  ADD KEY `helper_id` (`helper_id`);

--
-- Indexes for table `transport_bus_routes`
--
ALTER TABLE `transport_bus_routes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bus_id` (`bus_id`),
  ADD KEY `route_id` (`route_id`);

--
-- Indexes for table `transport_live_location`
--
ALTER TABLE `transport_live_location`
  ADD PRIMARY KEY (`location_id`),
  ADD UNIQUE KEY `bus_id` (`bus_id`);

--
-- Indexes for table `transport_routes`
--
ALTER TABLE `transport_routes`
  ADD PRIMARY KEY (`route_id`),
  ADD UNIQUE KEY `bus_id` (`bus_id`),
  ADD UNIQUE KEY `bus_id_2` (`bus_id`);

--
-- Indexes for table `transport_staff`
--
ALTER TABLE `transport_staff`
  ADD PRIMARY KEY (`staff_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `transport_stops`
--
ALTER TABLE `transport_stops`
  ADD PRIMARY KEY (`stop_id`),
  ADD KEY `fk_route_stop` (`route_id`);

--
-- Indexes for table `transport_student_assignments`
--
ALTER TABLE `transport_student_assignments`
  ADD PRIMARY KEY (`assignment_id`),
  ADD KEY `stop_id` (`stop_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD PRIMARY KEY (`user_permission_id`),
  ADD UNIQUE KEY `unique_user_permission` (`user_id`,`permission_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_permission` (`permission_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `attendance_records`
--
ALTER TABLE `attendance_records`
  MODIFY `record_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `class_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `exams`
--
ALTER TABLE `exams`
  MODIFY `exam_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `exam_subjects`
--
ALTER TABLE `exam_subjects`
  MODIFY `exam_subject_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fee_payments`
--
ALTER TABLE `fee_payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `fee_structures`
--
ALTER TABLE `fee_structures`
  MODIFY `fee_structure_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `grading_system`
--
ALTER TABLE `grading_system`
  MODIFY `grading_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `notification_reads`
--
ALTER TABLE `notification_reads`
  MODIFY `read_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `notification_targets`
--
ALTER TABLE `notification_targets`
  MODIFY `target_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `notification_templates`
--
ALTER TABLE `notification_templates`
  MODIFY `template_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `parents`
--
ALTER TABLE `parents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `periods`
--
ALTER TABLE `periods`
  MODIFY `period_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `period_templates`
--
ALTER TABLE `period_templates`
  MODIFY `template_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `permission_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=134;

--
-- AUTO_INCREMENT for table `results`
--
ALTER TABLE `results`
  MODIFY `result_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `result_marks`
--
ALTER TABLE `result_marks`
  MODIFY `mark_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `role_permission_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1064;

--
-- AUTO_INCREMENT for table `salary_payments`
--
ALTER TABLE `salary_payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `section_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `student_fees`
--
ALTER TABLE `student_fees`
  MODIFY `student_fee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `subject_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `teacher_assignments`
--
ALTER TABLE `teacher_assignments`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `teacher_attendance`
--
ALTER TABLE `teacher_attendance`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `teacher_salaries`
--
ALTER TABLE `teacher_salaries`
  MODIFY `salary_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `timetables`
--
ALTER TABLE `timetables`
  MODIFY `timetable_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `timetable_entries`
--
ALTER TABLE `timetable_entries`
  MODIFY `entry_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `transport_buses`
--
ALTER TABLE `transport_buses`
  MODIFY `bus_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `transport_bus_routes`
--
ALTER TABLE `transport_bus_routes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transport_live_location`
--
ALTER TABLE `transport_live_location`
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `transport_routes`
--
ALTER TABLE `transport_routes`
  MODIFY `route_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `transport_staff`
--
ALTER TABLE `transport_staff`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `transport_stops`
--
ALTER TABLE `transport_stops`
  MODIFY `stop_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `transport_student_assignments`
--
ALTER TABLE `transport_student_assignments`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `user_permissions`
--
ALTER TABLE `user_permissions`
  MODIFY `user_permission_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `fk_attendance_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`),
  ADD CONSTRAINT `fk_attendance_period` FOREIGN KEY (`period_id`) REFERENCES `periods` (`period_id`),
  ADD CONSTRAINT `fk_attendance_section` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`),
  ADD CONSTRAINT `fk_attendance_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`);

--
-- Constraints for table `attendance_records`
--
ALTER TABLE `attendance_records`
  ADD CONSTRAINT `attendance_records_ibfk_1` FOREIGN KEY (`attendance_id`) REFERENCES `attendance` (`attendance_id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_subjects`
--
ALTER TABLE `exam_subjects`
  ADD CONSTRAINT `exam_subjects_ibfk_1` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`exam_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_subjects_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) ON DELETE CASCADE;

--
-- Constraints for table `fee_payments`
--
ALTER TABLE `fee_payments`
  ADD CONSTRAINT `fee_payments_ibfk_1` FOREIGN KEY (`student_fee_id`) REFERENCES `student_fees` (`student_fee_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fee_payments_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `fee_structures`
--
ALTER TABLE `fee_structures`
  ADD CONSTRAINT `fee_structures_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE;

--
-- Constraints for table `parents`
--
ALTER TABLE `parents`
  ADD CONSTRAINT `parents_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `periods`
--
ALTER TABLE `periods`
  ADD CONSTRAINT `periods_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `period_templates` (`template_id`) ON DELETE CASCADE;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `fk_role_permissions_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`permission_id`) ON DELETE CASCADE;

--
-- Constraints for table `sections`
--
ALTER TABLE `sections`
  ADD CONSTRAINT `sections_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_fees`
--
ALTER TABLE `student_fees`
  ADD CONSTRAINT `student_fees_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_fees_ibfk_2` FOREIGN KEY (`fee_structure_id`) REFERENCES `fee_structures` (`fee_structure_id`) ON DELETE CASCADE;

--
-- Constraints for table `teachers`
--
ALTER TABLE `teachers`
  ADD CONSTRAINT `teachers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `teacher_attendance`
--
ALTER TABLE `teacher_attendance`
  ADD CONSTRAINT `fk_teacher_attendance_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transport_buses`
--
ALTER TABLE `transport_buses`
  ADD CONSTRAINT `transport_buses_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `transport_staff` (`staff_id`),
  ADD CONSTRAINT `transport_buses_ibfk_2` FOREIGN KEY (`helper_id`) REFERENCES `transport_staff` (`staff_id`);

--
-- Constraints for table `transport_bus_routes`
--
ALTER TABLE `transport_bus_routes`
  ADD CONSTRAINT `transport_bus_routes_ibfk_1` FOREIGN KEY (`bus_id`) REFERENCES `transport_buses` (`bus_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transport_bus_routes_ibfk_2` FOREIGN KEY (`route_id`) REFERENCES `transport_routes` (`route_id`) ON DELETE CASCADE;

--
-- Constraints for table `transport_live_location`
--
ALTER TABLE `transport_live_location`
  ADD CONSTRAINT `fk_live_bus` FOREIGN KEY (`bus_id`) REFERENCES `transport_buses` (`bus_id`) ON DELETE CASCADE;

--
-- Constraints for table `transport_routes`
--
ALTER TABLE `transport_routes`
  ADD CONSTRAINT `transport_routes_ibfk_1` FOREIGN KEY (`bus_id`) REFERENCES `transport_buses` (`bus_id`) ON DELETE CASCADE;

--
-- Constraints for table `transport_staff`
--
ALTER TABLE `transport_staff`
  ADD CONSTRAINT `fk_transport_staff_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `transport_staff_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `transport_stops`
--
ALTER TABLE `transport_stops`
  ADD CONSTRAINT `fk_route_stop` FOREIGN KEY (`route_id`) REFERENCES `transport_routes` (`route_id`) ON DELETE CASCADE;

--
-- Constraints for table `transport_student_assignments`
--
ALTER TABLE `transport_student_assignments`
  ADD CONSTRAINT `transport_student_assignments_ibfk_1` FOREIGN KEY (`stop_id`) REFERENCES `transport_stops` (`stop_id`) ON DELETE SET NULL;

--
-- Constraints for table `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD CONSTRAINT `fk_user_permissions_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`permission_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user_permissions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
