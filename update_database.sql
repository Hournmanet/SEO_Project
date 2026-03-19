-- ====================================================================
-- SQL SCRIPT TO UPDATE YOUR 'pages' TABLE
-- ====================================================================

-- This script adds the necessary columns for advanced SEO analysis.
-- It is safe to run even if the columns already exist.

-- Add 'image_count' column if it doesn't exist
ALTER TABLE pages ADD COLUMN IF NOT EXISTS image_count INT;

-- Add 'broken_links' column if it doesn't exist
-- This was already in your schema, this command ensures it is present.
ALTER TABLE pages ADD COLUMN IF NOT EXISTS broken_links INT;

-- Add 'keyword_coverage' column if it doesn't exist
-- This was also in your schema, this ensures it is present.
ALTER TABLE pages ADD COLUMN IF NOT EXISTS keyword_coverage INT;


-- ====================================================================
-- SCRIPT END
-- Please run this script in your pgAdmin4 Query Tool.
-- ====================================================================
