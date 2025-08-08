<?php
// check_auth.php
session_start();
header("Content-Type: application/json");

if (isset($_SESSION['user_id'])) {
    echo json_encode([
        'authenticated' => true,
        'username' => $_SESSION['username'],
        'email' => $_SESSION['email'],
        'created_at' => $_SESSION['created_at']
    ]);
} else {
    echo json_encode(['authenticated' => false]);
}
?>