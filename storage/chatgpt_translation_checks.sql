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
(1,	'що вона робить кожен день?',	'what does she do every day?',	'what doess she work every day',	'uk',	0,	'Студент переклав речення \"Що вона робить кожен день?\" як \"what doess she work every day\", що є помилкою. Слово \'робить\' можна перекласти як \'do\', а не \'work\'. Також в студентському перекладі є помилка в слові \'doess\', правильно \'does\'. Правильний переклад – \'What does she do every day?\'',	'2025-07-30 10:05:28',	'2025-07-30 10:05:28'),
(2,	'з ким вона розмовляла вчора ?',	'who was she talking to yesterday?',	'who does she speack yesterday',	'uk',	0,	'Переклад студента має кілька помилок. По-перше, форма дієслова \'does she speack\' неправильна, оскільки \'does\' вживається для теперішнього часу, тоді як в оригінальному реченні використано минулий час. Також міститься орфографічна помилка: \'speack\' має бути \'speak\'. Правильний переклад: \'Who was she speaking to yesterday?\'',	'2025-07-30 14:46:18',	'2025-07-30 14:46:18'),
(3,	'з ким вона розмовляла вчора ?',	'who was she talking to yesterday?',	'who did she talk yesterday',	'uk',	0,	'Студентський переклад має помилки: 1) Питальна форма повинна бути \'Who was she talking to yesterday?\' замість \'Who did she talk yesterday\'. 2) У студента відсутній прийменник \'to\', який є необхідним для правильного перекладу.',	'2025-07-30 14:48:23',	'2025-07-30 14:48:23'),
(4,	'мій брат не дзвонив вчора.',	'my brother didn\'t call yesterday.',	'my brother didn\'t call yesterday',	'uk',	1,	'Переклад студента є правильним, оскільки він точно відображає зміст оригінального речення українською мовою. Хоча в англійській мові речення повинно починатися з великої літери, це не вважається помилкою у значенні перекладу.',	'2025-07-30 14:49:11',	'2025-07-30 14:49:11');

-- 2025-07-31 21:43:06 UTC
