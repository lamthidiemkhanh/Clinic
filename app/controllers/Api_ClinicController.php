<?php
class Api_ClinicController {
    private ?PDO $db = null;
    public function __construct(){
        try { $this->db = Database::pdo(); }
        catch (Throwable $e) { $this->db = null; }
    }

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
        try {
            if (isset($_GET['id'])){
                if (!$this->tableExists('clinic_center')) { echo json_encode($this->sampleClinics()[0] ?? null, JSON_UNESCAPED_UNICODE); return; }
                $st = $this->db->prepare('SELECT * FROM clinic_center WHERE id = ?');
                $st->execute([$_GET['id']]);
                echo json_encode($st->fetch(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
                return;
            }
            if ($this->db && $this->tableExists('clinic_center')){
                // If service + category tables exist, enrich clinics with service categories
                $hasService = $this->tableExists('service');
                $hasCat = $this->tableExists('category_service');
                if ($hasService && $hasCat) {
                    $sql = "SELECT cc.id, cc.name, cc.description, cc.address, cc.is_verify,
                                   GROUP_CONCAT(DISTINCT cs.name SEPARATOR ',') AS categories
                            FROM clinic_center cc
                            LEFT JOIN service s ON s.center_id = cc.id AND s.deleted_at IS NULL
                            LEFT JOIN category_service cs ON cs.id = s.category_service_id AND cs.deleted_at IS NULL
                            WHERE cc.deleted_at IS NULL
                            GROUP BY cc.id, cc.name, cc.description, cc.address";
                    $st = $this->db->prepare($sql);
                    $st->execute();
                    $rows = $st->fetchAll(PDO::FETCH_ASSOC);
                    // Map category names to normalized slug string for front-end filtering
                    foreach ($rows as &$r) {
                        $cats = array_filter(array_map('trim', explode(',', (string)($r['categories'] ?? ''))));
                        $slugs = [];
                        foreach ($cats as $cname) {
                            $slugs[] = $this->slugify($cname);
                        }
                        $r['service_category'] = implode(' ', array_unique($slugs));
                        unset($r['categories']);
                    }
                    echo json_encode($rows, JSON_UNESCAPED_UNICODE);
                    return;
                } else {
                    $q = $this->db->query('SELECT id, name, description, address FROM clinic_center WHERE deleted_at IS NULL');
                    $rows = $q->fetchAll(PDO::FETCH_ASSOC);
                    echo json_encode($rows, JSON_UNESCAPED_UNICODE);
                    return;
                }
            }
            // Fallback if table not found
            echo json_encode($this->sampleClinics(), JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            // Fallback on any DB error
            echo json_encode($this->sampleClinics(), JSON_UNESCAPED_UNICODE);
        }
    }
    private function tableExists(string $name): bool {
        try {
            $st = $this->db->prepare('SHOW TABLES LIKE :t');
            $st->execute([':t'=>$name]);
            return (bool)$st->fetchColumn();
        } catch (Throwable $e){ return false; }
    }
    private function sampleClinics(): array {
        return [
            [ 'id'=>1, 'name'=>'Phòng khám Thú y Khang Việt', 'address'=>'Quận 1, TP.HCM', 'description'=>'Dịch vụ thú y tổng quát', 'logo'=>'public/img/clinic-center.png', 'rating'=>4.7 ],
            [ 'id'=>2, 'name'=>'PetCare Center', 'address'=>'Cầu Giấy, Hà Nội', 'description'=>'Khám bệnh, tiêm phòng, grooming', 'logo'=>'public/img/clinic-center.png', 'rating'=>4.5 ],
            [ 'id'=>3, 'name'=>'Happy Paw Clinic', 'address'=>'Ngũ Hành Sơn, Đà Nẵng', 'description'=>'Khám – phẫu thuật – lưu trú', 'logo'=>'public/img/clinic-center.png', 'rating'=>4.6 ],
        ];
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
