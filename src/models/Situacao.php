<?php
require_once __DIR__ . '/../config.php';

class Situacao {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM situacoes ORDER BY nome");
        return $stmt->fetchAll();
    }

    public function getAtivos() {
        $stmt = $this->db->query("SELECT * FROM situacoes WHERE ativo = 1 ORDER BY nome");
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM situacoes WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO situacoes (nome, cor, ativo)
            VALUES (?, ?, ?)
        ");

        return $stmt->execute([
            $data['nome'],
            $data['cor'] ?? '#3B82F6',
            $data['ativo'] ?? 1
        ]);
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE situacoes
            SET nome = ?, cor = ?, ativo = ?
            WHERE id = ?
        ");

        return $stmt->execute([
            $data['nome'],
            $data['cor'] ?? '#3B82F6',
            $data['ativo'] ?? 1,
            $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM situacoes WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
