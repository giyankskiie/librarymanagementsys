-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 11, 2026 at 04:50 PM
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
-- Database: `library_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'admin', '$2y$10$WTydeSS9rv5AG4isXrdVUuIQ6BuSulPTJ4su8IzE.hHtOLpZ6lp7C');

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `author` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `published_year` varchar(50) DEFAULT NULL,
  `status` enum('Available','Borrowed') DEFAULT 'Available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`id`, `title`, `author`, `category`, `published_year`, `status`) VALUES
(1, 'To Kill a Mockingbird', 'Harper Lee', 'Fiction', '1960', 'Borrowed'),
(2, 'The Fault in Our Stars', 'John Green', 'Fiction', '2012', 'Borrowed'),
(3, 'The Kite Runner', 'Khaled Hosseini', 'Fiction', '2003', 'Borrowed'),
(4, 'The Book Thief', 'Markus Zusak', 'Fiction', '2005', 'Borrowed'),
(5, 'Gone Girl', 'Gillian Flynn', 'Fiction', '2012', 'Borrowed'),
(6, 'Sapiens', 'Yuval Noah Harari', 'Non-Fiction', '2011', 'Available'),
(7, 'Educated', 'Tara Westover', 'Non-Fiction', '2018', 'Available'),
(8, 'Atomic Habits', 'James Clear', 'Non-Fiction', '2018', 'Available'),
(9, 'The Psychology of Money', 'Morgan Housel', 'Non-Fiction', '2020', 'Available'),
(10, 'Thinking, Fast and Slow', 'Daniel Kahneman', 'Non-Fiction', '2011', 'Available'),
(11, 'A Brief History of Time', 'Stephen Hawking', 'Science', '1988', 'Available'),
(12, 'The Selfish Gene', 'Richard Dawkins', 'Science', '1976', 'Available'),
(13, 'Cosmos', 'Carl Sagan', 'Science', '1980', 'Available'),
(14, 'The Gene', 'Siddhartha Mukherjee', 'Science', '2016', 'Available'),
(15, 'Astrophysics for People in a Hurry', 'Neil deGrasse Tyson', 'Science', '2017', 'Available'),
(16, '1984', 'George Orwell', 'Classic', '1949', 'Available'),
(17, 'Pride and Prejudice', 'Jane Austen', 'Classic', '1813', 'Available'),
(18, 'The Great Gatsby', 'F. Scott Fitzgerald', 'Classic', '1925', 'Available'),
(19, 'Moby-Dick', 'Herman Melville', 'Classic', '1851', 'Available'),
(20, 'The Catcher in the Rye', 'J.D. Salinger', 'Classic', '1951', 'Available'),
(21, 'The Diary of a Young Girl', 'Anne Frank', 'History', '1947', 'Available'),
(22, 'Guns, Germs, and Steel', 'Jared Diamond', 'History', '1997', 'Available'),
(23, 'The Wright Brothers', 'David McCullough', 'History', '2015', 'Available'),
(24, 'Team of Rivals', 'Doris Kearns Goodwin', 'History', '2005', 'Available'),
(25, 'The Silk Roads', 'Peter Frankopan', 'History', '2015', 'Available'),
(26, 'The Hobbit', 'J.R.R. Tolkien', 'Fantasy', '1937', 'Available'),
(27, 'Harry Potter and the Sorcerer\'s Stone', 'J.K. Rowling', 'Fantasy', '1997', 'Available'),
(28, 'The Name of the Wind', 'Patrick Rothfuss', 'Fantasy', '2007', 'Available'),
(29, 'A Game of Thrones', 'George R.R. Martin', 'Fantasy', '1996', 'Available'),
(30, 'The Chronicles of Narnia', 'C.S. Lewis', 'Fantasy', '1950', 'Available');

-- --------------------------------------------------------

--
-- Table structure for table `borrow_transactions`
--

CREATE TABLE `borrow_transactions` (
  `checkout_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `student_name` varchar(100) NOT NULL,
  `borrow_date` datetime DEFAULT current_timestamp(),
  `due_date` datetime DEFAULT NULL,
  `return_date` datetime DEFAULT NULL,
  `status` enum('borrowed','pending_return','returned') DEFAULT 'borrowed'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `borrow_transactions`
--

INSERT INTO `borrow_transactions` (`checkout_id`, `book_id`, `student_id`, `student_name`, `borrow_date`, `due_date`, `return_date`, `status`) VALUES
(1, 1, 's', 's', '2026-02-11 22:38:56', '2026-02-14 15:38:56', NULL, 'pending_return'),
(2, 2, 's', 's', '2026-02-11 22:39:08', '2026-02-14 15:39:08', NULL, 'borrowed'),
(3, 3, 's', 's', '2026-02-11 22:39:12', '2026-02-14 15:39:12', NULL, 'pending_return'),
(4, 4, 's', 's', '2026-02-11 22:39:14', '2026-02-14 15:39:14', NULL, 'pending_return'),
(5, 5, 's', 's', '2026-02-11 22:39:17', '2026-02-14 15:39:17', '2026-02-11 22:40:04', 'returned'),
(6, 5, 's', 's', '2026-02-11 23:42:25', '2026-02-14 16:42:25', NULL, 'pending_return');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `role` enum('Admin','Student') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `borrow_transactions`
--
ALTER TABLE `borrow_transactions`
  ADD PRIMARY KEY (`checkout_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `borrow_transactions`
--
ALTER TABLE `borrow_transactions`
  MODIFY `checkout_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
