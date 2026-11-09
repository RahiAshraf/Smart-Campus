<?php

/**
 * Installs the additive Student Development schema on first use.
 * The migration is deliberately idempotent so existing installations and
 * data remain untouched.
 */
function student_development_ensure_schema(mysqli $conn): void
{
    static $checked = false;

    if ($checked) {
        return;
    }

    if (!mysqli_query($conn, 'CREATE TABLE IF NOT EXISTS sd_schema_versions (
        version INT NOT NULL PRIMARY KEY,
        applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4')) {
        die('Student Development migration tracking failed: ' . mysqli_error($conn));
    }

    $versionResult = mysqli_query($conn, 'SELECT 1 FROM sd_schema_versions WHERE version = 2 LIMIT 1');
    if ($versionResult && mysqli_num_rows($versionResult) > 0) {
        $checked = true;
        return;
    }

    $migration = __DIR__ . '/../database/student_development.sql';
    $sql = file_get_contents($migration);

    if ($sql === false || !student_development_run_multi_query($conn, $sql)) {
        die('Student Development schema installation failed. Check the PHP error log for the failed statement.');
    }

    $columns = [
        ['sd_clubs', 'registration_open', 'TINYINT(1) NOT NULL DEFAULT 1'],
        ['sd_clubs', 'published', 'TINYINT(1) NOT NULL DEFAULT 1'],
        ['sd_sports', 'registration_open', 'TINYINT(1) NOT NULL DEFAULT 1'],
        ['sd_sports', 'published', 'TINYINT(1) NOT NULL DEFAULT 1'],
    ];

    foreach ($columns as [$table, $column, $definition]) {
        $database = mysqli_real_escape_string($conn, (string) mysqli_fetch_row(mysqli_query($conn, 'SELECT DATABASE()'))[0]);
        $tableEscaped = mysqli_real_escape_string($conn, $table);
        $columnEscaped = mysqli_real_escape_string($conn, $column);
        $exists = mysqli_query(
            $conn,
            "SELECT 1
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = '{$database}'
               AND TABLE_NAME = '{$tableEscaped}'
               AND COLUMN_NAME = '{$columnEscaped}'
             LIMIT 1"
        );

        if (!$exists) {
            die('Student Development column check failed: ' . mysqli_error($conn));
        }

        if (mysqli_num_rows($exists) === 0) {
            if (!mysqli_query($conn, "ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}")) {
                die("Student Development column migration failed for {$table}.{$column}: " . mysqli_error($conn));
            }
        }
    }

    if (!mysqli_query($conn, 'INSERT INTO sd_schema_versions (version) VALUES (2)')) {
        die('Student Development migration version update failed: ' . mysqli_error($conn));
    }

    $checked = true;
}

function student_development_run_multi_query(mysqli $conn, string $sql): bool
{
    if (!mysqli_multi_query($conn, $sql)) {
        error_log('Student Development schema error: ' . mysqli_error($conn));
        return false;
    }

    do {
        $result = mysqli_store_result($conn);
        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        } elseif (mysqli_errno($conn) !== 0) {
            error_log('Student Development schema error: ' . mysqli_error($conn));
            return false;
        }

        if (!mysqli_more_results($conn)) {
            break;
        }
    } while (mysqli_next_result($conn));

    if (mysqli_errno($conn) !== 0) {
        error_log('Student Development schema error: ' . mysqli_error($conn));
        return false;
    }

    return true;
}

function student_development_refresh_permissions(mysqli $conn): void
{
    if (isset($_SESSION['user'])) {
        permission_load_user($conn, $_SESSION['user']);
    }
}

function student_development_bind(mysqli_stmt $stmt, string $types, array $values): void
{
    $params = [$types];
    foreach ($values as $key => $value) {
        $params[] = &$values[$key];
    }
    mysqli_stmt_bind_param($stmt, ...$params);
}
