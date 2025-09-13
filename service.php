<?php
global $conn;
header("Content-Type: application/json");
require "db.php";

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET': // Lấy danh sách hoặc 1 service
        if (isset($_GET['id'])) {
            $stmt = $conn->prepare("SELECT * FROM service WHERE id = ? AND deleted_at IS NULL");
            $stmt->execute([$_GET['id']]);
            echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        } else {
            if (isset($_GET['center_id'])) {
                $stmt = $conn->prepare("SELECT * FROM service WHERE center_id = ? AND deleted_at IS NULL");
                $stmt->execute([$_GET['center_id']]);
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            } else {
                $stmt = $conn->query("SELECT * FROM service WHERE deleted_at IS NULL");
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            }
        }
        break;

    case 'POST': // Thêm service mới
        $data = json_decode(file_get_contents("php://input"), true);

        $sql = "INSERT INTO service (name, price, description, center_id, category_service_id, created_at, updated_at) 
                VALUES (:name, :price, :description, :center_id, :category_service_id, NOW(), NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':name' => $data['name'],
            ':price' => $data['price'],
            ':description' => $data['description'],
            ':center_id' => $data['center_id'],
            ':category_service_id' => $data['category_service_id']
        ]);

        $id = $conn->lastInsertId();
        $stmt = $conn->prepare("SELECT * FROM service WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        break;

    case 'PUT': // Cập nhật service
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Thiếu id"]);
            exit;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        $sql = "UPDATE service SET 
                    name = :name,
                    price = :price,
                    description = :description,
                    center_id = :center_id,
                    category_service_id = :category_service_id,
                    updated_at = NOW()
                WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':name' => $data['name'],
            ':price' => $data['price'],
            ':description' => $data['description'],
            ':center_id' => $data['center_id'],
            ':category_service_id' => $data['category_service_id'],
            ':id' => $_GET['id']
        ]);

        $stmt = $conn->prepare("SELECT * FROM service WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        break;

    case 'DELETE': // Xóa mềm hoặc cứng
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Thiếu id"]);
            exit;
        }

        if (isset($_GET['hard']) && $_GET['hard'] == 'true') {
            $sql = "DELETE FROM service WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $_GET['id']]);
            echo json_encode(["message" => "Xóa CỨNG service thành công"]);
        } else {
            $sql = "UPDATE service SET deleted_at = NOW() WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $_GET['id']]);
            echo json_encode(["message" => "Xóa MỀM service thành công"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method không được hỗ trợ"]);
}
