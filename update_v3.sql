-- ====================================================================
-- SQL SCRIPT TO UPDATE 'pages' TABLE FOR NEW ADVANCED FEATURES
-- ====================================================================

-- This script adds columns for Heading Audit, SSL, and Social Preview.

-- Add 'h1_tags', 'h2_tags', 'h3_tags' columns
ALTER TABLE pages ADD COLUMN IF NOT EXISTS h1_tags TEXT;
ALTER TABLE pages ADD COLUMN IF NOT EXISTS h2_tags TEXT;
ALTER TABLE pages ADD COLUMN IF NOT EXISTS h3_tags TEXT;

-- Add 'has_ssl' column (boolean to check for HTTPS)
ALTER TABLE pages ADD COLUMN IF NOT EXISTS has_ssl BOOLEAN;

-- Add columns for Open Graph (Social Media Preview)
ALTER TABLE pages ADD COLUMN IF NOT EXISTS og_title VARCHAR(255);
ALTER TABLE pages ADD COLUMN IF NOT EXISTS og_image VARCHAR(255);


-- ====================================================================
-- SCRIPT END
-- Please run this script in your pgAdmin4 Query Tool.
-- ====================================================================
