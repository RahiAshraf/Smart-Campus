<?php

require_once __DIR__ . '/../../config/notifications.php';
require_once __DIR__ . '/ExamService.php';

class ParentDashboardService
{
    private mysqli $conn;
    private ?array $parent = null;
    private array $parentLinks = [];
    private array $children = [];
    private array $tableCache = [];
    private array $columnCache = [];

    public function __construct(mysqli $conn, int $user_id)
    {
        $this->conn = $conn;
        $query = mysqli_query(
            $conn,
            "SELECT p.*, u.full_name, u.email, u.phone, u.profile_photo
             FROM parents p
             INNER JOIN users u ON u.id = p.user_id
             WHERE p.user_id = '$user_id'
             ORDER BY p.id ASC"
        );

        while ($query && $row = mysqli_fetch_assoc($query)) {
            $this->parentLinks[] = $row;
        }

        $this->parent = $this->parentLinks[0] ?? null;

        $this->loadChildren();
    }

    public function getParent(): ?array
    {
        return $this->parent;
    }

    public function getChildren(): array
    {
        return $this->children;
    }

    public function setActiveChild(int $student_db_id): void
    {
        if ($student_db_id <= 0) {
            return;
        }

        $this->children = array_values(array_filter(
            $this->children,
            fn($child) => (int) ($child['id'] ?? 0) === $student_db_id
        ));
    }

    public function getParentLinks(): array
    {
        return $this->parentLinks;
    }

    public function getStats(): array
    {
        $count = count($this->children);
        $avg_attendance = 0;
        $avg_performance = $this->averagePerformance();

        if ($count > 0 && $this->tableExists('attendance_records')) {
            $total_att = 0;

            foreach ($this->children as $child) {
                $sid = (int) $child['id'];
                $present = $this->scalar(
                    "SELECT COUNT(*) FROM attendance_records
                     WHERE student_id = '$sid' AND status != 'absent'"
                );
                $total = $this->scalar(
                    "SELECT COUNT(*) FROM attendance_records WHERE student_id = '$sid'"
                );
                $total_att += $total > 0 ? ($present / $total) * 100 : 0;
            }

            $avg_attendance = round($total_att / $count, 2);
        }

        $uid = (int) ($_SESSION['user']['id'] ?? 0);
        $role = $_SESSION['user']['role'] ?? 'parent';
        $context = notification_user_context($this->conn, $uid, $role);
        $unread = notification_unread_count($this->conn, $context);

        $examStats = (new ExamService($this->conn))->getExamStatistics(['children' => $this->children]);

        return [
            'children_count'       => $count,
            'average_attendance'   => $avg_attendance,
            'average_performance'  => $avg_performance,
            'unread_notifications' => $unread,
            'fee_balance'          => $this->getTotalFeeBalance(),
            'upcoming_exams'       => $examStats['upcoming_exams'],
        ];
    }

