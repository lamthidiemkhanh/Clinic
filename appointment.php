<?php
header('Content-Type: application/json');

// Simple appointment handler: logs to file and tries to send email
// Expects JSON: center_id, service_id, date, time, price, service_name, center_name, email

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

?>

