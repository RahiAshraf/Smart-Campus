<?php

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/student_development.php';
require_once __DIR__ . '/../../shared/helpers/portal.php';

student_development_ensure_schema($conn);
$sd_user_role = portal_get_role();
$sd_user_id = (int) ($_SESSION['user']['id'] ?? 0);

function sd_user_e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function sd_user_stmt(mysqli $conn, string $sql, string $types = '', array $values = []): mysqli_stmt
{
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new RuntimeException('Student Development query preparation failed.');
    }
    if ($types !== '') {
        student_development_bind($stmt, $types, $values);
    }
    if (!mysqli_stmt_execute($stmt)) {
        throw new RuntimeException('Student Development query execution failed.');
    }
    return $stmt;
}

function sd_user_rows(mysqli_stmt $stmt): array
{
    $result = mysqli_stmt_get_result($stmt);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $rows;
}

function sd_user_csrf(): string
{
    if (!isset($_SESSION['sd_user_csrf'])) {
        $_SESSION['sd_user_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['sd_user_csrf'];
}

function sd_user_require_csrf(): void
{
    if (!hash_equals(sd_user_csrf(), $_POST['csrf'] ?? '')) {
        http_response_code(400);
        exit('Invalid request token.');
    }
}

function sd_user_student_id(mysqli $conn, int $userId): int
{
    $row = sd_user_rows(sd_user_stmt($conn, 'SELECT id FROM students WHERE user_id = ? LIMIT 1', 'i', [$userId]))[0] ?? [];
    return (int) ($row['id'] ?? 0);
}

function sd_user_parent_children(mysqli $conn, int $userId): array
{
    return sd_user_rows(sd_user_stmt($conn, 'SELECT s.id, s.student_id, u.full_name FROM parents p JOIN students s ON s.student_id = p.student_id JOIN users u ON u.id = s.user_id WHERE p.user_id = ? ORDER BY u.full_name', 'i', [$userId]));
}

