<?php
class Api_ClinicController {

    public function index(): void {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $m = new Clinic();
            $data = $m->all(50);
            echo json_encode($data, JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => 'server_error'], JSON_UNESCAPED_UNICODE);
        }
    }


    /**
     * Convert Vietnamese (and general UTF-8) names to simple slugs that
     * match front-end chips, e.g. "Khám bệnh" -> "kham-benh".
     */
    private function slugify(string $name): string {
        $s = $name;
        // remove accents
        if (function_exists('transliterator_transliterate')) {
            $s = transliterator_transliterate('Any-Latin; Latin-ASCII', $s);
        } else {
            $s = iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$s) ?: $s;
        }
        $s = strtolower($s);
        $s = preg_replace('/[^a-z0-9]+/','-',$s);
        $s = trim($s,'-');
        // small aliases for common categories
        $map = [
            'kham' => 'kham-benh',
            'kham-benh' => 'kham-benh',
            'tiem' => 'tiem-phong',
            'tiem-phong' => 'tiem-phong',
            'spa' => 'spa', 'grooming' => 'spa', 'spa-grooming' => 'spa',
            'khach-san' => 'khach-san', 'luu-tru' => 'khach-san',
            'phau-thuat' => 'phau-thuat',
            'khac' => 'khac'
        ];
        return $map[$s] ?? $s;
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
        if (!isset($_GET['id'])) { $this->json(['error'=>'Missing id'],400); return; }
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
        if (!isset($_GET['id'])) { $this->json(['error'=>'Missing id'],400); return; }
        $st = $this->db->prepare('UPDATE clinic_center SET deleted_at = NOW() WHERE id = ?');
        $st->execute([$_GET['id']]);
        $this->json(['message'=>'Clinic deleted']);
    }
    private function json($data, int $code=200){ http_response_code($code); echo json_encode($data, JSON_UNESCAPED_UNICODE); }
}
