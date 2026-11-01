-- Select the application database in phpMyAdmin before running this statement.
-- Use when accomplishment_reports is missing the attachments column.
-- Adds nullable document metadata storage without changing existing report content.
ALTER TABLE `accomplishment_reports`
    ADD COLUMN `attachments` JSON NULL;
