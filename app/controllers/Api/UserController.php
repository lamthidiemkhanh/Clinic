<?php
class Api_UserController {
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
            $st = $this->db->prepare('SELECT u.*, r.name AS role_name FROM user u JOIN role r ON u.role_id = r.id WHERE u.id = ? AND u.deleted_at IS NULL AND r.deleted_at IS NULL');
            $st->execute([$_GET['id']]); echo json_encode($st->fetch(PDO::FETCH_ASSOC)); return;
        }
        $q = $this->db->query('SELECT u.*, r.name AS role_name FROM user u JOIN role r ON u.role_id = r.id WHERE u.deleted_at IS NULL AND r.deleted_at IS NULL');
        echo json_encode($q->fetchAll(PDO::FETCH_ASSOC));
    }
    private function post(): void {
        $d = json_decode(file_get_contents('php://input'), true) ?: [];
        $sql = 'INSERT INTO user (name, birth_date, gender, phone, address, email, avatar, introduction, role_id, created_at, updated_at) VALUES (:name,:birth_date,:gender,:phone,:address,:email,:avatar,:introduction,:role_id,NOW(),NOW())';
        $st = $this->db->prepare($sql);
        $st->execute([
            ':name'=>$d['name']??null,
            ':birth_date'=>$d['birth_date']??null,
            ':gender'=>$d['gender']??null,
            ':phone'=>$d['phone']??null,
            ':address'=>$d['address']??null,
            ':email'=>$d['email']??null,
            ':avatar'=>$d['avatar']??null,
            ':introduction'=>$d['introduction']??null,
            ':role_id'=>$d['role_id']??null,
        ]);
        $id = $this->db->lastInsertId();
        $st = $this->db->prepare('SELECT u.*, r.name AS role_name FROM user u JOIN role r ON u.role_id = r.id WHERE u.id = ?');
        $st->execute([$id]); echo json_encode($st->fetch(PDO::FETCH_ASSOC));
    }
    private function put(): void {
        if (!isset($_GET['id'])) return $this->json(['error'=>'Missing id'],400);
        $d = json_decode(file_get_contents('php://input'), true) ?: [];
        $sql = 'UPDATE user SET name=:name, birth_date=:birth_date, gender=:gender, phone=:phone, address=:address, email=:email, avatar=:avatar, introduction=:introduction, role_id=:role_id, updated_at=NOW() WHERE id=:id';
        $st = $this->db->prepare($sql);
        $st->execute([
            ':name'=>$d['name']??null,
            ':birth_date'=>$d['birth_date']??null,
            ':gender'=>$d['gender']??null,
            ':phone'=>$d['phone']??null,
            ':address'=>$d['address']??null,
            ':email'=>$d['email']??null,
            ':avatar'=>$d['avatar']??null,
            ':introduction'=>$d['introduction']??null,
            ':role_id'=>$d['role_id']??null,
            ':id'=>$_GET['id'],
        ]);
        $st = $this->db->prepare('SELECT u.*, r.name AS role_name FROM user u JOIN role r ON u.role_id = r.id WHERE u.id = ?');
        $st->execute([$_GET['id']]); echo json_encode($st->fetch(PDO::FETCH_ASSOC));
    }
    private function delete(): void {
        if (!isset($_GET['id'])) return $this->json(['error'=>'Missing id'],400);
        if (isset($_GET['force']) && $_GET['force']==='true'){
            $st = $this->db->prepare('DELETE FROM user WHERE id = :id'); $st->execute([':id'=>$_GET['id']]);
            return $this->json(['message'=>'Xóa CỨNG user thành công']);
        }
        $st = $this->db->prepare('UPDATE user SET deleted_at = NOW() WHERE id = :id'); $st->execute([':id'=>$_GET['id']]);
        $this->json(['message'=>'Xóa MỀM user thành công']);
    }
    private function json($d, int $code=200){ http_response_code($code); echo json_encode($d, JSON_UNESCAPED_UNICODE); }
}

