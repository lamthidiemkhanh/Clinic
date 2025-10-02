<?php
use PHPUnit\Framework\TestCase;

class AppointmentTest extends TestCase
{
    private PDO $pdo;
    private Appointment $appointment;

    protected function setUp(): void
    {
        // SQLite memory thay DB thật
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Fake bảng
        $this->pdo->exec('CREATE TABLE appointments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            appointment_date TEXT,
            appointment_time TEXT,
            status TEXT,
            pet_name TEXT,
            owner_name TEXT,
            color TEXT,
            weight_gram INTEGER,
            birth_date TEXT,
            price REAL,
            service_id INTEGER,
            clinic_id INTEGER
        )');
        $this->pdo->exec('CREATE TABLE service (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT)');
        $this->pdo->exec('CREATE TABLE clinic_center (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT)');

        // Insert dữ liệu mẫu
        $this->pdo->exec("INSERT INTO service (name) VALUES ('Khám tổng quát')");
        $this->pdo->exec("INSERT INTO clinic_center (name) VALUES ('Phòng khám A')");
        $this->pdo->exec("INSERT INTO appointments 
            (appointment_date, appointment_time, status, pet_name, owner_name, color, weight_gram, birth_date, price, service_id, clinic_id) 
            VALUES ('2025-10-02','09:30:00','pending','Milo','Khánh','Trắng',5000,'2021-05-01',200000,1,1)");

        // Mock Database::pdo() trả về SQLite
        Database::setPdoInstance($this->pdo);

        // Tạo instance
        $this->appointment = new Appointment();
        $this->appointment->db = $this->pdo;
    }

    /** @test */
    public function testRecentFromLegacySchemaReturnsData()
    {
        $result = $this->appointment->recent(5);

        $this->assertNotEmpty($result);
        $this->assertEquals('Milo', $result[0]['pet_name']);
        $this->assertEquals('Khám tổng quát', $result[0]['service_name']);
        $this->assertEquals('Phòng khám A', $result[0]['center_name']);
        $this->assertEquals('Chờ xác nhận', $result[0]['status']); // translated
    }

    /** @test */
    public function testTranslateLegacyStatus()
    {
        $this->assertEquals('Chờ xác nhận', $this->invokePrivate('translateLegacyStatus', ['pending']));
        $this->assertEquals('Đã xác nhận', $this->invokePrivate('translateLegacyStatus', ['confirmed']));
        $this->assertEquals('Hoàn thành', $this->invokePrivate('translateLegacyStatus', ['completed']));
        $this->assertEquals('Đã hủy', $this->invokePrivate('translateLegacyStatus', ['cancelled']));
    }

    // Helper gọi private method
    private function invokePrivate($methodName, array $args)
    {
        $ref = new ReflectionClass(Appointment::class);
        $method = $ref->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($this->appointment, $args);
    }
}
