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

// Automatically initialize the database if tables are missing
$init_query = "
CREATE TABLE IF NOT EXISTS pages (
    id SERIAL PRIMARY KEY,
    url VARCHAR(255) NOT NULL,
    keyword VARCHAR(255),
    page_title TEXT,
    page_speed DECIMAL(10, 4),
    meta_description TEXT,
    keyword_coverage DECIMAL(5,2),
    image_count INT,
    broken_links INT,
    seo_score INT,
    missing_alt_tags TEXT,
    h1_tags TEXT,
    h2_tags TEXT,
    h3_tags TEXT,
    has_ssl BOOLEAN,
    og_title TEXT,
    og_image TEXT,
    created_at TIMESTAMP DEFAULT NOW()
);";
@pg_query($db_connection, $init_query);

// Also apply ALTER TABLE just in case they have an old version of the table
@pg_query($db_connection, "ALTER TABLE pages ADD COLUMN IF NOT EXISTS keyword VARCHAR(255);");
@pg_query($db_connection, "ALTER TABLE pages ADD COLUMN IF NOT EXISTS page_title TEXT;");
@pg_query($db_connection, "ALTER TABLE pages ADD COLUMN IF NOT EXISTS h1_tags TEXT;");
@pg_query($db_connection, "ALTER TABLE pages ADD COLUMN IF NOT EXISTS h2_tags TEXT;");
@pg_query($db_connection, "ALTER TABLE pages ADD COLUMN IF NOT EXISTS h3_tags TEXT;");
@pg_query($db_connection, "ALTER TABLE pages ADD COLUMN IF NOT EXISTS og_title TEXT;");
@pg_query($db_connection, "ALTER TABLE pages ADD COLUMN IF NOT EXISTS og_image TEXT;");
?>