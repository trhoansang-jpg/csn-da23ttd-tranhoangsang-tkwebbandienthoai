-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th1 06, 2026 lúc 09:08 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `web-ban-dt`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `brand`
--

CREATE TABLE `brand` (
  `brand_id` int(11) NOT NULL,
  `tenHang` varchar(50) NOT NULL,
  `xuatXu` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `brand`
--

INSERT INTO `brand` (`brand_id`, `tenHang`, `xuatXu`) VALUES
(1, 'Apple', 'Mỹ'),
(2, 'Samsung', 'Hàn Quốc'),
(3, 'Oppo', 'Trung Quốc'),
(4, 'Realme', 'Trung Quốc'),
(5, 'Vivo', 'Trung Quốc'),
(6, 'Xiaomi', 'Trung Quốc');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tenKH` varchar(255) NOT NULL,
  `sdtKH` varchar(15) DEFAULT NULL,
  `diaChi` text NOT NULL,
  `tongTien` decimal(12,2) NOT NULL DEFAULT 0.00,
  `ngayDat` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `tenKH`, `sdtKH`, `diaChi`, `tongTien`, `ngayDat`) VALUES
(1, 3, 'Sang', '0353045377', 'Ấp Rẫy, Xã Vinh Kim, Cầu Ngang, Trà Vinh', 102060000.00, '2025-12-27 13:58:19'),
(2, 4, 'Đoàn Thị Thùy Dung', '0354399168', 'Trọ Navi, Khóm 4, Hòa Thuận, Vĩnh Long', 34690000.00, '2025-12-28 02:18:43'),
(3, 5, 'Phạm Quý Ngọc Trân', '0355324548', '12, Long Hữu, Duyên Hải, Vĩnh Long', 37990000.00, '2025-12-28 11:53:25'),
(5, 2, 'Trần Hoàng Sang', '0353044315', '12, ấp Rẫy, Vinh Kim, Trà Vinh', 25590000.00, '2026-01-03 09:18:18');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_items`
--

CREATE TABLE `order_items` (
  `item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `soLuong` int(11) NOT NULL,
  `donGia` decimal(12,2) NOT NULL,
  `thanhTien` decimal(12,2) NOT NULL,
  `ngayTao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `order_items`
--

INSERT INTO `order_items` (`item_id`, `order_id`, `product_id`, `soLuong`, `donGia`, `thanhTien`, `ngayTao`) VALUES
(1, 1, 9, 1, 26990000.00, 26990000.00, '2025-12-27 13:58:19'),
(2, 1, 40, 1, 17490000.00, 17490000.00, '2025-12-27 13:58:19'),
(3, 1, 30, 1, 22590000.00, 22590000.00, '2025-12-27 13:58:19'),
(4, 1, 43, 1, 34990000.00, 34990000.00, '2025-12-27 13:58:19'),
(5, 2, 4, 1, 34690000.00, 34690000.00, '2025-12-28 02:18:43'),
(6, 3, 14, 1, 37990000.00, 37990000.00, '2025-12-28 11:53:25'),
(8, 5, 46, 1, 25590000.00, 25590000.00, '2026-01-03 09:18:18');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `brand_id` int(11) NOT NULL,
  `tenSp` varchar(150) NOT NULL,
  `moTa` text DEFAULT NULL,
  `giaBan` decimal(12,2) NOT NULL,
  `soLuongTon` int(11) NOT NULL DEFAULT 0,
  `CPU` varchar(120) DEFAULT NULL,
  `RAM` varchar(50) DEFAULT NULL,
  `boNho` int(11) DEFAULT NULL,
  `Camera` varchar(120) DEFAULT NULL,
  `DLPin` varchar(50) DEFAULT NULL,
  `HDH` varchar(60) DEFAULT NULL,
  `mauSac` varchar(120) DEFAULT NULL,
  `khoiLuong` decimal(5,2) DEFAULT NULL,
  `kichThuoc` varchar(50) DEFAULT NULL,
  `is_home` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`product_id`, `brand_id`, `tenSp`, `moTa`, `giaBan`, `soLuongTon`, `CPU`, `RAM`, `boNho`, `Camera`, `DLPin`, `HDH`, `mauSac`, `khoiLuong`, `kichThuoc`, `is_home`) VALUES
(1, 1, 'Iphone 17', '', 24990000.00, 100, 'Apple A19', '8', 256, '2', '3692', 'IOS 26', 'Đen', 177.00, '6.3', 1),
(2, 1, 'Iphone 16', '', 21190000.00, 10, 'Apple A18', '8', 128, '2', '3561', 'IOS 18', 'Hồng', 170.00, '6.1', 1),
(3, 1, 'Iphone 16 pro max', '', 31290000.00, 20, 'Apple A18 pro', '8', 256, '3', '4676', 'IOS 18', 'Vàng sa mạc', 221.00, '6.9', 1),
(4, 1, 'Iphone 17 pro', '', 34690000.00, 30, 'Apple A19 pro', '12', 256, '3', '4252', 'IOS 26', 'Cam vũ trụ', 204.00, '6.9', 1),
(5, 2, 'Samsung Galaxy S25 Ultra', '', 27500000.00, 20, 'Snapdragon 8 Elite', '12', 256, '4', '5000', 'Android 15', 'Xám', 218.00, '6.9', 1),
(6, 2, 'Samsung Z Fold7', '', 44500000.00, 5, 'Snapdragon 8 Elite', '12', 512, '3', '4400', 'Android 16', 'Xanh Navy', 215.00, '8.0', 1),
(7, 2, 'Samsung S25', '', 22290000.00, 20, 'Snapdragon 8 Elite', '12', 512, '3', '4000', 'Android 15', 'Xanh Lá', 162.00, '6.2', 1),
(8, 2, 'Samsung Z Flip7', '', 24990000.00, 10, 'Exynos 2500', '12', 256, '2', '4300', 'Android 15', 'Đỏ', 188.00, '6.9', 1),
(9, 3, 'Oppo Find X9 5G', '', 26990000.00, 10, 'MediaTek Dimensity 9500', '16', 512, '4', '7025', 'Android 16', 'Đỏ', 203.00, '6.59', 1),
(10, 3, 'Oppo A6 Pro 5G', '', 10290000.00, 21, 'MediaTek Dimensity 6300', '8', 256, '2', '6500', 'Android 15', 'Hồng', 185.00, '6.57', 1),
(11, 6, 'Xiaomi 15T 5G', '', 13990000.00, 12, 'MediaTek Dimensity 8400-Ultra', '12', 512, '4', '5500', 'Android 15', 'Vàng Hồng', 194.00, '6.93', 1),
(12, 5, 'Vivo V50 Lite 5G', '', 9510000.00, 0, 'MediaTek Dimensity 6300 5G 8 nhân', '8', 256, '2', '6500', 'Android 15', 'Vàng', 197.00, '6.77', 1),
(13, 1, 'Iphone Air', '', 30590000.00, 5, 'Apple A19 pro', '12', 256, '1', '3149', 'IOS 26', 'Xanh da trời', 165.00, '6.5', 1),
(14, 1, 'Iphone 17 pro max', '', 37990000.00, 25, 'Apple A19 pro', '12', 256, '3', '5000', 'IOS 26', 'Trắng', 231.00, '6.9', 1),
(15, 6, 'Xiaomi 15T Pro 5G', '', 19490000.00, 23, 'MediaTek Dimensity 9400+', '12', 512, '3', '5500', 'Android 16', 'Vàng', 210.00, '6.83', 1),
(16, 6, 'Xiaomi Redmi Note 14 Pro', '', 7920000.00, 10, 'Snapdragon® 7s Gen 3', '8', 256, '3', '5110', 'Android 16', 'Tím', 190.00, '6.67', 1),
(17, 1, 'Iphone 16 Pro', '', 25490000.00, 21, 'Apple A18 pro', '8', 128, '3', '4676', 'IOS 18', 'Trắng Titan', 199.00, '6.3', 0),
(18, 1, 'Iphone 16 Plus', '', 24590000.00, 16, 'Apple A18', '8', 128, '2', '4006', 'IOS 18', 'Xanh Lưu Ly', 199.00, '6.7', 0),
(19, 1, 'Iphone 16e', '', 12490000.00, 23, 'Apple A18', '8', 128, '1', '3961', 'IOS 18', 'Trắng', 167.00, '6.1', 0),
(20, 1, 'Iphone 15', '', 16990000.00, 50, 'Apple 16', '6', 128, '2', '3349', 'IOS 17', 'Hồng', 171.00, '6.1', 0),
(21, 1, 'Iphone 15 Plus', '', 17690000.00, 25, 'Apple 16', '6', 128, '2', '4383', 'IOS 17', 'Xanh lá', 201.00, '6.7', 0),
(22, 1, 'Iphone 15 Pro', '', 23990000.00, 24, 'Apple A17 pro', '8', 128, '3', '3274', 'IOS 17', 'Titan tự nhiên', 221.00, '6.1', 0),
(23, 1, 'Iphone 15 Pro Max', '', 24990000.00, 26, 'Apple A17', '8', 256, '3', '4422', 'IOS 17', 'Titan Xanh', 221.00, '6.7', 0),
(24, 1, 'Iphone 14', '', 13690000.00, 2, 'Apple A15', '6', 128, '2', '3279', 'IOS 16', 'Đỏ', 172.00, '6.1', 0),
(25, 2, 'Samsung S24 Plus', '', 16190000.00, 12, 'Exynos 2400', '12', 256, '3', '4900', 'Android 14, One UI 6.1', 'Xám', 196.00, '6.7', 0),
(26, 2, 'Samsung A56', '', 9100000.00, 21, 'Exynos 1580', '8', 128, '3', '5000', 'Android 16', 'Hồng', 198.00, '6.7', 0),
(27, 2, 'Samsung A36', '', 7440000.00, 21, 'Snapdragon 6 Gen 3', '8', 128, '3', '5000', 'Android', 'Tím', 195.00, '6.7', 0),
(28, 2, 'Samsung A17 5G', '', 5890000.00, 12, 'Exynos 1330', '8', 128, '3', '5000', 'Android', 'Đen', 190.00, '6.7', 0),
(29, 2, 'Samsung S25 Plus', '', 22700000.00, 34, 'Snapdragon 8 Elite', '12', 256, '3', '4900', 'Android', 'Xanh lá', 190.00, '6.7', 0),
(30, 2, 'Samsung Galaxy S24 Ultra', '', 22590000.00, 23, 'Snapdragon 8 Gen 3', '12', 256, '4', '5000', 'Android', 'Xám', 232.00, '6.8', 0),
(31, 2, 'Samsung Galaxy A26 5G', '', 6270000.00, 12, 'Exynos 1380', '8', 128, '3', '5000', 'Android', 'Xanh', 200.00, '6.7', 0),
(32, 2, 'Samsung Galaxy A16 5G', '', 5290000.00, 12, 'MediaTek Dimensity 6300', '8', 128, '3', '5000', 'Android', 'Đen', 192.00, '6.7', 0),
(33, 2, 'Samsung A06 5G', '', 3210000.00, 12, 'MediaTek Dimensity 6300', '4', 128, '2', '5000', 'Android', 'Xám', 192.00, '6.7', 0),
(34, 3, 'Oppo Find X9 Pro 5G', '', 32990000.00, 12, 'Dimensity 9500 5G', '16', 512, '4', '7500', 'Android', 'Xám', 224.00, '6.78', 0),
(35, 3, 'Oppo Reno 14 5G', '', 15500000.00, 5, 'MediaTek Dimensity 8350', '12', 256, '3', '6000', 'Android', 'Xanh lá', NULL, '6.59', 0),
(36, 3, 'Oppo Find X8', '', 19490000.00, 3, 'MediaTek Dimensity 9400', '16', 512, '3', '5630', 'ColorOS 15', 'Đen', NULL, '6.59', 0),
(38, 3, 'Oppo Find N3 Flip', '', 13990000.00, 3, 'MediaTek Dimensity 9200', '12', 256, '3', '4300', 'Android 13', 'Gold', NULL, '6.8', 0),
(39, 3, 'Oppo Reno 13 F 5G', '', 9990000.00, 3, 'Qualcomm Snapdragon 6 Gen1', '12', 256, '3', '5800', 'ColorOS 15', 'Tím', NULL, '6.67', 0),
(40, 6, 'Xiaomi 15 5G', '', 17490000.00, 10, 'Snapdragon 8 Elite', '12', 256, '3', '5240', 'Xiaomi HyperOS 2', 'Trắng', NULL, '6.36', 0),
(41, 6, 'Xiaomi 15 Ultra 5G', '', 25990000.00, 3, 'Snapdragon 8 Elite', '16', 512, '4', '5410', 'Xiaomi HyperOS 2', 'Bạc', NULL, '6.73', 0),
(42, 5, 'Vivo X300 5G', '', 23990000.00, 5, 'MediaTek Dimensity 9500 8 nhân', '12', 256, '3', '6040', 'Android 16', 'Hồng', NULL, '6.31', 0),
(43, 5, 'Vivo X300 Pro 5G', '', 34990000.00, 10, 'MediaTek Dimensity 9500 8 nhân', '16', 512, '3', '6510', 'Android 16', 'Vàng', NULL, '6.78', 0),
(44, 5, 'Vivo V60 Lite 5G', '', 10490000.00, 3, 'MediaTek Dimensity 7360-Turbo', '8', 256, '2', '6500', 'Android 15', 'Hồng', NULL, '6.77', 0),
(45, 5, 'Vivo Y19s Pro', '', 5400000.00, 3, 'Unisoc Tiger T612', '8', 128, '2', '6000', 'Android 15', 'Bạc', NULL, '6.68', 0),
(46, 1, 'Iphone 14 Pro Max', '', 25590000.00, 2, 'Apple A16 Bionic 6', '6', 128, '3', '4.323 mAh', 'IOS', 'Tím', NULL, '6.7', 0),
(47, 1, 'Iphone 13', '', 11990000.00, 2, 'Apple A15', '4', 128, '2', '3240mAh', 'IOS', 'Đỏ', NULL, '6.1', 0),
(48, 4, 'Realme 15T 5G', '', 8990000.00, 2, 'MediaTek Dimensity 6400 Max 8 nhân', '8', 256, '2', '7000', 'Android 15', 'Bạc', 189.00, '6.57', 0),
(49, 4, 'Realme 15', '', 11490000.00, 2, 'MediaTek Dimensity 7300+ 5G 8 nhân', '12', 256, '3', '7000', 'Android 15', 'Xanh', 185.00, '6.77', 0),
(50, 4, 'Realme C75', '', 4090000.00, 2, 'MediaTek Helio G92 Max 8 nhân', '8', 128, '2', '6000', 'Android 15', 'Vangf', NULL, '6.72', 0),
(51, 4, 'Realme Note 60', '', 2930000.00, 2, 'Unisoc Tiger T612', '6', 128, '2', '5000', 'Android', 'Đen', NULL, '6.7', 0),
(52, 1, 'Iphone 17', '', 30990000.00, 50, 'Apple A19', '8', 512, '2', '3692', 'IOS 26', 'Tím', 177.00, '6.3', 1),
(53, 1, 'Iphone 17', '', 30990000.00, 12, 'Apple A19', '8', 512, '2', '3692', 'IOS 26', 'Xanh Lá Xô Thơm', 177.00, '6.3', 1),
(54, 1, 'Iphone 17', '', 30990000.00, 15, 'Apple A19', '8', 512, '2', '3692', 'IOS 26', 'Đen', 177.00, '6.3', 1),
(55, 1, 'Iphone 17', '', 24990000.00, 16, 'Apple A19', '8', 256, '2', '3692', 'IOS 26', 'Tím', 177.00, '6.3', 1),
(56, 1, 'Iphone 17', '', 24990000.00, 14, 'Apple A19', '8', 256, '2', '3692', 'IOS 26', 'Xanh Lá Xô Thơm', 177.00, '6.3', 1),
(57, 1, 'Iphone 17', '', 24990000.00, 15, 'Apple A19', '8', 256, '2', '3692', 'IOS 26', 'Xanh Xám Khói', 177.00, '6.3', 1),
(58, 1, 'Iphone 17', '', 30990000.00, 21, 'Apple A19', '8', 512, '2', '3692', 'IOS 26', 'Xanh Xám Khói', 177.00, '6.3', 1),
(59, 1, 'Iphone 17', '', 24990000.00, 6, 'Apple A19', '8', 256, '2', '3692', 'IOS 26', 'Trắng', 177.00, '6.3', 1),
(60, 1, 'Iphone 17', '', 30990000.00, 25, 'Apple A19', '8', 512, '2', '3692', 'IOS 26', 'Trắng', 177.00, '6.3', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_images`
--

CREATE TABLE `product_images` (
  `img_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `hinhAnh` varchar(255) DEFAULT NULL,
  `anhthumbnail` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `product_images`
--

INSERT INTO `product_images` (`img_id`, `product_id`, `hinhAnh`, `anhthumbnail`) VALUES
(1, 1, 'images/apple/iphone_17_256_black.jpg', 'images/apple/iphone_17_256_green.jpg, images/apple/iphone_17_256_blue.jpg, images/apple/iphone_17_256_lavender.jpg, images/apple/iphone_17_256_white.jpg'),
(2, 2, 'images/apple/iphone16.jpeg', NULL),
(3, 3, 'images/apple/iphone-16-pro-max.jpg', NULL),
(4, 4, 'images/apple/iphone17pro.png', NULL),
(5, 5, 'images/apple/samsungs25-ultra.webp', NULL),
(6, 6, 'images/apple/samsung-galaxy-z-fold-7.webp', NULL),
(7, 7, 'images/apple/samsung-s25.jpeg', NULL),
(8, 8, 'images/apple/ss-z-flip.jpeg', NULL),
(9, 9, 'images/apple/oppo-findx9.webp', NULL),
(10, 10, 'images/apple/oppo-a6-pro.jpg', NULL),
(11, 11, 'images/apple/xiaomi-15t.webp', NULL),
(12, 12, 'images/apple/vivo_v50.jpg', NULL),
(13, 13, 'images/apple/iphone_air.webp', NULL),
(14, 14, 'images/apple/iphone-17-promax.webp', NULL),
(15, 15, 'images/apple/xiaomi-15t-pro.webp', NULL),
(16, 16, 'images/apple/xiaomi-redmi-note14pro.webp', NULL),
(17, 17, 'images/apple/iphone-16-protitan.webp', NULL),
(18, 18, 'images/apple/iphone-16-plus.webp', NULL),
(19, 19, 'images/apple/iphone-16e.webp', NULL),
(20, 20, 'images/apple/iphone-15-hong.webp', NULL),
(21, 21, 'images/apple/iphone-15-plus.webp', NULL),
(22, 22, 'images/apple/iphone15-pro.webp', NULL),
(23, 23, 'images/apple/iphone15-promax-titanxanh.webp', NULL),
(24, 24, 'images/apple/iphone14v.webp', NULL),
(25, 25, 'images/apple/galaxy-s24plus.webp', NULL),
(26, 26, 'images/apple/samsung-a56hong.webp', NULL),
(27, 27, 'images/apple/samsung-a36.webp', NULL),
(28, 28, 'images/apple/samsung-a175g.webp', NULL),
(29, 29, 'images/apple/samsungs25-plus.webp', NULL),
(30, 30, 'images/apple/galaxy-s24-ultra.webp', NULL),
(31, 31, 'images/apple/galaxy-a26.webp', NULL),
(32, 32, 'images/apple/galaxy-a16.webp', NULL),
(33, 33, 'images/apple/galaxy-a06.webp', NULL),
(34, 34, 'images/apple/oppofind-x9pro.webp', NULL),
(35, 35, 'images/apple/oppo-renoo14.webp', NULL),
(36, 36, 'images/apple/oppofind-x8.webp', NULL),
(37, 38, 'images/apple/oppo-n3flip.webp', NULL),
(38, 39, 'images/apple/opporeno13-f.webp', NULL),
(39, 40, 'images/apple/xiaomi15.webp', NULL),
(40, 41, 'images/apple/xiaomi15-ultra.webp', NULL),
(41, 42, 'images/apple/vivox300.png', NULL),
(42, 43, 'images/apple/vivox300pro.png', NULL),
(43, 44, 'images/apple/vivov60.jpg', NULL),
(44, 45, 'images/apple/vivoy19s.jpg', NULL),
(45, 46, 'images/apple/iphone14promax.webp', NULL),
(46, 47, 'images/apple/iphone13.webp', NULL),
(47, 48, 'images/apple/realme15t.jpg', NULL),
(48, 49, 'images/apple/realme15.jpg', NULL),
(49, 50, 'images/apple/realmec75.jpg', NULL),
(50, 51, 'images/apple/realme60.jpg', NULL),
(51, 52, 'images/apple/iphone_17_256_lavender.jpg', 'images/apple/iphone_17_256_green.jpg, images/apple/iphone_17_256_blue.jpg, images/apple/iphone_17_256_black.jpg, images/apple/iphone_17_256_white.jpg'),
(52, 53, 'images/apple/iphone_17_256_green.jpg', 'images/apple/iphone_17_256_black.jpg, images/apple/iphone_17_256_blue.jpg, images/apple/iphone_17_256_lavender.jpg, images/apple/iphone_17_256_white.jpg'),
(53, 54, 'images/apple/iphone_17_256_black.jpg', 'images/apple/iphone_17_256_green.jpg, images/apple/iphone_17_256_blue.jpg, images/apple/iphone_17_256_lavender.jpg, images/apple/iphone_17_256_white.jpg'),
(54, 55, 'images/apple/iphone_17_256_lavender.jpg', 'images/apple/iphone_17_256_green.jpg, images/apple/iphone_17_256_blue.jpg, images/apple/iphone_17_256_black.jpg, images/apple/iphone_17_256_white.jpg'),
(55, 56, 'images/apple/iphone_17_256_green.jpg', 'images/apple/iphone_17_256_black.jpg, images/apple/iphone_17_256_blue.jpg, images/apple/iphone_17_256_lavender.jpg, images/apple/iphone_17_256_white.jpg'),
(56, 57, 'images/apple/iphone_17_256_blue.jpg', 'images/apple/iphone_17_256_black.jpg, images/apple/iphone_17_256_green.jpg, images/apple/iphone_17_256_lavender.jpg, images/apple/iphone_17_256_white.jpg'),
(57, 58, 'images/apple/iphone_17_256_blue.jpg', 'images/apple/iphone_17_256_black.jpg, images/apple/iphone_17_256_green.jpg, images/apple/iphone_17_256_lavender.jpg, images/apple/iphone_17_256_white.jpg'),
(58, 59, 'images/apple/iphone_17_256_white.jpg', 'images/apple/iphone_17_256_black.jpg, images/apple/iphone_17_256_blue.jpg, images/apple/iphone_17_256_lavender.jpg, images/apple/iphone_17_256_green.jpg'),
(59, 60, 'images/apple/iphone_17_256_white.jpg', 'images/apple/iphone_17_256_black.jpg, images/apple/iphone_17_256_blue.jpg, images/apple/iphone_17_256_lavender.jpg, images/apple/iphone_17_256_green.jpg');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `tenVaiTro` varchar(50) NOT NULL,
  `moTa` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `roles`
--

INSERT INTO `roles` (`role_id`, `tenVaiTro`, `moTa`) VALUES
(1, 'Quản trị', 'Quản trị trang web'),
(2, 'Khách hàng', 'Người mua hàng');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `hoTen` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `matKhau` varchar(255) NOT NULL,
  `sdt` varchar(15) DEFAULT NULL,
  `diaChi` text DEFAULT NULL,
  `ngaySinh` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`user_id`, `role_id`, `hoTen`, `email`, `matKhau`, `sdt`, `diaChi`, `ngaySinh`) VALUES
(2, 1, 'Trần Hoàng Sang', 'trhoansangwww@gmail.com', '$2y$10$jVwtlFl1FNV94XiPj/NBjupZKWRZZmy0lzgvB2U2DEukQcd/b9Pla', '0353044315', NULL, NULL),
(3, 2, 'Hoàng Bii', 'tranhoangsangtravinh@gmail.com', '$2y$10$3h3RWi/8PM.pxwvcwnTBt.zlVxiGAPzJJ0Thw58LbEnRRsibWpO.i', '0353045377', NULL, NULL),
(4, 2, 'Đoàn Thị Thùy Dung', 'thuydungwww@gmail.com', '$2y$10$Expp5dT0jfT94JR7WT57x.jbbDgmq0qUJQnkIm7W6UOS1UdYD/XT6', '0354399168', NULL, NULL),
(5, 2, 'Phạm Quý Ngọc Trân', 'quytranwww@gmail.com', '$2y$10$.WV1XZUsnrML9QB.Es9wyevqy0uGoNwa2QIMDPv3bDGY5WYZ8JjcO', '0355324548', NULL, NULL);

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `brand`
--
ALTER TABLE `brand`
  ADD PRIMARY KEY (`brand_id`),
  ADD UNIQUE KEY `tenHang` (`tenHang`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_ngayDat` (`ngayDat`);

--
-- Chỉ mục cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `idx_order` (`order_id`),
  ADD KEY `idx_product` (`product_id`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `idx_brand` (`brand_id`),
  ADD KEY `idx_giaBan` (`giaBan`);

--
-- Chỉ mục cho bảng `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`img_id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- Chỉ mục cho bảng `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `tenVaiTro` (`tenVaiTro`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `idx_email` (`email`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `brand`
--
ALTER TABLE `brand`
  MODIFY `brand_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `order_items`
--
ALTER TABLE `order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT cho bảng `product_images`
--
ALTER TABLE `product_images`
  MODIFY `img_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT cho bảng `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Các ràng buộc cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Các ràng buộc cho bảng `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`brand_id`) REFERENCES `brand` (`brand_id`);

--
-- Các ràng buộc cho bảng `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `fk_product_images_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
