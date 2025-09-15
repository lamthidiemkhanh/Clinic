<?php
class NotificationsController {
    public function index(): void {
        // Try DB table 'notifications' if exists
        $items = [];
        try {
            $pdo = Database::pdo();
            $st = $pdo->prepare("SHOW TABLES LIKE 'notifications'");
            $st->execute();
            if ($st->fetchColumn()){
                $q = $pdo->query("SELECT id, title, type, icon, payload, created_at FROM notifications ORDER BY created_at DESC LIMIT 50");
                while($r=$q->fetch(PDO::FETCH_ASSOC)){
                    $payload = [];
                    if (!empty($r['payload'])){ $json = json_decode($r['payload'], true); if (is_array($json)) $payload = $json; }
                    $items[] = [
                        'title'=>$r['title'] ?? 'Thông báo',
                        'type'=>$r['type'] ?? 'info',
                        'icon'=>$r['icon'] ?? 'bell',
                        'payload'=>$payload,
                    ];
                }
            }
        } catch (Throwable $e) { /* ignore, fallback to JS/localStorage */ }

        view('notifications/index', [ 'title' => 'Thông báo', 'pageId' => 'notifications-page', 'serverNotifs' => $items ]);
    }
}
