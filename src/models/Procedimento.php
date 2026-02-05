<?php
require_once __DIR__ . '/../config.php';

class Procedimento {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM procedimentos ORDER BY nome");
        return $stmt->fetchAll();
    }

    public function getAtivos() {
        $stmt = $this->db->query("SELECT * FROM procedimentos WHERE ativo = 1 ORDER BY nome");
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM procedimentos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO procedimentos (nome, descricao, duracao_estimada, ativo)
            VALUES (?, ?, ?, ?)
        ");

        return $stmt->execute([
            $data['nome'],
            $data['descricao'] ?? null,
            $data['duracao_estimada'] ?? 60,
            $data['ativo'] ?? 1
        ]);
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE procedimentos
            SET nome = ?, descricao = ?, duracao_estimada = ?, ativo = ?
            WHERE id = ?
        ");

        return $stmt->execute([
            $data['nome'],
            $data['descricao'] ?? null,
            $data['duracao_estimada'] ?? 60,
            $data['ativo'] ?? 1,
            $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM procedimentos WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
