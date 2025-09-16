<?php
class Api_SeedClinicController {
    private ?PDO $db = null;
    public function __construct(){ try { $this->db = Database::pdo(); } catch (Throwable $e) { $this->db = null; } }
    public function handle(): void { $this->seed(); }
    private function seed(): void {
        header('Content-Type: application/json; charset=utf-8');
        if (!$this->db) { http_response_code(500); echo json_encode(['error'=>'DB connection failed']); return; }
        try {
            $this->db->exec("CREATE TABLE IF NOT EXISTS clinic_center (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(191) NOT NULL,
                is_verify TINYINT(1) DEFAULT 0,
                description TEXT NULL,
                phone VARCHAR(50) NULL,
                address VARCHAR(255) NULL,
                email VARCHAR(191) NULL,
                deleted_at TIMESTAMP NULL DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
            $count = (int)$this->db->query('SELECT COUNT(*) FROM clinic_center')->fetchColumn();
            if ($count === 0){
                $st = $this->db->prepare('INSERT INTO clinic_center (name,is_verify,description,phone,address,email) VALUES (?,?,?,?,?,?)');
                $rows = [
                    ['Phòng khám Thú y Khang Việt',1,'Dịch vụ thú y tổng quát','0900000001','Quận 1, TP.HCM','khangviet@example.com'],
                    ['PetCare Center',1,'Khám bệnh, tiêm phòng, grooming','0900000002','Cầu Giấy, Hà Nội','petcare@example.com'],
                    ['Happy Paw Clinic',0,'Khám – phẫu thuật – lưu trú','0900000003','Ngũ Hành Sơn, Đà Nẵng','happypaw@example.com'],
                ];
                foreach($rows as $r){ $st->execute($r); }
            }
            echo json_encode(['seeded'=>true, 'count'=>(int)$this->db->query('SELECT COUNT(*) FROM clinic_center')->fetchColumn()], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e){ http_response_code(500); echo json_encode(['error'=>$e->getMessage()]); }
    }
}
