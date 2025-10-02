<?php
require_once __DIR__ . '/../app/core/helpers.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/controllers/NotificationsController.php';
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class NotificationsControllerTest extends TestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec(
            "CREATE TABLE notifications (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT,
                type TEXT,
                icon TEXT,
                payload TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )"
        );

        Database::setPdoInstance($this->pdo);
    }

    protected function tearDown(): void
    {
        Database::setPdoInstance(null);
    }

    #[Test]
    public function testIndexWithNoNotifications(): void
    {
        $controller = new NotificationsController();

        ob_start();
        $controller->index();
        $output = ob_get_clean();

        $this->assertStringContainsString('Thong bao', $output);
    }

    #[Test]
    public function testIndexWithNotifications(): void
    {
        $payload = json_encode(['url' => '/profile']);
        $this->pdo->exec("INSERT INTO notifications (title, type, icon, payload) VALUES ('Chao mung', 'success', 'star', '$payload')");

        $controller = new NotificationsController();

        ob_start();
        $controller->index();
        $output = ob_get_clean();

        $this->assertStringContainsString('Thong bao', $output);
        $this->assertStringContainsString('Chao mung', $output);
        $this->assertStringContainsString('/profile', $output);
    }

    #[Test]
    public function testPayloadIsDecodedCorrectly(): void
    {
        $payload = json_encode(['link' => '/dashboard']);
        $this->pdo->exec("INSERT INTO notifications (title, type, icon, payload) VALUES ('Di toi dashboard', 'info', 'bell', '$payload')");

        $controller = new NotificationsController();

        ob_start();
        $controller->index();
        $output = ob_get_clean();

        $this->assertStringContainsString('Di toi dashboard', $output);
        $this->assertStringContainsString('/dashboard', $output);
    }
}
