<?php
require_once __DIR__ . '/../config.php';

class Hospital {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll() {
        $stmt = $this->db->query("
            SELECT * FROM hospitais
            ORDER BY nome ASC
        ");
        return $stmt->fetchAll();
    }

    public function getAtivos() {
        $stmt = $this->db->query("
            SELECT * FROM hospitais
            WHERE ativo = 1
            ORDER BY nome ASC
        ");
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM hospitais WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getByNome($nome) {
        $stmt = $this->db->prepare("SELECT * FROM hospitais WHERE nome = ? LIMIT 1");
        $stmt->execute([$nome]);
        return $stmt->fetch();
    }

    public function getEmailByNome($nome) {
        $hospital = $this->getByNome($nome);
        return $hospital ? $hospital['email'] : null;
    }

    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO hospitais (nome, email, telefone, endereco, ativo)
            VALUES (?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            $data['nome'],
            $data['email'],
            $data['telefone'] ?? null,
            $data['endereco'] ?? null,
            isset($data['ativo']) ? 1 : 0
        ]);
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE hospitais SET
                nome = ?,
                email = ?,
                telefone = ?,
                endereco = ?,
                ativo = ?
            WHERE id = ?
        ");

        return $stmt->execute([
            $data['nome'],
            $data['email'],
            $data['telefone'] ?? null,
            $data['endereco'] ?? null,
            isset($data['ativo']) ? 1 : 0,
            $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM hospitais WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getNomesHospitais() {
        $stmt = $this->db->query("
            SELECT DISTINCT nome FROM hospitais WHERE ativo = 1 ORDER BY nome ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
