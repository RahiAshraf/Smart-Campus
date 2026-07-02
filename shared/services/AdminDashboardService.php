<?php

require_once __DIR__ . '/../../config/notifications.php';
require_once __DIR__ . '/ExamService.php';

class AdminDashboardService
{
    private mysqli $conn;
    private array $tableCache = [];
    private array $columnCache = [];

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function getStats(): array
    {
        $total_students = $this->scalar("SELECT COUNT(*) FROM students");
        $total_teachers = $this->scalar("SELECT COUNT(*) FROM teachers");
        $total_classes  = $this->scalar("SELECT COUNT(*) FROM classes WHERE status='active'");
        $total_sections = $this->scalar("SELECT COUNT(*) FROM sections WHERE status='active'");
        $total_transport = $this->scalar("SELECT COUNT(*) FROM transport_buses WHERE status='active'");
        $fee_pending = $this->scalarFloat("SELECT COALESCE(SUM(balance_amount),0) FROM student_fees");

        $student_rate = $this->attendanceRate(
            "SELECT COUNT(*) FROM attendance_records ar
             INNER JOIN attendance a ON a.attendance_id = ar.attendance_id
             WHERE ar.status != 'absent'"
        );

        $teacher_present = $this->scalar(
            "SELECT COUNT(*) FROM teacher_attendance
             WHERE status IN ('present','late','half_day')"
        );
        $teacher_total = $this->scalar("SELECT COUNT(*) FROM teacher_attendance");
        $teacher_rate = $teacher_total > 0
            ? round(($teacher_present / $teacher_total) * 100, 2)
            : 0;

        $uid = (int) ($_SESSION['user']['id'] ?? 0);
        $context = notification_user_context($this->conn, $uid, 'admin');
        $unread = notification_unread_count($this->conn, $context);

        return [
            'total_students'         => $total_students,
            'total_teachers'         => $total_teachers,
            'total_classes'          => $total_classes,
            'total_sections'         => $total_sections,
            'total_transport'        => $total_transport,
            'fee_pending'            => $fee_pending,
            'attendance_rate'        => $student_rate,
            'teacher_attendance_rate'=> $teacher_rate,
            'unread_notifications'   => $unread,
            'upcoming_exams'         => $this->upcomingExamCount(),
        ];
    }

