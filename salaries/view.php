<?php

include('../config/auth.php');
include('../config/db.php');
include('../config/finance.php');

finance_ensure_tables($conn);

$salaryId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$query = mysqli_query($conn, "
    SELECT ts.*, t.teacher_id, u.full_name, u.email, u.phone,
           IFNULL(SUM(sp.amount_paid), 0) AS total_paid
    FROM teacher_salaries ts
    INNER JOIN teachers t ON ts.teacher_id = t.id
    INNER JOIN users u ON t.user_id = u.id
    LEFT JOIN salary_payments sp ON sp.salary_id = ts.salary_id
    WHERE ts.salary_id = '$salaryId'
    GROUP BY ts.salary_id, t.teacher_id, u.full_name, u.email, u.phone
    LIMIT 1
");

if (!$query || mysqli_num_rows($query) === 0) {
    header('Location:index.php?error=not_found');
    exit;
}

$salary = mysqli_fetch_assoc($query);
$payments = mysqli_query($conn, "
    SELECT *
    FROM salary_payments
    WHERE salary_id = '$salaryId'
    ORDER BY payment_date DESC, payment_id DESC
");

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">

<script>
(function(){
    if(localStorage.getItem("mindmerge-theme") === "dark"){
        document.documentElement.classList.add("dark-mode");
    }
})();
</script>


<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Salary Details | MindMerge SmartCampus</title>
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
<div>
<h1>Salary Details</h1>
<p><?php echo htmlspecialchars($salary['full_name']); ?> - <?php echo htmlspecialchars($salary['teacher_id']); ?></p>
</div>
<div style="display:flex;gap:10px;flex-wrap:wrap;">
<a href="index.php" class="btn"><i class="fa-solid fa-arrow-left"></i> Back</a>
<a href="edit.php?id=<?php echo $salaryId; ?>" class="btn btn-primary"><i class="fa-solid fa-pen"></i> Edit</a>
</div>
</div>

<div class="dashboard-grid">
<div class="dashboard-card"><h3>Monthly Salary</h3><h2><?php echo finance_money($salary['monthly_salary']); ?></h2><p>Status: <?php echo ucfirst(htmlspecialchars($salary['status'])); ?></p></div>
<div class="dashboard-card"><h3>Total Paid</h3><h2><?php echo finance_money($salary['total_paid']); ?></h2><p>All recorded payments</p></div>
<div class="dashboard-card"><h3>Effective From</h3><h2><?php echo htmlspecialchars($salary['effective_from']); ?></h2><p>Payroll start date</p></div>
</div>

<div class="dashboard-section">
<div class="section-header"><h2>Teacher Information</h2></div>
<div class="form-grid">
<div class="form-group"><label class="form-label">Teacher</label><div class="form-input"><?php echo htmlspecialchars($salary['full_name']); ?></div></div>
<div class="form-group"><label class="form-label">Teacher ID</label><div class="form-input"><?php echo htmlspecialchars($salary['teacher_id']); ?></div></div>
<div class="form-group"><label class="form-label">Email</label><div class="form-input"><?php echo htmlspecialchars($salary['email']); ?></div></div>
<div class="form-group"><label class="form-label">Phone</label><div class="form-input"><?php echo htmlspecialchars($salary['phone'] ?: '-'); ?></div></div>
<div class="form-group" style="grid-column:1/-1;"><label class="form-label">Notes</label><div class="form-textarea"><?php echo nl2br(htmlspecialchars($salary['notes'] ?: '-')); ?></div></div>
</div>
</div>

<div class="dashboard-section">
<div class="section-header"><h2>Payment History</h2></div>
<div class="table-responsive">
<table class="custom-table">
<thead><tr><th>Receipt</th><th>Period</th><th>Amount</th><th>Date</th><th>Method</th><th>Actions</th></tr></thead>
<tbody>
<?php if ($payments && mysqli_num_rows($payments) > 0) { ?>
<?php while ($payment = mysqli_fetch_assoc($payments)) { ?>
<tr>
<td><?php echo htmlspecialchars($payment['receipt_no']); ?></td>
<td><?php echo htmlspecialchars($payment['pay_period']); ?></td>
<td><?php echo finance_money($payment['amount_paid']); ?></td>
<td><?php echo htmlspecialchars($payment['payment_date']); ?></td>
<td><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $payment['payment_method']))); ?></td>
<td><a href="payment-view.php?id=<?php echo (int) $payment['payment_id']; ?>" class="btn"><i class="fa-solid fa-eye"></i> View</a></td>
</tr>
<?php } ?>
<?php } else { ?>
<tr><td colspan="6" style="text-align:center;">No payments recorded.</td></tr>
<?php } ?>
</tbody>
</table>
</div>
</div>
</div>
</div>
</div>
<script src="../assets/js/sidebar.js"></script>
<script src="../assets/js/common.js"></script>
</body>
</html>
