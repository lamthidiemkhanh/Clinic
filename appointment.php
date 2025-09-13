<?php
header('Content-Type: application/json');

// Appointment handler: 
//  - GET: return list of appointments from appointments.log (demo storage)
//  - POST: create appointment, append to log and optionally send email

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $file = __DIR__ . '/appointments.log';
    if (!file_exists($file)) {
        echo json_encode([]); // no data yet
        exit;
    }
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $items = [];
    foreach ($lines as $ln) {
        $obj = json_decode($ln, true);
        if (is_array($obj)) $items[] = $obj;
    }
    // sort newest first by ts
    usort($items, function($a,$b){ return strcmp($b['ts'] ?? '', $a['ts'] ?? ''); });
    echo json_encode($items, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($method === 'POST') {
    try {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (!$data) { throw new Exception('Invalid JSON'); }

        // Basic validation
        foreach (['center_id','service_id','date','time'] as $f) {
            if (empty($data[$f])) { throw new Exception('Missing field: '.$f); }
        }

        // Log appointment locally
        $line = json_encode(['ts'=>date('c')] + $data, JSON_UNESCAPED_UNICODE);
        file_put_contents(__DIR__.'/appointments.log', $line.PHP_EOL, FILE_APPEND);

        // Try sending email if provided
        $email = isset($data['email']) ? trim($data['email']) : '';
        if ($email !== '') {
            $subject = 'Xác nhận đặt lịch';
            $body = "Bạn đã đặt lịch thành công:\n" .
                    "Phòng khám: ".$data['center_name']."\n" .
                    "Dịch vụ: ".$data['service_name']."\n" .
                    "Thời gian: ".$data['time'].", ".$data['date']."\n" .
                    "Giá: ".$data['price']."\n";
            @mail($email, $subject, $body);
        }

        echo json_encode(['message' => 'Đặt lịch thành công']);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);

?>
