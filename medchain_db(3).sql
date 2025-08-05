-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 22, 2025 at 01:14 PM
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
-- Database: `medchain_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_trail`
--

CREATE TABLE `audit_trail` (
  `log_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `actor_id` int(11) NOT NULL,
  `log_timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `details` text DEFAULT NULL,
  `current_hash` varchar(64) NOT NULL,
  `previous_hash` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_trail`
--

INSERT INTO `audit_trail` (`log_id`, `product_id`, `action`, `actor_id`, `log_timestamp`, `details`, `current_hash`, `previous_hash`) VALUES
(1, 1, 'PRODUCT_REGISTERED', 1, '2025-07-20 13:35:23', '{\"brand_name\":\"n\",\"batch\":\"12\",\"quantity\":120}', '42b6a988a042d51fe8de8cef45ba6af272caca274857a360b8e0ef4ee299a22d', '0000000000000000000000000000000000000000000000000000000000000000'),
(2, 1, 'PRODUCT_DISPENSED_TO_DISTRIBUTOR', 1, '2025-07-20 18:10:19', '{\"product_id\":1,\"brand_name\":\"n\",\"batch_number\":\"12\",\"dispensed_quantity\":20,\"remaining_quantity\":100,\"dispensed_at\":\"2025-07-20 23:40:19\"}', '0a406d55f2685e4fca39d5cfa0ab610292b4210b303dd471abc5f39915489c01', '42b6a988a042d51fe8de8cef45ba6af272caca274857a360b8e0ef4ee299a22d'),
(3, 1, 'PRODUCT_DISPENSED', 4, '2025-07-20 18:14:14', '{\"brand_name\":\"n\",\"dispensed_quantity\":5,\"remaining_stock\":95,\"patient_id\":5}', 'b52262bc070b15ac14b1bb6cd4eae483247da752c1ab45aa9a0be386c818c765', '0a406d55f2685e4fca39d5cfa0ab610292b4210b303dd471abc5f39915489c01'),
(4, 1, 'PRODUCT_PICKED_UP_BY_DISTRIBUTOR', 3, '2025-07-20 18:19:13', '{\"product_id\":1,\"distributor_product_id\":1,\"picked_up_by_actor_id\":3,\"timestamp\":\"2025-07-20 23:49:13\"}', '0ee8e7c16d23d857663cc703b3509b852f12a81cd7dee417de4842722c7a41ad', 'b52262bc070b15ac14b1bb6cd4eae483247da752c1ab45aa9a0be386c818c765'),
(5, 1, 'PRODUCT_SOLD_TO_PHARMACIST', 3, '2025-07-20 18:19:27', '{\"product_id\":1,\"sold_by_distributor_id\":3,\"sold_to_pharmacist_id\":4,\"quantity\":1,\"timestamp\":\"2025-07-20 23:49:27\"}', 'b39307dab5c10c0f2a40b7c26a68ea8e1fa3357e10d418190c41e6f0f9e151c5', '0ee8e7c16d23d857663cc703b3509b852f12a81cd7dee417de4842722c7a41ad'),
(6, 1, 'PRODUCT_SOLD_TO_PHARMACIST', 3, '2025-07-20 18:30:41', '{\"product_id\":1,\"sold_by_distributor_id\":3,\"sold_to_pharmacist_id\":4,\"quantity\":10,\"timestamp\":\"2025-07-21 00:00:41\"}', '2e51b2fdab5ba4eee348dd4ed1a8ba52f955a6a6510488f0252306f1a7e9066a', 'b39307dab5c10c0f2a40b7c26a68ea8e1fa3357e10d418190c41e6f0f9e151c5'),
(7, 1, 'PRODUCT_SOLD_TO_PHARMACIST', 3, '2025-07-20 18:43:28', '{\"product_id\":1,\"sold_by_distributor_id\":3,\"sold_to_pharmacist_id\":4,\"quantity\":1,\"timestamp\":\"2025-07-21 00:13:28\"}', '8f8c40d7ecc67b44783673e02770a1291ae7d94614681549d8cbdef28ca6a164', '2e51b2fdab5ba4eee348dd4ed1a8ba52f955a6a6510488f0252306f1a7e9066a'),
(8, 1, 'PRODUCT_DISPENSED_TO_PATIENT', 4, '2025-07-20 18:48:30', '{\"product_id\":1,\"dispensed_by_pharmacist_id\":4,\"patient_name\":\"ok\",\"quantity\":1,\"timestamp\":\"2025-07-21 00:18:30\"}', '802b3f6ff9f6a7b494ca5ad5a42395c7f1cc7452849d0d0fdf7af396efe376d4', '8f8c40d7ecc67b44783673e02770a1291ae7d94614681549d8cbdef28ca6a164'),
(9, 1, 'PRODUCT_DISPENSED_TO_DISTRIBUTOR', 1, '2025-07-20 18:59:36', '{\"product_id\":1,\"brand_name\":\"n\",\"batch_number\":\"12\",\"dispensed_quantity\":10,\"remaining_quantity\":85,\"dispensed_at\":\"2025-07-21 00:29:36\"}', '872c62b49d15af6751f90ff7fcdbe2ea24f71d260ac3ca45d170e7710b8e4946', '802b3f6ff9f6a7b494ca5ad5a42395c7f1cc7452849d0d0fdf7af396efe376d4'),
(10, 1, 'PRODUCT_PICKED_UP_BY_DISTRIBUTOR', 3, '2025-07-20 19:00:15', '{\"product_id\":1,\"distributor_product_id\":2,\"picked_up_by_actor_id\":3,\"timestamp\":\"2025-07-21 00:30:15\"}', 'c45c8ffd3ad9b34c64966e0d0286ba5facd60fc1b4df67e1e2c9670f992e7619', '872c62b49d15af6751f90ff7fcdbe2ea24f71d260ac3ca45d170e7710b8e4946'),
(11, 1, 'PRODUCT_SOLD_TO_PHARMACIST', 3, '2025-07-20 19:00:29', '{\"product_id\":1,\"sold_by_distributor_id\":3,\"sold_to_pharmacist_id\":4,\"quantity\":1,\"timestamp\":\"2025-07-21 00:30:29\"}', 'be1ca984fe11883cb969ad0f98f8d5228eb1f51d756209a7cdce00b7305f699b', 'c45c8ffd3ad9b34c64966e0d0286ba5facd60fc1b4df67e1e2c9670f992e7619'),
(12, 1, 'PRODUCT_DISPENSED_TO_DISTRIBUTOR', 1, '2025-07-20 19:18:00', '{\"product_id\":1,\"brand_name\":\"n\",\"batch_number\":\"12\",\"dispensed_quantity\":50,\"remaining_quantity\":35,\"dispensed_at\":\"2025-07-21 00:48:00\"}', 'cd6cd7e79e9c9e93c33ceed6fcecb7d1537bf16b802e27c8ac559cb9a9a56404', 'be1ca984fe11883cb969ad0f98f8d5228eb1f51d756209a7cdce00b7305f699b'),
(13, 1, 'PRODUCT_PICKED_UP_BY_DISTRIBUTOR', 3, '2025-07-20 19:18:47', '{\"product_id\":1,\"distributor_product_id\":3,\"picked_up_by_actor_id\":3,\"timestamp\":\"2025-07-21 00:48:47\"}', '5ef2d9fdcd9600a482b0612d2a4e91ae8d73ffff17203d3122d99dc5b9657289', 'cd6cd7e79e9c9e93c33ceed6fcecb7d1537bf16b802e27c8ac559cb9a9a56404'),
(14, 1, 'PRODUCT_SOLD_TO_PHARMACIST', 3, '2025-07-20 19:19:07', '{\"product_id\":1,\"sold_by_distributor_id\":3,\"sold_to_pharmacist_id\":4,\"quantity\":5,\"timestamp\":\"2025-07-21 00:49:07\"}', 'bb376da86290842d113de07849670a1fa9cba0a857398fe57afed45a78a1d4be', '5ef2d9fdcd9600a482b0612d2a4e91ae8d73ffff17203d3122d99dc5b9657289'),
(15, 1, 'PRODUCT_DISPENSED_TO_PATIENT', 4, '2025-07-20 19:20:15', '{\"product_id\":1,\"dispensed_by_pharmacist_id\":4,\"patient_name\":\"Patient\",\"quantity\":1,\"timestamp\":\"2025-07-21 00:50:15\"}', 'f459dd974405e83d2c6af62c7ebfd4f03a56e0fbfa8312c8446b2eeba7e940e1', 'bb376da86290842d113de07849670a1fa9cba0a857398fe57afed45a78a1d4be'),
(16, 1, 'PRODUCT_SOLD_TO_PHARMACIST', 3, '2025-07-20 19:38:18', '{\"product_id\":1,\"sold_by_distributor_id\":3,\"sold_to_pharmacist_id\":4,\"quantity\":30,\"timestamp\":\"2025-07-21 01:08:18\"}', '04a001a7ad4e807d1f1111fc2598ce8035276b4cce8333e4af356bbdd8dab7a3', 'f459dd974405e83d2c6af62c7ebfd4f03a56e0fbfa8312c8446b2eeba7e940e1'),
(17, 1, 'PRODUCT_DISPENSED_TO_PATIENT', 4, '2025-07-20 19:38:47', '{\"product_id\":1,\"dispensed_by_pharmacist_id\":4,\"patient_id\":5,\"patient_name\":\"CHICKYA\",\"quantity\":12,\"timestamp\":\"2025-07-21 01:08:47\"}', '05ea963316d27adf50aa5aa67c3a8d55b537fa2ffa3d3dfb46597a594f06680a', '04a001a7ad4e807d1f1111fc2598ce8035276b4cce8333e4af356bbdd8dab7a3'),
(18, 2, 'PRODUCT_REGISTERED', 1, '2025-07-20 19:43:33', '{\"brand_name\":\"omee\",\"batch\":\"21\",\"quantity\":20}', '10ba6946450ae5fa80feaa57a6d18fcca339d7bc97c67f19ef5d7d13693d8a74', '0000000000000000000000000000000000000000000000000000000000000000'),
(19, 2, 'PRODUCT_DISPENSED_TO_DISTRIBUTOR', 1, '2025-07-20 19:44:18', '{\"product_id\":2,\"brand_name\":\"omee\",\"batch_number\":\"21\",\"dispensed_quantity\":10,\"remaining_quantity\":10,\"dispensed_at\":\"2025-07-21 01:14:18\"}', 'f779becfbb0644d1732e6487816fa0e2b799451ec5cff78c8ee7755d6491e867', '10ba6946450ae5fa80feaa57a6d18fcca339d7bc97c67f19ef5d7d13693d8a74'),
(20, 2, 'PRODUCT_PICKED_UP_BY_DISTRIBUTOR', 3, '2025-07-20 19:44:49', '{\"product_id\":2,\"distributor_product_id\":4,\"picked_up_by_actor_id\":3,\"timestamp\":\"2025-07-21 01:14:49\"}', '8bd8b35388910003727045dd0c44aee35e8555d0cd732befb0358e8497e026d9', 'f779becfbb0644d1732e6487816fa0e2b799451ec5cff78c8ee7755d6491e867'),
(21, 2, 'PRODUCT_SOLD_TO_PHARMACIST', 3, '2025-07-20 19:45:02', '{\"product_id\":2,\"sold_by_distributor_id\":3,\"sold_to_pharmacist_id\":4,\"quantity\":5,\"timestamp\":\"2025-07-21 01:15:02\"}', '2a1ef94f6086fa1e4ddf8107c3886935417a84df8d65985f534a625a605abfd7', '8bd8b35388910003727045dd0c44aee35e8555d0cd732befb0358e8497e026d9'),
(22, 2, 'PRODUCT_DISPENSED_TO_PATIENT', 4, '2025-07-20 19:45:52', '{\"product_id\":2,\"dispensed_by_pharmacist_id\":4,\"patient_id\":5,\"patient_name\":\"CHICKYA\",\"quantity\":3,\"timestamp\":\"2025-07-21 01:15:52\"}', '27ab4748a94511f9bd4e81ad2910c2e32b938dc3294e47abd6fad17c5a3cfe4d', '2a1ef94f6086fa1e4ddf8107c3886935417a84df8d65985f534a625a605abfd7'),
(23, 2, 'PRODUCT_SOLD_TO_PHARMACIST', 3, '2025-07-20 20:07:28', '{\"product_id\":2,\"sold_by_distributor_id\":3,\"sold_to_pharmacist_id\":4,\"quantity\":5,\"timestamp\":\"2025-07-21 01:37:28\"}', 'ea0ab4252730f7d80e5342423d6e7748f0c70ba20318e3ca98c11704d9c30d0d', '27ab4748a94511f9bd4e81ad2910c2e32b938dc3294e47abd6fad17c5a3cfe4d'),
(24, 3, 'PRODUCT_REGISTERED', 1, '2025-07-20 22:06:19', '{\"brand_name\":\"dolo\",\"batch\":\"33\",\"quantity\":120}', 'dcbca9d95a490e62e1f2dabdc4836448e85e3a3d1e564d03df210b052278a44f', '0000000000000000000000000000000000000000000000000000000000000000'),
(25, 3, 'PRODUCT_DISPENSED_TO_DISTRIBUTOR', 1, '2025-07-20 22:07:20', '{\"product_id\":3,\"brand_name\":\"dolo\",\"batch_number\":\"33\",\"dispensed_quantity\":50,\"remaining_quantity\":70,\"dispensed_at\":\"2025-07-21 03:37:20\"}', 'c87d71805ec2af6545c14723ce3d132e554e8330d94eb099f6aa248a6e5e9252', 'dcbca9d95a490e62e1f2dabdc4836448e85e3a3d1e564d03df210b052278a44f'),
(26, 3, 'PRODUCT_PICKED_UP_BY_DISTRIBUTOR', 3, '2025-07-20 22:07:55', '{\"product_id\":3,\"distributor_product_id\":5,\"picked_up_by_actor_id\":3,\"timestamp\":\"2025-07-21 03:37:55\"}', '51cc8b8e3aa9d3ae4d3c4df465391655f38ef58a26c20a95bdcba8153c34eaef', 'c87d71805ec2af6545c14723ce3d132e554e8330d94eb099f6aa248a6e5e9252'),
(27, 3, 'PRODUCT_SOLD_TO_PHARMACIST', 3, '2025-07-20 22:08:42', '{\"product_id\":3,\"sold_by_distributor_id\":3,\"sold_to_pharmacist_id\":4,\"quantity\":20,\"timestamp\":\"2025-07-21 03:38:42\"}', '53ca2884db3af109950676a3e32a84a27a6216e8af2b1530f7dc7658e20a0261', '51cc8b8e3aa9d3ae4d3c4df465391655f38ef58a26c20a95bdcba8153c34eaef'),
(28, 3, 'PRODUCT_DISPENSED_TO_PATIENT', 4, '2025-07-20 22:13:36', '{\"product_id\":3,\"dispensed_by_pharmacist_id\":4,\"patient_id\":6,\"patient_name\":\"MR GAUTAM\",\"quantity\":10,\"timestamp\":\"2025-07-21 03:43:36\"}', '2506819e1b72d8079a93133b3143443c2a2bbd08b902e8006ea0979c9311fc10', '53ca2884db3af109950676a3e32a84a27a6216e8af2b1530f7dc7658e20a0261'),
(29, 3, 'PRODUCT_DISPENSED_TO_DISTRIBUTOR', 1, '2025-07-21 01:38:14', '{\"product_id\":3,\"brand_name\":\"dolo\",\"batch_number\":\"33\",\"dispensed_quantity\":22,\"remaining_quantity\":48,\"dispensed_at\":\"2025-07-21 07:08:14\"}', '41a4032fd5af0122842258e26d877a993574aee37df7e92fe2785d6b9fdbb24d', '2506819e1b72d8079a93133b3143443c2a2bbd08b902e8006ea0979c9311fc10'),
(30, 3, 'PRODUCT_PICKED_UP_BY_DISTRIBUTOR', 3, '2025-07-21 01:38:37', '{\"product_id\":3,\"distributor_product_id\":6,\"picked_up_by_actor_id\":3,\"timestamp\":\"2025-07-21 07:08:37\"}', '77b344ba9bad5f72e55d6f196bd677fbc66eebe871b0a74e008a0b39c0623816', '41a4032fd5af0122842258e26d877a993574aee37df7e92fe2785d6b9fdbb24d'),
(31, 3, 'PRODUCT_SOLD_TO_PHARMACIST', 3, '2025-07-21 01:38:54', '{\"product_id\":3,\"sold_by_distributor_id\":3,\"sold_to_pharmacist_id\":4,\"quantity\":20,\"timestamp\":\"2025-07-21 07:08:54\"}', 'e66859000183c9bbb403920f73a02e8755a4cbddde5c732f03261081b897c4eb', '77b344ba9bad5f72e55d6f196bd677fbc66eebe871b0a74e008a0b39c0623816'),
(32, 3, 'PRODUCT_DISPENSED_TO_PATIENT', 4, '2025-07-21 01:39:47', '{\"product_id\":3,\"dispensed_by_pharmacist_id\":4,\"patient_id\":6,\"patient_name\":\"MR GAUTAM\",\"quantity\":10,\"timestamp\":\"2025-07-21 07:09:47\"}', 'f372a79d11beb16797fc78f91961af07224930d18fc7c9582d87beb3713a62e1', 'e66859000183c9bbb403920f73a02e8755a4cbddde5c732f03261081b897c4eb'),
(33, 3, 'PRODUCT_SOLD_TO_PHARMACIST', 3, '2025-07-21 01:46:07', '{\"product_id\":3,\"sold_by_distributor_id\":3,\"sold_to_pharmacist_id\":4,\"quantity\":5,\"timestamp\":\"2025-07-21 07:16:07\"}', 'ec9cb4fa3dc52a552ca9ecfd74fe7e6bd3d517cd53e7eeff490e6040f5525c5f', 'f372a79d11beb16797fc78f91961af07224930d18fc7c9582d87beb3713a62e1'),
(34, 3, 'PRODUCT_SOLD_TO_PHARMACIST', 3, '2025-07-21 01:46:27', '{\"product_id\":3,\"sold_by_distributor_id\":3,\"sold_to_pharmacist_id\":4,\"quantity\":10,\"timestamp\":\"2025-07-21 07:16:27\"}', 'dddf326cd838f62b9a6b74bbfce346469e7d9178a33e520239e7b3e5dab978de', 'ec9cb4fa3dc52a552ca9ecfd74fe7e6bd3d517cd53e7eeff490e6040f5525c5f'),
(35, 3, 'PRODUCT_SOLD_TO_PHARMACIST', 3, '2025-07-21 01:50:16', '{\"product_id\":3,\"sold_by_distributor_id\":3,\"sold_to_pharmacist_id\":4,\"quantity\":3,\"timestamp\":\"2025-07-21 07:20:16\"}', 'a843f2f43bd7d26079d7f11358fa622a6a8570f91e106c18c2e07240288dbc3c', 'dddf326cd838f62b9a6b74bbfce346469e7d9178a33e520239e7b3e5dab978de'),
(36, 3, 'PRODUCT_SOLD_TO_PHARMACIST', 3, '2025-07-21 01:51:38', '{\"product_id\":3,\"sold_by_distributor_id\":3,\"sold_to_pharmacist_id\":4,\"quantity\":2,\"timestamp\":\"2025-07-21 07:21:38\"}', 'ac764771461157329ebe73aa0dc547590e59e15d5b9d12208c840ee246339320', 'a843f2f43bd7d26079d7f11358fa622a6a8570f91e106c18c2e07240288dbc3c'),
(37, 3, 'PRODUCT_SOLD_TO_PHARMACIST', 3, '2025-07-21 02:43:30', '{\"product_id\":3,\"sold_by_distributor_id\":3,\"sold_to_pharmacist_id\":4,\"quantity\":2,\"timestamp\":\"2025-07-21 08:13:30\"}', '736de814a068dfaef3041fd3d047ff9f85db537c01e6b8aaf2f93546d05d2067', 'ac764771461157329ebe73aa0dc547590e59e15d5b9d12208c840ee246339320'),
(38, 3, 'PRODUCT_SOLD_TO_PHARMACIST', 3, '2025-07-21 02:53:05', '{\"product_id\":3,\"sold_by_distributor_id\":3,\"sold_to_pharmacist_id\":4,\"quantity\":5,\"timestamp\":\"2025-07-21 08:23:05\"}', 'c6e34825201a9abb7d5319d81d760873f4785a155081fb5de534325402c942b5', '736de814a068dfaef3041fd3d047ff9f85db537c01e6b8aaf2f93546d05d2067'),
(39, 4, 'PRODUCT_REGISTERED', 1, '2025-07-21 05:15:30', '{\"brand_name\":\"final\",\"batch\":\"111\",\"quantity\":51}', '0b0c8a05f1b923d85ffe690081f4a1d8fdfd9feaceaac7a94e3fe36e59418b2e', '0000000000000000000000000000000000000000000000000000000000000000'),
(40, 4, 'PRODUCT_DISPENSED_TO_DISTRIBUTOR', 1, '2025-07-21 05:16:47', '{\"product_id\":4,\"brand_name\":\"final\",\"batch_number\":\"111\",\"dispensed_quantity\":30,\"remaining_quantity\":21,\"dispensed_at\":\"2025-07-21 10:46:47\"}', 'f824e20deac497c661acf1fce1f8d2b79a1ccfcfae8e520e035ff80faf00b502', '0b0c8a05f1b923d85ffe690081f4a1d8fdfd9feaceaac7a94e3fe36e59418b2e'),
(41, 4, 'PRODUCT_PICKED_UP_BY_DISTRIBUTOR', 3, '2025-07-21 05:16:59', '{\"product_id\":4,\"distributor_product_id\":7,\"picked_up_by_actor_id\":3,\"timestamp\":\"2025-07-21 10:46:59\"}', 'ef8250b03bda7c338775db17a35fa3b3f5d4338da045f4e13ab0267b94ca7ff9', 'f824e20deac497c661acf1fce1f8d2b79a1ccfcfae8e520e035ff80faf00b502'),
(42, 4, 'PRODUCT_SOLD_TO_PHARMACIST', 3, '2025-07-21 05:17:19', '{\"product_id\":4,\"sold_by_distributor_id\":3,\"sold_to_pharmacist_id\":4,\"quantity\":20,\"timestamp\":\"2025-07-21 10:47:19\"}', '23ff4a43b54c5af34664085f9e773831f29dd9aca085e07408ccda996ec84df7', 'ef8250b03bda7c338775db17a35fa3b3f5d4338da045f4e13ab0267b94ca7ff9'),
(43, 4, 'PRODUCT_DISPENSED_TO_PATIENT', 4, '2025-07-21 05:18:42', '{\"product_id\":4,\"dispensed_by_pharmacist_id\":4,\"patient_id\":6,\"patient_name\":\"MR GAUTAM\",\"quantity\":10,\"timestamp\":\"2025-07-21 10:48:42\"}', '937bdfbc91a74f0cb2f0c7c6fe6896ef7f20413db4d52ee8ab8c171a51938dd9', '23ff4a43b54c5af34664085f9e773831f29dd9aca085e07408ccda996ec84df7'),
(44, 4, 'PRODUCT_DISPENSED_TO_DISTRIBUTOR', 1, '2025-07-21 13:41:39', '{\"product_id\":4,\"brand_name\":\"final\",\"batch_number\":\"111\",\"dispensed_quantity\":12,\"remaining_quantity\":9,\"dispensed_at\":\"2025-07-21 19:11:39\"}', '68704305eefd90b6a7e342c30a1bf575094a39357e2420911c335d2ff66aad52', '937bdfbc91a74f0cb2f0c7c6fe6896ef7f20413db4d52ee8ab8c171a51938dd9'),
(45, 3, 'PRODUCT_DISPENSED_TO_PATIENT', 4, '2025-07-21 13:42:57', '{\"product_id\":3,\"dispensed_by_pharmacist_id\":4,\"patient_id\":6,\"patient_name\":\"MR GAUTAM\",\"quantity\":5,\"timestamp\":\"2025-07-21 19:12:57\"}', 'd0e123ba2216a3397d86d161ea02ed5b6a3cae2d18b6c48804737dd7fd61dd82', 'c6e34825201a9abb7d5319d81d760873f4785a155081fb5de534325402c942b5'),
(46, 4, 'PRODUCT_SOLD_TO_PHARMACIST', 8, '2025-07-21 14:52:26', '{\"product_id\":4,\"sold_by_distributor_id\":8,\"sold_to_pharmacist_id\":9,\"quantity\":2,\"timestamp\":\"2025-07-21 20:22:26\"}', '971c203289ec809fc39246a7a0975e4f4dbb490eaee4de6f98f113403dbac48c', '68704305eefd90b6a7e342c30a1bf575094a39357e2420911c335d2ff66aad52'),
(47, 4, 'PRODUCT_DISPENSED_TO_PATIENT', 9, '2025-07-21 14:57:38', '{\"product_id\":4,\"dispensed_by_pharmacist_id\":9,\"patient_id\":6,\"patient_name\":\"MR GAUTAM\",\"quantity\":1,\"timestamp\":\"2025-07-21 20:27:38\"}', 'e11ff6c29671f5766b46098018963fe68b7e41ce60683070f23bb48a4f39c249', '971c203289ec809fc39246a7a0975e4f4dbb490eaee4de6f98f113403dbac48c'),
(48, 4, 'PRODUCT_DISPENSED_TO_PATIENT', 9, '2025-07-21 15:20:41', '{\"product_id\":4,\"dispensed_by_pharmacist_id\":9,\"patient_id\":11,\"patient_name\":\"patient\",\"quantity\":1,\"timestamp\":\"2025-07-21 20:50:41\"}', '8a267358348a958c0269a14358b10057e7d95d1bb3a7c8d6bbfef74e993d66ce', 'e11ff6c29671f5766b46098018963fe68b7e41ce60683070f23bb48a4f39c249'),
(49, 4, 'PRODUCT_DISPENSED_TO_PATIENT', 4, '2025-07-21 17:29:57', '{\"product_id\":4,\"dispensed_by_pharmacist_id\":4,\"patient_id\":11,\"patient_name\":\"patient\",\"quantity\":5,\"timestamp\":\"2025-07-21 22:59:57\"}', '2cab2a60fe48d24e10e90b9fd2d59af9ae1789410c6f38163d065480712ab830', '8a267358348a958c0269a14358b10057e7d95d1bb3a7c8d6bbfef74e993d66ce'),
(50, 1, 'PRODUCT_SOLD_TO_PHARMACIST', 8, '2025-07-21 17:40:51', '{\"product_id\":1,\"sold_by_distributor_id\":8,\"sold_to_pharmacist_id\":9,\"quantity\":10,\"timestamp\":\"2025-07-21 23:10:51\"}', 'e515bf6d498b39bb209a900b89fc50b592ab23fb16daf6ac3f892095fd979453', '05ea963316d27adf50aa5aa67c3a8d55b537fa2ffa3d3dfb46597a594f06680a'),
(51, 1, 'PRODUCT_DISPENSED_TO_PATIENT', 9, '2025-07-21 17:42:00', '{\"product_id\":1,\"dispensed_by_pharmacist_id\":9,\"patient_id\":11,\"patient_name\":\"patient\",\"quantity\":5,\"timestamp\":\"2025-07-21 23:12:00\"}', 'a27f164190265bfe12b0634e79884bc97671716f16a5d0b9de7e836998c96afa', 'e515bf6d498b39bb209a900b89fc50b592ab23fb16daf6ac3f892095fd979453'),
(52, 1, 'PRODUCT_DISPENSED_TO_PATIENT', 9, '2025-07-21 20:02:38', '{\"product_id\":1,\"dispensed_by_pharmacist_id\":9,\"patient_id\":5,\"patient_name\":\"CHICKYA\",\"quantity\":1,\"timestamp\":\"2025-07-22 01:32:38\"}', 'e74396c6c077cbe06d3c6020b70bfc030e6c89829f4b6d06f9c51af0f2938513', 'a27f164190265bfe12b0634e79884bc97671716f16a5d0b9de7e836998c96afa'),
(53, 1, 'PRODUCT_DISPENSED_TO_PATIENT', 9, '2025-07-22 02:37:21', '{\"product_id\":1,\"dispensed_by_pharmacist_id\":9,\"patient_id\":11,\"patient_name\":\"patient\",\"quantity\":2,\"timestamp\":\"2025-07-22 08:07:21\"}', '3e7c4ac5dcf6f676e551c89056967ff16a36c5c67d6fe025686f0e75340c4122', 'e74396c6c077cbe06d3c6020b70bfc030e6c89829f4b6d06f9c51af0f2938513'),
(54, 4, 'PRODUCT_PICKED_UP_BY_DISTRIBUTOR', 8, '2025-07-22 09:43:31', '{\"product_id\":4,\"distributor_product_id\":8,\"picked_up_by_actor_id\":8,\"timestamp\":\"2025-07-22 15:13:31\"}', 'd9ca2e9bc9a9f4d70e6dcff7fd0ddb0f223e738fd241943cc55ee4d09f95aae8', '2cab2a60fe48d24e10e90b9fd2d59af9ae1789410c6f38163d065480712ab830');

-- --------------------------------------------------------

--
-- Table structure for table `distributor_products`
--

CREATE TABLE `distributor_products` (
  `distributor_product_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `distributor_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `dispensed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `pickup_status` enum('pending','picked_up','delivered') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `distributor_products`
--

INSERT INTO `distributor_products` (`distributor_product_id`, `product_id`, `distributor_id`, `quantity`, `dispensed_at`, `pickup_status`) VALUES
(1, 1, 3, 8, '2025-07-20 18:10:19', 'picked_up'),
(2, 1, 3, 10, '2025-07-20 18:59:36', 'picked_up'),
(3, 1, 3, 50, '2025-07-20 19:18:00', 'picked_up'),
(4, 2, 3, 10, '2025-07-20 19:44:18', 'picked_up'),
(5, 3, 3, 50, '2025-07-20 22:07:20', 'picked_up'),
(6, 3, 3, 22, '2025-07-21 01:38:14', 'picked_up'),
(7, 4, 3, 30, '2025-07-21 05:16:47', 'picked_up'),
(8, 4, 8, 12, '2025-07-21 13:41:39', 'picked_up');

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `attempt_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `attempt_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `success` tinyint(1) DEFAULT 0,
  `failure_reason` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_attempts`
--

INSERT INTO `login_attempts` (`attempt_id`, `email`, `ip_address`, `attempt_time`, `success`, `failure_reason`) VALUES
(1, 'shrikantchavan0003@gmail.com', '127.0.0.1', '2025-07-21 13:37:05', 0, 'Account not verified'),
(2, 'shrikantchavan0003@gmail.com', '127.0.0.1', '2025-07-21 13:39:06', 1, NULL),
(3, 'shrikantchavan0003@gmail.com', '127.0.0.1', '2025-07-21 13:40:17', 1, NULL),
(4, 'shrikantchavan0003@gmail.com', '127.0.0.1', '2025-07-21 14:12:02', 1, NULL),
(5, 'shrikantchavan0003@gmail.com', '::1', '2025-07-21 14:16:00', 1, NULL),
(6, 'shrikantchavan0003@gmail.com', '::1', '2025-07-21 14:28:00', 1, NULL),
(7, 'shrikantchavan0003@gmail.com', '127.0.0.1', '2025-07-21 14:31:15', 1, NULL),
(8, 'shrikantchavan0003@gmail.com', '::1', '2025-07-21 14:33:45', 1, NULL),
(9, 'shrikantchavan0003@gmail.com', '::1', '2025-07-21 14:37:48', 1, NULL),
(10, 'shrikantchavan0003@gmail.com', '::1', '2025-07-21 14:44:25', 1, NULL),
(11, 'shrikantchavan0003@gmail.com', '127.0.0.1', '2025-07-21 14:45:47', 1, NULL),
(12, 'shrikantchavan0003@gmail.com', '127.0.0.1', '2025-07-21 14:47:59', 1, NULL),
(13, 'shrikantchavan0003@gmail.com', '127.0.0.1', '2025-07-21 14:51:42', 1, NULL),
(14, 'shrikantchavan0003@gmail.com', '127.0.0.1', '2025-07-21 14:55:22', 1, NULL),
(15, 'shrikantchavan0003@gmail.com', '127.0.0.1', '2025-07-21 15:04:09', 1, NULL),
(16, 'shrikantchavan0003@gmail.com', '127.0.0.1', '2025-07-21 15:13:11', 1, NULL),
(17, 'shrikantchavan0003@gmail.com', '127.0.0.1', '2025-07-21 15:20:13', 1, NULL),
(18, 'shrikantchavan0003@gmail.com', '127.0.0.1', '2025-07-21 15:22:05', 1, NULL),
(19, 'rajhakati20@gmail.com', '::1', '2025-07-21 17:27:10', 1, NULL),
(20, 'shrikantchavan0003@gmail.com', '::1', '2025-07-21 17:28:57', 1, NULL),
(21, 'shrikantchavan0003@gmail.com', '::1', '2025-07-21 17:33:09', 1, NULL),
(22, 'shrikantchavan0003@gmail.com', '127.0.0.1', '2025-07-21 17:36:51', 1, NULL),
(23, 'shrikantchavan0003@gmail.com', '127.0.0.1', '2025-07-21 17:39:00', 0, 'Invalid OTP'),
(24, 'shrikantchavan0003@gmail.com', '127.0.0.1', '2025-07-21 17:39:12', 1, NULL),
(25, 'rajhakati20@gmail.com', '127.0.0.1', '2025-07-21 17:40:27', 1, NULL),
(26, 'shrikantchavan0003@gmail.com', '127.0.0.1', '2025-07-21 17:41:40', 1, NULL),
(27, 'chickya009@gmail.com', '127.0.0.1', '2025-07-21 17:43:35', 1, NULL),
(28, 'chickya009@gmail.com', '::1', '2025-07-21 18:32:35', 1, NULL),
(29, 'chickya009@gmail.com', '::1', '2025-07-21 18:48:43', 1, NULL),
(30, 'chickya009@gmail.com', '127.0.0.1', '2025-07-21 18:57:54', 1, NULL),
(31, 'chickya009@gmail.com', '127.0.0.1', '2025-07-21 19:04:10', 1, NULL),
(32, 'chickya009@gmail.com', '127.0.0.1', '2025-07-21 19:12:28', 1, NULL),
(33, 'chickya009@ggmail.com', '127.0.0.1', '2025-07-21 19:20:31', 0, 'User not found'),
(34, 'chickya009@gmail.com', '127.0.0.1', '2025-07-21 19:21:07', 1, NULL),
(35, 'chickya009@gmail.com', '127.0.0.1', '2025-07-21 19:43:51', 1, NULL),
(36, 'shrikantchavan0003@gmail.com', '127.0.0.1', '2025-07-21 20:01:57', 1, NULL),
(37, 'shrikantchavan0003@gmzail.com', '127.0.0.1', '2025-07-22 02:36:38', 0, 'User not found'),
(38, 'shrikantchavan0003@gmail.com', '127.0.0.1', '2025-07-22 02:37:04', 1, NULL),
(39, 'chickya009@gmail.com', '127.0.0.1', '2025-07-22 02:38:06', 0, 'Invalid OTP'),
(40, 'chickya009@gmail.com', '127.0.0.1', '2025-07-22 02:38:26', 1, NULL),
(41, 'chickya009@gmail.com', '127.0.0.1', '2025-07-22 08:08:13', 1, NULL),
(42, 'chickya009@gmail.com', '127.0.0.1', '2025-07-22 09:14:37', 1, NULL),
(43, 'shrikantchavan0003@gmail.com', '127.0.0.1', '2025-07-22 09:38:25', 1, NULL),
(44, 'chinmaymahajan83@gmail.com', '127.0.0.1', '2025-07-22 09:43:23', 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `priority` enum('Normal','Urgent') NOT NULL DEFAULT 'Normal',
  `message_content` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_status` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`message_id`, `sender_id`, `recipient_id`, `subject`, `priority`, `message_content`, `timestamp`, `read_status`) VALUES
