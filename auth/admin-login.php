<?php

session_start();

require_once __DIR__ . '/authenticate.php';

if (isset($_SESSION['user'])) {
    portal_redirect_home();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $attempt = auth_attempt_login(
        $conn,
        $_POST['email'] ?? '',
        $_POST['password'] ?? '',
        'admin'
    );

    if ($attempt['success']) {
        header('Location: ' . $attempt['redirect']);
        exit();
    }

    $error = $attempt['error'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login | MindMerge SmartCampus</title>

<script>
if(localStorage.getItem("mindmerge-theme") === "dark"){
    document.documentElement.classList.add("dark-mode");
}
</script>
<link rel="stylesheet" href="../assets/css/global.css">
<link rel="stylesheet" href="../assets/css/components.css">
<link rel="stylesheet" href="../assets/css/auth.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="assets/css/material-theme.css">

</head>
<body class="auth-v2 auth-admin">
<main class="auth-shell auth-compact">
<section class="auth-panel">
<a href="../index.php" class="auth-brand">
<span class="auth-brand-mark"><i class="fa-solid fa-shield-halved"></i></span>
<span><strong>MindMerge</strong><small>Administration</small></span>
</a>

<div class="auth-heading">
<h1>Admin sign in</h1>
<p>Sign in with an administrator account.</p>
</div>

<?php if ($error !== '') { ?>
<div class="auth-alert error"><i class="fa-solid fa-triangle-exclamation"></i><span><?php echo htmlspecialchars($error); ?></span></div>
<?php } ?>

<form method="POST" class="auth-form">
<div class="auth-field">
<label for="email">Admin email</label>
<div class="auth-input-wrap">
<i class="fa-solid fa-at"></i>
<input id="email" type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" placeholder="admin@school.edu" autocomplete="username" required>
</div>
</div>

<div class="auth-field">
<label for="password">Password</label>
<div class="auth-input-wrap">
<i class="fa-solid fa-key"></i>
<input id="password" type="password" name="password" placeholder="Enter your password" autocomplete="current-password" required>
<button type="button" class="auth-eye" data-password-toggle="password" aria-label="Show password"><i class="fa-solid fa-eye"></i></button>
</div>
</div>

<div class="auth-meta">
<a href="forgot-password.php">Forgot password?</a>
</div>

<button type="submit" class="auth-primary-btn"><i class="fa-solid fa-shield"></i> Sign In</button>
</form>

<p class="auth-admin-link"><a href="login.php"><i class="fa-solid fa-arrow-left"></i> User Login</a></p>
</section>
</main>
<script src="../assets/js/auth.js"></script>
</body>
</html>
