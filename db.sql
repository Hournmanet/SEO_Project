CREATE TABLE seo_results (
    id SERIAL PRIMARY KEY,
    url VARCHAR(255) NOT NULL,
    page_speed FLOAT NOT NULL,
    meta_description TEXT,
    image_count INTEGER NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);