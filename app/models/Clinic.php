<?php
class Clinic extends Model
{
    public function all(int $limit = 50): array
    {
        $limit = max(1, min(200, (int)$limit));
        return $this->paginate(1, $limit)['data'];
    }

    public function search(string $q, int $limit = 50): array
    {
        $limit = max(1, min(200, (int)$limit));
        return $this->paginate(1, $limit, $q)['data'];
    }

    public function paginate(int $page = 1, int $perPage = 10, string $keyword = ''): array
    {
        $page = max(1, (int)$page);
        $perPage = max(1, min(50, (int)$perPage));
        $offset = ($page - 1) * $perPage;
        $keyword = trim($keyword);

        $where = 'WHERE c.deleted_at IS NULL';
        $params = [];
        if ($keyword !== '') {
            $where .= ' AND (c.name LIKE :kw OR c.description LIKE :kw OR c.address LIKE :kw)';
            $params[':kw'] = "%{$keyword}%";
        }

        $countSql = "SELECT COUNT(*) FROM clinic_center c {$where}";
        $st = $this->db->prepare($countSql);
        $st->execute($params);
        $total = (int)$st->fetchColumn();

        $sql = "
            SELECT 
                c.id,
                c.name,
                c.description,
                c.address,
                GROUP_CONCAT(DISTINCT cs.name ORDER BY cs.name SEPARATOR ', ') AS services,
                GROUP_CONCAT(DISTINCT at.name ORDER BY at.name SEPARATOR ', ') AS pets
            FROM clinic_center c
            LEFT JOIN service cs ON cs.center_id = c.id AND cs.deleted_at IS NULL
            LEFT JOIN clinic_animal ca ON ca.clinic_id = c.id
            LEFT JOIN animal_types at ON at.id = ca.animal_type_id
            {$where}
            GROUP BY c.id
            ORDER BY c.name ASC
            LIMIT :limit OFFSET :offset
        ";
        $st = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $st->bindValue($key, $value);
        }
        $st->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $st->bindValue(':offset', $offset, PDO::PARAM_INT);
        $st->execute();
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);

        return [
            'data' => $rows,
            'pagination' => [
                'page' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'pages' => $total > 0 ? (int)ceil($total / $perPage) : 1,
                'keyword' => $keyword,
            ],
        ];
    }
}
