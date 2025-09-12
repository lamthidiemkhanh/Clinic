<?php
global $conn;
header("Content-Type: application/json");
require "db.php";

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET': // Lấy danh sách hoặc 1 role
        if (isset($_GET['id'])) {
            $stmt = $conn->prepare("SELECT * FROM role WHERE id = ? AND deleted_at IS NULL");
            $stmt->execute([$_GET['id']]);
            echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        } else {
            $stmt = $conn->query("SELECT * FROM role WHERE deleted_at IS NULL");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    case 'POST': // Thêm role mới
        $data = json_decode(file_get_contents("php://input"), true);

        $sql = "INSERT INTO role (name, created_at, updated_at) 
                VALUES (:name, NOW(), NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':name' => $data['name']
        ]);

        $id = $conn->lastInsertId();
        $stmt = $conn->prepare("SELECT * FROM role WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        break;

    case 'PUT': // Cập nhật role
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Thiếu id"]);
            exit;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        $sql = "UPDATE role SET 
                    name = :name,
                    updated_at = NOW()
                WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':name' => $data['name'],
            ':id' => $_GET['id']
        ]);

        $stmt = $conn->prepare("SELECT * FROM role WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        break;

    case 'DELETE': // Xóa mềm hoặc cứng
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Thiếu id"]);
            exit;
        }

        if (isset($_GET['force']) && $_GET['force'] == 'true') {
            $sql = "DELETE FROM role WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $_GET['id']]);
            echo json_encode(["message" => "Xóa CỨNG role thành công"]);
        } else {
            $sql = "UPDATE role SET deleted_at = NOW() WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $_GET['id']]);
            echo json_encode(["message" => "Xóa MỀM role thành công"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method không được hỗ trợ"]);
}
