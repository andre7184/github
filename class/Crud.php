<?php
require_once 'Database.php';

class Crud {
    private $conn;
    private $db;

    public function __construct(){
        $this->db = new Database();
        $this->conn = $this->db->dbConnection();
    }

    public function create($table, $data){
        $fields = implode(", ", array_keys($data));
        $values = ":" . implode(", :", array_keys($data));
        // echo "create:".$table."<br>";
        // print_r($data);
        $stmt = $this->conn->prepare("INSERT INTO $table ($fields) VALUES ($values)");
        $success = $stmt->execute($data);
        if ($success) {
            return $this->conn->lastInsertId();
        } else {
            return false;
        }
    }

    public function read($table, $conditions = [], $operador = []){
        $sql = "SELECT * FROM $table";
        if (!empty($conditions)) {
            $sql .= " WHERE ";
            $first = true;
            foreach ($conditions as $key => $value) {
                if (!$first) {
                    $sql .= " AND ";
                } else {
                    $first = false;
                }
                // Use o operador correspondente ou '=' como padrão
                $operador = isset($operador[$key]) ? $operador[$key] : '=';
                $sql .= "$key $operador :$key";
            }
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($conditions);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($table, $data, $conditions){
        $fields = "";
        foreach ($data as $key => $value) {
            $fields .= "$key = :$key, ";
        }
        $fields = rtrim($fields, ", ");
    
        $where = "";
        foreach ($conditions as $key => $value) {
            $where .= "$key = :$key AND ";
        }
        $where = rtrim($where, " AND ");
    
        $sql = "UPDATE $table SET $fields WHERE $where";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute(array_merge($data, $conditions));
    }

    public function delete($table, $conditions){
        // echo "delete:".$table."<br>";
        // print_r($conditions);
        $sql = "DELETE FROM $table WHERE " . key($conditions) . " = :" . key($conditions);
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($conditions);
    }

    public function count($table, $conditions = []){
        $sql = "SELECT COUNT(*) FROM $table";
        if (!empty($conditions)) {
            $sql .= " WHERE " . key($conditions) . " = :" . key($conditions);
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($conditions);
        return $stmt->fetchColumn();
    }

    public function sanitize($data) {
        return $this->db->sanitize($data);
    }
}
?>
