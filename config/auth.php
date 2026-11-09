<?php

session_start();

require_once __DIR__ . '/constants.php';

if (!isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/permissions.php';
require_once __DIR__ . '/student_development.php';

/* Install only the additive Student Development tables when needed. */
student_development_ensure_schema($conn);

/*
 * Reload on every authenticated request so role changes and user overrides
 * become visible immediately, including in the sidebar. The permission table
 * is intentionally small, making this predictable and avoiding stale access
 * sessions after an administrator updates the matrix.
 */
permission_load_user($conn, $_SESSION['user']);

permission_guard_request($conn);
