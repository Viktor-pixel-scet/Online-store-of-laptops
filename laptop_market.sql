-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Мар 25 2025 г., 20:24
-- Версия сервера: 9.1.0
-- Версия PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `laptop_market`
--

-- --------------------------------------------------------

--
-- Структура таблицы `customers`
--

DROP TABLE IF EXISTS `customers`;
CREATE TABLE IF NOT EXISTS `customers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `customers`
--

INSERT INTO `customers` (`id`, `name`, `email`, `phone`, `address`, `created_at`) VALUES
(1, 'Віктор Малівський', 'ipz235_mva@student.ztu.edu.ua', '0986057400', 'Чуднівська 98', '2025-03-19 14:27:49'),
(2, 'Анна Лавнікович', 'Anna_08@gmail.com', '0685036636', 'Київська 98', '2025-03-19 17:57:23'),
(3, 'Віктор Малівський', 'ipz235_mva@student.ztu.edu.ua', '0986057400', 'Чуднівська 98', '2025-03-24 14:02:35'),
(4, 'Анна Лавнікович', 'Anna_08@gmail.com', '0685036636', 'Київська 98', '2025-03-24 14:54:28'),
(5, 'Віктор Малівський', 'ipz235_mva@student.ztu.edu.ua', '0986057400', 'Чуднівська 98', '2025-03-24 15:49:08'),
(6, 'Віктор Малівський', 'ipz235_mva@student.ztu.edu.ua', '0986057400', 'Чуднівська 98', '2025-03-24 16:57:47'),
(7, 'Віктор Малівський', 'ipz235_mva@student.ztu.edu.ua', '0986057400', 'Чуднівська 98', '2025-03-24 17:39:43'),
(8, 'Віктор Малівський', 'ipz235_mva@student.ztu.edu.ua', '0986057400', 'Чуднівська 98', '2025-03-24 17:47:28'),
(9, 'Віктор Малівський', 'ipz235_mva@student.ztu.edu.ua', '0986057400', 'Чуднівська 98', '2025-03-24 17:52:06'),
(10, 'Віктор Малівський', 'ipz235_mva@student.ztu.edu.ua', '0986057400', 'Чуднівська 98', '2025-03-24 18:02:13'),
(11, 'Віктор Малівський', 'ipz235_mva@student.ztu.edu.ua', '0986057400', 'Чуднівська 98', '2025-03-24 19:14:10'),
(12, 'Віктор Малівський', 'ipz235_mva@student.ztu.edu.ua', '0986057400', 'Чуднівська 98', '2025-03-24 19:18:57'),
(13, 'Віктор Малівський', 'ipz235_mva@student.ztu.edu.ua', '0986057400', 'Чуднівська 98', '2025-03-24 19:20:44'),
(14, 'Анна Лавнікович', 'Anna_08@gmail.com', '0685036636', 'Київська 98', '2025-03-24 19:21:32'),
(15, 'Віктор Малівський', 'ipz235_mva@student.ztu.edu.ua', '0986057400', 'Чуднівська 98', '2025-03-24 20:21:45'),
(16, 'Віктор Малівський', 'ipz235_mva@student.ztu.edu.ua', '0986057400', 'Чуднівська 98', '2025-03-24 20:28:54'),
(17, 'Аннф Лавнікович', 'Anna_08@gmail.com', '0685036636', 'Київська 98', '2025-03-25 14:52:59'),
(18, 'Віктор Малівський', 'ipz235_mva@student.ztu.edu.ua', '0986057400', 'Чуднівська 98', '2025-03-25 15:18:41'),
(19, 'Віктор Малівський', 'ipz235_mva@student.ztu.edu.ua', '0986057400', 'Чуднівська 98', '2025-03-25 15:37:11'),
(20, 'Віктор Малівський', 'ipz235_mva@student.ztu.edu.ua', '0986057400', 'Чуднівська 98', '2025-03-25 18:23:54');

-- --------------------------------------------------------

--
-- Структура таблицы `games`
--

DROP TABLE IF EXISTS `games`;
CREATE TABLE IF NOT EXISTS `games` (
  `id` int NOT NULL AUTO_INCREMENT,
  `game_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `game_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `min_fps` int NOT NULL,
  `max_fps` int NOT NULL,
  `category` enum('Shooter','RPG','Competitive') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `game_code` (`game_code`)
) ENGINE=MyISAM AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `games`
--

INSERT INTO `games` (`id`, `game_code`, `game_name`, `min_fps`, `max_fps`, `category`) VALUES
(1, 'csgo', 'CS:GO', 120, 300, 'Shooter'),
(2, 'cyberpunk', 'Cyberpunk 2077', 40, 120, 'RPG'),
(3, 'valorant', 'Valorant', 100, 240, 'Competitive'),
(4, 'gta5', 'Grand Theft Auto V', 50, 150, ''),
(5, 'witcher3', 'The Witcher 3: Wild Hunt', 45, 120, 'RPG'),
(6, 'fortnite', 'Fortnite', 80, 240, ''),
(7, 'apex', 'Apex Legends', 60, 180, ''),
(8, 'minecraft', 'Minecraft', 150, 600, ''),
(9, 'rdr2', 'Red Dead Redemption 2', 35, 100, ''),
(10, 'doom', 'DOOM Eternal', 120, 300, 'Shooter'),
(11, 'lol', 'League of Legends', 100, 300, ''),
(12, 'dota2', 'Dota 2', 80, 240, ''),
(13, 'overwatch', 'Overwatch 2', 90, 240, 'Shooter'),
(14, 'bf2042', 'Battlefield 2042', 50, 144, 'Shooter'),
(15, 'codmw', 'Call of Duty: Modern Warfare II', 60, 180, 'Shooter'),
(16, 'farcry6', 'Far Cry 6', 50, 144, ''),
(17, 'pubg', 'PUBG: Battlegrounds', 60, 144, ''),
(18, 'fallout4', 'Fallout 4', 40, 120, 'RPG'),
(19, 'eldenring', 'Elden Ring', 50, 140, 'RPG'),
(20, 'fifa23', 'FIFA 23', 60, 144, ''),
(21, 'nba2k23', 'NBA 2K23', 60, 144, ''),
(22, 'msfs', 'Microsoft Flight Simulator', 30, 90, ''),
(23, 'civ6', 'Civilization VI', 60, 120, ''),
(24, 'starcraft2', 'StarCraft II', 80, 200, ''),
(25, 'darkestdungeon', 'Darkest Dungeon', 60, 120, ''),
(26, 'subnautica', 'Subnautica', 50, 100, ''),
(27, 'tarkov', 'Escape from Tarkov', 45, 120, 'Shooter');

-- --------------------------------------------------------

--
-- Структура таблицы `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `order_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','card') COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `shipping_address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `customer_id` (`customer_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `orders`
--

INSERT INTO `orders` (`id`, `customer_id`, `order_number`, `total_amount`, `payment_method`, `status`, `shipping_address`, `created_at`, `updated_at`) VALUES
(1, 13, 'ORDER-20250324192044-3117', 35999.00, 'cash', 'pending', 'Чуднівська 98', '2025-03-24 19:20:44', '2025-03-24 19:20:44'),
(2, 14, 'ORDER-20250324192132-5180', 117131.00, 'cash', 'pending', '', '2025-03-24 19:21:32', '2025-03-24 19:21:32'),
(3, 15, 'ORDER-20250324202145-5824', 81131.00, 'cash', 'pending', '', '2025-03-24 20:21:45', '2025-03-24 20:21:45'),
(4, 16, 'ORDER-20250324202854-6046', 104534.00, 'cash', 'pending', '', '2025-03-24 20:28:54', '2025-03-24 20:28:54'),
(5, 17, 'ORDER-20250325145259-5054', 35999.00, 'cash', 'pending', '', '2025-03-25 14:52:59', '2025-03-25 14:52:59'),
(6, 18, 'ORDER-20250325151841-9757', 107997.00, 'cash', 'pending', '', '2025-03-25 15:18:41', '2025-03-25 15:18:41'),
(7, 19, 'ORDER-20250325153711-3253', 35999.00, 'cash', 'pending', '', '2025-03-25 15:37:11', '2025-03-25 15:37:11'),
(8, 20, 'ORDER-20250325182354-8944', 35999.00, 'cash', 'pending', '', '2025-03-25 18:23:54', '2025-03-25 18:23:54');

-- --------------------------------------------------------

--
-- Структура таблицы `order_attempts`
--

DROP TABLE IF EXISTS `order_attempts`;
CREATE TABLE IF NOT EXISTS `order_attempts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('success','failed') COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_order_attempts_email` (`email`(250))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 1, 1, 35999.00),
(2, 2, 3, 1, 36000.00),
(3, 2, 4, 1, 81131.00),
(4, 3, 4, 1, 81131.00),
(5, 4, 3, 1, 36000.00),
(6, 4, 2, 1, 68534.00),
(7, 5, 1, 1, 35999.00),
(8, 6, 1, 3, 35999.00),
(9, 7, 1, 1, 35999.00),
(10, 8, 1, 1, 35999.00);

-- --------------------------------------------------------

--
-- Структура таблицы `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `full_description` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stock` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `screen_size` decimal(4,1) DEFAULT NULL COMMENT 'Діагональ екрану в дюймах',
  `video_card_type` enum('Integrated','Discrete') COLLATE utf8mb4_unicode_ci DEFAULT 'Integrated' COMMENT 'Тип відеокарти',
  `storage_type` enum('HDD','SSD','SSD+HDD') COLLATE utf8mb4_unicode_ci DEFAULT 'SSD' COMMENT 'Тип накопичувача',
  `device_weight` decimal(4,2) DEFAULT NULL COMMENT 'Вага пристрою в кг',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `description`, `full_description`, `image`, `stock`, `created_at`, `updated_at`, `screen_size`, `video_card_type`, `storage_type`, `device_weight`) VALUES
(1, 'Ноутбук ASUS TUF Gaming F15', 35999.00, 'Ігровий ноутбук з процесором Intel Core i5-11400H та відеокартою NVIDIA GeForce RTX 3050', 'Ноутбук ASUS TUF Gaming F15 оснащений 15.6-дюймовим IPS-екраном з роздільною здатністю Full HD (1920x1080) та частотою оновлення 144 Гц. Під капотом встановлений 6-ядерний процесор Intel Core i5-11400H, 16 ГБ оперативної пам\'яті DDR4 та SSD-накопичувач на 512 ГБ. За графіку відповідає дискретна відеокарта NVIDIA GeForce RTX 3050 з 4 ГБ відеопам\'яті. Ноутбук має підсвічування клавіатури RGB, веб-камеру HD 720p, стереодинаміки з підтримкою DTS:X Ultra, модулі Wi-Fi 6 та Bluetooth 5.2, а також багатий набір портів (USB 3.2 Gen 1 Type-A, USB 3.2 Gen 2 Type-C, HDMI 2.0, RJ-45). Працює на операційній системі Windows 11.', 'https://files.foxtrot.com.ua/PhotoNew/img_0_58_27465_0_1_GnLWYZ.webp\r\n\r\nhttps://files.foxtrot.com.ua/PhotoNew/img_0_58_27465_0_1_mImOvb.webp', 5, '2025-03-19 14:06:35', '2025-03-25 18:23:54', 15.6, 'Discrete', 'SSD', 1.80),
(2, 'Ноутбук Lenovo IdeaPad Gaming 3', 68534.00, 'Потужний ігровий ноутбук з процесором AMD Ryzen 5 5600H та відеокартою NVIDIA GeForce RTX 3050 Ti', 'Ноутбук Lenovo IdeaPad Gaming 3 оснащений 15.6-дюймовим IPS-екраном з роздільною здатністю Full HD (1920x1080) та частотою оновлення 120 Гц. Працює на базі 6-ядерного процесора AMD Ryzen 5 5600H, має 16 ГБ оперативної пам\'яті DDR4 3200 МГц та SSD-накопичувач PCIe на 512 ГБ. Графічний адаптер - NVIDIA GeForce RTX 3050 Ti з 4 ГБ відеопам\'яті GDDR6. Ноутбук оснащений клавіатурою з білим підсвічуванням, стереодинаміками з підтримкою Nahimic Audio, модулями Wi-Fi 6 та Bluetooth 5.1, веб-камерою HD 720p з приватним затвором та батареєю на 45 Вт·год, яка забезпечує до 8 годин автономної роботи. Працює на операційній системі Windows 11 Home.', 'https://files.foxtrot.com.ua/PhotoNew/img_0_58_26296_0_1_sg0lqq.webp', 3, '2025-03-19 14:06:35', '2025-03-25 19:17:26', 15.6, 'Discrete', 'SSD', 1.80),
(3, 'Ноутбук Dell G3 3500', 36000.00, 'Ігровий ноутбук з процесором Intel Core i7-10750H та відеокартою NVIDIA GeForce GTX 1650 ti', 'Ноутбук Dell G3 3500 оснащений 15.6-дюймовим IPS-екраном з роздільною здатністю Full HD (1920x1080) та частотою оновлення 120 Гц. Працює на базі 6-ядерного процесора Intel Core i7-10750H з тактовою частотою до 5.0 ГГц в режимі Turbo Boost. Має 16 ГБ оперативної пам\'яті DDR4 2933 МГц та комбіновану систему зберігання даних: SSD-накопичувач PCIe NVMe на 512 ГБ та жорсткий диск на 1 ТБ. Графічний адаптер - NVIDIA GeForce GTX 1650 з 4 ГБ відеопам\'яті GDDR6. Ноутбук має двозонне підсвічування клавіатури, стереодинаміки з підтримкою Nahimic 3D Audio, модулі Wi-Fi 6 та Bluetooth 5.1, веб-камеру HD 720p та порти USB 3.2 Gen 1 Type-A, USB 3.2 Gen 2 Type-C з DisplayPort, HDMI 2.0, RJ-45, комбінований аудіороз\'єм. Працює на операційній системі Windows 11 Home.', 'https://pc.com.ua/tmp/images/products/10672/92848577373533-590x690-f.webp\r\n\r\nhttps://files.foxtrot.com.ua/StaticContent/ext/Dell/Dell_Gseries3_3500_DT1.jpg', 8, '2025-03-19 14:06:35', '2025-03-25 13:07:01', 15.6, 'Discrete', 'SSD', 1.80),
(23, 'Lenovo Legion 5', 59999.00, 'Потужний ігровий ноутбук', 'Lenovo Legion 5 з 15.6-дюймовим екраном, процесором AMD Ryzen 7, дискретною відеокартою та SSD.', 'https://content1.rozetka.com.ua/goods/images/big/492475969.jpg', 8, '2025-03-25 19:16:55', '2025-03-25 19:53:45', 15.6, 'Discrete', 'SSD', 2.40),
(22, 'Dell XPS 13', 58999.00, 'Преміальний компактний ноутбук', 'Dell XPS 13 з інноваційним дизайном, 13.3-дюймовим екраном, процесором Intel Core i7 та SSD накопичувачем.', 'https://content1.rozetka.com.ua/goods/images/big/453789802.jpg', 6, '2025-03-25 19:16:55', '2025-03-25 19:54:22', 13.3, 'Integrated', 'SSD', 1.30),
(21, 'Gigabyte Aero 16', 79999.00, 'Професійний ноутбук для креативців', 'Потужний ноутбук з 16-дюймовим OLED екраном, процесором Intel Core i9, дискретною відеокартою NVIDIA RTX та SSD.', 'https://content1.rozetka.com.ua/goods/images/big/464649290.jpg', 2, '2025-03-25 19:16:55', '2025-03-25 19:55:50', 16.0, 'Discrete', 'SSD', 2.10),
(20, 'MSI Modern 15', 29999.00, 'Бюджетний ноутбук для офісної роботи', 'Компактний ноутбук з 15.6-дюймовим екраном, процесором Intel Core i3, вбудованою графікою та SSD накопичувачем.', 'https://content1.rozetka.com.ua/goods/images/big/496816803.jpg', 15, '2025-03-25 19:16:55', '2025-03-25 19:57:26', 15.6, 'Integrated', 'SSD', 1.70),
(19, 'Asus ROG Strix G15', 69999.00, 'Потужний ігровий ноутбук', 'Asus ROG Strix G15 з процесором AMD Ryzen 9, дискретною відеокартою NVIDIA RTX, 32 ГБ RAM та SSD накопичувачем.', 'https://scdn.comfy.ua/89fc351a-22e7-41ee-8321-f8a9356ca351/https://cdn.comfy.ua/media/catalog/product/6/3/63bebe4e13e697.21246959_3.jpg/w_600', 4, '2025-03-25 19:16:55', '2025-03-25 19:58:08', 15.6, 'Discrete', 'SSD', 2.30),
(18, 'Acer Swift 3', 33999.00, 'Легкий ультрабук для повсякденного використання', 'Компактний ноутбук з 14-дюймовим екраном, процесором Intel Core i5, вбудованою графікою та SSD накопичувачем.', 'https://content.rozetka.com.ua/goods/images/big/379927482.jpg', 10, '2025-03-25 19:16:55', '2025-03-25 19:58:59', 14.0, 'Integrated', 'SSD', 1.40),
(17, 'HP Pavilion Gaming 16', 54999.00, 'Ігровий ноутбук середнього класу', 'HP Pavilion Gaming 16 з процесором AMD Ryzen 7, дискретною відеокартою NVIDIA, 16 ГБ RAM та SSD накопичувачем.', 'https://files.foxtrot.com.ua/PhotoNew/img_0_58_26765_0_1_F5tUBi.webp', 7, '2025-03-25 19:16:55', '2025-03-25 20:23:36', 16.0, 'Discrete', 'SSD', 2.20),
(16, 'Lenovo ThinkPad X1 Carbon', 65000.00, 'Бізнес-ноутбук для професіоналів', 'Легкий та потужний ноутбук для бізнесу з 14-дюймовим екраном, процесором Intel Core i7, 16 ГБ RAM та SSD накопичувачем.', 'https://content2.rozetka.com.ua/goods/images/big/434600251.jpg', 3, '2025-03-25 19:16:55', '2025-03-25 20:16:53', 14.0, 'Integrated', 'SSD', 1.50);

-- --------------------------------------------------------

--
-- Структура таблицы `product_images`
--

DROP TABLE IF EXISTS `product_images`;
CREATE TABLE IF NOT EXISTS `product_images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `image_filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_filename`) VALUES
(1, 1, 'https://files.foxtrot.com.ua/PhotoNew/img_0_58_27465_0_1_GnLWYZ.webp'),
(3, 3, 'https://pc.com.ua/tmp/images/products/10672/92848577373533-1000x1000-r.jpg'),
(2, 2, 'https://files.foxtrot.com.ua/PhotoNew/img_0_58_26296_0_1_sg0lqq.webp'),
(16, 16, 'https://content2.rozetka.com.ua/goods/images/big/434600251.jpg'),
(17, 17, 'https://files.foxtrot.com.ua/PhotoNew/img_0_58_26765_0_1_F5tUBi.webp'),
(18, 17, 'https://files.foxtrot.com.ua/PhotoNew/img_0_58_26572_0_1_Foxgh2.webp'),
(19, 18, 'https://content.rozetka.com.ua/goods/images/big/379927482.jpg'),
(20, 19, 'https://scdn.comfy.ua/89fc351a-22e7-41ee-8321-f8a9356ca351/https://cdn.comfy.ua/media/catalog/product/6/3/63bebe4e13e697.21246959_3.jpg/w_600'),
(21, 20, 'https://content1.rozetka.com.ua/goods/images/big/496816803.jpg'),
(22, 21, 'https://content1.rozetka.com.ua/goods/images/big/464649290.jpg'),
(23, 22, 'https://content1.rozetka.com.ua/goods/images/big/453789802.jpg'),
(24, 23, 'https://content1.rozetka.com.ua/goods/images/big/492475969.jpg');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`, `last_login`) VALUES
(1, 'Viktor', 'ipz235_mva@student.ztu.edu.ua', '$2y$10$.3eIqZt7ntsoB4540rBCfOGMwzBFAXDtqVdO6N8f1lx2YRBvaWXa.', '2025-03-24 19:44:38', NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
