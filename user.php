<?php
global $conn;
header("Content-Type: application/json");
require "db.php";

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET': // Lấy danh sách hoặc 1 user kèm role name
        if (isset($_GET['id'])) {
            $stmt = $conn->prepare("
                SELECT u.*, r.name AS role_name
                FROM user u
                JOIN role r ON u.role_id = r.id
                WHERE u.id = ? AND u.deleted_at IS NULL AND r.deleted_at IS NULL
            ");
            $stmt->execute([$_GET['id']]);
            echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        } else {
            $stmt = $conn->query("
                SELECT u.*, r.name AS role_name
                FROM user u
                JOIN role r ON u.role_id = r.id
                WHERE u.deleted_at IS NULL AND r.deleted_at IS NULL
            ");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    case 'POST': // Thêm user mới
        $data = json_decode(file_get_contents("php://input"), true);

        $sql = "INSERT INTO user (name, birth_date, gender, phone, address, email, avatar, introduction, role_id, created_at, updated_at) 
                VALUES (:name, :birth_date, :gender, :phone, :address, :email, :avatar, :introduction, :role_id, NOW(), NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':name' => $data['name'],
            ':birth_date' => $data['birth_date'],
            ':gender' => $data['gender'],
            ':phone' => $data['phone'],
            ':address' => $data['address'],
            ':email' => $data['email'],
            ':avatar' => $data['avatar'] ?? null,
            ':introduction' => $data['introduction'] ?? null,
            ':role_id' => $data['role_id']
        ]);

        $id = $conn->lastInsertId();
        $stmt = $conn->prepare("
            SELECT u.*, r.name AS role_name
            FROM user u
            JOIN role r ON u.role_id = r.id
            WHERE u.id = ?
        ");
        $stmt->execute([$id]);
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        break;

    case 'PUT': // Cập nhật user
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Thiếu id"]);
            exit;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        $sql = "UPDATE user SET 
                    name = :name,
                    birth_date = :birth_date,
                    gender = :gender,
                    phone = :phone,
                    address = :address,
                    email = :email,
                    avatar = :avatar,
                    introduction = :introduction,
                    role_id = :role_id,
                    updated_at = NOW()
                WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':name' => $data['name'],
            ':birth_date' => $data['birth_date'],
            ':gender' => $data['gender'],
            ':phone' => $data['phone'],
            ':address' => $data['address'],
            ':email' => $data['email'],
            ':avatar' => $data['avatar'] ?? null,
            ':introduction' => $data['introduction'] ?? null,
            ':role_id' => $data['role_id'],
            ':id' => $_GET['id']
        ]);

        $stmt = $conn->prepare("
            SELECT u.*, r.name AS role_name
            FROM user u
            JOIN role r ON u.role_id = r.id
            WHERE u.id = ?
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
            $sql = "DELETE FROM user WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $_GET['id']]);
            echo json_encode(["message" => "Xóa CỨNG user thành công"]);
        } else {
            $sql = "UPDATE user SET deleted_at = NOW() WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $_GET['id']]);
            echo json_encode(["message" => "Xóa MỀM user thành công"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method không được hỗ trợ"]);
}
