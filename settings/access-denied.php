<?php

session_start();

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/permissions.php';

$is_authenticated = isset($_SESSION['user']);
$dashboard_url = $is_authenticated
    ? permission_role_dashboard_url()
    : BASE_URL . 'auth/login.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Access Denied | MindMerge SmartCampus</title>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/global.css">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/layout.css">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/components.css">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/portals.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="../../assets/css/material-theme.css">
<style>

.access-denied-content{
display:flex;
justify-content:center;
align-items:center;
min-height:calc(100vh - 120px);
padding:40px 20px;
}

.access-denied-page{
min-height:100vh;
display:flex;
justify-content:center;
align-items:center;
padding:40px 20px;
background:var(--bg);
}

.access-denied-card{
max-width:700px;
width:100%;
background:var(--card);
border-radius:24px;
padding:48px;
text-align:center;
box-shadow:var(--shadow);
border:1px solid rgba(148,163,184,.15);
}

.access-denied-code{
font-size:72px;
font-weight:800;
line-height:1;
color:#ef4444;
margin-bottom:12px;
}

.access-denied-icon{
width:90px;
height:90px;
margin:0 auto 20px;
border-radius:50%;
display:flex;
align-items:center;
justify-content:center;
background:rgba(239,68,68,.08);
color:#ef4444;
font-size:38px;
}

.access-denied-eyebrow{
display:inline-block;
padding:8px 14px;
border-radius:999px;
background:rgba(239,68,68,.08);
color:#ef4444;
font-size:13px;
font-weight:600;
margin-bottom:18px;
}

.access-denied-card h1{
font-size:32px;
margin-bottom:16px;
}

.access-denied-card p{
max-width:550px;
margin:0 auto 24px;
line-height:1.7;
color:var(--text-light);
}

.access-denied-actions{
display:flex;
justify-content:center;
gap:12px;
flex-wrap:wrap;
margin-top:24px;
}

.access-denied-help{
margin-top:24px;
padding:16px;
border-radius:14px;
background:rgba(59,130,246,.08);
color:var(--text);
display:flex;
align-items:flex-start;
justify-content:center;
gap:10px;
line-height:1.6;
}

.access-denied-help i{
margin-top:2px;
color:#3b82f6;
}

@media(max-width:768px){

.access-denied-card{
padding:30px 20px;
}

.access-denied-code{
font-size:56px;
}

.access-denied-card h1{
font-size:26px;
}

.access-denied-actions{
flex-direction:column;
}

.access-denied-actions .btn{
width:100%;
justify-content:center;
}

}

</style>
</head>
<body>

<?php if ($is_authenticated) { ?>
<div class="app-layout">
<?php include __DIR__ . '/../partials/sidebar.php'; ?>
<div class="main-content">
<?php include __DIR__ . '/../partials/topbar.php'; ?>
<div class="page-content access-denied-content">
<?php } else { ?>
<div class="access-denied-page">
<?php } ?>

<section class="access-denied-card">
<div class="access-denied-code">403</div>
<div class="access-denied-icon">
<i class="fa-solid fa-shield-halved"></i>
</div>
<span class="access-denied-eyebrow">Permission required</span>
<h1>Access Denied</h1>
<p>
You do not currently have permission to access this page.
If you believe this is a mistake, please contact your administrator or request additional access.
</p>
<div class="access-denied-actions">
<a href="<?php echo htmlspecialchars($dashboard_url); ?>" class="btn btn-primary">
<i class="fa-solid fa-house"></i>
Go to Dashboard
</a>
<a
href="javascript:history.back();"
class="btn">
<i class="fa-solid fa-arrow-left"></i>
Go Back
</a>
<?php if ($is_authenticated) { ?>
<a href="<?php echo BASE_URL; ?>profile/index.php" class="btn btn-secondary">
<i class="fa-solid fa-user"></i>
My Profile
</a>
<?php } else { ?>
<a href="<?php echo BASE_URL; ?>auth/login.php" class="btn btn-secondary">
<i class="fa-solid fa-right-to-bracket"></i>
Sign In
</a>
<?php } ?>
</div>
<div class="access-denied-help">
<i class="fa-solid fa-circle-info"></i>
<span>If access was just granted, reload this page. Permission changes are now refreshed on every request.</span>
</div>
</section>

<?php if ($is_authenticated) { ?>
</div>
</div>
</div>
<?php } else { ?>
</div>
<?php } ?>

<script src="<?php echo BASE_URL; ?>assets/js/common.js"></script>
</body>
</html>
