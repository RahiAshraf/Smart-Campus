<?php

require_once __DIR__ . '/TeacherScopeService.php';
require_once __DIR__ . '/ExamService.php';
require_once __DIR__ . '/../../config/notifications.php';

class TeacherDashboardService
{
    private mysqli $conn;
    private TeacherScopeService $scope;
    private array $tableCache = [];
    private array $columnCache = [];

    public function __construct(mysqli $conn, TeacherScopeService $scope)
    {
        $this->conn  = $conn;
        $this->scope = $scope;
    }

    public function getStats(): array
    {
        $pairs = $this->scope->getAssignedClassSectionPairs();
        $today_classes = $this->getTodayScheduleCount();
        $pending = $this->getPendingAttendanceCount($pairs);
        $completed = $this->getCompletedAttendanceCount($pairs);

        $uid = (int) ($_SESSION['user']['id'] ?? 0);
        $role = $_SESSION['user']['role'] ?? 'teacher';
        $context = notification_user_context($this->conn, $uid, $role);
        $unread = notification_unread_count($this->conn, $context);

        return [
            'today_classes'       => $today_classes,
            'pending_attendance'  => $pending,
            'completed_attendance'=> $completed,
            'assigned_students'   => $this->scope->getAssignedStudentCount(),
            'assigned_subjects'   => count($this->scope->getSubjects()),
            'assigned_sections'   => count($pairs),
            'upcoming_exams'      => (new ExamService($this->conn))->getExamStatistics([
                'teacher_id' => $this->scope->getTeacherId(),
            ])['upcoming_exams'],
            'unread_notifications'=> $unread,
        ];
    }

    public function getTodaySchedule(): array
    {
        $tid = $this->scope->getTeacherId();
        $day = strtolower(date('l'));

        if ($tid <= 0 || !$this->tablesExist(['timetable_entries', 'timetables', 'teacher_assignments', 'periods', 'subjects', 'classes', 'sections'])) {
            return [];
        }

        $items = [];
        $query = mysqli_query(
            $this->conn,
            "SELECT te.*, p.period_name, p.start_time, p.end_time,
                    sub.subject_name, c.class_name, s.section_name
             FROM timetable_entries te
             INNER JOIN timetables tt ON tt.timetable_id = te.timetable_id
             INNER JOIN teacher_assignments ta ON ta.assignment_id = te.teacher_assignment_id
             INNER JOIN periods p ON p.period_id = te.period_id
             INNER JOIN subjects sub ON sub.subject_id = te.subject_id
             INNER JOIN classes c ON c.class_id = tt.class_id
             INNER JOIN sections s ON s.section_id = tt.section_id
             WHERE ta.teacher_id = '$tid'
               AND te.day_of_week = '$day'
             ORDER BY p.start_time ASC"
        );

        while ($row = mysqli_fetch_assoc($query)) {
            $items[] = $row;
        }

        return $items;
    }

    public function getCurrentAndNextPeriod(): array
    {
        $schedule = $this->getTodaySchedule();
        $now = date('H:i:s');
        $current = null;
        $next = null;

        foreach ($schedule as $item) {
            if ($now >= $item['start_time'] && $now <= $item['end_time']) {
                $current = $item;
            } elseif ($now < $item['start_time'] && $next === null) {
                $next = $item;
            }
        }

        return ['current' => $current, 'next' => $next];
    }

    public function getAttendanceMarkingTrend(int $days = 14): array
    {
        $labels = [];
        $values = [];
        $pairs = $this->scope->getAssignedClassSectionPairs();

        if (empty($pairs)) {
            return ['labels' => $labels, 'values' => $values];
        }

        $conditions = [];

        foreach ($pairs as $p) {
            $conditions[] = "(a.class_id = {$p['class_id']} AND a.section_id = {$p['section_id']})";
        }

        $scope = '(' . implode(' OR ', $conditions) . ')';

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $labels[] = date('M j', strtotime($date));

            $values[] = $this->scalar(
                "SELECT COUNT(*) FROM attendance a
                 WHERE a.attendance_date = '$date' AND $scope"
            );
        }

