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
        $clean_meta_description = remove_emojis($report_data['meta_description'] ?? 'Missing');
        $clean_url = remove_emojis($report_data['url']);
        $clean_title = remove_emojis($report_data['page_title'] ?? 'Missing');
        $keyword = $report_data['keyword'] ?? '';

        // --- 10-Point Audit Logic (Same as index.php) ---
        $title = $clean_title;
        $desc = $clean_meta_description;
        
        $auditResults = [
            ['label' => 'Title length (30-60 characters)', 'passed' => strlen($title) >= 30 && strlen($title) <= 60],
            ['label' => 'Description length (70-155 characters)', 'passed' => strlen($desc) >= 70 && strlen($desc) <= 155],
            ['label' => 'Focus Keyword used in Title', 'passed' => $keyword ? stripos($title, $keyword) !== false : false],
            ['label' => 'Focus Keyword used in Description', 'passed' => $keyword ? stripos($desc, $keyword) !== false : false],
            ['label' => 'Title begins with Keyword', 'passed' => $keyword ? stripos($title, $keyword) === 0 : false],
            ['label' => 'Contains Brand Divider (| or -)', 'passed' => strpos($title, '|') !== false || strpos($title, '-') !== false],
            ['label' => 'Readability: No excessive All-Caps', 'passed' => !preg_match('/[A-Z\s]{10,}/', $title) && strlen($title) > 0],
            ['label' => 'Includes High-Value Word (Best/Top/Guide)', 'passed' => preg_match('/(Best|Top|Guide|Review|202[0-9])/i', $title)],
            ['label' => 'Meta Description is unique from Title', 'passed' => $title !== $desc && strlen($title) > 0],
            ['label' => 'Description ends with a Call-to-Action', 'passed' => preg_match('/(Learn|Buy|Get|Discover|Shop|Check|Try|Start|Join|Order)/i', $desc)]
        ];

        // ====================================================================
        // FIX FOR KHMER/UNICODE CHARACTERS
        // ====================================================================

        // 1. Initialize mPDF in UTF-8 mode and set a default Unicode font
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 15,
            'margin_bottom' => 15,
            'default_font' => 'dejavusans'
        ]);

        // 2. Enable auto-language detection to find the best font (like for Khmer)
        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont = true;

        // 3. Build the HTML content for the PDF with CSS for fonts
        $html = '
        <style>
            body { font-family: "dejavusans", sans-serif; color: #333; line-height: 1.6; }
            .header { text-align: center; border-bottom: 2px solid #007bff; padding-bottom: 10px; margin-bottom: 20px; }
            .section-title { background: #f8f9fa; padding: 10px; border-left: 5px solid #007bff; margin: 20px 0 10px 0; font-size: 18px; font-weight: bold; }
            .score-box { text-align: center; padding: 20px; background: #e9ecef; border-radius: 10px; margin-bottom: 20px; }
            .score-value { font-size: 36px; font-weight: bold; color: #28a745; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            th, td { border: 1px solid #dee2e6; padding: 12px; text-align: left; }
            th { background-color: #f2f2f2; font-weight: bold; }
            .audit-item { padding: 8px; border-bottom: 1px solid #eee; display: block; }
            .status-pass { color: #28a745; font-weight: bold; }
            .status-fail { color: #dc3545; font-weight: bold; }
            .preview-box { border: 1px solid #ddd; padding: 15px; border-radius: 5px; background: #fff; }
            .preview-title { color: #1a0dab; font-size: 18px; margin-bottom: 5px; }
            .preview-url { color: #006621; font-size: 14px; margin-bottom: 5px; }
            .preview-desc { color: #545454; font-size: 14px; }
        </style>';

        $html .= '<div class="header">';
        $html .= '<h1>SEO Analysis Report</h1>';
        $html .= '<p>Generated on: ' . date('F j, Y, g:i a', strtotime($report_data['created_at'])) . '</p>';
        $html .= '</div>';

        $html .= '<div class="score-box">';
        $html .= '<div>Overall SEO Score</div>';
        $html .= '<div class="score-value">' . $report_data['seo_score'] . ' / 100</div>';
        $html .= '</div>';

        $html .= '<div class="section-title">General Information</div>';
        $html .= '<table>';
        $html .= '<tr><th width="30%">Target URL</th><td>' . htmlspecialchars($clean_url) . '</td></tr>';
        if ($keyword) {
            $html .= '<tr><th>Target Keyword</th><td><strong>' . htmlspecialchars($keyword) . '</strong></td></tr>';
        }
        $html .= '<tr><th>Analysis Date</th><td>' . $report_data['created_at'] . '</td></tr>';
        $html .= '</table>';

        $html .= '<div class="section-title">Search Engine Preview</div>';
        $html .= '<div class="preview-box">';
        $html .= '<div class="preview-url">' . htmlspecialchars($clean_url) . ' ▼</div>';
        $html .= '<div class="preview-title">' . htmlspecialchars($clean_title) . '</div>';
        $html .= '<div class="preview-desc">' . htmlspecialchars($clean_meta_description) . '</div>';
        $html .= '</div>';
        $html .= '<p><small>Title Length: ' . strlen($title) . ' chars | Description Length: ' . strlen($desc) . ' chars</small></p>';

        $html .= '<div class="section-title">10-Point SEO Audit</div>';
        $html .= '<table>';
        $html .= '<tr><th>Audit Point</th><th width="15%">Status</th></tr>';
        foreach ($auditResults as $audit) {
            $statusClass = $audit['passed'] ? 'status-pass' : 'status-fail';
            $statusText = $audit['passed'] ? 'PASSED' : 'FAILED';
            $html .= '<tr>';
            $html .= '<td>' . $audit['label'] . '</td>';
            $html .= '<td class="' . $statusClass . '">' . $statusText . '</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';

        $html .= '<div class="section-title">Technical Metrics</div>';
        $html .= '<table>';
        $html .= '<tr><th width="40%">Metric</th><th>Result</th></tr>';
        $html .= '<tr><td>Page Speed (Load Time)</td><td>' . round($report_data['page_speed'], 2) . ' seconds</td></tr>';
        $html .= '<tr><td>SSL Security (HTTPS)</td><td>' . ($report_data['has_ssl'] == 't' ? 'Secure (Yes)' : 'Not Secure (No)') . '</td></tr>';
        $html .= '<tr><td>Total Images Found</td><td>' . $report_data['image_count'] . '</td></tr>';
        $html .= '<tr><td>Images Missing Alt Tags</td><td>' . ($report_data['missing_alt_tags'] ? count(explode(', ', $report_data['missing_alt_tags'])) : 0) . '</td></tr>';
        $html .= '<tr><td>Keyword Coverage</td><td>' . round($report_data['keyword_coverage'], 2) . '%</td></tr>';
        $html .= '<tr><td>Broken Links Detected</td><td>' . $report_data['broken_links'] . '</td></tr>';
        $html .= '</table>';

        if (!empty($report_data['h1_tags'])) {
            $html .= '<div class="section-title">Heading Audit (H1)</div>';
            $h1s = explode('|', $report_data['h1_tags']);
            $html .= '<ul>';
            foreach ($h1s as $h1) {
                $html .= '<li>' . htmlspecialchars(remove_emojis($h1)) . '</li>';
            }
            $html .= '</ul>';
        }

        $html .= '<div style="margin-top: 30px; text-align: center; color: #777; font-size: 12px; border-top: 1px solid #eee; padding-top: 10px;">';
        $html .= 'Report generated by Advanced SEO Audit Tool &copy; ' . date('Y');
        $html .= '</div>';

        // Write HTML to PDF
        $mpdf->WriteHTML($html);

        // Output the PDF to the browser for download
        $mpdf->Output('SEO_Report_' . date('Y-m-d_His') . '.pdf', 'D');
        exit;
    }
}
header('Location: index.php');
exit;
?>