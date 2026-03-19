-- ====================================================================
-- FINAL & COMPLETE SQL SCRIPT FOR SEO AUDIT PROJECT
-- ====================================================================

-- INSTRUCTIONS:
-- 1. In pgAdmin, create a new database named 'SEO_Project' (or use your existing one).
-- 2. Open the Query Tool for that database.
-- 3. Copy and paste this entire script into the Query Tool and run it.
-- This will create the correct table structure and add sample data.

-- ====================================================================
-- 1. TABLE STRUCTURE
-- ====================================================================

-- Drop the table if it already exists to ensure a fresh start
DROP TABLE IF EXISTS pages;

-- Create the final 'pages' table with all necessary columns
CREATE TABLE pages (
    id SERIAL PRIMARY KEY,
    url VARCHAR(255) NOT NULL,
    page_speed INT,           -- Stored in seconds (integer)
    meta_description VARCHAR(255),
    keyword_coverage INT,     -- Stored as a percentage (integer)
    image_count INT,          -- Total number of images
    broken_links INT,         -- Total number of broken links
    created_at TIMESTAMP DEFAULT NOW()
);

-- ====================================================================
-- 2. SAMPLE DATA
-- ====================================================================

-- Insert 3 rows of sample data for immediate testing
INSERT INTO pages (url, page_speed, meta_description, keyword_coverage, image_count, broken_links)
VALUES
    ('https://www.example.com', 1, 'This is a sample meta description.', 75, 12, 1),
    ('https://www.wikipedia.org', 2, 'Missing', 50, 25, 3),
    ('https://www.google.com', 0, 'Search the world's information.', 100, 5, 0);


-- ====================================================================
-- SCRIPT END
-- Your database is now ready for the advanced version of the project.
-- ====================================================================
