<?php
class Appointment extends Model {
    private function tableExists(string $name): bool {
        $st = $this->db->prepare('SHOW TABLES LIKE :t');
        $st->execute([':t' => $name]);
        return (bool) $st->fetchColumn();
    }

    public function recent(int $limit = 20): array {
        if (!$this->tableExists('appointments')) {
            return $this->readFromLog($limit);
        }

        $columns = $this->getColumns();
        if (isset($columns['appointment_date'])) {
            return $this->recentFromLegacySchema($limit) ?: $this->readFromLog($limit);
        }

        return $this->recentFromExtendedSchema($limit) ?: $this->readFromLog($limit);
    }

    private function recentFromLegacySchema(int $limit): array {
        $sql = 'SELECT a.id,
                       a.appointment_date AS date,
                       DATE_FORMAT(a.appointment_time, "%H:%i") AS time,
                       a.status,
                       a.pet_name,
                       a.price,
                       s.name AS service_name,
                       cc.name AS center_name
                FROM appointments a
                LEFT JOIN service s ON a.service_id = s.id
                LEFT JOIN clinic_center cc ON a.clinic_id = cc.id
                ORDER BY a.appointment_date DESC, a.appointment_time DESC
                LIMIT :lim';
        $st = $this->db->prepare($sql);
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();
        $rows = $st->fetchAll();
        return array_map(function(array $row){
            return [
                'date' => $row['date'] ?? '',
                'time' => $row['time'] ?? '',
                'status' => $this->translateLegacyStatus($row['status'] ?? ''),
                'service_name' => $row['service_name'] ?? '',
                'price' => $row['price'] ?? 0,
                'center_name' => $row['center_name'] ?? '',
                'pet_name' => $row['pet_name'] ?? '',
            ];
        }, $rows ?: []);
    }

    private function recentFromExtendedSchema(int $limit): array {
        $hasServiceTable = $this->tableExists('service');
        $hasClinicTable = $this->tableExists('clinic_center');

        $columns = ['a.id', 'a.date', 'a.time', 'a.status', 'a.pet_name', 'a.pet_type', 'a.pet_type_label', 'a.email', 'a.price'];
        if ($hasServiceTable) {
            $columns[] = 's.name AS service_name';
        } else {
            $columns[] = 'a.service_name';
        }
        if ($hasClinicTable) {
            $columns[] = 'c.name AS center_name';
        } else {
            $columns[] = 'a.center_name';
        }

        $sql = 'SELECT ' . implode(', ', $columns) . ' FROM appointments a';
        if ($hasServiceTable) {
            $sql .= ' LEFT JOIN service s ON a.service_id = s.id';
        }
        if ($hasClinicTable) {
            $sql .= ' LEFT JOIN clinic_center c ON a.clinic_id = c.id';
        }
        $sql .= ' ORDER BY a.date DESC, a.time DESC LIMIT :lim';

        $st = $this->db->prepare($sql);
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }

    private function readFromLog(int $limit): array {
        $file = __DIR__ . '/../../appointments.log';
        if (!is_file($file)) {
            return [];
        }
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $items = [];
        foreach ($lines as $ln) {
            $obj = json_decode($ln, true);
            if (is_array($obj)) {
                $items[] = $obj;
            }
        }
        usort($items, function($a, $b){
            return strcmp($b['ts'] ?? '', $a['ts'] ?? '');
        });
        $out = [];
        foreach (array_slice($items, 0, $limit) as $it) {
            $out[] = [
                'date' => $it['date'] ?? '',
                'time' => $it['time'] ?? '',
                'status' => $it['status'] ?? 'Chờ xác nhận',
                'service_name' => $it['service_name'] ?? '',
                'price' => $it['price'] ?? 0,
                'center_name' => $it['center_name'] ?? '',
                'pet_name' => $it['pet_name'] ?? '',
                'pet_type' => $it['pet_type'] ?? '',
                'pet_type_label' => $it['pet_type_label'] ?? '',
                'email' => $it['email'] ?? ''
            ];
        }
        return $out;
    }

    private function getColumns(): array {
        $columns = [];
        $stmt = $this->db->query('SHOW COLUMNS FROM appointments');
        foreach ($stmt as $col) {
            $columns[$col['Field']] = $col;
        }
        return $columns;
    }

    private function translateLegacyStatus(string $status): string {
        $status = strtolower(trim($status));
        return match ($status) {
            'pending' => 'Chờ xác nhận',
            'confirmed' => 'Đã xác nhận',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
            default => $status,
        };
    }
}
