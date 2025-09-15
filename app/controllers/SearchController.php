<?php
class SearchController {
    public function index(): void {
        view('search/index', [ 'title' => 'Tìm kiếm', 'pageId' => 'clinic-search-page' ]);
    }
}

