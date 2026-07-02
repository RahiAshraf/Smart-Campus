<?php

session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/mail.php';
require_once __DIR__ . '/../shared/helpers/portal.php';

if (isset($_SESSION['user'])) {
    portal_redirect_home();
}

$error = '';
$classes = [];
$sections = [];

$class_query = mysqli_query(
    $conn,
    "SELECT class_id, class_name
     FROM classes
     WHERE status = 'active'
     ORDER BY class_name ASC"
);

while ($class_query && $row = mysqli_fetch_assoc($class_query)) {
    $classes[] = $row;
}

$section_query = mysqli_query(
    $conn,
    "SELECT s.section_id, s.class_id, s.section_code, s.section_name, c.class_name
     FROM sections s
     INNER JOIN classes c ON c.class_id = s.class_id
     WHERE s.status = 'active'
     ORDER BY c.class_name, s.section_name"
);

while ($section_query && $row = mysqli_fetch_assoc($section_query)) {
    $sections[] = $row;
}

if (isset($_POST['send_otp'])) {
    $role = strtolower(trim($_POST['role'] ?? ''));
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (!in_array($role, ['teacher', 'student', 'parent'], true)) {
        $error = 'Please select a valid role.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must contain at least 8 characters.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        $email_escaped = mysqli_real_escape_string($conn, $email);
        $existing = mysqli_query(
            $conn,
            "SELECT id FROM users WHERE email = '$email_escaped' LIMIT 1"
        );

        if ($existing && mysqli_num_rows($existing) > 0) {
            $error = 'An account already exists with this email.';
        } else {
            $otp = random_int(100000, 999999);
            $_SESSION['register_data'] = $_POST;
            $_SESSION['register_otp'] = $otp;

            $message = "
                <h2>MindMerge OTP Verification</h2>
                <p>Your verification code is:</p>
                <h1>$otp</h1>
                <p>Do not share this code.</p>
            ";

            if (sendMail($email, 'MindMerge OTP Verification', $message)) {
                header('Location: verify-otp.php');
                exit();
            }

            $error = 'Unable to send the verification code. Please try again.';
        }
    }
}

function register_value(string $key): string
{
    return htmlspecialchars($_POST[$key] ?? '');
}

