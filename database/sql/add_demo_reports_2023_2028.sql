-- Add or refresh marked demo reports after the project-variations script.
-- Preserves accounts, projects, real reports and existing report IDs.
-- Reference date 2026-10-26; no fabricated images or AI results.
-- Empty image fields use valid JSON '[]', including unpublished reports.
-- Back up first. Import this entire file in phpMyAdmin.
DROP PROCEDURE IF EXISTS dg_refresh_demo_reports_2023_2028;
DELIMITER $$
CREATE PROCEDURE dg_refresh_demo_reports_2023_2028()
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    START TRANSACTION;
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
    COMMIT;
END$$
DELIMITER ;
CALL dg_refresh_demo_reports_2023_2028();
DROP PROCEDURE dg_refresh_demo_reports_2023_2028;
SELECT approval_status,is_published_to_client,COUNT(*) AS demo_reports
FROM accomplishment_reports WHERE report_text LIKE '[DEMO-2023-2028]%'
GROUP BY approval_status,is_published_to_client;
