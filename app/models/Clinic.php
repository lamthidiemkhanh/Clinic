<?php
class Clinic extends Model
{
    public function all(int $limit = 50, array $filters = []): array
    {
        $limit = max(1, min(200, (int)$limit));
        return $this->paginate(1, $limit, '', $filters)['data'];
    }

    public function search(string $q, int $limit = 50, array $filters = []): array
    {
        $limit = max(1, min(200, (int)$limit));
        return $this->paginate(1, $limit, $q, $filters)['data'];
    }

    public function find(int $id): ?array
    {
        $sql = "
            SELECT
                c.id,
                c.name,
                c.description,
                c.address,
                c.phone,
                c.email,
                c.is_verify,
                COUNT(DISTINCT s.id) AS service_count,
                GROUP_CONCAT(DISTINCT s.name ORDER BY s.name SEPARATOR ', ') AS services,
                GROUP_CONCAT(DISTINCT at.name ORDER BY at.name SEPARATOR ', ') AS pets
            FROM clinic_center c
            LEFT JOIN service s ON s.center_id = c.id AND s.deleted_at IS NULL
            LEFT JOIN clinic_animal ca ON ca.clinic_id = c.id
            LEFT JOIN animal_types at ON at.id = ca.animal_type_id
            WHERE c.id = :id AND c.deleted_at IS NULL
            GROUP BY c.id
            LIMIT 1
        ";
        $st = $this->db->prepare($sql);
        $st->execute([':id' => $id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }
        $row['service_count'] = (int)($row['service_count'] ?? 0);
        return $row;
    }

    public function paginate(int $page = 1, int $perPage = 10, string $keyword = '', array $filters = []): array
    {
        $page = max(1, (int)$page);
        $perPage = max(1, min(1000, (int)$perPage));
        $offset = ($page - 1) * $perPage;
        $keyword = trim($keyword);

        $baseSql = "
            SELECT 
                c.id,
                c.name,
                c.description,
                c.address,
                GROUP_CONCAT(DISTINCT cat.name ORDER BY cat.name SEPARATOR ', ') AS service_categories,
                GROUP_CONCAT(DISTINCT cs.name ORDER BY cs.name SEPARATOR ', ') AS services,
                GROUP_CONCAT(DISTINCT at.name ORDER BY at.name SEPARATOR ', ') AS pets
            FROM clinic_center c
            LEFT JOIN service cs ON cs.center_id = c.id AND cs.deleted_at IS NULL
            LEFT JOIN category_service cat ON cat.id = cs.category_service_id AND cat.deleted_at IS NULL
            LEFT JOIN clinic_animal ca ON ca.clinic_id = c.id
            LEFT JOIN animal_types at ON at.id = ca.animal_type_id
            WHERE c.deleted_at IS NULL
            GROUP BY c.id
        ";

        $havingParts = [];
        $havingParams = [];
        $paramIndex = 0;

        if ($keyword !== '') {
            $havingParts[] = '(c.name LIKE :kw OR c.description LIKE :kw OR c.address LIKE :kw OR services LIKE :kw OR service_categories LIKE :kw OR pets LIKE :kw)';
            $havingParams[':kw'] = "%{$keyword}%";
        }

        $this->applyServiceFilter($filters, $havingParts, $havingParams, $paramIndex);

        $havingClause = $havingParts ? ' HAVING ' . implode(' AND ', $havingParts) : '';

        $countSql = "SELECT COUNT(*) FROM ({$baseSql}{$havingClause}) AS counted";
        $st = $this->db->prepare($countSql);
        foreach ($havingParams as $key => $value) {
            $st->bindValue($key, $value);
        }
        $st->execute();
        $total = (int)$st->fetchColumn();

        $dataSql = "{$baseSql}{$havingClause} ORDER BY c.name ASC LIMIT :limit OFFSET :offset";
        $st = $this->db->prepare($dataSql);
        foreach ($havingParams as $key => $value) {
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

    private function applyServiceFilter(array $filters, array &$havingParts, array &$havingParams, int &$paramIndex): void
    {
        $service = $filters['service'] ?? 'all';
        if ($service === 'all') {
            return;
        }

        $keywords = $filters['service_keywords'] ?? [];
        $keywords = array_values(array_filter($keywords, static function ($value) {
            return is_string($value) && trim($value) !== '';
        }));

        if (empty($keywords)) {
            $keywords[] = str_replace('-', ' ', $service);
            $keywords[] = $service;
        }

        $conditions = [];
        foreach ($keywords as $word) {
            $paramName = ':svc' . $paramIndex++;
            $conditions[] = "(service_categories LIKE {$paramName} OR services LIKE {$paramName})";
            $havingParams[$paramName] = '%' . $word . '%';
        }

        if (!empty($conditions)) {
            $havingParts[] = '(' . implode(' OR ', $conditions) . ')';
        }
    }
}
