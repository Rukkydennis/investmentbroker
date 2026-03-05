<?php
session_start();
require_once "./db.php";
header('Content-Type: application/json');



if (isset($_GET['action']) && $_GET['action'] == 'login') {
    $username = $_GET['username'];
    $password = $_GET['password'];
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $query = "SELECT * FROM users WHERE (username = '$username' OR email = '$username')";
    $result = $DB->query($query);
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['issAuthenticated'] = true;
            $_SESSION['user'] = $username;
            $_SESSION['role'] = $user['role'];
            $_SESSION['user_id'] = $user['id'];
            echo json_encode(array('issAuthenticated' => true, 'role' => $user['role']));
        } else {
            echo json_encode(array('issAuthenticated' => false, 'error' => 'Invalid password'));
        }
    } else {
        echo json_encode(array('issAuthenticated' => false, 'error' => 'User not found'));
    }
} 

if (isset($_GET['action']) && $_GET['action'] == 'register') {
    $username = $_GET['username'];
    $password = $_GET['password'];
    $email = $_GET['email'];
    $fullname = $_GET['fullname'];
    $referrer_id = isset($_GET['referrer_id']) && !empty($_GET['referrer_id']) ? intval($_GET['referrer_id']) : 'NULL';
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

   $check = $DB->query("SELECT id FROM users WHERE username = '$username' OR email = '$email'");
//    var_dump($check);
    if ($check->num_rows == 0) {
        
        // Validate referrer if provided
        if ($referrer_id !== 'NULL') {
            $refCheck = $DB->query("SELECT id FROM users WHERE id = $referrer_id");
            if ($refCheck->num_rows == 0) {
                $referrer_id = 'NULL'; // Invalid referrer, fallback to NULL
            }
        }

        $query = "INSERT INTO users (username, password_hash, email, full_name, referrer_id) VALUES ('$username', '$hashedPassword', '$email', '$fullname', $referrer_id)";
        if ($DB->query($query)) {
            echo json_encode(array('isRegistered' => true));
        } else {
            echo json_encode(array('isRegistered' => false, 'error' => $DB->error));
        }
     } else {
        echo json_encode(array('isRegistered' => false, 'error' => 'User already exists'));
    }
 }
?>