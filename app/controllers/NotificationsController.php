<?php
class NotificationsController {
    public function index(): void {
        $items = [];
        try {
            $pdo = Database::pdo();
            $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
            if ($this->tableExists($pdo, $driver)) {
                $query = $pdo->query("SELECT id, title, type, icon, payload, created_at FROM notifications ORDER BY created_at DESC LIMIT 50");
                while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                    $payload = [];
                    if (!empty($row['payload'])) {
                        $decoded = json_decode($row['payload'], true);
                        if (is_array($decoded)) {
                            $payload = $decoded;
                        }
                    }

                    $items[] = [
                        'title' => $row['title'] ?? 'Thong bao',
                        'type' => $row['type'] ?? 'info',
                        'icon' => $row['icon'] ?? 'bell',
                        'payload' => $payload,
                    ];
                }
            }
        } catch (Throwable $e) {
            // Fallback to client-side/localStorage rendering when DB not available
        }

        view('notifications/index', [
            'title' => 'Thong bao',
            'pageId' => 'notifications-page',
            'serverNotifs' => $items,
        ]);
    }

    private function tableExists(PDO $pdo, string $driver): bool {
        if ($driver === 'sqlite') {
            $stmt = $pdo->prepare("SELECT name FROM sqlite_master WHERE type = 'table' AND name = :name");
            $stmt->execute(['name' => 'notifications']);
            return (bool) $stmt->fetchColumn();
        }

        $stmt = $pdo->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :name LIMIT 1");
        $stmt->execute(['name' => 'notifications']);
        return (bool) $stmt->fetchColumn();
    }
}

