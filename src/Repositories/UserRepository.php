<?php
namespace Src\Repositories;

use PDO;
use Src\Config\Database;

class UserRepository {
    private PDO $db;

    public function __construct(array $cfg) {
        $this->db = Database::conn($cfg);
    }

    public function paginate($page, $per, $search = '', $sort_by = 'id', $sort_dir = 'DESC') {
        $off = ($page - 1) * $per;
        
        $where = '';
        $params = [];
        if (!empty($search)) {
            $where = 'WHERE name LIKE :search OR email LIKE :search';
            $params[':search'] = '%' . $search . '%';
        }

        $allowed_sorts = ['id', 'name', 'email', 'role', 'created_at', 'updated_at'];
        $sort_by = in_array($sort_by, $allowed_sorts) ? $sort_by : 'id';
        $sort_dir = strtoupper($sort_dir) === 'ASC' ? 'ASC' : 'DESC';

        $total_sql = "SELECT COUNT(*) FROM users $where";
        $total_stmt = $this->db->prepare($total_sql);
        foreach ($params as $key => $value) {
            $total_stmt->bindValue($key, $value);
        }
        $total_stmt->execute();
        $total = (int) $total_stmt->fetchColumn();

        $sql = "SELECT id,name,email,role,created_at,updated_at 
            FROM users $where ORDER BY $sort_by $sort_dir LIMIT :per OFFSET :off";
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':per', (int)$per, PDO::PARAM_INT);
        $stmt->bindValue(':off', (int)$off, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data' => $stmt->fetchAll(),
            'meta' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $per,
                'last_page' => max(1, (int)ceil($total / $per)),
                'search' => $search,
                'sort_by' => $sort_by,
                'sort_dir' => $sort_dir
            ]
        ];
    }

    public function find($id) {
        $s = $this->db->prepare('SELECT id,name,email,role,created_at,updated_at FROM users WHERE id=?');
        $s->execute([$id]);
        return $s->fetch();
    }

    public function create($name, $email, $hash, $role='user') {
        $this->db->beginTransaction();
        try {
            $s = $this->db->prepare('INSERT INTO users(name,email,password_hash,role) VALUES(?,?,?,?)');
            $s->execute([$name, $email, $hash, $role]);
            $id = (int)$this->db->lastInsertId();
            $this->db->commit();
            return $this->find($id);
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function update($id, $name, $email, $role) {
        $s = $this->db->prepare('UPDATE users SET name=?,email=?,role=? WHERE id=?');
        $s->execute([$name, $email, $role, $id]);
        return $this->find($id);
    }

    public function delete($id) {
        $s = $this->db->prepare('DELETE FROM users WHERE id=?');
        return $s->execute([$id]);
    }
}