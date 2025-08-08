<?php
// register.php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // 允许所有域名访问（开发环境用）
header("Access-Control-Allow-Methods: POST, OPTIONS"); // 允许的请求方法
header("Access-Control-Allow-Headers: Content-Type"); // 允许的请求头
// register.php 顶部添加
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit;
}

require_once 'db.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"), true);

$username = $data['username'] ?? '';
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

// 验证输入
if (empty($username) || empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => '所有字段都是必填的']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => '邮箱格式无效']);
    exit;
}

// 检查用户名是否已存在
$query = "SELECT id FROM users WHERE username = :username";
$stmt = $db->prepare($query);
$stmt->bindParam(":username", $username);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => false, 'message' => '用户名已被使用']);
    exit;
}

// 检查邮箱是否已存在
$query = "SELECT id FROM users WHERE email = :email";
$stmt = $db->prepare($query);
$stmt->bindParam(":email", $email);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => false, 'message' => '邮箱已被使用']);
    exit;
}

// 哈希密码
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

// 插入新用户
$query = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)";
$stmt = $db->prepare($query);
$stmt->bindParam(":username", $username);
$stmt->bindParam(":email", $email);
$stmt->bindParam(":password", $hashed_password);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => '注册失败']);
}
?>