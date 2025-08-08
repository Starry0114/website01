<?php
class Database {
    private $host = "starry00.mysql.database.azure.com";
    private $db_name = "demo";
    private $username = "starry";
    private $password = "Vincent020114";
    private $port = "3306";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name, 
                $this->username, 
                $this->password,
                [
                    PDO::MYSQL_ATTR_SSL_CA => 'C:\Users\shmcv\Downloads\DigiCertGlobalRootCA.crt.pem', // 替换为实际路径
                    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => true
                ]
            );
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // 添加错误模式设置
        } catch(PDOException $e) {
            echo "数据库连接错误: " . $e->getMessage();
        }
        return $this->conn;
    }
}
?>
