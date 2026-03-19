<?php
// Hide deprecated warnings
error_reporting(E_ALL & ~E_DEPRECATED);

require_once 'db.php';

function get_history($db_connection) {
    $query = 'SELECT url, page_speed, meta_description, image_count, keyword_coverage, broken_links, seo_score, has_ssl, created_at FROM pages ORDER BY created_at DESC LIMIT 10';
    $result = pg_query($db_connection, $query);
    if ($result) {
        return pg_fetch_all($result);
    }
    return [];
}

$history = get_history($db_connection);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced SEO Audit Tool</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Loading Spinner CSS */
        #loading-spinner {
            display: none;
            text-align: center;
            margin: 20px 0;
        }
        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
        #results-container {
            display: none; /* Hidden initially */
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="text-center mb-4">
            <h1>Advanced SEO Audit Tool</h1>
        </div>
        
        <!-- AJAX Form -->
        <form id="seo-form" class="mb-5">
            <div class="input-group">
                <input type="text" id="url-input" name="url" class="form-control" placeholder="Enter a website URL (e.g., https://www.example.com)" required>
                <button type="submit" class="btn btn-primary">Analyze</button>
            </div>
        </form>

        <!-- Loading Spinner -->
        <div id="loading-spinner">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Analyzing website... Please wait.</p>
        </div>

        <!-- Error Message Container -->
        <div id="error-message" class="alert alert-danger" style="display: none;"></div>

        <!-- Results Container (Populated by JS) -->
        <div id="results-container" class="results">
            <div class="alert alert-info" id="cache-notice" style="display: none;">
                Showing cached results from the last 10 minutes.
            </div>
            <div class="row">
                <!-- SEO Score Card -->
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <h5 class="card-title">Overall SEO Score</h5>
                            <p class="card-text score" id="res-score">0 / 100</p>
                            <form action="export_pdf.php" method="post">
                                <input type="hidden" name="report_id" id="export-report-id" value="">
                                <button type="submit" class="btn btn-success">Export to PDF</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- SSL & Social Preview Card -->
                <div class="col-md-8 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">Security & Social</h5>
                            <p><strong>SSL Security (HTTPS):</strong> <span id="res-ssl"></span></p>
                            <hr>
                            <h6 class="card-subtitle mb-2 text-muted">Social Share Preview</h6>
                            <div class="social-preview-card">
                                <div id="res-og-image-container"></div>
                                <div class="social-preview-content">
                                    <strong id="res-og-title"></strong>
                                    <small class="text-muted" id="res-og-url"></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Analysis & Headings -->
            <div class="row">
                <div class="col-md-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Core Web Vitals & Content</h5>
                            <table class="table table-striped">
                                <tr><td>Page Speed (s)</td><td id="res-speed"></td></tr>
                                <tr><td>Meta Description</td><td id="res-meta"></td></tr>
                                <tr><td>Image Count</td><td id="res-images"></td></tr>
                                <tr><td>Keyword Coverage</td><td id="res-keywords"></td></tr>
                                <tr><td>Broken Links</td><td id="res-broken"></td></tr>
                                <tr><td>Images Missing 'alt'</td><td id="res-alt-count"></td></tr>
                            </table>
                            <div id="res-alt-list-container"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Heading Audit (H1, H2, H3)</h5>
                            <div id="res-h1-warning"></div>
                            <h6>H1 Tags:</h6><ul id="res-h1-list"></ul>
                            <h6>H2 Tags:</h6><ul id="res-h2-list"></ul>
                            <h6>H3 Tags:</h6><ul id="res-h3-list"></ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart and History -->
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Page Speed History</h5>
                        <canvas id="pageSpeedChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Full History</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead><tr><th>URL</th><th>Score</th><th>Speed</th><th>SSL</th><th>Date</th></tr></thead>
                                <tbody>
                                    <?php if (!empty($history)): ?>
                                    <?php 
                                        $chart_labels = [];
                                        $chart_data = [];
                                        $chart_history = array_slice($history, 0, 5);
                                        foreach (array_reverse($chart_history) as $row) {
                                            $chart_labels[] = parse_url($row['url'], PHP_URL_HOST);
                                            $chart_data[] = round($row['page_speed'], 2);
                                        }
                                    ?>
                                    <?php foreach ($history as $row): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['url']) ?></td>
                                        <td><?= $row['seo_score'] ?></td>
                                        <td><?= round($row['page_speed'], 2) ?></td>
                                        <td><?= $row['has_ssl'] === 't' || $row['has_ssl'] === true ? 'Yes' : 'No' ?></td>
                                        <td><?= $row['created_at'] ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
    // Initialize Chart
    const ctx = document.getElementById('pageSpeedChart');
    let pageSpeedChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($chart_labels ?? []) ?>,
            datasets: [{
                label: 'Page Speed (s)',
                data: <?= json_encode($chart_data ?? []) ?>,
                backgroundColor: 'rgba(0, 123, 255, 0.5)',
                borderColor: 'rgba(0, 123, 255, 1)',
                borderWidth: 1
            }]
        },
        options: { scales: { y: { beginAtZero: true } } }
    });

    // AJAX Form Submission
    document.getElementById('seo-form').addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent page reload
        
        const urlInput = document.getElementById('url-input').value;
        const spinner = document.getElementById('loading-spinner');
        const resultsContainer = document.getElementById('results-container');
        const errorMsg = document.getElementById('error-message');
        
        // Show spinner, hide results and errors
        spinner.style.display = 'block';
        resultsContainer.style.display = 'none';
        errorMsg.style.display = 'none';

        // Prepare data
        const formData = new FormData();
        formData.append('url', urlInput);

        // Fetch API call to analyze.php
        fetch('analyze.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            spinner.style.display = 'none'; // Hide spinner

            if (data.error) {
                errorMsg.textContent = data.error;
                errorMsg.style.display = 'block';
                return;
            }

            // Populate UI with data
            document.getElementById('cache-notice').style.display = data.cached ? 'block' : 'none';
            document.getElementById('res-score').textContent = data.seo_score + ' / 100';
            document.getElementById('export-report-id').value = data.id;
            
            document.getElementById('res-ssl').innerHTML = (data.has_ssl === 't' || data.has_ssl === true) ? '<span class="badge bg-success">Secure</span>' : '<span class="badge bg-danger">Not Secure</span>';
            
            if (data.og_image !== 'Missing') {
                document.getElementById('res-og-image-container').innerHTML = `<img src="${data.og_image}" class="img-fluid rounded" alt="Open Graph Image">`;
            } else {
                document.getElementById('res-og-image-container').innerHTML = `<div class="text-center p-3 bg-light">No og:image found</div>`;
            }
            document.getElementById('res-og-title').textContent = data.og_title !== 'Missing' ? data.og_title : 'No Title Found';
            try { document.getElementById('res-og-url').textContent = new URL(data.url).hostname; } catch(e) {}

            document.getElementById('res-speed').textContent = parseFloat(data.page_speed).toFixed(2);
            document.getElementById('res-meta').textContent = data.meta_description;
            document.getElementById('res-images').textContent = data.image_count;
            document.getElementById('res-keywords').textContent = parseFloat(data.keyword_coverage).toFixed(2) + '%';
            document.getElementById('res-broken').textContent = data.broken_links;
            
            document.getElementById('res-alt-count').textContent = data.missing_alt_tags.length;
            let altListHtml = '';
            if (data.missing_alt_tags.length > 0) {
                altListHtml = '<ul class="missing-alt-list">';
                data.missing_alt_tags.forEach(src => altListHtml += `<li>${src}</li>`);
                altListHtml += '</ul>';
            }
            document.getElementById('res-alt-list-container').innerHTML = altListHtml;

            if (data.h1_tags.length !== 1) {
                document.getElementById('res-h1-warning').innerHTML = `<div class="alert alert-danger">Warning: Page should have exactly one H1 tag. Found: ${data.h1_tags.length}.</div>`;
            } else {
                document.getElementById('res-h1-warning').innerHTML = '';
            }
            
            const populateList = (id, arr) => {
                document.getElementById(id).innerHTML = arr.map(item => `<li>${item}</li>`).join('');
            };
            populateList('res-h1-list', data.h1_tags);
            populateList('res-h2-list', data.h2_tags);
            populateList('res-h3-list', data.h3_tags);

            // Show results
            resultsContainer.style.display = 'block';
            
            // Note: To update the chart and history table dynamically without reload, 
            // we would need to append the new data to the DOM and Chart instance here.
            // For simplicity in this step, they will update on the next full page load.
        })
        .catch(error => {
            spinner.style.display = 'none';
            errorMsg.textContent = 'An error occurred during analysis.';
            errorMsg.style.display = 'block';
            console.error('Error:', error);
        });
    });
</script>
</body>
</html>