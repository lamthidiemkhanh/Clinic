<?php
class HomeController {
    public function index(): void {
        $clinics = [];
        try { $clinics = (new Clinic())->all(12); } catch (Throwable $e) { $clinics = []; }
        view('home/index', [ 'title' => 'Trang chủ', 'pageId' => 'home-page', 'clinics' => $clinics ]);
    }
}
