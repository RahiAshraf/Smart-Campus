# SmartCampus – School Management System

![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white)
![MariaDB](https://img.shields.io/badge/MariaDB-10.4-003545?logo=mariadb&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5-7952B3?logo=bootstrap&logoColor=white)
![License](https://img.shields.io/badge/license-not%20specified-lightgrey)

SmartCampus is a role-based, web-based school management system developed during an internship at **Mind Merge Soft Tech Pvt. Ltd.** It centralizes academic and administrative work for administrators, teachers, students, parents, drivers, and transport helpers.

The system covers user access, student and teacher records, attendance, examinations, results, timetables, fees, salaries, notifications, and transport operations in one PHP/MariaDB application.

## Contents

- [Features](#features)
- [User Roles](#user-roles)
- [Technology Stack](#technology-stack)
- [System Architecture](#system-architecture)
- [Project Requirements](#project-requirements)
- [Installation and Execution](#installation-and-execution)
- [Database](#database)
- [Database Tables](#database-tables)
- [Relationships and Data Integrity](#relationships-and-data-integrity)
- [How to Use](#how-to-use)
- [Security Notes](#security-notes)
- [Testing Checklist](#testing-checklist)
- [Troubleshooting](#troubleshooting)
- [Screenshots](#screenshots)
- [Future Improvements](#future-improvements)
- [Project Team](#project-team)
- [License](#license)

## Features

- Secure login with hashed passwords and PHP sessions
- Role-based and user-specific permissions
- Admin, Teacher, Student, Parent, Driver, and Helper accounts
- Student, teacher, class, section, and subject management
- Daily and period-wise student attendance
- Teacher attendance management
- Examination and subject scheduling
- Draft and published result workflows
- Configurable grading system
- Timetable templates, periods, and timetable entries
- Teacher-to-subject/class/section assignments
- Student fee structures, fee allocation, payment tracking, and receipts
- Teacher salary setup and salary-payment records
- Targeted notifications with read-status tracking and reusable templates
- Transport staff, buses, routes, stops, student assignments, and live location
- Contact-message storage
- Responsive dashboards with light/dark theme preference

## User Roles

| Role | Typical access |
| --- | --- |
| **Admin** | Manages users, permissions, academics, attendance, exams, results, fees, salaries, notifications, timetables, and transport. |
| **Teacher** | Views assigned classes and schedules, records permitted attendance, and accesses academic functions granted by the administrator. |
| **Student** | Views personal profile, timetable, attendance, exams, published results, fees, transport details, and notifications as permitted. |
| **Parent** | Monitors linked student information, attendance, results, fees, and announcements. |
| **Driver** | Accesses assigned transport information and transport-related notifications. |
| **Helper** | Supports permitted transport operations. |

Authorization must be checked in PHP on every protected page and action. Hiding an interface control is not sufficient access control.

## Technology Stack

| Layer | Technologies |
| --- | --- |
| Front end | HTML5, CSS3, JavaScript, Bootstrap 5, Material Dashboard/Purple Admin-inspired components, Font Awesome |
| Charts | Chart.js |
| Back end | PHP 8.2, PHP sessions, role/permission checks |
| Database | MariaDB 10.4.32 (MySQL compatible), InnoDB, `utf8mb4` |
| Local server | XAMPP, Apache, phpMyAdmin |
| Version control | Git and GitHub |

## System Architecture

SmartCampus uses a conventional three-layer architecture:

1. **Presentation layer:** browser pages, forms, dashboards, CSS, JavaScript, Bootstrap, and charts.
2. **Application layer:** PHP authentication, validation, authorization, business rules, and database operations.
3. **Data layer:** MariaDB tables, indexes, foreign keys, and transactional records.

```text
Browser → Apache/PHP → Authentication and business logic → MariaDB
Browser ← HTML/JSON  ← Processed application response      ← MariaDB
```

## Project Requirements

### Software

| Software | Minimum/recommended version | Purpose |
| --- | --- | --- |
| Windows, Linux, or macOS | Current supported release | Host operating system |
| XAMPP | A release containing PHP 8.2 | Apache, PHP, MariaDB, phpMyAdmin |
| PHP | 8.2 recommended | Application runtime |
| MariaDB/MySQL | MariaDB 10.4.32 or compatible | Database server |
| Browser | Current Chrome, Edge, Firefox, or Safari | Run and test the system |
| Git | Current stable version | Clone and manage source code |
| Code editor | VS Code or equivalent | Optional development environment |

### Hardware

| Component | Minimum | Recommended |
| --- | --- | --- |
| CPU | 64-bit dual-core | Quad-core or better |
| RAM | 4 GB | 8 GB or more |
| Free storage | 1 GB | 2 GB or more, including backups |
| Display | 1366 × 768 | Full HD |

### PHP Extensions

Confirm that the extensions required by the application are enabled in `php.ini`:

```ini
extension=mysqli
extension=pdo_mysql
extension=mbstring
extension=openssl
extension=fileinfo
```

Restart Apache after changing `php.ini`.

## Installation and Execution

### 1. Download the project

Clone the repository:

```bash
git clone <repository-url>
cd <repository-folder>
```

You may instead download the repository ZIP and extract it.

### 2. Place it in the XAMPP web root

On Windows, use a path such as:

```text
C:\xampp\htdocs\SmartCampus
```

The final folder name becomes part of the local URL.

### 3. Start the services

Open **XAMPP Control Panel** and start:

- Apache
- MySQL

Both services should display a running/green status.

### 4. Create the database

The supplied dump identifies its database as **`mindmerge_db`**.

Open `http://localhost/phpmyadmin`, select the **SQL** tab, and run:

```sql
CREATE DATABASE mindmerge_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;
```

### 5. Import the SQL dump

In phpMyAdmin:

1. Select `mindmerge_db`.
2. Open **Import**.
3. Choose the project SQL dump.
4. Keep the format as **SQL**.
5. Click **Import** and wait for the success message.

The dump was exported by phpMyAdmin 5.2.1 from MariaDB 10.4.32 and PHP 8.2.12. It creates **35 tables** and includes development/sample records.

#### Optional command-line import

```bat
cd C:\xampp\mysql\bin
mysql -u root -p mindmerge_db < C:\path\to\mindmerge_db.sql
```

If the local root account has no password, press Enter at the password prompt. Never use a blank database password in production.

### 6. Configure the PHP connection

Locate the project’s database connection/configuration file and make it match the imported database:

```php
$host = '127.0.0.1';
$port = 3306;
$database = 'mindmerge_db';
$username = 'root';
$password = '';
```

The exact variable names may differ in the project. Do not commit real production credentials.

### 7. Run SmartCampus

Open:

```text
http://localhost/SmartCampus/
```

If the folder has another name, replace `SmartCampus` in the URL. For example, a folder named `SCHOOL-MANAGEMENT-SYSTEM` is opened at `http://localhost/SCHOOL-MANAGEMENT-SYSTEM/`.

### Demo Login Credentials

After importing the supplied SQL dump, use the following development accounts to test each portal:

| Portal / Role | Email | Password |
| --- | --- | --- |
| Administrator | `ashraf@gmail.com` | `11111` |
| Teacher | `teacher@gmail.com` | `11111` |
| Student | `raju@gmail.com` | `11111` |
| Parent | `rbabafakhrudhin@gmail.com` | `11111` |
| Driver | `driver@gmail.com` | `11111` |

> [!WARNING]
> These credentials are intended only for local development and project demonstration. Change every demo password, remove unnecessary sample accounts, and delete personal/sample data before deploying the application or publishing the database dump.

### 8. Verify the first run

1. Confirm that the landing/login page and assets load.
2. Sign in with a development account from the imported database.
3. Confirm redirection to the correct role dashboard.
4. Test read-only screens before changing records.
5. Verify logout and confirm protected pages cannot be reopened without authentication.

## Database

| Property | Value |
| --- | --- |
| Database name in dump | `mindmerge_db` |
| Database engine | MariaDB 10.4.32 / MySQL compatible |
| Storage engine | InnoDB |
| Character set | `utf8mb4` |
| Collation | `utf8mb4_general_ci` |
| Number of tables | 35 |
| Dump generation time | 4 July 2026, 1:29 PM |
| Included content | Schema, indexes, foreign keys, auto-increment settings, and development/sample data |

Verify a successful import:

```sql
USE mindmerge_db;
SHOW TABLES;
SELECT COUNT(*) AS table_count
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'mindmerge_db';
```

The last query should return `35` for this dump.

## Database Tables

The following reference uses the exact table and column names from the supplied SQL dump. “Key columns” highlights the principal identifiers and important fields; it is not a replacement for the full SQL schema.

### Authentication, Profiles, and Permissions

| Table | Purpose | Key columns |
| --- | --- | --- |
| `users` | Central login/profile record for all roles. | `id`, `full_name`, `email`, `phone`, `password`, `role`, `profile_photo`, `theme_preference`, `admin_id` |
| `students` | Student-specific academic and personal details linked to a user. | `id`, `user_id`, `student_id`, `class_id`, `section_id`, `dob`, `gender`, `address`, `parent_phone` |
| `teachers` | Teacher-specific academic and employment details linked to a user. | `id`, `user_id`, `teacher_id`, `class_id`, `section_id`, `subject_name`, `qualification`, `specialization`, `salary` |
| `parents` | Parent account and linked student code. | `id`, `user_id`, `parent_id`, `student_id`, `relationship_name` |
| `permissions` | Defines module/action permissions displayed by the system. | `permission_id`, `module_key`, `action_key`, `label`, `sort_order` |
| `role_permissions` | Grants or denies permissions for `admin`, `teacher`, `student`, `parent`, or `driver` roles. | `role_permission_id`, `role`, `permission_id`, `granted` |
| `user_permissions` | Overrides or assigns a permission for a specific user. | `user_permission_id`, `user_id`, `permission_id`, `granted` |

### Academic Structure and Scheduling

| Table | Purpose | Key columns |
| --- | --- | --- |
| `classes` | Stores class codes, names, descriptions, and active state. | `class_id`, `class_code`, `class_name`, `status` |
| `sections` | Stores sections belonging to a class. | `section_id`, `class_id`, `section_code`, `section_name`, `capacity`, `status` |
| `subjects` | Master list of subjects. | `subject_id`, `subject_code`, `subject_name`, `description`, `status` |
| `teacher_assignments` | Assigns a teacher to a subject, class, and section with an assignment role. | `assignment_id`, `teacher_id`, `subject_id`, `class_id`, `section_id`, `assignment_role` |
| `period_templates` | Defines reusable schedules such as regular, exam, lab, hostel, or custom templates. | `template_id`, `template_code`, `template_name`, `template_type`, `status` |
| `periods` | Defines each period’s time, type, attendance rule, display color, and ordering. | `period_id`, `template_id`, `period_name`, `start_time`, `end_time`, `period_type`, `sort_order` |
| `timetables` | Timetable header for a class, section, template, and academic year. | `timetable_id`, `class_id`, `section_id`, `template_id`, `academic_year`, `effective_from`, `effective_to`, `status` |
| `timetable_entries` | Day/period entries with subject, assigned teacher, room, and remarks. | `entry_id`, `timetable_id`, `day_of_week`, `period_id`, `subject_id`, `teacher_assignment_id`, `room_no` |

### Attendance

| Table | Purpose | Key columns |
| --- | --- | --- |
| `attendance` | Attendance session for a class/section, date, and optional period/subject. Supports `daily` and `period` modes. | `attendance_id`, `class_id`, `section_id`, `attendance_date`, `period_id`, `subject_id`, `teacher_assignment_id`, `attendance_mode` |
| `attendance_records` | Per-student status within an attendance session. | `record_id`, `attendance_id`, `student_id`, `status`, `remarks` |
| `teacher_attendance` | Daily teacher attendance. | `attendance_id`, `teacher_id`, `attendance_date`, `status`, `remarks`, `created_by` |

Student statuses supported by the dump are `present`, `absent`, `late`, `leave`, `medical_leave`, and `od`. Teacher attendance additionally supports `half_day`.

### Examinations, Results, and Grades

| Table | Purpose | Key columns |
| --- | --- | --- |
| `exams` | Exam definition, optional class/section/subject, date, time, marks, and status. | `exam_id`, `exam_name`, `exam_type`, `class_id`, `section_id`, `subject_id`, `total_marks`, `exam_date`, `status` |
| `exam_subjects` | Subject-wise exam schedule and pass/max marks. | `exam_subject_id`, `exam_id`, `class_id`, `subject_id`, `exam_date`, `start_time`, `end_time`, `max_marks`, `pass_marks` |
| `results` | Result batch for an exam/class/section with draft or published state. | `result_id`, `exam_id`, `class_id`, `section_id`, `published_at`, `status` |
| `result_marks` | Student marks belonging to a result batch. | `mark_id`, `result_id`, `student_id`, `marks_obtained`, `remarks` |
| `grading_system` | Configurable mark ranges and grade points. | `grading_id`, `grade_name`, `min_marks`, `max_marks`, `grade_point`, `status` |

### Fees and Salaries

| Table | Purpose | Key columns |
| --- | --- | --- |
| `fee_structures` | Fee definition for a class and academic year. | `fee_structure_id`, `class_id`, `fee_name`, `amount`, `due_date`, `academic_year`, `status` |
| `student_fees` | Assigns a fee structure to a student and tracks paid/balance amounts. | `student_fee_id`, `student_id`, `fee_structure_id`, `amount`, `paid_amount`, `balance_amount`, `payment_status` |
| `fee_payments` | Individual student payment and receipt records. | `payment_id`, `receipt_no`, `student_fee_id`, `amount_paid`, `payment_date`, `payment_method`, `transaction_ref`, `created_by` |
| `teacher_salaries` | Effective monthly salary configuration for teachers. | `salary_id`, `teacher_id`, `monthly_salary`, `effective_from`, `status`, `created_by` |
| `salary_payments` | Teacher salary disbursement and receipt records. | `payment_id`, `receipt_no`, `salary_id`, `teacher_id`, `pay_period`, `amount_paid`, `payment_date`, `payment_method`, `created_by` |

Supported payment methods are `cash`, `upi`, `card`, `bank_transfer`, and `cheque`.

### Notifications and Contact

| Table | Purpose | Key columns |
| --- | --- | --- |
| `notifications` | Notification content, category, source, creator, and time. | `id`, `title`, `message`, `type`, `source_module`, `source_ref`, `created_by`, `created_at` |
| `notification_targets` | Targets a notification by role, class, section, student, or teacher. | `target_id`, `notification_id`, `target_type`, `target_value` |
| `notification_reads` | Tracks notification reads per user. | `read_id`, `notification_id`, `user_id`, `read_at` |
| `notification_templates` | Reusable notification titles and messages. | `template_id`, `template_name`, `type`, `title`, `message` |
| `contact_messages` | Stores messages submitted through the contact form with basic request metadata. | `id`, `name`, `email`, `message`, `ip_address`, `user_agent`, `created_at` |

### Transport

| Table | Purpose | Key columns |
| --- | --- | --- |
| `transport_staff` | Driver/helper profile, contact, license, status, and optional user account. | `staff_id`, `user_id`, `staff_type`, `full_name`, `phone`, `license_number`, `status` |
| `transport_buses` | Bus details and assigned driver/helper. | `bus_id`, `bus_number`, `bus_name`, `capacity`, `driver_id`, `helper_id`, `start_time`, `end_time`, `status` |
| `transport_routes` | Route details, timing, color, and assigned bus. | `route_id`, `bus_id`, `route_name`, `start_time`, `end_time`, `route_description`, `route_color`, `status` |
| `transport_bus_routes` | Bus-to-route mapping with a primary-route flag. | `id`, `bus_id`, `route_id`, `is_primary` |
| `transport_stops` | Ordered route stops with location and expected arrival time. | `stop_id`, `route_id`, `stop_name`, `latitude`, `longitude`, `stop_order`, `arrival_time` |
| `transport_student_assignments` | Assigns a student to a bus and boarding stop. | `assignment_id`, `student_id`, `bus_id`, `stop_id`, `assigned_at` |
| `transport_live_location` | Latest bus coordinates and trip state. | `location_id`, `bus_id`, `latitude`, `longitude`, `updated_at`, `status` |

## Relationships and Data Integrity

The dump declares foreign keys for the main parent-child relationships, including:

- `sections.class_id` → `classes.class_id`
- `students.user_id` and `teachers.user_id` and `parents.user_id` → `users.id`
- `attendance` → `classes`, `sections`, `periods`, and `subjects`
- `attendance_records.attendance_id` → `attendance.attendance_id` with cascading deletion
- `exam_subjects` → `exams` and `subjects`
- `fee_structures.class_id` → `classes.class_id`
- `student_fees` → `students` and `fee_structures`
- `fee_payments` → `student_fees`; its creator may reference `users`
- `periods.template_id` → `period_templates.template_id`
- `role_permissions.permission_id` → `permissions.permission_id`
- `user_permissions` → `users` and `permissions`
- transport buses → transport staff; routes/stops/location mappings → their transport parents

Some columns are logically related but do not have a foreign-key constraint in this dump. For example, several timetable, result, notification, salary, and transport-assignment identifiers are indexed or used by the application without a declared foreign key. Application validation must therefore prevent orphaned or inconsistent records.

Inspect any table after import:

```sql
DESCRIBE users;
SHOW CREATE TABLE attendance;
```

Generate a complete column inventory:

```sql
SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_KEY
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'mindmerge_db'
ORDER BY TABLE_NAME, ORDINAL_POSITION;
```

## How to Use

1. Open the landing page or login page.
2. Sign in with an authorized development account.
3. The system redirects the account to its role-based portal.
4. Use the sidebar/top navigation to open permitted modules.
5. Create or update records only through authorized actions.
6. Log out after use, especially on a shared computer.

> The dump contains development user accounts, password hashes, personal data, and sample operational records. This README intentionally does not publish login credentials. Replace sample accounts and sensitive data before sharing or deploying the database.

## Security Notes

- Use `password_hash()` and `password_verify()`; never store plain-text passwords.
- Enforce session, role, and permission checks server-side.
- Use prepared statements for all user-controlled database values.
- Validate input and escape output to reduce SQL injection and XSS risks.
- Add CSRF tokens to state-changing forms.
- Regenerate the session identifier after successful login.
- Use secure, HTTP-only, SameSite session cookies in production.
- Validate file type, size, name, and storage location for uploads.
- Use HTTPS and non-root database credentials in production.
- Remove development accounts and sample personal data before publishing a database dump.
- Keep configuration files, logs, and database backups outside publicly served directories.
- Back up the database before migrations or bulk operations.

## Testing Checklist

- [ ] Database import completes without errors and reports 35 tables.
- [ ] Landing page, CSS, JavaScript, icons, images, and charts load correctly.
- [ ] Valid accounts reach the correct dashboard; invalid credentials are rejected.
- [ ] Unauthenticated users cannot directly open protected pages.
- [ ] Role and user permission grants/denials are enforced server-side.
- [ ] Student, teacher, class, section, and subject CRUD operations work correctly.
- [ ] Daily and period attendance enforce the expected unique session rules.
- [ ] Student and teacher attendance statuses save and display correctly.
- [ ] Timetable entries use the correct period, subject, teacher assignment, and room.
- [ ] Exams, marks, grades, and publication state are calculated and displayed correctly.
- [ ] Fee balances and salary-payment totals remain accurate after transactions.
- [ ] Notifications reach the intended targets and read status updates correctly.
- [ ] Bus, route, stop, staff, student assignment, and live-location operations work.
- [ ] Forms reject incomplete and invalid input.
- [ ] Logout destroys the session.
- [ ] Pages remain usable on desktop, tablet, and mobile widths.
- [ ] Backup and restore have been tested.

## Troubleshooting

### Apache does not start

- Check whether ports 80 or 443 are already used by IIS, Skype, or another server.
- Change Apache’s port if necessary and include it in the URL, such as `http://localhost:8080/SmartCampus/`.

### MySQL does not start

- Check whether port 3306 is already in use.
- Do not delete the XAMPP data directory to solve startup problems; preserve and back up the database first.

### “Unknown database” or connection error

- Confirm the imported database is named `mindmerge_db`.
- Verify host, port, username, password, and database name in the PHP configuration.
- Confirm MySQL is running.

### “Table not found”

- Select `mindmerge_db` in phpMyAdmin and run `SHOW TABLES;`.
- Re-import the complete SQL dump if fewer than 35 tables exist.

### Blank page or HTTP 500

- Check the Apache/PHP error log.
- Temporarily enable PHP error reporting only in a local development environment.
- Confirm the PHP version and required extensions.

### CSS, JavaScript, or images do not load

- Confirm the browser URL matches the folder under `htdocs`.
- Check absolute and relative asset paths.
- Hard refresh with `Ctrl + Shift + R`.

### Login fails after import

- Confirm the application uses `password_verify()` against the hash in `users.password`.
- Do not replace hashes with plain-text values.
- If the original passwords are unknown, create a new authorized development account using the project’s account-creation flow.

## Screenshots

Add current images under `docs/screenshots/` and update these paths if necessary:

| Screen | Suggested path |
| --- | --- |
| Landing Page | `docs/screenshots/landing-page.png` |
| Login Page | `docs/screenshots/login.png` |
| Admin Dashboard | `docs/screenshots/admin-dashboard.png` |
| Teacher Dashboard | `docs/screenshots/teacher-dashboard.png` |
| Student Dashboard | `docs/screenshots/student-dashboard.png` |
| Parent Dashboard | `docs/screenshots/parent-dashboard.png` |
| Attendance | `docs/screenshots/attendance.png` |
| Examinations | `docs/screenshots/examinations.png` |
| Results | `docs/screenshots/results.png` |
| Fees | `docs/screenshots/fees.png` |
| Timetable | `docs/screenshots/timetable.png` |
| Transport | `docs/screenshots/transport.png` |

Example:

```markdown
![Admin Dashboard](docs/screenshots/admin-dashboard.png)
```

## Future Improvements

- Email, SMS, and push-notification integrations
- OTP verification and secure password recovery
- Google or institutional single sign-on
- Progressive Web App or mobile application
- Bulk student/staff import and export
- Advanced analytical reports and downloadable documents
- Automated backups and tested recovery procedures
- Complete foreign-key coverage for logically related identifiers
- REST API for approved integrations
- Production deployment with HTTPS, centralized logging, monitoring, and rate limiting

## Project Team

Developed as an internship project by a 10-member team at **Mind Merge Soft Tech Pvt. Ltd.**

- **Team Lead:** N. Ashraf Vali
- **Supportive Team Lead:** G. Akshaya
- **Academic Mentor:** Dr. G. Sreenivasulu
- **Internship Coordinator:** Mr. Ch. Hemanand
- **Department:** Computer Science and Engineering
- **Institution:** Madanapalle Institute of Technology & Science (MITS)

Add the remaining members after confirming their preferred names and contributions:

| Name | Role / contribution |
| --- | --- |
| N. Ashraf Vali | Team Lead; integration and project development |
| G. Akshaya | Supportive Team Lead; project development |
| _Add member_ | _Add contribution_ |

## Acknowledgements

We thank **Mind Merge Soft Tech Pvt. Ltd.**, the academic mentor, internship coordinator, faculty members, and all team members who supported the planning, development, testing, and completion of SmartCampus.

## License

No open-source license has been specified. Unless the repository owner adds a license, the source remains protected by the owner’s default copyright rights. Add a `LICENSE` file only after selecting and approving suitable terms.

---

**SmartCampus** — simplifying school administration through one secure, centralized platform.
