-- Run the entire script in phpMyAdmin's SQL tab with the application database selected.
-- Assigns existing non-archived projects, including completed projects, to four active supervisors.
-- Previous assignments are deactivated, not deleted. Does not implement future auto-assignment.

START TRANSACTION;

DROP TEMPORARY TABLE IF EXISTS dg_distribution_supervisors;
DROP TEMPORARY TABLE IF EXISTS dg_distribution_projects;
DROP TEMPORARY TABLE IF EXISTS dg_distribution_plan;

CREATE TEMPORARY TABLE dg_distribution_supervisors (
    position INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    supervisor_id BIGINT UNSIGNED NOT NULL
) ENGINE=InnoDB;

INSERT INTO dg_distribution_supervisors (supervisor_id)
SELECT user_id FROM `users`
WHERE role = 'supervisor' AND is_active = 1
ORDER BY user_id;

SET @dg_supervisor_count = (SELECT COUNT(*) FROM dg_distribution_supervisors);

CREATE TEMPORARY TABLE dg_distribution_projects (
    position INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NOT NULL
) ENGINE=InnoDB;

INSERT INTO dg_distribution_projects (project_id)
SELECT project_id FROM `projects`
WHERE status IS NULL OR status <> 'archived'
ORDER BY project_id;

CREATE TEMPORARY TABLE dg_distribution_plan (
    project_id BIGINT UNSIGNED NOT NULL PRIMARY KEY,
    supervisor_id BIGINT UNSIGNED NOT NULL
) ENGINE=InnoDB;

-- An empty plan guarantees no assignment changes unless exactly four supervisors exist.
INSERT INTO dg_distribution_plan (project_id, supervisor_id)
SELECT p.project_id, s.supervisor_id
FROM dg_distribution_projects AS p
JOIN dg_distribution_supervisors AS s ON s.position = MOD(p.position - 1, 4) + 1
WHERE @dg_supervisor_count = 4;

UPDATE `project_supervisors` AS a
JOIN dg_distribution_plan AS p ON p.project_id = a.project_id
SET a.is_active = 0
WHERE a.supervisor_id <> p.supervisor_id;

UPDATE `project_supervisors` AS a
JOIN dg_distribution_plan AS p
    ON p.project_id = a.project_id AND p.supervisor_id = a.supervisor_id
SET a.assigned_date = IF(a.is_active = 1, a.assigned_date, CURRENT_DATE()),
    a.is_active = 1;

INSERT INTO `project_supervisors` (project_id, supervisor_id, assigned_date, is_active)
SELECT p.project_id, p.supervisor_id, CURRENT_DATE(), 1
FROM dg_distribution_plan AS p
WHERE NOT EXISTS (
    SELECT 1 FROM `project_supervisors` AS a
    WHERE a.project_id = p.project_id AND a.supervisor_id = p.supervisor_id
);

COMMIT;

SELECT IF(@dg_supervisor_count = 4,
    'Project distribution completed.',
    CONCAT('No changes made: expected 4 active supervisors, found ', @dg_supervisor_count)) AS result;

SELECT u.user_id, CONCAT_WS(' ', u.first_name, u.last_name) AS supervisor,
    u.email, COUNT(DISTINCT p.project_id) AS assigned_projects
FROM dg_distribution_supervisors AS s
JOIN `users` AS u ON u.user_id = s.supervisor_id
LEFT JOIN `project_supervisors` AS a ON a.supervisor_id = u.user_id AND a.is_active = 1
LEFT JOIN `projects` AS p ON p.project_id = a.project_id AND (p.status IS NULL OR p.status <> 'archived')
GROUP BY u.user_id, u.first_name, u.last_name, u.email
ORDER BY u.user_id;

DROP TEMPORARY TABLE dg_distribution_plan;
DROP TEMPORARY TABLE dg_distribution_projects;
DROP TEMPORARY TABLE dg_distribution_supervisors;
