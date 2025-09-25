<?php
class Clinic extends Model
{
    public function all(int $limit = 50): array
{
    $limit = max(1, min(200, (int)$limit));

    $sql = "
        SELECT 
            c.id,
            c.name,
            c.description,
            c.address,
            GROUP_CONCAT(DISTINCT cs.name) AS services,
            GROUP_CONCAT(DISTINCT at.name) AS pets
        FROM clinic_center c
        LEFT JOIN service cs ON cs.center_id = c.id AND cs.deleted_at IS NULL
        LEFT JOIN clinic_animal ca ON ca.clinic_id = c.id
        LEFT JOIN animal_types at ON at.id = ca.animal_type_id
        WHERE c.deleted_at IS NULL
        GROUP BY c.id
        ORDER BY c.id DESC
        LIMIT {$limit}
    ";

    $st = $this->db->query($sql);
    return $st->fetchAll(PDO::FETCH_ASSOC);
}


    public function search(string $q, int $limit = 50): array
{
    $limit = max(1, min(200, (int)$limit));
    $q = trim($q);
    if ($q === '') return $this->all($limit);

    $sql = "
        SELECT 
            c.id,
            c.name,
            c.description,
            c.address,
            GROUP_CONCAT(DISTINCT cs.name) AS services,
            GROUP_CONCAT(DISTINCT at.name) AS pets
        FROM clinic_center c
        LEFT JOIN service cs ON cs.center_id = c.id AND cs.deleted_at IS NULL
        LEFT JOIN clinic_animal ca ON ca.clinic_id = c.id
        LEFT JOIN animal_types at ON at.id = ca.animal_type_id
        WHERE c.deleted_at IS NULL
          AND (c.name LIKE :kw OR c.description LIKE :kw OR c.address LIKE :kw)
        GROUP BY c.id
        ORDER BY c.id DESC
        LIMIT {$limit}
    ";

    $st = $this->db->prepare($sql);
    $st->execute([':kw' => "%{$q}%"]);
    return $st->fetchAll(PDO::FETCH_ASSOC);
}

    
}