(1, 4, 1, 'urgent stock request', 'Normal', 'ok', '2025-07-20 21:04:36', 1),
(2, 4, 1, 'i want', 'Urgent', 'omeez 200 quanttity', '2025-07-20 21:15:50', 1),
(3, 4, 3, 'jaldi aa', 'Normal', 'sale', '2025-07-20 21:18:38', 1),
(4, 4, 3, 'ok', 'Urgent', 'ok', '2025-07-20 21:24:06', 1),
(5, 4, 1, 'ok', 'Urgent', 'ok', '2025-07-20 22:02:12', 1),
(6, 4, 1, 'ok', 'Urgent', 'tthank you', '2025-07-20 22:14:12', 1),
(7, 4, 1, 'i want tablate', 'Urgent', 'vittamin e 20 mg', '2025-07-20 22:35:02', 1),
(8, 4, 1, 'i want', 'Normal', 'vvicks 20mg', '2025-07-20 22:35:53', 1),
(9, 4, 3, 'ok', 'Normal', 'thhanks', '2025-07-21 01:52:03', 1),
(10, 4, 1, 'product', 'Urgent', 'recieved', '2025-07-21 05:18:13', 1),
(11, 4, 3, 'product', 'Urgent', 'recieved', '2025-07-21 05:18:29', 1),
(12, 4, 1, 'i waant kproduct', 'Urgent', 'dolo 200mg', '2025-07-21 10:20:47', 1),
(13, 4, 1, 'urgent ', 'Urgent', 'omee tablet i want ', '2025-07-21 10:28:34', 1),
(14, 9, 8, 'hi', 'Urgent', 'hi', '2025-07-21 14:48:51', 1),
(15, 9, 8, 'message ', 'Urgent', 'how are you;', '2025-07-22 09:39:15', 1);

