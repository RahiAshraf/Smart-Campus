<?php

include('../config/auth.php');
include('../config/db.php');
include('../config/finance.php');

finance_ensure_tables($conn);

$paymentId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($paymentId <= 0) {
    header('Location:payments.php?error=not_found');
    exit;
}

$deleted = mysqli_query($conn, "DELETE FROM salary_payments WHERE payment_id='$paymentId'");

if ($deleted) {
    header('Location:payments.php?success=deleted');
} else {
    header('Location:payments.php?error=delete_failed');
}

exit;
