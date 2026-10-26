-- D&G Construction demonstration reset ? MySQL 8 / MariaDB, current migrated schema.
-- DESTRUCTIVE: back up the selected database before importing this entire file.
-- Not executed by Codex. Run during maintenance; stop application writes first.
-- Preserves users, clients (account profiles), passkeys, sessions, password reset tokens,
-- migrations and unknown tables. Clears the explicitly listed operational tables only.
-- Unknown/custom operational tables must be reviewed separately before import.
-- Child tables (including tool_loans) are cleared before their parent tables.
-- No uploaded files are deleted. This is database data only.
-- Dataset reference date: 2026-10-26; 8 completed, 9 ongoing, 3 delayed ongoing, 2 planning.
-- Project dates span 2023?2028. Later dates are target completion dates.
-- Requires existing active engineer/admin, supervisor, and client accounts.
-- Run in the intended database (select it in phpMyAdmin first).

DROP PROCEDURE IF EXISTS dg_reset_demo_2023_2028;
DELIMITER $$
CREATE PROCEDURE dg_reset_demo_2023_2028()
BEGIN
    DECLARE finished INT DEFAULT 0;
    DECLARE target_table VARCHAR(64);
    DECLARE old_fk INT DEFAULT 1;
    DECLARE engineer BIGINT;
    DECLARE supervisor BIGINT;
    DECLARE client_account BIGINT;
    DECLARE demo_client BIGINT;
    DECLARE has_legacy_location INT DEFAULT 0;
    DECLARE has_location INT DEFAULT 0;
    DECLARE affected_tables CURSOR FOR
        SELECT TABLE_NAME FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE = 'BASE TABLE'
          AND TABLE_NAME IN ('accomplishment_reports', 'admin_notifications', 'ai_analysis_results', 'attendance_logs', 'attendance_schedule_rules', 'cache', 'cache_locks', 'client_notifications', 'construction_phases', 'landing_gallery_images', 'landing_gallery_project_images', 'landing_page_settings', 'material_deliveries', 'material_requests', 'material_usages', 'materials', 'project_archives', 'project_materials', 'project_status_histories', 'project_supervisors', 'project_workers', 'projects', 'stock_receipts', 'supervisor_notifications', 'system_logs', 'timeline_milestones', 'tool_deductions', 'tool_loans', 'tools', 'worker_biometric_profiles', 'worker_face_attempts', 'worker_face_enrollments', 'workers')
        ORDER BY FIELD(TABLE_NAME, 'admin_notifications', 'supervisor_notifications', 'client_notifications', 'system_logs', 'tool_deductions', 'tool_loans', 'tools', 'worker_face_attempts', 'worker_face_enrollments', 'worker_biometric_profiles', 'attendance_logs', 'project_workers', 'workers', 'ai_analysis_results', 'accomplishment_reports', 'timeline_milestones', 'landing_gallery_project_images', 'landing_gallery_images', 'construction_phases', 'material_usages', 'material_requests', 'material_deliveries', 'stock_receipts', 'project_materials', 'materials', 'project_status_histories', 'project_archives', 'project_supervisors', 'projects', 'attendance_schedule_rules', 'landing_page_settings', 'cache_locks', 'cache');
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET finished = 1;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET FOREIGN_KEY_CHECKS = old_fk;
        RESIGNAL;
    END;

    SET old_fk = @@FOREIGN_KEY_CHECKS;
    SELECT MIN(user_id) INTO engineer FROM users WHERE role IN ('engineer','admin') AND is_active = 1;
    SELECT MIN(user_id) INTO supervisor FROM users WHERE role = 'supervisor' AND is_active = 1;
    SELECT MIN(user_id) INTO client_account FROM users WHERE role = 'client' AND is_active = 1;
    IF engineer IS NULL OR supervisor IS NULL OR client_account IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Reset aborted: active engineer/admin, supervisor and client accounts are required.';
    END IF;
    IF EXISTS (SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME IN ('accomplishment_reports', 'admin_notifications', 'ai_analysis_results', 'attendance_logs', 'attendance_schedule_rules', 'cache', 'cache_locks', 'client_notifications', 'construction_phases', 'landing_gallery_images', 'landing_gallery_project_images', 'landing_page_settings', 'material_deliveries', 'material_requests', 'material_usages', 'materials', 'project_archives', 'project_materials', 'project_status_histories', 'project_supervisors', 'project_workers', 'projects', 'stock_receipts', 'supervisor_notifications', 'system_logs', 'timeline_milestones', 'tool_deductions', 'tool_loans', 'tools', 'worker_biometric_profiles', 'worker_face_attempts', 'worker_face_enrollments', 'workers', 'users', 'clients')
        AND TABLE_TYPE = 'BASE TABLE' AND ENGINE <> 'InnoDB') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Reset aborted: affected tables must use InnoDB for rollback.';
    END IF;

    SELECT COUNT(*) INTO has_legacy_location FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'projects' AND COLUMN_NAME = 'project_location';
    SELECT COUNT(*) INTO has_location FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'projects' AND COLUMN_NAME = 'location';
    IF has_legacy_location = 0 AND has_location = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Reset aborted: projects requires location or project_location.';
    END IF;

    START TRANSACTION;
    -- Delete children before parents with constraints enabled.
    SET FOREIGN_KEY_CHECKS = 1;
    -- Break self-references before deleting phases.
    UPDATE construction_phases SET depends_on_phase_id = NULL WHERE depends_on_phase_id IS NOT NULL;
    OPEN affected_tables;
    clear_loop: LOOP
        FETCH affected_tables INTO target_table;
        IF finished = 1 THEN LEAVE clear_loop; END IF;
        SET @demo_delete_sql = CONCAT('DELETE FROM `', REPLACE(target_table, '`', '``'), '`');
        PREPARE demo_delete_stmt FROM @demo_delete_sql;
        EXECUTE demo_delete_stmt;
        DEALLOCATE PREPARE demo_delete_stmt;
    END LOOP;
    CLOSE affected_tables;
    -- DELETE is used instead of TRUNCATE so the reset can roll back on errors.
    -- Auto-increment counters are intentionally not reset.
    SET FOREIGN_KEY_CHECKS = old_fk;

    INSERT INTO clients (user_id, company_name, created_at, updated_at)
    SELECT client_account, 'Demonstration Client', NOW(), NOW()
    WHERE NOT EXISTS (SELECT 1 FROM clients WHERE user_id = client_account);
    SELECT MIN(client_id) INTO demo_client FROM clients WHERE user_id = client_account;

    -- 1. Mabini Family Residence
    IF has_legacy_location > 0 THEN
        INSERT INTO projects (project_name, project_location, client_id, engineer_id, start_date, target_end_date, actual_end_date, status, description, created_at, updated_at)
        VALUES ('Mabini Family Residence', 'Batangas City', demo_client, engineer, '2023-01-16', '2023-10-30', '2023-10-20', 'completed', 'Demonstration project. Completed and turned over.', '2023-01-16 08:00:00', NOW());
    ELSE
        INSERT INTO projects (project_name, location, client_id, engineer_id, start_date, target_end_date, actual_end_date, status, description, created_at, updated_at)
        VALUES ('Mabini Family Residence', 'Batangas City', demo_client, engineer, '2023-01-16', '2023-10-30', '2023-10-20', 'completed', 'Demonstration project. Completed and turned over.', '2023-01-16 08:00:00', NOW());
    END IF;
    SET @demo_project_id = LAST_INSERT_ID();
    INSERT INTO project_supervisors (project_id, supervisor_id, assigned_date, is_active, created_at)
    VALUES (@demo_project_id, supervisor, '2023-01-16', 1, NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Mobilization and Site Preparation', 1, '2023-01-16', '2023-03-11', '2023-01-16', '2023-03-11', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Mobilization and Site Preparation sign-off', '2023-01-16', '2023-03-11', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Foundation Works', 2, '2023-03-12', '2023-05-05', '2023-03-12', '2023-05-05', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Foundation Works sign-off', '2023-03-12', '2023-05-05', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Structural Works', 3, '2023-05-06', '2023-06-29', '2023-05-06', '2023-06-29', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Structural Works sign-off', '2023-05-06', '2023-06-29', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'MEP and Architectural Finishes', 4, '2023-06-30', '2023-08-23', '2023-06-30', '2023-08-23', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'MEP and Architectural Finishes sign-off', '2023-06-30', '2023-08-23', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Inspection and Turnover', 5, '2023-08-24', '2023-10-20', '2023-08-24', '2023-10-20', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Inspection and Turnover sign-off', '2023-08-24', '2023-10-20', 1, 0, 'completed', NOW(), NOW());

    -- 2. Lipa Townhouse Renovation
    IF has_legacy_location > 0 THEN
        INSERT INTO projects (project_name, project_location, client_id, engineer_id, start_date, target_end_date, actual_end_date, status, description, created_at, updated_at)
        VALUES ('Lipa Townhouse Renovation', 'Lipa City', demo_client, engineer, '2023-03-06', '2023-12-15', '2023-12-08', 'completed', 'Demonstration project. Completed and turned over.', '2023-03-06 08:00:00', NOW());
    ELSE
        INSERT INTO projects (project_name, location, client_id, engineer_id, start_date, target_end_date, actual_end_date, status, description, created_at, updated_at)
        VALUES ('Lipa Townhouse Renovation', 'Lipa City', demo_client, engineer, '2023-03-06', '2023-12-15', '2023-12-08', 'completed', 'Demonstration project. Completed and turned over.', '2023-03-06 08:00:00', NOW());
    END IF;
    SET @demo_project_id = LAST_INSERT_ID();
    INSERT INTO project_supervisors (project_id, supervisor_id, assigned_date, is_active, created_at)
    VALUES (@demo_project_id, supervisor, '2023-03-06', 1, NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Mobilization and Site Preparation', 1, '2023-03-06', '2023-04-29', '2023-03-06', '2023-04-29', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Mobilization and Site Preparation sign-off', '2023-03-06', '2023-04-29', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Foundation Works', 2, '2023-04-30', '2023-06-23', '2023-04-30', '2023-06-23', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Foundation Works sign-off', '2023-04-30', '2023-06-23', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Structural Works', 3, '2023-06-24', '2023-08-17', '2023-06-24', '2023-08-17', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Structural Works sign-off', '2023-06-24', '2023-08-17', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'MEP and Architectural Finishes', 4, '2023-08-18', '2023-10-11', '2023-08-18', '2023-10-11', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'MEP and Architectural Finishes sign-off', '2023-08-18', '2023-10-11', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Inspection and Turnover', 5, '2023-10-12', '2023-12-08', '2023-10-12', '2023-12-08', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Inspection and Turnover sign-off', '2023-10-12', '2023-12-08', 1, 0, 'completed', NOW(), NOW());

    -- 3. Bauan Retail Center
    IF has_legacy_location > 0 THEN
        INSERT INTO projects (project_name, project_location, client_id, engineer_id, start_date, target_end_date, actual_end_date, status, description, created_at, updated_at)
        VALUES ('Bauan Retail Center', 'Bauan, Batangas', demo_client, engineer, '2023-06-05', '2024-05-31', '2024-05-24', 'completed', 'Demonstration project. Completed and turned over.', '2023-06-05 08:00:00', NOW());
    ELSE
        INSERT INTO projects (project_name, location, client_id, engineer_id, start_date, target_end_date, actual_end_date, status, description, created_at, updated_at)
        VALUES ('Bauan Retail Center', 'Bauan, Batangas', demo_client, engineer, '2023-06-05', '2024-05-31', '2024-05-24', 'completed', 'Demonstration project. Completed and turned over.', '2023-06-05 08:00:00', NOW());
    END IF;
    SET @demo_project_id = LAST_INSERT_ID();
    INSERT INTO project_supervisors (project_id, supervisor_id, assigned_date, is_active, created_at)
    VALUES (@demo_project_id, supervisor, '2023-06-05', 1, NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Mobilization and Site Preparation', 1, '2023-06-05', '2023-08-13', '2023-06-05', '2023-08-13', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Mobilization and Site Preparation sign-off', '2023-06-05', '2023-08-13', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Foundation Works', 2, '2023-08-14', '2023-10-22', '2023-08-14', '2023-10-22', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Foundation Works sign-off', '2023-08-14', '2023-10-22', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Structural Works', 3, '2023-10-23', '2023-12-31', '2023-10-23', '2023-12-31', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Structural Works sign-off', '2023-10-23', '2023-12-31', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'MEP and Architectural Finishes', 4, '2024-01-01', '2024-03-10', '2024-01-01', '2024-03-10', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'MEP and Architectural Finishes sign-off', '2024-01-01', '2024-03-10', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Inspection and Turnover', 5, '2024-03-11', '2024-05-24', '2024-03-11', '2024-05-24', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Inspection and Turnover sign-off', '2024-03-11', '2024-05-24', 1, 0, 'completed', NOW(), NOW());

    -- 4. San Jose Farmhouse
    IF has_legacy_location > 0 THEN
        INSERT INTO projects (project_name, project_location, client_id, engineer_id, start_date, target_end_date, actual_end_date, status, description, created_at, updated_at)
        VALUES ('San Jose Farmhouse', 'San Jose, Batangas', demo_client, engineer, '2023-09-04', '2024-06-28', '2024-06-21', 'completed', 'Demonstration project. Completed and turned over.', '2023-09-04 08:00:00', NOW());
    ELSE
        INSERT INTO projects (project_name, location, client_id, engineer_id, start_date, target_end_date, actual_end_date, status, description, created_at, updated_at)
        VALUES ('San Jose Farmhouse', 'San Jose, Batangas', demo_client, engineer, '2023-09-04', '2024-06-28', '2024-06-21', 'completed', 'Demonstration project. Completed and turned over.', '2023-09-04 08:00:00', NOW());
    END IF;
    SET @demo_project_id = LAST_INSERT_ID();
    INSERT INTO project_supervisors (project_id, supervisor_id, assigned_date, is_active, created_at)
    VALUES (@demo_project_id, supervisor, '2023-09-04', 1, NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Mobilization and Site Preparation', 1, '2023-09-04', '2023-10-31', '2023-09-04', '2023-10-31', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Mobilization and Site Preparation sign-off', '2023-09-04', '2023-10-31', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Foundation Works', 2, '2023-11-01', '2023-12-28', '2023-11-01', '2023-12-28', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Foundation Works sign-off', '2023-11-01', '2023-12-28', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Structural Works', 3, '2023-12-29', '2024-02-24', '2023-12-29', '2024-02-24', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Structural Works sign-off', '2023-12-29', '2024-02-24', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'MEP and Architectural Finishes', 4, '2024-02-25', '2024-04-22', '2024-02-25', '2024-04-22', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'MEP and Architectural Finishes sign-off', '2024-02-25', '2024-04-22', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Inspection and Turnover', 5, '2024-04-23', '2024-06-21', '2024-04-23', '2024-06-21', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Inspection and Turnover sign-off', '2024-04-23', '2024-06-21', 1, 0, 'completed', NOW(), NOW());

    -- 5. Tanauan Duplex Homes
    IF has_legacy_location > 0 THEN
        INSERT INTO projects (project_name, project_location, client_id, engineer_id, start_date, target_end_date, actual_end_date, status, description, created_at, updated_at)
        VALUES ('Tanauan Duplex Homes', 'Tanauan City', demo_client, engineer, '2024-01-08', '2024-11-29', '2024-11-22', 'completed', 'Demonstration project. Completed and turned over.', '2024-01-08 08:00:00', NOW());
    ELSE
        INSERT INTO projects (project_name, location, client_id, engineer_id, start_date, target_end_date, actual_end_date, status, description, created_at, updated_at)
        VALUES ('Tanauan Duplex Homes', 'Tanauan City', demo_client, engineer, '2024-01-08', '2024-11-29', '2024-11-22', 'completed', 'Demonstration project. Completed and turned over.', '2024-01-08 08:00:00', NOW());
    END IF;
    SET @demo_project_id = LAST_INSERT_ID();
    INSERT INTO project_supervisors (project_id, supervisor_id, assigned_date, is_active, created_at)
    VALUES (@demo_project_id, supervisor, '2024-01-08', 1, NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Mobilization and Site Preparation', 1, '2024-01-08', '2024-03-10', '2024-01-08', '2024-03-10', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Mobilization and Site Preparation sign-off', '2024-01-08', '2024-03-10', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Foundation Works', 2, '2024-03-11', '2024-05-12', '2024-03-11', '2024-05-12', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Foundation Works sign-off', '2024-03-11', '2024-05-12', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Structural Works', 3, '2024-05-13', '2024-07-14', '2024-05-13', '2024-07-14', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Structural Works sign-off', '2024-05-13', '2024-07-14', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'MEP and Architectural Finishes', 4, '2024-07-15', '2024-09-15', '2024-07-15', '2024-09-15', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'MEP and Architectural Finishes sign-off', '2024-07-15', '2024-09-15', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Inspection and Turnover', 5, '2024-09-16', '2024-11-22', '2024-09-16', '2024-11-22', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Inspection and Turnover sign-off', '2024-09-16', '2024-11-22', 1, 0, 'completed', NOW(), NOW());

    -- 6. Rosario Community Hall
    IF has_legacy_location > 0 THEN
        INSERT INTO projects (project_name, project_location, client_id, engineer_id, start_date, target_end_date, actual_end_date, status, description, created_at, updated_at)
        VALUES ('Rosario Community Hall', 'Rosario, Batangas', demo_client, engineer, '2024-04-08', '2025-03-28', '2025-03-21', 'completed', 'Demonstration project. Completed and turned over.', '2024-04-08 08:00:00', NOW());
    ELSE
        INSERT INTO projects (project_name, location, client_id, engineer_id, start_date, target_end_date, actual_end_date, status, description, created_at, updated_at)
        VALUES ('Rosario Community Hall', 'Rosario, Batangas', demo_client, engineer, '2024-04-08', '2025-03-28', '2025-03-21', 'completed', 'Demonstration project. Completed and turned over.', '2024-04-08 08:00:00', NOW());
    END IF;
    SET @demo_project_id = LAST_INSERT_ID();
    INSERT INTO project_supervisors (project_id, supervisor_id, assigned_date, is_active, created_at)
    VALUES (@demo_project_id, supervisor, '2024-04-08', 1, NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Mobilization and Site Preparation', 1, '2024-04-08', '2024-06-15', '2024-04-08', '2024-06-15', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Mobilization and Site Preparation sign-off', '2024-04-08', '2024-06-15', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Foundation Works', 2, '2024-06-16', '2024-08-23', '2024-06-16', '2024-08-23', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Foundation Works sign-off', '2024-06-16', '2024-08-23', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Structural Works', 3, '2024-08-24', '2024-10-31', '2024-08-24', '2024-10-31', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Structural Works sign-off', '2024-08-24', '2024-10-31', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'MEP and Architectural Finishes', 4, '2024-11-01', '2025-01-08', '2024-11-01', '2025-01-08', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'MEP and Architectural Finishes sign-off', '2024-11-01', '2025-01-08', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Inspection and Turnover', 5, '2025-01-09', '2025-03-21', '2025-01-09', '2025-03-21', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Inspection and Turnover sign-off', '2025-01-09', '2025-03-21', 1, 0, 'completed', NOW(), NOW());

    -- 7. Sto Tomas Office Fit Out
    IF has_legacy_location > 0 THEN
        INSERT INTO projects (project_name, project_location, client_id, engineer_id, start_date, target_end_date, actual_end_date, status, description, created_at, updated_at)
        VALUES ('Sto Tomas Office Fit Out', 'Santo Tomas City', demo_client, engineer, '2024-10-07', '2025-07-31', '2025-07-25', 'completed', 'Demonstration project. Completed and turned over.', '2024-10-07 08:00:00', NOW());
    ELSE
        INSERT INTO projects (project_name, location, client_id, engineer_id, start_date, target_end_date, actual_end_date, status, description, created_at, updated_at)
        VALUES ('Sto Tomas Office Fit Out', 'Santo Tomas City', demo_client, engineer, '2024-10-07', '2025-07-31', '2025-07-25', 'completed', 'Demonstration project. Completed and turned over.', '2024-10-07 08:00:00', NOW());
    END IF;
    SET @demo_project_id = LAST_INSERT_ID();
    INSERT INTO project_supervisors (project_id, supervisor_id, assigned_date, is_active, created_at)
    VALUES (@demo_project_id, supervisor, '2024-10-07', 1, NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Mobilization and Site Preparation', 1, '2024-10-07', '2024-12-03', '2024-10-07', '2024-12-03', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Mobilization and Site Preparation sign-off', '2024-10-07', '2024-12-03', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Foundation Works', 2, '2024-12-04', '2025-01-30', '2024-12-04', '2025-01-30', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Foundation Works sign-off', '2024-12-04', '2025-01-30', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Structural Works', 3, '2025-01-31', '2025-03-29', '2025-01-31', '2025-03-29', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Structural Works sign-off', '2025-01-31', '2025-03-29', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'MEP and Architectural Finishes', 4, '2025-03-30', '2025-05-26', '2025-03-30', '2025-05-26', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'MEP and Architectural Finishes sign-off', '2025-03-30', '2025-05-26', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Inspection and Turnover', 5, '2025-05-27', '2025-07-25', '2025-05-27', '2025-07-25', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Inspection and Turnover sign-off', '2025-05-27', '2025-07-25', 1, 0, 'completed', NOW(), NOW());

    -- 8. Malvar Warehouse Extension
    IF has_legacy_location > 0 THEN
        INSERT INTO projects (project_name, project_location, client_id, engineer_id, start_date, target_end_date, actual_end_date, status, description, created_at, updated_at)
        VALUES ('Malvar Warehouse Extension', 'Malvar, Batangas', demo_client, engineer, '2025-02-03', '2026-04-30', '2026-04-24', 'completed', 'Demonstration project. Completed and turned over.', '2025-02-03 08:00:00', NOW());
    ELSE
        INSERT INTO projects (project_name, location, client_id, engineer_id, start_date, target_end_date, actual_end_date, status, description, created_at, updated_at)
        VALUES ('Malvar Warehouse Extension', 'Malvar, Batangas', demo_client, engineer, '2025-02-03', '2026-04-30', '2026-04-24', 'completed', 'Demonstration project. Completed and turned over.', '2025-02-03 08:00:00', NOW());
    END IF;
    SET @demo_project_id = LAST_INSERT_ID();
    INSERT INTO project_supervisors (project_id, supervisor_id, assigned_date, is_active, created_at)
    VALUES (@demo_project_id, supervisor, '2025-02-03', 1, NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Mobilization and Site Preparation', 1, '2025-02-03', '2025-05-02', '2025-02-03', '2025-05-02', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Mobilization and Site Preparation sign-off', '2025-02-03', '2025-05-02', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Foundation Works', 2, '2025-05-03', '2025-07-30', '2025-05-03', '2025-07-30', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Foundation Works sign-off', '2025-05-03', '2025-07-30', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Structural Works', 3, '2025-07-31', '2025-10-27', '2025-07-31', '2025-10-27', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Structural Works sign-off', '2025-07-31', '2025-10-27', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'MEP and Architectural Finishes', 4, '2025-10-28', '2026-01-24', '2025-10-28', '2026-01-24', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'MEP and Architectural Finishes sign-off', '2025-10-28', '2026-01-24', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Inspection and Turnover', 5, '2026-01-25', '2026-04-24', '2026-01-25', '2026-04-24', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Inspection and Turnover sign-off', '2026-01-25', '2026-04-24', 1, 0, 'completed', NOW(), NOW());

    -- 9. Calaca Commercial Building
    IF has_legacy_location > 0 THEN
        INSERT INTO projects (project_name, project_location, client_id, engineer_id, start_date, target_end_date, actual_end_date, status, description, created_at, updated_at)
        VALUES ('Calaca Commercial Building', 'Calaca City', demo_client, engineer, '2025-06-02', '2027-01-29', NULL, 'ongoing', 'Demonstration project. Construction in progress.', '2025-06-02 08:00:00', NOW());
    ELSE
        INSERT INTO projects (project_name, location, client_id, engineer_id, start_date, target_end_date, actual_end_date, status, description, created_at, updated_at)
        VALUES ('Calaca Commercial Building', 'Calaca City', demo_client, engineer, '2025-06-02', '2027-01-29', NULL, 'ongoing', 'Demonstration project. Construction in progress.', '2025-06-02 08:00:00', NOW());
    END IF;
    SET @demo_project_id = LAST_INSERT_ID();
    INSERT INTO project_supervisors (project_id, supervisor_id, assigned_date, is_active, created_at)
    VALUES (@demo_project_id, supervisor, '2025-06-02', 1, NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Mobilization and Site Preparation', 1, '2025-06-02', '2025-10-30', '2025-06-02', '2025-10-30', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Mobilization and Site Preparation sign-off', '2025-06-02', '2025-10-30', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Foundation Works', 2, '2025-10-31', '2026-03-30', '2025-10-31', '2026-03-30', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Foundation Works sign-off', '2025-10-31', '2026-03-30', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Structural Works', 3, '2026-03-31', '2026-08-31', '2026-03-31', '2026-08-31', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Structural Works sign-off', '2026-03-31', '2026-08-31', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'MEP and Architectural Finishes', 4, '2026-09-01', '2026-10-30', '2026-09-01', NULL, 60, 60, 'Demonstration baseline progress', NOW(), engineer, 'in_progress', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'MEP and Architectural Finishes sign-off', '2026-09-01', '2026-10-30', 0, 0, 'pending', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Inspection and Turnover', 5, '2026-10-31', '2027-01-29', NULL, NULL, 0, 0, 'Demonstration baseline progress', NOW(), engineer, 'not_started', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Inspection and Turnover sign-off', '2026-10-31', '2027-01-29', 0, 0, 'pending', NOW(), NOW());

    -- 10. Balayan Residential Villas
    IF has_legacy_location > 0 THEN
        INSERT INTO projects (project_name, project_location, client_id, engineer_id, start_date, target_end_date, actual_end_date, status, description, created_at, updated_at)
        VALUES ('Balayan Residential Villas', 'Balayan, Batangas', demo_client, engineer, '2025-07-07', '2027-03-31', NULL, 'ongoing', 'Demonstration project. Construction in progress.', '2025-07-07 08:00:00', NOW());
    ELSE
        INSERT INTO projects (project_name, location, client_id, engineer_id, start_date, target_end_date, actual_end_date, status, description, created_at, updated_at)
        VALUES ('Balayan Residential Villas', 'Balayan, Batangas', demo_client, engineer, '2025-07-07', '2027-03-31', NULL, 'ongoing', 'Demonstration project. Construction in progress.', '2025-07-07 08:00:00', NOW());
    END IF;
    SET @demo_project_id = LAST_INSERT_ID();
    INSERT INTO project_supervisors (project_id, supervisor_id, assigned_date, is_active, created_at)
    VALUES (@demo_project_id, supervisor, '2025-07-07', 1, NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Mobilization and Site Preparation', 1, '2025-07-07', '2025-11-23', '2025-07-07', '2025-11-23', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Mobilization and Site Preparation sign-off', '2025-07-07', '2025-11-23', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Foundation Works', 2, '2025-11-24', '2026-04-12', '2025-11-24', '2026-04-12', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Foundation Works sign-off', '2025-11-24', '2026-04-12', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Structural Works', 3, '2026-04-13', '2026-08-31', '2026-04-13', '2026-08-31', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Structural Works sign-off', '2026-04-13', '2026-08-31', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'MEP and Architectural Finishes', 4, '2026-09-01', '2026-12-30', '2026-09-01', NULL, 40, 40, 'Demonstration baseline progress', NOW(), engineer, 'in_progress', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'MEP and Architectural Finishes sign-off', '2026-09-01', '2026-12-30', 0, 0, 'pending', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Inspection and Turnover', 5, '2026-12-31', '2027-03-31', NULL, NULL, 0, 0, 'Demonstration baseline progress', NOW(), engineer, 'not_started', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Inspection and Turnover sign-off', '2026-12-31', '2027-03-31', 0, 0, 'pending', NOW(), NOW());

    -- 11. Nasugbu Beach Residence
    IF has_legacy_location > 0 THEN
        INSERT INTO projects (project_name, project_location, client_id, engineer_id, start_date, target_end_date, actual_end_date, status, description, created_at, updated_at)
        VALUES ('Nasugbu Beach Residence', 'Nasugbu, Batangas', demo_client, engineer, '2025-08-04', '2027-06-30', NULL, 'ongoing', 'Demonstration project. Construction in progress.', '2025-08-04 08:00:00', NOW());
    ELSE
        INSERT INTO projects (project_name, location, client_id, engineer_id, start_date, target_end_date, actual_end_date, status, description, created_at, updated_at)
        VALUES ('Nasugbu Beach Residence', 'Nasugbu, Batangas', demo_client, engineer, '2025-08-04', '2027-06-30', NULL, 'ongoing', 'Demonstration project. Construction in progress.', '2025-08-04 08:00:00', NOW());
    END IF;
    SET @demo_project_id = LAST_INSERT_ID();
    INSERT INTO project_supervisors (project_id, supervisor_id, assigned_date, is_active, created_at)
    VALUES (@demo_project_id, supervisor, '2025-08-04', 1, NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Mobilization and Site Preparation', 1, '2025-08-04', '2025-12-11', '2025-08-04', '2025-12-11', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Mobilization and Site Preparation sign-off', '2025-08-04', '2025-12-11', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Foundation Works', 2, '2025-12-12', '2026-04-20', '2025-12-12', '2026-04-20', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Foundation Works sign-off', '2025-12-12', '2026-04-20', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Structural Works', 3, '2026-04-21', '2026-08-31', '2026-04-21', '2026-08-31', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Structural Works sign-off', '2026-04-21', '2026-08-31', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'MEP and Architectural Finishes', 4, '2026-09-01', '2027-03-31', '2026-09-01', NULL, 50, 50, 'Demonstration baseline progress', NOW(), engineer, 'in_progress', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'MEP and Architectural Finishes sign-off', '2026-09-01', '2027-03-31', 0, 0, 'pending', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Inspection and Turnover', 5, '2027-04-01', '2027-06-30', NULL, NULL, 0, 0, 'Demonstration baseline progress', NOW(), engineer, 'not_started', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Inspection and Turnover sign-off', '2027-04-01', '2027-06-30', 0, 0, 'pending', NOW(), NOW());

    -- 12. San Juan Resort Annex
    IF has_legacy_location > 0 THEN
        INSERT INTO projects (project_name, project_location, client_id, engineer_id, start_date, target_end_date, actual_end_date, status, description, created_at, updated_at)
        VALUES ('San Juan Resort Annex', 'San Juan, Batangas', demo_client, engineer, '2025-09-01', '2027-08-31', NULL, 'ongoing', 'Demonstration project. Construction in progress.', '2025-09-01 08:00:00', NOW());
    ELSE
        INSERT INTO projects (project_name, location, client_id, engineer_id, start_date, target_end_date, actual_end_date, status, description, created_at, updated_at)
        VALUES ('San Juan Resort Annex', 'San Juan, Batangas', demo_client, engineer, '2025-09-01', '2027-08-31', NULL, 'ongoing', 'Demonstration project. Construction in progress.', '2025-09-01 08:00:00', NOW());
    END IF;
    SET @demo_project_id = LAST_INSERT_ID();
    INSERT INTO project_supervisors (project_id, supervisor_id, assigned_date, is_active, created_at)
    VALUES (@demo_project_id, supervisor, '2025-09-01', 1, NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Mobilization and Site Preparation', 1, '2025-09-01', '2025-12-30', '2025-09-01', '2025-12-30', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Mobilization and Site Preparation sign-off', '2025-09-01', '2025-12-30', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Foundation Works', 2, '2025-12-31', '2026-04-30', '2025-12-31', '2026-04-30', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Foundation Works sign-off', '2025-12-31', '2026-04-30', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Structural Works', 3, '2026-05-01', '2026-08-31', '2026-05-01', '2026-08-31', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Structural Works sign-off', '2026-05-01', '2026-08-31', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'MEP and Architectural Finishes', 4, '2026-09-01', '2027-06-01', '2026-09-01', NULL, 60, 60, 'Demonstration baseline progress', NOW(), engineer, 'in_progress', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'MEP and Architectural Finishes sign-off', '2026-09-01', '2027-06-01', 0, 0, 'pending', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Inspection and Turnover', 5, '2027-06-02', '2027-08-31', NULL, NULL, 0, 0, 'Demonstration baseline progress', NOW(), engineer, 'not_started', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Inspection and Turnover sign-off', '2027-06-02', '2027-08-31', 0, 0, 'pending', NOW(), NOW());

    -- 13. Lemery Medical Clinic
    IF has_legacy_location > 0 THEN
        INSERT INTO projects (project_name, project_location, client_id, engineer_id, start_date, target_end_date, actual_end_date, status, description, created_at, updated_at)
        VALUES ('Lemery Medical Clinic', 'Lemery, Batangas', demo_client, engineer, '2025-10-06', '2027-10-29', NULL, 'ongoing', 'Demonstration project. Construction in progress.', '2025-10-06 08:00:00', NOW());
    ELSE
        INSERT INTO projects (project_name, location, client_id, engineer_id, start_date, target_end_date, actual_end_date, status, description, created_at, updated_at)
        VALUES ('Lemery Medical Clinic', 'Lemery, Batangas', demo_client, engineer, '2025-10-06', '2027-10-29', NULL, 'ongoing', 'Demonstration project. Construction in progress.', '2025-10-06 08:00:00', NOW());
    END IF;
    SET @demo_project_id = LAST_INSERT_ID();
    INSERT INTO project_supervisors (project_id, supervisor_id, assigned_date, is_active, created_at)
    VALUES (@demo_project_id, supervisor, '2025-10-06', 1, NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Mobilization and Site Preparation', 1, '2025-10-06', '2026-01-22', '2025-10-06', '2026-01-22', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Mobilization and Site Preparation sign-off', '2025-10-06', '2026-01-22', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Foundation Works', 2, '2026-01-23', '2026-05-11', '2026-01-23', '2026-05-11', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Foundation Works sign-off', '2026-01-23', '2026-05-11', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Structural Works', 3, '2026-05-12', '2026-08-31', '2026-05-12', '2026-08-31', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Structural Works sign-off', '2026-05-12', '2026-08-31', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'MEP and Architectural Finishes', 4, '2026-09-01', '2027-07-30', '2026-09-01', NULL, 40, 40, 'Demonstration baseline progress', NOW(), engineer, 'in_progress', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'MEP and Architectural Finishes sign-off', '2026-09-01', '2027-07-30', 0, 0, 'pending', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Inspection and Turnover', 5, '2027-07-31', '2027-10-29', NULL, NULL, 0, 0, 'Demonstration baseline progress', NOW(), engineer, 'not_started', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Inspection and Turnover sign-off', '2027-07-31', '2027-10-29', 0, 0, 'pending', NOW(), NOW());

    -- 14. Taal Heritage Guesthouse
    IF has_legacy_location > 0 THEN
        INSERT INTO projects (project_name, project_location, client_id, engineer_id, start_date, target_end_date, actual_end_date, status, description, created_at, updated_at)
        VALUES ('Taal Heritage Guesthouse', 'Taal, Batangas', demo_client, engineer, '2025-11-03', '2027-12-17', NULL, 'ongoing', 'Demonstration project. Construction in progress.', '2025-11-03 08:00:00', NOW());
    ELSE
        INSERT INTO projects (project_name, location, client_id, engineer_id, start_date, target_end_date, actual_end_date, status, description, created_at, updated_at)
        VALUES ('Taal Heritage Guesthouse', 'Taal, Batangas', demo_client, engineer, '2025-11-03', '2027-12-17', NULL, 'ongoing', 'Demonstration project. Construction in progress.', '2025-11-03 08:00:00', NOW());
    END IF;
    SET @demo_project_id = LAST_INSERT_ID();
    INSERT INTO project_supervisors (project_id, supervisor_id, assigned_date, is_active, created_at)
    VALUES (@demo_project_id, supervisor, '2025-11-03', 1, NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Mobilization and Site Preparation', 1, '2025-11-03', '2026-02-10', '2025-11-03', '2026-02-10', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Mobilization and Site Preparation sign-off', '2025-11-03', '2026-02-10', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Foundation Works', 2, '2026-02-11', '2026-05-21', '2026-02-11', '2026-05-21', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Foundation Works sign-off', '2026-02-11', '2026-05-21', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Structural Works', 3, '2026-05-22', '2026-08-31', '2026-05-22', '2026-08-31', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Structural Works sign-off', '2026-05-22', '2026-08-31', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'MEP and Architectural Finishes', 4, '2026-09-01', '2027-09-17', '2026-09-01', NULL, 50, 50, 'Demonstration baseline progress', NOW(), engineer, 'in_progress', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'MEP and Architectural Finishes sign-off', '2026-09-01', '2027-09-17', 0, 0, 'pending', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Inspection and Turnover', 5, '2027-09-18', '2027-12-17', NULL, NULL, 0, 0, 'Demonstration baseline progress', NOW(), engineer, 'not_started', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Inspection and Turnover sign-off', '2027-09-18', '2027-12-17', 0, 0, 'pending', NOW(), NOW());

    -- 15. Cuenca Learning Center
    IF has_legacy_location > 0 THEN
        INSERT INTO projects (project_name, project_location, client_id, engineer_id, start_date, target_end_date, actual_end_date, status, description, created_at, updated_at)
        VALUES ('Cuenca Learning Center', 'Cuenca, Batangas', demo_client, engineer, '2026-01-05', '2028-03-31', NULL, 'ongoing', 'Demonstration project. Construction in progress.', '2026-01-05 08:00:00', NOW());
    ELSE
        INSERT INTO projects (project_name, location, client_id, engineer_id, start_date, target_end_date, actual_end_date, status, description, created_at, updated_at)
        VALUES ('Cuenca Learning Center', 'Cuenca, Batangas', demo_client, engineer, '2026-01-05', '2028-03-31', NULL, 'ongoing', 'Demonstration project. Construction in progress.', '2026-01-05 08:00:00', NOW());
    END IF;
    SET @demo_project_id = LAST_INSERT_ID();
    INSERT INTO project_supervisors (project_id, supervisor_id, assigned_date, is_active, created_at)
    VALUES (@demo_project_id, supervisor, '2026-01-05', 1, NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Mobilization and Site Preparation', 1, '2026-01-05', '2026-03-24', '2026-01-05', '2026-03-24', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Mobilization and Site Preparation sign-off', '2026-01-05', '2026-03-24', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Foundation Works', 2, '2026-03-25', '2026-06-11', '2026-03-25', '2026-06-11', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Foundation Works sign-off', '2026-03-25', '2026-06-11', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Structural Works', 3, '2026-06-12', '2026-08-31', '2026-06-12', '2026-08-31', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Structural Works sign-off', '2026-06-12', '2026-08-31', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'MEP and Architectural Finishes', 4, '2026-09-01', '2027-12-31', '2026-09-01', NULL, 60, 60, 'Demonstration baseline progress', NOW(), engineer, 'in_progress', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'MEP and Architectural Finishes sign-off', '2026-09-01', '2027-12-31', 0, 0, 'pending', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Inspection and Turnover', 5, '2028-01-01', '2028-03-31', NULL, NULL, 0, 0, 'Demonstration baseline progress', NOW(), engineer, 'not_started', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Inspection and Turnover sign-off', '2028-01-01', '2028-03-31', 0, 0, 'pending', NOW(), NOW());

    -- 16. Padre Garcia Market Wing
    IF has_legacy_location > 0 THEN
        INSERT INTO projects (project_name, project_location, client_id, engineer_id, start_date, target_end_date, actual_end_date, status, description, created_at, updated_at)
        VALUES ('Padre Garcia Market Wing', 'Padre Garcia, Batangas', demo_client, engineer, '2026-02-02', '2028-06-30', NULL, 'ongoing', 'Demonstration project. Construction in progress.', '2026-02-02 08:00:00', NOW());
    ELSE
        INSERT INTO projects (project_name, location, client_id, engineer_id, start_date, target_end_date, actual_end_date, status, description, created_at, updated_at)
        VALUES ('Padre Garcia Market Wing', 'Padre Garcia, Batangas', demo_client, engineer, '2026-02-02', '2028-06-30', NULL, 'ongoing', 'Demonstration project. Construction in progress.', '2026-02-02 08:00:00', NOW());
    END IF;
    SET @demo_project_id = LAST_INSERT_ID();
    INSERT INTO project_supervisors (project_id, supervisor_id, assigned_date, is_active, created_at)
    VALUES (@demo_project_id, supervisor, '2026-02-02', 1, NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Mobilization and Site Preparation', 1, '2026-02-02', '2026-04-12', '2026-02-02', '2026-04-12', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Mobilization and Site Preparation sign-off', '2026-02-02', '2026-04-12', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Foundation Works', 2, '2026-04-13', '2026-06-21', '2026-04-13', '2026-06-21', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Foundation Works sign-off', '2026-04-13', '2026-06-21', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Structural Works', 3, '2026-06-22', '2026-08-31', '2026-06-22', '2026-08-31', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Structural Works sign-off', '2026-06-22', '2026-08-31', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'MEP and Architectural Finishes', 4, '2026-09-01', '2028-03-31', '2026-09-01', NULL, 40, 40, 'Demonstration baseline progress', NOW(), engineer, 'in_progress', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'MEP and Architectural Finishes sign-off', '2026-09-01', '2028-03-31', 0, 0, 'pending', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Inspection and Turnover', 5, '2028-04-01', '2028-06-30', NULL, NULL, 0, 0, 'Demonstration baseline progress', NOW(), engineer, 'not_started', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Inspection and Turnover sign-off', '2028-04-01', '2028-06-30', 0, 0, 'pending', NOW(), NOW());

    -- 17. Ibaan Residential Estate
    IF has_legacy_location > 0 THEN
        INSERT INTO projects (project_name, project_location, client_id, engineer_id, start_date, target_end_date, actual_end_date, status, description, created_at, updated_at)
        VALUES ('Ibaan Residential Estate', 'Ibaan, Batangas', demo_client, engineer, '2026-03-02', '2028-11-30', NULL, 'ongoing', 'Demonstration project. Construction in progress.', '2026-03-02 08:00:00', NOW());
    ELSE
        INSERT INTO projects (project_name, location, client_id, engineer_id, start_date, target_end_date, actual_end_date, status, description, created_at, updated_at)
        VALUES ('Ibaan Residential Estate', 'Ibaan, Batangas', demo_client, engineer, '2026-03-02', '2028-11-30', NULL, 'ongoing', 'Demonstration project. Construction in progress.', '2026-03-02 08:00:00', NOW());
    END IF;
    SET @demo_project_id = LAST_INSERT_ID();
    INSERT INTO project_supervisors (project_id, supervisor_id, assigned_date, is_active, created_at)
    VALUES (@demo_project_id, supervisor, '2026-03-02', 1, NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Mobilization and Site Preparation', 1, '2026-03-02', '2026-04-30', '2026-03-02', '2026-04-30', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Mobilization and Site Preparation sign-off', '2026-03-02', '2026-04-30', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Foundation Works', 2, '2026-05-01', '2026-06-29', '2026-05-01', '2026-06-29', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Foundation Works sign-off', '2026-05-01', '2026-06-29', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Structural Works', 3, '2026-06-30', '2026-08-31', '2026-06-30', '2026-08-31', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Structural Works sign-off', '2026-06-30', '2026-08-31', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'MEP and Architectural Finishes', 4, '2026-09-01', '2028-08-31', '2026-09-01', NULL, 50, 50, 'Demonstration baseline progress', NOW(), engineer, 'in_progress', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'MEP and Architectural Finishes sign-off', '2026-09-01', '2028-08-31', 0, 0, 'pending', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Inspection and Turnover', 5, '2028-09-01', '2028-11-30', NULL, NULL, 0, 0, 'Demonstration baseline progress', NOW(), engineer, 'not_started', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Inspection and Turnover sign-off', '2028-09-01', '2028-11-30', 0, 0, 'pending', NOW(), NOW());

    -- 18. Talisay Hillside Residence (DELAYED)
    IF has_legacy_location > 0 THEN
        INSERT INTO projects (project_name, project_location, client_id, engineer_id, start_date, target_end_date, actual_end_date, status, description, created_at, updated_at)
        VALUES ('Talisay Hillside Residence', 'Talisay, Batangas', demo_client, engineer, '2025-03-03', '2026-08-31', NULL, 'ongoing', 'Demonstration project. Schedule delayed; see phase delay reason.', '2025-03-03 08:00:00', NOW());
    ELSE
        INSERT INTO projects (project_name, location, client_id, engineer_id, start_date, target_end_date, actual_end_date, status, description, created_at, updated_at)
        VALUES ('Talisay Hillside Residence', 'Talisay, Batangas', demo_client, engineer, '2025-03-03', '2026-08-31', NULL, 'ongoing', 'Demonstration project. Schedule delayed; see phase delay reason.', '2025-03-03 08:00:00', NOW());
    END IF;
    SET @demo_project_id = LAST_INSERT_ID();
    INSERT INTO project_supervisors (project_id, supervisor_id, assigned_date, is_active, created_at)
    VALUES (@demo_project_id, supervisor, '2025-03-03', 1, NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Mobilization and Site Preparation', 1, '2025-03-03', '2025-06-19', '2025-03-03', '2025-06-19', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Mobilization and Site Preparation sign-off', '2025-03-03', '2025-06-19', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Foundation Works', 2, '2025-06-20', '2025-10-06', '2025-06-20', '2025-10-06', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Foundation Works sign-off', '2025-06-20', '2025-10-06', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Structural Works', 3, '2025-10-07', '2026-01-23', '2025-10-07', NULL, 35, 35, 'Demonstration baseline progress', NOW(), engineer, 'delayed', 'Weather disruption', 'Demonstration: recovery schedule awaiting confirmation.', NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Structural Works sign-off', '2025-10-07', '2026-01-23', 0, 1, 'delayed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'MEP and Architectural Finishes', 4, '2026-01-24', '2026-05-12', NULL, NULL, 0, 0, 'Demonstration baseline progress', NOW(), engineer, 'not_started', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'MEP and Architectural Finishes sign-off', '2026-01-24', '2026-05-12', 0, 0, 'pending', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Inspection and Turnover', 5, '2026-05-13', '2026-08-31', NULL, NULL, 0, 0, 'Demonstration baseline progress', NOW(), engineer, 'not_started', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Inspection and Turnover sign-off', '2026-05-13', '2026-08-31', 0, 0, 'pending', NOW(), NOW());

    -- 19. Laurel Riverside Apartments (DELAYED)
    IF has_legacy_location > 0 THEN
        INSERT INTO projects (project_name, project_location, client_id, engineer_id, start_date, target_end_date, actual_end_date, status, description, created_at, updated_at)
        VALUES ('Laurel Riverside Apartments', 'Laurel, Batangas', demo_client, engineer, '2025-04-07', '2026-09-30', NULL, 'ongoing', 'Demonstration project. Schedule delayed; see phase delay reason.', '2025-04-07 08:00:00', NOW());
    ELSE
        INSERT INTO projects (project_name, location, client_id, engineer_id, start_date, target_end_date, actual_end_date, status, description, created_at, updated_at)
        VALUES ('Laurel Riverside Apartments', 'Laurel, Batangas', demo_client, engineer, '2025-04-07', '2026-09-30', NULL, 'ongoing', 'Demonstration project. Schedule delayed; see phase delay reason.', '2025-04-07 08:00:00', NOW());
    END IF;
    SET @demo_project_id = LAST_INSERT_ID();
    INSERT INTO project_supervisors (project_id, supervisor_id, assigned_date, is_active, created_at)
    VALUES (@demo_project_id, supervisor, '2025-04-07', 1, NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Mobilization and Site Preparation', 1, '2025-04-07', '2025-07-23', '2025-04-07', '2025-07-23', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Mobilization and Site Preparation sign-off', '2025-04-07', '2025-07-23', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Foundation Works', 2, '2025-07-24', '2025-11-08', '2025-07-24', '2025-11-08', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Foundation Works sign-off', '2025-07-24', '2025-11-08', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Structural Works', 3, '2025-11-09', '2026-02-24', '2025-11-09', NULL, 35, 35, 'Demonstration baseline progress', NOW(), engineer, 'delayed', 'Supplier delivery delay', 'Demonstration: recovery schedule awaiting confirmation.', NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Structural Works sign-off', '2025-11-09', '2026-02-24', 0, 1, 'delayed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'MEP and Architectural Finishes', 4, '2026-02-25', '2026-06-12', NULL, NULL, 0, 0, 'Demonstration baseline progress', NOW(), engineer, 'not_started', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'MEP and Architectural Finishes sign-off', '2026-02-25', '2026-06-12', 0, 0, 'pending', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Inspection and Turnover', 5, '2026-06-13', '2026-09-30', NULL, NULL, 0, 0, 'Demonstration baseline progress', NOW(), engineer, 'not_started', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Inspection and Turnover sign-off', '2026-06-13', '2026-09-30', 0, 0, 'pending', NOW(), NOW());

    -- 20. Agoncillo Municipal Annex (DELAYED)
    IF has_legacy_location > 0 THEN
        INSERT INTO projects (project_name, project_location, client_id, engineer_id, start_date, target_end_date, actual_end_date, status, description, created_at, updated_at)
        VALUES ('Agoncillo Municipal Annex', 'Agoncillo, Batangas', demo_client, engineer, '2025-05-05', '2026-10-15', NULL, 'ongoing', 'Demonstration project. Schedule delayed; see phase delay reason.', '2025-05-05 08:00:00', NOW());
    ELSE
        INSERT INTO projects (project_name, location, client_id, engineer_id, start_date, target_end_date, actual_end_date, status, description, created_at, updated_at)
        VALUES ('Agoncillo Municipal Annex', 'Agoncillo, Batangas', demo_client, engineer, '2025-05-05', '2026-10-15', NULL, 'ongoing', 'Demonstration project. Schedule delayed; see phase delay reason.', '2025-05-05 08:00:00', NOW());
    END IF;
    SET @demo_project_id = LAST_INSERT_ID();
    INSERT INTO project_supervisors (project_id, supervisor_id, assigned_date, is_active, created_at)
    VALUES (@demo_project_id, supervisor, '2025-05-05', 1, NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Mobilization and Site Preparation', 1, '2025-05-05', '2025-08-17', '2025-05-05', '2025-08-17', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Mobilization and Site Preparation sign-off', '2025-05-05', '2025-08-17', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Foundation Works', 2, '2025-08-18', '2025-11-30', '2025-08-18', '2025-11-30', 100, 100, 'Demonstration baseline progress', NOW(), engineer, 'completed', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Foundation Works sign-off', '2025-08-18', '2025-11-30', 1, 0, 'completed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Structural Works', 3, '2025-12-01', '2026-03-15', '2025-12-01', NULL, 35, 35, 'Demonstration baseline progress', NOW(), engineer, 'delayed', 'Permit approval delay', 'Demonstration: recovery schedule awaiting confirmation.', NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Structural Works sign-off', '2025-12-01', '2026-03-15', 0, 1, 'delayed', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'MEP and Architectural Finishes', 4, '2026-03-16', '2026-06-28', NULL, NULL, 0, 0, 'Demonstration baseline progress', NOW(), engineer, 'not_started', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'MEP and Architectural Finishes sign-off', '2026-03-16', '2026-06-28', 0, 0, 'pending', NOW(), NOW());
    INSERT INTO construction_phases (project_id, phase_name, phase_order, planned_start_date, planned_end_date, actual_start_date, actual_end_date, completion_percentage, admin_progress_override, override_reason, override_applied_at, override_applied_by, status, delay_reason, delay_notes, created_at, updated_at)
    VALUES (@demo_project_id, 'Inspection and Turnover', 5, '2026-06-29', '2026-10-15', NULL, NULL, 0, 0, 'Demonstration baseline progress', NOW(), engineer, 'not_started', NULL, NULL, NOW(), NOW());
    SET @demo_phase_id = LAST_INSERT_ID();
    INSERT INTO timeline_milestones (phase_id, milestone_name, start_date, end_date, is_completed, is_delayed, status, created_at, updated_at)
    VALUES (@demo_phase_id, 'Inspection and Turnover sign-off', '2026-06-29', '2026-10-15', 0, 0, 'pending', NOW(), NOW());

    -- Vary project progress and phase dates; add two planning projects.
    SELECT MIN(user_id) INTO @demo_engineer FROM users WHERE role IN ('engineer','admin') AND is_active = 1;
    SELECT MIN(user_id) INTO @demo_supervisor FROM users WHERE role = 'supervisor' AND is_active = 1;
    SELECT MIN(c.client_id) INTO @demo_client FROM clients c JOIN users u ON u.user_id=c.user_id WHERE u.role='client' AND u.is_active=1;
    SET @demo_planning_sql = REPLACE('INSERT INTO projects (project_name, LOCATION_COLUMN, client_id, engineer_id, start_date, target_end_date, status, description, created_at, updated_at) SELECT ''San Pascual Business Center'', ''San Pascual, Batangas'', @demo_client, @demo_engineer, ''2027-01-11'', ''2028-05-31'', ''planning'', ''Demonstration project. Planning and design; construction has not started.'', NOW(), NOW() WHERE @demo_client IS NOT NULL AND @demo_engineer IS NOT NULL AND NOT EXISTS (SELECT 1 FROM projects WHERE project_name=''San Pascual Business Center'')', 'LOCATION_COLUMN', IF(EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='projects' AND COLUMN_NAME='project_location'), 'project_location', 'location'));
    PREPARE demo_planning_stmt FROM @demo_planning_sql;
    EXECUTE demo_planning_stmt;
    DEALLOCATE PREPARE demo_planning_stmt;
    SET @demo_planning_sql = REPLACE('INSERT INTO projects (project_name, LOCATION_COLUMN, client_id, engineer_id, start_date, target_end_date, status, description, created_at, updated_at) SELECT ''Lian Coastal Homes'', ''Lian, Batangas'', @demo_client, @demo_engineer, ''2027-06-07'', ''2028-12-15'', ''planning'', ''Demonstration project. Planning and design; construction has not started.'', NOW(), NOW() WHERE @demo_client IS NOT NULL AND @demo_engineer IS NOT NULL AND NOT EXISTS (SELECT 1 FROM projects WHERE project_name=''Lian Coastal Homes'')', 'LOCATION_COLUMN', IF(EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='projects' AND COLUMN_NAME='project_location'), 'project_location', 'location'));
    PREPARE demo_planning_stmt FROM @demo_planning_sql;
    EXECUTE demo_planning_stmt;
    DEALLOCATE PREPARE demo_planning_stmt;
    INSERT INTO project_supervisors (project_id,supervisor_id,assigned_date,is_active,created_at)
    SELECT p.project_id,@demo_supervisor,'2026-10-26',1,NOW() FROM projects p
    WHERE p.status='planning' AND p.description LIKE 'Demonstration project.%' AND @demo_supervisor IS NOT NULL
    AND NOT EXISTS(SELECT 1 FROM project_supervisors ps WHERE ps.project_id=p.project_id);
    INSERT INTO construction_phases (project_id,phase_name,phase_order,planned_start_date,planned_end_date,status,completion_percentage,admin_progress_override,override_reason,created_at,updated_at)
    SELECT p.project_id, CASE n.num WHEN 1 THEN 'Mobilization and Site Preparation' WHEN 2 THEN 'Foundation Works' WHEN 3 THEN 'Structural Works' WHEN 4 THEN 'MEP and Architectural Finishes' ELSE 'Inspection and Turnover' END,
    n.num, DATE_ADD(p.start_date,INTERVAL FLOOR(DATEDIFF(p.target_end_date,p.start_date)*(n.num-1)/5) DAY), DATE_ADD(p.start_date,INTERVAL FLOOR(DATEDIFF(p.target_end_date,p.start_date)*n.num/5) DAY),
    'not_started',0,0,'Demonstration baseline progress',NOW(),NOW()
    FROM projects p CROSS JOIN (SELECT 1 num UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5)n
    WHERE p.status='planning' AND p.description LIKE 'Demonstration project.%'
    AND NOT EXISTS(SELECT 1 FROM construction_phases ph WHERE ph.project_id=p.project_id AND ph.phase_order=n.num);
    DROP TEMPORARY TABLE IF EXISTS demo_variations;
    CREATE TEMPORARY TABLE demo_variations (project_name VARCHAR(200), progress INT, is_delayed_project INT);
    INSERT INTO demo_variations VALUES
    ('Calaca Commercial Building',92,0),
    ('Balayan Residential Villas',84,0),
    ('Nasugbu Beach Residence',76,0),
    ('San Juan Resort Annex',68,0),
    ('Lemery Medical Clinic',58,0),
    ('Taal Heritage Guesthouse',46,0),
    ('Cuenca Learning Center',34,0),
    ('Padre Garcia Market Wing',22,0),
    ('Ibaan Residential Estate',12,0),
    ('Talisay Hillside Residence',42,1),
    ('Laurel Riverside Apartments',28,1),
    ('Agoncillo Municipal Annex',18,1);
    UPDATE construction_phases ph JOIN projects p ON p.project_id=ph.project_id JOIN demo_variations v ON v.project_name=p.project_name
    SET ph.completion_percentage=LEAST(100,GREATEST(0,v.progress*5-(ph.phase_order-1)*100)),
    ph.admin_progress_override=LEAST(100,GREATEST(0,v.progress*5-(ph.phase_order-1)*100)),
    ph.override_reason='Demonstration baseline progress', ph.updated_at=NOW()
    WHERE p.description LIKE 'Demonstration project.%';
    -- Schedule completed phases before the reference date, current work around it,
    -- and future phases up to each project's individual target date.
    UPDATE construction_phases ph JOIN projects p ON p.project_id=ph.project_id JOIN demo_variations v ON v.project_name=p.project_name
    SET ph.planned_start_date=CASE
      WHEN ph.phase_order<=FLOOR(v.progress/20) THEN DATE_ADD(p.start_date,INTERVAL FLOOR(DATEDIFF('2026-09-01',p.start_date)*(ph.phase_order-1)/GREATEST(1,FLOOR(v.progress/20))) DAY)
      WHEN ph.phase_order=FLOOR(v.progress/20)+1 THEN IF(FLOOR(v.progress/20)=0,p.start_date,'2026-09-01')
      ELSE DATE_ADD('2026-11-01',INTERVAL FLOOR(DATEDIFF(p.target_end_date,'2026-11-01')*(ph.phase_order-FLOOR(v.progress/20)-2)/GREATEST(1,4-FLOOR(v.progress/20))) DAY) END,
    ph.planned_end_date=CASE
      WHEN ph.phase_order<=FLOOR(v.progress/20) THEN DATE_SUB(DATE_ADD(p.start_date,INTERVAL FLOOR(DATEDIFF('2026-09-01',p.start_date)*ph.phase_order/GREATEST(1,FLOOR(v.progress/20))) DAY),INTERVAL 1 DAY)
      WHEN ph.phase_order=FLOOR(v.progress/20)+1 THEN IF(v.is_delayed_project=1,'2026-10-01','2026-10-31')
      ELSE DATE_ADD('2026-11-01',INTERVAL FLOOR(DATEDIFF(p.target_end_date,'2026-11-01')*(ph.phase_order-FLOOR(v.progress/20)-1)/GREATEST(1,4-FLOOR(v.progress/20))) DAY) END
    WHERE p.description LIKE 'Demonstration project.%';
    -- Delayed projects keep their original overdue schedules.
    UPDATE construction_phases ph JOIN projects p ON p.project_id=ph.project_id JOIN demo_variations v ON v.project_name=p.project_name
    SET ph.planned_start_date=DATE_ADD(p.start_date,INTERVAL FLOOR(DATEDIFF(p.target_end_date,p.start_date)*(ph.phase_order-1)/5) DAY),
    ph.planned_end_date=DATE_ADD(p.start_date,INTERVAL FLOOR(DATEDIFF(p.target_end_date,p.start_date)*ph.phase_order/5) DAY)
    WHERE v.is_delayed_project=1 AND p.description LIKE 'Demonstration project.%';
    UPDATE construction_phases ph JOIN projects p ON p.project_id=ph.project_id JOIN demo_variations v ON v.project_name=p.project_name
    SET ph.status=CASE WHEN ph.completion_percentage=100 THEN 'completed' WHEN ph.completion_percentage=0 THEN 'not_started' WHEN v.is_delayed_project=1 THEN 'delayed' ELSE 'in_progress' END,
    ph.actual_start_date=IF(ph.completion_percentage>0,ph.planned_start_date,NULL),
    ph.actual_end_date=IF(ph.completion_percentage=100,ph.planned_end_date,NULL),
    ph.delay_reason=IF(v.is_delayed_project=1 AND ph.completion_percentage BETWEEN 1 AND 99,'Demonstration schedule disruption',NULL)
    WHERE p.description LIKE 'Demonstration project.%';
    -- Replace demo milestones so milestone-based progress agrees with phase values.
    DELETE m FROM timeline_milestones m JOIN construction_phases ph ON ph.phase_id=m.phase_id JOIN projects p ON p.project_id=ph.project_id
    WHERE p.description LIKE 'Demonstration project.%' AND ph.override_reason='Demonstration baseline progress';
    INSERT INTO timeline_milestones (phase_id,milestone_name,start_date,end_date,is_completed,is_delayed,status,created_at,updated_at)
    SELECT ph.phase_id,CONCAT(ph.phase_name,' - Checkpoint ',n.num),ph.planned_start_date,
    DATE_ADD(ph.planned_start_date,INTERVAL FLOOR(DATEDIFF(ph.planned_end_date,ph.planned_start_date)*n.num/10) DAY),
    n.num<=ph.completion_percentage/10,ph.status='delayed' AND n.num>ph.completion_percentage/10,
    CASE WHEN n.num<=ph.completion_percentage/10 THEN 'completed' WHEN ph.status='delayed' THEN 'delayed' ELSE 'pending' END,NOW(),NOW()
    FROM construction_phases ph JOIN projects p ON p.project_id=ph.project_id
    CROSS JOIN (SELECT 1 num UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10)n
    WHERE p.description LIKE 'Demonstration project.%' AND ph.override_reason='Demonstration baseline progress';
    DROP TEMPORARY TABLE demo_variations;

    -- Reports aligned to the revised phase data.
    DROP TEMPORARY TABLE IF EXISTS demo_report_plan;
    CREATE TEMPORARY TABLE demo_report_plan AS
    SELECT p.project_id, ph.phase_id, p.engineer_id, assignment.supervisor_id,
        ph.completion_percentage AS progress_value,
        LEAST(DATE('2026-10-26'), COALESCE(ph.actual_end_date,
            GREATEST(COALESCE(ph.actual_start_date,ph.planned_start_date),DATE('2026-10-26')))) AS sample_date,
        MOD(CRC32(CONCAT(p.project_name,':',ph.phase_order)),4) AS workflow_group,
        CONCAT('[DEMO-2023-2028] ',p.project_name,' - ',ph.phase_name,CHAR(10),
            'Phase accomplishment: ',ph.completion_percentage,'%.',CHAR(10),
            CASE WHEN ph.status='completed' THEN 'Demonstration: phase scope completed and recorded for review.'
                 WHEN ph.status='delayed' THEN CONCAT('Demonstration: work behind schedule. Reason: ',COALESCE(ph.delay_reason,'Schedule disruption'),'. Recovery actions require review.')
                 ELSE 'Demonstration: work in progress; remaining activities are scheduled under this phase.' END,
            CHAR(10),'Fictional demonstration report. No actual site photographs or AI assessment attached.') AS sample_text
    FROM projects p JOIN construction_phases ph ON ph.project_id=p.project_id
    JOIN (SELECT project_id,MIN(supervisor_id) AS supervisor_id FROM project_supervisors WHERE is_active=1 GROUP BY project_id) assignment ON assignment.project_id=p.project_id
    WHERE p.description LIKE 'Demonstration project.%'
      AND ph.override_reason='Demonstration baseline progress'
      AND ph.completion_percentage>0 AND p.status IN ('ongoing','completed');

    -- Supply valid JSON arrays explicitly, including the initial insert.
    INSERT INTO accomplishment_reports (project_id,phase_id,submitted_by,report_date,report_text,
        accomplishment_percentage,site_images,admin_site_images,ai_status,approval_status,
        is_published_to_client,created_at,updated_at)
    SELECT d.project_id,d.phase_id,d.supervisor_id,d.sample_date,d.sample_text,
        d.progress_value,'[]','[]','pending','pending',0,TIMESTAMP(d.sample_date,'09:00:00'),TIMESTAMP(d.sample_date,'09:00:00')
    FROM demo_report_plan d
    WHERE NOT EXISTS (SELECT 1 FROM accomplishment_reports r WHERE r.phase_id=d.phase_id AND r.report_text LIKE '[DEMO-2023-2028]%');

    -- Refresh only marked demo reports; preserve their IDs and real reports.
    -- 0: published; 1: approved/hidden; 2: pending; 3: rejected/hidden.
    UPDATE accomplishment_reports r JOIN demo_report_plan d ON d.phase_id=r.phase_id
    SET r.project_id=d.project_id,r.submitted_by=d.supervisor_id,
        r.report_date=d.sample_date,r.report_text=d.sample_text,r.accomplishment_percentage=d.progress_value,
        r.site_images='[]',r.admin_site_images='[]',r.ai_status='pending',
        r.approval_status=CASE WHEN d.workflow_group IN (0,1) THEN 'approved' WHEN d.workflow_group=2 THEN 'pending' ELSE 'rejected' END,
        r.reviewed_by=IF(d.workflow_group=2,NULL,d.engineer_id),
        r.approved_by=IF(d.workflow_group IN (0,1),d.engineer_id,NULL),
        r.approval_remarks=CASE d.workflow_group WHEN 0 THEN 'Demo: approved and published to client.' WHEN 1 THEN 'Demo: approved internally; not published to client.' WHEN 2 THEN NULL ELSE 'Demo: returned for revision. Add activity breakdown and site evidence before resubmission.' END,
        r.reviewed_at=IF(d.workflow_group=2,NULL,TIMESTAMP(d.sample_date,'14:00:00')),
        r.approved_at=IF(d.workflow_group IN (0,1),TIMESTAMP(d.sample_date,'14:30:00'),NULL),
        r.rejected_at=IF(d.workflow_group=3,TIMESTAMP(d.sample_date,'14:30:00'),NULL),
        r.is_published_to_client=IF(d.workflow_group=0,1,0),
        r.admin_report_text=IF(d.workflow_group=0,d.sample_text,NULL),
        r.admin_explanation=IF(d.workflow_group=0,'Demonstration client update; sample data only.',NULL),
        r.published_at=IF(d.workflow_group=0,TIMESTAMP(d.sample_date,'15:00:00'),NULL),
        r.created_at=TIMESTAMP(d.sample_date,'09:00:00'),r.updated_at=TIMESTAMP(d.sample_date,'15:00:00')
    WHERE r.report_text LIKE '[DEMO-2023-2028]%';

    -- Previously generated reports for phases now unstarted stay private and pending.
    UPDATE accomplishment_reports r JOIN construction_phases ph ON ph.phase_id=r.phase_id
    JOIN projects p ON p.project_id=ph.project_id
    SET r.accomplishment_percentage=0,r.approval_status='pending',r.is_published_to_client=0,
        r.reviewed_by=NULL,r.approved_by=NULL,r.reviewed_at=NULL,r.approved_at=NULL,r.rejected_at=NULL,
        r.published_at=NULL,r.admin_report_text=NULL,r.admin_explanation=NULL,r.site_images='[]',r.admin_site_images='[]',
        r.report_text=CONCAT('[DEMO-2023-2028] ',p.project_name,' - ',ph.phase_name,CHAR(10),'Phase has not started after the demo schedule revision. Draft only; not published.'),
        r.approval_remarks='Demo draft: phase not started.',r.updated_at=NOW()
    WHERE r.report_text LIKE '[DEMO-2023-2028]%' AND p.description LIKE 'Demonstration project.%' AND ph.completion_percentage=0;
    DROP TEMPORARY TABLE demo_report_plan;

    IF (SELECT COUNT(*) FROM projects) <> 22 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Expected exactly 22 demonstration projects.';
    END IF;
    COMMIT;
END$$
DELIMITER ;
CALL dg_reset_demo_2023_2028();
DROP PROCEDURE dg_reset_demo_2023_2028;

-- Verification: 22 projects, 110 phases, 1100 milestones, 3 explicitly delayed projects.
SELECT status, COUNT(*) AS project_count FROM projects GROUP BY status;
SELECT COUNT(DISTINCT project_id) AS explicitly_delayed_projects FROM construction_phases WHERE status = 'delayed';
SELECT MIN(start_date) AS earliest_start, MAX(target_end_date) AS latest_target FROM projects;
SELECT project_id, project_name, status, start_date, target_end_date FROM projects ORDER BY start_date;

SELECT approval_status, is_published_to_client, COUNT(*) AS demo_reports FROM accomplishment_reports WHERE report_text LIKE '[DEMO-2023-2028]%' GROUP BY approval_status, is_published_to_client;
