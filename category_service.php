<?php
global $conn;
header("Content-Type: application/json");
require "db.php";

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET': // Lấy danh sách hoặc 1 service
        if (isset($_GET['id'])) {
            $stmt = $conn->prepare("SELECT * FROM category_service WHERE id = ? AND deleted_at IS NULL");
            $stmt->execute([$_GET['id']]);
            echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        } else {
            $stmt = $conn->query("SELECT * FROM category_service WHERE deleted_at IS NULL");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    case 'POST': // Thêm mới service
        $data = json_decode(file_get_contents("php://input"), true);
        $sql = "INSERT INTO category_service (name, description, created_at, updated_at) 
                VALUES (:name, :description, NOW(), NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':name' => $data['name'],
            ':description' => $data['description']
        ]);
        echo json_encode(["message" => "Thêm service thành công"]);
        break;

    case 'PUT': // Cập nhật service
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Thiếu id"]);
            exit;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $sql = "UPDATE category_service 
                SET name = :name, description = :description, updated_at = NOW() 
                WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':name' => $data['name'],
            ':description' => $data['description'],
            ':id' => $_GET['id']
        ]);
        echo json_encode(["message" => "Cập nhật service thành công"]);
        break;

    case 'DELETE': // Xóa mềm hoặc xóa cứng
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Thiếu id"]);
            exit;
        }

        if (isset($_GET['force']) && $_GET['force'] == 'true') {
            // Xóa cứng
            $sql = "DELETE FROM category_service WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $_GET['id']]);
            echo json_encode(["message" => "Xóa CỨNG service thành công"]);
        } else {
            // Xóa mềm
            $sql = "UPDATE category_service SET deleted_at = NOW() WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $_GET['id']]);
            echo json_encode(["message" => "Xóa MỀM service thành công"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method không được hỗ trợ"]);
}
