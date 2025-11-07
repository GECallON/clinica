<?php
require_once __DIR__ . '/../config.php';

class Usuario {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function login($email, $senha) {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE email = ? AND ativo = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($senha, $user['senha'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nome'] = $user['nome'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['nivel_acesso'] = $user['nivel_acesso'];
            return true;
        }
        return false;
    }

    public function logout() {
        session_destroy();
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT id, nome, email, nivel_acesso, telefone, ativo, created_at FROM usuarios ORDER BY nome");
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT id, nome, email, nivel_acesso, telefone, ativo FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getMedicos() {
        $stmt = $this->db->query("SELECT id, nome FROM usuarios WHERE nivel_acesso = 'medico' AND ativo = 1 ORDER BY nome");
        return $stmt->fetchAll();
    }

    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO usuarios (nome, email, senha, nivel_acesso, telefone, ativo)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $senha_hash = password_hash($data['senha'], PASSWORD_DEFAULT);

        return $stmt->execute([
            $data['nome'],
            $data['email'],
            $senha_hash,
            $data['nivel_acesso'],
            $data['telefone'] ?? null,
            $data['ativo'] ?? 1
        ]);
    }

    public function update($id, $data) {
        if (!empty($data['senha'])) {
            $stmt = $this->db->prepare("
                UPDATE usuarios
                SET nome = ?, email = ?, senha = ?, nivel_acesso = ?, telefone = ?, ativo = ?
                WHERE id = ?
            ");
            $senha_hash = password_hash($data['senha'], PASSWORD_DEFAULT);
            return $stmt->execute([
                $data['nome'],
                $data['email'],
                $senha_hash,
                $data['nivel_acesso'],
                $data['telefone'] ?? null,
                $data['ativo'] ?? 1,
                $id
            ]);
        } else {
            $stmt = $this->db->prepare("
                UPDATE usuarios
                SET nome = ?, email = ?, nivel_acesso = ?, telefone = ?, ativo = ?
                WHERE id = ?
            ");
            return $stmt->execute([
                $data['nome'],
                $data['email'],
                $data['nivel_acesso'],
                $data['telefone'] ?? null,
                $data['ativo'] ?? 1,
                $id
            ]);
        }
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
