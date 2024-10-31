/*
Navicat MySQL Data Transfer

Source Server         : MySQL56
Source Server Version : 50616
Source Host           : localhost:3306
Source Database       : test02

Target Server Type    : MYSQL
Target Server Version : 50616
File Encoding         : 65001

Date: 2014-06-02 07:07:30
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for wp_notifications
-- ----------------------------
DROP TABLE IF EXISTS `wp_notifications`;
CREATE TABLE `wp_notifications` (
  `ID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  `session_id` varchar(100) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `notification_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_sticky` bit(1) NOT NULL DEFAULT b'0',
  `notification_type` int(4) NOT NULL,
  `notification_text` varchar(2048) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1119 DEFAULT CHARSET=latin1;
