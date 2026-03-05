<?php
session_start();
if (!isset($_SESSION['issAuthenticated']) || $_SESSION['issAuthenticated'] !== true || $_SESSION['role'] !== 'admin') {
    header("Location: ./login.html");
    exit();
}
require_once "../../asset/config/DB.php";
$user_id = $_SESSION['user_id']; // Although admin might not have user_id in same context, usually they share the users table
$userQuery = "SELECT * FROM users WHERE id = $user_id";
$userResult = $DB->query($userQuery);
$admin = $userResult->fetch_assoc();
?>
