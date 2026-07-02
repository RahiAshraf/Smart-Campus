<?php

include('../config/auth.php');
include('../config/db.php');
include('../config/finance.php');

finance_ensure_tables($conn);

$error = '';

$teachers = mysqli_query($conn, "
    SELECT t.id, t.teacher_id, u.full_name, u.email
    FROM teachers t
    INNER JOIN users u ON t.user_id = u.id
    ORDER BY u.full_name ASC
");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacherId = (int) ($_POST['teacher_id'] ?? 0);
    $monthlySalary = (float) ($_POST['monthly_salary'] ?? 0);
    $effectiveFrom = mysqli_real_escape_string($conn, $_POST['effective_from'] ?? '');
    $status = mysqli_real_escape_string($conn, $_POST['status'] ?? 'active');
    $notes = mysqli_real_escape_string($conn, trim($_POST['notes'] ?? ''));
    $createdBy = (int) ($_SESSION['user']['id'] ?? 0);

    if ($teacherId <= 0 || $monthlySalary <= 0 || $effectiveFrom === '') {
        $error = 'Please select a teacher, salary amount, and effective date.';
    } elseif (!in_array($status, ['active', 'inactive'], true)) {
        $error = 'Please select a valid status.';
    } else {
        if ($status === 'active') {
            mysqli_query($conn, "UPDATE teacher_salaries SET status='inactive' WHERE teacher_id='$teacherId' AND status='active'");
        }

        $insert = mysqli_query($conn, "
            INSERT INTO teacher_salaries
            (teacher_id, monthly_salary, effective_from, status, notes, created_by)
            VALUES
            ('$teacherId', '$monthlySalary', '$effectiveFrom', '$status', '$notes', '$createdBy')
        ");

        if ($insert) {
            header('Location:index.php?success=added');
            exit;
        }

        $error = 'Failed to create salary record.';
    }
}

function old_salary_value(string $key): string
{
    return htmlspecialchars($_POST[$key] ?? '');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Teacher Salary | MindMerge SmartCampus</title>
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
<h1>Add Teacher Salary</h1>
<p>Create a monthly salary setup for a teacher.</p>
</div>
<a href="index.php" class="btn"><i class="fa-solid fa-arrow-left"></i> Back</a>
</div>

<?php if ($error !== '') { ?>
<div style="background:#fee2e2;color:#991b1b;padding:14px 18px;border-radius:14px;margin-bottom:20px;font-weight:500;"><?php echo htmlspecialchars($error); ?></div>
<?php } ?>

<div class="dashboard-section">
<form method="POST">
<div class="form-grid">
<div class="form-group">
<label class="form-label">Teacher *</label>
<select name="teacher_id" class="form-select" required>
<option value="">Select teacher</option>
<?php while ($teacher = mysqli_fetch_assoc($teachers)) { ?>
<option value="<?php echo (int) $teacher['id']; ?>" <?php echo (old_salary_value('teacher_id') === (string) $teacher['id']) ? 'selected' : ''; ?>>
<?php echo htmlspecialchars($teacher['full_name'] . ' - ' . $teacher['teacher_id'] . ' (' . $teacher['email'] . ')'); ?>
</option>
<?php } ?>
</select>
</div>

<div class="form-group">
<label class="form-label">Monthly Salary *</label>
<input type="number" step="0.01" min="1" name="monthly_salary" class="form-input" value="<?php echo old_salary_value('monthly_salary'); ?>" required>
</div>

<div class="form-group">
<label class="form-label">Effective From *</label>
<input type="date" name="effective_from" class="form-input" value="<?php echo old_salary_value('effective_from') ?: date('Y-m-d'); ?>" required>
</div>

<div class="form-group">
<label class="form-label">Status *</label>
<select name="status" class="form-select" required>
<option value="active" <?php echo old_salary_value('status') !== 'inactive' ? 'selected' : ''; ?>>Active</option>
<option value="inactive" <?php echo old_salary_value('status') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
</select>
</div>

<div class="form-group" style="grid-column:1/-1;">
<label class="form-label">Notes</label>
<textarea name="notes" class="form-textarea" placeholder="Optional salary notes"><?php echo old_salary_value('notes'); ?></textarea>
</div>
</div>

<button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Save Salary</button>
</form>
</div>

</div>
</div>
</div>
<script src="../assets/js/sidebar.js"></script>
<script src="../assets/js/common.js"></script>
</body>
</html>
