<?php
require_once __DIR__ . '/../Models/Page.php';
require_once __DIR__ . '/../Services/SeoCrawler.php';

class SeoController {
    private $pageModel;
    private $crawler;

    public function __construct() {
        $this->pageModel = new Page();
        $this->crawler = new SeoCrawler();
    }

    public function run($url) {
        // Step 1: Analyze
        $before = $this->crawler->analyze($url);
        $this->pageModel->save($before);

        // Step 2: Optimize
        $after = $this->crawler->optimize($before);
        // $this->pageModel->save($after);

        return ['before' => $before, 'after' => $after];
    }

    public function getAll() {
        return $this->pageModel->getAll();
    }
}
?>