    public function getMonthlyAttendanceTrend(int $months = 6): array
    {
        $labels = [];
        $values = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = new DateTime("first day of -$i months");
            $month = $date->format('Y-m');
            $labels[] = $date->format('M Y');

            $present = $this->scalar(
                "SELECT COUNT(*) FROM attendance_records ar
                 INNER JOIN attendance a ON a.attendance_id = ar.attendance_id
                 WHERE DATE_FORMAT(a.attendance_date,'%Y-%m') = '$month'
                   AND ar.status != 'absent'"
            );
            $total = $this->scalar(
                "SELECT COUNT(*) FROM attendance_records ar
                 INNER JOIN attendance a ON a.attendance_id = ar.attendance_id
                 WHERE DATE_FORMAT(a.attendance_date,'%Y-%m') = '$month'"
            );

            $values[] = $total > 0 ? round(($present / $total) * 100, 2) : 0;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    public function getClassAttendanceComparison(): array
    {
        $labels = [];
        $values = [];

        if (!$this->tablesExist(['attendance', 'attendance_records', 'classes'])) {
            return ['labels' => $labels, 'values' => $values];
        }

        $query = mysqli_query(
            $this->conn,
            "SELECT c.class_name,
                    COUNT(ar.record_id) AS total,
                    SUM(CASE WHEN ar.status != 'absent' THEN 1 ELSE 0 END) AS present
             FROM attendance a
             INNER JOIN classes c ON c.class_id = a.class_id
             INNER JOIN attendance_records ar ON ar.attendance_id = a.attendance_id
             GROUP BY c.class_id, c.class_name
             ORDER BY c.class_name ASC
             LIMIT 10"
        );

        while ($query && $row = mysqli_fetch_assoc($query)) {
            $labels[] = $row['class_name'];
            $rate = (int) $row['total'] > 0
                ? round(((int) $row['present'] / (int) $row['total']) * 100, 2)
                : 0;
            $values[] = $rate;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    public function getTeacherAttendanceTrend(int $months = 6): array
    {
        $labels = [];
        $values = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = new DateTime("first day of -$i months");
            $month = $date->format('Y-m');
            $labels[] = $date->format('M Y');

            $present = $this->scalar(
                "SELECT COUNT(*) FROM teacher_attendance
                 WHERE DATE_FORMAT(attendance_date,'%Y-%m') = '$month'
                   AND status IN ('present','late','half_day')"
            );
            $total = $this->scalar(
                "SELECT COUNT(*) FROM teacher_attendance
                 WHERE DATE_FORMAT(attendance_date,'%Y-%m') = '$month'"
            );

            $values[] = $total > 0 ? round(($present / $total) * 100, 2) : 0;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    public function getStudentGrowthTrend(int $months = 6): array
    {
        $labels = [];
        $values = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = new DateTime("first day of -$i months");
            $end = $date->format('Y-m-t');
            $labels[] = $date->format('M Y');

            $values[] = $this->scalar(
                "SELECT COUNT(*) FROM students
                 WHERE DATE(created_at) <= '$end'"
            );
        }

        return ['labels' => $labels, 'values' => $values];
    }

    public function getInsights(): array
    {
        $best = null;
        $lowest = null;
        if (!$this->tablesExist(['attendance', 'attendance_records'])) {
            return [
                'best_class'          => $best,
                'lowest_class'        => $lowest,
                'critical_students'   => 0,
                'critical_teachers'   => 0,
                'pending_sessions'    => 0,
            ];
        }

        $query = mysqli_query(
            $this->conn,
            "SELECT c.class_name, s.section_name,
                    COUNT(ar.record_id) AS total,
                    SUM(CASE WHEN ar.status != 'absent' THEN 1 ELSE 0 END) AS present
             FROM attendance a
             INNER JOIN classes c ON c.class_id = a.class_id
             INNER JOIN sections s ON s.section_id = a.section_id
             INNER JOIN attendance_records ar ON ar.attendance_id = a.attendance_id
             GROUP BY a.class_id, a.section_id, c.class_name, s.section_name
             HAVING total > 0"
        );

        $classes = [];

        while ($query && $row = mysqli_fetch_assoc($query)) {
            $rate = round(((int) $row['present'] / (int) $row['total']) * 100, 2);
            $name = $row['class_name'] . ' - ' . $row['section_name'];
            $classes[] = ['name' => $name, 'rate' => $rate];
        }

        if (!empty($classes)) {
            usort($classes, fn($a, $b) => $b['rate'] <=> $a['rate']);
            $best = $classes[0];
            $lowest = $classes[count($classes) - 1];
        }

        $critical_students = $this->scalar(
            "SELECT COUNT(*) FROM (
                SELECT st.id
                FROM students st
                INNER JOIN attendance_records ar ON ar.student_id = st.id
                INNER JOIN attendance a ON a.attendance_id = ar.attendance_id
                GROUP BY st.id
                HAVING (SUM(CASE WHEN ar.status != 'absent' THEN 1 ELSE 0 END) / COUNT(*)) * 100 < 75
             ) low_attendance"
        );

        $pending_sessions = $this->scalar(
            "SELECT COUNT(DISTINCT CONCAT(s.class_id,'-',s.section_id))
             FROM sections s
             WHERE NOT EXISTS (
                SELECT 1 FROM attendance a
                WHERE a.class_id = s.class_id
                  AND a.section_id = s.section_id
                  AND a.attendance_date = CURDATE()
             )"
        );

        return [
            'best_class'          => $best,
            'lowest_class'        => $lowest,
            'critical_students'   => $critical_students,
            'critical_teachers'   => 0,
            'pending_sessions'    => $pending_sessions,
        ];
    }

    public function getRecentNotifications(int $limit = 5): array
    {
        if (!$this->tableExists('notifications')) {
            return [];
        }

        $uid = (int) ($_SESSION['user']['id'] ?? 0);
        $context = notification_user_context($this->conn, $uid, 'admin');

        return notification_recent_for_context($this->conn, $context, $limit);
    }
    public function getInstitutionOverview(): array
{
    return [
        'students' => $this->scalar("SELECT COUNT(*) FROM students"),
        'teachers' => $this->scalar("SELECT COUNT(*) FROM teachers"),
        'parents' => $this->scalar("SELECT COUNT(*) FROM parents"),
        'classes' => $this->scalar("SELECT COUNT(*) FROM classes WHERE status='active'"),
        'sections' => $this->scalar("SELECT COUNT(*) FROM sections WHERE status='active'"),
        'subjects' => $this->scalar("SELECT COUNT(*) FROM subjects"),
    ];
}
public function getFinanceOverview(): array
{
    $salaryExpense = $this->scalarFloat("
        SELECT COALESCE(SUM(amount_paid),0)
        FROM salary_payments
    ");
    $feeCollected = $this->scalarFloat("
        SELECT COALESCE(SUM(amount_paid),0)
        FROM fee_payments
    ");

    return [
        'total_assigned' => $this->scalarFloat("
            SELECT COALESCE(SUM(amount),0)
            FROM student_fees
        "),

        'total_collected' => $feeCollected,

        'pending_amount' => $this->scalarFloat("
            SELECT COALESCE(SUM(balance_amount),0)
            FROM student_fees
        "),

        'pending_students' => $this->scalar("
            SELECT COUNT(*)
            FROM student_fees
            WHERE payment_status!='paid'
        "),

        'today_collection' => $this->scalarFloat("
            SELECT COALESCE(SUM(amount_paid),0)
            FROM fee_payments
            WHERE DATE(payment_date)=CURDATE()
        "),

        'salary_expense' => $salaryExpense,
        'net_balance' => $feeCollected - $salaryExpense,
    ];
}

public function getSalaryOverview(): array
{
    return [
        'active_records' => $this->scalar("
            SELECT COUNT(*)
            FROM teacher_salaries
            WHERE status='active'
        "),
        'active_payroll' => $this->scalarFloat("
            SELECT COALESCE(SUM(monthly_salary),0)
            FROM teacher_salaries
            WHERE status='active'
        "),
        'paid_this_month' => $this->scalarFloat("
            SELECT COALESCE(SUM(amount_paid),0)
            FROM salary_payments
            WHERE pay_period = DATE_FORMAT(CURDATE(), '%Y-%m')
        "),
        'total_paid' => $this->scalarFloat("
            SELECT COALESCE(SUM(amount_paid),0)
            FROM salary_payments
        "),
    ];
}
public function getExamOverview(): array
{
    $examStats = (new ExamService($this->conn))->getExamStatistics();

    return [

        'total_exams' => $examStats['total_exams'],

        'upcoming_exams' => $examStats['upcoming_exams'],

        'published_results' => $this->scalar("
            SELECT COUNT(*)
            FROM results
            WHERE status='published'
        "),

        'draft_results' => $this->scalar("
            SELECT COUNT(*)
            FROM results
            WHERE status!='published'
        ")

    ];
}
public function getTransportOverview(): array
{
    return [

        'buses' => $this->scalar("
            SELECT COUNT(*)
            FROM transport_buses
        "),

        'routes' => $this->scalar("
            SELECT COUNT(*)
            FROM transport_routes
        "),

        'drivers' => $this->scalar("
            SELECT COUNT(*)
            FROM transport_staff
            WHERE staff_type='driver'
        "),

        'assigned_students' => $this->scalar("
            SELECT COUNT(*)
            FROM transport_student_assignments
        ")

    ];
}
public function getRecentAdmissions(int $limit=5): array
{
    $items=[];
    if (!$this->tablesExist(['students', 'users', 'classes', 'sections'])) {
        return $items;
    }

    $q=mysqli_query(
        $this->conn,
        "SELECT
        s.student_id,
        u.full_name,
        c.class_name,
        sec.section_name,
        s.created_at

        FROM students s

        LEFT JOIN users u
        ON u.id=s.user_id

        LEFT JOIN classes c
        ON c.class_id=s.class_id

        LEFT JOIN sections sec
        ON sec.section_id=s.section_id

        ORDER BY s.created_at DESC

        LIMIT {$limit}"
    );

    while($q && $row=mysqli_fetch_assoc($q)){
        $items[]=$row;
    }

    return $items;
}public function getRecentPayments(int $limit=5): array
{
    $items=[];
    if (!$this->tablesExist(['fee_payments', 'student_fees', 'students', 'users'])) {
        return $items;
    }

    $q=mysqli_query(
        $this->conn,
        "SELECT
        fp.receipt_no,
        fp.amount_paid,
        fp.payment_date,
        u.full_name

        FROM fee_payments fp

        INNER JOIN student_fees sf
        ON sf.student_fee_id=fp.student_fee_id

        INNER JOIN students s
        ON s.id=sf.student_id

        LEFT JOIN users u
        ON u.id=s.user_id

        ORDER BY fp.payment_date DESC

        LIMIT {$limit}"
    );

    while($q && $row=mysqli_fetch_assoc($q)){
        $items[]=$row;
    }

    return $items;
}public function getRecentSalaryPayments(int $limit=5): array
{
    $items=[];
    if (!$this->tablesExist(['salary_payments', 'teachers', 'users'])) {
        return $items;
    }

    $q=mysqli_query(
        $this->conn,
        "SELECT
        sp.receipt_no,
        sp.amount_paid,
        sp.payment_date,
        sp.pay_period,
        u.full_name

        FROM salary_payments sp

        INNER JOIN teachers t
        ON t.id=sp.teacher_id

        LEFT JOIN users u
        ON u.id=t.user_id

        ORDER BY sp.payment_date DESC, sp.payment_id DESC

        LIMIT {$limit}"
    );

    while($q && $row=mysqli_fetch_assoc($q)){
        $items[]=$row;
    }

    return $items;
}public function getRecentResults(int $limit=5): array
{
    $items=[];
    if (!$this->tableExists('results')) {
        return $items;
    }

    $q=mysqli_query(
        $this->conn,
        "SELECT
        r.result_id,
        e.exam_name,
        c.class_name,
        s.section_name,
        r.status,
        r.published_at

        FROM results r

        LEFT JOIN exams e
        ON e.exam_id=r.exam_id

        LEFT JOIN classes c
        ON c.class_id=r.class_id

        LEFT JOIN sections s
        ON s.section_id=r.section_id

        ORDER BY r.created_at DESC

        LIMIT {$limit}"
    );

    while($q && $row=mysqli_fetch_assoc($q)){
        $items[]=$row;
    }

    return $items;
}public function getDashboardAlerts(): array
{
    return [

        'pending_attendance'=>$this->getInsights()['pending_sessions'],

        'critical_students'=>$this->getInsights()['critical_students'],

        'pending_fee_students'=>$this->scalar("
            SELECT COUNT(*)
            FROM student_fees
            WHERE payment_status!='paid'
        "),

        'draft_results'=>$this->scalar("
            SELECT COUNT(*)
            FROM results
            WHERE status!='published'
        ")

    ];
}
public function getModuleHealth(): array
{
    return [

        'attendance'=>$this->scalar("SELECT COUNT(*) FROM attendance"),

        'fees'=>$this->scalar("SELECT COUNT(*) FROM fee_payments"),

        'transport'=>$this->scalar("SELECT COUNT(*) FROM transport_student_assignments"),

        'results'=>$this->scalar("SELECT COUNT(*) FROM results"),

        'notifications'=>$this->scalar("SELECT COUNT(*) FROM notifications")

    ];
}

public function getFinanceCollectionTrend(int $months = 6): array
{
    $labels = [];
    $values = [];

    for ($i = $months - 1; $i >= 0; $i--) {
        $date = new DateTime("first day of -$i months");
        $month = $date->format('Y-m');
        $labels[] = $date->format('M Y');

        $values[] = $this->scalarFloat(
            "SELECT COALESCE(SUM(amount_paid),0)
             FROM fee_payments
             WHERE DATE_FORMAT(payment_date,'%Y-%m') = '$month'"
        );
    }

    return ['labels' => $labels, 'values' => $values];
}

public function getExamPerformanceTrend(int $limit = 6): array
{
    $labels = [];
    $values = [];

    if (!$this->tablesExist(['result_marks', 'results', 'exams'])) {
        return ['labels' => $labels, 'values' => $values];
    }

    $query = mysqli_query(
        $this->conn,
        "SELECT e.exam_name,
                e.total_marks,
                AVG(rm.marks_obtained) AS average_marks
         FROM result_marks rm
         INNER JOIN results r ON r.result_id = rm.result_id
         INNER JOIN exams e ON e.exam_id = r.exam_id
         GROUP BY e.exam_id, e.exam_name, e.total_marks
         ORDER BY MAX(r.created_at) DESC
         LIMIT $limit"
    );

    $rows = [];
    while ($query && $row = mysqli_fetch_assoc($query)) {
        $rows[] = $row;
    }

    $rows = array_reverse($rows);
    foreach ($rows as $row) {
        $labels[] = $row['exam_name'];
        $totalMarks = (float) ($row['total_marks'] ?: 100);
        $averageMarks = (float) ($row['average_marks'] ?? 0);
        $values[] = $totalMarks > 0 ? round(($averageMarks / $totalMarks) * 100, 2) : 0;
    }

    return ['labels' => $labels, 'values' => $values];
}

public function getUpcomingEvents(int $limit = 5): array
{
    $items = [];

    if (!$this->tableHasColumn('exams', 'exam_date')) {
        return $items;
    }

    $query = mysqli_query(
        $this->conn,
        "SELECT e.exam_name,
                e.exam_type,
                e.exam_date,
                e.start_time,
                c.class_name,
                s.section_name
         FROM exams e
         LEFT JOIN classes c ON c.class_id = e.class_id
         LEFT JOIN sections s ON s.section_id = e.section_id
         WHERE e.exam_date >= CURDATE()
           AND e.status IN ('upcoming', 'ongoing')
         ORDER BY e.exam_date ASC, e.start_time ASC
         LIMIT $limit"
    );

    while ($query && $row = mysqli_fetch_assoc($query)) {
        $items[] = [
            'title' => $row['exam_name'],
            'type' => ucfirst(str_replace('_', ' ', $row['exam_type'] ?? 'Exam')),
            'date' => $row['exam_date'],
            'time' => $row['start_time'],
            'scope' => trim(($row['class_name'] ?? '') . ' ' . ($row['section_name'] ?? '')),
        ];
    }

    return $items;
}

    private function attendanceRate(string $present_sql): float
    {
        $present = $this->scalar($present_sql);
        $total   = $this->scalar("SELECT COUNT(*) FROM attendance_records");

        return $total > 0 ? round(($present / $total) * 100, 2) : 0;
    }

    private function upcomingExamCount(): int
    {
        return (new ExamService($this->conn))->getExamStatistics()['upcoming_exams'];
    }

    private function tableHasColumn(string $table, string $column): bool
    {
        $key = $table . '.' . $column;
        if (isset($this->columnCache[$key])) {
            return $this->columnCache[$key];
        }
        if (!$this->tableExists($table)) {
            return $this->columnCache[$key] = false;
        }

        $safeTable = mysqli_real_escape_string($this->conn, $table);
        $safeColumn = mysqli_real_escape_string($this->conn, $column);
        $result = mysqli_query($this->conn, "SHOW COLUMNS FROM `$safeTable` LIKE '$safeColumn'");

        return $this->columnCache[$key] = ($result && mysqli_num_rows($result) > 0);
    }

    private function tableExists(string $table): bool
    {
        if (isset($this->tableCache[$table])) {
            return $this->tableCache[$table];
        }

        $safe = mysqli_real_escape_string($this->conn, $table);
        $result = mysqli_query($this->conn, "SHOW TABLES LIKE '$safe'");

        return $this->tableCache[$table] = ($result && mysqli_num_rows($result) > 0);
    }

    private function tablesExist(array $tables): bool
    {
        foreach ($tables as $table) {
            if (!$this->tableExists($table)) {
                return false;
            }
        }

        return true;
    }

    private function scalar(string $sql): int
    {
        try {
            $result = mysqli_query($this->conn, $sql);
        } catch (mysqli_sql_exception $exception) {
            return 0;
        }

        if (!$result) {
            return 0;
        }

        $row = mysqli_fetch_row($result);

        return (int) ($row[0] ?? 0);
    }

    private function scalarFloat(string $sql): float
    {
        try {
            $result = mysqli_query($this->conn, $sql);
        } catch (mysqli_sql_exception $exception) {
            return 0;
        }

        if (!$result) {
            return 0;
        }

        $row = mysqli_fetch_row($result);

        return (float) ($row[0] ?? 0);
    }
}
