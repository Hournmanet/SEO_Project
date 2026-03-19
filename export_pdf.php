<?php
require_once 'db.php';
require_once 'vendor/autoload.php';

// Function to remove emojis and special symbols
function remove_emojis($string) {
    // Match Emoticons
    $regex_emoticons = '/[\x{1F600}-\x{1F64F}]/u';
    $clear_string = preg_replace($regex_emoticons, '', $string);

    // Match Miscellaneous Symbols and Pictographs
    $regex_symbols = '/[\x{1F300}-\x{1F5FF}]/u';
    $clear_string = preg_replace($regex_symbols, '', $clear_string);

    // Match Transport And Map Symbols
    $regex_transport = '/[\x{1F680}-\x{1F6FF}]/u';
    $clear_string = preg_replace($regex_transport, '', $clear_string);

    // Match Miscellaneous Symbols
    $regex_misc = '/[\x{2600}-\x{26FF}]/u';
    $clear_string = preg_replace($regex_misc, '', $clear_string);

    // Match Dingbats
    $regex_dingbats = '/[\x{2700}-\x{27BF}]/u';
    $clear_string = preg_replace($regex_dingbats, '', $clear_string);

    return $clear_string;
}

if (isset($_POST['report_id'])) {
    $report_id = $_POST['report_id'];

    // Fetch the specific report from the database
    $query = 'SELECT * FROM pages WHERE id = $1';
    $result = pg_query_params($db_connection, $query, [$report_id]);
    $report_data = pg_fetch_assoc($result);

    if ($report_data) {
        // Clean data before putting it into PDF
        $clean_meta_description = remove_emojis($report_data['meta_description']);
        $clean_url = remove_emojis($report_data['url']);

        // ====================================================================
        // FIX FOR KHMER/UNICODE CHARACTERS
        // ====================================================================

        // 1. Initialize mPDF in UTF-8 mode and set a default Unicode font
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'default_font' => 'dejavusans'
        ]);

        // 2. Enable auto-language detection to find the best font (like for Khmer)
        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont = true;

        // 3. Build the HTML content for the PDF with CSS for fonts
        $html = '
        <style>
            body {
                font-family: "dejavusans", sans-serif; /* Use a font that supports Unicode */
            }
            table {
                width: 100%;
                border-collapse: collapse;
            }
            th, td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: left;
            }
            th {
                background-color: #f2f2f2;
            }
        </style>';

        $html .= '<h1>SEO Analysis Report</h1>';
        $html .= '<p><strong>URL:</strong> ' . htmlspecialchars($clean_url) . '</p>';
        $html .= '<p><strong>Date:</strong> ' . $report_data['created_at'] . '</p>';
        $html .= '<hr>';
        $html .= '<h2>Overall SEO Score: ' . $report_data['seo_score'] . ' / 100</h2>';
        $html .= '<table>';
        $html .= '<tr><th>Metric</th><th>Result</th></tr>';
        $html .= '<tr><td>Page Speed (s)</td><td>' . round($report_data['page_speed'], 2) . '</td></tr>';
        $html .= '<tr><td>Meta Description</td><td>' . htmlspecialchars($clean_meta_description) . '</td></tr>';
        $html .= '<tr><td>Image Count</td><td>' . $report_data['image_count'] . '</td></tr>';
        $html .= '<tr><td>Keyword Coverage</td><td>' . round($report_data['keyword_coverage'], 2) . '%</td></tr>';
        $html .= '<tr><td>Broken Links</td><td>' . $report_data['broken_links'] . '</td></tr>';
        $html .= '<tr><td>Images Missing &apos;alt&apos;</td><td>' . ($report_data['missing_alt_tags'] ? count(explode(',', $report_data['missing_alt_tags'])) : 0) . '</td></tr>';
        $html .= '</table>';

        if (!empty($report_data['missing_alt_tags'])) {
            $html .= '<h3>Images Missing Alt Tags:</h3><ul>';
            $missing_alts = explode(', ', $report_data['missing_alt_tags']);
            foreach ($missing_alts as $alt) {
                $html .= '<li>' . htmlspecialchars($alt) . '</li>';
            }
            $html .= '</ul>';
        }

        // Write HTML to PDF
        $mpdf->WriteHTML($html);

        // Output the PDF to the browser for download
        $mpdf->Output('SEO_Report_' . date('Y-m-d') . '.pdf', 'D');
        exit;
    }
}
header('Location: index.php');
exit;
?>