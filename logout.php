<?php
session_start();

// Include the file containing the insertAuditLog() function
include('functions.php');

// Insert audit log entry for logging out
$username = $_SESSION['username'];
$action = "Logged out";
insertAuditLog($username, $action);

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to the login page
header("Location: login.php");
exit;
?>
