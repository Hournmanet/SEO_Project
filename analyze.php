<?php
// Hide deprecated warnings
error_reporting(E_ALL & ~E_DEPRECATED);

require_once 'db.php';
require_once 'vendor/autoload.php';

use Symfony\Component\DomCrawler\Crawler;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['url'])) {
    $url = filter_var($_POST['url'], FILTER_SANITIZE_URL);

    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        echo json_encode(['error' => 'Invalid URL format.']);
        exit;
    }

    // --- 1. DATABASE CACHING (10 Minutes) ---
    $cache_query = "SELECT * FROM pages WHERE url = $1 AND created_at >= NOW() - INTERVAL '10 minutes' ORDER BY created_at DESC LIMIT 1";
    $cache_result = pg_query_params($db_connection, $cache_query, [$url]);
    
    if ($cache_result && pg_num_rows($cache_result) > 0) {
        $cached_data = pg_fetch_assoc($cache_result);
        // Convert string representations back to arrays for JSON response
        $cached_data['missing_alt_tags'] = $cached_data['missing_alt_tags'] ? explode(', ', $cached_data['missing_alt_tags']) : [];
        $cached_data['h1_tags'] = $cached_data['h1_tags'] ? explode('|', $cached_data['h1_tags']) : [];
        $cached_data['h2_tags'] = $cached_data['h2_tags'] ? explode('|', $cached_data['h2_tags']) : [];
        $cached_data['h3_tags'] = $cached_data['h3_tags'] ? explode('|', $cached_data['h3_tags']) : [];
        $cached_data['cached'] = true; // Flag to indicate it's from cache
        echo json_encode($cached_data);
        exit;
    }

    // --- 2. FAST cURL FETCH ---
    $start_time = microtime(true);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 5-second timeout
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'); // Pretend to be a browser
    
    $html_content = curl_exec($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);

    $end_time = microtime(true);
    $page_speed = $end_time - $start_time;

    if ($html_content === false) {
        echo json_encode(['error' => 'Failed to fetch URL. ' . $curl_error]);
        exit;
    }

    // --- 3. ANALYSIS USING DomCrawler ---
    $crawler = new Crawler($html_content, $url);

    $title_node = $crawler->filter('title');
    $page_title = $title_node->count() > 0 ? $title_node->text() : 'Missing';

    $meta_description_node = $crawler->filter('meta[name="description"]');
    $meta_description = $meta_description_node->count() > 0 ? $meta_description_node->attr('content') : 'Missing';

    $image_count = $crawler->filter('img')->count();

    $keyword = isset($_POST['keyword']) ? strtolower(trim($_POST['keyword'])) : '';
    $keywords = $keyword ? [$keyword] : ['seo', 'optimization', 'website', 'marketing'];
    $body_text = strtolower($crawler->filter('body')->text());
    $found_keywords = 0;
    foreach ($keywords as $kw) {
        if (strpos($body_text, $kw) !== false) {
            $found_keywords++;
        }
    }
    $keyword_coverage = ($found_keywords / count($keywords)) * 100;

    $broken_links = 0;
    $links = $crawler->filter('a')->extract(['href']);
    $links_to_check = array_slice($links, 0, 10);
    foreach ($links_to_check as $link) {
        if (filter_var($link, FILTER_VALIDATE_URL)) {
            $headers = @get_headers($link);
            if (!$headers || strpos($headers[0], '404') !== false) {
                $broken_links++;
            }
        }
    }

    $missing_alt_tags_list = [];
    $crawler->filter('img')->each(function ($node) use (&$missing_alt_tags_list) {
        $alt = $node->attr('alt');
        if (empty($alt) || trim($alt) === '') {
            $missing_alt_tags_list[] = $node->attr('src');
        }
    });
    $missing_alt_tags_text = implode(', ', $missing_alt_tags_list);

    $h1_tags = $crawler->filter('h1')->each(function ($node) { return $node->text(); });
    $h2_tags = $crawler->filter('h2')->each(function ($node) { return $node->text(); });
    $h3_tags = $crawler->filter('h3')->each(function ($node) { return $node->text(); });

    $has_ssl = strpos($url, 'https://') === 0;

    $og_title_node = $crawler->filter('meta[property="og:title"]');
    $og_title = $og_title_node->count() > 0 ? $og_title_node->attr('content') : 'Missing';
    $og_image_node = $crawler->filter('meta[property="og:image"]');
    $og_image = $og_image_node->count() > 0 ? $og_image_node->attr('content') : 'Missing';

    // Calculate SEO Score
    $score = 100;
    if ($page_speed > 3) $score -= 10;
    if ($meta_description === 'Missing') $score -= 15;
    if ($keyword_coverage < 50) $score -= 10;
    if (count($h1_tags) !== 1) $score -= 15;
    if (!$has_ssl) $score -= 20;
    if ($og_title === 'Missing' || $og_image === 'Missing') $score -= 5;
    $score -= count($missing_alt_tags_list) * 1;
    $score -= $broken_links * 2;
    $seo_score = max(0, $score);

    // --- 4. SAVE TO DATABASE ---
    $query = 'INSERT INTO pages (url, keyword, page_title, page_speed, meta_description, image_count, keyword_coverage, broken_links, seo_score, missing_alt_tags, h1_tags, h2_tags, h3_tags, has_ssl, og_title, og_image) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, $16)';
    pg_query_params($db_connection, $query, [
        $url, $keyword, $page_title, (int)$page_speed, $meta_description, $image_count, (int)$keyword_coverage, $broken_links, $seo_score, $missing_alt_tags_text,
        implode('|', $h1_tags), implode('|', $h2_tags), implode('|', $h3_tags), $has_ssl ? 't' : 'f', $og_title, $og_image
    ]);

    $last_id_query = pg_query($db_connection, "SELECT lastval()");
    $last_id = pg_fetch_result($last_id_query, 0, 0);

    // --- 5. RETURN JSON RESPONSE ---
    $response = [
        'id' => $last_id,
        'url' => $url,
        'page_title' => $page_title,
        'keyword' => $keyword,
        'page_speed' => $page_speed,
        'meta_description' => $meta_description,
        'image_count' => $image_count,
        'keyword_coverage' => $keyword_coverage,
        'broken_links' => $broken_links,
        'seo_score' => $seo_score,
        'missing_alt_tags' => $missing_alt_tags_list,
        'h1_tags' => $h1_tags,
        'h2_tags' => $h2_tags,
        'h3_tags' => $h3_tags,
        'has_ssl' => $has_ssl,
        'og_title' => $og_title,
        'og_image' => $og_image,
        'cached' => false
    ];

    echo json_encode($response);
    exit;
} else {
    echo json_encode(['error' => 'Invalid request.']);
}
?>