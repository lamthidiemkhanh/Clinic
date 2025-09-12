<?php
global $conn;
header("Content-Type: application/json");
require "db.php";

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET': // Lấy danh sách hoặc 1 species
        if (isset($_GET['id'])) {
            $stmt = $conn->prepare("SELECT * FROM animal_species WHERE id = ? AND deleted_at IS NULL");
            $stmt->execute([$_GET['id']]);
            echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        } else {
            $stmt = $conn->query("SELECT * FROM animal_species WHERE deleted_at IS NULL");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    case 'POST': // Thêm mới species
        $data = json_decode(file_get_contents("php://input"), true);
        $sql = "INSERT INTO animal_species (name, created_at, updated_at) 
                VALUES (:name, NOW(), NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':name' => $data['name']]);
        echo json_encode(["message" => "Thêm species thành công"]);
        break;

    case 'PUT': // Cập nhật species
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Thiếu id"]);
            exit;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $sql = "UPDATE animal_species SET name = :name, updated_at = NOW() WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':name' => $data['name'], ':id' => $_GET['id']]);
        echo json_encode(["message" => "Cập nhật species thành công"]);
        break;

    case 'DELETE':
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Thiếu id"]);
            exit;
        }

        // Nếu có ?hard=true thì xóa cứng
        if (isset($_GET['force']) && $_GET['force'] == 'true') {
            $sql = "DELETE FROM animal_species WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $_GET['id']]);
            echo json_encode(["message" => "Xóa species vĩnh viễn thành công"]);
        } else {
            // Ngược lại thì xóa mềm
            $sql = "UPDATE animal_species SET deleted_at = NOW() WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $_GET['id']]);
            echo json_encode(["message" => "Xóa species thành công"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method không được hỗ trợ"]);
}
