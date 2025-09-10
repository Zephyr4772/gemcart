-- SQL script to rename order_items table to placed_orders
-- This script should be run on your XAMPP database

-- First, create the new placed_orders table with the same structure
CREATE TABLE `placed_orders` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copy all data from the old table to the new table
INSERT INTO `placed_orders` (`id`, `order_id`, `product_id`, `quantity`, `price`)
SELECT `id`, `order_id`, `product_id`, `quantity`, `price` FROM `order_items`;

-- Add indexes to the new table
ALTER TABLE `placed_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

-- Add foreign key constraints to the new table
ALTER TABLE `placed_orders`
  ADD CONSTRAINT `placed_orders_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `placed_orders_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

-- Set auto increment to continue from the last ID
ALTER TABLE `placed_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

-- Drop the old table
DROP TABLE `order_items`;