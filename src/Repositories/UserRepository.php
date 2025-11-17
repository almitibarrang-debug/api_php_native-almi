<?php

namespace Src\Repositories;

use PDO;
use Src\Config\Database;

class UserRepository
{
    private PDO $db;

    public function __construct(array $cfg)
    {
        $this->db = Database::conn($cfg);
    }

    public function paginate($page, $perPage, $search = '', $sortBy = 'id', $sortDir = 'DESC')
    {
        $offset = ($page - 1) * $perPage;

        $whereClause = '';
        $params = [];
        if (!empty($search)) {
            $whereClause = 'WHERE name LIKE :search OR email LIKE :search';
            $params[':search'] = '%' . $search . '%';
        }

        $allowedSorts = ['id', 'name', 'email', 'role', 'created_at', 'updated_at'];
        $sortBy = in_array($sortBy, $allowedSorts) ? $sortBy : 'id';
        $sortDir = strtoupper($sortDir) === 'ASC' ? 'ASC' : 'DESC';

        $countSql = "SELECT COUNT(*) FROM users $whereClause";
        $countStatement = $this->db->prepare($countSql);
        foreach ($params as $key => $value) {
            $countStatement->bindValue($key, $value);
        }
        $countStatement->execute();
        $total = (int)$countStatement->fetchColumn();

        $sql = "SELECT id,name,email,role,created_at,updated_at 
            FROM users $whereClause ORDER BY $sortBy $sortDir LIMIT :per OFFSET :off";
        $statement = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $statement->bindValue($key, $value);
        }
        $statement->bindValue(':per', (int)$perPage, PDO::PARAM_INT);
        $statement->bindValue(':off', (int)$offset, PDO::PARAM_INT);
        $statement->execute();

        return [
            'data' => $statement->fetchAll(),
            'meta' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'last_page' => max(1, (int)ceil($total / $perPage)),
                'search' => $search,
                'sort_by' => $sortBy,
                'sort_dir' => $sortDir
            ]
        ];
    }

    public function find($id)
    {
        $statement = $this->db->prepare('SELECT id,name,email,role,created_at,updated_at FROM users WHERE id=?');
        $statement->execute([$id]);
        return $statement->fetch();
    }

    public function create($name, $email, $passwordHash, $role = 'user')
    {
        $this->db->beginTransaction();
        try {
            $statement = $this->db->prepare('INSERT INTO users(name,email,password_hash,role) VALUES(?,?,?,?)');
            $statement->execute([$name, $email, $passwordHash, $role]);
            $id = (int)$this->db->lastInsertId();
            $this->db->commit();
            return $this->find($id);
        } catch (\Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    public function update($id, $name, $email, $role)
    {
        $statement = $this->db->prepare('UPDATE users SET name=?,email=?,role=? WHERE id=?');
        $statement->execute([$name, $email, $role, $id]);
        return $this->find($id);
    }

    public function delete($id): bool
    {
        $statement = $this->db->prepare('DELETE FROM users WHERE id=?');
        return $statement->execute([$id]);
    }
}