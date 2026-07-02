<?php

include('../config/auth.php');
include('../config/db.php');
include('../config/finance.php');

finance_ensure_tables($conn);

$salaryId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($salaryId <= 0) {
    header('Location:index.php?error=not_found');
    exit;
}

$check = mysqli_query($conn, "SELECT salary_id FROM teacher_salaries WHERE salary_id='$salaryId' LIMIT 1");
if (!$check || mysqli_num_rows($check) === 0) {
    header('Location:index.php?error=not_found');
    exit;
}

mysqli_begin_transaction($conn);

try {
    mysqli_query($conn, "DELETE FROM salary_payments WHERE salary_id='$salaryId'");
    mysqli_query($conn, "DELETE FROM teacher_salaries WHERE salary_id='$salaryId'");
    mysqli_commit($conn);
    header('Location:index.php?success=deleted');
    exit;
} catch (Throwable $exception) {
    mysqli_rollback($conn);
    header('Location:index.php?error=delete_failed');
    exit;
}

