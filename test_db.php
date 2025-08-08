<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=user_auth', 'root', '');
    echo "数据库连接成功！";
    // 检查表是否存在
    $tableExists = $pdo->query("SHOW TABLES LIKE 'users'")->rowCount() > 0;
    echo $tableExists ? "表存在" : "表不存在";
} catch (PDOException $e) {
    die("连接失败: " . $e->getMessage());
}