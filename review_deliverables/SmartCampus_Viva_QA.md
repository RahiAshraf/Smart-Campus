# SmartCampus – 20 Technical Viva Questions and Answers

1. **What problem does SmartCampus solve?**  
   It centralizes school operations such as students, classes, attendance, examinations, results, fees, notifications, transport and role-specific access.

2. **What is the architecture?**  
   A server-rendered PHP application using session authentication, permission helpers, module PHP entry points, mysqli database access and a MySQL/MariaDB-compatible InnoDB schema.

3. **Why use a role-based portal?**  
   Each stakeholder sees only relevant workflows. Portal routing maps roles to dashboards, while permissions control modules and actions.

4. **How is a login verified?**  
   The email is validated, the user record is read from `users`, `password_verify` checks the stored password hash, the session ID is regenerated, and the user is stored in the session.

5. **Why is `session_regenerate_id(true)` important?**  
   It reduces session fixation risk by replacing the session identifier after successful authentication.

6. **How are permissions resolved?**  
   Admin bypasses permissions; otherwise the application checks user-specific overrides, then role defaults, and finally denies access.

7. **What roles are defined?**  
   The permission configuration defines admin, teacher, student, parent and driver. The portal helper also maps helper to the driver dashboard.

8. **How is a teacher prevented from marking arbitrary attendance?**  
   The attendance flow checks class/section scope, timetable existence, day/period validity and teacher assignment authorization before writing.

9. **How is duplicate attendance prevented?**  
   The flow checks class, section, date, mode and period condition before inserting a new attendance header.

10. **Why are attendance records split into two tables?**  
    `attendance` stores the session/header context; `attendance_records` stores one status per student. This avoids repeating the header data for every student.

11. **How are fee payments handled?**  
    The amount must be positive and cannot exceed the current balance. A payment is inserted, paid/balance amounts are recalculated, and status becomes unpaid, partial or paid.

12. **What is the purpose of `result_marks`?**  
    It stores marks associated with a result header, allowing multiple subject/student marks to belong to one result process.

13. **How are SQL injection risks reduced?**  
    The code uses escaping in legacy flows and prepared statements in newer flows such as Student Development. The recommended improvement is to standardize prepared statements everywhere.

14. **What CSRF protection is implemented?**  
    Student Development creates a session token and validates it using `hash_equals`. CSRF protection should be extended consistently to all state-changing forms.

15. **How is uploaded certificate data validated?**  
    The flow checks upload success, MIME type, file size up to 5 MB, extension/path generation and allowed categories before moving the file and inserting metadata.

16. **How does the Student Development schema avoid breaking existing data?**  
    It creates a version table, checks whether version 2 exists, runs the SQL migration only when needed, adds missing columns conditionally and records the applied version.

17. **What database engine features are used?**  
    The dumps use InnoDB, utf8mb4, auto-increment IDs, enum fields, timestamps and relational IDs. The exact foreign-key constraint inventory should be confirmed before claiming full referential enforcement.

18. **What is the difference between authentication and authorization?**  
    Authentication verifies identity; authorization decides what the authenticated user may access. SmartCampus implements both through auth.php and permissions.php.

19. **What is currently verified versus unverified?**  
    The local landing/login screens, source modules and SQL schema were verified. Authenticated dashboards, all role workflows, deployment, performance and automated test coverage were not verified without test accounts/reports.

20. **What would you improve next?**  
    Add automated tests, standardize prepared statements and CSRF protection, centralize error handling/logging, add audit trails, validate all role flows in staging, and document deployment/backups/privacy controls.
