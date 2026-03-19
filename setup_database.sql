-- ====================================================================
-- SQL SCRIPT FOR SEO AUDIT MIDTERM PROJECT
-- Database: PostgreSQL
-- ====================================================================

-- Note: In pgAdmin, you might need to create the database manually first.
-- Right-click on 'Databases' -> 'Create' -> 'Database...' and name it 'seo_project_db'.
-- Then, open the Query Tool for that database and run the rest of this script.

-- 1. DATABASE CREATION (run this command separately if needed)
-- CREATE DATABASE seo_project_db;

-- ====================================================================
-- 2. TABLE STRUCTURE
-- ====================================================================

-- Drop the table if it already exists to start fresh
DROP TABLE IF EXISTS seo_reports;

-- Create the 'seo_reports' table
CREATE TABLE seo_reports (
    -- id: Serial (Auto-increment) Primary Key
    id SERIAL PRIMARY KEY,

    -- url: Text (To store the scanned website link), cannot be null
    url TEXT NOT NULL,

    -- page_speed: Decimal/Float (To store loading time in seconds)
    page_speed DECIMAL(10, 4),

    -- meta_description: Text (To store the description or 'Missing')
    meta_description TEXT,

    -- image_count: Integer (Number of images found)
    image_count INTEGER,

    -- broken_links: Integer (Number of broken links found)
    broken_links INTEGER,

    -- created_at: Timestamp (To record when the check was done)
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- ====================================================================
-- 3. SAMPLE DATA
-- ====================================================================

-- Insert 3 rows of sample data for testing purposes
INSERT INTO seo_reports (url, page_speed, meta_description, image_count, broken_links)
VALUES
    ('https://www.example.com', 1.2543, 'This is an example meta description for a test website.', 15, 2),
    ('https://www.google.com', 0.8765, 'Search the world's information, including webpages, images, videos and more.', 10, 0),
    ('https://www.wikipedia.org', 1.5987, 'Missing', 5, 1);


-- ====================================================================
-- SCRIPT END
-- You can now verify the table and data in pgAdmin.
-- ====================================================================
