<?php
class Clinic extends Model
{
    public function all(int $limit = 50): array
    {
        $limit = max(1, min(200, (int)$limit));        // chặn biên + ép int
        $sql = "SELECT id, name, description, address
                FROM clinic_center
                WHERE deleted_at IS NULL
                ORDER BY id DESC
                LIMIT {$limit}";
        $st = $this->db->query($sql);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function search(string $q, int $limit = 50): array
    {
        $limit = max(1, min(200, (int)$limit));
        $q = trim($q);
        if ($q === '') return $this->all($limit);

        $sql = "SELECT id, name, description, address
                FROM clinic_center
                WHERE deleted_at IS NULL
                  AND (name LIKE :kw OR description LIKE :kw OR address LIKE :kw)
                ORDER BY id DESC
                LIMIT {$limit}";
        $st = $this->db->prepare($sql);
        $st->execute([':kw' => "%{$q}%"]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }
}


