<?php
class Api_AppointmentsController {
    public function handle(): void {
        header('Content-Type: application/json; charset=utf-8');
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if ($method === 'GET') { $this->get(); return; }
        if ($method === 'POST') { $this->post(); return; }
        http_response_code(405);
        echo json_encode(['error' => 'Phương thức không được hỗ trợ'], JSON_UNESCAPED_UNICODE);
    }

    private function get(): void {
        try {
            $data = (new Appointment())->recent(50);
            echo json_encode($data, JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    private function post(): void {
        try {
            $raw = file_get_contents('php://input');
            $data = json_decode($raw ?? '', true);
            if (!is_array($data)) {
                throw new Exception('Dữ liệu JSON không hợp lệ');
            }

            $date = trim((string)($data['date'] ?? ''));
            $time = trim((string)($data['time'] ?? ''));
            $petType = trim((string)($data['pet_type'] ?? ''));
            $petName = trim((string)($data['pet_name'] ?? ''));
            if ($date === '' || $time === '' || $petType === '' || $petName === '') {
                throw new Exception('Thiếu thông tin đặt lịch bắt buộc');
            }

            $pdo = Database::pdo();
            $columns = $this->getColumns($pdo);

            if (isset($columns['appointment_date'])) {
                $recordId = $this->insertLegacySchema($pdo, $data, $date, $time, $petType, $petName);
            } else {
                $this->ensureExtendedSchema($pdo, $columns);
                $recordId = $this->insertExtendedSchema($pdo, $data, $date, $time, $petType, $petName);
            }

            $record = ['id' => $recordId, 'date' => $date, 'time' => $time, 'pet_type' => $petType, 'pet_name' => $petName] + $data;
            $this->logBooking($record);

            echo json_encode(['message' => 'Đặt lịch thành công', 'id' => $recordId], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    private function insertLegacySchema(PDO $pdo, array $payload, string $date, string $time, string $petType, string $petName): int {
        $clinicId = $this->requireClinic($pdo, (int)($payload['center_id'] ?? 0));
        $serviceId = $this->resolveServiceId($pdo, $payload, $clinicId);
        $animalTypeId = $this->resolveAnimalTypeId($pdo, $petType);

        $stmt = $pdo->prepare('INSERT INTO appointments (animal_type_id, clinic_id, service_id, pet_name, appointment_date, appointment_time, price, status, created_at, updated_at)
                               VALUES (:animal_type_id, :clinic_id, :service_id, :pet_name, :appointment_date, :appointment_time, :price, :status, NOW(), NOW())');
        $stmt->execute([
            ':animal_type_id' => $animalTypeId,
            ':clinic_id' => $clinicId,
            ':service_id' => $serviceId,
            ':pet_name' => $petName,
            ':appointment_date' => $date,
            ':appointment_time' => $this->formatTimeForDb($time),
            ':price' => isset($payload['price']) ? (float)$payload['price'] : 0,
            ':status' => 'pending'
        ]);
        return (int)$pdo->lastInsertId();
    }

    private function insertExtendedSchema(PDO $pdo, array $payload, string $date, string $time, string $petType, string $petName): int {
        $centerId = (int)($payload['center_id'] ?? 0) ?: null;
        $serviceId = $this->resolveServiceId($pdo, $payload, $centerId);

        $stmt = $pdo->prepare('INSERT INTO appointments (clinic_id, center_id, center_name, service_id, service_name, price, date, time, status, pet_type, pet_type_label, pet_name, email, created_at)
                               VALUES (:clinic_id, :center_id, :center_name, :service_id, :service_name, :price, :date, :time, :status, :pet_type, :pet_type_label, :pet_name, :email, NOW())');
        $stmt->execute([
            ':clinic_id' => $centerId,
            ':center_id' => $centerId,
            ':center_name' => $payload['center_name'] ?? null,
            ':service_id' => $serviceId,
            ':service_name' => $payload['service_name'] ?? null,
            ':price' => isset($payload['price']) ? (float)$payload['price'] : 0,
            ':date' => $date,
            ':time' => $time,
            ':status' => 'pending',
            ':pet_type' => $petType,
            ':pet_type_label' => $payload['pet_type_label'] ?? null,
            ':pet_name' => $petName,
            ':email' => $payload['email'] ?? null,
        ]);
        return (int)$pdo->lastInsertId();
    }

    private function ensureExtendedSchema(PDO $pdo, array $columns): void {
        $pdo->exec('CREATE TABLE IF NOT EXISTS appointments (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            clinic_id INT NULL,
            center_id INT NULL,
            center_name VARCHAR(255) NULL,
            service_id INT NULL,
            service_name VARCHAR(255) NULL,
            price DECIMAL(12,2) NULL,
            date DATE NOT NULL,
            time VARCHAR(20) NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT "pending",
            pet_type VARCHAR(50) NOT NULL,
            pet_type_label VARCHAR(50) NULL,
            pet_name VARCHAR(150) NOT NULL,
            email VARCHAR(255) NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $columns = $columns ?: $this->getColumns($pdo);
        $alter = [];
        $add = function(string $name, string $definition) use (&$columns, &$alter): void {
            if (!isset($columns[$name])) {
                $alter[] = 'ADD COLUMN ' . $definition;
            }
        };
        $add('clinic_id', 'clinic_id INT NULL AFTER id');
        $add('center_id', 'center_id INT NULL AFTER clinic_id');
        $add('center_name', 'center_name VARCHAR(255) NULL AFTER center_id');
        $add('service_id', 'service_id INT NULL AFTER center_name');
        $add('service_name', 'service_name VARCHAR(255) NULL AFTER service_id');
        $add('price', 'price DECIMAL(12,2) NULL AFTER service_name');
        $add('status', 'status VARCHAR(20) NOT NULL DEFAULT "pending" AFTER time');
        $add('pet_type', 'pet_type VARCHAR(50) NOT NULL AFTER status');
        $add('pet_type_label', 'pet_type_label VARCHAR(50) NULL AFTER pet_type');
        $add('pet_name', 'pet_name VARCHAR(150) NOT NULL AFTER pet_type_label');
        $add('email', 'email VARCHAR(255) NULL AFTER pet_name');
        $add('created_at', 'created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER email');
        if ($alter) {
            $pdo->exec('ALTER TABLE appointments ' . implode(', ', $alter));
        }
    }

    private function getColumns(PDO $pdo): array {
        $columns = [];
        $stmt = $pdo->query('SHOW COLUMNS FROM appointments');
        if ($stmt) {
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
                $columns[$col['Field']] = $col;
            }
        }
        return $columns;
    }

    private function formatTimeForDb(string $time): string {
        if (preg_match('/^\d{2}:\d{2}$/', $time)) {
            return $time . ':00';
        }
        return $time;
    }

    private function requireClinic(PDO $pdo, int $clinicId): int {
        if ($clinicId <= 0) {
            throw new Exception('Thiếu thông tin phòng khám');
        }
        $stmt = $pdo->prepare('SELECT id FROM clinic_center WHERE id = :id');
        $stmt->execute([':id' => $clinicId]);
        if (!$stmt->fetchColumn()) {
            throw new Exception('Phòng khám không tồn tại');
        }
        return $clinicId;
    }

    private function resolveServiceId(PDO $pdo, array $payload, ?int $clinicId): int {
        $serviceId = (int)($payload['service_id'] ?? 0);
        if ($serviceId > 0 && $this->serviceExists($pdo, $serviceId)) {
            return $serviceId;
        }

        $serviceName = trim((string)($payload['service_name'] ?? ''));
        if ($serviceName === '') {
            throw new Exception('Thiếu thông tin dịch vụ');
        }

        $existing = $this->findServiceByName($pdo, $serviceName, $clinicId);
        if ($existing) {
            return (int)$existing;
        }

        if (!$clinicId) {
            $clinicId = $this->requireClinic($pdo, (int)($payload['center_id'] ?? 0));
        }

        $categoryId = $this->guessCategoryId($pdo, $serviceName);
        $stmt = $pdo->prepare('INSERT INTO service (name, price, description, center_id, category_service_id, created_at, updated_at)
                               VALUES (:name, :price, :description, :center_id, :category_service_id, NOW(), NOW())');
        $stmt->execute([
            ':name' => $serviceName,
            ':price' => isset($payload['price']) ? (float)$payload['price'] : 0,
            ':description' => $payload['service_description'] ?? '',
            ':center_id' => $clinicId,
            ':category_service_id' => $categoryId
        ]);
        return (int)$pdo->lastInsertId();
    }

    private function serviceExists(PDO $pdo, int $id): bool {
        $stmt = $pdo->prepare('SELECT id FROM service WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return (bool)$stmt->fetchColumn();
    }

    private function findServiceByName(PDO $pdo, string $name, ?int $clinicId): ?int {
        $sql = 'SELECT id FROM service WHERE name LIKE :name';
        $params = [':name' => $name];
        if ($clinicId) {
            $sql .= ' AND center_id = :center_id';
            $params[':center_id'] = $clinicId;
        }
        $sql .= ' ORDER BY id LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $id = $stmt->fetchColumn();
        return $id ? (int)$id : null;
    }

    private function guessCategoryId(PDO $pdo, string $serviceName): int {
        $map = [
            'spa' => 1,
            'groom' => 1,
            'khám' => 2,
            'kham' => 2,
            'tiêm' => 3,
            'tiem' => 3,
            'khách sạn' => 4,
            'khach san' => 4,
            'phẫu thuật' => 5,
            'phau thuat' => 5,
        ];
        $normalized = strtolower($serviceName);
        foreach ($map as $needle => $catId) {
            if (strpos($normalized, $needle) !== false) {
                return $catId;
            }
        }
        return 6; // Khác
    }

    private function resolveAnimalTypeId(PDO $pdo, string $petType): int {
        $target = $this->normalizeKey($petType);
        $map = [
            'dog' => ['cho', 'dog'],
            'cat' => ['meo', 'cat'],
            'bird' => ['chim', 'bird'],
            'other' => ['khac', 'other']
        ];
        $wanted = $map[$target] ?? [];
        $fallback = null;
        $stmt = $pdo->query('SELECT id, name FROM animal_types');
        foreach ($stmt as $row) {
            $id = (int)$row['id'];
            if ($fallback === null) {
                $fallback = $id;
            }
            $normalized = $this->normalizeKey($row['name'] ?? '');
            if ($wanted && in_array($normalized, $wanted, true)) {
                return $id;
            }
        }
        if ($fallback !== null) {
            return $fallback;
        }
        throw new Exception('Không tìm thấy loại thú nuôi phù hợp');
    }

    private function normalizeKey(string $value): string {
        $transformed = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if ($transformed === false) {
            $transformed = $value;
        }
        $transformed = strtolower(preg_replace('/[^a-z]/', '', $transformed));
        return $transformed;
    }

    private function logBooking(array $data): void {
        try {
            $line = json_encode(['ts' => date('c')] + $data, JSON_UNESCAPED_UNICODE);
            file_put_contents(__DIR__ . '/../../../appointments.log', $line . PHP_EOL, FILE_APPEND);
        } catch (Throwable $e) {
            // ignore logging errors
        }
    }
}
