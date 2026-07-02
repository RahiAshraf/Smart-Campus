<?php

include('../config/auth.php');
include('../config/db.php');
include('../config/finance.php');

finance_ensure_tables($conn);

$search = '';
$where = '';

if (isset($_GET['search']) && trim($_GET['search']) !== '') {
    $search = mysqli_real_escape_string($conn, trim($_GET['search']));
    $where = "
        WHERE u.full_name LIKE '%$search%'
           OR t.teacher_id LIKE '%$search%'
           OR sp.receipt_no LIKE '%$search%'
           OR sp.pay_period LIKE '%$search%'
           OR sp.payment_method LIKE '%$search%'
    ";
}

$payments = mysqli_query($conn, "
    SELECT
        sp.*,
        ts.monthly_salary,
        t.teacher_id,
        u.full_name,
        u.email
    FROM salary_payments sp
    INNER JOIN teacher_salaries ts ON sp.salary_id = ts.salary_id
    INNER JOIN teachers t ON sp.teacher_id = t.id
    INNER JOIN users u ON t.user_id = u.id
    $where
    ORDER BY sp.payment_id DESC
");

$totalPayments = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) total FROM salary_payments"))['total'];
$totalPaid = mysqli_fetch_assoc(mysqli_query($conn, "SELECT IFNULL(SUM(amount_paid),0) total FROM salary_payments"))['total'];
$todayPaid = mysqli_fetch_assoc(mysqli_query($conn, "SELECT IFNULL(SUM(amount_paid),0) total FROM salary_payments WHERE payment_date = CURDATE()"))['total'];
$monthPaid = mysqli_fetch_assoc(mysqli_query($conn, "SELECT IFNULL(SUM(amount_paid),0) total FROM salary_payments WHERE MONTH(payment_date)=MONTH(CURDATE()) AND YEAR(payment_date)=YEAR(CURDATE())"))['total'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Salary Payments | MindMerge SmartCampus</title>
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
<h1>Salary Payments</h1>
<p>Outgoing salary payment history.</p>
</div>
<a href="index.php" class="btn"><i class="fa-solid fa-arrow-left"></i> Back</a>
</div>

<div class="dashboard-grid">
<div class="dashboard-card"><h3>Total Payments</h3><h2><?php echo number_format($totalPayments); ?></h2><p>Salary payment entries</p></div>
<div class="dashboard-card"><h3>Total Paid</h3><h2><?php echo finance_money($totalPaid); ?></h2><p>All salary outflow</p></div>
<div class="dashboard-card"><h3>Today</h3><h2><?php echo finance_money($todayPaid); ?></h2><p>Paid today</p></div>
<div class="dashboard-card"><h3>This Month</h3><h2><?php echo finance_money($monthPaid); ?></h2><p>Paid this month</p></div>
</div>

<div class="dashboard-section">
<div class="section-header"><h2>Payment History</h2></div>
<form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:20px;">
<input type="text" name="search" class="form-input" style="max-width:360px;" placeholder="Search teacher, receipt, period or method" value="<?php echo htmlspecialchars($search); ?>">
<button class="btn btn-primary" type="submit"><i class="fa-solid fa-search"></i> Search</button>
<a class="btn" href="payments.php"><i class="fa-solid fa-rotate-left"></i> Reset</a>
</form>

<div class="table-responsive">
<table class="custom-table">
<thead>
<tr>
<th>Receipt</th>
<th>Teacher</th>
<th>Period</th>
<th>Amount</th>
<th>Date</th>
<th>Method</th>
<th>Reference</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php if ($payments && mysqli_num_rows($payments) > 0) { ?>
<?php while ($payment = mysqli_fetch_assoc($payments)) { ?>
<tr>
<td><?php echo htmlspecialchars($payment['receipt_no']); ?></td>
<td><strong><?php echo htmlspecialchars($payment['full_name']); ?></strong><br><small><?php echo htmlspecialchars($payment['teacher_id']); ?></small></td>
<td><?php echo htmlspecialchars($payment['pay_period']); ?></td>
<td><?php echo finance_money($payment['amount_paid']); ?></td>
<td><?php echo htmlspecialchars($payment['payment_date']); ?></td>
<td><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $payment['payment_method']))); ?></td>
<td><?php echo htmlspecialchars($payment['transaction_ref'] ?: '-'); ?></td>
<td>
<div style="display:flex;gap:8px;flex-wrap:wrap;">
<a href="payment-view.php?id=<?php echo (int) $payment['payment_id']; ?>" class="btn btn-primary"><i class="fa-solid fa-eye"></i> View</a>
<a href="payment-edit.php?id=<?php echo (int) $payment['payment_id']; ?>" class="btn btn-primary"><i class="fa-solid fa-pen"></i> Edit</a>
<a href="payment-delete.php?id=<?php echo (int) $payment['payment_id']; ?>" class="btn btn-danger" style="background:#ef4444;color:white;" onclick="return confirm('Delete this salary payment?');"><i class="fa-solid fa-trash"></i> Delete</a>
</div>
</td>
</tr>
<?php } ?>
<?php } else { ?>
<tr><td colspan="8" style="text-align:center;">No salary payments found.</td></tr>
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
