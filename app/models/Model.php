<?php
abstract class Model {
    protected PDO $db;
    public function __construct(){
        $this->db = Database::pdo();
    }
    // Helper names use 'query*' to avoid clashing with child method names
    protected function queryAll(string $sql, array $params = []): array {
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }
    protected function queryOne(string $sql, array $params = []): ?array {
        $st = $this->db->prepare($sql);
        $st->execute($params);
        $row = $st->fetch();
        return $row === false ? null : $row;
    }
}
