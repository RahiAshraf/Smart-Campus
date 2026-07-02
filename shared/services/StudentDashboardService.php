<?php

require_once __DIR__ . '/../../config/notifications.php';
require_once __DIR__ . '/ExamService.php';

class StudentDashboardService
{
    private mysqli $conn;
    private ?array $student = null;
    private array $tableCache = [];
    private array $columnCache = [];

    public function __construct(mysqli $conn, int $user_id)
    {
        $this->conn = $conn;
        $this->student = mysqli_fetch_assoc(mysqli_query(
            $conn,
            "SELECT st.*, u.full_name, u.email, u.profile_photo,
                    c.class_name, s.section_name
             FROM students st
             INNER JOIN users u ON u.id = st.user_id
             INNER JOIN classes c ON c.class_id = st.class_id
             INNER JOIN sections s ON s.section_id = st.section_id
             WHERE st.user_id = '$user_id'
             LIMIT 1"
        ));
    }

    public function getStudent(): ?array
    {
        return $this->student;
    }

    public function getStudentDbId(): int
    {
        return (int) ($this->student['id'] ?? 0);
    }

    public function getStats(): array
    {
        $sid = $this->getStudentDbId();
        $attendance_pct = 0;

        if ($sid > 0 && $this->tableExists('attendance_records')) {
            $present = $this->scalar(
                "SELECT COUNT(*) FROM attendance_records
                 WHERE student_id = '$sid' AND status != 'absent'"
            );
            $total = $this->scalar(
                "SELECT COUNT(*) FROM attendance_records
                 WHERE student_id = '$sid'"
            );
            $attendance_pct = $total > 0 ? round(($present / $total) * 100, 2) : 0;
        }

        $uid = (int) ($_SESSION['user']['id'] ?? 0);
        $role = $_SESSION['user']['role'] ?? 'student';
        $context = notification_user_context($this->conn, $uid, $role);
        $unread = notification_unread_count($this->conn, $context);

        $subjects = $this->tablesExist(['timetable_entries', 'timetables'])
            ? $this->scalar(
                "SELECT COUNT(DISTINCT te.subject_id)
                 FROM timetable_entries te
                 INNER JOIN timetables tt ON tt.timetable_id = te.timetable_id
                 WHERE tt.class_id = '" . (int) ($this->student['class_id'] ?? 0) . "'
                   AND tt.section_id = '" . (int) ($this->student['section_id'] ?? 0) . "'"
            )
            : 0;

        return [
            'attendance_pct'      => $attendance_pct,
            'subjects_count'      => $subjects,
            'upcoming_exams'      => $this->upcomingExamCount(),
            'unread_notifications'=> $unread,
            'latest_percentage'   => $this->getLatestPercentage(),
            'results_trend'       => $this->getResultsTrend(),
            'latest_result_pct'   => $this->getLatestResultPercentage(),
        ];
    }

    public function getLatestGpa(): ?float
    {
        return $this->getLatestPercentage();
    }

    public function getLatestPercentage(): ?float
    {
        require_once __DIR__ . '/ResultsService.php';
        $service = new ResultsService($this->conn);
        $summary = $service->getLatestPerformance($this->getStudentDbId());

        return $summary['percentage'] ?? null;
    }

    public function getResultsTrend(): array
    {
        require_once __DIR__ . '/ResultsService.php';
        $service = new ResultsService($this->conn);

        return $service->getPerformanceTrend($this->getStudentDbId());
    }

    public function getAttendanceTrend(int $days = 30): array
    {
        $sid = $this->getStudentDbId();
        $labels = [];
        $values = [];

        if ($sid <= 0 || !$this->tablesExist(['attendance_records', 'attendance'])) {
            return ['labels' => $labels, 'values' => $values];
        }

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $labels[] = date('M j', strtotime($date));

            $row = mysqli_fetch_assoc(mysqli_query(
                $this->conn,
                "SELECT ar.status FROM attendance_records ar
                 INNER JOIN attendance a ON a.attendance_id = ar.attendance_id
                 WHERE ar.student_id = '$sid' AND a.attendance_date = '$date'
                 LIMIT 1"
            ));

            if (!$row) {
                $values[] = null;
            } else {
                $values[] = $row['status'] === 'absent' ? 0 : 100;
            }
        }

