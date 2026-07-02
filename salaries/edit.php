<?php

include('../config/auth.php');
include('../config/db.php');
include('../config/finance.php');

finance_ensure_tables($conn);

$error = '';
$salaryId = isset($_GET['id']) ? (int) $_GET['id'] : (int) ($_POST['salary_id'] ?? 0);

$salaryQuery = mysqli_query($conn, "
    SELECT ts.*, t.teacher_id AS teacher_code, u.full_name, u.email
    FROM teacher_salaries ts
    INNER JOIN teachers t ON ts.teacher_id = t.id
    INNER JOIN users u ON t.user_id = u.id
    WHERE ts.salary_id = '$salaryId'
    LIMIT 1
");

if (!$salaryQuery || mysqli_num_rows($salaryQuery) === 0) {
    header('Location:index.php?error=not_found');
    exit;
}

$salary = mysqli_fetch_assoc($salaryQuery);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $monthlySalary = (float) ($_POST['monthly_salary'] ?? 0);
    $effectiveFrom = mysqli_real_escape_string($conn, $_POST['effective_from'] ?? '');
    $status = mysqli_real_escape_string($conn, $_POST['status'] ?? 'active');
    $notes = mysqli_real_escape_string($conn, trim($_POST['notes'] ?? ''));
    $teacherId = (int) $salary['teacher_id'];

    if ($monthlySalary <= 0 || $effectiveFrom === '') {
        $error = 'Please enter salary amount and effective date.';
    } elseif (!in_array($status, ['active', 'inactive'], true)) {
        $error = 'Please select a valid status.';
    } else {
        if ($status === 'active') {
            mysqli_query($conn, "UPDATE teacher_salaries SET status='inactive' WHERE teacher_id='$teacherId' AND salary_id!='$salaryId' AND status='active'");
        }

        $updated = mysqli_query($conn, "
            UPDATE teacher_salaries
            SET monthly_salary='$monthlySalary',
                effective_from='$effectiveFrom',
                status='$status',
                notes='$notes'
            WHERE salary_id='$salaryId'
        ");

        if ($updated) {
            header('Location:view.php?id=' . $salaryId . '&success=updated');
            exit;
        }

        $error = 'Failed to update salary record.';
    }
}

function salary_form_value(array $salary, string $key): string
{
    return htmlspecialchars($_POST[$key] ?? $salary[$key] ?? '');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Teacher Salary | MindMerge SmartCampus</title>
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
<div><h1>Edit Teacher Salary</h1><p><?php echo htmlspecialchars($salary['full_name'] . ' - ' . $salary['teacher_code']); ?></p></div>
<a href="view.php?id=<?php echo $salaryId; ?>" class="btn"><i class="fa-solid fa-arrow-left"></i> Back</a>
</div>
<?php if ($error !== '') { ?><div style="background:#fee2e2;color:#991b1b;padding:14px 18px;border-radius:14px;margin-bottom:20px;font-weight:500;"><?php echo htmlspecialchars($error); ?></div><?php } ?>
<div class="dashboard-section">
<form method="POST">
<input type="hidden" name="salary_id" value="<?php echo $salaryId; ?>">
<div class="form-grid">
<div class="form-group"><label class="form-label">Teacher</label><div class="form-input"><?php echo htmlspecialchars($salary['full_name'] . ' - ' . $salary['teacher_code'] . ' (' . $salary['email'] . ')'); ?></div></div>
<div class="form-group"><label class="form-label">Monthly Salary *</label><input type="number" step="0.01" min="1" name="monthly_salary" class="form-input" value="<?php echo salary_form_value($salary, 'monthly_salary'); ?>" required></div>
<div class="form-group"><label class="form-label">Effective From *</label><input type="date" name="effective_from" class="form-input" value="<?php echo salary_form_value($salary, 'effective_from'); ?>" required></div>
<div class="form-group"><label class="form-label">Status *</label><select name="status" class="form-select" required><option value="active" <?php echo salary_form_value($salary, 'status') === 'active' ? 'selected' : ''; ?>>Active</option><option value="inactive" <?php echo salary_form_value($salary, 'status') === 'inactive' ? 'selected' : ''; ?>>Inactive</option></select></div>
<div class="form-group" style="grid-column:1/-1;"><label class="form-label">Notes</label><textarea name="notes" class="form-textarea"><?php echo salary_form_value($salary, 'notes'); ?></textarea></div>
</div>
<button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Update Salary</button>
</form>
</div>
</div>
</div>
</div>
<script src="../assets/js/sidebar.js"></script>
<script src="../assets/js/common.js"></script>
</body>
</html>