-- --------------------------------------------------------

--
-- Table structure for table `pharmacist_products`
--

CREATE TABLE `pharmacist_products` (
  `pharmacist_product_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `pharmacist_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `received_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pharmacist_products`
--

INSERT INTO `pharmacist_products` (`pharmacist_product_id`, `product_id`, `pharmacist_id`, `quantity`, `received_at`) VALUES
(6, 1, 4, 18, '2025-07-20 19:38:18'),
(7, 2, 4, 2, '2025-07-20 19:45:02'),
(8, 2, 4, 5, '2025-07-20 20:07:28'),
(17, 4, 4, 5, '2025-07-21 05:17:19'),
(19, 1, 9, 2, '2025-07-21 17:40:51');

-- --------------------------------------------------------

--
-- Table structure for table `pharmacist_received_history`
--

CREATE TABLE `pharmacist_received_history` (
  `history_id` int(11) NOT NULL,
  `pharmacist_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity_received` int(11) NOT NULL,
  `received_from_distributor_id` int(11) NOT NULL,
  `received_timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pharmacist_received_history`
--

INSERT INTO `pharmacist_received_history` (`history_id`, `pharmacist_id`, `product_id`, `quantity_received`, `received_from_distributor_id`, `received_timestamp`) VALUES
(1, 4, 1, 30, 3, '2025-07-20 19:38:18'),
(2, 4, 2, 5, 3, '2025-07-20 19:45:02'),
(3, 4, 2, 5, 3, '2025-07-20 20:07:28'),
(4, 4, 3, 20, 3, '2025-07-20 22:08:42'),
(5, 4, 3, 20, 3, '2025-07-21 01:38:54'),
(6, 4, 3, 5, 3, '2025-07-21 01:46:07'),
(7, 4, 3, 10, 3, '2025-07-21 01:46:27'),
(8, 4, 3, 3, 3, '2025-07-21 01:50:16'),
(9, 4, 3, 2, 3, '2025-07-21 01:51:38'),
(10, 4, 3, 2, 3, '2025-07-21 02:43:30'),
(11, 4, 3, 5, 3, '2025-07-21 02:53:05'),
(12, 4, 4, 20, 3, '2025-07-21 05:17:19'),
(13, 9, 4, 2, 8, '2025-07-21 14:52:26'),
(14, 9, 1, 10, 8, '2025-07-21 17:40:51');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `unique_identifier` varchar(255) NOT NULL,
  `manufacturer_id` int(11) NOT NULL,
  `brand_name` varchar(255) NOT NULL,
  `generic_name` varchar(255) NOT NULL,
  `batch_number` varchar(100) NOT NULL,
  `product_code_sku` varchar(100) DEFAULT NULL,
  `manufacturer_name` varchar(255) NOT NULL,
  `manufacturing_license_number` varchar(100) DEFAULT NULL,
  `country_of_origin` varchar(100) DEFAULT NULL,
  `manufacturing_date` date NOT NULL,
  `expiry_date` date NOT NULL,
  `active_ingredients` text DEFAULT NULL,
  `excipients` text DEFAULT NULL,
  `formulation_type` varchar(100) DEFAULT NULL,
  `strength` varchar(100) DEFAULT NULL,
  `dosage_instructions` text DEFAULT NULL,
  `route_of_administration` varchar(100) DEFAULT NULL,
  `drug_license_number` varchar(100) DEFAULT NULL,
  `approval_authority` varchar(100) DEFAULT NULL,
  `approval_date` date DEFAULT NULL,
  `storage_conditions` varchar(255) DEFAULT NULL,
  `shelf_life` varchar(100) DEFAULT NULL,
  `mrp` decimal(10,2) DEFAULT NULL,
  `pack_size` varchar(100) DEFAULT NULL,
  `packaging_type` varchar(100) DEFAULT NULL,
  `therapeutic_category` varchar(255) DEFAULT NULL,
  `indications` text DEFAULT NULL,
  `contraindications` text DEFAULT NULL,
  `side_effects` text DEFAULT NULL,
  `precautions` text DEFAULT NULL,
  `interactions` text DEFAULT NULL,
  `stock_quantity` int(11) NOT NULL,
  `reorder_level` int(11) DEFAULT NULL,
  `supplier_name` varchar(255) DEFAULT NULL,
  `registration_timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `unique_identifier`, `manufacturer_id`, `brand_name`, `generic_name`, `batch_number`, `product_code_sku`, `manufacturer_name`, `manufacturing_license_number`, `country_of_origin`, `manufacturing_date`, `expiry_date`, `active_ingredients`, `excipients`, `formulation_type`, `strength`, `dosage_instructions`, `route_of_administration`, `drug_license_number`, `approval_authority`, `approval_date`, `storage_conditions`, `shelf_life`, `mrp`, `pack_size`, `packaging_type`, `therapeutic_category`, `indications`, `contraindications`, `side_effects`, `precautions`, `interactions`, `stock_quantity`, `reorder_level`, `supplier_name`, `registration_timestamp`) VALUES
(1, 'med_687cf09b8e2802.81804718', 1, 'n', 'n', '12', '', 'Default Pharma Inc.', '', '', '2025-07-20', '2025-07-26', '', '', 'Tablet', '', '', '', '', '', '2025-07-20', '', '', 120.00, '', '', '', '', '', '', '', '', 35, 2, 'ok', '2025-07-20 13:35:23'),
(2, 'med_687d46e59a11c4.01980900', 1, 'omee', 'omeez', '21', '', 'Default Pharma Inc.', '', '', '2025-07-21', '2025-08-02', '', '', 'Tablet', '', '', '', '', '', '2025-07-21', '', '', 140.00, '', '', '', '', '', '', '', '', 10, 1, 'city', '2025-07-20 19:43:33'),
(3, 'med_687d685b139d90.00613767', 1, 'dolo', 'dolo', '33', '', 'Default Pharma Inc.', '', '', '2025-07-21', '2025-08-09', '', '', 'Tablet', '', '', '', '', '', '2025-07-21', '', '', 100.00, '', '', '', '', '', '', '', '', 48, 1, 'ok', '2025-07-20 22:06:19'),
(4, 'med_687dccf2a04a26.61748932', 1, 'final', 'final', '111', '', 'Default Pharma Inc.', '', '', '2025-07-21', '2025-08-09', '', '', 'Tablet', '', '', '', '', '', '2025-07-21', '', '', 120.00, '', '', '', '', '', '', '', '', 9, 1, 'chickoo', '2025-07-21 05:15:30');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('manufacturer','distributor','pharmacist','patient','admin','hospital') NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `otp` varchar(10) DEFAULT NULL,
  `otp_expiry` datetime DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password_hash`, `role`, `full_name`, `email`, `otp`, `otp_expiry`, `phone`, `is_verified`, `created_at`, `last_login`) VALUES
(1, 'admin', '$2y$10$E.qA8c2J5C1j.yK9s4zL9eH2/d6L2s5n8a.rG8n3bX7zK4v.O1w.q', 'admin', 'System Administrator', 'admin@medchain.com', NULL, NULL, NULL, 0, '2025-07-21 13:18:22', NULL),
(2, 'manufacturer_a', '$2y$10$iP/k/wX8N.gC5.zG7hJ6j.X8r/a9e.fG4n.H2o.I1p.T5q.L3o.E4', 'manufacturer', 'Default Pharma Inc.', 'manufacturer@medchain.com', NULL, NULL, NULL, 0, '2025-07-21 13:18:22', NULL),
(3, 'distributor_x', '$2y$10$o.L8r.a9e.fG4n.H2o.I1p.T5q.L3o.E4iP/k/wX8N.gC5.zG7hJ6j', 'distributor', 'National Distributors', 'distributor@medchain.com', NULL, NULL, NULL, 0, '2025-07-21 13:18:22', NULL),
(4, 'pharmacist_y', '$2y$10$zG7hJ6j.X8r/a9e.fG4n.H2o.I1p.T5q.L3o.E4iP/k/wX8N.gC5.o', 'pharmacist', 'City Pharmacy', 'pharmacist@medchain.com', NULL, NULL, NULL, 0, '2025-07-21 13:18:22', NULL),
(5, 'chickya', '$2y$10$nPkjyFBi1AhdR0nRz6of2.8Y0/M07fpv9W9UPZkBZyfbeQeybASgi', 'patient', 'CHICKYA', 'chickya@medchain.com', NULL, NULL, NULL, 0, '2025-07-21 13:18:22', NULL),
(6, 'GAUTAM', '$2y$10$buNGklpWgfNl7RGoSdllMOiY/VENTuvaCjz3t6ERuiPOCgrutsu/2', 'patient', 'MR GAUTAM', 'gautam@medchain.com', NULL, NULL, NULL, 0, '2025-07-21 13:18:22', NULL),
(8, 'Hanamant', '$2y$10$K39N1cmtcFruo1X2vEOHmOk4C5B/XJJVXs5efx2yEQV3whgv7nNgO', 'distributor', 'Hanamant', 'kcdianconfession@gmail.com', NULL, NULL, '9623967324', 1, '2025-07-21 13:37:52', '2025-07-22 09:43:23'),
(9, 'hubli_medical', '$2y$10$k1xFN5CRzw9I/9gPrJFnSuxhUQV/rp3.8ea5yMfSqfaLWgqbakJYu', 'pharmacist', 'chickoo', 'rajashekhardurgad10@gmail.com', NULL, NULL, '9741898651', 1, '2025-07-21 14:36:38', '2025-07-22 09:38:25'),
(11, 'patient', '$2y$10$/IWgr.pK/PPov22ZPhMuNehIS5PR0FhOlCPx.z7ZnuI7ewhASj/1O', 'patient', 'patient', 'chickya009@gmail.com', NULL, NULL, '9966332255', 1, '2025-07-21 15:03:08', '2025-07-22 09:14:37'),
(12,'Apollo Hospital','$2y$10$Tv.xOQ3rSyyJ.lTXt33qLuZ4N/A0V14J3XmzOeza0V5b3reGu6UlO','hospital','Apollo Hospital','rajashekhardurgad01@gmail.com',NULL,NULL,'9876543210',1,'2025-08-04 14:13:02','');

-- --------------------------------------------------------

--
-- Table structure for table `user_profiles`
--

CREATE TABLE `user_profiles` (
  `profile_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `address` text DEFAULT NULL,
  `license_number` varchar(100) DEFAULT NULL,
  `organization_name` varchar(255) DEFAULT NULL,
  `specialization` varchar(255) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_profiles`
--

INSERT INTO `user_profiles` (`profile_id`, `user_id`, `address`, `license_number`, `organization_name`, `specialization`, `profile_image`, `status`, `created_at`, `updated_at`) VALUES
(1, 8, NULL, NULL, 'ok', NULL, NULL, 'active', '2025-07-21 13:39:06', '2025-07-21 13:39:06'),
(2, 9, NULL, NULL, 'ok', NULL, NULL, 'active', '2025-07-21 14:37:48', '2025-07-21 14:37:48'),
(3, 11, NULL, NULL, 'ok', NULL, NULL, 'active', '2025-07-21 15:04:09', '2025-07-21 15:04:09');

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `session_id` varchar(128) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` varchar(50) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_sessions`
--

INSERT INTO `user_sessions` (`session_id`, `user_id`, `role`, `ip_address`, `user_agent`, `created_at`, `expires_at`, `is_active`) VALUES
('0125c5f8dabe532ce5842e53531dfd8247160252d670c9a2be020931b7412d23', 8, 'distributor', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-07-21 17:40:27', '2025-07-22 14:10:27', 0),
('04fe7604472797128b09e03c1a8b1463a2a568c2e451d5794a7c70fec38c2d76', 11, 'patient', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-07-21 17:43:35', '2025-07-22 14:13:35', 1),
('0c969a3477884b267be02eee19c9f7771d463f2300a745ad8e4e84a48d90bc44', 8, 'distributor', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-07-21 13:39:06', '2025-07-22 10:09:06', 1),
('0dd8fd16b9b1ed75f3a9da4090ab9d2a8cca24f52becc7cc3fcd90996d12e5da', 11, 'patient', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-07-22 09:14:37', '2025-07-23 05:44:37', 0),
('10ed8cc49b540424ee4ef4d9135b63a7ba68b986e4f01741057ffeab24cfa756', 11, 'patient', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-07-21 15:22:05', '2025-07-22 11:52:05', 1),
('15d1a5cc6b875376a1b5cc8ce8c4d5870a909d88660755ff5c6558dc76e7dae5', 9, 'pharmacist', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-07-21 17:39:12', '2025-07-22 14:09:12', 1),
('1e842d2046558aef2ef96f904d6e35540ce31310ce77f2cdbc945a7ee99f52ab', 11, 'patient', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-21 17:33:09', '2025-07-22 14:03:09', 1),
('1fa31b9c036c1427e77b607033f1ae3cf02d7661bdce3dcfbfc7f693a2937eb4', 8, 'distributor', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-07-21 13:40:17', '2025-07-22 10:10:17', 1),
('29828e7999b6a576488c3abd0ca692fbd95a85b0d3b399bdb70f5912e0a2a92b', 11, 'patient', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-07-22 08:08:13', '2025-07-23 04:38:13', 0),
('2c477aa9f4bc19b1ad20c1ec34581049a59f5fadbc98166e766c5b293ec20d5e', 9, 'pharmacist', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-21 14:37:48', '2025-07-22 11:07:48', 1),
('34145cd9d72d499e309b11113993a0b68f7ae008d6a275ab8af1bd539a54e13b', 9, 'pharmacist', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-07-21 15:20:13', '2025-07-22 11:50:13', 0),
('5ada17f522cfdbe3ac38d893222bc67cd7e75972dc04df4a2be4403c5767f191', 11, 'patient', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-07-21 17:36:51', '2025-07-22 14:06:51', 1),
('5b6c609ee6c3283d8a1ad6ce011b3a23524e345a2d84984e503c0bbf284309d4', 8, 'distributor', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-07-21 14:31:15', '2025-07-22 11:01:15', 1),
('5fa8aae3f021ade8d7b6b141a3b4ee7ae9320dfc9a9f4753ef2a8ba3ff8d0b55', 11, 'patient', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-21 18:48:43', '2025-07-22 15:18:43', 1),
('5fe4f3c2b88772daaafd86cee672c03c223944c88fb8245538c41ef9cd861022', 8, 'distributor', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-21 14:16:00', '2025-07-22 10:46:00', 1),
('65061c0ba620f40245abcf536fa16e64248c27800cbe449fc58f56affb3f1098', 11, 'patient', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-07-21 18:57:54', '2025-07-22 15:27:54', 1),
('7365f1bebd7d02e36d52c46202a505ff216b92e6138e067479178e9ea615f992', 9, 'pharmacist', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-07-22 09:38:25', '2025-07-23 06:08:25', 0),
('8209029b4f4621bb8b4c04fbcb0f310ba5038cbbbd5fce513eac58cc8f01ecd2', 9, 'pharmacist', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-21 14:44:25', '2025-07-22 11:14:25', 1),
('873ee396923851df4c7e263b9ea5d9a12c9b02a74e0a05dfbc3ff0f2fbbd4961', 8, 'distributor', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-07-22 09:43:23', '2025-07-23 06:13:23', 0),
('9464451b977aa0ea2dde7895a0234286399400f1d221b882aa23c00c0d277a48', 8, 'distributor', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-21 14:28:00', '2025-07-22 10:58:00', 1),
('95bb660f989879f62c6bb842d8b475dba9b58e3d447b1a60f12f94adf494813a', 11, 'patient', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-21 18:32:35', '2025-07-22 15:02:35', 0),
('9cf80b08ed4e32b22976cf46b5a4a5c0bb44ba3a6011d5ada571ef2e7317d04d', 9, 'pharmacist', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-07-21 14:47:59', '2025-07-22 11:17:59', 0),
('a43ff969bde333b858f15c2d6427f4b0fbd9ffcabcd92c4555687d7d3e646cc3', 11, 'patient', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-07-21 15:04:09', '2025-07-22 11:34:09', 1),
('ac17bf1bb719120c1edfe0e315e17aa88e002c84fc3fafa23d1220707340dbef', 11, 'patient', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-07-21 19:12:28', '2025-07-22 15:42:28', 1),
('acf0fc0d72efcee5abfd13a7aa0a97dc3c96d8cebe7610fb55517d0cf1aaa4e0', 11, 'patient', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-07-21 15:13:11', '2025-07-22 11:43:11', 1),
('b055cc82d5d0ca04cc952d8ba6de706a1f5b75a035e7ecc350189532cd09c3a9', 11, 'patient', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-07-21 19:43:51', '2025-07-22 16:13:51', 0),
('b8009c8c3dc61c641a11bcd3b2ad48dc5f64fe90819ebc2a7ffb2b8337667ff8', 11, 'patient', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-07-22 02:38:26', '2025-07-22 23:08:26', 1),
('b88b8777caa98a0f1eefa247af681efa6566f6d22bb4f4cdf35a6e2ccf04ff56', 11, 'patient', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-07-21 19:04:10', '2025-07-22 15:34:10', 1),
('b8a424b0a3fb8fc51f125040f436d5293533c460b4f5561d49f72a2025c4f69e', 9, 'pharmacist', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-07-22 02:37:04', '2025-07-22 23:07:04', 0),
('c78e9f7286875b4774dbd7a5f24a48365335c45c3bef3ce21548f664169a2ed9', 8, 'distributor', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-21 14:33:45', '2025-07-22 11:03:45', 0),
('ca29ac7b80b6405bbb17fafab05669f51461c44e7c2520c740092326b62c7e24', 8, 'distributor', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-21 17:27:10', '2025-07-22 13:57:10', 0),
('cdb2b72d7fc4e0f410f1ac71675e0be700a3c34967356f46809a19908746e356', 8, 'distributor', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-07-21 14:51:42', '2025-07-22 11:21:42', 1),
('d1799468744afb776353d16c2d3c64fe660325410b79b506f6f4c12b58079e5f', 9, 'pharmacist', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-07-21 17:41:40', '2025-07-22 14:11:40', 1),
('dc5bd23cd0eb15220b63f0b528692dead70d73c6e5f3e09ff52e6c63d9c3d0bc', 9, 'pharmacist', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-07-21 20:01:56', '2025-07-22 16:31:56', 0),
('de8bdb9d7226aa0ba991ecf8a4c03911d74cde850c1a76b07d6d326f754e7601', 11, 'patient', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-07-21 19:21:07', '2025-07-22 15:51:07', 1),
('e7cb678e230ad6feb32d6b77e79a6fb6eeaf01d3209b1af6ce2d1683aca554e1', 9, 'pharmacist', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-07-21 14:55:22', '2025-07-22 11:25:22', 0),
('e98c6b06efc75591807a63bd4a253fd3790f124a4a9f7af7d2f1ffb771966f8a', 8, 'distributor', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-07-21 14:12:02', '2025-07-22 10:42:02', 1),
('fbed097f081c7d01c9588b021301d5cb6dc3b94d86bdc4b6e47e7c00d1669d28', 11, 'patient', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-21 17:28:57', '2025-07-22 13:58:57', 1),
('ff08c46828c0acf2f1df6d0272820dabac8bb479110a309aabd6b07a8ac18850', 9, 'pharmacist', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-07-21 14:45:47', '2025-07-22 11:15:47', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_trail`
--
ALTER TABLE `audit_trail`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `actor_id` (`actor_id`);

--
-- Indexes for table `distributor_products`
--
ALTER TABLE `distributor_products`
  ADD PRIMARY KEY (`distributor_product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`attempt_id`),
  ADD KEY `idx_login_attempts_email` (`email`),
  ADD KEY `idx_login_attempts_ip` (`ip_address`),
  ADD KEY `idx_login_attempts_time` (`attempt_time`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `recipient_id` (`recipient_id`);

--
-- Indexes for table `pharmacist_products`
--
ALTER TABLE `pharmacist_products`
  ADD PRIMARY KEY (`pharmacist_product_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `pharmacist_id` (`pharmacist_id`);

--
-- Indexes for table `pharmacist_received_history`
--
ALTER TABLE `pharmacist_received_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `pharmacist_id` (`pharmacist_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD UNIQUE KEY `unique_identifier` (`unique_identifier`),
  ADD KEY `manufacturer_id` (`manufacturer_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_email` (`email`),
  ADD KEY `idx_users_role` (`role`);

--
-- Indexes for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`profile_id`),
  ADD UNIQUE KEY `unique_user_profile` (`user_id`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `idx_user_sessions_user_id` (`user_id`),
  ADD KEY `idx_user_sessions_expires` (`expires_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_trail`
--
ALTER TABLE `audit_trail`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `distributor_products`
--
ALTER TABLE `distributor_products`
  MODIFY `distributor_product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `attempt_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `pharmacist_products`
--
ALTER TABLE `pharmacist_products`
  MODIFY `pharmacist_product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `pharmacist_received_history`
--
ALTER TABLE `pharmacist_received_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `user_profiles`
--
ALTER TABLE `user_profiles`
  MODIFY `profile_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `distributor_products`
--
ALTER TABLE `distributor_products`
  ADD CONSTRAINT `distributor_products_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `pharmacist_products`
--
ALTER TABLE `pharmacist_products`
  ADD CONSTRAINT `pharmacist_products_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pharmacist_products_ibfk_2` FOREIGN KEY (`pharmacist_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
CREATE TABLE `hospital_products` (
  `hospital_product_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `hospital_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `received_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`hospital_product_id`),
  KEY `product_id` (`product_id`),
  KEY `hospital_id` (`hospital_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `hospital_received_history` (
  `history_id` int(11) NOT NULL AUTO_INCREMENT,
  `hospital_product_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `received_from_actor_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `received_timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `details` text DEFAULT NULL,
  PRIMARY KEY (`history_id`),
  KEY `hospital_product_id` (`hospital_product_id`),
  KEY `product_id` (`product_id`),
  KEY `received_from_actor_id` (`received_from_actor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
