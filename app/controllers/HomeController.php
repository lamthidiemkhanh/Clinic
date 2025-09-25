<?php
class HomeController {
    public function index(): void {
        $page = max(1, (int)($_GET['p'] ?? 1));
        $perPage = max(1, min(12, (int)($_GET['per_page'] ?? 6)));
        $keyword = trim((string)($_GET['q'] ?? ''));

        try {
            $clinicModel = new Clinic();
            $result = $clinicModel->paginate($page, $perPage, $keyword);
        } catch (Throwable $e) {
            $result = ['data' => [], 'pagination' => ['page' => $page, 'perPage' => $perPage, 'total' => 0, 'pages' => 1, 'keyword' => $keyword]];
        }

        view('home/index', [
            'title'  => 'Trang chủ',
            'pageId' => 'home-page',
            'clinics'=> $result['data'],
            'pagination' => $result['pagination'],
        ]);
    }
}
