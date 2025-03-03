/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 100424
 Source Host           : localhost:3306
 Source Schema         : dbscan

 Target Server Type    : MySQL
 Target Server Version : 100424
 File Encoding         : 65001

 Date: 03/03/2025 15:43:44
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for clustering_results
-- ----------------------------
DROP TABLE IF EXISTS `clustering_results`;
CREATE TABLE `clustering_results`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cluster_count` int(11) NOT NULL,
  `epsilon` float NOT NULL,
  `min_samples` int(11) NULL DEFAULT NULL,
  `min_points` int(11) NOT NULL,
  `data_points` int(11) NOT NULL,
  `outliers` int(11) NOT NULL,
  `execution_time` float NOT NULL,
  `date_generated` timestamp(0) NOT NULL DEFAULT current_timestamp(0),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 114 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of clustering_results
-- ----------------------------
INSERT INTO `clustering_results` VALUES (5, 3, 0, NULL, 0, 0, 0, 0, '2024-10-11 10:54:44');
INSERT INTO `clustering_results` VALUES (6, 2, 0, NULL, 0, 0, 0, 0, '2024-10-11 10:55:45');
INSERT INTO `clustering_results` VALUES (7, 1, 0, NULL, 0, 0, 0, 0, '2024-10-11 11:31:35');
INSERT INTO `clustering_results` VALUES (8, 1, 0, NULL, 0, 0, 0, 0, '2024-10-11 11:35:40');
INSERT INTO `clustering_results` VALUES (9, 1, 0, NULL, 0, 0, 0, 0, '2024-10-11 11:35:56');
INSERT INTO `clustering_results` VALUES (10, 1, 0, NULL, 0, 0, 0, 0, '2024-10-11 12:36:45');
INSERT INTO `clustering_results` VALUES (11, 1, 0, NULL, 0, 0, 0, 0, '2024-10-11 12:37:52');
INSERT INTO `clustering_results` VALUES (12, 1, 0, NULL, 0, 0, 0, 0, '2024-10-11 12:38:04');
INSERT INTO `clustering_results` VALUES (13, 1, 0, NULL, 0, 0, 0, 0, '2024-10-11 12:41:03');
INSERT INTO `clustering_results` VALUES (14, 1, 0, NULL, 0, 0, 0, 0, '2024-10-11 12:44:49');
INSERT INTO `clustering_results` VALUES (15, 1, 0, NULL, 0, 0, 0, 0, '2024-10-11 12:46:19');
INSERT INTO `clustering_results` VALUES (16, 1, 0, NULL, 0, 0, 0, 0, '2024-10-11 12:46:33');
INSERT INTO `clustering_results` VALUES (17, 2, 0, NULL, 0, 0, 0, 0, '2024-10-11 12:46:45');
INSERT INTO `clustering_results` VALUES (18, 1, 1.5, 3, 3, 31, 0, 0.074, '2024-11-25 12:53:15');
INSERT INTO `clustering_results` VALUES (19, 1, 1.2, 5, 5, 31, 0, 0.0749, '2024-11-25 12:54:44');
INSERT INTO `clustering_results` VALUES (20, 1, 0.5, 5, 5, 31, 0, 0.074, '2024-11-25 12:55:04');
INSERT INTO `clustering_results` VALUES (21, 9, 0.1, 1, 1, 31, 9, 0.0695, '2024-11-25 12:55:17');
INSERT INTO `clustering_results` VALUES (22, 2, 0.2, 5, 5, 31, 8, 0.0737, '2024-11-25 12:55:46');
INSERT INTO `clustering_results` VALUES (23, 1, 0.5, 3, 3, 31, 0, 0.0807, '2024-11-25 12:57:42');
INSERT INTO `clustering_results` VALUES (24, 1, 2, 3, 3, 31, 0, 0.0722, '2024-11-25 12:57:54');
INSERT INTO `clustering_results` VALUES (25, 1, 2, 1, 1, 31, 0, 0.1017, '2024-11-25 12:58:02');
INSERT INTO `clustering_results` VALUES (26, 1, 0.9, 8, 8, 31, 0, 0.0706, '2024-11-25 12:58:15');
INSERT INTO `clustering_results` VALUES (27, 1, 0.9, 1, 1, 31, 0, 0.0734, '2024-11-25 12:58:21');
INSERT INTO `clustering_results` VALUES (28, 1, 5, 1, 1, 31, 0, 0.0744, '2024-11-25 12:58:30');
INSERT INTO `clustering_results` VALUES (29, 1, 0.5, 3, 3, 31, 0, 0.0753, '2024-11-25 12:58:55');
INSERT INTO `clustering_results` VALUES (30, 2, 0.2, 3, 3, 31, 5, 0.0731, '2024-11-25 12:59:03');
INSERT INTO `clustering_results` VALUES (31, 1, 0.2, 3, 3, 3, 0, 0.0085, '2024-11-25 13:00:19');
INSERT INTO `clustering_results` VALUES (32, 1, 0.2, 3, 3, 4, 0, 0.0127, '2024-11-25 13:01:11');
INSERT INTO `clustering_results` VALUES (33, 1, 0.1, 3, 3, 4, 0, 0.0117, '2024-11-25 13:01:46');
INSERT INTO `clustering_results` VALUES (34, 2, 0.3, 3, 3, 31, 0, 0.0755, '2024-11-25 13:02:00');
INSERT INTO `clustering_results` VALUES (35, 2, 0.3, 2, 2, 31, 0, 0.0759, '2024-11-25 13:02:19');
INSERT INTO `clustering_results` VALUES (36, 1, 0.2, 3, 3, 4, 0, 0.0119, '2024-11-25 13:03:25');
INSERT INTO `clustering_results` VALUES (37, 1, 0.2, 9, 9, 4, 0, 0.0117, '2024-11-25 13:03:45');
INSERT INTO `clustering_results` VALUES (38, 1, 0, 3, 3, 4, 0, 0.0124, '2024-11-25 13:04:15');
INSERT INTO `clustering_results` VALUES (39, 1, 0, 1, 1, 4, 0, 0.0121, '2024-11-25 13:04:33');
INSERT INTO `clustering_results` VALUES (40, 1, 0.2, 3, 3, 4, 0, 0.0129, '2024-11-25 13:11:00');
INSERT INTO `clustering_results` VALUES (41, 1, 0.8, 3, 3, 4, 0, 0.0128, '2024-11-25 13:14:21');
INSERT INTO `clustering_results` VALUES (42, 1, 1.5, 3, 3, 31, 0, 0.0739, '2024-11-25 13:14:37');
INSERT INTO `clustering_results` VALUES (43, 1, 0.5, 3, 3, 31, 0, 0.0726, '2024-11-25 13:14:52');
INSERT INTO `clustering_results` VALUES (44, 2, 0.2, 3, 3, 31, 5, 0.0735, '2024-11-25 13:15:02');
INSERT INTO `clustering_results` VALUES (45, 1, 0, 1, 1, 4, 0, 0.0113, '2024-11-25 13:15:17');
INSERT INTO `clustering_results` VALUES (46, 1, 0.2, 3, 3, 7, 2, 0.0195, '2024-11-28 19:02:49');
INSERT INTO `clustering_results` VALUES (47, 1, 0.2, 3, 3, 10, 0, 0.3726, '2024-11-28 12:15:25');
INSERT INTO `clustering_results` VALUES (48, 1, 0.2, 3, 3, 11, 0, 0.4286, '2024-11-28 12:15:34');
INSERT INTO `clustering_results` VALUES (49, 1, 0.1, 2, 2, 10, 0, 0, '2024-11-28 12:15:57');
INSERT INTO `clustering_results` VALUES (50, 1, 0.1, 2, 2, 7, 2, 0.002, '2024-11-28 12:16:11');
INSERT INTO `clustering_results` VALUES (51, 1, 0.4, 3, 3, 7, 2, 0.003, '2024-11-28 12:16:31');
INSERT INTO `clustering_results` VALUES (52, 1, 0.1, 1, 1, 7, 2, 0.002, '2024-11-28 12:18:11');
INSERT INTO `clustering_results` VALUES (53, 1, 1, 2, 2, 7, 1, 0.1182, '2024-11-28 12:18:25');
INSERT INTO `clustering_results` VALUES (54, 1, 3, 2, 2, 10, 0, 0.004, '2024-11-28 12:18:43');
INSERT INTO `clustering_results` VALUES (55, 1, 3, 2, 2, 7, 0, 0.053, '2024-11-28 12:18:47');
INSERT INTO `clustering_results` VALUES (56, 1, 2, 1, 1, 10, 0, 0.004, '2024-11-28 12:19:01');
INSERT INTO `clustering_results` VALUES (57, 1, 2, 1, 1, 7, 0, 0.002, '2024-11-28 12:19:11');
INSERT INTO `clustering_results` VALUES (58, 1, 1.5, 1, 1, 7, 0, 0.002, '2024-11-28 12:19:21');
INSERT INTO `clustering_results` VALUES (59, 1, 1.5, 2, 2, 10, 0, 0.005, '2024-11-28 12:19:29');
INSERT INTO `clustering_results` VALUES (60, 1, 1.5, 2, 2, 7, 0, 0.002, '2024-11-28 12:19:34');
INSERT INTO `clustering_results` VALUES (61, 1, 0.8, 2, 2, 10, 0, 0.004, '2024-11-28 12:19:47');
INSERT INTO `clustering_results` VALUES (62, 1, 0.8, 2, 2, 7, 1, 0.028, '2024-11-28 12:19:55');
INSERT INTO `clustering_results` VALUES (63, 1, 0.2, 2, 2, 10, 0, 0.006, '2024-11-28 12:20:14');
INSERT INTO `clustering_results` VALUES (64, 1, 0.2, 2, 2, 10, 0, 0.004, '2024-11-28 12:22:35');
INSERT INTO `clustering_results` VALUES (65, 1, 0.2, 3, 3, 10, 3, 0.316, '2024-11-28 12:25:34');
INSERT INTO `clustering_results` VALUES (66, 1, 0.2, 3, 3, 11, 3, 0.495, '2024-11-28 12:25:52');
INSERT INTO `clustering_results` VALUES (67, 1, 0.2, 2, 2, 11, 3, 0.006, '2024-11-28 12:25:58');
INSERT INTO `clustering_results` VALUES (68, 1, 0.5, NULL, 2, 10, 1, 0.108, '2025-02-25 14:51:23');
INSERT INTO `clustering_results` VALUES (69, 1, 0.3, NULL, 5, 10, 3, 0.126, '2025-02-25 14:52:30');
INSERT INTO `clustering_results` VALUES (70, 1, 0.5, NULL, 7, 10, 3, 0.167, '2025-02-25 14:52:43');
INSERT INTO `clustering_results` VALUES (71, 2, 0.3, NULL, 1, 10, 1, 0.335, '2025-02-25 14:52:55');
INSERT INTO `clustering_results` VALUES (72, 1, 0.5, NULL, 1, 10, 1, 0.1966, '2025-02-25 14:53:18');
INSERT INTO `clustering_results` VALUES (73, 1, 0.1, NULL, 2, 10, 4, 0.136, '2025-02-25 14:53:27');
INSERT INTO `clustering_results` VALUES (74, 1, 0.2, NULL, 2, 10, 3, 0.099, '2025-02-25 14:53:35');
INSERT INTO `clustering_results` VALUES (75, 1, 0.5, NULL, 2, 10, 1, 0.1526, '2025-02-25 14:53:44');
INSERT INTO `clustering_results` VALUES (76, 1, 0.9, NULL, 2, 10, 0, 0.034, '2025-02-25 14:53:48');
INSERT INTO `clustering_results` VALUES (77, 1, 1, NULL, 2, 10, 0, 0.007, '2025-02-25 14:53:59');
INSERT INTO `clustering_results` VALUES (78, 1, 1, NULL, 1, 10, 0, 0.003, '2025-02-25 14:54:07');
INSERT INTO `clustering_results` VALUES (79, 1, 2, NULL, 1, 10, 0, 0.003, '2025-02-25 14:54:23');
INSERT INTO `clustering_results` VALUES (80, 1, 0.15, NULL, 2, 10, 3, 0.132, '2025-02-25 14:54:40');
INSERT INTO `clustering_results` VALUES (81, 1, 0.35, NULL, 2, 10, 3, 0.007, '2025-02-25 14:54:55');
INSERT INTO `clustering_results` VALUES (82, 1, 0.05, NULL, 2, 10, 6, 0.09, '2025-02-25 14:55:09');
INSERT INTO `clustering_results` VALUES (83, 1, 0.62, NULL, 2, 10, 1, 0.286, '2025-02-25 14:55:32');
INSERT INTO `clustering_results` VALUES (84, 2, 0.34, NULL, 1, 10, 1, 0.134, '2025-02-25 14:55:46');
INSERT INTO `clustering_results` VALUES (85, 1, 0.28, NULL, 6, 10, 3, 0.239, '2025-02-25 14:56:20');
INSERT INTO `clustering_results` VALUES (86, 1, 0.5, NULL, 1, 10, 1, 0.173, '2025-02-25 14:56:30');
INSERT INTO `clustering_results` VALUES (87, 1, 0.6, NULL, 1, 10, 1, 0.005, '2025-02-25 14:56:42');
INSERT INTO `clustering_results` VALUES (88, 1, 0.25, NULL, 2, 10, 3, 0.145, '2025-02-25 14:56:55');
INSERT INTO `clustering_results` VALUES (89, 1, 0.3, NULL, 7, 10, 0, 0.099, '2025-02-25 14:57:09');
INSERT INTO `clustering_results` VALUES (90, 2, 0.37, NULL, 1, 10, 1, 0.254, '2025-02-25 14:57:21');
INSERT INTO `clustering_results` VALUES (91, 1, 0.14, NULL, 1, 10, 3, 0.108, '2025-02-25 14:57:34');
INSERT INTO `clustering_results` VALUES (92, 1, 0.74, NULL, 1, 10, 1, 0.112, '2025-02-25 14:57:47');
INSERT INTO `clustering_results` VALUES (93, 2, 0.5, NULL, 0, 10, 0, 0.086, '2025-02-25 15:00:14');
INSERT INTO `clustering_results` VALUES (94, 2, 0.5, NULL, 0, 10, 0, 0.0156, '2025-02-25 15:00:53');
INSERT INTO `clustering_results` VALUES (95, 1, 0.5, NULL, 2, 10, 1, 0.032, '2025-02-25 15:01:04');
INSERT INTO `clustering_results` VALUES (96, 2, 0.5, NULL, 0, 10, 0, 0.0906, '2025-02-25 15:01:16');
INSERT INTO `clustering_results` VALUES (97, 5, 0.1, NULL, 0, 10, 0, 0.185, '2025-02-25 15:02:01');
INSERT INTO `clustering_results` VALUES (98, 4, 0.2, NULL, 0, 10, 0, 0.194, '2025-02-25 15:02:17');
INSERT INTO `clustering_results` VALUES (99, 3, 0.3, NULL, 0, 10, 0, 0.078, '2025-02-25 15:02:39');
INSERT INTO `clustering_results` VALUES (100, 3, 0.3, NULL, 0, 10, 0, 0, '2025-02-25 15:02:51');
INSERT INTO `clustering_results` VALUES (101, 2, 0.3, NULL, 1, 10, 1, 0.282, '2025-02-25 15:03:01');
INSERT INTO `clustering_results` VALUES (102, 4, 0.18, NULL, 0, 10, 0, 0.216, '2025-02-25 15:03:54');
INSERT INTO `clustering_results` VALUES (103, 4, 0.18, NULL, 0, 10, 0, 0.003, '2025-02-25 15:04:17');
INSERT INTO `clustering_results` VALUES (104, 1, 10, NULL, 5, 10, 0, 0.123, '2025-02-25 15:04:56');
INSERT INTO `clustering_results` VALUES (105, 2, 0.5414, NULL, 0, 10, 0, 0.1006, '2025-02-25 15:05:25');
INSERT INTO `clustering_results` VALUES (106, 1, 0.2, NULL, 3, 10, 3, 0.15, '2025-02-25 15:05:53');
INSERT INTO `clustering_results` VALUES (107, 2, 0.5, NULL, 0, 10, 0, 0.091, '2025-02-25 15:06:08');
INSERT INTO `clustering_results` VALUES (108, 1, 4, NULL, 3, 10, 0, 0.0936, '2025-02-25 15:06:33');
INSERT INTO `clustering_results` VALUES (109, 1, 0.5, NULL, 5, 10, 1, 0.031, '2025-02-25 15:06:48');
INSERT INTO `clustering_results` VALUES (110, 2, 0.5, NULL, 0, 10, 0, 0.05, '2025-02-25 15:06:58');
INSERT INTO `clustering_results` VALUES (111, 2, 0.5, NULL, 0, 10, 0, 0.003, '2025-02-25 15:07:29');
INSERT INTO `clustering_results` VALUES (112, 2, 0.5, NULL, 0, 11, 0, 0.152, '2025-02-25 15:07:34');
INSERT INTO `clustering_results` VALUES (113, 2, 0.5, NULL, 0, 7, 0, 0.109, '2025-02-25 15:07:39');

-- ----------------------------
-- Table structure for diabetes_data
-- ----------------------------
DROP TABLE IF EXISTS `diabetes_data`;
CREATE TABLE `diabetes_data`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wilayah` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `jumlah_penduduk` int(11) NOT NULL,
  `jumlah_penderita` int(11) NOT NULL,
  `jumlah_kematian` int(11) NOT NULL,
  `created_at` timestamp(0) NOT NULL DEFAULT current_timestamp(0),
  `cluster` int(11) NULL DEFAULT NULL,
  `tahun` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 66 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of diabetes_data