function register_selected(string $key, string $value): string
{
    return (($_POST[$key] ?? '') === $value) ? 'selected' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register | MindMerge SmartCampus</title>

<script>
if(localStorage.getItem("mindmerge-theme") === "dark"){
    document.documentElement.classList.add("dark-mode");
}
</script>

<link rel="stylesheet" href="../assets/css/global.css">
<link rel="stylesheet" href="../assets/css/components.css">
<link rel="stylesheet" href="../assets/css/auth.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</head>

<style>
body.dark-mode {
    background: #020817;
    color: #f8fafc;
}

.auth-theme-btn{
    position:fixed;
    top:20px;
    right:20px;
    z-index:999;

    width:44px;
    height:44px;
    border:none;
    border-radius:50%;

    background:#ffffff;
       color:#111827;

    box-shadow:0 10px 25px rgba(0,0,0,.12);
    cursor:pointer;
}

body.dark-mode .auth-theme-btn{
    background:#1e293b;
    color:#f8fafc;
}

html.dark-mode body,
body.dark-mode{
    background:#020817 !important;
    color:#f8fafc !important;
}

html.dark-mode .auth-register-shell{
    background:#020817 !important;
}

html.dark-mode .auth-register-card{
    background:#071126 !important;
    border:1px solid #334155 !important;
}

html.dark-mode .auth-brand strong,
html.dark-mode .auth-register-header h1,
html.dark-mode .auth-section-title h2,
html.dark-mode .auth-field label{
    color:#f8fafc !important;
}

html.dark-mode .auth-brand small,
html.dark-mode .auth-register-header p,
html.dark-mode .auth-section-title p,
html.dark-mode .auth-register-actions p,
html.dark-mode .auth-password-rules li,
html.dark-mode .auth-field-help{
    color:#94a3b8 !important;
}

html.dark-mode .auth-input-wrap,
html.dark-mode .auth-input-wrap input,
html.dark-mode .auth-input-wrap select,
html.dark-mode .auth-input-wrap textarea{
    background:#111c31 !important;
    border-color:#334155 !important;
    color:#f8fafc !important;
}

html.dark-mode .auth-input-wrap input::placeholder,
html.dark-mode .auth-input-wrap textarea::placeholder{
    color:#94a3b8 !important;
}

html.dark-mode .auth-step-line{
    background:#334155 !important;
}
</style>

<body class="auth-v2">


<a href="/SmartCampus/index.php" class="back-landing-btn">
    <i class="fa-solid fa-arrow-left"></i>
    Back to Home Page
</a>

<button type="button" id="themeToggle" class="auth-theme-btn">
    <i class="fa-solid fa-moon"></i>
</button>

<main class="auth-register-shell">
<section class="auth-register-card">
<header class="auth-register-header">
<a href="../index.php" class="auth-brand">
<span class="auth-brand-mark"><i class="fa-solid fa-graduation-cap"></i></span>
<span><strong>MindMerge</strong><small>SmartCampus</small></span>
</a>

<div class="auth-steps">
<div class="auth-step is-active">
<span class="auth-step-num">1</span>
<span>Details</span>
</div>
<span class="auth-step-line"></span>
<div class="auth-step">
<span class="auth-step-num">2</span>
<span>Verify Email</span>
</div>
<span class="auth-step-line"></span>
<div class="auth-step">
<span class="auth-step-num">3</span>
<span>Done</span>
</div>
</div>

<div>
<h1>Create Account</h1>
<p>Join MindMerge SmartCampus today.</p>
</div>
</header>

<?php if ($error !== '') { ?>
<div class="auth-alert error"><i class="fa-solid fa-triangle-exclamation"></i><span><?php echo htmlspecialchars($error); ?></span></div>
<?php } ?>

<form method="POST" class="auth-register-form" id="registerForm">
<div class="auth-form-section">
<div class="auth-section-title"><span>1</span><div><h2>Account details</h2><p>Basic information used to sign in.</p></div></div>
<div class="auth-register-grid">

<div class="auth-field">
<label for="fullName">Full name</label>
<div class="auth-input-wrap">
<i class="fa-solid fa-user"></i>
<input id="fullName" type="text" name="full_name" value="<?php echo register_value('full_name'); ?>" placeholder="Enter your full name" autocomplete="name" required>
</div>
</div>

<div class="auth-field">
<label for="roleSelect">Select Role</label>
<div class="auth-input-wrap">
<i class="fa-solid fa-id-badge"></i>
<select id="roleSelect" name="role" required>
<option value="">— Choose your role —</option>
<option value="teacher" <?php echo register_selected('role', 'teacher'); ?>>Teacher</option>
<option value="student" <?php echo register_selected('role', 'student'); ?>>Student</option>
<option value="parent" <?php echo register_selected('role', 'parent'); ?>>Parent</option>
</select>
</div>
</div>

<div class="auth-field">
<label for="email">Email address</label>
<div class="auth-input-wrap">
<i class="fa-solid fa-at"></i>
<input id="email" type="email" name="email" value="<?php echo register_value('email'); ?>" placeholder="your@email.com" autocomplete="email" required>
</div>
</div>

<div class="auth-field">
<label for="phone">Phone number</label>
<div class="auth-input-wrap">
<i class="fa-solid fa-phone"></i>
<input id="phone" type="tel" name="phone" value="<?php echo register_value('phone'); ?>" placeholder="+91 98765 43210" autocomplete="tel" required>
</div>
</div>

</div>
</div>

<div class="auth-form-section auth-role-section" id="studentFields">
<div class="auth-section-title"><span>2</span><div><h2>Student details</h2><p>Class and personal information.</p></div></div>
<div class="auth-register-grid">
<div class="auth-field">
<label for="studentClass">Class</label>
<div class="auth-input-wrap">
<i class="fa-solid fa-school"></i>
<select id="studentClass" name="class_id">
<option value="">Select class</option>
<?php foreach ($classes as $class) { ?>
<option value="<?php echo (int) $class['class_id']; ?>" <?php echo register_selected('class_id', (string) $class['class_id']); ?>><?php echo htmlspecialchars($class['class_name']); ?></option>
<?php } ?>
</select>
</div>
</div>
<div class="auth-field">
<label for="studentSection">Section</label>
<div class="auth-input-wrap">
<i class="fa-solid fa-layer-group"></i>
<select id="studentSection" name="section_id">
<option value="">Select section</option>
<?php foreach ($sections as $section) { ?>
<option data-class="<?php echo (int) $section['class_id']; ?>" value="<?php echo (int) $section['section_id']; ?>" <?php echo register_selected('section_id', (string) $section['section_id']); ?>><?php echo htmlspecialchars($section['section_code'] . ' - ' . $section['section_name']); ?></option>
<?php } ?>
</select>
</div>
</div>
<div class="auth-field">
<label for="dob">Date of birth</label>
<div class="auth-input-wrap">
<i class="fa-solid fa-cake-candles"></i>
<input id="dob" type="date" name="dob" value="<?php echo register_value('dob'); ?>">
</div>
</div>
<div class="auth-field">
<label for="gender">Gender</label>
<div class="auth-input-wrap">
<i class="fa-solid fa-venus-mars"></i>
<select id="gender" name="gender">
<option value="">Select gender</option>
<option value="Male" <?php echo register_selected('gender', 'Male'); ?>>Male</option>
<option value="Female" <?php echo register_selected('gender', 'Female'); ?>>Female</option>
<option value="Other" <?php echo register_selected('gender', 'Other'); ?>>Other</option>
</select>
</div>
</div>
<div class="auth-field">
<label for="parentPhone">Parent phone</label>
<div class="auth-input-wrap">
<i class="fa-solid fa-phone-volume"></i>
<input id="parentPhone" type="tel" name="parent_phone" value="<?php echo register_value('parent_phone'); ?>" placeholder="Parent or guardian phone">
</div>
</div>
<div class="auth-field auth-field-wide">
<label for="address">Address</label>
<div class="auth-input-wrap">
<i class="fa-solid fa-location-dot"></i>
<textarea id="address" name="address" placeholder="Home address"><?php echo register_value('address'); ?></textarea>
</div>
</div>
</div>
</div>

<div class="auth-form-section auth-role-section" id="teacherFields">
<div class="auth-section-title"><span>2</span><div><h2>Teacher details</h2><p>Academic assignment information.</p></div></div>
<div class="auth-register-grid">
<div class="auth-field auth-field-wide">
<label for="qualification">Qualification</label>
<div class="auth-input-wrap">
<i class="fa-solid fa-graduation-cap"></i>
<input id="qualification" type="text" name="qualification" value="<?php echo register_value('qualification'); ?>" placeholder="Highest qualification">
</div>
</div>
</div>
</div>

<div class="auth-form-section auth-role-section" id="parentFields">
<div class="auth-section-title"><span>2</span><div><h2>Parent details</h2><p>Connect this account to a student.</p></div></div>
<div class="auth-register-grid">
<div class="auth-field">
<label for="studentId">Student ID</label>
<div class="auth-input-wrap">
<i class="fa-solid fa-user-graduate"></i>
<input id="studentId" type="text" name="student_id" value="<?php echo register_value('student_id'); ?>" placeholder="Student ID">
</div>
</div>
<div class="auth-field">
<label for="relationship">Relationship</label>
<div class="auth-input-wrap">
<i class="fa-solid fa-people-roof"></i>
<input id="relationship" type="text" name="relationship_name" value="<?php echo register_value('relationship_name'); ?>" placeholder="Father, mother, or guardian">
</div>
</div>
</div>
</div>

<div class="auth-form-section">
<div class="auth-section-title"><span id="securityStep">2</span><div><h2>Security</h2><p>Create a password for your account.</p></div></div>
<div class="auth-field auth-field-full">
<label for="password">Password</label>
<div class="auth-input-wrap">
<i class="fa-solid fa-key"></i>
<input id="password" type="password" name="password" placeholder="Create a strong password" autocomplete="new-password" required>
<button type="button" class="auth-eye" data-password-toggle="password" aria-label="Show password"><i class="fa-solid fa-eye"></i></button>
</div>
<ul class="auth-password-rules" id="passwordRules">
<li data-rule="length"><i class="fa-solid fa-circle"></i> At least 8 characters</li>
<li data-rule="upper"><i class="fa-solid fa-circle"></i> At least one uppercase letter (A–Z)</li>
<li data-rule="number"><i class="fa-solid fa-circle"></i> At least one number (0–9)</li>
<li data-rule="symbol"><i class="fa-solid fa-circle"></i> At least one symbol (!@#$...)</li>
</ul>
</div>
<div class="auth-field auth-field-full">
<label for="confirmPassword">Confirm password</label>
<div class="auth-input-wrap">
<i class="fa-solid fa-lock"></i>
<input id="confirmPassword" type="password" name="confirm_password" placeholder="Re-enter your password" autocomplete="new-password" required>
<button type="button" class="auth-eye" data-password-toggle="confirmPassword" aria-label="Show password"><i class="fa-solid fa-eye"></i></button>
</div>
<small class="auth-field-help" id="confirmHelp"></small>
</div>
</div>

<div class="auth-register-actions">
<button type="submit" name="send_otp" class="auth-primary-btn"><i class="fa-solid fa-paper-plane"></i> Send Verification Code</button>
<p>Already registered? <a href="login.php">Sign in</a></p>
</div>
</form>
</section>
</main>

<script src="../assets/js/auth.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const roleSelect = document.getElementById('roleSelect');
    const roleSections = document.querySelectorAll('.auth-role-section');
    const securityStep = document.getElementById('securityStep');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirmPassword');

    function updateRoleFields() {
        roleSections.forEach(section => section.classList.remove('active'));
        const target = document.getElementById(roleSelect.value + 'Fields');
        if (target) {
            target.classList.add('active');
            securityStep.textContent = '3';
        } else {
            securityStep.textContent = '2';
        }
    }

    function filterSections(classSelectId, sectionSelectId) {
        const classSelect = document.getElementById(classSelectId);
        const sectionSelect = document.getElementById(sectionSelectId);
        if (!classSelect || !sectionSelect) return;

        const apply = function () {
            const selectedClass = classSelect.value;
            Array.from(sectionSelect.options).forEach((option, index) => {
                if (index === 0) return;
                option.hidden = selectedClass !== '' && option.dataset.class !== selectedClass;
                if (option.hidden && option.selected) sectionSelect.value = '';
            });
        };

        classSelect.addEventListener('change', apply);
        apply();
    }

    function updatePasswordState() {
        const value = password.value;
        const rules = {
            length: value.length >= 8,
            upper: /[A-Z]/.test(value),
            number: /[0-9]/.test(value),
            symbol: /[^A-Za-z0-9]/.test(value)
        };

        Object.keys(rules).forEach(function (key) {
            const item = document.querySelector('#passwordRules [data-rule="' + key + '"]');
            if (!item) return;
            item.classList.toggle('valid', rules[key]);
            const icon = item.querySelector('i');
            if (icon) {
                icon.className = rules[key] ? 'fa-solid fa-circle-check' : 'fa-solid fa-circle';
            }
        });

        updatePasswordMatch();
    }

    function updatePasswordMatch() {
        const help = document.getElementById('confirmHelp');
        if (!confirmPassword.value) {
            help.textContent = '';
            return;
        }
        const matches = confirmPassword.value === password.value;
        help.textContent = matches ? 'Passwords match.' : 'Passwords do not match.';
        help.className = 'auth-field-help ' + (matches ? 'valid' : 'invalid');
    }

    roleSelect.addEventListener('change', updateRoleFields);
    password.addEventListener('input', updatePasswordState);
    confirmPassword.addEventListener('input', updatePasswordMatch);
    filterSections('studentClass', 'studentSection');
    updateRoleFields();
});
</script>

<script src="../assets/js/common.js"></script>

</body>
</html>
