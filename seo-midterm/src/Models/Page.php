<?php
require_once __DIR__ . '/../Utils/Database.php';

class Page {
    private $conn;
    private $table = 'pages';

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function save($data) {
        if (isset($data['images_size_kb'])) {
            $data['images_size_kb'] = (int) round($data['images_size_kb']);
        }
        if (isset($data['page_speed'])) {
            $data['page_speed'] = (int) round($data['page_speed']);
        }
        $sql = "INSERT INTO {$this->table} (url, page_speed, meta_description, keyword_coverage, images_size_kb, broken_links) 
                VALUES (:url, :page_speed, :meta_description, :keyword_coverage, :images_size_kb, :broken_links)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($data);
    }

    public function getAll() {
        $stmt = $this->conn->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>