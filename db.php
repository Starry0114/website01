<?php
class Database {
    private $host = "starry00.mysql.database.azure.com";
    private $db_name = "demo";
    private $username = "starry";
    private $password = "Dark0689@@";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name, 
                $this->username, 
                $this->password,
                [
                    PDO::MYSQL_ATTR_SSL_CA => '/path/to/BaltimoreCyberTrustRoot.crt.pem',
                    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => true
                ]
            );
            $this->conn->exec("set names utf8");
        } catch(PDOException $e) {
            echo "数据库连接错误: " . $e->getMessage();
        }
        return $this->conn;
    }
}
?>
