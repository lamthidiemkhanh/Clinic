<?php
class User extends Model {
    public function find(int $id): ?array {
        return $this->queryOne("SELECT id, name, phone, address, gender, dob FROM users WHERE id=:id", [':id'=>$id]);
    }
    public function updateProfile(int $id, array $data): bool {
        $sql = "UPDATE users SET name=:name, phone=:phone, address=:address, gender=:gender, dob=:dob WHERE id=:id";
        $st = $this->db->prepare($sql);
        return $st->execute([
            ':name'=>$data['name']??null,
            ':phone'=>$data['phone']??null,
            ':address'=>$data['address']??null,
            ':gender'=>$data['gender']??null,
            ':dob'=>$data['dob']??null,
            ':id'=>$id
        ]);
    }
}
