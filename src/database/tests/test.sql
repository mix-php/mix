/*
 Navicat Premium Data Transfer

 Source Server         : 127.0.0.1
 Source Server Type    : MySQL
 Source Server Version : 80025
 Source Host           : 127.0.0.1:3306
 Source Schema         : test

 Target Server Type    : MySQL
 Target Server Version : 80025
 File Encoding         : 65001

 Date: 06/07/2021 16:41:57
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for news
-- ----------------------------
DROP TABLE IF EXISTS `news`;
CREATE TABLE `news` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `uid` int unsigned NOT NULL,
  `title` varchar(100) NOT NULL,
  `content` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ----------------------------
-- Records of news
-- ----------------------------
BEGIN;
INSERT INTO `news` VALUES (1, 2, '标题1', '内容1');
COMMIT;

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `balance` int unsigned NOT NULL,
  `add_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=107 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Records of users
-- ----------------------------
BEGIN;
INSERT INTO `users` VALUES (1, 'foo2', 102, '2021-07-06 08:40:20');
INSERT INTO `users` VALUES (2, 'test2', 3, '2021-07-06 04:05:01');
INSERT INTO `users` VALUES (3, 'test3', 3, '2021-07-06 04:05:01');
INSERT INTO `users` VALUES (4, 'test3', 3, '2021-07-06 04:05:01');
INSERT INTO `users` VALUES (5, 'test3', 3, '2021-07-06 04:05:01');
INSERT INTO `users` VALUES (6, 'test4', 4, '2021-07-06 04:05:01');
INSERT INTO `users` VALUES (7, 'test3', 3, '2021-07-06 04:05:01');
INSERT INTO `users` VALUES (8, 'test4', 4, '2021-07-06 04:05:01');
INSERT INTO `users` VALUES (9, 'test3', 3, '2021-07-06 04:05:01');
INSERT INTO `users` VALUES (10, 'test4', 4, '2021-07-06 04:05:01');
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