-- ----------------------------
INSERT INTO `diabetes_data` VALUES (38, 'Baktiya Barat', 15000, 1, 1, '2024-11-28 18:55:37', 1, 2021);
INSERT INTO `diabetes_data` VALUES (39, 'Banda Baro', 25000, 11, 2, '2024-11-28 18:55:57', 1, 2021);
INSERT INTO `diabetes_data` VALUES (40, 'Dewantara', 45000, 372, 50, '2024-11-28 18:56:09', 2, 2021);
INSERT INTO `diabetes_data` VALUES (41, 'Muara Batu', 35000, 48, 20, '2024-11-28 18:56:22', 1, 2021);
INSERT INTO `diabetes_data` VALUES (42, 'Nisam', 40000, 81, 20, '2024-11-28 18:56:33', 1, 2021);
INSERT INTO `diabetes_data` VALUES (43, 'Nisam Antara', 20000, 19, 4, '2024-11-28 18:56:52', 1, 2021);
INSERT INTO `diabetes_data` VALUES (44, 'Sawang', 22000, 35, 8, '2024-11-28 18:57:01', 1, 2021);
INSERT INTO `diabetes_data` VALUES (45, 'Banda Baro', 25000, 29, 9, '2024-11-28 18:57:24', 1, 2022);
INSERT INTO `diabetes_data` VALUES (46, 'Dewantara', 45000, 572, 100, '2024-11-28 18:57:37', 2, 2022);
INSERT INTO `diabetes_data` VALUES (47, 'Kuta Makmur', 18000, 5, 0, '2024-11-28 18:57:55', 1, 2022);
INSERT INTO `diabetes_data` VALUES (48, 'Muara Batu', 35000, 108, 34, '2024-11-28 18:58:09', 1, 2022);
INSERT INTO `diabetes_data` VALUES (49, 'Nisam', 40000, 259, 56, '2024-11-28 18:58:22', 1, 2022);
INSERT INTO `diabetes_data` VALUES (50, 'Nisam Antara', 20000, 17, 3, '2024-11-28 18:58:37', 1, 2022);
INSERT INTO `diabetes_data` VALUES (51, 'Sawang', 22000, 33, 7, '2024-11-28 18:58:49', 1, 2022);
INSERT INTO `diabetes_data` VALUES (52, 'Seunudon', 12000, 1, 0, '2024-11-28 18:59:04', 1, 2022);
INSERT INTO `diabetes_data` VALUES (53, 'Simpang Kramat', 15000, 1, 0, '2024-11-28 18:59:22', 1, 2022);
INSERT INTO `diabetes_data` VALUES (54, 'Syamtalira Aron', 16000, 1, 0, '2024-11-28 18:59:31', 1, 2022);
INSERT INTO `diabetes_data` VALUES (55, 'Tanah Jambo Aye', 19000, 1, 0, '2024-11-28 18:59:43', 1, 2022);
INSERT INTO `diabetes_data` VALUES (56, ' Baktiya', 17000, 2, 0, '2024-11-28 19:00:50', 1, 2023);
INSERT INTO `diabetes_data` VALUES (57, 'Banda Baro', 25000, 16, 6, '2024-11-28 19:00:59', 1, 2023);
INSERT INTO `diabetes_data` VALUES (58, 'Dewantara', 45000, 833, 223, '2024-11-28 19:01:17', 2, 2023);
INSERT INTO `diabetes_data` VALUES (59, 'Kuta Makmur', 18000, 19, 3, '2024-11-28 19:01:29', 1, 2023);
INSERT INTO `diabetes_data` VALUES (60, 'Meurah Mulia', 14000, 1, 0, '2024-11-28 19:01:40', 1, 2023);
INSERT INTO `diabetes_data` VALUES (61, 'Muara Batu', 35000, 218, 50, '2024-11-28 19:01:52', 1, 2023);
INSERT INTO `diabetes_data` VALUES (62, 'Nisam', 40000, 306, 90, '2024-11-28 19:02:04', 1, 2023);
INSERT INTO `diabetes_data` VALUES (63, 'Nisam Antara', 20000, 37, 0, '2024-11-28 19:02:16', 1, 2023);
INSERT INTO `diabetes_data` VALUES (64, 'Sawang', 22000, 86, 0, '2024-11-28 19:02:29', 1, 2023);
INSERT INTO `diabetes_data` VALUES (65, 'Simpang Kramat', 15000, 1, 0, '2024-11-28 19:02:38', 1, 2023);

