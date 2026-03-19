-- ====================================================================
-- SQL SCRIPT TO UPDATE 'pages' TABLE FOR ADVANCED FEATURES
-- ====================================================================

-- This script adds columns for SEO Score and missing alt tags.

-- Add 'seo_score' column to store the overall score (out of 100)
ALTER TABLE pages ADD COLUMN IF NOT EXISTS seo_score INT;

-- Add 'missing_alt_tags' column to store a list of images missing alt text
ALTER TABLE pages ADD COLUMN IF NOT EXISTS missing_alt_tags TEXT;


-- ====================================================================
-- SCRIPT END
-- Please run this script in your pgAdmin4 Query Tool.
-- ====================================================================
