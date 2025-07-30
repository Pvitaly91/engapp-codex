-- Adminer 5.2.1 MariaDB 10.4.32-MariaDB dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `chatgpt_translation_checks`;
CREATE TABLE `chatgpt_translation_checks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `original` text NOT NULL,
  `reference` text NOT NULL,
  `user_text` text NOT NULL,
  `language` varchar(255) NOT NULL DEFAULT 'uk',
  `is_correct` tinyint(1) NOT NULL,
  `explanation` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `chatgpt_translation_checks_unique` (`original`,`reference`,`user_text`,`language`) USING HASH
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `chatgpt_translation_checks` (`id`, `original`, `reference`, `user_text`, `language`, `is_correct`, `explanation`, `created_at`, `updated_at`) VALUES
(1,	'що вона робить кожен день?',	'what does she do every day?',	'what doess she work every day',	'uk',	0,	'Студент переклав речення \"Що вона робить кожен день?\" як \"what doess she work every day\", що є помилкою. Слово \'робить\' можна перекласти як \'do\', а не \'work\'. Також в студентському перекладі є помилка в слові \'doess\', правильно \'does\'. Правильний переклад – \'What does she do every day?\'',	'2025-07-30 10:05:28',	'2025-07-30 10:05:28');

-- 2025-07-30 13:07:18 UTC
