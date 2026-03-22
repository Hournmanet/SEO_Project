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
        display: none;
        /* Hidden initially */
    }

    .serp-preview {
        max-width: 600px;
        font-family: arial, sans-serif;
    }

    .serp-title {
        color: #1a0dab;
        font-size: 20px;
        line-height: 1.3;
        margin-bottom: 3px;
    }

    .serp-url {
        color: #202124;
        font-size: 14px;
        padding-top: 1px;
        line-height: 1.3;
    }

    .serp-description {
        color: #4d5156;
        line-height: 1.58;
        font-size: 14px;
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
            <div class="row">
                <div class="col-md-8">
                    <label for="url-input" class="form-label">Website URL</label>
                    <input type="text" id="url-input" name="url" class="form-control"
                        placeholder="Enter a website URL (e.g., https://www.example.com)" required>
                </div>
                <div class="col-md-4">
                    <label for="keyword-input" class="form-label">Target Keyword (Optional)</label>
                    <input type="text" id="keyword-input" name="keyword" class="form-control"
                        placeholder="e.g., Laravel">
                </div>
            </div>
            <div class="mt-3 text-center">
                <button type="submit" class="btn btn-primary btn-lg px-5">Analyze</button>
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

            <!-- SERP Simulator Inputs (Editable) -->
            <div class="card mb-4 border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">SERP Simulator & Audit</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Target Keyword</label>
                                <input type="text" id="sim-keyword" class="form-control" placeholder="Focus keyword">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">SEO Title</label>
                                <input type="text" id="sim-title" class="form-control" placeholder="Enter SEO Title">
                                <div class="text-end small mt-1"><span id="sim-title-count">0</span>/60</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Meta Description</label>
                                <textarea id="sim-desc" class="form-control" rows="3"
                                    placeholder="Enter Meta Description"></textarea>
                                <div class="text-end small mt-1"><span id="sim-desc-count">0</span>/155</div>
                            </div>
                        </div>
                        <div class="col-md-6 border-start">
                            <h6 class="fw-bold">Google Preview</h6>
                            <div class="serp-preview border p-3 rounded bg-white mb-4">
                                <div class="serp-url text-success small mb-1" id="serp-url">https://example.com ▼</div>
                                <div class="serp-title text-primary h5 mb-1" id="serp-title"
                                    style="cursor: pointer; text-decoration: none;">Example Title</div>
                                <div class="serp-description text-muted small" id="serp-description">This is where your
                                    meta description will appear in search results...</div>
                            </div>

                            <h6 class="fw-bold">10-Point SEO Audit</h6>
                            <ul class="list-group list-group-flush small" id="audit-list">
                                <li class="list-group-item d-flex justify-content-between align-items-center audit-item py-1"
                                    data-id="title-length">
                                    Title length (30-60 characters)
                                    <span class="badge rounded-pill bg-secondary">-</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center audit-item py-1"
                                    data-id="desc-length">
                                    Description length (70-155 characters)
                                    <span class="badge rounded-pill bg-secondary">-</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center audit-item py-1"
                                    data-id="kw-title">
                                    Focus Keyword used in Title
                                    <span class="badge rounded-pill bg-secondary">-</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center audit-item py-1"
                                    data-id="kw-desc">
                                    Focus Keyword used in Description
                                    <span class="badge rounded-pill bg-secondary">-</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center audit-item py-1"
                                    data-id="kw-start">
                                    Title begins with Keyword
                                    <span class="badge rounded-pill bg-secondary">-</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center audit-item py-1"
                                    data-id="brand-divider">
                                    Contains Brand Divider (| or -)
                                    <span class="badge rounded-pill bg-secondary">-</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center audit-item py-1"
                                    data-id="readability">
                                    Readability: No excessive All-Caps
                                    <span class="badge rounded-pill bg-secondary">-</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center audit-item py-1"
                                    data-id="high-value">
                                    Includes High-Value Word (Best/Top/Guide)
                                    <span class="badge rounded-pill bg-secondary">-</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center audit-item py-1"
                                    data-id="unique">
                                    Meta Description is unique from Title
                                    <span class="badge rounded-pill bg-secondary">-</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center audit-item py-1"
                                    data-id="cta">
                                    Description ends with a Call-to-Action
                                    <span class="badge rounded-pill bg-secondary">-</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
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
                                <tr>
                                    <td>Page Speed (s)</td>
                                    <td id="res-speed"></td>
                                </tr>
                                <tr>
                                    <td>Meta Description</td>
                                    <td id="res-meta"></td>
                                </tr>
                                <tr>
                                    <td>Image Count</td>
                                    <td id="res-images"></td>
                                </tr>
                                <tr>
                                    <td>Keyword Coverage</td>
                                    <td id="res-keywords"></td>
                                </tr>
                                <tr>
                                    <td>Broken Links</td>
                                    <td id="res-broken"></td>
                                </tr>
                                <tr>
                                    <td>Images Missing 'alt'</td>
                                    <td id="res-alt-count"></td>
                                </tr>
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
                            <h6>H1 Tags:</h6>
                            <ul id="res-h1-list"></ul>
                            <h6>H2 Tags:</h6>
                            <ul id="res-h2-list"></ul>
                            <h6>H3 Tags:</h6>
                            <ul id="res-h3-list"></ul>
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
                                <thead>
                                    <tr>
                                        <th>URL</th>
                                        <th>Score</th>
                                        <th>Speed</th>
                                        <th>SSL</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
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
                                        <td><?= $row['has_ssl'] === 't' || $row['has_ssl'] === true ? 'Yes' : 'No' ?>
                                        </td>
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
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Live Audit Logic
    function updateAudit() {
        const title = document.getElementById('sim-title').value;
        const desc = document.getElementById('sim-desc').value;
        const keyword = document.getElementById('sim-keyword').value;

        // Update SERP Preview
        document.getElementById('serp-title').textContent = title || 'Example Title';
        document.getElementById('serp-description').textContent = desc ||
            'This is where your meta description will appear in search results...';
        document.getElementById('sim-title-count').textContent = title.length;
        document.getElementById('sim-desc-count').textContent = desc.length;

        const auditResults = {
            'title-length': title.length >= 30 && title.length <= 60,
            'desc-length': desc.length >= 70 && desc.length <= 155,
            'kw-title': keyword ? title.toLowerCase().includes(keyword.toLowerCase()) : false,
            'kw-desc': keyword ? desc.toLowerCase().includes(keyword.toLowerCase()) : false,
            'kw-start': keyword ? title.toLowerCase().startsWith(keyword.toLowerCase()) : false,
            'brand-divider': title.includes('|') || title.includes('-'),
            'readability': !(/[A-Z\s]{10,}/.test(title)) && title.length > 0,
            'high-value': /(Best|Top|Guide|Review|202[0-9])/i.test(title),
            'unique': title !== desc && title.length > 0,
            'cta': /(Learn|Buy|Get|Discover|Shop|Check|Try|Start|Join|Order)/i.test(desc)
        };

        for (const [id, passed] of Object.entries(auditResults)) {
            const item = document.querySelector(`.audit-item[data-id="${id}"]`);
            const badge = item.querySelector('.badge');
            if (passed) {
                badge.textContent = '✓';
                badge.className = 'badge rounded-pill bg-success';
            } else {
                badge.textContent = '✕';
                badge.className = 'badge rounded-pill bg-danger';
            }
        }
    }

    // Add event listeners for live updates
    ['sim-title', 'sim-desc', 'sim-keyword'].forEach(id => {
        document.getElementById(id).addEventListener('input', updateAudit);
    });

    // AJAX Form Submission
    document.getElementById('seo-form').addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent page reload

        const urlInput = document.getElementById('url-input').value;
        const keywordInput = document.getElementById('keyword-input').value;
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
        formData.append('keyword', keywordInput);

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

                document.getElementById('res-ssl').innerHTML = (data.has_ssl === 't' || data.has_ssl ===
                        true) ? '<span class="badge bg-success">Secure</span>' :
                    '<span class="badge bg-danger">Not Secure</span>';

                // Populate Simulator
                document.getElementById('sim-title').value = data.page_title || '';
                document.getElementById('sim-desc').value = data.meta_description !== 'Missing' ? data
                    .meta_description : '';
                document.getElementById('sim-keyword').value = data.keyword || '';
                document.getElementById('serp-url').textContent = data.url;

                // Initial Audit update
                updateAudit();

                if (data.og_image !== 'Missing') {
                    document.getElementById('res-og-image-container').innerHTML =
                        `<img src="${data.og_image}" class="img-fluid rounded" alt="Open Graph Image">`;
                } else {
                    document.getElementById('res-og-image-container').innerHTML =
                        `<div class="text-center p-3 bg-light">No og:image found</div>`;
                }
                document.getElementById('res-og-title').textContent = data.og_title !== 'Missing' ? data
                    .og_title : 'No Title Found';
                try {
                    document.getElementById('res-og-url').textContent = new URL(data.url).hostname;
                } catch (e) {}

                document.getElementById('res-speed').textContent = parseFloat(data.page_speed).toFixed(2);
                document.getElementById('res-meta').textContent = data.meta_description;
                document.getElementById('res-images').textContent = data.image_count;
                document.getElementById('res-keywords').textContent = parseFloat(data.keyword_coverage)
                    .toFixed(2) + '%';
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
                    document.getElementById('res-h1-warning').innerHTML =
                        `<div class="alert alert-danger">Warning: Page should have exactly one H1 tag. Found: ${data.h1_tags.length}.</div>`;
                } else {
                    document.getElementById('res-h1-warning').innerHTML = '';
                }

                const populateList = (id, arr) => {
                    document.getElementById(id).innerHTML = arr.map(item => `<li>${item}</li>`).join(
                        '');
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