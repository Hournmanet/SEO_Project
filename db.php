<?php
// Use Environment Variables for Vercel, fallback to localhost for local development
$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '5432';
$dbname = getenv('DB_NAME') ?: 'SEO_Project'; 
$user = getenv('DB_USER') ?: 'postgres'; 
$password = getenv('DB_PASS') ?: 'Maneth99'; 

$conn_string = "host={$host} port={$port} dbname={$dbname} user={$user} password={$password}";

$db_connection = pg_connect($conn_string);

if (!$db_connection) {
    die("Error: Cannot connect to the Database. Please check your credentials or Environment Variables.");
}
?>