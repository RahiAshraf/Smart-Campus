<?php

session_start();

include('../config/db.php');

$error = "";
$success = "";

if(
!isset($_SESSION['reset_otp'])
||
!isset($_SESSION['reset_email'])
){

header("Location: forgot-password.php");
exit();

}

if(isset($_POST['reset'])){

$otp =
$_POST['otp'];

$new_password =
password_hash(
$_POST['password'],
PASSWORD_DEFAULT
);

if(
$otp ==
$_SESSION['reset_otp']
){

$email =
$_SESSION['reset_email'];

mysqli_query($conn,

"UPDATE users
SET password='$new_password'
WHERE email='$email'"

);

unset($_SESSION['reset_otp']);
unset($_SESSION['reset_email']);

$success =
"Password Reset Successful";

}else{

$error =
"Invalid OTP";

}

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Reset Password | MindMerge</title>

<link
rel="stylesheet"
href="../assets/css/global.css">

<link
rel="stylesheet"
href="../assets/css/components.css">

<link
rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="../../assets/css/material-theme.css">


<style>

.auth-page{
min-height:100vh;
display:flex;
justify-content:center;
align-items:center;
padding:20px;
background:
linear-gradient(
135deg,
#081028,
#172554
);
}

.auth-card{
width:100%;
max-width:460px;
background:white;
padding:38px;
border-radius:24px;
box-shadow:
0 20px 60px rgba(0,0,0,0.25);
}

.auth-card h1{
margin-bottom:10px;
}

.auth-card p{
margin-bottom:24px;
}

.success-box{
background:#dcfce7;
color:#166534;
padding:14px;
border-radius:12px;
margin-bottom:20px;
font-size:14px;
}

.error-box{
background:#fee2e2;
color:#991b1b;
padding:14px;
border-radius:12px;
margin-bottom:20px;
font-size:14px;
}

.auth-links{
margin-top:20px;
display:flex;
justify-content:center;
gap:20px;
flex-wrap:wrap;
}

.auth-links a{
text-decoration:none;
color:var(--primary);
font-weight:600;
font-size:14px;
}

</style>

</head>

<body class="auth-v2">

<div class="auth-page">

<div class="auth-card">

<h1>Reset Password</h1>

<p>
Enter OTP and create a new password
</p>

<?php if($success != ""){ ?>

<div class="success-box">

<?php echo $success; ?>

<br><br>

You can now login with your new password.

</div>

<a
href="login.php"
class="btn btn-primary"
style="width:100%;">

<i class="fa-solid fa-right-to-bracket"></i>

Go To Login

</a>

<?php }else{ ?>

<?php if($error != ""){ ?>

<div class="error-box">

<?php echo $error; ?>

</div>

<?php } ?>

<form method="POST">

<div class="form-group">

<label class="form-label">
OTP
</label>

<input
type="text"
name="otp"
class="form-input"
required>

</div>

<div class="form-group">

<label class="form-label">
New Password
</label>

<input
type="password"
name="password"
class="form-input"
required>

</div>

<button
type="submit"
name="reset"
class="btn btn-primary"
style="width:100%;">

<i class="fa-solid fa-key"></i>

Reset Password

</button>

</form>

<div class="auth-links">

<a href="login.php">

Back To Login

</a>

<a href="register.php">

Create Account

</a>

</div>

<?php } ?>

</div>

</div>

</body>
</html>