-- ----------------------------
-- Table structure for region_coordinates
-- ----------------------------
DROP TABLE IF EXISTS `region_coordinates`;
CREATE TABLE `region_coordinates`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wilayah` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `latitude` decimal(10, 6) NOT NULL,
  `longitude` decimal(10, 6) NOT NULL,
  `created_at` timestamp(0) NOT NULL DEFAULT current_timestamp(0),
  `updated_at` timestamp(0) NOT NULL DEFAULT current_timestamp(0) ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `wilayah`(`wilayah`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 56 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of region_coordinates
-- ----------------------------
INSERT INTO `region_coordinates` VALUES (49, 'Dewantara', 5.223946, 97.002900, '2025-03-03 15:20:26', '2025-03-03 15:20:26');
INSERT INTO `region_coordinates` VALUES (50, 'Banda Baro', 5.183345, 96.956740, '2025-03-03 15:21:44', '2025-03-03 15:21:44');
INSERT INTO `region_coordinates` VALUES (51, 'Baktiya Barat', 5.148609, 97.349800, '2025-03-03 15:22:19', '2025-03-03 15:22:19');
INSERT INTO `region_coordinates` VALUES (52, 'Muara Batu', 5.246093, 96.934680, '2025-03-03 15:22:59', '2025-03-03 15:22:59');
INSERT INTO `region_coordinates` VALUES (53, 'Nisam', 5.175595, 97.001390, '2025-03-03 15:23:33', '2025-03-03 15:23:33');
INSERT INTO `region_coordinates` VALUES (54, 'Nisam Antara', 5.097384, 96.958540, '2025-03-03 15:24:07', '2025-03-03 15:24:07');
INSERT INTO `region_coordinates` VALUES (55, 'Sawang', 5.155114, 96.901920, '2025-03-03 15:24:59', '2025-03-03 15:24:59');

-- ----------------------------
-- Table structure for user_settings
-- ----------------------------
DROP TABLE IF EXISTS `user_settings`;
CREATE TABLE `user_settings`  (
  `user_id` int(11) NOT NULL,
  `theme` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'light',
  `show_chart_index` tinyint(1) NULL DEFAULT 1,
  `show_chart_dashboard` tinyint(1) NULL DEFAULT 1,
  `default_sort` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'jumlah_penderita',
  `language` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'id',
  `show_map_index` tinyint(1) NULL DEFAULT 1,
  `map_default_zoom` int(11) NULL DEFAULT 5,
  `map_default_center` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '-2.5, 118',
  `map_default_color` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'penderita',
  PRIMARY KEY (`user_id`) USING BTREE,
  CONSTRAINT `user_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user_settings
-- ----------------------------
INSERT INTO `user_settings` VALUES (1, 'light', 1, 1, 'jumlah_penderita', 'en', 1, 6, '5.5, 95.3', 'penderita');

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp(0) NOT NULL DEFAULT current_timestamp(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `username`(`username`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES (1, 'admin', '$2a$12$eW5YG8gUoO45OOh0vF3OyONlsREKWhcqdkfehlHEbyb9eAdyMv/Du', '2024-10-11 09:02:11');

SET FOREIGN_KEY_CHECKS = 1;
