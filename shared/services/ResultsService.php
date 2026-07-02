<?php

class ResultsService
{
    private mysqli $conn;
    private array $tableCache = [];
    private array $columnCache = [];

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function isReady(): bool
    {
        return $this->tablesExist(['results', 'result_marks', 'exams', 'students']);
    }

    public function getResultSets(array $scope = []): array
    {
        if (!$this->isReady()) {
            return [];
        }

        $where = ['1=1'];

        if (isset($scope['class_id'])) {
            $where[] = 'r.class_id = ' . (int) $scope['class_id'];
        }

        if (isset($scope['section_id'])) {
            $where[] = 'r.section_id = ' . (int) $scope['section_id'];
        }

        if (!empty($scope['exam_id'])) {
            $where[] = 'r.exam_id = ' . (int) $scope['exam_id'];
        }

        if (!empty($scope['result_id'])) {
            $where[] = 'r.result_id = ' . (int) $scope['result_id'];
        }

        if (!empty($scope['published_only'])) {
            $where[] = "r.status = 'published'";
        }

        if (!empty($scope['status'])) {
            $status = mysqli_real_escape_string($this->conn, $scope['status']);
            $where[] = "r.status = '$status'";
        }

        if (!empty($scope['teacher_id'])) {
            $teacherId = (int) $scope['teacher_id'];
            $where[] = "(
                r.class_id = 0
                OR EXISTS (
                    SELECT 1 FROM teacher_assignments ta
                    WHERE ta.teacher_id = '$teacherId'
                      AND ta.class_id = r.class_id
                      AND (r.section_id = 0 OR ta.section_id = r.section_id)
                )
            )";
        }

        $items = [];
        $query = mysqli_query(
            $this->conn,
            "SELECT r.*, e.exam_name, e.exam_type, e.exam_date, e.start_time, e.end_time, e.total_marks,
                    COALESCE(sub.subject_name, e.custom_subject, e.exam_name) AS subject_name,
                    COALESCE(c.class_name, 'School Wide') AS class_name,
                    COALESCE(s.section_name, 'All Sections') AS section_name,
                    COUNT(rm.mark_id) AS entry_count,
                    AVG(rm.marks_obtained) AS average_marks
             FROM results r
             INNER JOIN exams e ON e.exam_id = r.exam_id
             LEFT JOIN subjects sub ON sub.subject_id = e.subject_id
             LEFT JOIN classes c ON c.class_id = NULLIF(r.class_id, 0)
             LEFT JOIN sections s ON s.section_id = NULLIF(r.section_id, 0)
             LEFT JOIN result_marks rm ON rm.result_id = r.result_id
             WHERE " . implode(' AND ', $where) . "
             GROUP BY r.result_id, e.exam_name, e.exam_type, e.exam_date, e.start_time, e.end_time,
                      e.total_marks, subject_name, c.class_name, s.section_name
             ORDER BY COALESCE(e.exam_date, DATE(r.created_at)) DESC, r.created_at DESC"
        );

        while ($query && $row = mysqli_fetch_assoc($query)) {
            $row['exam_code'] = 'EXM-' . (int) $row['exam_id'];
            $row['exam_time'] = $row['start_time'] ?? null;
            $row['academic_year'] = !empty($row['exam_date']) ? date('Y', strtotime($row['exam_date'])) : date('Y');
            $items[] = $row;
        }

