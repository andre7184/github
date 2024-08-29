<?php
include '../../../private/config_gh.php';
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    public $conn;
    public function __construct() {
        global $host, $db_name, $username, $password;
        $this->host = $host; // coloque seu host aqui
        $this->db_name = $db_name; // coloque nome do banco de dados aqui
        $this->username = $username; // coloque o usuario do banco de dados aqui
        $this->password = $password; // coloque a senha do banco de dados aqui
    }
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