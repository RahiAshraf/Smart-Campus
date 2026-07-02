<?php

include('../config/auth.php');
include('../config/db.php');
include('../config/finance.php');

finance_ensure_tables($conn);

$error = '';
$paymentId = isset($_GET['id']) ? (int) $_GET['id'] : (int) ($_POST['payment_id'] ?? 0);

$query = mysqli_query($conn, "
    SELECT sp.*, t.teacher_id, u.full_name
    FROM salary_payments sp
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payPeriod = mysqli_real_escape_string($conn, $_POST['pay_period'] ?? '');
    $amountPaid = (float) ($_POST['amount_paid'] ?? 0);
    $paymentDate = mysqli_real_escape_string($conn, $_POST['payment_date'] ?? '');
    $paymentMethod = mysqli_real_escape_string($conn, $_POST['payment_method'] ?? 'cash');
    $transactionRef = mysqli_real_escape_string($conn, trim($_POST['transaction_ref'] ?? ''));
    $remarks = mysqli_real_escape_string($conn, trim($_POST['remarks'] ?? ''));

    if ($payPeriod === '') {
        $error = 'Please select a salary period.';
    } elseif ($amountPaid <= 0) {
        $error = 'Payment amount must be greater than zero.';
    } elseif ($paymentDate === '') {
        $error = 'Please select a payment date.';
    } elseif (!array_key_exists($paymentMethod, finance_payment_methods())) {
        $error = 'Please select a valid payment method.';
    } else {
        $updated = mysqli_query($conn, "
            UPDATE salary_payments
            SET pay_period='$payPeriod',
                amount_paid='$amountPaid',
                payment_date='$paymentDate',
                payment_method='$paymentMethod',
                transaction_ref='$transactionRef',
                remarks='$remarks'
            WHERE payment_id='$paymentId'
        ");

        if ($updated) {
            header('Location:payment-view.php?id=' . $paymentId . '&success=updated');
            exit;
        }

        $error = 'Failed to update salary payment.';
    }
}

function payment_form_value(array $payment, string $key): string
{
    return htmlspecialchars($_POST[$key] ?? $payment[$key] ?? '');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Salary Payment | MindMerge SmartCampus</title>
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
<div><h1>Edit Salary Payment</h1><p><?php echo htmlspecialchars($payment['full_name'] . ' - ' . $payment['teacher_id']); ?></p></div>
<a href="payment-view.php?id=<?php echo $paymentId; ?>" class="btn"><i class="fa-solid fa-arrow-left"></i> Back</a>
</div>
<?php if ($error !== '') { ?><div style="background:#fee2e2;color:#991b1b;padding:14px 18px;border-radius:14px;margin-bottom:20px;font-weight:500;"><?php echo htmlspecialchars($error); ?></div><?php } ?>
<div class="dashboard-section">
<form method="POST">
<input type="hidden" name="payment_id" value="<?php echo $paymentId; ?>">
<div class="form-grid">
<div class="form-group"><label class="form-label">Receipt No</label><div class="form-input"><?php echo htmlspecialchars($payment['receipt_no']); ?></div></div>
<div class="form-group"><label class="form-label">Pay Period *</label><input type="month" name="pay_period" class="form-input" value="<?php echo payment_form_value($payment, 'pay_period'); ?>" required></div>
<div class="form-group"><label class="form-label">Amount Paid *</label><input type="number" step="0.01" min="1" name="amount_paid" class="form-input" value="<?php echo payment_form_value($payment, 'amount_paid'); ?>" required></div>
<div class="form-group"><label class="form-label">Payment Date *</label><input type="date" name="payment_date" class="form-input" value="<?php echo payment_form_value($payment, 'payment_date'); ?>" required></div>
<div class="form-group"><label class="form-label">Payment Method *</label><select name="payment_method" class="form-select" required><?php foreach (finance_payment_methods() as $value => $label) { ?><option value="<?php echo htmlspecialchars($value); ?>" <?php echo payment_form_value($payment, 'payment_method') === $value ? 'selected' : ''; ?>><?php echo htmlspecialchars($label); ?></option><?php } ?></select></div>
<div class="form-group"><label class="form-label">Transaction Reference</label><input type="text" name="transaction_ref" class="form-input" value="<?php echo payment_form_value($payment, 'transaction_ref'); ?>"></div>
<div class="form-group" style="grid-column:1/-1;"><label class="form-label">Remarks</label><input type="text" name="remarks" class="form-input" value="<?php echo payment_form_value($payment, 'remarks'); ?>"></div>
</div>
<button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Update Payment</button>
</form>
</div>
</div>
</div>
</div>
<script src="../assets/js/sidebar.js"></script>
<script src="../assets/js/common.js"></script>
</body>
</html>
