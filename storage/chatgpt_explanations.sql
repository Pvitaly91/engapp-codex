-- Adminer 5.2.1 MariaDB 10.4.32-MariaDB dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `chatgpt_explanations`;
CREATE TABLE `chatgpt_explanations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `question` text NOT NULL,
  `wrong_answer` text NOT NULL,
  `correct_answer` text NOT NULL,
  `language` varchar(255) NOT NULL DEFAULT 'uk',
  `explanation` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `chatgpt_explanations_unique` (`question`,`wrong_answer`,`correct_answer`,`language`) USING HASH
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `chatgpt_explanations` (`id`, `question`, `wrong_answer`, `correct_answer`, `language`, `explanation`, `created_at`, `updated_at`) VALUES
(1,	'Where {a1} my shoes?',	'does',	'are',	'ua',	'Неправильна відповідь \"does\" є невірною, оскільки \"does\" використовується як допоміжне дієслово для формування запитань у теперішньому часі з третєю особою однини. Замість цього потрібно використовувати дієслово \"are\", яке є правильною формою дієслова \"to be\" для множини у теперішньому часі, щоб коректно поставити питання \"Де мої черевики?\".',	'2025-07-30 10:02:43',	'2025-07-30 10:02:43'),
(2,	'{a1} she go to school in the morning or afternoon?',	'am',	'does',	'ua',	'Неправильна відповідь \"am\" є некоректною, оскільки вона не відповідає на запитання в англійській мові щодо часу, коли хтось ходить до школи. У правильній відповіді \"does\" використовується допоміжне дієслово для узгодження з підметом \"she\" та формування питання в простому теперішньому часі.',	'2025-07-30 10:02:47',	'2025-07-30 10:02:47'),
(3,	'{a1} you go to school in the morning or afternoon?',	'does',	'do',	'ua',	'Неправильна відповідь \"does\" є некоректною, оскільки у запитанні використовується займенник \"you\", який вимагає форми дієслова \"do\" для утворення запитань у теперішньому неозначеному часі. \"Does\" використовується з третєю особою однини (he, she, it).',	'2025-07-30 10:02:52',	'2025-07-30 10:02:52'),
(4,	'How many times a month {a1} you go to your beach house?',	'am',	'do',	'ua',	'Неправильна відповідь \"am\" є некоректною, тому що вона не відповідає формі граматичного питання у даному контексті. У цьому реченні питання починається з \"How many times a month\" і вимагає допоміжного дієслова \"do\" для правильної конструкції питання у теперішньому простому часі для узгодження з підметом \"you\".',	'2025-07-30 10:02:56',	'2025-07-30 10:02:56'),
(5,	'{a1} you like chocolate?',	'is',	'do',	'ua',	'Неправильна відповідь \"is\" є некоректною, оскільки це форма дієслова \"to be\", яка не пасує до запитання в теперішньому неозначеному часі. Запитання починається з \"do\", що є допоміжним дієсловом для теперішнього простого часу, яке використовується для утворення загальних питань щодо особистих вподобань.',	'2025-07-30 10:03:00',	'2025-07-30 10:03:00');

-- 2025-07-30 13:06:09 UTC
