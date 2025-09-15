<?php
class Api_PetController {
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
        if (isset($_GET['id'])){ $st = $this->db->prepare('SELECT * FROM pet WHERE id = ? AND deleted_at IS NULL'); $st->execute([$_GET['id']]); echo json_encode($st->fetch(PDO::FETCH_ASSOC)); return; }
        $q = $this->db->query('SELECT * FROM pet WHERE deleted_at IS NULL'); echo json_encode($q->fetchAll(PDO::FETCH_ASSOC));
    }
    private function post(): void {
        header('Content-Type: application/json; charset=utf-8');
        $d = json_decode(file_get_contents('php://input'), true) ?: [];
        $sql = 'INSERT INTO pet (name, year_of_birth, color, weight, gender, is_spayed_neutered, description, owner_id, animal_type_id, animal_breed_id, created_at, updated_at)
                VALUES (:name,:year_of_birth,:color,:weight,:gender,:is_spayed_neutered,:description,:owner_id,:animal_type_id,:animal_breed_id,NOW(),NOW())';
        $st = $this->db->prepare($sql);
        $st->execute([
            ':name'=>$d['name']??null,
            ':year_of_birth'=>$d['year_of_birth']??null,
            ':color'=>$d['color']??null,
            ':weight'=>$d['weight']??null,
            ':gender'=>$d['gender']??null,
            ':is_spayed_neutered'=>$d['is_spayed_neutered']??0,
            ':description'=>$d['description']??null,
            ':owner_id'=>$d['owner_id']??null,
            ':animal_type_id'=>$d['animal_type_id']??null,
            ':animal_breed_id'=>$d['animal_breed_id']??null,
        ]);
        $this->json(['message'=>'Thêm pet thành công']);
    }
    private function put(): void {
        header('Content-Type: application/json; charset=utf-8');
        if (!isset($_GET['id'])) return $this->json(['error'=>'Missing id'],400);
        $d = json_decode(file_get_contents('php://input'), true) ?: [];
        $sql = 'UPDATE pet SET name=:name, year_of_birth=:year_of_birth, color=:color, weight=:weight, gender=:gender, is_spayed_neutered=:is_spayed_neutered, description=:description, owner_id=:owner_id, animal_type_id=:animal_type_id, animal_breed_id=:animal_breed_id, updated_at=NOW() WHERE id=:id';
        $st = $this->db->prepare($sql);
        $st->execute([
            ':name'=>$d['name']??null,
            ':year_of_birth'=>$d['year_of_birth']??null,
            ':color'=>$d['color']??null,
            ':weight'=>$d['weight']??null,
            ':gender'=>$d['gender']??null,
            ':is_spayed_neutered'=>$d['is_spayed_neutered']??0,
            ':description'=>$d['description']??null,
            ':owner_id'=>$d['owner_id']??null,
            ':animal_type_id'=>$d['animal_type_id']??null,
            ':animal_breed_id'=>$d['animal_breed_id']??null,
            ':id'=>$_GET['id']
        ]);
        $this->json(['message'=>'Cập nhật pet thành công']);
    }
    private function delete(): void {
        header('Content-Type: application/json; charset=utf-8');
        if (!isset($_GET['id'])) return $this->json(['error'=>'Missing id'],400);
        if (isset($_GET['force']) && $_GET['force']==='true'){
            $st = $this->db->prepare('DELETE FROM pet WHERE id = :id'); $st->execute([':id'=>$_GET['id']]); return $this->json(['message'=>'Xóa CỨNG pet thành công']);
        }
        $st = $this->db->prepare('UPDATE pet SET deleted_at = NOW() WHERE id=:id'); $st->execute([':id'=>$_GET['id']]); $this->json(['message'=>'Xóa MỀM pet thành công']);
    }
    private function json($data, int $code=200){ http_response_code($code); echo json_encode($data, JSON_UNESCAPED_UNICODE); }
}

