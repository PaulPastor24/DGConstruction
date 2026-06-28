-- DG Construction System
-- FULL REVISED SCHEMA (based on current requirements)
-- Changes:
-- Removed ai_analysis_results
-- Added worker_biometric_profiles
-- attendance_logs now uses deployment_id
-- Worker fingerprint attendance retained

CREATE TABLE users (
  user_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','engineer','supervisor','client') NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
);

CREATE TABLE clients (
  client_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  company_name VARCHAR(200),
  address TEXT,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  FOREIGN KEY (user_id) REFERENCES users(user_id)
);

CREATE TABLE projects (
  project_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  client_id INT UNSIGNED NOT NULL,
  project_name VARCHAR(200) NOT NULL,
  location TEXT,
  start_date DATE,
  target_end_date DATE,
  status VARCHAR(50),
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  FOREIGN KEY (client_id) REFERENCES clients(client_id)
);

CREATE TABLE workers (
  worker_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  trade VARCHAR(100),
  contact_number VARCHAR(20),
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
);

CREATE TABLE worker_biometric_profiles (
  biometric_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  worker_id INT UNSIGNED NOT NULL,
  fingerprint_template LONGBLOB NOT NULL,
  enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  enrolled_by BIGINT UNSIGNED,
  FOREIGN KEY (worker_id) REFERENCES workers(worker_id),
  FOREIGN KEY (enrolled_by) REFERENCES users(user_id)
);

CREATE TABLE project_supervisors (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id INT UNSIGNED NOT NULL,
  supervisor_id BIGINT UNSIGNED NOT NULL,
  FOREIGN KEY (project_id) REFERENCES projects(project_id),
  FOREIGN KEY (supervisor_id) REFERENCES users(user_id)
);

CREATE TABLE project_workers (
  deployment_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id INT UNSIGNED NOT NULL,
  worker_id INT UNSIGNED NOT NULL,
  deployed_date DATE NOT NULL,
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (project_id) REFERENCES projects(project_id),
  FOREIGN KEY (worker_id) REFERENCES workers(worker_id)
);

CREATE TABLE attendance_logs (
  log_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  deployment_id INT UNSIGNED NOT NULL,
  recorded_by BIGINT UNSIGNED NOT NULL,
  log_date DATE NOT NULL,
  time_in TIME NULL,
  time_out TIME NULL,
  status ENUM('present','absent','half_day','on_leave') DEFAULT 'present',
  remarks TEXT,
  biometric_matched BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (deployment_id) REFERENCES project_workers(deployment_id),
  FOREIGN KEY (recorded_by) REFERENCES users(user_id)
);

CREATE TABLE construction_phases (
  phase_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id INT UNSIGNED NOT NULL,
  phase_name VARCHAR(200) NOT NULL,
  phase_order INT UNSIGNED NOT NULL,
  planned_start_date DATE,
  planned_end_date DATE,
  actual_start_date DATE,
  actual_end_date DATE,
  completion_percentage DECIMAL(5,2) DEFAULT 0,
  status ENUM('not_started','in_progress','completed','delayed') DEFAULT 'not_started',
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  FOREIGN KEY (project_id) REFERENCES projects(project_id)
);

CREATE TABLE timeline_milestones (
  milestone_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  phase_id INT UNSIGNED NOT NULL,
  milestone_name VARCHAR(200) NOT NULL,
  target_date DATE,
  completion_percentage DECIMAL(5,2) DEFAULT 0,
  status VARCHAR(50),
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  FOREIGN KEY (phase_id) REFERENCES construction_phases(phase_id)
);

CREATE TABLE accomplishment_reports (
  report_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id INT UNSIGNED NOT NULL,
  phase_id INT UNSIGNED NOT NULL,
  submitted_by BIGINT UNSIGNED NOT NULL,
  report_date DATE NOT NULL,
  report_text LONGTEXT NOT NULL,
  site_images JSON NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  FOREIGN KEY (project_id) REFERENCES projects(project_id),
  FOREIGN KEY (phase_id) REFERENCES construction_phases(phase_id),
  FOREIGN KEY (submitted_by) REFERENCES users(user_id)
);

CREATE TABLE system_logs (
  log_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED,
  action VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id)
);
