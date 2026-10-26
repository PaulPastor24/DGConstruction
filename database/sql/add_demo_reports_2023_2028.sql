-- ADDITIVE ONLY: run after the 22 demo projects have been inserted.
-- Does not delete projects, reports, or accounts. Repeat runs skip existing demo phase reports.
-- Requires the current migrated report schema. Reference date: 2026-10-26.
START TRANSACTION;

-- Demonstration reports: one per completed or started demo phase (count depends on started phases).
-- No fabricated photographs or AI results are inserted.
INSERT INTO accomplishment_reports (
    project_id, phase_id, submitted_by, reviewed_by, approved_by,
    report_date, report_text, accomplishment_percentage, site_images,
    ai_status, approval_status, approval_remarks, reviewed_at, approved_at,
    rejected_at, is_published_to_client, admin_report_text, admin_site_images,
    admin_explanation, published_at, created_at, updated_at
)
SELECT
    p.project_id, ph.phase_id, assignment.supervisor_id,
    CASE WHEN ph.status = 'completed' OR MOD(p.project_id, 3) <> 0 THEN p.engineer_id ELSE NULL END,
    CASE WHEN ph.status = 'completed' OR MOD(p.project_id, 3) = 1 THEN p.engineer_id ELSE NULL END,
    CASE WHEN ph.status = 'completed' THEN ph.actual_end_date ELSE '2026-10-26' END,
    CONCAT('[DEMO-2023-2028] ', p.project_name, ' - ', ph.phase_name, CHAR(10),
        'Phase accomplishment: ', ph.completion_percentage, '%.', CHAR(10),
        CASE
            WHEN ph.status = 'completed' THEN 'Scope completed. Site inspection and phase sign-off recorded for demonstration.'
            WHEN ph.status = 'delayed' THEN CONCAT('Work is behind schedule. Cause: ', COALESCE(ph.delay_reason, 'Schedule disruption'), '. Recovery plan and material availability require review.')
            ELSE 'Works are progressing. Completed activities were inspected; remaining activities are scheduled for the next reporting period.'
        END, CHAR(10), 'Sample report for demonstration only; no actual site photographs or AI analysis attached.'),
    ph.completion_percentage, JSON_ARRAY(), 'pending',
    CASE WHEN ph.status = 'completed' THEN 'approved'
         WHEN MOD(p.project_id, 3) = 0 THEN 'pending'
         WHEN MOD(p.project_id, 3) = 1 THEN 'approved' ELSE 'rejected' END,
    CASE WHEN ph.status = 'completed' THEN 'Demo: completed phase reviewed and published.'
         WHEN MOD(p.project_id, 3) = 0 THEN NULL
         WHEN MOD(p.project_id, 3) = 1 THEN 'Demo: approved; awaiting publication.'
         ELSE 'Demo: provide site photographs and a detailed activity breakdown before resubmission.' END,
    CASE WHEN ph.status = 'completed' THEN TIMESTAMP(ph.actual_end_date, '16:00:00')
         WHEN MOD(p.project_id, 3) <> 0 THEN '2026-10-26 16:00:00' ELSE NULL END,
    CASE WHEN ph.status = 'completed' THEN TIMESTAMP(ph.actual_end_date, '16:00:00')
         WHEN MOD(p.project_id, 3) = 1 THEN '2026-10-26 16:00:00' ELSE NULL END,
    CASE WHEN ph.status <> 'completed' AND MOD(p.project_id, 3) = 2 THEN '2026-10-26 16:00:00' ELSE NULL END,
    CASE WHEN ph.status = 'completed' THEN 1 ELSE 0 END,
    CASE WHEN ph.status = 'completed' THEN CONCAT('Demonstration update: ', ph.phase_name, ' is complete at ', ph.completion_percentage, '%.') ELSE NULL END,
    CASE WHEN ph.status = 'completed' THEN JSON_ARRAY() ELSE NULL END,
    CASE WHEN ph.status = 'completed' THEN 'Sample client publication; no actual inspection evidence is attached.' ELSE NULL END,
    CASE WHEN ph.status = 'completed' THEN TIMESTAMP(ph.actual_end_date, '16:30:00') ELSE NULL END,
    CASE WHEN ph.status = 'completed' THEN TIMESTAMP(ph.actual_end_date, '15:00:00') ELSE '2026-10-26 15:00:00' END,
    CASE WHEN ph.status = 'completed' THEN TIMESTAMP(ph.actual_end_date, '16:30:00') ELSE '2026-10-26 16:00:00' END
FROM projects p
JOIN construction_phases ph ON ph.project_id = p.project_id
JOIN (
    SELECT project_id, MIN(supervisor_id) AS supervisor_id
    FROM project_supervisors WHERE is_active = 1 GROUP BY project_id
) assignment ON assignment.project_id = p.project_id
WHERE p.description LIKE 'Demonstration project.%'
  AND ph.override_reason = 'Demonstration baseline progress'
  AND ph.completion_percentage > 0
  AND NOT EXISTS (
      SELECT 1 FROM accomplishment_reports existing
      WHERE existing.phase_id = ph.phase_id
        AND existing.report_text LIKE '[DEMO-2023-2028]%'
  );

COMMIT;
SELECT approval_status, is_published_to_client, COUNT(*) AS demo_reports
FROM accomplishment_reports WHERE report_text LIKE '[DEMO-2023-2028]%'
GROUP BY approval_status, is_published_to_client;
