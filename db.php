<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    private $ssl_cert_cache;
    public $conn;

    public function __construct() {
        $this->loadConfig();
        $this->validateConfig();
    }

    private function loadConfig(): void {
        $this->host = getenv('DB_HOST');
        $this->port = getenv('DB_PORT') ?: '3306'; // 默认端口
        $this->db_name = getenv('DB_NAME');
        $this->username = getenv('DB_USER');
        $this->password = getenv('DB_PASS');
        $this->ssl_cert_cache = sys_get_temp_dir() . '/DigiCertGlobalRootCA.crt';
    }

    private function validateConfig(): void {
        $required = ['host', 'db_name', 'username', 'password'];
        foreach ($required as $field) {
            if (empty($this->$field)) {
                throw new RuntimeException("数据库配置缺失: DB_{strtoupper($field)}");
            }
        }
    }

    public function getConnection(): PDO {
        if ($this->conn instanceof PDO) {
            return $this->conn; // 返回现有连接
        }

        try {
            $this->conn = new PDO(
                $this->getDsn(),
                $this->username,
                $this->password,
                $this->getPdoOptions()
            );
            
            $this->configureConnection();
            return $this->conn;
            
        } catch (PDOException $e) {
            $this->logError($e);
            throw new RuntimeException("数据库服务不可用，请稍后重试");
        }
    }

    private function getDsn(): string {
        return "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8mb4";
    }

    private function getPdoOptions(): array {
        return [
            PDO::MYSQL_ATTR_SSL_CA => $this->getSSLCertPath(),
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => true,
            PDO::ATTR_PERSISTENT => false, // 不建议使用持久连接
            PDO::ATTR_EMULATE_PREPARES => false, // 禁用模拟预处理
            PDO::ATTR_STRINGIFY_FETCHES => false
        ];
    }

    private function configureConnection(): void {
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    private function getSSLCertPath(): string {
        // 检查系统证书路径
        $systemPaths = [
            '/etc/ssl/certs/DigiCert_Global_Root_CA.pem',
            '/usr/local/etc/openssl/cert.pem',
            'C:\\certs\\DigiCertGlobalRootCA.crt'
        ];

        foreach ($systemPaths as $path) {
            if (is_readable($path)) {
                return $path;
            }
        }

        // 检查缓存证书
        if ($this->isCertValid($this->ssl_cert_cache)) {
            return $this->ssl_cert_cache;
        }

        // 下载新证书
        return $this->downloadSSLCert();
    }

    private function isCertValid(string $path): bool {
        return file_exists($path) && 
               filesize($path) > 0 && 
               time() - filemtime($path) < 30 * 24 * 3600; // 30天有效期
    }

    private function downloadSSLCert(): string {
        $certUrl = 'https://cacerts.digicert.com/DigiCertGlobalRootCA.crt.pem';
        $certContent = @file_get_contents($certUrl);
        
        if ($certContent === false) {
            throw new RuntimeException("无法下载SSL证书");
        }

        if (file_put_contents($this->ssl_cert_cache, $certContent, LOCK_EX) === false) {
            throw new RuntimeException("无法保存SSL证书");
        }

        chmod($this->ssl_cert_cache, 0644);
        return $this->ssl_cert_cache;
    }

    private function logError(PDOException $e): void {
        $message = sprintf(
            "[MySQL Error %s] %s\nDSN: %s\nStack trace:\n%s",
            $e->getCode(),
            $e->getMessage(),
            $this->getDsn(),
            $e->getTraceAsString()
        );
        error_log($message);
    }

    public function __destruct() {
        $this->conn = null; // 确保连接关闭
    }
}
?>
