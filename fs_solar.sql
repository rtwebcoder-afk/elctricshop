/*
 Navicat Premium Dump SQL

 Source Server         : mysql
 Source Server Type    : MySQL
 Source Server Version : 80046 (8.0.46)
 Source Host           : localhost:3307
 Source Schema         : fs_solar

 Target Server Type    : MySQL
 Target Server Version : 80046 (8.0.46)
 File Encoding         : 65001

 Date: 01/08/2026 10:40:57
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for service_entries
-- ----------------------------
DROP TABLE IF EXISTS `service_entries`;
CREATE TABLE `service_entries`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `entry_date` date NOT NULL,
  `fault` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `accessories` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `amount` decimal(10, 2) NULL DEFAULT 0.00,
  `advance` decimal(10, 2) NULL DEFAULT 0.00,
  `remaining` decimal(10, 2) NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 17 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of service_entries
-- ----------------------------
INSERT INTO `service_entries` VALUES (1, 'junaid', '2026-07-29', 'inverter short', 'circuit', 'he come tpmorrow', 5000.00, 1000.00, 4000.00, '2026-07-29 10:13:46');
INSERT INTO `service_entries` VALUES (2, 'ayazkhan', '2026-07-29', 'battery', 'new battery', 'buy new battery', 40000.00, 20000.00, 20000.00, '2026-07-29 10:26:27');
INSERT INTO `service_entries` VALUES (3, 'khan', '2026-07-29', 'inervter shot', 'circuit', 'there is note', 30000.00, 1000.00, 29000.00, '2026-07-29 10:44:53');
INSERT INTO `service_entries` VALUES (4, 'khan', '2026-07-29', 'kahn', 'sjk', '7888', 1000.00, 500.00, 500.00, '2026-07-29 10:51:15');
INSERT INTO `service_entries` VALUES (5, 'sinan', '2026-07-29', 'inverter shot', 'circuit', 'there is notes', 100000.00, 50000.00, 50000.00, '2026-07-29 10:52:58');
INSERT INTO `service_entries` VALUES (6, 'sinan', '2026-07-29', 'inverter shot', 'circuit', 'there is notes', 100000.00, 50000.00, 50000.00, '2026-07-29 10:53:40');
INSERT INTO `service_entries` VALUES (8, 'ayaz', '2026-07-30', 'khN', 'inverter, refregirator, solar panel', 'JHFJKASL', 2000.00, 1000.00, 1000.00, '2026-07-30 08:46:19');
INSERT INTO `service_entries` VALUES (9, 'ayaz', '2026-07-30', 'khN', 'inverter, refregirator, solar panel', 'JHFJKASL', 2000.00, 1000.00, 1000.00, '2026-07-30 08:47:13');
INSERT INTO `service_entries` VALUES (10, 'ayaz', '2026-07-30', 'khN', 'inverter, refregirator, solar panel', 'JHFJKASL', 2000.00, 1000.00, 1000.00, '2026-07-30 08:48:15');
INSERT INTO `service_entries` VALUES (11, 'shayan', '2026-07-30', 'jkfhkasl', 'solar panel', 'ksjdfhkl', 10000.00, 7778.00, 2222.00, '2026-07-30 09:42:57');
INSERT INTO `service_entries` VALUES (12, 'test', '2026-07-30', 'test', 'inverter, refregirator', 'jsdfhjsdklfh', 760000.00, 90000.00, 670000.00, '2026-07-30 09:59:37');
INSERT INTO `service_entries` VALUES (13, 'shahan', '2026-07-30', 'solar', 'solar panel', '', 25000.00, 0.00, 25000.00, '2026-07-30 17:32:41');
INSERT INTO `service_entries` VALUES (14, 'khan', '2026-08-01', 'sdflash', 'refregirator', 'ad;fjkls', 60000.00, 30000.00, 30000.00, '2026-08-01 09:20:12');
INSERT INTO `service_entries` VALUES (15, 'sdkfjsdf', '2026-08-01', 'sdfgklsjkl', 'refregirator (x5)', 'jlsd;af', 300000.00, 0.00, 300000.00, '2026-08-01 10:03:38');
INSERT INTO `service_entries` VALUES (16, 'fhasdjklhf', '2026-08-01', 'sdfkhlasdjkfh', 'inverter (x1)', 'faerflwedfwe', 700000.00, 0.00, 700000.00, '2026-08-01 10:38:31');

-- ----------------------------
-- Table structure for store_items
-- ----------------------------
DROP TABLE IF EXISTS `store_items`;
CREATE TABLE `store_items`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `item_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `item_price` decimal(10, 2) NOT NULL DEFAULT 0.00,
  `quantity` int NOT NULL DEFAULT 0,
  `item_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of store_items
-- ----------------------------
INSERT INTO `store_items` VALUES (1, 'solar panel', 25000.00, 12, 'canadian', '2026-07-29 11:14:48');
INSERT INTO `store_items` VALUES (2, 'inverter', 700000.00, 49, 'canadian', '2026-07-29 11:15:34');
INSERT INTO `store_items` VALUES (3, 'refregirator', 60000.00, 14, 'hair', '2026-07-29 17:05:36');

SET FOREIGN_KEY_CHECKS = 1;
