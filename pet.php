<?php
global $conn;
header("Content-Type: application/json");
require "db.php";

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET': // Lấy danh sách hoặc 1 pet
        if (isset($_GET['id'])) {
            $stmt = $conn->prepare("SELECT * FROM pet WHERE id = ? AND deleted_at IS NULL");
            $stmt->execute([$_GET['id']]);
            echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        } else {
            $stmt = $conn->query("SELECT * FROM pet WHERE deleted_at IS NULL");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    case 'POST': // Thêm mới pet
        $data = json_decode(file_get_contents("php://input"), true);
        $sql = "INSERT INTO pet 
                (name, year_of_birth, color, weight, gender, is_spayed_neutered, description, owner_id, animal_type_id, animal_breed_id, created_at, updated_at) 
                VALUES (:name, :year_of_birth, :color, :weight, :gender, :is_spayed_neutered, :description, :owner_id, :animal_type_id, :animal_breed_id, NOW(), NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':name' => $data['name'],
            ':year_of_birth' => $data['year_of_birth'],
            ':color' => $data['color'],
            ':weight' => $data['weight'],
            ':gender' => $data['gender'],
            ':is_spayed_neutered' => $data['is_spayed_neutered'],
            ':description' => $data['description'],
            ':owner_id' => $data['owner_id'],
            ':animal_type_id' => $data['animal_type_id'],
            ':animal_breed_id' => $data['animal_breed_id']
        ]);
        echo json_encode(["message" => "Thêm pet thành công"]);
        break;

    case 'PUT': // Cập nhật pet
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Thiếu id"]);
            exit;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $sql = "UPDATE pet SET 
                    name = :name,
                    year_of_birth = :year_of_birth,
                    color = :color,
                    weight = :weight,
                    gender = :gender,
                    is_spayed_neutered = :is_spayed_neutered,
                    description = :description,
                    owner_id = :owner_id,
                    animal_type_id = :animal_type_id,
                    animal_breed_id = :animal_breed_id,
                    updated_at = NOW()
                WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':name' => $data['name'],
            ':year_of_birth' => $data['year_of_birth'],
            ':color' => $data['color'],
            ':weight' => $data['weight'],
            ':gender' => $data['gender'],
            ':is_spayed_neutered' => $data['is_spayed_neutered'],
            ':description' => $data['description'],
            ':owner_id' => $data['owner_id'],
            ':animal_type_id' => $data['animal_type_id'],
            ':animal_breed_id' => $data['animal_breed_id'],
            ':id' => $_GET['id']
        ]);
        echo json_encode(["message" => "Cập nhật pet thành công"]);
        break;

    case 'DELETE': // Xóa mềm hoặc xóa cứng
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Thiếu id"]);
            exit;
        }

        if (isset($_GET['force']) && $_GET['force'] == 'true') {
            // Xóa cứng
            $sql = "DELETE FROM pet WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $_GET['id']]);
            echo json_encode(["message" => "Xóa CỨNG pet thành công"]);
        } else {
            // Xóa mềm
            $sql = "UPDATE pet SET deleted_at = NOW() WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $_GET['id']]);
            echo json_encode(["message" => "Xóa MỀM pet thành công"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method không được hỗ trợ"]);
}
