<?php
class SearchController {
    public function index(): void {
        $q = $_GET['q'] ?? '';
        if (is_string($q) && strtolower(trim($q)) === 'search') { $q = ''; }
        $clinics = [];
        try {
            $mdl = new Clinic();
            $clinics = $q !== '' ? $mdl->search($q, 50) : $mdl->all(50);
        } catch (Throwable $e) { $clinics = []; }
        view('search/index', [ 'title' => 'Tìm kiếm', 'pageId' => 'clinic-search-page', 'clinics' => $clinics, 'q' => $q ]);
    }
}
