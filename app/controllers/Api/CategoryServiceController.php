<?php
class Api_CategoryServiceController {
    private PDO $db;
    public function __construct(){ $this->db = Database::pdo(); }
    public function handle(): void {
        $m = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        switch ($m){
            case 'GET': $this->get(); break;
            case 'POST': $this->post(); break;
            case 'PUT': $this->put(); break;
            case 'DELETE': $this->delete(); break;
            default: $this->json(['error'=>'Method not allowed'],405);
        }
    }
    private function get(): void {
        header('Content-Type: application/json; charset=utf-8');
        if (isset($_GET['id'])){
            $st = $this->db->prepare('SELECT * FROM category_service WHERE id = ? AND deleted_at IS NULL');
            $st->execute([$_GET['id']]); echo json_encode($st->fetch(PDO::FETCH_ASSOC)); return;
        }
        $q = $this->db->query('SELECT * FROM category_service WHERE deleted_at IS NULL');
        echo json_encode($q->fetchAll(PDO::FETCH_ASSOC));
    }
    private function post(): void {
        header('Content-Type: application/json; charset=utf-8');
        $d = json_decode(file_get_contents('php://input'), true) ?: [];
        $st = $this->db->prepare('INSERT INTO category_service (name, description, created_at, updated_at) VALUES (:name,:description,NOW(),NOW())');
        $st->execute([':name'=>$d['name']??null, ':description'=>$d['description']??null]);
        $this->json(['message'=>'Thêm service thành công']);
    }
    private function put(): void {
        header('Content-Type: application/json; charset=utf-8');
        if (!isset($_GET['id'])) return $this->json(['error'=>'Missing id'],400);
        $d = json_decode(file_get_contents('php://input'), true) ?: [];
        $st = $this->db->prepare('UPDATE category_service SET name=:name, description=:description, updated_at=NOW() WHERE id=:id');
        $st->execute([':name'=>$d['name']??null, ':description'=>$d['description']??null, ':id'=>$_GET['id']]);
        $this->json(['message'=>'Cập nhật service thành công']);
    }
    private function delete(): void {
        header('Content-Type: application/json; charset=utf-8');
        if (!isset($_GET['id'])) return $this->json(['error'=>'Missing id'],400);
        if (isset($_GET['force']) && $_GET['force']==='true'){
            $st = $this->db->prepare('DELETE FROM category_service WHERE id=:id');
            $st->execute([':id'=>$_GET['id']]);
            return $this->json(['message'=>'Xóa CỨNG service thành công']);
        }
        $st = $this->db->prepare('UPDATE category_service SET deleted_at=NOW() WHERE id=:id');
        $st->execute([':id'=>$_GET['id']]);
        $this->json(['message'=>'Xóa MỀM service thành công']);
    }
    private function json($data, int $code=200){ http_response_code($code); echo json_encode($data, JSON_UNESCAPED_UNICODE); }
}

