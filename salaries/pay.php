<?php

include('../config/auth.php');
include('../config/db.php');
include('../config/finance.php');

finance_ensure_tables($conn);

$error = '';
$salaryId = isset($_GET['id']) ? (int) $_GET['id'] : (int) ($_POST['salary_id'] ?? 0);
$salaryData = null;

if ($salaryId > 0) {
    $salaryQuery = mysqli_query($conn, "
        SELECT
            ts.*,
            ts.teacher_id AS teacher_record_id,
            t.teacher_id AS teacher_code,
            u.full_name,
            u.email
        FROM teacher_salaries ts
        INNER JOIN teachers t ON ts.teacher_id = t.id
        INNER JOIN users u ON t.user_id = u.id
        WHERE ts.salary_id='$salaryId'
        LIMIT 1
    ");

    if ($salaryQuery && mysqli_num_rows($salaryQuery) > 0) {
        $salaryData = mysqli_fetch_assoc($salaryQuery);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $salaryId = (int) ($_POST['salary_id'] ?? 0);
    $teacherId = (int) ($_POST['teacher_id'] ?? 0);
    $payPeriod = mysqli_real_escape_string($conn, $_POST['pay_period'] ?? '');
    $amountPaid = (float) ($_POST['amount_paid'] ?? 0);
    $paymentDate = mysqli_real_escape_string($conn, $_POST['payment_date'] ?? '');
    $paymentMethod = mysqli_real_escape_string($conn, $_POST['payment_method'] ?? 'cash');
    $transactionRef = mysqli_real_escape_string($conn, trim($_POST['transaction_ref'] ?? ''));
    $remarks = mysqli_real_escape_string($conn, trim($_POST['remarks'] ?? ''));
    $createdBy = (int) ($_SESSION['user']['id'] ?? 0);

    if ($salaryId <= 0 || $teacherId <= 0) {
        $error = 'Invalid salary record.';
    } elseif ($payPeriod === '') {
        $error = 'Please select a salary period.';
    } elseif ($amountPaid <= 0) {
        $error = 'Payment amount must be greater than zero.';
    } elseif ($paymentDate === '') {
        $error = 'Please select a payment date.';
    } elseif (!array_key_exists($paymentMethod, finance_payment_methods())) {
        $error = 'Please select a valid payment method.';
    } else {
        $receiptNo = 'MMS' . date('Ymd') . rand(1000, 9999);

        $insert = mysqli_query($conn, "
            INSERT INTO salary_payments
            (receipt_no, salary_id, teacher_id, pay_period, amount_paid, payment_date, payment_method, transaction_ref, remarks, created_by)
            VALUES
            ('$receiptNo', '$salaryId', '$teacherId', '$payPeriod', '$amountPaid', '$paymentDate', '$paymentMethod', '$transactionRef', '$remarks', '$createdBy')
        ");

        if ($insert) {
            header('Location:payments.php?success=paid');
            exit;
        }

        $error = 'Failed to record salary payment.';
    }
}

$periodPaid = 0;
if ($salaryData) {
    $period = date('Y-m');
    $paidRow = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT IFNULL(SUM(amount_paid),0) total
        FROM salary_payments
        WHERE salary_id='" . (int) $salaryData['salary_id'] . "'
          AND pay_period='$period'
    "));
    $periodPaid = $paidRow['total'] ?? 0;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Give Salary | MindMerge SmartCampus</title>
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
<h1>Give Salary</h1>
<p>Record salary payment for a teacher.</p>
</div>
<a href="index.php" class="btn"><i class="fa-solid fa-arrow-left"></i> Back</a>
</div>

<?php if ($error !== '') { ?>
<div style="background:#fee2e2;color:#991b1b;padding:14px 18px;border-radius:14px;margin-bottom:20px;font-weight:500;"><?php echo htmlspecialchars($error); ?></div>
<?php } ?>

<?php if (!$salaryData) { ?>
<div class="dashboard-section">Salary record not found.</div>
<?php } else { ?>
<div class="dashboard-grid">
<div class="dashboard-card"><h3>Teacher</h3><h2><?php echo htmlspecialchars($salaryData['full_name']); ?></h2><p><?php echo htmlspecialchars($salaryData['teacher_code']); ?></p></div>
<div class="dashboard-card"><h3>Monthly Salary</h3><h2><?php echo finance_money($salaryData['monthly_salary']); ?></h2><p>Status: <?php echo ucfirst(htmlspecialchars($salaryData['status'])); ?></p></div>
<div class="dashboard-card"><h3>Paid Current Period</h3><h2><?php echo finance_money($periodPaid); ?></h2><p><?php echo date('F Y'); ?></p></div>
</div>

<div class="dashboard-section">
<form method="POST">
<input type="hidden" name="salary_id" value="<?php echo (int) $salaryData['salary_id']; ?>">
<input type="hidden" name="teacher_id" value="<?php echo (int) $salaryData['teacher_record_id']; ?>">

<div class="form-grid">
<div class="form-group">
<label class="form-label">Pay Period *</label>
<input type="month" name="pay_period" class="form-input" value="<?php echo htmlspecialchars($_POST['pay_period'] ?? date('Y-m')); ?>" required>
</div>

<div class="form-group">
<label class="form-label">Amount Paid *</label>
<input type="number" step="0.01" min="1" name="amount_paid" class="form-input" value="<?php echo htmlspecialchars($_POST['amount_paid'] ?? $salaryData['monthly_salary']); ?>" required>
</div>

<div class="form-group">
<label class="form-label">Payment Date *</label>
<input type="date" name="payment_date" class="form-input" value="<?php echo htmlspecialchars($_POST['payment_date'] ?? date('Y-m-d')); ?>" required>
</div>

<div class="form-group">
<label class="form-label">Payment Method *</label>
<select name="payment_method" class="form-select" required>
<?php foreach (finance_payment_methods() as $value => $label) { ?>
<option value="<?php echo htmlspecialchars($value); ?>" <?php echo (($_POST['payment_method'] ?? 'cash') === $value) ? 'selected' : ''; ?>><?php echo htmlspecialchars($label); ?></option>
<?php } ?>
</select>
</div>

<div class="form-group">
<label class="form-label">Transaction Reference</label>
<input type="text" name="transaction_ref" class="form-input" value="<?php echo htmlspecialchars($_POST['transaction_ref'] ?? ''); ?>" placeholder="UPI ID, cheque no, bank ref">
</div>

<div class="form-group">
<label class="form-label">Remarks</label>
<input type="text" name="remarks" class="form-input" value="<?php echo htmlspecialchars($_POST['remarks'] ?? ''); ?>" placeholder="Optional remarks">
</div>
</div>

<button type="submit" class="btn btn-primary"><i class="fa-solid fa-money-check-dollar"></i> Record Salary Payment</button>
</form>
</div>
<?php } ?>

</div>
</div>
</div>
<script src="../assets/js/sidebar.js"></script>
<script src="../assets/js/common.js"></script>
</body>
</html>
