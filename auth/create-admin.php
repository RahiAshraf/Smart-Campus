<?php

session_start();

require_once __DIR__ . '/../config/db.php';

$error = '';
$success = '';
$currentRole = strtolower($_SESSION['user']['role'] ?? '');

function admin_count(mysqli $conn): int
{
    $result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role = 'admin'");
    $row = $result ? mysqli_fetch_assoc($result) : ['total' => 0];
    return (int) ($row['total'] ?? 0);
}

function generate_admin_id(mysqli $conn): string
{
    $next = admin_count($conn) + 1;
    return 'ADM' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
}

function old_value(string $key): string
{
    return htmlspecialchars($_POST[$key] ?? '');
}

$hasAdmin = admin_count($conn) > 0;
$canCreateAdmin = !$hasAdmin || $currentRole === 'admin';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$canCreateAdmin) {
        $error = 'Admin creation is restricted. Please sign in as an administrator.';
    } else {
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($fullName === '') {
            $error = 'Please enter the admin full name.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid admin email address.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must contain at least 8 characters.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match.';
        } else {
            $existingStmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? LIMIT 1");
            mysqli_stmt_bind_param($existingStmt, 's', $email);
            mysqli_stmt_execute($existingStmt);
            mysqli_stmt_store_result($existingStmt);

            if (mysqli_stmt_num_rows($existingStmt) > 0) {
                $error = 'An account already exists with this email.';
            }

            mysqli_stmt_close($existingStmt);
        }

        if ($error === '') {
            $adminId = generate_admin_id($conn);
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $role = 'admin';
            $phone = $phone !== '' ? $phone : '-';

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO users (full_name, email, phone, password, role, admin_id) VALUES (?, ?, ?, ?, ?, ?)"
            );

            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'ssssss', $fullName, $email, $phone, $hashedPassword, $role, $adminId);

                if (mysqli_stmt_execute($stmt)) {
                    $success = 'Admin account created successfully. You can now sign in from Admin Login.';
                    $_POST = [];
                    $hasAdmin = true;
                    $canCreateAdmin = $currentRole === 'admin';
                } else {
                    $error = 'Unable to create admin account. Please try again.';
                }

                mysqli_stmt_close($stmt);
            } else {
                $error = 'Unable to prepare admin account creation.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Admin | MindMerge SmartCampus</title>
<link rel="stylesheet" href="../assets/css/global.css">
<link rel="stylesheet" href="../assets/css/components.css">
<link rel="stylesheet" href="../assets/css/auth.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="../../assets/css/material-theme.css">

</head>
<body class="auth-v2 auth-admin">
<main class="auth-shell auth-compact">
<section class="auth-panel">
<a href="../index.php" class="auth-brand">
<span class="auth-brand-mark"><i class="fa-solid fa-user-shield"></i></span>
<span><strong>MindMerge</strong><small>Admin Setup</small></span>
</a>

<div class="auth-heading">
<h1>Create admin</h1>
<p>Create an administrator account with an email and password.</p>
</div>

<?php if ($error !== '') { ?>
<div class="auth-alert error"><i class="fa-solid fa-triangle-exclamation"></i><span><?php echo htmlspecialchars($error); ?></span></div>
<?php } ?>

<?php if ($success !== '') { ?>
<div class="auth-alert success"><i class="fa-solid fa-circle-check"></i><span><?php echo htmlspecialchars($success); ?></span></div>
<?php } ?>

<?php if (!$canCreateAdmin) { ?>
<div class="auth-alert error"><i class="fa-solid fa-lock"></i><span>An admin account already exists. Sign in as admin before creating another administrator.</span></div>
<p class="auth-admin-link"><a href="admin-login.php"><i class="fa-solid fa-shield-halved"></i> Admin Login</a></p>
<?php } else { ?>
<form method="POST" class="auth-form">
<div class="auth-field">
<label for="fullName">Full name</label>
<div class="auth-input-wrap">
<i class="fa-solid fa-user"></i>
<input id="fullName" type="text" name="full_name" value="<?php echo old_value('full_name'); ?>" placeholder="Admin full name" autocomplete="name" required>
</div>
</div>

<div class="auth-field">
<label for="email">Admin email</label>
<div class="auth-input-wrap">
<i class="fa-solid fa-at"></i>
<input id="email" type="email" name="email" value="<?php echo old_value('email'); ?>" placeholder="admin@school.edu" autocomplete="username" required>
</div>
</div>

<div class="auth-field">
<label for="phone">Phone number</label>
<div class="auth-input-wrap">
<i class="fa-solid fa-phone"></i>
<input id="phone" type="tel" name="phone" value="<?php echo old_value('phone'); ?>" placeholder="+91 98765 43210" autocomplete="tel">
</div>
</div>

<div class="auth-field">
<label for="password">Password</label>
<div class="auth-input-wrap">
<i class="fa-solid fa-key"></i>
<input id="password" type="password" name="password" placeholder="Create admin password" autocomplete="new-password" required>
<button type="button" class="auth-eye" data-password-toggle="password" aria-label="Show password"><i class="fa-solid fa-eye"></i></button>
</div>
</div>

<div class="auth-field">
<label for="confirmPassword">Confirm password</label>
<div class="auth-input-wrap">
<i class="fa-solid fa-lock"></i>
<input id="confirmPassword" type="password" name="confirm_password" placeholder="Re-enter password" autocomplete="new-password" required>
<button type="button" class="auth-eye" data-password-toggle="confirmPassword" aria-label="Show password"><i class="fa-solid fa-eye"></i></button>
</div>
</div>

<button type="submit" class="auth-primary-btn"><i class="fa-solid fa-user-shield"></i> Create Admin</button>
</form>

<p class="auth-admin-link"><a href="admin-login.php"><i class="fa-solid fa-arrow-left"></i> Admin Login</a></p>
<?php } ?>
</section>
</main>
<script src="../assets/js/auth.js"></script>
</body>
</html>
