<?php
global $conn;
header("Content-Type: application/json");
require "db.php";

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET': // Lấy danh sách hoặc 1 breed
        if (isset($_GET['id'])) {
            $stmt = $conn->prepare("SELECT * FROM animal_breed WHERE id = ? AND deleted_at IS NULL");
            $stmt->execute([$_GET['id']]);
            echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        } else {
            $stmt = $conn->query("SELECT * FROM animal_breed WHERE deleted_at IS NULL");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    case 'POST': // Thêm breed
        $data = json_decode(file_get_contents("php://input"), true);
        $sql = "INSERT INTO animal_breed (name, animal_type_id) 
                VALUES (:name, :animal_type_id)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':name' => $data['name'],
            ':animal_type_id' => $data['animal_type_id']
        ]);
        echo json_encode(["message" => "Breed added successfully"]);
        break;

    case 'PUT': // Cập nhật breed
        if (!isset($_GET['id'])) {
            echo json_encode(["error" => "ID required"]);
            exit;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $sql = "UPDATE animal_breed SET 
                name=:name, animal_type_id=:animal_type_id, updated_at=NOW()
                WHERE id=:id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':id' => $_GET['id'],
            ':name' => $data['name'],
            ':animal_type_id' => $data['animal_type_id']
        ]);
        echo json_encode(["message" => "Breed updated successfully"]);
        break;

    case 'DELETE':
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Thiếu id"]);
            exit;
        }

        if (isset($_GET['force']) && $_GET['force'] == "true") {
            // Xoá cứng
            $sql = "DELETE FROM animal_breed WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $_GET['id']]);
            echo json_encode(["message" => "Xoá breed vĩnh viễn"]);
        } else {
            // Xoá mềm
            $sql = "UPDATE animal_breed SET deleted_at = NOW() WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $_GET['id']]);
            echo json_encode(["message" => "Xoá breed (soft delete)"]);
        }
        break;


    default:
        echo json_encode(["error" => "Unsupported request method"]);
        break;
}
