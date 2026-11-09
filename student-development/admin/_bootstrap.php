<?php

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/student_development.php';
require_once __DIR__ . '/../../shared/helpers/portal.php';

portal_require_role(['admin']);
student_development_ensure_schema($conn);

function sd_admin_ensure_support_schema(mysqli $conn): void
{
    $queries = [
        'CREATE TABLE IF NOT EXISTS sd_audit_log (
            audit_id INT NOT NULL AUTO_INCREMENT,
            actor_user_id INT NOT NULL,
            action_key VARCHAR(80) NOT NULL,
            entity_type VARCHAR(80) NOT NULL,
            entity_id INT NULL,
            details VARCHAR(500) NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (audit_id),
            KEY idx_sd_audit_created (created_at),
            CONSTRAINT fk_sd_audit_actor FOREIGN KEY (actor_user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
        'CREATE TABLE IF NOT EXISTS sd_user_notifications (
            notification_id INT NOT NULL AUTO_INCREMENT,
            user_id INT NOT NULL,
            title VARCHAR(160) NOT NULL,
            message VARCHAR(500) NOT NULL,
            link VARCHAR(255) NULL,
            read_at DATETIME NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (notification_id),
            KEY idx_sd_user_notifications_user (user_id, read_at),
            CONSTRAINT fk_sd_user_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
    ];

    foreach ($queries as $query) {
        if (!mysqli_query($conn, $query)) {
            throw new RuntimeException('Student Development support schema failed: ' . mysqli_error($conn));
        }
    }
}

sd_admin_ensure_support_schema($conn);

if (!isset($_SESSION['sd_admin_csrf'])) {
    $_SESSION['sd_admin_csrf'] = bin2hex(random_bytes(32));
}

$sd_admin_csrf = $_SESSION['sd_admin_csrf'];
$sd_admin_user_id = (int) ($_SESSION['user']['id'] ?? 0);

function sd_admin_e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function sd_admin_stmt(mysqli $conn, string $sql, string $types = '', array $values = []): mysqli_stmt
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

function sd_admin_rows(mysqli_stmt $stmt): array
{
    $result = mysqli_stmt_get_result($stmt);
    $rows = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }

    mysqli_stmt_close($stmt);
    return $rows;
}

function sd_admin_require_csrf(string $expected): void
{
    if (!hash_equals($expected, $_POST['csrf'] ?? '')) {
        http_response_code(400);
        exit('Invalid request token.');
    }
}

function sd_admin_redirect(string $page, array $query = []): never
{
    $location = $page . ($query ? '?' . http_build_query($query) : '');
    header('Location: ' . $location);
    exit;
}

function sd_admin_flash(string $message, string $type = 'success'): void
{
    $_SESSION['sd_admin_flash'] = ['message' => $message, 'type' => $type];
}

function sd_admin_take_flash(): ?array
{
    $flash = $_SESSION['sd_admin_flash'] ?? null;
    unset($_SESSION['sd_admin_flash']);
    return $flash;
}

function sd_admin_audit(mysqli $conn, int $actor, string $action, string $entity, ?int $entityId, string $details = ''): void
{
    sd_admin_stmt($conn, 'INSERT INTO sd_audit_log (actor_user_id, action_key, entity_type, entity_id, details) VALUES (?, ?, ?, ?, ?)', 'issis', [$actor, $action, $entity, $entityId, $details]);
}

function sd_admin_notify(mysqli $conn, int $userId, string $title, string $message, string $link = ''): void
{
    sd_admin_stmt($conn, 'INSERT INTO sd_user_notifications (user_id, title, message, link) VALUES (?, ?, ?, ?)', 'isss', [$userId, $title, $message, $link]);
}
