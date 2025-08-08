<?php
// login.php
session_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once 'db.php';

$database = new Database();
$db = $database->getConnection();

// 从POST获取数据而不是JSON
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => '用户名/邮箱和密码是必填的']);
    exit;
}

// 查询用户
$query = "SELECT * FROM users WHERE username = :username OR email = :username";
$stmt = $db->prepare($query);
$stmt->bindParam(":username", $username);
$stmt->execute();

if ($stmt->rowCount() == 1) {
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (password_verify($password, $row['password'])) {
        // 设置会话变量
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['email'] = $row['email'];
        $_SESSION['created_at'] = $row['created_at'];
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => '密码错误']);
    }
} else {
    echo json_encode(['success' => false, 'message' => '用户名/邮箱不存在']);
}
?>