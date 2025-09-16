<?php
class Api_AnimalSpeciesController {
    private PDO $db;
    public function __construct(){ $this->db = Database::pdo(); }
    public function handle(): void {
        header('Content-Type: application/json; charset=utf-8');
        $m = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        switch ($m) {
            case 'GET': $this->get(); break;
            case 'POST': $this->post(); break;
            case 'PUT': $this->put(); break;
            case 'DELETE': $this->delete(); break;
            default: $this->json(['error'=>'Method not allowed'],405);
        }
    }
    private function get(): void {
        if (isset($_GET['id'])){
            $st = $this->db->prepare('SELECT * FROM animal_species WHERE id = ? AND deleted_at IS NULL');
            $st->execute([$_GET['id']]); echo json_encode($st->fetch(PDO::FETCH_ASSOC)); return;
        }
        $q = $this->db->query('SELECT * FROM animal_species WHERE deleted_at IS NULL');
        echo json_encode($q->fetchAll(PDO::FETCH_ASSOC));
    }
    private function post(): void {
        $d = json_decode(file_get_contents('php://input'), true) ?: [];
        $st = $this->db->prepare('INSERT INTO animal_species (name, created_at, updated_at) VALUES (:name, NOW(), NOW())');
        $st->execute([':name'=>$d['name'] ?? null]);
        $this->json(['message'=>'Thêm species thành công']);
    }
    private function put(): void {
        if (!isset($_GET['id'])) return $this->json(['error'=>'Missing id'],400);
        $d = json_decode(file_get_contents('php://input'), true) ?: [];
        $st = $this->db->prepare('UPDATE animal_species SET name=:name, updated_at=NOW() WHERE id=:id');
        $st->execute([':name'=>$d['name'] ?? null, ':id'=>$_GET['id']]);
        $this->json(['message'=>'Cập nhật species thành công']);
    }
    private function delete(): void {
        if (!isset($_GET['id'])) return $this->json(['error'=>'Missing id'],400);
        if (isset($_GET['force']) && $_GET['force']==='true'){
            $st = $this->db->prepare('DELETE FROM animal_species WHERE id=:id'); $st->execute([':id'=>$_GET['id']]);
            return $this->json(['message'=>'Xóa CỨNG species thành công']);
        }
        $st = $this->db->prepare('UPDATE animal_species SET deleted_at = NOW() WHERE id=:id'); $st->execute([':id'=>$_GET['id']]);
        $this->json(['message'=>'Xóa MỀM species thành công']);
    }
    private function json($d, int $code=200){ http_response_code($code); echo json_encode($d, JSON_UNESCAPED_UNICODE); }
}

