<?php
session_start();

unset($_SESSION['user']);
unset($_SESSION['admin']);

$_SESSION['logout_message'] = 'You have been signed out successfully.';

header("Location: login.php");
exit;
?>