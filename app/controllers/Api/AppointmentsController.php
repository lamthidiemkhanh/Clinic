<?php
class Api_AppointmentsController {
    public function handle(): void {
        header('Content-Type: application/json; charset=utf-8');
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if ($method === 'GET') return $this->get();
        if ($method === 'POST') return $this->post();
        http_response_code(405); echo json_encode(['error'=>'Method not allowed']);
    }
    private function get(): void {
        try { $data = (new Appointment())->recent(50); echo json_encode($data, JSON_UNESCAPED_UNICODE); }
        catch (Throwable $e){ http_response_code(500); echo json_encode(['error'=>$e->getMessage()]); }
    }
    private function post(): void {
        try {
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true);
            if (!$data) throw new Exception('Invalid JSON');
            // log to file for now (compatible with old endpoint)
            $line = json_encode(['ts'=>date('c')] + $data, JSON_UNESCAPED_UNICODE);
            file_put_contents(__DIR__.'/../../../appointments.log', $line.PHP_EOL, FILE_APPEND);
            echo json_encode(['message'=>'Tạo lịch hẹn thành công']);
        } catch (Throwable $e) { http_response_code(400); echo json_encode(['error'=>$e->getMessage()]); }
    }
}

