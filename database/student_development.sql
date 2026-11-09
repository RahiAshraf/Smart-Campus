-- Additive migration for Student Development and transport extensions.
-- Run after mindmerge_schema.sql. All tables use existing users/students/teachers
-- records; no duplicate identity tables are introduced.

CREATE TABLE IF NOT EXISTS sd_clubs (
  club_id INT NOT NULL AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  category ENUM('cultural','technical','literary','social','volunteering','other') NOT NULL DEFAULT 'other',
  description TEXT NULL,
  coordinator_teacher_id INT NULL,
  meeting_schedule VARCHAR(255) NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  registration_open TINYINT(1) NOT NULL DEFAULT 1,
  published TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (club_id),
  KEY idx_sd_clubs_coordinator (coordinator_teacher_id),
  CONSTRAINT fk_sd_clubs_teacher FOREIGN KEY (coordinator_teacher_id) REFERENCES teachers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sd_club_members (
  membership_id INT NOT NULL AUTO_INCREMENT,
  club_id INT NOT NULL,
  student_id INT NOT NULL,
  role ENUM('member','leader') NOT NULL DEFAULT 'member',
  joined_on DATE NOT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (membership_id),
  UNIQUE KEY uq_sd_club_student (club_id, student_id),
  CONSTRAINT fk_sd_club_members_club FOREIGN KEY (club_id) REFERENCES sd_clubs(club_id) ON DELETE CASCADE,
  CONSTRAINT fk_sd_club_members_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sd_club_applications (
  application_id INT NOT NULL AUTO_INCREMENT,
  club_id INT NOT NULL,
  student_id INT NOT NULL,
  status ENUM('pending','approved','rejected','withdrawn') NOT NULL DEFAULT 'pending',
  applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  reviewed_by INT NULL,
  reviewed_at DATETIME NULL,
  review_remarks VARCHAR(255) NULL,
  PRIMARY KEY (application_id),
  UNIQUE KEY uq_sd_club_application (club_id, student_id),
  CONSTRAINT fk_sd_club_app_club FOREIGN KEY (club_id) REFERENCES sd_clubs(club_id) ON DELETE CASCADE,
  CONSTRAINT fk_sd_club_app_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_sd_club_app_reviewer FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sd_events (
  event_id INT NOT NULL AUTO_INCREMENT,
  title VARCHAR(160) NOT NULL,
  event_type ENUM('club','cultural','competition','volunteering','sports','ncc','nss','other') NOT NULL DEFAULT 'other',
  description TEXT NULL,
  event_date DATE NOT NULL,
  venue VARCHAR(160) NULL,
  organizer_user_id INT NULL,
  status ENUM('planned','completed','cancelled') NOT NULL DEFAULT 'planned',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (event_id),
  KEY idx_sd_events_date (event_date),
  CONSTRAINT fk_sd_events_user FOREIGN KEY (organizer_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sd_event_participants (
  participation_id INT NOT NULL AUTO_INCREMENT,
  event_id INT NOT NULL,
  student_id INT NOT NULL,
  attendance_status ENUM('registered','attended','absent') NOT NULL DEFAULT 'registered',
  remarks VARCHAR(255) NULL,
  PRIMARY KEY (participation_id),
  UNIQUE KEY uq_sd_event_student (event_id, student_id),
  CONSTRAINT fk_sd_event_part_event FOREIGN KEY (event_id) REFERENCES sd_events(event_id) ON DELETE CASCADE,
  CONSTRAINT fk_sd_event_part_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sd_sports (
  sport_id INT NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  mode ENUM('indoor','outdoor') NOT NULL,
  coach_teacher_id INT NULL,
  practice_schedule VARCHAR(255) NULL,
  equipment_notes TEXT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  registration_open TINYINT(1) NOT NULL DEFAULT 1,
  published TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (sport_id),
  CONSTRAINT fk_sd_sports_teacher FOREIGN KEY (coach_teacher_id) REFERENCES teachers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sd_sport_applications (
  application_id INT NOT NULL AUTO_INCREMENT,
  sport_id INT NOT NULL,
  student_id INT NOT NULL,
  status ENUM('pending','trial','selected','rejected','withdrawn') NOT NULL DEFAULT 'pending',
  trial_date DATE NULL,
  trial_result VARCHAR(255) NULL,
  applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  reviewed_by INT NULL,
  reviewed_at DATETIME NULL,
  PRIMARY KEY (application_id),
  UNIQUE KEY uq_sd_sport_application (sport_id, student_id),
  CONSTRAINT fk_sd_sport_app_sport FOREIGN KEY (sport_id) REFERENCES sd_sports(sport_id) ON DELETE CASCADE,
  CONSTRAINT fk_sd_sport_app_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_sd_sport_app_reviewer FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sd_sports_teams (
  team_id INT NOT NULL AUTO_INCREMENT,
  sport_id INT NOT NULL,
  name VARCHAR(120) NOT NULL,
  captain_student_id INT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (team_id),
  CONSTRAINT fk_sd_sports_team_sport FOREIGN KEY (sport_id) REFERENCES sd_sports(sport_id) ON DELETE CASCADE,
  CONSTRAINT fk_sd_sports_team_captain FOREIGN KEY (captain_student_id) REFERENCES students(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sd_sports_team_members (
  team_member_id INT NOT NULL AUTO_INCREMENT,
  team_id INT NOT NULL,
  student_id INT NOT NULL,
  joined_on DATE NOT NULL,
  PRIMARY KEY (team_member_id),
  UNIQUE KEY uq_sd_team_student (team_id, student_id),
  CONSTRAINT fk_sd_team_member_team FOREIGN KEY (team_id) REFERENCES sd_sports_teams(team_id) ON DELETE CASCADE,
  CONSTRAINT fk_sd_team_member_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sd_sports_matches (
  match_id INT NOT NULL AUTO_INCREMENT,
  sport_id INT NOT NULL,
  home_team_id INT NULL,
  away_team_id INT NULL,
  match_date DATETIME NOT NULL,
  venue VARCHAR(160) NULL,
  home_score INT NULL,
  away_score INT NULL,
  status ENUM('scheduled','completed','cancelled') NOT NULL DEFAULT 'scheduled',
  PRIMARY KEY (match_id),
  CONSTRAINT fk_sd_match_sport FOREIGN KEY (sport_id) REFERENCES sd_sports(sport_id) ON DELETE CASCADE,
  CONSTRAINT fk_sd_match_home FOREIGN KEY (home_team_id) REFERENCES sd_sports_teams(team_id) ON DELETE SET NULL,
  CONSTRAINT fk_sd_match_away FOREIGN KEY (away_team_id) REFERENCES sd_sports_teams(team_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sd_sports_attendance (
  attendance_id INT NOT NULL AUTO_INCREMENT,
  sport_id INT NOT NULL,
  student_id INT NOT NULL,
  attendance_date DATE NOT NULL,
  status ENUM('present','absent','late','excused') NOT NULL DEFAULT 'present',
  remarks VARCHAR(255) NULL,
  PRIMARY KEY (attendance_id),
  UNIQUE KEY uq_sd_sports_attendance (sport_id, student_id, attendance_date),
  CONSTRAINT fk_sd_sports_att_sport FOREIGN KEY (sport_id) REFERENCES sd_sports(sport_id) ON DELETE CASCADE,
  CONSTRAINT fk_sd_sports_att_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sd_sports_achievements (
  achievement_id INT NOT NULL AUTO_INCREMENT,
  student_id INT NOT NULL,
  sport_id INT NULL,
  title VARCHAR(160) NOT NULL,
  award_level VARCHAR(80) NULL,
  achieved_on DATE NULL,
  description TEXT NULL,
  PRIMARY KEY (achievement_id),
  CONSTRAINT fk_sd_sport_achievement_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_sd_sport_achievement_sport FOREIGN KEY (sport_id) REFERENCES sd_sports(sport_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sd_sports_equipment (
  equipment_id INT NOT NULL AUTO_INCREMENT,
  sport_id INT NULL,
  name VARCHAR(120) NOT NULL,
  quantity INT NOT NULL DEFAULT 0,
  condition_status ENUM('good','needs_repair','retired') NOT NULL DEFAULT 'good',
  location VARCHAR(120) NULL,
  PRIMARY KEY (equipment_id),
  CONSTRAINT fk_sd_equipment_sport FOREIGN KEY (sport_id) REFERENCES sd_sports(sport_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sd_certificates (
  certificate_id INT NOT NULL AUTO_INCREMENT,
  student_id INT NOT NULL,
  category ENUM('technical','academic','sports','cultural','extracurricular') NOT NULL,
  title VARCHAR(180) NOT NULL,
  issuing_organization VARCHAR(160) NULL,
  achievement_date DATE NULL,
  file_path VARCHAR(255) NOT NULL,
  mime_type VARCHAR(80) NOT NULL,
  verification_status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  verified_by INT NULL,
  verified_at DATETIME NULL,
  reviewer_remarks VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (certificate_id),
  CONSTRAINT fk_sd_cert_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_sd_cert_reviewer FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sd_ncc_cadets (
  cadet_id INT NOT NULL AUTO_INCREMENT,
  student_id INT NOT NULL,
  enrollment_no VARCHAR(80) NOT NULL,
  unit_name VARCHAR(120) NULL,
  enrollment_date DATE NOT NULL,
  status ENUM('active','inactive','completed') NOT NULL DEFAULT 'active',
  PRIMARY KEY (cadet_id),
  UNIQUE KEY uq_sd_ncc_student (student_id),
  UNIQUE KEY uq_sd_ncc_enrollment (enrollment_no),
  CONSTRAINT fk_sd_ncc_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sd_programme_settings (
  programme_key ENUM('ncc','nss') NOT NULL,
  unit_name VARCHAR(160) NOT NULL,
  overview TEXT NULL,
  eligibility TEXT NULL,
  registration_open TINYINT(1) NOT NULL DEFAULT 1,
  coordinator_user_id INT NULL,
  PRIMARY KEY (programme_key),
  CONSTRAINT fk_sd_programme_coordinator FOREIGN KEY (coordinator_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sd_programme_applications (
  application_id INT NOT NULL AUTO_INCREMENT,
  programme_key ENUM('ncc','nss') NOT NULL,
  student_id INT NOT NULL,
  status ENUM('pending','approved','rejected','withdrawn') NOT NULL DEFAULT 'pending',
  applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  reviewed_by INT NULL,
  reviewed_at DATETIME NULL,
  review_remarks VARCHAR(255) NULL,
  PRIMARY KEY (application_id),
  UNIQUE KEY uq_sd_programme_application (programme_key, student_id),
  CONSTRAINT fk_sd_programme_app_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_sd_programme_app_reviewer FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sd_ncc_activities (
  activity_id INT NOT NULL AUTO_INCREMENT,
  title VARCHAR(160) NOT NULL,
  activity_type ENUM('training','parade','camp','certificate','other') NOT NULL DEFAULT 'training',
  activity_date DATE NOT NULL,
  venue VARCHAR(160) NULL,
  hours DECIMAL(5,2) NOT NULL DEFAULT 0,
  PRIMARY KEY (activity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sd_ncc_attendance (
  attendance_id INT NOT NULL AUTO_INCREMENT,
  activity_id INT NOT NULL,
  cadet_id INT NOT NULL,
  status ENUM('present','absent','excused') NOT NULL DEFAULT 'present',
  PRIMARY KEY (attendance_id),
  UNIQUE KEY uq_sd_ncc_attendance (activity_id, cadet_id),
  CONSTRAINT fk_sd_ncc_att_activity FOREIGN KEY (activity_id) REFERENCES sd_ncc_activities(activity_id) ON DELETE CASCADE,
  CONSTRAINT fk_sd_ncc_att_cadet FOREIGN KEY (cadet_id) REFERENCES sd_ncc_cadets(cadet_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sd_nss_volunteers (
  volunteer_id INT NOT NULL AUTO_INCREMENT,
  student_id INT NOT NULL,
  enrollment_no VARCHAR(80) NOT NULL,
  enrollment_date DATE NOT NULL,
  status ENUM('active','inactive','completed') NOT NULL DEFAULT 'active',
  PRIMARY KEY (volunteer_id),
  UNIQUE KEY uq_sd_nss_student (student_id),
  UNIQUE KEY uq_sd_nss_enrollment (enrollment_no),
  CONSTRAINT fk_sd_nss_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sd_parent_consents (
  consent_id INT NOT NULL AUTO_INCREMENT,
  student_id INT NOT NULL,
  requested_by INT NOT NULL,
  request_type VARCHAR(80) NOT NULL,
  request_text TEXT NOT NULL,
  response ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  responded_by INT NULL,
  responded_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (consent_id),
  CONSTRAINT fk_sd_consent_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_sd_consent_requester FOREIGN KEY (requested_by) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_sd_consent_responder FOREIGN KEY (responded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sd_nss_activities (
  activity_id INT NOT NULL AUTO_INCREMENT,
  title VARCHAR(160) NOT NULL,
  activity_type ENUM('community_service','plantation','cleanliness','blood_donation','awareness','camp','other') NOT NULL DEFAULT 'other',
  activity_date DATE NOT NULL,
  venue VARCHAR(160) NULL,
  service_hours DECIMAL(5,2) NOT NULL DEFAULT 0,
  PRIMARY KEY (activity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sd_nss_attendance (
  attendance_id INT NOT NULL AUTO_INCREMENT,
  activity_id INT NOT NULL,
  volunteer_id INT NOT NULL,
  status ENUM('present','absent','excused') NOT NULL DEFAULT 'present',
  PRIMARY KEY (attendance_id),
  UNIQUE KEY uq_sd_nss_attendance (activity_id, volunteer_id),
  CONSTRAINT fk_sd_nss_att_activity FOREIGN KEY (activity_id) REFERENCES sd_nss_activities(activity_id) ON DELETE CASCADE,
  CONSTRAINT fk_sd_nss_att_volunteer FOREIGN KEY (volunteer_id) REFERENCES sd_nss_volunteers(volunteer_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sd_transport_documents (
  document_id INT NOT NULL AUTO_INCREMENT,
  bus_id INT NOT NULL,
  document_type ENUM('insurance','permit','fitness','pollution','other') NOT NULL DEFAULT 'other',
  document_number VARCHAR(100) NULL,
  expires_on DATE NULL,
  file_path VARCHAR(255) NULL,
  status ENUM('valid','expiring','expired') NOT NULL DEFAULT 'valid',
  PRIMARY KEY (document_id),
  CONSTRAINT fk_sd_transport_document_bus FOREIGN KEY (bus_id) REFERENCES transport_buses(bus_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sd_transport_trips (
  trip_id INT NOT NULL AUTO_INCREMENT,
  bus_id INT NOT NULL,
  route_id INT NULL,
  started_at DATETIME NOT NULL,
  ended_at DATETIME NULL,
  status ENUM('running','completed','cancelled') NOT NULL DEFAULT 'running',
  PRIMARY KEY (trip_id),
  CONSTRAINT fk_sd_transport_trip_bus FOREIGN KEY (bus_id) REFERENCES transport_buses(bus_id) ON DELETE CASCADE,
  CONSTRAINT fk_sd_transport_trip_route FOREIGN KEY (route_id) REFERENCES transport_routes(route_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sd_transport_boarding_events (
  boarding_event_id INT NOT NULL AUTO_INCREMENT,
  trip_id INT NOT NULL,
  student_id INT NOT NULL,
  event_type ENUM('boarded','dropped_off') NOT NULL,
  recorded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  recorded_by INT NULL,
  PRIMARY KEY (boarding_event_id),
  KEY idx_sd_boarding_trip_student (trip_id, student_id),
  CONSTRAINT fk_sd_boarding_trip FOREIGN KEY (trip_id) REFERENCES sd_transport_trips(trip_id) ON DELETE CASCADE,
  CONSTRAINT fk_sd_boarding_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_sd_boarding_user FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sd_transport_notifications (
  notification_id INT NOT NULL AUTO_INCREMENT,
  trip_id INT NULL,
  student_id INT NULL,
  notification_type ENUM('approaching','arriving','delayed','emergency') NOT NULL,
  message VARCHAR(255) NOT NULL,
  sent_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (notification_id),
  CONSTRAINT fk_sd_transport_notification_trip FOREIGN KEY (trip_id) REFERENCES sd_transport_trips(trip_id) ON DELETE SET NULL,
  CONSTRAINT fk_sd_transport_notification_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO permissions (module_key, action_key, label, sort_order) VALUES
('student_development','view','View Student Development',210),
('student_development','create','Create Student Development Records',211),
('student_development','edit','Edit Student Development Records',212),
('student_development','delete','Delete Student Development Records',213);

INSERT IGNORE INTO role_permissions (role, permission_id, granted)
SELECT r.role, p.permission_id, 1
FROM (SELECT 'admin' role UNION SELECT 'teacher' UNION SELECT 'student' UNION SELECT 'parent') r
JOIN permissions p ON p.module_key = 'student_development'
WHERE p.action_key = 'view';

INSERT IGNORE INTO sd_programme_settings (programme_key, unit_name, overview, eligibility)
VALUES
('ncc', 'NCC Unit', 'National Cadet Corps training and parade programme.', 'Eligibility is reviewed by the authorized NCC coordinator.'),
('nss', 'NSS Unit', 'National Service Scheme community service programme.', 'Eligibility is reviewed by the authorized NSS coordinator.');
