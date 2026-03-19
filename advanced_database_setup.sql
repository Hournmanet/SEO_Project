-- ==================================================================== 
 -- SEO Project Full Database Setup Script 
 -- ==================================================================== 
 
 -- 1️⃣ Drop tables if they already exist (safe for a fresh start) 
 DROP TABLE IF EXISTS seo_reports CASCADE; 
 DROP TABLE IF EXISTS keywords CASCADE; 
 DROP TABLE IF EXISTS backlinks CASCADE; 
 DROP TABLE IF EXISTS analytics CASCADE; 
 DROP TABLE IF EXISTS pages CASCADE; 
 
 -- ==================================================================== 
 -- 2️⃣ Create the 'pages' table (main SEO metrics) 
 -- ==================================================================== 
 CREATE TABLE pages ( 
     id SERIAL PRIMARY KEY, 
     url VARCHAR(255) NOT NULL, 
     page_speed INT,            -- in seconds 
     meta_description VARCHAR(255), 
     keyword_coverage INT,      -- percentage 
     image_count INT,           -- total images 
     broken_links INT,          -- number of broken links 
     created_at TIMESTAMP DEFAULT NOW() 
 ); 
 
 -- ==================================================================== 
 -- 3️⃣ Create 'analytics' table (optional detailed page analytics) 
 -- ==================================================================== 
 CREATE TABLE analytics ( 
     id SERIAL PRIMARY KEY, 
     page_id INT REFERENCES pages(id) ON DELETE CASCADE, 
     load_time_ms INT, 
     requests INT, 
     size_kb INT, 
     created_at TIMESTAMP DEFAULT NOW() 
 ); 
 
 -- ==================================================================== 
 -- 4️⃣ Create 'backlinks' table 
 -- ==================================================================== 
 CREATE TABLE backlinks ( 
     id SERIAL PRIMARY KEY, 
     page_id INT REFERENCES pages(id) ON DELETE CASCADE, 
     source_url VARCHAR(255), 
     anchor_text VARCHAR(255), 
     created_at TIMESTAMP DEFAULT NOW() 
 ); 
 
 -- ==================================================================== 
 -- 5️⃣ Create 'keywords' table 
 -- ==================================================================== 
 CREATE TABLE keywords ( 
     id SERIAL PRIMARY KEY, 
     page_id INT REFERENCES pages(id) ON DELETE CASCADE, 
     keyword VARCHAR(100), 
     density DECIMAL(5,2), 
     created_at TIMESTAMP DEFAULT NOW() 
 ); 
 
 -- ==================================================================== 
 -- 6️⃣ Create 'seo_reports' table 
 -- ==================================================================== 
 CREATE TABLE seo_reports ( 
     id SERIAL PRIMARY KEY, 
     page_id INT REFERENCES pages(id) ON DELETE CASCADE, 
     report_text TEXT, 
     created_at TIMESTAMP DEFAULT NOW() 
 ); 
 
 -- ==================================================================== 
 -- 7️⃣ Insert sample data into 'pages' 
 -- ==================================================================== 
 INSERT INTO pages (url, page_speed, meta_description, keyword_coverage, image_count, broken_links) 
 VALUES 
     ('https://www.example.com', 1, 'This is a sample meta description.', 75, 12, 1), 
     ('https://www.wikipedia.org', 2, 'Missing', 50, 25, 3), 
     ('https://www.google.com', 0, 'Search the world''s information.', 100, 5, 0); 
 
 -- ==================================================================== 
 -- 8️⃣ Optional: Sample data for analytics, backlinks, keywords 
 -- ==================================================================== 
 INSERT INTO analytics (page_id, load_time_ms, requests, size_kb) 
 VALUES 
     (1, 1200, 45, 1024), 
     (2, 2500, 78, 2048), 
     (3, 800, 30, 512); 
 
 INSERT INTO backlinks (page_id, source_url, anchor_text) 
 VALUES 
     (1, 'https://referrer.com', 'Example link'), 
     (2, 'https://other.com', 'Wikipedia ref'), 
     (3, 'https://search.com', 'Google search'); 
 
 INSERT INTO keywords (page_id, keyword, density) 
 VALUES 
     (1, 'SEO', 3.5), 
     (1, 'website', 2.1), 
     (2, 'knowledge', 4.0), 
     (3, 'search', 5.2); 
 
 INSERT INTO seo_reports (page_id, report_text) 
 VALUES 
     (1, 'Page speed is good. Meta description is present.'), 
     (2, 'Page is slightly slow. Meta description missing.'), 
     (3, 'Page is fast. Keywords well optimized.');