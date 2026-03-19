<?php
class Database {
    private $host = "localhost";
    private $db_name = "SEO_Project"; // Match the name in your Object Explorer
    private $username = "postgres";   
    private $password = "Maneth99"; // The one you use to log into pgAdmin
    public $conn;

    public function connect() {
        $this->conn = null;
        try {
            $this->conn = new PDO("pgsql:host={$this->host};dbname={$this->db_name}", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo "Connection error: " . $e->getMessage();
        }
        return $this->conn;
    }
}
?>