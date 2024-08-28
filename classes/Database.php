<?php
class Database {
    private $host = "localhost";
    private $db_name = "admin_github";
    private $username = "admin_github";
    private $password = "Amb@142536";
    public $conn;

    public function dbConnection() {
        $this->conn = null;    
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);   
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }

    public function sanitize($data) {
        return htmlspecialchars(strip_tags($data));
    }
}
?>
