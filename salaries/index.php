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
           OR u.email LIKE '%$search%'
           OR t.teacher_id LIKE '%$search%'
           OR ts.status LIKE '%$search%'
    ";
}

$salaryQuery = mysqli_query($conn, "
    SELECT
        ts.*,
        t.teacher_id,
        u.full_name,
        u.email,
        u.phone,
        IFNULL((
            SELECT SUM(sp.amount_paid)
            FROM salary_payments sp
            WHERE sp.salary_id = ts.salary_id
        ), 0) AS total_paid,
        IFNULL((
            SELECT SUM(sp.amount_paid)
            FROM salary_payments sp
            WHERE sp.salary_id = ts.salary_id
              AND sp.pay_period = DATE_FORMAT(CURDATE(), '%Y-%m')
        ), 0) AS paid_this_month
    FROM teacher_salaries ts
    INNER JOIN teachers t ON ts.teacher_id = t.id
    INNER JOIN users u ON t.user_id = u.id
    $where
    ORDER BY ts.salary_id DESC
");

$totalSalaries = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) total FROM teacher_salaries"))['total'];
$activePayroll = mysqli_fetch_assoc(mysqli_query($conn, "SELECT IFNULL(SUM(monthly_salary),0) total FROM teacher_salaries WHERE status='active'"))['total'];
$paidThisMonth = mysqli_fetch_assoc(mysqli_query($conn, "SELECT IFNULL(SUM(amount_paid),0) total FROM salary_payments WHERE pay_period = DATE_FORMAT(CURDATE(), '%Y-%m')"))['total'];
$pendingThisMonth = max(0, (float) $activePayroll - (float) $paidThisMonth);

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
<title>Salaries Management | MindMerge SmartCampus</title>
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
<h1>Salaries Management</h1>
<p>Assign salaries to teachers and record salary payments.</p>
</div>
<div style="display:flex;gap:12px;flex-wrap:wrap;">
<a href="../billing/index.php" class="btn"><i class="fa-solid fa-chart-line"></i> Billing</a>
<a href="payments.php" class="btn"><i class="fa-solid fa-receipt"></i> Payments</a>
<a href="add.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add Salary</a>
</div>
</div>

<div class="dashboard-grid">

<div class="dashboard-card">
    <div>
        <h3>Salary Records</h3>
        <h2><?php echo number_format($totalSalaries); ?></h2>
        <p>Total teacher salary setups</p>
    </div>
    <div class="card-icon"><i class="fa-solid fa-file-invoice-dollar"></i></div>
</div>

<div class="dashboard-card">
    <div>
        <h3>Active Payroll</h3>
        <h2><?php echo finance_money($activePayroll); ?></h2>
        <p>Monthly committed salaries</p>
    </div>
    <div class="card-icon"><i class="fa-solid fa-user-tie"></i></div>
</div>

<div class="dashboard-card">
    <div>
        <h3>Paid This Month</h3>
        <h2><?php echo finance_money($paidThisMonth); ?></h2>
        <p>Salary outflow recorded</p>
    </div>
    <div class="card-icon"><i class="fa-solid fa-money-check-dollar"></i></div>
</div>

<div class="dashboard-card">
    <div>
        <h3>Pending Estimate</h3>
        <h2><?php echo finance_money($pendingThisMonth); ?></h2>
        <p>Active payroll minus paid</p>
    </div>
    <div class="card-icon"><i class="fa-solid fa-hourglass-half"></i></div>
</div>

</div>

<div class="dashboard-section">
<div class="section-header">
<h2>Teacher Salaries</h2>
</div>

<form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:20px;">
<input type="text" name="search" class="form-input" style="max-width:360px;" placeholder="Search teacher, email, ID or status" value="<?php echo htmlspecialchars($search); ?>">
<button class="btn btn-primary" type="submit"><i class="fa-solid fa-search"></i> Search</button>
<a class="btn" href="index.php"><i class="fa-solid fa-rotate-left"></i> Reset</a>
</form>

<div class="table-responsive">
<table class="custom-table">
<thead>
<tr>
<th>Teacher</th>
<th>Monthly Salary</th>
<th>Effective From</th>
<th>Status</th>
<th>Paid This Month</th>
<th>Total Paid</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php if ($salaryQuery && mysqli_num_rows($salaryQuery) > 0) { ?>
<?php while ($row = mysqli_fetch_assoc($salaryQuery)) { ?>
<tr>
<td>
<strong><?php echo htmlspecialchars($row['full_name']); ?></strong><br>
<small><?php echo htmlspecialchars($row['teacher_id']); ?> | <?php echo htmlspecialchars($row['email']); ?></small>
</td>
<td><?php echo finance_money($row['monthly_salary']); ?></td>
<td><?php echo htmlspecialchars($row['effective_from']); ?></td>
<td><?php echo ucfirst(htmlspecialchars($row['status'])); ?></td>
<td><?php echo finance_money($row['paid_this_month']); ?></td>
<td><?php echo finance_money($row['total_paid']); ?></td>
<td>
<div style="display:flex;gap:8px;flex-wrap:wrap;">
<a href="view.php?id=<?php echo (int) $row['salary_id']; ?>" class="btn btn-primary"><i class="fa-solid fa-eye"></i> View</a>
<a href="edit.php?id=<?php echo (int) $row['salary_id']; ?>" class="btn btn-primary"><i class="fa-solid fa-pen"></i> Edit</a>
<a href="pay.php?id=<?php echo (int) $row['salary_id']; ?>" class="btn btn-primary"><i class="fa-solid fa-money-check-dollar"></i> Pay</a>
<a href="delete.php?id=<?php echo (int) $row['salary_id']; ?>" class="btn" style="background:#ef4444;color:white;" onclick="return confirm('Delete this salary setup and its related payments?');"><i class="fa-solid fa-trash"></i> Delete</a>
</div>
</td>
</tr>
<?php } ?>
<?php } else { ?>
<tr><td colspan="7" style="text-align:center;">No salary records found.</td></tr>
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
