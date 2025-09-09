-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 28, 2025 at 07:31 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gemcart`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'admin', '123456ab', '2025-07-24 20:51:30');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Rings'),
(2, 'Necklaces'),
(3, 'Earrings'),
(4, 'Bracelets'),
(5, 'Watches');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `date_submitted` timestamp NOT NULL DEFAULT current_timestamp(),
  `issue_type` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `user_id`, `name`, `email`, `message`, `date_submitted`, `issue_type`) VALUES
(1, 1, 'Alice', 'alice@example.com', 'Absolutely stunning ring!', '2025-07-13 05:46:39', NULL),
(2, 2, 'Bob', 'bob@example.com', 'Great quality earrings.', '2025-07-13 05:46:39', NULL),
(3, 3, 'Carol', 'carol@example.com', 'Beautiful necklace, fast shipping.', '2025-07-13 05:46:39', NULL),
(5, 6, 'john doe', 'johndoe@gmail.com', 'website not working', '2025-07-16 06:43:27', 'Order');

-- --------------------------------------------------------

--
-- Table structure for table `jewle_cat`
--

CREATE TABLE `jewle_cat` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jewle_cat`
--

INSERT INTO `jewle_cat` (`id`, `name`, `description`, `image`, `created_at`) VALUES
(1, 'Gold Jewelry', 'Timeless elegance in pure gold', 'https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=400&q=80', '2025-07-13 11:45:55'),
(2, 'Silver Jewelry', 'Modern sophistication in sterling silver', 'https://images.unsplash.com/photo-1464983953574-0892a716854b?auto=format&fit=crop&w=400&q=80', '2025-07-13 11:45:55'),
(3, 'Platinum Jewelry', 'Luxury and durability in pure platinum', 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80', '2025-07-13 11:45:55');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','cashless') NOT NULL,
  `delivery_address` text NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `payment_method`, `delivery_address`, `order_date`) VALUES
