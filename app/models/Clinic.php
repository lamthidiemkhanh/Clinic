<?php
class Clinic extends Model {
    public function all(int $limit = 50): array {
        // Use existing table clinic_center
        $sql = "SELECT id, name, description, address FROM clinic_center WHERE deleted_at IS NULL ORDER BY id DESC LIMIT :lim";
        $st = $this->db->prepare($sql);
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }
    public function find(int $id): ?array {
        return $this->queryOne("SELECT * FROM clinic_center WHERE id = :id", [':id'=>$id]);
    }
}
