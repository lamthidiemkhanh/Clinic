<?php
class Api_ClinicController {

    public function index(): void {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $page = max(1, (int)($_GET['page_number'] ?? $_GET['p'] ?? 1));
            $perPage = max(1, min(50, (int)($_GET['per_page'] ?? 50)));
            $keyword = trim((string)($_GET['q'] ?? ''));

            $m = new Clinic();
            $result = $m->paginate($page, $perPage, $keyword);

            echo json_encode($result, JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }
}
