-- SQL: create supervisor_notifications table and insert demo rows
-- Import this file via phpMyAdmin or run using mysql client

SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `supervisor_notifications` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `supervisor_id` BIGINT UNSIGNED NOT NULL,
  `type` VARCHAR(100) DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT DEFAULT NULL,
  `data` JSON DEFAULT NULL,
  `related_id` BIGINT UNSIGNED DEFAULT NULL,
  `related_type` VARCHAR(100) DEFAULT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `read_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_supervisor_id` (`supervisor_id`),
  KEY `idx_type` (`type`),
  KEY `idx_is_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional foreign key: enable if your `users` table uses `user_id` as PK
-- ALTER TABLE `supervisor_notifications`
--   ADD CONSTRAINT `fk_supervisor_user` FOREIGN KEY (`supervisor_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;

-- Demo data: replace supervisor_id values with your actual supervisor `user_id`s if different
INSERT INTO `supervisor_notifications` (`supervisor_id`,`type`,`title`,`message`,`data`,`related_id`,`related_type`,`is_read`,`read_at`,`created_at`,`updated_at`) VALUES
(1,'report','Daily report reminder','Please submit your daily project update before 6:00 PM.', JSON_OBJECT('module','supervisor.reports'), NULL,'report',0,NULL, NOW(), NOW()),
(1,'phase','New construction phase added','Phase 3 has been added to Project A.', JSON_OBJECT('module','supervisor.phases'), NULL,'phase',0,NULL, NOW() - INTERVAL 1 DAY, NOW() - INTERVAL 1 DAY),
(1,'announcement','Site announcement','Safety briefing at 8:00 AM tomorrow.', JSON_OBJECT('module','supervisor.dashboard'), NULL,'announcement',1,NOW() - INTERVAL 2 DAY, NOW() - INTERVAL 2 DAY, NOW() - INTERVAL 2 DAY),
(2,'system','System maintenance scheduled','Planned maintenance on Saturday 10 PM - 12 AM.', JSON_OBJECT('module','supervisor.dashboard'), NULL,'system',0,NULL, NOW() - INTERVAL 3 HOUR, NOW() - INTERVAL 3 HOUR);

-- Notes:
-- 1) If your users' primary key is not `user_id`, update the foreign key or supervisor_id values accordingly.
-- 2) If you want the SQL to also add the foreign key constraint, uncomment the ALTER TABLE block above.
-- 3) Import this file in phpMyAdmin: choose your database -> Import -> choose this .sql file -> Go.
