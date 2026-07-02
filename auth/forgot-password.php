<?php

session_start();

include('../config/db.php');
include('../config/mail.php');

$error = "";

if(isset($_POST['send_otp'])){

$email =
mysqli_real_escape_string(
$conn,
$_POST['email']
);

$check =
mysqli_query(
$conn,
"SELECT * FROM users
WHERE email='$email'"
);

if(mysqli_num_rows($check) > 0){

$otp =
rand(100000,999999);

$_SESSION['reset_email'] =
$email;

$_SESSION['reset_otp'] =
$otp;

$message = "

<h2>Password Reset OTP</h2>

<p>Your OTP code is:</p>

<h1>$otp</h1>

";

if(
sendMail(
$email,
"MindMerge Password Reset",
$message
)
){

header(
"Location: reset-password.php"
);

exit();

}else{

$error =
"Failed to send OTP";

}

}else{

$error =
"Email not found";

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

<title>Forgot Password | MindMerge</title>

<link rel="stylesheet" href="../assets/css/global.css">

<link  rel="stylesheet" href="../assets/css/components.css">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link
rel="stylesheet"
href="../assets/css/material-theme.css">
<style>

.auth-page{
min-height:100vh;
display:flex;
justify-content:center;
align-items:center;
padding:20px;
background:#fff;
}

.auth-card{
width:100%;
max-width:460px;
background:white;
padding:38px;
box-shadow:
0 20px 60px rgba(0,0,0,0.25);
}

.auth-logo{
    display:flex;
    align-items:center;
    justify-content:center;
    gap:16px;
    margin-bottom:28px;
}

.auth-logo i{
    width:58px;
    height:58px;

    display:flex;
    align-items:center;
    justify-content:center;

    background:#7A00FF;
    color:#fff;

    border-radius:2px;

    font-size:24px;

    box-shadow:0 10px 25px rgba(122,0,255,.25);
}

.auth-logo div{
    display:flex;
    flex-direction:column;
    line-height:1.1;
}

.auth-logo h2{
    margin-top:24px;
    font-size:1.8rem;
    font-weight:700;
    justify-content: center;
    color:#111827;
}

.auth-logo p{
    margin:4px 0 0;
    font-size:.78rem;
    font-weight:700;
    letter-spacing:3px;
    text-transform:uppercase;
    color:#6b7280;
}
.auth-card h1{
margin-bottom:10px;

    display:flex;
    align-items:center;
    justify-content:center;

}

.auth-card p{
margin-bottom:24px;
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
justify-content:space-between;
gap:12px;
flex-wrap:wrap;
}

.auth-links a{
text-decoration:none;
color: #7a00ff;
font-weight:600;
font-size:14px;
}

.input-icon{
    position:relative;
}

.input-icon i{
    position:absolute;
    left:16px;
    top:50%;
    transform:translateY(-50%);

    color:#7A00FF;
    font-size:16px;

    pointer-events:none;
}

.input-icon .form-input{
    padding-left:48px !important;
}
html.dark-mode body,
body.dark-mode{
    background:#020817 !important;
    color:#f8fafc !important;
}

html.dark-mode .auth-page{
    background:#020817 !important;
}

html.dark-mode .auth-card{
    background:#071126 !important;
    color:#f8fafc !important;
    border:1px solid #334155;
}

html.dark-mode .auth-card h1,
html.dark-mode .auth-logo h2,
html.dark-mode .auth-card p,
html.dark-mode .form-label{
    color:#f8fafc !important;
}

html.dark-mode .form-input{
    background:#111c31 !important;
    border-color:#334155 !important;
    color:#f8fafc !important;
}

html.dark-mode .form-input::placeholder{
    color:#94a3b8 !important;
}

</style>

</head>

<body class="auth-v2">

<div class="auth-page">

<div class="auth-card">

<div class="auth-logo">
    <i class="fa-solid fa-graduation-cap"></i>
    <div>
        <h2>MindMerge</h2>
        <p>SmartCampus</p>
    </div>
</div>

<h1>Forgot Password</h1>

<p>
Enter your registered email
to receive OTP verification.
</p>

<?php if($error != ""){ ?>

<div class="error-box">

<?php echo $error; ?>

</div>

<?php } ?>

<form method="POST">

<div class="form-group">

    <label class="form-label">Email Address</label>

    <div class="input-icon">
        <i class="fa-solid fa-envelope"></i>

        <input
            type="email"
            name="email"
            class="form-input"
            placeholder="Enter your email"
            required>
    </div>

</div>

<button
type="submit"
name="send_otp"
class="btn btn-primary"
style="width:100%;">

<i class="fa-solid fa-envelope"></i>

Send OTP

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

</div>

</div>
<script>
if(localStorage.getItem("mindmerge-theme") === "dark"){
    document.documentElement.classList.add("dark-mode");
}
</script>
</body>
</html>