        return ['labels' => $labels, 'values' => $values];
    }

    public function getTodayTimetable(): array
    {
        $class_id   = (int) ($this->student['class_id'] ?? 0);
        $section_id = (int) ($this->student['section_id'] ?? 0);
        $day        = strtolower(date('l'));
        $items      = [];

        if ($class_id <= 0 || !$this->tablesExist(['timetable_entries', 'timetables', 'periods', 'subjects'])) {
            return $items;
        }

        $query = mysqli_query(
            $this->conn,
            "SELECT te.*, p.period_name, p.start_time, p.end_time,
                    sub.subject_name, u.full_name AS teacher_name
             FROM timetable_entries te
             INNER JOIN timetables tt ON tt.timetable_id = te.timetable_id
             INNER JOIN periods p ON p.period_id = te.period_id
             INNER JOIN subjects sub ON sub.subject_id = te.subject_id
             LEFT JOIN teacher_assignments ta ON ta.assignment_id = te.teacher_assignment_id
             LEFT JOIN teachers t ON t.id = ta.teacher_id
             LEFT JOIN users u ON u.id = t.user_id
             WHERE tt.class_id = '$class_id'
               AND tt.section_id = '$section_id'
               AND te.day_of_week = '$day'
             ORDER BY p.start_time ASC"
        );

        while ($query && $row = mysqli_fetch_assoc($query)) {
            $items[] = $row;
        }

        return $items;
    }

    public function getAttendanceStatus(): string
    {
        $pct = $this->getStats()['attendance_pct'];

        if ($pct >= 85) {
            return 'Excellent';
        }

        if ($pct >= 75) {
            return 'Good';
        }

        if ($pct >= 60) {
            return 'Needs Improvement';
        }

        return 'Critical';
    }

    public function getRecentAttendance(int $limit = 10): array
    {
        $sid = $this->getStudentDbId();
        $items = [];

        if ($sid <= 0 || !$this->tablesExist(['attendance_records', 'attendance'])) {
            return $items;
        }

        $query = mysqli_query(
            $this->conn,
            "SELECT a.attendance_date, ar.status, sub.subject_name, p.period_name
             FROM attendance_records ar
             INNER JOIN attendance a ON a.attendance_id = ar.attendance_id
             LEFT JOIN subjects sub ON sub.subject_id = a.subject_id
             LEFT JOIN periods p ON p.period_id = a.period_id
             WHERE ar.student_id = '$sid'
             ORDER BY a.attendance_date DESC
             LIMIT $limit"
        );

        while ($query && $row = mysqli_fetch_assoc($query)) {
            $items[] = $row;
        }

        return $items;
    }

    public function getFeeOverview(): array
    {
        $sid = $this->getStudentDbId();

        if ($sid <= 0 || !$this->tableExists('student_fees')) {
            return ['assigned' => 0, 'paid' => 0, 'balance' => 0, 'status' => 'unassigned'];
        }

        $assigned = $this->scalarFloat("SELECT COALESCE(SUM(amount),0) FROM student_fees WHERE student_id = '$sid'");
        $paid = $this->scalarFloat("SELECT COALESCE(SUM(paid_amount),0) FROM student_fees WHERE student_id = '$sid'");
        $balance = $this->scalarFloat("SELECT COALESCE(SUM(balance_amount),0) FROM student_fees WHERE student_id = '$sid'");

        $status = 'paid';
        if ($assigned <= 0) {
            $status = 'unassigned';
        } elseif ($balance >= $assigned) {
            $status = 'unpaid';
        } elseif ($balance > 0) {
            $status = 'partial';
        }

        return ['assigned' => $assigned, 'paid' => $paid, 'balance' => $balance, 'status' => $status];
    }

    public function getFeeDetails(int $limit = 6): array
    {
        $sid = $this->getStudentDbId();
        $items = [];

        if ($sid <= 0 || !$this->tablesExist(['student_fees', 'fee_structures'])) {
            return $items;
        }

        $query = mysqli_query(
            $this->conn,
            "SELECT sf.*, fs.fee_name, fs.due_date, fs.academic_year
             FROM student_fees sf
             INNER JOIN fee_structures fs ON fs.fee_structure_id = sf.fee_structure_id
             WHERE sf.student_id = '$sid'
             ORDER BY fs.due_date ASC, sf.student_fee_id DESC
             LIMIT $limit"
        );

        while ($query && $row = mysqli_fetch_assoc($query)) {
            $items[] = $row;
        }

        return $items;
    }

    public function getRecentFeePayments(int $limit = 5): array
    {
        $sid = $this->getStudentDbId();
        $items = [];

        if ($sid <= 0 || !$this->tablesExist(['fee_payments', 'student_fees', 'fee_structures'])) {
            return $items;
        }

        $query = mysqli_query(
            $this->conn,
            "SELECT fp.receipt_no, fp.amount_paid, fp.payment_date, fp.payment_method, fs.fee_name
             FROM fee_payments fp
             INNER JOIN student_fees sf ON sf.student_fee_id = fp.student_fee_id
             INNER JOIN fee_structures fs ON fs.fee_structure_id = sf.fee_structure_id
             WHERE sf.student_id = '$sid'
             ORDER BY fp.payment_date DESC, fp.payment_id DESC
             LIMIT $limit"
        );

        while ($query && $row = mysqli_fetch_assoc($query)) {
            $items[] = $row;
        }

        return $items;
    }

    public function getUpcomingExams(int $limit = 5): array
    {
        $class_id = (int) ($this->student['class_id'] ?? 0);
        $section_id = (int) ($this->student['section_id'] ?? 0);
        $items = [];

        if ($class_id <= 0) {
            return $items;
        }

        require_once __DIR__ . '/ExamService.php';
        $service = new ExamService($this->conn);
        return $service->getUpcomingExamsForStudent($class_id, $section_id, $limit);
    }

    public function getRecentResults(int $limit = 5): array
    {
        $sid = $this->getStudentDbId();
        $items = [];

        if ($sid <= 0 || !$this->tablesExist(['result_marks', 'results', 'exams'])) {
            return $items;
        }

        $query = mysqli_query(
            $this->conn,
            "SELECT rm.marks_obtained, rm.remarks, r.status, r.published_at,
                    e.exam_name, e.total_marks, e.exam_date
             FROM result_marks rm
             INNER JOIN results r ON r.result_id = rm.result_id
             INNER JOIN exams e ON e.exam_id = r.exam_id
             WHERE rm.student_id = '$sid' AND r.status = 'published'
             ORDER BY COALESCE(r.published_at, r.created_at) DESC
             LIMIT $limit"
        );

        while ($query && $row = mysqli_fetch_assoc($query)) {
            $items[] = $row;
        }

        return $items;
    }

    public function getResultPercentageTrend(): array
    {
        $sid = $this->getStudentDbId();
        $labels = [];
        $values = [];

        if ($sid <= 0 || !$this->tablesExist(['result_marks', 'results', 'exams'])) {
            return ['labels' => $labels, 'values' => $values];
        }

        $query = mysqli_query(
            $this->conn,
            "SELECT e.exam_name, e.total_marks, rm.marks_obtained
             FROM result_marks rm
             INNER JOIN results r ON r.result_id = rm.result_id
             INNER JOIN exams e ON e.exam_id = r.exam_id
             WHERE rm.student_id = '$sid' AND r.status = 'published'
             ORDER BY COALESCE(e.exam_date, DATE(r.created_at)) ASC"
        );

        while ($query && $row = mysqli_fetch_assoc($query)) {
            $total = (float) ($row['total_marks'] ?: 100);
            $labels[] = $row['exam_name'];
            $values[] = $total > 0 ? round(((float) $row['marks_obtained'] / $total) * 100, 2) : 0;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    public function getLatestResultPercentage(): ?float
    {
        $trend = $this->getResultPercentageTrend();

        if (empty($trend['values'])) {
            return null;
        }

        return end($trend['values']);
    }

    public function getRecentNotifications(int $limit = 5): array
    {
        if (!$this->tableExists('notifications')) {
            return [];
        }

        $uid = (int) ($_SESSION['user']['id'] ?? 0);
        $role = $_SESSION['user']['role'] ?? 'student';
        $context = notification_user_context($this->conn, $uid, $role);

        return notification_recent_for_context($this->conn, $context, $limit);
    }

    public function getTransportSummary(): ?array
    {
        $sid = $this->getStudentDbId();

        if ($sid <= 0 || !$this->tablesExist(['transport_student_assignments', 'transport_buses'])) {
            return null;
        }

        return mysqli_fetch_assoc(mysqli_query(
            $this->conn,
            "SELECT b.bus_number, b.bus_name, r.route_name, st.stop_name,
                    d.full_name AS driver_name, d.phone AS driver_phone,
                    l.status, l.updated_at
             FROM transport_student_assignments tsa
             INNER JOIN transport_buses b ON b.bus_id = tsa.bus_id
             LEFT JOIN transport_routes r ON r.bus_id = b.bus_id
             LEFT JOIN transport_stops st ON st.stop_id = tsa.stop_id
             LEFT JOIN transport_staff d ON d.staff_id = b.driver_id
             LEFT JOIN transport_live_location l ON l.bus_id = b.bus_id
             WHERE tsa.student_id = '$sid'
             LIMIT 1"
        )) ?: null;
    }

    public function getHomeworkOverview(): array
    {
        return [
            'configured' => false,
            'pending' => 0,
            'submitted' => 0,
            'message' => 'Homework and assignment tracking is not configured in this installation.',
        ];
    }

    private function scalar(string $sql): int
    {
        $result = mysqli_query($this->conn, $sql);

        if (!$result) {
            return 0;
        }

        $row = mysqli_fetch_row($result);

        return (int) ($row[0] ?? 0);
    }

    private function scalarFloat(string $sql): float
    {
        $result = mysqli_query($this->conn, $sql);

        if (!$result) {
            return 0;
        }

        $row = mysqli_fetch_row($result);

        return (float) ($row[0] ?? 0);
    }

    private function upcomingExamCount(): int
{
    $class_id = (int)($this->student['class_id'] ?? 0);
    $section_id = (int)($this->student['section_id'] ?? 0);

    require_once __DIR__ . '/ExamService.php';
    return (new ExamService($this->conn))->getExamStatistics([
        'class_id' => $class_id,
        'section_id' => $section_id,
    ])['upcoming_exams'];
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

        $table = mysqli_real_escape_string($this->conn, $table);
        $column = mysqli_real_escape_string($this->conn, $column);
        $result = mysqli_query($this->conn, "SHOW COLUMNS FROM `$table` LIKE '$column'");

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
}
