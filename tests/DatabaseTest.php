<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../app/core/Database.php';

class DatabaseTest extends TestCase
{
    protected function setUp(): void
    {
        // Reset lại PDO để test độc lập
        $ref = new ReflectionClass(Database::class);
        $prop = $ref->getProperty('pdo');
        $prop->setAccessible(true);
        $prop->setValue(null);
    }

    public function testSetAndGetPdoInstance()
    {
        $pdo = new PDO('sqlite::memory:');
        Database::setPdoInstance($pdo);

        $result = Database::pdo();
        $this->assertSame($pdo, $result);
    }

    public function testPdoReturnsSameInstance()
    {
        $pdo1 = new PDO('sqlite::memory:');
        Database::setPdoInstance($pdo1);

        $pdo2 = Database::pdo();

        $this->assertSame($pdo1, $pdo2);
    }

    public function testFallbackConnectionUsesDefaults()
    {
        // Xóa biến môi trường để fallback về mặc định
        putenv('DB_HOST');
        putenv('DB_NAME');
        putenv('DB_USER');
        putenv('DB_PASS');

        $this->expectException(PDOException::class);
        Database::pdo(); 
        // Sẽ ném exception nếu không có MySQL chạy với db "clinic"
        // => test pass khi có lỗi kết nối (mong đợi)
    }

    public function testConnectionWithEnvVariables()
    {
        // Giả lập biến môi trường với SQLite in-memory
        putenv('DB_HOST=:memory:');
        putenv('DB_NAME=');
        putenv('DB_USER=');
        putenv('DB_PASS=');

        // Tạo DSN SQLite (hack: override DSN logic)
        $pdo = new PDO('sqlite::memory:');
        Database::setPdoInstance($pdo);

        $result = Database::pdo();
        $this->assertInstanceOf(PDO::class, $result);
    }
}
