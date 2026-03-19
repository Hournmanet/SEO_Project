<?php
$host = 'localhost';
$port = '5432';

// យោងតាមរូបភាព pgAdmin របស់អ្នក ឈ្មោះ Database គឺ SEO_Project
$dbname = 'SEO_Project'; 

// យោងតាមរូបភាព Username របស់អ្នកគឺ postgres
$user = 'postgres'; 

// *** ប្តូរលេខ ១២៣៤៥៦ ទៅជា Password ពិតប្រាកដរបស់អ្នក ***
$password = 'Maneth99'; 

$conn_string = "host={$host} port={$port} dbname={$dbname} user={$user} password={$password}";

$db_connection = pg_connect($conn_string);

if (!$db_connection) {
    die("Error: មិនអាចភ្ជាប់ទៅ Database SEO_Project បានទេ។ សូមឆែក Password ក្នុង db.php!");
}
?>