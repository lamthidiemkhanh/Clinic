<?php
class Api_ServiceController {
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
            $st = $this->db->prepare('SELECT * FROM service WHERE id = ? AND deleted_at IS NULL');
            $st->execute([$_GET['id']]);
            echo json_encode($st->fetch(PDO::FETCH_ASSOC)); return;
        }
        if (isset($_GET['center_id'])){
            $st = $this->db->prepare('SELECT * FROM service WHERE center_id = ? AND deleted_at IS NULL');
            $st->execute([$_GET['center_id']]);
            echo json_encode($st->fetchAll(PDO::FETCH_ASSOC)); return;
        }
        $q = $this->db->query('SELECT * FROM service WHERE deleted_at IS NULL');
        echo json_encode($q->fetchAll(PDO::FETCH_ASSOC));
    }
    private function post(): void {
        header('Content-Type: application/json; charset=utf-8');
        $d = json_decode(file_get_contents('php://input'), true) ?: [];
        $sql = 'INSERT INTO service (name, price, description, center_id, category_service_id, created_at, updated_at)
                VALUES (:name,:price,:description,:center_id,:category_service_id,NOW(),NOW())';
        $st = $this->db->prepare($sql);
        $st->execute([
            ':name'=>$d['name']??null,
            ':price'=>$d['price']??0,
            ':description'=>$d['description']??null,
            ':center_id'=>$d['center_id']??null,
            ':category_service_id'=>$d['category_service_id']??null,
        ]);
        $id = $this->db->lastInsertId();
        $st = $this->db->prepare('SELECT * FROM service WHERE id=?'); $st->execute([$id]);
        echo json_encode($st->fetch(PDO::FETCH_ASSOC));
    }
    private function put(): void {
        header('Content-Type: application/json; charset=utf-8');
        if (!isset($_GET['id'])) { $this->json(['error'=>'Missing id'],400); return; }
        $d = json_decode(file_get_contents('php://input'), true) ?: [];
        $sql = 'UPDATE service SET name=:name, price=:price, description=:description, center_id=:center_id, category_service_id=:category_service_id, updated_at=NOW() WHERE id=:id';
        $st = $this->db->prepare($sql);
        $st->execute([
            ':name'=>$d['name']??null,
            ':price'=>$d['price']??0,
            ':description'=>$d['description']??null,
            ':center_id'=>$d['center_id']??null,
            ':category_service_id'=>$d['category_service_id']??null,
            ':id'=>$_GET['id']
        ]);
        $st = $this->db->prepare('SELECT * FROM service WHERE id=?'); $st->execute([$_GET['id']]);
        echo json_encode($st->fetch(PDO::FETCH_ASSOC));
    }
    private function delete(): void {
        header('Content-Type: application/json; charset=utf-8');
        if (!isset($_GET['id'])) { $this->json(['error'=>'Missing id'],400); return; }
        if (isset($_GET['hard']) && $_GET['hard']==='true'){
            $st = $this->db->prepare('DELETE FROM service WHERE id = :id');
            $st->execute([':id'=>$_GET['id']]);
            $this->json(['message'=>'Xóa cứng service thành công']);
            return;
        }
        $st = $this->db->prepare('UPDATE service SET deleted_at=NOW() WHERE id=:id');
        $st->execute([':id'=>$_GET['id']]);
        $this->json(['message'=>'Xóa mềm service thành công']);
    }
    private function json($data, int $code=200){ http_response_code($code); echo json_encode($data, JSON_UNESCAPED_UNICODE); }
}

