<?php

include('../config/auth.php');
include('../config/db.php');
include('../config/finance.php');

finance_ensure_tables($conn);

$paymentId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$query = mysqli_query($conn, "
    SELECT sp.*, ts.monthly_salary, t.teacher_id, u.full_name, u.email
    FROM salary_payments sp
    INNER JOIN teacher_salaries ts ON sp.salary_id = ts.salary_id
    INNER JOIN teachers t ON sp.teacher_id = t.id
    INNER JOIN users u ON t.user_id = u.id
    WHERE sp.payment_id = '$paymentId'
    LIMIT 1
");

if (!$query || mysqli_num_rows($query) === 0) {
    header('Location:payments.php?error=not_found');
    exit;
}

$payment = mysqli_fetch_assoc($query);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Salary Payment Details | MindMerge SmartCampus</title>
<link rel="stylesheet" href="../assets/css/global.css">
<link rel="stylesheet" href="../assets/css/layout.css">
<link rel="stylesheet" href="../assets/css/components.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="../../assets/css/material-theme.css">
</head>
<body>
<div class="app-layout">
<?php include('../partials/sidebar.php'); ?>
<div class="main-content">
<?php include('../partials/topbar.php'); ?>
<div class="page-content">
<div class="page-header">
<div><h1>Salary Payment Details</h1><p>Receipt No: <?php echo htmlspecialchars($payment['receipt_no']); ?></p></div>
<div style="display:flex;gap:10px;flex-wrap:wrap;">
<a href="payments.php" class="btn"><i class="fa-solid fa-arrow-left"></i> Back</a>
<a href="payment-edit.php?id=<?php echo $paymentId; ?>" class="btn btn-primary"><i class="fa-solid fa-pen"></i> Edit</a>
</div>
</div>
<div class="dashboard-grid">
<div class="dashboard-card"><h3>Teacher</h3><h2><?php echo htmlspecialchars($payment['full_name']); ?></h2><p><?php echo htmlspecialchars($payment['teacher_id']); ?></p></div>
<div class="dashboard-card"><h3>Amount Paid</h3><h2><?php echo finance_money($payment['amount_paid']); ?></h2><p>Monthly salary: <?php echo finance_money($payment['monthly_salary']); ?></p></div>
<div class="dashboard-card"><h3>Period</h3><h2><?php echo htmlspecialchars($payment['pay_period']); ?></h2><p><?php echo htmlspecialchars($payment['payment_date']); ?></p></div>
</div>
<div class="dashboard-section">
<div class="section-header"><h2>Payment Information</h2></div>
<div class="form-grid">
<div class="form-group"><label class="form-label">Receipt No</label><div class="form-input"><?php echo htmlspecialchars($payment['receipt_no']); ?></div></div>
<div class="form-group"><label class="form-label">Payment Method</label><div class="form-input"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $payment['payment_method']))); ?></div></div>
<div class="form-group"><label class="form-label">Transaction Reference</label><div class="form-input"><?php echo htmlspecialchars($payment['transaction_ref'] ?: '-'); ?></div></div>
<div class="form-group"><label class="form-label">Created At</label><div class="form-input"><?php echo htmlspecialchars($payment['created_at']); ?></div></div>
<div class="form-group" style="grid-column:1/-1;"><label class="form-label">Remarks</label><div class="form-textarea"><?php echo nl2br(htmlspecialchars($payment['remarks'] ?: '-')); ?></div></div>
</div>
</div>
</div>
</div>
</div>
<script src="../assets/js/sidebar.js"></script>
<script src="../assets/js/common.js"></script>
</body>
</html>
