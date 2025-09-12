<?php
global $conn;
header("Content-Type: application/json");
require "db.php";

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET': // Lấy danh sách hoặc 1 clinic
        if (isset($_GET['id'])) {
            $stmt = $conn->prepare("SELECT * FROM clinic_center WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        } else {
            $stmt = $conn->query("SELECT * FROM clinic_center WHERE deleted_at IS NULL");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    case 'POST': // Thêm clinic
        $data = json_decode(file_get_contents("php://input"), true);
        $sql = "INSERT INTO clinic_center (name, is_verify, description, phone, address, email) 
                VALUES (:name, :is_verify, :description, :phone, :address, :email)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':name' => $data['name'],
            ':is_verify' => $data['is_verify'],
            ':description' => $data['description'],
            ':phone' => $data['phone'],
            ':address' => $data['address'],
            ':email' => $data['email']
        ]);
        echo json_encode(["message" => "Clinic created"]);
        break;

    case 'PUT': // Cập nhật clinic
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing id"]);
            exit;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $sql = "UPDATE clinic_center 
                SET name=:name, is_verify=:is_verify, description=:description, phone=:phone, address=:address, email=:email 
                WHERE id=:id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':id' => $_GET['id'],
            ':name' => $data['name'],
            ':is_verify' => $data['is_verify'],
            ':description' => $data['description'],
            ':phone' => $data['phone'],
            ':address' => $data['address'],
            ':email' => $data['email']
        ]);
        echo json_encode(["message" => "Clinic updated"]);
        break;

    case 'DELETE': // Xóa mềm (soft delete)
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing id"]);
            exit;
        }
        $stmt = $conn->prepare("UPDATE clinic_center SET deleted_at=NOW() WHERE id=?");
        $stmt->execute([$_GET['id']]);
        echo json_encode(["message" => "Clinic deleted"]);
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
        break;
}
?>
