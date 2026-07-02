<?php

include('../config/auth.php');
include('../config/db.php');
include('../config/finance.php');

finance_ensure_tables($conn);

$fromDate = $_GET['from_date'] ?? date('Y-m-01');
$toDate = $_GET['to_date'] ?? date('Y-m-d');

$fromEsc = mysqli_real_escape_string($conn, $fromDate);
$toEsc = mysqli_real_escape_string($conn, $toDate);

$feeIncome = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT IFNULL(SUM(amount_paid),0) total
    FROM fee_payments
    WHERE payment_date BETWEEN '$fromEsc' AND '$toEsc'
"))['total'];

$salaryExpense = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT IFNULL(SUM(amount_paid),0) total
    FROM salary_payments
    WHERE payment_date BETWEEN '$fromEsc' AND '$toEsc'
"))['total'];

$netBalance = (float) $feeIncome - (float) $salaryExpense;

$totalFeeIncome = mysqli_fetch_assoc(mysqli_query($conn, "SELECT IFNULL(SUM(amount_paid),0) total FROM fee_payments"))['total'];
$totalSalaryExpense = mysqli_fetch_assoc(mysqli_query($conn, "SELECT IFNULL(SUM(amount_paid),0) total FROM salary_payments"))['total'];
$lifetimeNet = (float) $totalFeeIncome - (float) $totalSalaryExpense;

$recentTransactions = mysqli_query($conn, "
    SELECT *
    FROM (
        SELECT
            fp.receipt_no,
            fp.payment_date AS transaction_date,
            fp.created_at,
            fp.amount_paid AS amount,
            'income' AS flow_type,
            'Fees' AS source_module,
            CONCAT(u.full_name, ' - ', fs.fee_name) AS party,
            fp.payment_method,
            fp.transaction_ref
        FROM fee_payments fp
        INNER JOIN student_fees sf ON fp.student_fee_id = sf.student_fee_id
        INNER JOIN fee_structures fs ON sf.fee_structure_id = fs.fee_structure_id
        INNER JOIN students st ON sf.student_id = st.id
        INNER JOIN users u ON st.user_id = u.id
        WHERE fp.payment_date BETWEEN '$fromEsc' AND '$toEsc'

        UNION ALL

        SELECT
            sp.receipt_no,
            sp.payment_date AS transaction_date,
            sp.created_at,
            sp.amount_paid AS amount,
            'expense' AS flow_type,
            'Salaries' AS source_module,
            CONCAT(u.full_name, ' - ', sp.pay_period) AS party,
            sp.payment_method,
            sp.transaction_ref
        FROM salary_payments sp
        INNER JOIN teachers t ON sp.teacher_id = t.id
        INNER JOIN users u ON t.user_id = u.id
        WHERE sp.payment_date BETWEEN '$fromEsc' AND '$toEsc'
    ) ledger
    ORDER BY transaction_date DESC, created_at DESC
    LIMIT 50
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
<title>Billing | MindMerge SmartCampus</title>
<link rel="stylesheet" href="../assets/css/global.css">
<link rel="stylesheet" href="../assets/css/layout.css">
<link rel="stylesheet" href="../assets/css/components.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="../assets/css/material-theme.css">
</head>
<body>
<div class="app-layout">
<?php include('../partials/sidebar.php'); ?>
<div class="main-content">
<?php include('../partials/topbar.php'); ?>
<div class="page-content">

<div class="page-header">
<div>
<h1>Billing</h1>
<p>Track money coming in from fees and going out through salaries.</p>
</div>
<div style="display:flex;gap:12px;flex-wrap:wrap;">
<a href="../fees/index.php" class="btn"><i class="fa-solid fa-money-bill-wave"></i> Fees</a>
<a href="../salaries/index.php" class="btn"><i class="fa-solid fa-user-tie"></i> Salaries</a>
</div>
</div>

<div class="dashboard-section" style="margin-top:0;margin-bottom:24px;">
<form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
<div class="form-group" style="max-width:220px;margin-bottom:0;">
<label class="form-label">From</label>
<input type="date" name="from_date" class="form-input" value="<?php echo htmlspecialchars($fromDate); ?>">
</div>
<div class="form-group" style="max-width:220px;margin-bottom:0;">
<label class="form-label">To</label>
<input type="date" name="to_date" class="form-input" value="<?php echo htmlspecialchars($toDate); ?>">
</div>
<button class="btn btn-primary" type="submit"><i class="fa-solid fa-filter"></i> Apply</button>
<a class="btn" href="index.php"><i class="fa-solid fa-rotate-left"></i> This Month</a>
</form>
</div>

<div class="dashboard-grid">

    <div class="dashboard-card">
        <div>
            <h3>Fee Income</h3>
            <h2><?php echo finance_money($feeIncome); ?></h2>
            <p>Incoming payments in selected range</p>
        </div>
        <div class="card-icon">
            <i class="fa-solid fa-money-bill-trend-up"></i>
        </div>
    </div>

    <div class="dashboard-card">
        <div>
            <h3>Salary Expense</h3>
            <h2><?php echo finance_money($salaryExpense); ?></h2>
            <p>Outgoing salary payments</p>
        </div>
        <div class="card-icon">
            <i class="fa-solid fa-money-check-dollar"></i>
        </div>
    </div>

    <div class="dashboard-card">
        <div>
            <h3>Net Balance</h3>
            <h2 style="color:<?php echo $netBalance >= 0 ? '#16a34a' : '#dc2626'; ?>">
                <?php echo finance_money($netBalance); ?>
            </h2>
            <p>Income minus expense</p>
        </div>
        <div class="card-icon">
            <i class="fa-solid fa-scale-balanced"></i>
        </div>
    </div>

</div>

<div class="dashboard-section">
<div class="section-header"><h2>Recent Billing Ledger</h2></div>
<div class="table-responsive">
<table class="custom-table">
<thead>
<tr>
<th>Date</th>
<th>Receipt</th>
<th>Type</th>
<th>Source</th>
<th>Party</th>
<th>Method</th>
<th>Amount</th>
</tr>
</thead>
<tbody>
<?php if ($recentTransactions && mysqli_num_rows($recentTransactions) > 0) { ?>
<?php while ($row = mysqli_fetch_assoc($recentTransactions)) { ?>
<tr>
<td><?php echo htmlspecialchars($row['transaction_date']); ?></td>
<td><?php echo htmlspecialchars($row['receipt_no']); ?></td>
<td>
<span style="font-weight:700;color:<?php echo $row['flow_type'] === 'income' ? '#16a34a' : '#dc2626'; ?>">
<?php echo $row['flow_type'] === 'income' ? 'In' : 'Out'; ?>
</span>
</td>
<td><?php echo htmlspecialchars($row['source_module']); ?></td>
<td><?php echo htmlspecialchars($row['party']); ?></td>
<td><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $row['payment_method']))); ?></td>
<td style="font-weight:700;color:<?php echo $row['flow_type'] === 'income' ? '#16a34a' : '#dc2626'; ?>">
<?php echo ($row['flow_type'] === 'income' ? '+' : '-') . finance_money($row['amount']); ?>
</td>
</tr>
<?php } ?>
<?php } else { ?>
<tr><td colspan="7" style="text-align:center;">No billing transactions found for this range.</td></tr>
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
