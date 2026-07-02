<?php

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';

requirePermission('students', 'edit');

$error = '';
$success = '';

function nextParentId(mysqli $conn): string
{
    $row = mysqli_fetch_assoc(mysqli_query(
        $conn,
        "SELECT parent_id FROM parents
         WHERE parent_id IS NOT NULL AND parent_id != ''
         ORDER BY id DESC
         LIMIT 1"
    ));

    if (!$row) {
        return 'PAR0001';
    }

    $number = (int) substr($row['parent_id'], 3);
    return 'PAR' . str_pad($number + 1, 4, '0', STR_PAD_LEFT);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $parent_user_id = (int) ($_POST['parent_user_id'] ?? 0);
    $student_code = trim($_POST['student_id'] ?? '');
    $relationship = trim($_POST['relationship_name'] ?? '');

    if ($parent_user_id <= 0 || $student_code === '') {
        $error = 'Select a parent and student.';
    } else {
        $student_code_esc = mysqli_real_escape_string($conn, $student_code);
        $relationship_esc = mysqli_real_escape_string($conn, $relationship);
        $existing = mysqli_fetch_assoc(mysqli_query(
            $conn,
            "SELECT id FROM parents
             WHERE user_id = '$parent_user_id'
               AND student_id = '$student_code_esc'
             LIMIT 1"
        ));

        if ($existing) {
            $error = 'This student is already assigned to the selected parent.';
        } else {
            $parent_id = nextParentId($conn);
            mysqli_query(
                $conn,
                "INSERT INTO parents (user_id, parent_id, student_id, relationship_name)
                 VALUES ('$parent_user_id', '$parent_id', '$student_code_esc', '$relationship_esc')"
            );
            $success = 'Student assigned to parent successfully.';
        }
    }
}

$parents = mysqli_query(
    $conn,
    "SELECT id, full_name, email, phone
     FROM users
     WHERE role = 'parent'
     ORDER BY full_name ASC"
);

$students = mysqli_query(
    $conn,
    "SELECT st.student_id, u.full_name, c.class_name, s.section_name
     FROM students st
     INNER JOIN users u ON u.id = st.user_id
     LEFT JOIN classes c ON c.class_id = st.class_id
     LEFT JOIN sections s ON s.section_id = st.section_id
     ORDER BY u.full_name ASC"
);

$assignments = mysqli_query(
    $conn,
    "SELECT p.id, p.parent_id, p.student_id, p.relationship_name,
            pu.full_name AS parent_name,
            su.full_name AS student_name
     FROM parents p
     INNER JOIN users pu ON pu.id = p.user_id
     LEFT JOIN students st ON st.student_id = p.student_id
     LEFT JOIN users su ON su.id = st.user_id
     ORDER BY pu.full_name ASC, su.full_name ASC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Assign Students to Parents | MindMerge</title>
<link rel="stylesheet" href="../assets/css/global.css">
<link rel="stylesheet" href="../assets/css/layout.css">
<link rel="stylesheet" href="../assets/css/components.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="../../assets/css/material-theme.css">
</head>
<body>
<div class="app-layout">
<?php include __DIR__ . '/../partials/sidebar.php'; ?>
<div class="main-content">
<?php include __DIR__ . '/../partials/topbar.php'; ?>
<div class="page-content">
<div class="page-header">
<div><h1>Assign Students to Parents</h1><p>Link one parent account with one or more enrolled students.</p></div>
<a href="index.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back to Students</a>
</div>

<?php if ($success !== '') { ?><div class="alert alert-success"><i class="fa-solid fa-circle-check"></i><?php echo htmlspecialchars($success); ?></div><?php } ?>
<?php if ($error !== '') { ?><div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation"></i><?php echo htmlspecialchars($error); ?></div><?php } ?>

<form method="POST" class="dashboard-section">
<div class="section-header"><h2>New Parent Link</h2></div>
<div class="form-grid">
<div class="form-group">
<label class="form-label">Parent Account</label>
<select name="parent_user_id" class="form-select" required>
<option value="">Select parent</option>
<?php while ($parent = mysqli_fetch_assoc($parents)) { ?>
<option value="<?php echo (int) $parent['id']; ?>"><?php echo htmlspecialchars($parent['full_name'] . ' - ' . $parent['email']); ?></option>
<?php } ?>
</select>
</div>
<div class="form-group">
<label class="form-label">Student</label>
<select name="student_id" class="form-select" required>
<option value="">Select student</option>
<?php while ($student = mysqli_fetch_assoc($students)) { ?>
<option value="<?php echo htmlspecialchars($student['student_id']); ?>"><?php echo htmlspecialchars($student['full_name'] . ' (' . $student['student_id'] . ') - ' . ($student['class_name'] ?? '') . ' ' . ($student['section_name'] ?? '')); ?></option>
<?php } ?>
</select>
</div>
<div class="form-group">
<label class="form-label">Relationship</label>
<input type="text" name="relationship_name" class="form-input" placeholder="Father / Mother / Guardian">
</div>
</div>
<button type="submit" class="btn btn-primary"><i class="fa-solid fa-link"></i> Assign Student</button>
</form>

<div class="dashboard-section">
<div class="section-header"><h2>Existing Parent Links</h2></div>
<div class="table-responsive">
<table class="custom-table">
<thead><tr><th>Parent</th><th>Student</th><th>Student ID</th><th>Relationship</th></tr></thead>
<tbody>
<?php if (!$assignments || mysqli_num_rows($assignments) === 0) { ?>
<tr><td colspan="4"><div class="empty-state"><i class="fa-solid fa-people-roof"></i><h3>No links yet</h3><p>Assignments will appear here.</p></div></td></tr>
<?php } else { while ($assignment = mysqli_fetch_assoc($assignments)) { ?>
<tr>
<td><?php echo htmlspecialchars($assignment['parent_name']); ?></td>
<td><?php echo htmlspecialchars($assignment['student_name'] ?? '-'); ?></td>
<td><?php echo htmlspecialchars($assignment['student_id']); ?></td>
<td><?php echo htmlspecialchars($assignment['relationship_name'] ?? '-'); ?></td>
</tr>
<?php } } ?>
</tbody>
</table>
</div>
</div>
</div>
</div>
</div>
<script src="../assets/js/common.js"></script>
</body>
</html>