        return $items;
    }

    public function getResultById(int $result_id): ?array
    {
        if (!$this->isReady() || $result_id <= 0) {
            return null;
        }

        $items = $this->getResultSets(['result_id' => $result_id]);
        if (!empty($items)) {
            return $items[0];
        }

        $row = mysqli_fetch_assoc(mysqli_query(
            $this->conn,
            "SELECT r.*, e.exam_name, e.exam_type, e.exam_date, e.start_time, e.end_time, e.total_marks,
                    COALESCE(c.class_name, 'School Wide') AS class_name,
                    COALESCE(s.section_name, 'All Sections') AS section_name
             FROM results r
             INNER JOIN exams e ON e.exam_id = r.exam_id
             LEFT JOIN classes c ON c.class_id = NULLIF(r.class_id, 0)
             LEFT JOIN sections s ON s.section_id = NULLIF(r.section_id, 0)
             WHERE r.result_id = '$result_id'
             LIMIT 1"
        ));

        if (!$row) {
            return null;
        }

        $row['exam_code'] = 'EXM-' . (int) $row['exam_id'];
        $row['exam_time'] = $row['start_time'] ?? null;
        $row['academic_year'] = !empty($row['exam_date']) ? date('Y', strtotime($row['exam_date'])) : date('Y');

        return $row;
    }

    public function createResultFromExam(int $exam_id, int $created_by = 0): int
    {
        if (!$this->isReady() || $exam_id <= 0) {
            return 0;
        }

        $exam = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM exams WHERE exam_id = '$exam_id' LIMIT 1"));
        if (!$exam) {
            return 0;
        }

        $existing = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT result_id FROM results WHERE exam_id = '$exam_id' LIMIT 1"));
        if ($existing) {
            return (int) $existing['result_id'];
        }

        $classId = (int) ($exam['class_id'] ?? 0);
        $sectionId = (int) ($exam['section_id'] ?? 0);
        mysqli_query(
            $this->conn,
            "INSERT INTO results (exam_id, class_id, section_id, status, created_at)
             VALUES ('$exam_id', '$classId', '$sectionId', 'draft', NOW())"
        );

        return (int) mysqli_insert_id($this->conn);
    }

    public function deleteResult(int $result_id): bool
    {
        if (!$this->isReady() || $result_id <= 0) {
            return false;
        }

        mysqli_query($this->conn, "DELETE FROM result_marks WHERE result_id = '$result_id'");
        return (bool) mysqli_query($this->conn, "DELETE FROM results WHERE result_id = '$result_id'");
    }

    public function publishResult(int $result_id): bool
    {
        if (!$this->isReady() || $result_id <= 0 || count($this->getEntries($result_id)) === 0) {
            return false;
        }

        return (bool) mysqli_query($this->conn, "UPDATE results SET status='published', published_at=NOW() WHERE result_id='$result_id'");
    }

    public function unpublishResult(int $result_id): bool
    {
        if (!$this->isReady() || $result_id <= 0) {
            return false;
        }

        return (bool) mysqli_query($this->conn, "UPDATE results SET status='draft', published_at=NULL WHERE result_id='$result_id'");
    }

    public function getStudentsForResult(int $result_id): array
    {
        $result = $this->getResultById($result_id);
        if (!$result) {
            return [];
        }

        $where = ['1=1'];
        if ((int) $result['class_id'] > 0) {
            $where[] = 'st.class_id = ' . (int) $result['class_id'];
        }
        if ((int) $result['section_id'] > 0) {
            $where[] = 'st.section_id = ' . (int) $result['section_id'];
        }

        $students = [];
        $query = mysqli_query(
            $this->conn,
            "SELECT st.id, st.student_id, u.full_name
             FROM students st
             INNER JOIN users u ON u.id = st.user_id
             WHERE " . implode(' AND ', $where) . "
             ORDER BY u.full_name ASC"
        );

        while ($query && $row = mysqli_fetch_assoc($query)) {
            $students[] = $row;
        }

        return $students;
    }

    public function getSubjectsForResult(int $result_id): array
    {
        $result = $this->getResultById($result_id);
        if (!$result) {
            return [];
        }

        return [[
            'subject_id' => (int) ($result['exam_id'] ?? 0),
            'subject_name' => $result['subject_name'] ?? $result['exam_name'] ?? 'Exam',
            'subject_code' => 'EXM-' . (int) ($result['exam_id'] ?? 0),
        ]];
    }

    public function getEntries(int $result_id, ?int $subject_id = null): array
    {
        if (!$this->isReady() || $result_id <= 0) {
            return [];
        }

        $entries = [];
        $query = mysqli_query(
            $this->conn,
            "SELECT rm.mark_id AS entry_id, rm.*, u.full_name AS student_name, st.student_id AS student_code,
                    e.exam_id AS subject_id, COALESCE(sub.subject_name, e.custom_subject, e.exam_name) AS subject_name,
                    e.total_marks AS max_marks,
                    CASE WHEN e.total_marks > 0 THEN ROUND((rm.marks_obtained / e.total_marks) * 100, 2) ELSE 0 END AS percentage
             FROM result_marks rm
             INNER JOIN results r ON r.result_id = rm.result_id
             INNER JOIN exams e ON e.exam_id = r.exam_id
             INNER JOIN students st ON st.id = rm.student_id
             INNER JOIN users u ON u.id = st.user_id
             LEFT JOIN subjects sub ON sub.subject_id = e.subject_id
             WHERE rm.result_id = '$result_id'
             ORDER BY u.full_name ASC"
        );

        while ($query && $row = mysqli_fetch_assoc($query)) {
            $row['total_marks'] = $row['marks_obtained'];
            $gradeInfo = $this->resolveGrade((float) $row['percentage']);
            $row['grade'] = $gradeInfo['grade_name'];
            $row['grade_point'] = null;
            $entries[] = $row;
        }

        return $entries;
    }

    public function saveEntry(int $result_id, int $student_id, int $subject_id, array $marks, ?int $teacher_id = null): bool
    {
        if (!$this->isReady() || $result_id <= 0 || $student_id <= 0) {
            return false;
        }

        $result = $this->getResultById($result_id);
        if (!$result) {
            return false;
        }

        $max = max(0, (float) ($result['total_marks'] ?? 100));
        $value = (float) ($marks['marks_obtained'] ?? $marks['total_marks'] ?? $marks['external_marks'] ?? 0);
        $value = max(0, min($max, $value));
        $remarks = mysqli_real_escape_string($this->conn, (string) ($marks['remarks'] ?? ''));

        $existing = mysqli_fetch_assoc(mysqli_query(
            $this->conn,
            "SELECT mark_id FROM result_marks WHERE result_id='$result_id' AND student_id='$student_id' LIMIT 1"
        ));

        if ($existing) {
            return (bool) mysqli_query(
                $this->conn,
                "UPDATE result_marks SET marks_obtained='$value', remarks='$remarks' WHERE mark_id='" . (int) $existing['mark_id'] . "'"
            );
        }

        return (bool) mysqli_query(
            $this->conn,
            "INSERT INTO result_marks (result_id, student_id, marks_obtained, remarks, created_at)
             VALUES ('$result_id', '$student_id', '$value', '$remarks', NOW())"
        );
    }

    public function bulkSaveEntries(int $result_id, array $rows, ?int $teacher_id = null): int
    {
        $saved = 0;
        foreach ($rows as $row) {
            if ($this->saveEntry($result_id, (int) ($row['student_id'] ?? 0), 0, $row, $teacher_id)) {
                $saved++;
            }
        }
        return $saved;
    }

    public function resolveGrade(float $percentage): array
    {
        if ($this->tableExists('grading_system')) {
            $pct = (float) $percentage;
            $row = mysqli_fetch_assoc(mysqli_query(
                $this->conn,
                "SELECT grade_name, grade_point FROM grading_system
                 WHERE status='active' AND $pct BETWEEN min_marks AND max_marks
                 ORDER BY min_marks DESC LIMIT 1"
            ));
            if ($row) {
                return $row;
            }
        }

        if ($percentage >= 90) return ['grade_name' => 'A+', 'grade_point' => null];
        if ($percentage >= 80) return ['grade_name' => 'A', 'grade_point' => null];
        if ($percentage >= 70) return ['grade_name' => 'B+', 'grade_point' => null];
        if ($percentage >= 60) return ['grade_name' => 'B', 'grade_point' => null];
        if ($percentage >= 50) return ['grade_name' => 'C', 'grade_point' => null];
        if ($percentage >= 35) return ['grade_name' => 'D', 'grade_point' => null];
        return ['grade_name' => 'F', 'grade_point' => null];
    }

    public function getStudentResults(int $student_db_id): array
    {
        if (!$this->isReady() || $student_db_id <= 0) {
            return [];
        }

        $items = [];
        $query = mysqli_query(
            $this->conn,
            "SELECT r.result_id, r.status, r.published_at, r.created_at,
                    e.exam_id, e.exam_name, e.exam_type, e.exam_date, e.total_marks AS max_marks,
                    COALESCE(sub.subject_name, e.custom_subject, e.exam_name) AS subject_name,
                    rm.marks_obtained, rm.remarks,
                    CASE WHEN e.total_marks > 0 THEN ROUND((rm.marks_obtained / e.total_marks) * 100, 2) ELSE 0 END AS percentage
             FROM result_marks rm
             INNER JOIN results r ON r.result_id = rm.result_id
             INNER JOIN exams e ON e.exam_id = r.exam_id
             LEFT JOIN subjects sub ON sub.subject_id = e.subject_id
             WHERE rm.student_id = '$student_db_id' AND r.status = 'published'
             ORDER BY COALESCE(e.exam_date, DATE(r.created_at)) DESC, r.published_at DESC"
        );

        while ($query && $row = mysqli_fetch_assoc($query)) {
            $gradeInfo = $this->resolveGrade((float) $row['percentage']);
            $row['exam_code'] = 'EXM-' . (int) $row['exam_id'];
            $row['academic_year'] = !empty($row['exam_date']) ? date('Y', strtotime($row['exam_date'])) : date('Y', strtotime($row['created_at']));
            $row['total_marks'] = $row['marks_obtained'];
            $row['grade'] = $gradeInfo['grade_name'];
            $row['grade_point'] = null;
            $items[] = $row;
        }

        return $items;
    }

    public function getLatestGpa(int $student_db_id): ?array
    {
        $summary = $this->getLatestPerformance($student_db_id);
        if ($summary === null) {
            return null;
        }

        return [
            'academic_year' => $summary['academic_year'],
            'semester' => '',
            'gpa' => null,
            'overall_gpa' => null,
            'percentage' => $summary['percentage'],
            'grade' => $summary['grade'],
        ];
    }

    public function calculateSemesterGpa(int $student_db_id, string $academic_year, string $semester): ?float
    {
        return null;
    }

    public function calculateOverallGpa(int $student_db_id): ?float
    {
        return null;
    }

    public function getLatestPerformance(int $student_db_id): ?array
    {
        $results = $this->getStudentResults($student_db_id);
        if (empty($results)) {
            return null;
        }

        $latest = $results[0];
        return [
            'academic_year' => $latest['academic_year'],
            'percentage' => (float) $latest['percentage'],
            'grade' => $latest['grade'],
        ];
    }

    public function getPerformanceTrend(int $student_db_id): array
    {
        if (!$this->isReady() || $student_db_id <= 0) {
            return ['labels' => [], 'values' => []];
        }

        $labels = [];
        $values = [];
        $query = mysqli_query(
            $this->conn,
            "SELECT e.exam_name, e.total_marks, rm.marks_obtained, COALESCE(e.exam_date, DATE(r.created_at)) AS sort_date
             FROM result_marks rm
             INNER JOIN results r ON r.result_id = rm.result_id
             INNER JOIN exams e ON e.exam_id = r.exam_id
             WHERE rm.student_id = '$student_db_id' AND r.status = 'published'
             ORDER BY sort_date ASC"
        );

        while ($query && $row = mysqli_fetch_assoc($query)) {
            $total = (float) ($row['total_marks'] ?: 100);
            $labels[] = $row['exam_name'];
            $values[] = $total > 0 ? round(((float) $row['marks_obtained'] / $total) * 100, 2) : 0;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    public function exportEntriesCsv(int $result_id): string
    {
        $entries = $this->getEntries($result_id);
        $lines = ['Student ID,Student Name,Exam,Marks,Max Marks,Percentage,Grade,Remarks'];

        foreach ($entries as $e) {
            $lines[] = implode(',', [
                '"' . str_replace('"', '""', $e['student_code'] ?? '') . '"',
                '"' . str_replace('"', '""', $e['student_name'] ?? '') . '"',
                '"' . str_replace('"', '""', $e['subject_name'] ?? '') . '"',
                $e['marks_obtained'],
                $e['max_marks'],
                $e['percentage'],
                '"' . ($e['grade'] ?? '') . '"',
                '"' . str_replace('"', '""', $e['remarks'] ?? '') . '"',
            ]);
        }

        return implode("\n", $lines);
    }

    public function exportEntriesExcelHtml(int $result_id): string
    {
        $result = $this->getResultById($result_id);
        $entries = $this->getEntries($result_id);
        $title = htmlspecialchars(($result['exam_name'] ?? 'Result') . ' Result Sheet');
        $html = '<table border="1"><thead><tr><th colspan="8">' . $title . '</th></tr>';
        $html .= '<tr><th>Student ID</th><th>Student Name</th><th>Exam</th><th>Marks</th><th>Max Marks</th><th>Percentage</th><th>Grade</th><th>Remarks</th></tr></thead><tbody>';

        foreach ($entries as $e) {
            $html .= '<tr>';
            foreach (['student_code', 'student_name', 'subject_name', 'marks_obtained', 'max_marks', 'percentage', 'grade', 'remarks'] as $key) {
                $html .= '<td>' . htmlspecialchars((string) ($e[$key] ?? '')) . '</td>';
            }
            $html .= '</tr>';
        }

        return $html . '</tbody></table>';
    }

    public function exportEntriesPdf(int $result_id): string
    {
        $result = $this->getResultById($result_id);
        $entries = $this->getEntries($result_id);
        $lines = [
            ($result['exam_name'] ?? 'Result Sheet') . ' - Result #' . $result_id,
            ($result['class_name'] ?? '') . ' / ' . ($result['section_name'] ?? ''),
            '',
        ];

        foreach ($entries as $e) {
            $lines[] = ($e['student_code'] ?? '') . '  ' . ($e['student_name'] ?? '') . '  Marks: ' . ($e['marks_obtained'] ?? '0') . '/' . ($e['max_marks'] ?? '0') . '  Grade: ' . ($e['grade'] ?? '-');
        }

        return $this->buildSimplePdf($lines);
    }

    public function getResultsForTeacher(int $teacher_db_id): array
    {
        if ($teacher_db_id <= 0) {
            return [];
        }

        return $this->getResultSets(['teacher_id' => $teacher_db_id]);
    }

    private function buildSimplePdf(array $lines): string
    {
        $content = "BT\n/F1 11 Tf\n50 790 Td\n";
        $first = true;

        foreach ($lines as $line) {
            $safe = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], substr((string) $line, 0, 110));
            if (!$first) {
                $content .= "0 -16 Td\n";
            }
            $content .= '(' . $safe . ") Tj\n";
            $first = false;
        }

        $content .= "ET\n";
        $objects = [
            "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj\n",
            "2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj\n",
            "3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >> endobj\n",
            "4 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj\n",
            "5 0 obj << /Length " . strlen($content) . " >> stream\n" . $content . "endstream endobj\n",
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object;
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";

        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= str_pad((string) $offsets[$i], 10, '0', STR_PAD_LEFT) . " 00000 n \n";
        }

        return $pdf . "trailer << /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n$xref\n%%EOF";
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

    private function columnExists(string $table, string $column): bool
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