        return ['labels' => $labels, 'values' => $values];
    }

    public function getStudentAttendanceTrend(int $days = 14): array
    {
        $labels = [];
        $values = [];
        $pairs = $this->scope->getAssignedClassSectionPairs();

        if (empty($pairs)) {
            return ['labels' => $labels, 'values' => $values];
        }

        $conditions = [];

        foreach ($pairs as $p) {
            $conditions[] = "(a.class_id = {$p['class_id']} AND a.section_id = {$p['section_id']})";
        }

        $scope = '(' . implode(' OR ', $conditions) . ')';

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $labels[] = date('M j', strtotime($date));

            $present = $this->scalar(
                "SELECT COUNT(*) FROM attendance_records ar
                 INNER JOIN attendance a ON a.attendance_id = ar.attendance_id
                 WHERE a.attendance_date = '$date' AND $scope
                   AND ar.status != 'absent'"
            );
            $total = $this->scalar(
                "SELECT COUNT(*) FROM attendance_records ar
                 INNER JOIN attendance a ON a.attendance_id = ar.attendance_id
                 WHERE a.attendance_date = '$date' AND $scope"
            );

            $values[] = $total > 0 ? round(($present / $total) * 100, 2) : 0;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    public function getInsights(): array
    {
        $pairs = $this->scope->getAssignedClassSectionPairs();
        $attention = [];
        $below_threshold = 0;

        foreach ($pairs as $p) {
            $cid = (int) $p['class_id'];
            $sid = (int) $p['section_id'];

            $has_today = $this->scalar(
                "SELECT COUNT(*) FROM attendance
                 WHERE class_id = '$cid' AND section_id = '$sid'
                   AND attendance_date = CURDATE()"
            );

            if ($has_today === 0) {
                $attention[] = $p['class_name'] . ' - ' . $p['section_name'];
            }
        }

        $total_pairs = count($pairs);
        $completed = $total_pairs - count($attention);
        $completion_pct = $total_pairs > 0
            ? round(($completed / $total_pairs) * 100, 2)
            : 0;

        return [
            'classes_requiring_attention' => $attention,
            'students_below_threshold'    => $below_threshold,
            'attendance_completion_pct'   => $completion_pct,
        ];
    }

    public function getAssignmentOverview(): array
    {
        $assignments = $this->scope->getAssignments();
        $items = [];

        foreach ($assignments as $assignment) {
            $items[] = [
                'subject_name' => $assignment['subject_name'],
                'class_name' => $assignment['class_name'],
                'section_name' => $assignment['section_name'],
                'assignment_role' => $assignment['assignment_role'],
            ];
        }

        return $items;
    }

    public function getUpcomingExams(int $limit = 5): array
    {
        require_once __DIR__ . '/ExamService.php';
        return (new ExamService($this->conn))->getUpcomingExamsForTeacher($this->scope->getTeacherId(), $limit);
    }

    public function getStudentPerformance(): array
    {
        $pairs = $this->scope->getAssignedClassSectionPairs();

        if (empty($pairs) || !$this->tablesExist(['results', 'result_marks', 'exams'])) {
            return ['labels' => [], 'values' => []];
        }

        $conditions = [];
        foreach ($pairs as $p) {
            $conditions[] = "(r.class_id = {$p['class_id']} AND r.section_id = {$p['section_id']})";
        }

        $labels = [];
        $values = [];
        $query = mysqli_query(
            $this->conn,
            "SELECT c.class_name, s.section_name, e.total_marks,
                    AVG(rm.marks_obtained) AS average_marks
             FROM results r
             INNER JOIN result_marks rm ON rm.result_id = r.result_id
             INNER JOIN exams e ON e.exam_id = r.exam_id
             INNER JOIN classes c ON c.class_id = r.class_id
             INNER JOIN sections s ON s.section_id = r.section_id
             WHERE (" . implode(' OR ', $conditions) . ") AND r.status = 'published'
             GROUP BY r.class_id, r.section_id, c.class_name, s.section_name, e.total_marks
             ORDER BY c.class_name, s.section_name"
        );

        while ($query && $row = mysqli_fetch_assoc($query)) {
            $total = (float) ($row['total_marks'] ?: 100);
            $avg = (float) ($row['average_marks'] ?? 0);
            $labels[] = $row['class_name'] . ' ' . $row['section_name'];
            $values[] = $total > 0 ? round(($avg / $total) * 100, 2) : 0;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    public function getRecentNotifications(int $limit = 5): array
    {
        if (!$this->tableExists('notifications')) {
            return [];
        }

        $uid = (int) ($_SESSION['user']['id'] ?? 0);
        $role = $_SESSION['user']['role'] ?? 'teacher';
        $context = notification_user_context($this->conn, $uid, $role);

        return notification_recent_for_context($this->conn, $context, $limit);
    }

    public function getSalarySummary(): array
    {
        $tid = $this->scope->getTeacherId();

        if ($tid <= 0 || !$this->tablesExist(['teacher_salaries', 'salary_payments'])) {
            return ['monthly_salary' => 0, 'status' => 'unassigned', 'paid_this_month' => 0, 'last_payment' => null];
        }

        $salary = mysqli_fetch_assoc(mysqli_query(
            $this->conn,
            "SELECT *
             FROM teacher_salaries
             WHERE teacher_id = '$tid'
             ORDER BY status = 'active' DESC, effective_from DESC, salary_id DESC
             LIMIT 1"
        ));

        if (!$salary) {
            return ['monthly_salary' => 0, 'status' => 'unassigned', 'paid_this_month' => 0, 'last_payment' => null];
        }

        $salaryId = (int) $salary['salary_id'];
        $paid = $this->scalarFloat(
            "SELECT COALESCE(SUM(amount_paid),0)
             FROM salary_payments
             WHERE salary_id = '$salaryId'
               AND pay_period = DATE_FORMAT(CURDATE(), '%Y-%m')"
        );
        $last = mysqli_fetch_assoc(mysqli_query(
            $this->conn,
            "SELECT *
             FROM salary_payments
             WHERE salary_id = '$salaryId'
             ORDER BY payment_date DESC, payment_id DESC
             LIMIT 1"
        )) ?: null;

        return [
            'monthly_salary' => (float) $salary['monthly_salary'],
            'status' => $salary['status'],
            'paid_this_month' => $paid,
            'last_payment' => $last,
        ];
    }

    private function getTodayScheduleCount(): int
    {
        return count($this->getTodaySchedule());
    }

    private function getPendingAttendanceCount(array $pairs): int
    {
        $pending = 0;

        foreach ($pairs as $p) {
            $has = $this->scalar(
                "SELECT COUNT(*) FROM attendance
                 WHERE class_id = '{$p['class_id']}'
                   AND section_id = '{$p['section_id']}'
                   AND attendance_date = CURDATE()"
            );

            if ($has === 0) {
                $pending++;
            }
        }

        return $pending;
    }

    private function getCompletedAttendanceCount(array $pairs): int
    {
        return count($pairs) - $this->getPendingAttendanceCount($pairs);
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
}
