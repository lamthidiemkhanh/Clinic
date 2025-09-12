<?php
global $conn;
header("Content-Type: application/json");
require "db.php";

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET': // Lấy danh sách hoặc 1 record
        if (isset($_GET['id'])) {
            $stmt = $conn->prepare("
                SELECT pts.*, a.name AS animal_species_name, s.name AS service_name
                FROM pet_type_service pts
                JOIN animal_species a ON pts.animal_type_id = a.id
                JOIN service s ON pts.service_id = s.id
                WHERE pts.id = ? AND pts.deleted_at IS NULL
            ");
            $stmt->execute([$_GET['id']]);
            echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        } else {
            $stmt = $conn->query("
                SELECT pts.*, a.name AS animal_species_name, s.name AS service_name
                FROM pet_type_service pts
                JOIN animal_species a ON pts.animal_type_id = a.id
                JOIN service s ON pts.service_id = s.id
                WHERE pts.deleted_at IS NULL
            ");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    case 'POST': // Thêm mới
        $data = json_decode(file_get_contents("php://input"), true);

        $sql = "INSERT INTO pet_type_service (animal_type_id, service_id, created_at, updated_at) 
                VALUES (:animal_type_id, :service_id, NOW(), NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':animal_type_id' => $data['animal_type_id'],
            ':service_id' => $data['service_id']
        ]);

        $id = $conn->lastInsertId();
        $stmt = $conn->prepare("
            SELECT pts.*, a.name AS animal_species_name, s.name AS service_name
            FROM pet_type_service pts
            JOIN animal_species a ON pts.animal_type_id = a.id
            JOIN service s ON pts.service_id = s.id
            WHERE pts.id = ?
        ");
        $stmt->execute([$id]);
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        break;

    case 'PUT': // Cập nhật
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Thiếu id"]);
            exit;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        $sql = "UPDATE pet_type_service SET 
                    animal_type_id = :animal_type_id,
                    service_id = :service_id,
                    updated_at = NOW()
                WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':animal_type_id' => $data['animal_type_id'],
            ':service_id' => $data['service_id'],
            ':id' => $_GET['id']
        ]);

        $stmt = $conn->prepare("
            SELECT pts.*, a.name AS animal_species_name, s.name AS service_name
            FROM pet_type_service pts
            JOIN animal_species a ON pts.animal_type_id = a.id
            JOIN service s ON pts.service_id = s.id
            WHERE pts.id = ?
        ");
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
            $sql = "DELETE FROM pet_type_service WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $_GET['id']]);
            echo json_encode(["message" => "Xóa CỨNG thành công"]);
        } else {
            $sql = "UPDATE pet_type_service SET deleted_at = NOW() WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $_GET['id']]);
            echo json_encode(["message" => "Xóa MỀM thành công"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method không được hỗ trợ"]);
}
