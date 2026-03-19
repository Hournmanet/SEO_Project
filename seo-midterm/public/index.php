<?php
ini_set('max_execution_time', 300);
require_once __DIR__ . '/../src/Controllers/SeoController.php';
$controller = new SeoController();
$result = $controller->run('https://www.google.com');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SEO Midterm Demo</title>
    <!-- Link to external CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <h1>SEO Midterm Demo</h1>
    <h2>Website: <?= $result['before']['url'] ?></h2>

    <table>
        <tr>
            <th>Metric</th>
            <th>Before Optimization</th>
            <th>After Optimization</th>
        </tr>
        <tr>
            <td>Page Speed (s)</td>
            <td><?= $result['before']['page_speed'] ?></td>
            <td><?= $result['after']['page_speed'] ?></td>
        </tr>
        <tr>
            <td>Meta Description</td>
            <td><?= $result['before']['meta_description'] ?: "Missing" ?></td>
            <td><?= $result['after']['meta_description'] ?></td>
        </tr>
        <tr>
            <td>Keyword Coverage (%)</td>
            <td><?= $result['before']['keyword_coverage'] ?></td>
            <td><?= $result['after']['keyword_coverage'] ?></td>
        </tr>
        <tr>
            <td>Image Size (KB)</td>
            <td><?= $result['before']['images_size_kb'] ?></td>
            <td><?= $result['after']['images_size_kb'] ?></td>
        </tr>
        <tr>
            <td>Broken Links</td>
            <td><?= $result['before']['broken_links'] ?></td>
            <td><?= $result['after']['broken_links'] ?></td>
        </tr>
    </table>

    <!-- Link to external JS -->
    <script src="assets/js/script.js"></script>
</body>
</html>