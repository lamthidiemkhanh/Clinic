<?php
use PHPUnit\Framework\TestCase;

class ClinicTest extends TestCase
{
    private PDO $pdo;
    private Clinic $clinic;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Tạo schema giả lập
        $this->pdo->exec("CREATE TABLE clinic_center (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT,
            description TEXT,
            address TEXT,
            phone TEXT,
            email TEXT,
            is_verify INTEGER,
            deleted_at TEXT
        )");

        $this->pdo->exec("CREATE TABLE service (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT,
            center_id INTEGER,
            category_service_id INTEGER,
            deleted_at TEXT
        )");

        $this->pdo->exec("CREATE TABLE category_service (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT,
            deleted_at TEXT
        )");

        $this->pdo->exec("CREATE TABLE clinic_animal (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            clinic_id INTEGER,
            animal_type_id INTEGER
        )");

        $this->pdo->exec("CREATE TABLE animal_types (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT
        )");

        // Dữ liệu mẫu
        $this->pdo->exec("INSERT INTO clinic_center (name, description, address, phone, email, is_verify) 
                          VALUES ('Phòng khám A', 'Khám tổng quát', 'Hà Nội', '0123456789', 'a@test.com', 1)");
        $this->pdo->exec("INSERT INTO category_service (name) VALUES ('Khám bệnh')");
        $this->pdo->exec("INSERT INTO service (name, center_id, category_service_id) VALUES ('Khám tổng quát', 1, 1)");
        $this->pdo->exec("INSERT INTO animal_types (name) VALUES ('Chó')");
        $this->pdo->exec("INSERT INTO clinic_animal (clinic_id, animal_type_id) VALUES (1, 1)");

        // Mock Database::pdo() trả về SQLite
        Database::setPdoInstance($this->pdo);

        $this->clinic = new Clinic();
        $this->clinic->db = $this->pdo;
    }

    /** @test */
    public function testFindClinicById()
    {
        $clinic = $this->clinic->find(1);

        $this->assertNotNull($clinic);
        $this->assertEquals('Phòng khám A', $clinic['name']);
        $this->assertEquals(1, $clinic['service_count']);
        $this->assertStringContainsString('Khám tổng quát', $clinic['services']);
        $this->assertStringContainsString('Chó', $clinic['pets']);
    }

    /** @test */
    public function testSearchClinicByKeyword()
    {
        $result = $this->clinic->search('Hà Nội', 10);

        $this->assertNotEmpty($result);
        $this->assertEquals('Phòng khám A', $result[0]['name']);
    }

    /** @test */
    public function testPaginateWithServiceFilter()
    {
        $result = $this->clinic->paginate(1, 10, '', ['service' => 'khám']);

        $this->assertNotEmpty($result['data']);
        $this->assertEquals('Phòng khám A', $result['data'][0]['name']);
    }
}
