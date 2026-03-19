<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

use Goutte\Client;

class SeoCrawler {

    public function analyze($url) {
        $client = new Client();
        $startTime = microtime(true);
        $crawler = $client->request('GET', $url);
        $endTime = microtime(true);

        $pageSpeed = $endTime - $startTime;

        $metaDescriptionNode = $crawler->filter('meta[name="description"]');
        $metaDescription = $metaDescriptionNode->count() > 0 ? $metaDescriptionNode->attr('content') : 'Missing';

        $keywords = ['seo', 'framework', 'optimization'];
        $bodyText = $crawler->filter('body')->text();
        $foundKeywords = array_filter($keywords, function($keyword) use ($bodyText) {
            return stripos($bodyText, $keyword) !== false;
        });
        $keywordCoverage = (count($foundKeywords) / count($keywords)) * 100;

        // $imageSize = 0;
        // $crawler->filter('img')->each(function ($node) use (&$imageSize) {
        //     $src = $node->attr('src');
        //     if ($src) {
        //         $headers = @get_headers($src, 1);
        //         if ($headers && isset($headers["Content-Length"])) {
        //             $imageSize += (int)$headers["Content-Length"];
        //         }
        //     }
        // });

        // $brokenLinks = 0;
        // $crawler->filter('a')->each(function ($node) use (&$brokenLinks) {
        //     $link = $node->attr('href');
        //     if ($link) {
        //         $headers = @get_headers($link, 1);
        //         if (!$headers || strpos($headers[0], '404') !== false) {
        //             $brokenLinks++;
        //         }
        //     }
        // });

        return [
            'url' => $url,
            'page_speed' => $pageSpeed,
            'meta_description' => $metaDescription,
            'keyword_coverage' => $keywordCoverage,
            'images_size_kb' => 0, // $imageSize / 1024,
            'broken_links' => 0 // $brokenLinks
        ];
    }

    public function optimize($data) {
        $data['page_speed'] = 'Aim for a page speed of less than 2 seconds.';
        if ($data['meta_description'] === 'Missing' || strlen($data['meta_description']) < 50) {
            $data['meta_description'] = 'Consider writing a meta description that is between 50 and 160 characters long.';
        }
        $data['keyword_coverage'] = 'Aim for 100% keyword coverage.';
        $data['images_size_kb'] = 'Compress images to reduce their size.';
        $data['broken_links'] = 'Fix any broken links.';
        return $data;
    }
}
?>