<?php
class Api_PetTypeServiceController {
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
            $sql = 'SELECT pts.*, a.name AS animal_types_name, s.name AS service_name
                    FROM pet_type_service pts
                    JOIN animal_types a ON pts.animal_type_id = a.id
                    JOIN service s ON pts.service_id = s.id
                    WHERE pts.id = ? AND pts.deleted_at IS NULL';
            $st = $this->db->prepare($sql); $st->execute([$_GET['id']]);
            echo json_encode($st->fetch(PDO::FETCH_ASSOC)); return;
        }
        $sql = 'SELECT pts.*, a.name AS animal_types_name, s.name AS service_name
                FROM pet_type_service pts
                JOIN animal_types a ON pts.animal_type_id = a.id
                JOIN service s ON pts.service_id = s.id
                WHERE pts.deleted_at IS NULL';
        $q = $this->db->query($sql); echo json_encode($q->fetchAll(PDO::FETCH_ASSOC));
    }
    private function post(): void {
        $d = json_decode(file_get_contents('php://input'), true) ?: [];
        $st = $this->db->prepare('INSERT INTO pet_type_service (animal_type_id, service_id, created_at, updated_at) VALUES (:animal_type_id, :service_id, NOW(), NOW())');
        $st->execute([':animal_type_id'=>$d['animal_type_id'] ?? null, ':service_id'=>$d['service_id'] ?? null]);
        $id = $this->db->lastInsertId();
        $sql = 'SELECT pts.*, a.name AS animal_types_name, s.name AS service_name
                FROM pet_type_service pts
                JOIN animal_types a ON pts.animal_type_id = a.id
                JOIN service s ON pts.service_id = s.id
                WHERE pts.id = ?';
        $st = $this->db->prepare($sql); $st->execute([$id]); echo json_encode($st->fetch(PDO::FETCH_ASSOC));
    }
    private function put(): void {
        if (!isset($_GET['id'])) return $this->json(['error'=>'Missing id'],400);
        $d = json_decode(file_get_contents('php://input'), true) ?: [];
        $st = $this->db->prepare('UPDATE pet_type_service SET animal_type_id=:animal_type_id, service_id=:service_id, updated_at=NOW() WHERE id=:id');
        $st->execute([':animal_type_id'=>$d['animal_type_id'] ?? null, ':service_id'=>$d['service_id'] ?? null, ':id'=>$_GET['id']]);
        $sql = 'SELECT pts.*, a.name AS animal_types_name, s.name AS service_name
                FROM pet_type_service pts
                JOIN animal_types a ON pts.animal_type_id = a.id
                JOIN service s ON pts.service_id = s.id
                WHERE pts.id = ?';
        $st = $this->db->prepare($sql); $st->execute([$_GET['id']]); echo json_encode($st->fetch(PDO::FETCH_ASSOC));
    }
    private function delete(): void {
        if (!isset($_GET['id'])) return $this->json(['error'=>'Missing id'],400);
        if (isset($_GET['force']) && $_GET['force']==='true'){
            $st = $this->db->prepare('DELETE FROM pet_type_service WHERE id = :id'); $st->execute([':id'=>$_GET['id']]);
            return $this->json(['message'=>'Xóa CỨNG thành công']);
        }
        $st = $this->db->prepare('UPDATE pet_type_service SET deleted_at = NOW() WHERE id = :id'); $st->execute([':id'=>$_GET['id']]);
        $this->json(['message'=>'Xóa MỀM thành công']);
    }
    private function json($d, int $code=200){ http_response_code($code); echo json_encode($d, JSON_UNESCAPED_UNICODE); }
}

