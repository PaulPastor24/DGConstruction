-- Updates existing demo data only; adds 2 planning projects (22 total).
-- Run before adding demonstration reports. Back up first.
START TRANSACTION;
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

COMMIT;
SELECT p.project_name,p.status,ROUND(AVG(ph.completion_percentage),2) AS progress FROM projects p JOIN construction_phases ph ON ph.project_id=p.project_id WHERE p.description LIKE 'Demonstration project.%' GROUP BY p.project_id,p.project_name,p.status ORDER BY progress DESC;