    public function getAttendanceTrend(int $days = 30): array
    {
        $labels = [];
        $values = [];

        if (empty($this->children)) {
            return ['labels' => $labels, 'values' => $values];
        }

        $student_ids = array_map(fn($c) => (int) $c['id'], $this->children);
        $id_list = implode(',', $student_ids);

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $labels[] = date('M j', strtotime($date));

            $present = $this->scalar(
                "SELECT COUNT(*) FROM attendance_records ar
                 INNER JOIN attendance a ON a.attendance_id = ar.attendance_id
                 WHERE ar.student_id IN ($id_list)
                   AND a.attendance_date = '$date'
                   AND ar.status != 'absent'"
            );
            $total = $this->scalar(
                "SELECT COUNT(*) FROM attendance_records ar
                 INNER JOIN attendance a ON a.attendance_id = ar.attendance_id
                 WHERE ar.student_id IN ($id_list)
                   AND a.attendance_date = '$date'"
            );

            $values[] = $total > 0 ? round(($present / $total) * 100, 2) : null;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    public function getInsights(): array
    {
        $alerts = [];
        $performance_alerts = [];

        if (!$this->tableExists('attendance_records')) {
            return [
                'attendance_alerts'    => $alerts,
                'performance_alerts'   => $performance_alerts,
                'upcoming_exams'       => $this->getUpcomingExams(),
            ];
        }

        foreach ($this->children as $child) {
            $sid = (int) $child['id'];
            $present = $this->scalar(
                "SELECT COUNT(*) FROM attendance_records
                 WHERE student_id = '$sid' AND status != 'absent'"
            );
            $total = $this->scalar(
                "SELECT COUNT(*) FROM attendance_records WHERE student_id = '$sid'"
            );
            $rate = $total > 0 ? round(($present / $total) * 100, 2) : 100;

            if ($rate < 75) {
                $alerts[] = [
                    'name' => $child['full_name'],
                    'rate' => $rate,
                ];
            }
        }

        return [
            'attendance_alerts'    => $alerts,
            'performance_alerts'   => $performance_alerts,
            'upcoming_exams'       => $this->getUpcomingExams(),
        ];
    }

    public function getChildAttendance(int $student_db_id): array
    {
        $items = [];
        if ($student_db_id <= 0 || !$this->tablesExist(['attendance_records', 'attendance'])) {
            return $items;
        }

        $query = mysqli_query(
            $this->conn,
            "SELECT a.attendance_date, ar.status, sub.subject_name
             FROM attendance_records ar
             INNER JOIN attendance a ON a.attendance_id = ar.attendance_id
             LEFT JOIN subjects sub ON sub.subject_id = a.subject_id
             WHERE ar.student_id = '$student_db_id'
             ORDER BY a.attendance_date DESC
             LIMIT 30"
        );

        while ($query && $row = mysqli_fetch_assoc($query)) {
            $items[] = $row;
        }

        return $items;
    }

    public function getChildrenOverview(): array
    {
        $items = [];

        foreach ($this->children as $child) {
            $sid = (int) $child['id'];
            $present = $this->tableExists('attendance_records') ? $this->scalar("SELECT COUNT(*) FROM attendance_records WHERE student_id = '$sid' AND status != 'absent'") : 0;
            $total = $this->tableExists('attendance_records') ? $this->scalar("SELECT COUNT(*) FROM attendance_records WHERE student_id = '$sid'") : 0;
            $attendance = $total > 0 ? round(($present / $total) * 100, 2) : 0;
            $feeBalance = $this->tableExists('student_fees') ? $this->scalarFloat("SELECT COALESCE(SUM(balance_amount),0) FROM student_fees WHERE student_id = '$sid'") : 0;
            $latest = $this->tablesExist(['result_marks', 'results', 'exams']) ? mysqli_fetch_assoc(mysqli_query(
                $this->conn,
                "SELECT rm.marks_obtained, e.total_marks, e.exam_name
                 FROM result_marks rm
                 INNER JOIN results r ON r.result_id = rm.result_id
                 INNER JOIN exams e ON e.exam_id = r.exam_id
                 WHERE rm.student_id = '$sid' AND r.status = 'published'
                 ORDER BY COALESCE(r.published_at, r.created_at) DESC
                 LIMIT 1"
            )) : null;

            $performance = null;
            if ($latest && (float) $latest['total_marks'] > 0) {
                $performance = round(((float) $latest['marks_obtained'] / (float) $latest['total_marks']) * 100, 2);
            }

            $items[] = [
                'student' => $child,
                'attendance' => $attendance,
                'fee_balance' => $feeBalance,
                'latest_exam' => $latest['exam_name'] ?? null,
                'performance' => $performance,
            ];
        }

        return $items;
    }

    public function getTotalFeeBalance(): float
    {
        $ids = $this->childIdList();

        if ($ids === '' || !$this->tableExists('student_fees')) {
            return 0;
        }

        return $this->scalarFloat("SELECT COALESCE(SUM(balance_amount),0) FROM student_fees WHERE student_id IN ($ids)");
    }

    public function getFeeSummary(): array
    {
        $ids = $this->childIdList();

        if ($ids === '' || !$this->tableExists('student_fees')) {
            return ['assigned' => 0, 'paid' => 0, 'balance' => 0];
        }

        return [
            'assigned' => $this->scalarFloat("SELECT COALESCE(SUM(amount),0) FROM student_fees WHERE student_id IN ($ids)"),
            'paid' => $this->scalarFloat("SELECT COALESCE(SUM(paid_amount),0) FROM student_fees WHERE student_id IN ($ids)"),
            'balance' => $this->scalarFloat("SELECT COALESCE(SUM(balance_amount),0) FROM student_fees WHERE student_id IN ($ids)"),
        ];
    }

    public function getFeeDetails(int $limit = 8): array
    {
        $ids = $this->childIdList();
        $items = [];

        if ($ids === '' || !$this->tablesExist(['student_fees', 'fee_structures', 'students', 'users'])) {
            return $items;
        }

        $query = mysqli_query(
            $this->conn,
            "SELECT sf.*, fs.fee_name, fs.due_date, fs.academic_year, u.full_name
             FROM student_fees sf
             INNER JOIN fee_structures fs ON fs.fee_structure_id = sf.fee_structure_id
             INNER JOIN students st ON st.id = sf.student_id
             INNER JOIN users u ON u.id = st.user_id
             WHERE sf.student_id IN ($ids)
             ORDER BY fs.due_date ASC, sf.student_fee_id DESC
             LIMIT $limit"
        );

        while ($query && $row = mysqli_fetch_assoc($query)) {
            $items[] = $row;
        }

        return $items;
    }

    public function getRecentFeePayments(int $limit = 6): array
    {
        $ids = $this->childIdList();
        $items = [];

        if ($ids === '' || !$this->tablesExist(['fee_payments', 'student_fees', 'fee_structures', 'students', 'users'])) {
            return $items;
        }

        $query = mysqli_query(
            $this->conn,
            "SELECT fp.receipt_no, fp.amount_paid, fp.payment_date, fp.payment_method, fs.fee_name, u.full_name
             FROM fee_payments fp
             INNER JOIN student_fees sf ON sf.student_fee_id = fp.student_fee_id
             INNER JOIN fee_structures fs ON fs.fee_structure_id = sf.fee_structure_id
             INNER JOIN students st ON st.id = sf.student_id
             INNER JOIN users u ON u.id = st.user_id
             WHERE sf.student_id IN ($ids)
             ORDER BY fp.payment_date DESC, fp.payment_id DESC
             LIMIT $limit"
        );

        while ($query && $row = mysqli_fetch_assoc($query)) {
            $items[] = $row;
        }

        return $items;
    }

    public function getUpcomingExams(int $limit = 6): array
    {
        require_once __DIR__ . '/ExamService.php';
        $service = new ExamService($this->conn);
        return $service->getUpcomingExamsForChildren($this->children, $limit);
    }

    public function getRecentResults(int $limit = 6): array
    {
        $ids = $this->childIdList();
        $items = [];

        if ($ids === '' || !$this->tablesExist(['result_marks', 'results', 'exams'])) {
            return $items;
        }

        $query = mysqli_query(
            $this->conn,
            "SELECT u.full_name, rm.marks_obtained, r.status, e.exam_name, e.total_marks, e.exam_date
             FROM result_marks rm
             INNER JOIN students st ON st.id = rm.student_id
             INNER JOIN users u ON u.id = st.user_id
             INNER JOIN results r ON r.result_id = rm.result_id
             INNER JOIN exams e ON e.exam_id = r.exam_id
             WHERE rm.student_id IN ($ids) AND r.status = 'published'
             ORDER BY COALESCE(r.published_at, r.created_at) DESC
             LIMIT $limit"
        );

        while ($query && $row = mysqli_fetch_assoc($query)) {
            $items[] = $row;
        }

        return $items;
    }

    public function getPerformanceTrend(): array
    {
        $ids = $this->childIdList();
        $labels = [];
        $values = [];

        if ($ids === '' || !$this->tablesExist(['result_marks', 'results', 'exams'])) {
            return ['labels' => $labels, 'values' => $values];
        }

        $query = mysqli_query(
            $this->conn,
            "SELECT e.exam_name, e.total_marks, AVG(rm.marks_obtained) AS average_marks,
                    MAX(COALESCE(e.exam_date, DATE(r.created_at))) AS sort_date
             FROM result_marks rm
             INNER JOIN results r ON r.result_id = rm.result_id
             INNER JOIN exams e ON e.exam_id = r.exam_id
             WHERE rm.student_id IN ($ids) AND r.status = 'published'
             GROUP BY e.exam_id, e.exam_name, e.total_marks
             ORDER BY sort_date ASC"
        );

        while ($query && $row = mysqli_fetch_assoc($query)) {
            $total = (float) ($row['total_marks'] ?: 100);
            $labels[] = $row['exam_name'];
            $values[] = $total > 0 ? round(((float) $row['average_marks'] / $total) * 100, 2) : 0;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    public function getTransportSummary(): array
    {
        $ids = $this->childIdList();
        $items = [];

        if ($ids === '' || !$this->tablesExist(['transport_student_assignments', 'transport_buses'])) {
            return $items;
        }

        $query = mysqli_query(
            $this->conn,
            "SELECT u.full_name, b.bus_number, b.bus_name, r.route_name, ts.stop_name,
                    d.full_name AS driver_name, d.phone AS driver_phone, l.status
             FROM transport_student_assignments tsa
             INNER JOIN students st ON st.id = tsa.student_id
             INNER JOIN users u ON u.id = st.user_id
             INNER JOIN transport_buses b ON b.bus_id = tsa.bus_id
             LEFT JOIN transport_routes r ON r.bus_id = b.bus_id
             LEFT JOIN transport_stops ts ON ts.stop_id = tsa.stop_id
             LEFT JOIN transport_staff d ON d.staff_id = b.driver_id
             LEFT JOIN transport_live_location l ON l.bus_id = b.bus_id
             WHERE tsa.student_id IN ($ids)
             ORDER BY u.full_name ASC"
        );

        while ($query && $row = mysqli_fetch_assoc($query)) {
            $items[] = $row;
        }

        return $items;
    }

    public function getRecentNotifications(int $limit = 5): array
    {
        if (!$this->tableExists('notifications')) {
            return [];
        }

        $uid = (int) ($_SESSION['user']['id'] ?? 0);
        $role = $_SESSION['user']['role'] ?? 'parent';
        $context = notification_user_context($this->conn, $uid, $role);

        return notification_recent_for_context($this->conn, $context, $limit);
    }

    

    public function getHomeworkOverview(): array
    {
        return [
            'configured' => false,
            'pending' => 0,
            'message' => 'Homework tracking is not configured in this installation.',
        ];
    }

    private function loadChildren(): void
    {
        if (empty($this->parentLinks)) {
            return;
        }

        $student_codes = [];

        foreach ($this->parentLinks as $link) {
            if (!empty($link['student_id'])) {
                $student_codes[] = "'" . mysqli_real_escape_string($this->conn, $link['student_id']) . "'";
            }
        }

        if (empty($student_codes)) {
            return;
        }

        $student_list = implode(',', array_unique($student_codes));

        $query = mysqli_query(
            $this->conn,
            "SELECT st.*, u.full_name, u.profile_photo,
                    c.class_name, s.section_name
             FROM students st
             INNER JOIN users u ON u.id = st.user_id
             INNER JOIN classes c ON c.class_id = st.class_id
             INNER JOIN sections s ON s.section_id = st.section_id
             WHERE st.student_id IN ($student_list)
             ORDER BY u.full_name ASC"
        );

        while ($query && $row = mysqli_fetch_assoc($query)) {
            $this->children[] = $row;
        }
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

    private function childIdList(): string
    {
        if (empty($this->children)) {
            return '';
        }

        return implode(',', array_map(fn($child) => (int) $child['id'], $this->children));
    }

    private function averagePerformance(): float
    {
        $ids = $this->childIdList();

        if ($ids === '' || !$this->tablesExist(['result_marks', 'results', 'exams'])) {
            return 0;
        }

        $row = mysqli_fetch_assoc(mysqli_query(
            $this->conn,
            "SELECT AVG(CASE WHEN e.total_marks > 0 THEN (rm.marks_obtained / e.total_marks) * 100 ELSE 0 END) AS avg_pct
             FROM result_marks rm
             INNER JOIN results r ON r.result_id = rm.result_id
             INNER JOIN exams e ON e.exam_id = r.exam_id
             WHERE rm.student_id IN ($ids) AND r.status = 'published'"
        ));

        return $row ? round((float) ($row['avg_pct'] ?? 0), 2) : 0;
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
