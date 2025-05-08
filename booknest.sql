-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 09, 2025 at 10:13 PM
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
-- Database: `booknest`
--

-- --------------------------------------------------------

--
-- Table structure for table `authors`
--

CREATE TABLE `authors` (
  `author_id` int(11) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `bio` text DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `authors`
--

INSERT INTO `authors` (`author_id`, `first_name`, `last_name`, `bio`, `profile_image`, `created_at`) VALUES
(1, 'J.K.', 'Rowling', 'British author, best known for the Harry Potter series.', NULL, '2025-04-09 17:40:49'),
(2, 'George', 'Orwell', 'English novelist and essayist, known for his works like 1984 and Animal Farm.', NULL, '2025-04-09 17:40:49'),
(3, 'J.R.R.', 'Tolkien', 'English writer, famous for The Lord of the Rings series.', NULL, '2025-04-09 17:40:49'),
(4, 'Agatha', 'Christie', 'English writer, known for her detective novels featuring Hercule Poirot and Miss Marple.', NULL, '2025-04-09 17:40:49'),
(5, 'Stephen', 'King', 'American author, known for horror, supernatural fiction, and suspense novels.', NULL, '2025-04-09 17:40:49'),
(6, 'fadfasdf', 'adfasdf', 'asdfasdf', NULL, '2025-04-09 19:34:17'),
(7, 'fadfasdf', 'adfasdf', 'asdfasdf', NULL, '2025-04-09 19:58:43');

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `book_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author_id` int(11) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `publish_date` date DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `stock_quantity` int(11) NOT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `isbn` varchar(13) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--      

INSERT INTO `books` (`book_id`, `title`, `author_id`, `price`, `description`, `publish_date`, `category_id`, `stock_quantity`, `cover_image`, `isbn`, `created_at`, `updated_at`) VALUES
(1, 'Harry Potter and the Sorcerer\'s Stone', 1, 19.99, 'A young boy discovers he is a wizard and embarks on a magical journey.', '1997-06-26', 4, 100, 'harry_potter_cover.jpg', '9780747532699', '2025-04-09 17:42:28', '2025-04-09 17:42:28'),
(2, '1984', 2, 15.99, 'A dystopian novel that critiques totalitarian regimes and the loss of individual freedom.', '1949-06-08', 3, 200, '1984_cover.jpg', '9780451524935', '2025-04-09 17:42:28', '2025-04-09 17:42:28'),
(3, 'The Hobbit', 3, 12.99, 'Bilbo Baggins embarks on an adventure to reclaim a treasure guarded by a dragon.', '1937-09-21', 4, 150, 'hobbit_cover.jpg', '9780618260300', '2025-04-09 17:42:28', '2025-04-09 17:42:28'),
(4, 'Murder on the Orient Express', 4, 10.99, 'A classic detective novel featuring Hercule Poirot solving a murder mystery.', '1934-01-01', 3, 75, 'murder_orient_cover.jpg', '9780062693662', '2025-04-09 17:42:28', '2025-04-09 17:42:28'),
(5, 'The Shining', 5, 14.99, 'A man’s descent into madness while isolated in a haunted hotel.', '1977-01-28', 5, 120, 'shining_cover.jpg', '9780307743657', '2025-04-09 17:42:28', '2025-04-09 17:42:28'),
(6, 'The Catcher in the Rye', 2, 13.99, 'A novel about the confusion and alienation of a young boy in a big city.', '1951-07-16', 1, 80, 'catcher_rye_cover.jpg', '9780316769488', '2025-04-09 17:42:28', '2025-04-09 17:42:28'),
(7, 'To Kill a Mockingbird', 1, 16.99, 'A compelling story about racial injustice and the loss of innocence in the South.', '1960-07-11', 1, 150, 'mockingbird_cover.jpg', '9780061120084', '2025-04-09 17:42:28', '2025-04-09 17:42:28'),
(8, 'Pride and Prejudice', 3, 11.99, 'A romantic novel that critiques the British class system in the 19th century.', '1813-01-28', 1, 200, 'pride_prejudice_cover.jpg', '9781503290563', '2025-04-09 17:42:28', '2025-04-09 17:42:28'),
(9, 'The Lord of the Rings', 3, 29.99, 'An epic fantasy adventure following the quest to destroy the One Ring.', '1954-07-29', 4, 120, 'lotr_cover.jpg', '9780261102385', '2025-04-09 17:42:28', '2025-04-09 17:42:28'),
(10, 'The Great Gatsby', 2, 18.99, 'A novel about the American Dream, wealth, and the disillusionment of the Jazz Age.', '1925-04-10', 1, 90, 'gatsby_cover.jpg', '9780743273565', '2025-04-09 17:42:28', '2025-04-09 17:42:28'),
(11, 'Brave New World', 2, 16.50, 'A dystopian society where technology and consumerism reign over human life.', '1932-08-30', 3, 200, 'brave_new_world_cover.jpg', '9780060850524', '2025-04-09 17:42:28', '2025-04-09 17:42:28'),
(12, 'The Chronicles of Narnia', 3, 25.99, 'A series of seven fantasy novels about a magical world called Narnia.', '1950-10-16', 4, 150, 'narnia_cover.jpg', '9780064471190', '2025-04-09 17:42:28', '2025-04-09 17:42:28'),
(13, 'The Fault in Our Stars', 5, 13.99, 'A poignant love story between two teenagers battling cancer.', '2012-01-10', 1, 220, 'fault_in_our_stars_cover.jpg', '9780525478812', '2025-04-09 17:42:28', '2025-04-09 17:42:28'),
(14, 'The Alchemist', 5, 16.00, 'A young shepherd embarks on a journey to fulfill his personal legend and find treasure.', '1988-11-01', 1, 140, 'alchemist_cover.jpg', '9780061122415', '2025-04-09 17:42:28', '2025-04-09 17:42:28'),
(15, 'The Hunger Games', 4, 12.50, 'A dystopian story where a young girl fights for survival in a post-apocalyptic world.', '2008-09-14', 3, 200, 'hunger_games_cover.jpg', '9780439023481', '2025-04-09 17:42:28', '2025-04-09 17:42:28'),
(16, 'The Da Vinci Code', 4, 17.99, 'A gripping thriller about religious secrets and conspiracy theories.', '2003-03-18', 3, 100, 'davinci_code_cover.jpg', '9780307474278', '2025-04-09 17:42:28', '2025-04-09 17:42:28'),
(17, 'A Game of Thrones', 3, 22.99, 'A fantasy epic of political intrigue, war, and betrayal in a medieval world.', '1996-08-06', 4, 150, 'game_of_thrones_cover.jpg', '9780553593716', '2025-04-09 17:42:28', '2025-04-09 17:42:28'),
(18, 'Wuthering Heights', 2, 11.00, 'A dark and passionate love story set on the English moors.', '1847-12-01', 1, 120, 'wuthering_heights_cover.jpg', '9780141439556', '2025-04-09 17:42:28', '2025-04-09 17:42:28'),
(19, 'The Road', 5, 14.99, 'A father and son struggle to survive in a post-apocalyptic world.', '2006-09-26', 3, 90, 'the_road_cover.jpg', '9780307387899', '2025-04-09 17:42:28', '2025-04-09 17:42:28'),
(20, 'The Picture of Dorian Gray', 2, 12.99, 'A young man makes a pact to remain eternally youthful, while his portrait ages.', '1890-07-01', 1, 200, 'dorian_gray_cover.jpg', '9780141439570', '2025-04-09 17:42:28', '2025-04-09 17:42:28'),
(21, 'The Handmaid\'s Tale', 5, 15.99, 'A dystopian novel set in a theocratic society that controls women\'s rights.', '1985-01-01', 3, 80, 'handmaids_tale_cover.jpg', '9780385490818', '2025-04-09 17:42:28', '2025-04-09 17:42:28'),
(22, 'The Outsiders', 4, 9.99, 'A group of teenagers struggle with class conflicts and identity in 1960s America.', '1967-04-24', 1, 150, 'outsiders_cover.jpg', '9780142407332', '2025-04-09 17:42:28', '2025-04-09 17:42:28'),
(23, 'Catch-22', 2, 14.50, 'A satirical novel about the absurdity of war and bureaucracy.', '1961-11-10', 3, 180, 'catch22_cover.jpg', '9781451626650', '2025-04-09 17:42:28', '2025-04-09 17:42:28'),
(24, 'One Hundred Years of Solitude', 5, 17.00, 'A multi-generational story set in a mythical Latin American town.', '1967-06-05', 1, 140, 'solitude_cover.jpg', '9780060883287', '2025-04-09 17:42:28', '2025-04-09 17:42:28'),
(25, 'Animal Farm', 2, 9.99, 'A political allegory of the Russian Revolution and the rise of totalitarianism.', '1945-08-17', 3, 200, 'animal_farm_cover.jpg', '9780451526342', '2025-04-09 17:42:28', '2025-04-09 17:42:28'),
(26, 'Lord of the Flies', 5, 13.50, 'A novel about a group of boys stranded on an island who descend into savagery.', '1954-09-17', 1, 100, 'lord_of_the_flies_cover.jpg', '9780399501487', '2025-04-09 17:42:28', '2025-04-09 17:42:28'),
(27, 'The Secret Garden', 4, 11.50, 'A young girl discovers the healing power of a hidden garden.', '1911-08-05', 1, 180, 'secret_garden_cover.jpg', '9780064401883', '2025-04-09 17:42:28', '2025-04-09 17:42:28'),
(28, 'Les Misérables', 3, 24.99, 'A historical novel about the struggles and injustices of post-revolutionary France.', '1862-01-01', 1, 70, 'les_miserables_cover.jpg', '9780140444308', '2025-04-09 17:42:28', '2025-04-09 17:42:28'),
(29, 'The Call of the Wild', 4, 10.00, 'A dog returns to the wild to embrace his destiny as a leader of a pack.', '1903-03-01', 5, 130, 'call_of_the_wild_cover.jpg', '9780451530525', '2025-04-09 17:42:28', '2025-04-09 17:42:28'),
(30, 'The Invisible Man', 2, 13.00, 'A scientist becomes invisible and struggles with his descent into madness.', '1897-03-01', 1, 110, 'invisible_man_cover.jpg', '9780141439977', '2025-04-09 17:42:28', '2025-04-09 17:42:28'),
(31, 'Dracula', 5, 14.99, 'A Gothic horror novel about the infamous vampire Count Dracula.', '1897-05-26', 1, 200, 'dracula_cover.jpg', '9780141439847', '2025-04-09 17:42:28', '2025-04-09 17:42:28'),
(32, 'Frankenstein', 4, 12.00, 'A scientist creates a creature who becomes his worst nightmare.', '1818-01-01', 5, 160, 'frankenstein_cover.jpg', '9780486282114', '2025-04-09 17:42:28', '2025-04-09 17:42:28'),
(33, 'The Brothers Karamazov', 3, 19.50, 'A philosophical novel about family, morality, and religion in 19th-century Russia.', '1880-11-01', 1, 90, 'brothers_karamazov_cover.jpg', '9780140449242', '2025-04-09 17:42:28', '2025-04-09 17:42:28'),
(34, 'Dr. Jekyll and Mr. Hyde', 4, 9.99, 'A man discovers his dark alter-ego through scientific experimentation.', '1886-01-01', 1, 160, 'jekyll_hyde_cover.jpg', '9780486266886', '2025-04-09 17:42:28', '2025-04-09 17:42:28'),
(35, 'The Time Machine', 2, 12.00, 'A scientist invents a machine that allows him to travel through time.', '1895-01-01', 3, 110, 'time_machine_cover.jpg', '9780451530501', '2025-04-09 17:42:28', '2025-04-09 17:42:28'),
(36, 'The Sun Also Rises', 2, 16.00, 'A post-World War I novel about a group of expatriates in Europe.', '1926-10-22', 1, 100, 'sun_also_rises_cover.jpg', '9780743297332', '2025-04-09 17:42:28', '2025-04-09 17:42:28'),
(37, 'Gone with the Wind', 5, 18.00, 'A sweeping historical novel set during the American Civil War and Reconstruction.', '1936-06-30', 1, 120, 'gone_with_the_wind_cover.jpg', '9781416548942', '2025-04-09 17:42:28', '2025-04-09 17:42:28'),
(38, 'The Kite Runner', 1, 16.99, 'A story of friendship and redemption set in Afghanistan during the 1970s and 1980s.', '2003-05-29', 1, 90, 'kite_runner_cover.jpg', '9781594631931', '2025-04-09 17:42:28', '2025-04-09 17:42:28'),
(39, 'The Girl on the Train', 4, 14.99, 'A psychological thriller about a woman’s obsession with a married couple.', '2015-01-13', 3, 180, 'girl_on_the_train_cover.jpg', '9781594633669', '2025-04-09 17:42:28', '2025-04-09 17:42:28'),
;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`, `description`, `created_at`) VALUES
(1, 'Fiction', 'Novels and stories that are based on imaginative narration.', '2025-04-09 17:41:04'),
(2, 'Science Fiction', 'Books based on speculative scientific discoveries and futuristic concepts.', '2025-04-09 17:41:04'),
(3, 'Mystery', 'Books involving suspenseful events and detective work to solve puzzles or crimes.', '2025-04-09 17:41:04'),
(4, 'Fantasy', 'Books involving magical elements, supernatural beings, and imaginary worlds.', '2025-04-09 17:41:04'),
(5, 'Horror', 'Books that evoke fear, horror, and suspense, often with supernatural elements.', '2025-04-09 17:41:04');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `first_name`, `last_name`, `email`, `password_hash`, `phone`, `address`, `created_at`) VALUES
(1, 'John', 'Doe', 'john.doe@example.com', 'hashedpassword123', '555-1234', '123 Elm St, Springfield, IL', '2025-04-09 17:41:16'),
(2, 'Jane', 'Smith', 'jane.smith@example.com', 'hashedpassword456', '555-5678', '456 Oak St, Riverton, NJ', '2025-04-09 17:41:16'),
(3, 'Alice', 'Johnson', 'alice.johnson@example.com', 'hashedpassword789', '555-8765', '789 Pine St, Westfield, NJ', '2025-04-09 17:41:16'),
(4, 'Bob', 'Brown', 'bob.brown@example.com', 'hashedpassword101', '555-4321', '321 Maple St, Tallahassee, FL', '2025-04-09 17:41:16'),
(5, 'Mary', 'Davis', 'mary.davis@example.com', 'hashedpassword202', '555-9876', '654 Birch St, Orlando, FL', '2025-04-09 17:41:16');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `authors`
--
ALTER TABLE `authors`
  ADD PRIMARY KEY (`author_id`);

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`book_id`),
  ADD KEY `author_id` (`author_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `authors`
--
ALTER TABLE `authors`
  MODIFY `author_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `book_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `books`
--
ALTER TABLE `books`
  ADD CONSTRAINT `books_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `authors` (`author_id`),
  ADD CONSTRAINT `books_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
