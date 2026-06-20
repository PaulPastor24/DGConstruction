-- Add approval workflow columns to accomplishment_reports table
ALTER TABLE accomplishment_reports ADD COLUMN reviewed_by BIGINT UNSIGNED NULL AFTER submitted_by;
ALTER TABLE accomplishment_reports ADD COLUMN approved_by BIGINT UNSIGNED NULL AFTER reviewed_by;
ALTER TABLE accomplishment_reports ADD COLUMN approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending' AFTER ai_status;
ALTER TABLE accomplishment_reports ADD COLUMN approval_remarks LONGTEXT NULL AFTER approval_status;
ALTER TABLE accomplishment_reports ADD COLUMN reviewed_at TIMESTAMP NULL AFTER approval_remarks;
ALTER TABLE accomplishment_reports ADD COLUMN approved_at TIMESTAMP NULL AFTER reviewed_at;
ALTER TABLE accomplishment_reports ADD COLUMN rejected_at TIMESTAMP NULL AFTER approved_at;

-- Add foreign keys
ALTER TABLE accomplishment_reports ADD FOREIGN KEY (reviewed_by) REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE accomplishment_reports ADD FOREIGN KEY (approved_by) REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE;

-- Verify the columns were added
DESCRIBE accomplishment_reports;
