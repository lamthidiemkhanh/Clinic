<?php
class Api_AnimalBreedController {
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
            $st = $this->db->prepare('SELECT * FROM animal_breed WHERE id = ? AND deleted_at IS NULL');
            $st->execute([$_GET['id']]); echo json_encode($st->fetch(PDO::FETCH_ASSOC)); return;
        }
        $q = $this->db->query('SELECT * FROM animal_breed WHERE deleted_at IS NULL');
        echo json_encode($q->fetchAll(PDO::FETCH_ASSOC));
    }
    private function post(): void {
        $d = json_decode(file_get_contents('php://input'), true) ?: [];
        $st = $this->db->prepare('INSERT INTO animal_breed (name, animal_type_id) VALUES (:name, :animal_type_id)');
        $st->execute([':name'=>$d['name'] ?? null, ':animal_type_id'=>$d['animal_type_id'] ?? null]);
        $this->json(['message'=>'Breed added successfully']);
    }
    private function put(): void {
        if (!isset($_GET['id'])) return $this->json(['error'=>'ID required'],400);
        $d = json_decode(file_get_contents('php://input'), true) ?: [];
        $st = $this->db->prepare('UPDATE animal_breed SET name=:name, animal_type_id=:animal_type_id, updated_at=NOW() WHERE id=:id');
        $st->execute([':name'=>$d['name'] ?? null, ':animal_type_id'=>$d['animal_type_id'] ?? null, ':id'=>$_GET['id']]);
        $this->json(['message'=>'Breed updated successfully']);
    }
    private function delete(): void {
        if (!isset($_GET['id'])) return $this->json(['error'=>'Missing id'],400);
        if (isset($_GET['force']) && $_GET['force']==='true'){
            $st = $this->db->prepare('DELETE FROM animal_breed WHERE id = :id'); $st->execute([':id'=>$_GET['id']]);
            return $this->json(['message'=>'Xóa breed vĩnh viễn']);
        }
        $st = $this->db->prepare('UPDATE animal_breed SET deleted_at = NOW() WHERE id = :id'); $st->execute([':id'=>$_GET['id']]);
        $this->json(['message'=>'Xóa breed (soft delete)']);
    }
    private function json($d, int $code=200){ http_response_code($code); echo json_encode($d, JSON_UNESCAPED_UNICODE); }
}

