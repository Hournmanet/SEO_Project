<?php
require_once 'db.php';
require_once 'vendor/autoload.php';

use Goutte\Client;

function analyze_url($url, $db_connection) {
    $client = new Client();
    $start_time = microtime(true);
    $crawler = $client->request('GET', $url);
    $end_time = microtime(true);

    $page_speed = $end_time - $start_time;

    $meta_description_node = $crawler->filter('meta[name="description"]');
    $meta_description = $meta_description_node->count() > 0 ? $meta_description_node->attr('content') : 'Missing';

    // Advanced Analysis: Image Count
    $image_count = $crawler->filter('img')->count();

    // Advanced Analysis: Keyword Coverage (Simple Example)
    $keywords = ['seo', 'optimization', 'website', 'marketing'];
    $body_text = strtolower($crawler->filter('body')->text());
    $found_keywords = 0;
    foreach ($keywords as $keyword) {
        if (strpos($body_text, $keyword) !== false) {
            $found_keywords++;
        }
    }
    $keyword_coverage = ($found_keywords / count($keywords)) * 100;

    // Advanced Analysis: Broken Links (Basic Check)
    $broken_links = 0;
    $links = $crawler->filter('a')->extract(['href']);
    // Limit the number of links to check to avoid long execution times
    $links_to_check = array_slice($links, 0, 10); 
    foreach ($links_to_check as $link) {
        if (filter_var($link, FILTER_VALIDATE_URL)) {
            $headers = @get_headers($link);
            if (!$headers || strpos($headers[0], '404') !== false) {
                $broken_links++;
            }
        }
    }

    // Insert into the 'pages' table with the new columns
    $query = 'INSERT INTO pages (url, page_speed, meta_description, image_count, keyword_coverage, broken_links) VALUES ($1, $2, $3, $4, $5, $6)';
    $result = pg_query_params($db_connection, $query, [$url, (int)$page_speed, $meta_description, $image_count, (int)$keyword_coverage, $broken_links]);

    // Return the results for immediate display.
    return [
        'url' => $url,
        'page_speed' => $page_speed,
        'meta_description' => $meta_description,
        'image_count' => $image_count,
        'keyword_coverage' => $keyword_coverage,
        'broken_links' => $broken_links
    ];
}

function get_history($db_connection) {
    // Fetch history from the 'pages' table including the new columns
    $query = 'SELECT url, page_speed, meta_description, image_count, keyword_coverage, broken_links, created_at FROM pages ORDER BY created_at DESC LIMIT 10';
    $result = pg_query($db_connection, $query);
    // Check if the query was successful before fetching results.
    if ($result) {
        return pg_fetch_all($result);
    }
    return []; // Return an empty array on failure.
}

$analysis_result = null;
if (isset($_POST['url']) && !empty($_POST['url'])) {
    $url = $_POST['url'];
    $analysis_result = analyze_url($url, $db_connection);
}

$history = get_history($db_connection);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>SEO Audit Tool</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <h1>SEO Audit Tool</h1>
        <form action="index.php" method="post">
            <input type="text" name="url" placeholder="Enter a website URL" required>
            <button type="submit">Analyze</button>
        </form>

        <?php if ($analysis_result): ?>
        <div class="results">
            <h2>Analysis for <?= htmlspecialchars($analysis_result['url']) ?></h2>
            <table>
                <tr>
                    <th>Metric</th>
                    <th>Result</th>
                    <th>Advice</th>
                </tr>
                <tr>
                    <td>Page Speed (s)</td>
                    <td><?= round($analysis_result['page_speed'], 2) ?></td>
                    <td>Aim for a page speed of less than 2 seconds.</td>
                </tr>
                <tr>
                    <td>Meta Description</td>
                    <td><?= htmlspecialchars($analysis_result['meta_description']) ?></td>
                    <td>Consider writing a meta description that is between 50 and 160 characters long.</td>
                </tr>
                <tr>
                    <td>Image Count</td>
                    <td><?= $analysis_result['image_count'] ?></td>
                    <td>Review images to ensure they are optimized for web.</td>
                </tr>
                <tr>
                    <td>Keyword Coverage</td>
                    <td><?= round($analysis_result['keyword_coverage'], 2) ?>%</td>
                    <td>Aim for higher keyword coverage by including relevant terms in your content.</td>
                </tr>
                <tr>
                    <td>Broken Links</td>
                    <td><?= $analysis_result['broken_links'] ?></td>
                    <td>Fix any broken links to improve user experience and SEO.</td>
                </tr>
            </table>
        </div>
        <?php endif; ?>

        <div class="history">
            <h2>History</h2>
            <table>
                <tr>
                    <th>URL</th>
                    <th>Page Speed (s)</th>
                    <th>Meta Description</th>
                    <th>Image Count</th>
                    <th>Keyword Coverage (%)</th>
                    <th>Broken Links</th>
                    <th>Date</th>
                </tr>
                <?php if (!empty($history)): ?>
                <?php foreach ($history as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['url']) ?></td>
                    <td><?= round($row['page_speed'], 2) ?></td>
                    <td><?= htmlspecialchars($row['meta_description']) ?></td>
                    <td><?= $row['image_count'] ?></td>
                    <td><?= round($row['keyword_coverage'], 2) ?></td>
                    <td><?= $row['broken_links'] ?></td>
                    <td><?= $row['created_at'] ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </table>
        </div>
    </div>
</body>

</html>