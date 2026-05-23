-- MiniMax API 管理平台 数据库表结构
-- 数据库: tools_imoons_cn
-- 字符集: utf8mb4

CREATE DATABASE IF NOT EXISTS `tools_imoons_cn` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `tools_imoons_cn`;

-- 用户API Key表
CREATE TABLE IF NOT EXISTS `api_keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `api_key` varchar(100) NOT NULL,
  `user_name` varchar(100) NOT NULL DEFAULT '',
  `daily_limit` int(11) NOT NULL DEFAULT '1000',
  `monthly_limit` int(11) NOT NULL DEFAULT '30000',
  `used_today` int(11) NOT NULL DEFAULT '0',
  `used_monthly` int(11) NOT NULL DEFAULT '0',
  `total_used` int(11) NOT NULL DEFAULT '0',
  `is_active` tinyint(4) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_used_at` datetime DEFAULT NULL,
  `last_reset_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `api_key` (`api_key`),
  KEY `idx_api_key` (`api_key`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 主Key表
CREATE TABLE IF NOT EXISTS `master_keys` (
  `id` int(11) NOT NULL DEFAULT '1',
  `api_key` varchar(200) NOT NULL DEFAULT '',
  `is_active` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 使用日志表
CREATE TABLE IF NOT EXISTS `usage_logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `api_key` varchar(100) NOT NULL,
  `tokens_used` int(11) NOT NULL DEFAULT '0',
  `cost` decimal(10,6) NOT NULL DEFAULT '0.000000',
  `endpoint` varchar(200) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_api_key` (`api_key`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 插入示例主Key（请替换为真实MiniMax API Key）
INSERT INTO `master_keys` (`id`, `api_key`, `is_active`) VALUES (1, 'YOUR_MINIMAX_API_KEY_HERE', 1);