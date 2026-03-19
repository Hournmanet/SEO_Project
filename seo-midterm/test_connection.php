<?php
require_once __DIR__ . '/../src/Utils/Database.php';

$db = new Database();
$conn = $db->connect();

if($conn) {
    echo "✅ Connection successful!";
} else {
    echo "❌ Connection failed!";
}
?>
