<?php
class Api_ClinicController {
    private PDO $db;
    public function __construct(){ $this->db = Database::pdo(); }

    public function handle(): void {
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
        header('Content-Type: application/json; charset=utf-8');
        if (isset($_GET['id'])){
            $st = $this->db->prepare('SELECT * FROM clinic_center WHERE id = ?');
            $st->execute([$_GET['id']]);
            echo json_encode($st->fetch(PDO::FETCH_ASSOC));
            return;
        }
        $q = $this->db->query('SELECT * FROM clinic_center WHERE deleted_at IS NULL');
        echo json_encode($q->fetchAll(PDO::FETCH_ASSOC));
    }
    private function post(): void {
        header('Content-Type: application/json; charset=utf-8');
        $data = json_decode(file_get_contents('php://input'), true) ?: [];
        $sql = 'INSERT INTO clinic_center (name, is_verify, description, phone, address, email)
                VALUES (:name, :is_verify, :description, :phone, :address, :email)';
        $st = $this->db->prepare($sql);
        $st->execute([
            ':name'=>$data['name']??null,
            ':is_verify'=>$data['is_verify']??0,
            ':description'=>$data['description']??null,
            ':phone'=>$data['phone']??null,
            ':address'=>$data['address']??null,
            ':email'=>$data['email']??null,
        ]);
        $this->json(['message'=>'Clinic created','id'=>$this->db->lastInsertId()]);
    }
    private function put(): void {
        header('Content-Type: application/json; charset=utf-8');
        if (!isset($_GET['id'])) return $this->json(['error'=>'Missing id'],400);
        $data = json_decode(file_get_contents('php://input'), true) ?: [];
        $sql = 'UPDATE clinic_center SET name=:name, is_verify=:is_verify, description=:description, phone=:phone, address=:address, email=:email WHERE id=:id';
        $st = $this->db->prepare($sql);
        $st->execute([
            ':id'=>$_GET['id'],
            ':name'=>$data['name']??null,
            ':is_verify'=>$data['is_verify']??0,
            ':description'=>$data['description']??null,
            ':phone'=>$data['phone']??null,
            ':address'=>$data['address']??null,
            ':email'=>$data['email']??null,
        ]);
        $this->json(['message'=>'Clinic updated']);
    }
    private function delete(): void {
        header('Content-Type: application/json; charset=utf-8');
        if (!isset($_GET['id'])) return $this->json(['error'=>'Missing id'],400);
        $st = $this->db->prepare('UPDATE clinic_center SET deleted_at = NOW() WHERE id = ?');
        $st->execute([$_GET['id']]);
        $this->json(['message'=>'Clinic deleted']);
    }
    private function json($data, int $code=200){ http_response_code($code); echo json_encode($data, JSON_UNESCAPED_UNICODE); }
}

