<?php
class Pet extends Model {
    public function byUser(int $userId): array {
        return $this->queryAll("SELECT id, name, type, age, sex, weight FROM pets WHERE user_id=:uid ORDER BY id DESC", [':uid'=>$userId]);
    }
}
