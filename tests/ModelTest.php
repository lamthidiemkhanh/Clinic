<?php
use PHPUnit\Framework\TestCase;

class ModelTest extends TestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Tạo bảng test
        $this->pdo->exec("CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT)");

        $this->pdo->exec("INSERT INTO users (name) VALUES ('Alice')");
        $this->pdo->exec("INSERT INTO users (name) VALUES ('Bob')");

        Database::setPdoInstance($this->pdo);
    }

    /** @test */
    public function testQueryAllReturnsRows()
    {
        $dummyModel = new class extends Model {
            public function getAllUsers() {
                return $this->queryAll("SELECT * FROM users");
            }
        };

        $rows = $dummyModel->getAllUsers();

        $this->assertCount(2, $rows);
        $this->assertEquals('Alice', $rows[0]['name']);
    }

    /** @test */
    public function testQueryOneReturnsSingleRow()
    {
        $dummyModel = new class extends Model {
            public function getUserById($id) {
                return $this->queryOne("SELECT * FROM users WHERE id = :id", [':id' => $id]);
            }
        };

        $row = $dummyModel->getUserById(2);

        $this->assertNotNull($row);
        $this->assertEquals('Bob', $row['name']);
    }

    /** @test */
    public function testQueryOneReturnsNullWhenNotFound()
    {
        $dummyModel = new class extends Model {
            public function getUserById($id) {
                return $this->queryOne("SELECT * FROM users WHERE id = :id", [':id' => $id]);
            }
        };

        $row = $dummyModel->getUserById(99);

        $this->assertNull($row);
    }
}
