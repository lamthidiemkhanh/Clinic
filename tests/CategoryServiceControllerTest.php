<?php
use PHPUnit\Framework\TestCase;

class CategoryServiceControllerTest extends TestCase
{
    private Api_CategoryServiceController $controller;
    private PDO $pdo;

    protected function setUp(): void
    {
        // Mock PDO bằng SQLite memory (không cần MySQL thật)
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Tạo bảng giả lập category_service
        $this->pdo->exec('CREATE TABLE category_service (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT,
            description TEXT,
            created_at TEXT,
            updated_at TEXT,
            deleted_at TEXT
        )');

        // Mock Database::pdo() để trả về SQLite PDO này
        Database::setPdoInstance($this->pdo);

        $this->controller = new Api_CategoryServiceController();
    }

    /** @test */
    public function get_returns_empty_list_initially()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = [];

        ob_start();
        $this->controller->handle();
        $output = ob_get_clean();

        $this->assertEquals('[]', $output);
    }

    /** @test */
    public function post_creates_new_category()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_GET = [];

        $payload = json_encode(['name' => 'Khám bệnh', 'description' => 'Dịch vụ khám thú y']);
        file_put_contents('php://memory', $payload);

        // Fake input stream
        stream_wrapper_unregister('php');
        stream_wrapper_register('php', TestStream::class);

        TestStream::$content = $payload;

        ob_start();
        $this->controller->handle();
        $output = ob_get_clean();

        stream_wrapper_restore('php');

        $this->assertStringContainsString('Thêm service thành công', $output);

        $row = $this->pdo->query('SELECT * FROM category_service')->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals('Khám bệnh', $row['name']);
    }

    /** @test */
    public function put_updates_existing_category()
    {
        // Insert data trước
        $this->pdo->exec("INSERT INTO category_service (name, description, created_at, updated_at) VALUES ('Cũ','Mô tả cũ',datetime(),datetime())");

        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $_GET = ['id' => 1];

        $payload = json_encode(['name' => 'Mới', 'description' => 'Mô tả mới']);
        stream_wrapper_unregister('php');
        stream_wrapper_register('php', TestStream::class);
        TestStream::$content = $payload;

        ob_start();
        $this->controller->handle();
        $output = ob_get_clean();

        stream_wrapper_restore('php');

        $this->assertStringContainsString('Cập nhật service thành công', $output);

        $row = $this->pdo->query('SELECT * FROM category_service WHERE id=1')->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals('Mới', $row['name']);
    }

    /** @test */
    public function delete_soft_deletes_category()
    {
        $this->pdo->exec("INSERT INTO category_service (name, description, created_at, updated_at) VALUES ('Xóa mềm','abc',datetime(),datetime())");

        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $_GET = ['id' => 1];

        ob_start();
        $this->controller->handle();
        $output = ob_get_clean();

        $this->assertStringContainsString('Xóa mềm service thành công', $output);

        $row = $this->pdo->query('SELECT * FROM category_service WHERE id=1')->fetch(PDO::FETCH_ASSOC);
        $this->assertNotNull($row['deleted_at']);
    }

    /** @test */
    public function delete_force_deletes_category()
    {
        $this->pdo->exec("INSERT INTO category_service (name, description, created_at, updated_at) VALUES ('Xóa cứng','abc',datetime(),datetime())");

        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $_GET = ['id' => 1, 'force' => 'true'];

        ob_start();
        $this->controller->handle();
        $output = ob_get_clean();

        $this->assertStringContainsString('Xóa cứng service thành công', $output);

        $row = $this->pdo->query('SELECT * FROM category_service WHERE id=1')->fetch(PDO::FETCH_ASSOC);
        $this->assertFalse($row); // Không còn dòng nào
    }
}

/**
 * TestStream: dùng để fake php://input
 */
class TestStream {
    public static string $content = '';
    private $index;

    public function stream_open($path, $mode, $options, &$opened_path) { $this->index = 0; return true; }
    public function stream_read($count) { $ret = substr(self::$content, $this->index, $count); $this->index += strlen($ret); return $ret; }
    public function stream_eof() { return $this->index >= strlen(self::$content); }
    public function stream_stat() { return []; }
}
