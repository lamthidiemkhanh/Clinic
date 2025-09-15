<?php
class Appointment extends Model {
    private function tableExists(string $name): bool {
        $st = $this->db->prepare("SHOW TABLES LIKE :t");
        $st->execute([':t'=>$name]);
        return (bool)$st->fetchColumn();
    }
    public function recent(int $limit = 20): array {
        // Prefer DB if table exists
        if ($this->tableExists('appointments')){
            $hasServices = $this->tableExists('services');
            $hasClinic = $this->tableExists('clinic_center') || $this->tableExists('clinics');
            $sql = "SELECT a.id, a.date, a.time, a.status";
            if ($hasServices) $sql .= ", s.name AS service_name, s.price"; else $sql .= ", a.service_name, a.price";
            if ($hasClinic) $sql .= ", c.name AS center_name"; else $sql .= ", a.center_name";
            $sql .= " FROM appointments a";
            if ($hasServices) $sql .= " LEFT JOIN services s ON a.service_id = s.id";
            if ($hasClinic) $sql .= " LEFT JOIN " . ($this->tableExists('clinic_center')? 'clinic_center':'clinics') . " c ON a.clinic_id = c.id";
            $sql .= " ORDER BY a.date DESC, a.time DESC LIMIT :lim";
            $st = $this->db->prepare($sql);
            $st->bindValue(':lim', $limit, PDO::PARAM_INT);
            $st->execute();
            $rows = $st->fetchAll();
            if ($rows) return $rows;
        }
        // Fallback: read demo log file
        $file = __DIR__ . '/../../appointments.log';
        if (!is_file($file)) return [];
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $items = [];
        foreach ($lines as $ln){ $obj = json_decode($ln, true); if (is_array($obj)) $items[] = $obj; }
        usort($items, function($a,$b){ return strcmp($b['ts']??'', $a['ts']??''); });
        // Normalize fields
        $out = [];
        foreach (array_slice($items, 0, $limit) as $it){
            $out[] = [
                'date'=>$it['date']??'',
                'time'=>$it['time']??'',
                'status'=>$it['status']??'Chờ xác nhận',
                'service_name'=>$it['service_name']??'',
                'price'=>$it['price']??0,
                'center_name'=>$it['center_name']??''
            ];
        }
        return $out;
    }
}