(1, 1, 3599.98, 'cash', '123 Main St, Cityville', '2025-07-13 05:46:08'),
(2, 2, 1299.99, 'cashless', '456 Oak Ave, Townsville', '2025-07-13 05:46:08');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 1, 1, 2999.99),
(2, 1, 3, 1, 599.99),
(3, 2, 5, 1, 1299.99);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT 'default.jpg',
  `category_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `image`, `category_id`, `created_at`) VALUES
(1, 'Diamond Engagement Ring', 'Beautiful 1-carat diamond engagement ring in 14k white gold setting. Perfect for that special proposal moment.', 2999.99, 'diamond-ring.jpg', 1, '2025-07-13 05:27:00'),
(2, 'Gold Wedding Band', 'Classic 18k gold wedding band with elegant finish. Comfortable fit for everyday wear.', 899.99, 'gold-band.jpg', 1, '2025-07-13 05:27:00'),
(3, 'Pearl Necklace', 'Elegant freshwater pearl necklace with 18k gold clasp. Perfect for formal occasions.', 599.99, 'pearl-necklace.jpg', 2, '2025-07-13 05:27:00'),
(4, 'Silver Chain Necklace', 'Minimalist sterling silver chain necklace. Versatile design for any outfit.', 199.99, 'silver-star-necklace.jpeg', 2, '2025-07-13 05:27:00'),
(5, 'Diamond Stud Earrings', 'Timeless 0.5-carat diamond stud earrings in white gold setting.', 1299.99, 'diamond-studs.jpg', 3, '2025-07-13 05:27:00'),
(6, 'Gold Hoop Earrings', 'Classic 14k gold hoop earrings. Perfect size for everyday elegance.', 399.99, 'gold-hoops.jpg', 3, '2025-07-13 05:27:00'),
(7, 'Tennis Bracelet', 'Stunning diamond tennis bracelet with 14k white gold setting.', 2499.99, 'tennis-bracelet.jpg', 4, '2025-07-13 05:27:00'),
(8, 'Charm Bracelet', 'Sterling silver charm bracelet with multiple charms. Personalize with your own charms.', 299.99, 'charm-bracelet.jpg', 4, '2025-07-13 05:27:00'),
(9, 'Luxury Watch', 'Premium automatic watch with leather strap. Water-resistant and elegant design.', 1899.99, 'luxury-watch.jpg', 5, '2025-07-13 05:27:00'),
(10, 'Classic Watch', 'Timeless design watch with stainless steel band. Perfect for business or casual wear.', 799.99, 'classic-watch.jpg', 5, '2025-07-13 05:27:00'),
(11, 'Sapphire Ring', 'Stunning blue sapphire ring surrounded by diamonds in white gold setting.', 1599.99, 'sapphire-ring.jpg', 1, '2025-07-13 05:27:00'),
(12, 'Emerald Necklace', 'Vintage-style emerald pendant necklace with diamond accents.', 899.99, 'emerald-necklace.jpg', 2, '2025-07-13 05:27:00'),
(13, 'Gold earrings', 'Delicate gold heart pendant with diamond accent', 649.99, 'gold-earrings-1.jpg', 1, '2025-07-13 11:49:50'),
(16, 'Gold Star Necklace', 'Gold necklace with a star-shaped pendant', 799.99, 'gold-full.jpg', 1, '2025-07-13 11:49:50'),
(17, 'Gold Infinity Ring', 'Gold ring with infinity symbol and small diamonds', 899.99, 'gold-ring-1.jpg', 1, '2025-07-13 11:49:50'),
(18, 'Silver Moon Pendant', 'Mystical silver moon pendant with crystal accent', 249.99, 'silver-dant.jpg', 2, '2025-07-13 11:49:50'),
(20, 'Silver star necklace', 'Minimalist silver bar bracelet for everyday wear', 149.99, 'silver-star-necklace.jpeg', 2, '2025-07-13 11:49:50'),
(21, 'Silver saphire chain', 'Silver necklace with a star-shaped pendant', 299.99, 'silversaphire.jpg', 2, '2025-07-13 11:49:50'),
(22, 'Silver Infinity Ring', 'Silver ring with infinity symbol and small crystals', 349.99, 'silver-infinity-ring.jpeg', 2, '2025-07-13 11:49:50'),
(23, 'Platinum Heart Pendant', 'Beautiful platinum heart pendant with diamond', 1499.99, 'platinum-heart-pendant.jpg', 3, '2025-07-13 11:49:50'),
(24, 'Platinum Bar Bracelet', 'Minimalist platinum bar bracelet for luxury wear', 1299.99, 'platinum-bar-bracelet.jpg', 3, '2025-07-13 11:49:50'),
(25, 'Platinum Leaf Earrings', 'Elegant platinum earrings shaped like leaves', 1199.99, 'platinum-leaf-earrings.jpg', 3, '2025-07-13 11:49:50'),
(26, 'Platinum Star Necklace', 'Platinum necklace with a star-shaped pendant', 1799.99, 'platinum-star-necklace.jpg', 3, '2025-07-13 11:49:50'),
(27, 'Platinum Infinity Ring', 'Platinum ring with infinity symbol and diamonds', 1999.99, 'platinum-infinity-ring.jpg', 3, '2025-07-13 11:49:50');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `created_at`) VALUES
(1, 'Alice', 'alice@example.com', 'password123', '2025-07-13 05:45:59'),
(2, 'Bob', 'bob@example.com', 'password123', '2025-07-13 05:45:59'),
(3, 'Carol', 'carol@example.com', 'password123', '2025-07-13 05:45:59'),
(5, 'dhwani', 'dhwanichavda45@gmail.com', '$2y$10$0tdEXD2KUcTUcwrZMUoN/.zoEopZdAoKV0PUsSNszFzZ7YSY9X0MO', '2025-07-13 12:17:34'),
(6, 'john doe', 'johndoe@gmail.com', '$2y$10$3E70YIlNZZE3q3BMf/qkc.cb9nU6qDXltiPzuvuFAcFQvodPitBzm', '2025-07-16 06:28:45'),
(7, 'divyesh', 'divyesh12@gmail.com', '$2y$10$ttdjwEsZAI895AahlCVzHee6AxWroE6jNiOvXZ8nBtwdL8er0l7nG', '2025-07-31 09:07:58'),
(8, 'ana jonas', 'ana12@gmail.com', '$2y$10$6WI8gesNwBT0tKNdge1.YeRTR8PdavE70fCZAEJq6NgYTqZIKauHK', '2025-08-28 08:04:40');

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
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `jewle_cat`
--
ALTER TABLE `jewle_cat`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `jewle_cat`
--
ALTER TABLE `jewle_cat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
