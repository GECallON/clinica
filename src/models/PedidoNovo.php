<?php
require_once __DIR__ . '/../config.php';

class PedidoNovo {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll() {
        $sql = "
            SELECT p.*,
                   s.nome as situacao_nome,
                   s.cor as situacao_cor,
                   u.nome as medico_nome
            FROM pedidos_novos p
            LEFT JOIN situacoes s ON p.situacao_id = s.id
            LEFT JOIN usuarios u ON p.medico_id = u.id
            ORDER BY p.created_at DESC
        ";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT p.*,
                   s.nome as situacao_nome,
                   s.cor as situacao_cor,
                   u.nome as medico_nome
            FROM pedidos_novos p
            LEFT JOIN situacoes s ON p.situacao_id = s.id
            LEFT JOIN usuarios u ON p.medico_id = u.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        // Buscar ID da situação "Aguardando Autorização" (padrão para novos pedidos)
        $situacao_padrao = 2; // ID da situação "Aguardando Autorização"

        $stmt = $this->db->prepare("
            INSERT INTO pedidos_novos (
                nome_paciente, telefone, medico_id, nome_medico, convenio,
                fornecedor, observacao, situacao_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $result = $stmt->execute([
            $data['nome_paciente'],
            $data['telefone'] ?? null,
            $data['medico_id'] ?? null,
            $data['nome_medico'] ?? null,
            $data['convenio'],
            $data['fornecedor'] ?? null,
            $data['observacao'] ?? null,
            $situacao_padrao // Sempre "Aguardando Autorização" ao criar
        ]);

        if ($result) {
            $pedido_id = $this->db->lastInsertId();

            // Disparar mensagem automática ao criar
            if (!class_exists('MensagemConfig')) {
                require_once __DIR__ . '/MensagemConfig.php';
            }
            $mensagemModel = new MensagemConfig();
            $mensagemModel->dispararAutomatico($pedido_id, $situacao_padrao);
        }

        return $result;
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE pedidos_novos SET
                nome_paciente = ?,
                telefone = ?,
                medico_id = ?,
                nome_medico = ?,
                convenio = ?,
                fornecedor = ?,
                observacao = ?,
                situacao_id = ?
            WHERE id = ?
        ");

        return $stmt->execute([
            $data['nome_paciente'],
            $data['telefone'] ?? null,
            $data['medico_id'] ?? null,
            $data['nome_medico'] ?? null,
            $data['convenio'],
            $data['fornecedor'] ?? null,
            $data['observacao'] ?? null,
            $data['situacao_id'] ?? null,
            $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM pedidos_novos WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Estatísticas de Pedidos
    public function getStatsBySituacao() {
        $stmt = $this->db->query("
            SELECT
                s.id,
                s.nome,
                s.cor,
                COUNT(p.id) as total,
                ROUND((COUNT(p.id) / NULLIF((SELECT COUNT(*) FROM pedidos_novos), 0) * 100), 1) as percentual
            FROM situacoes s
            LEFT JOIN pedidos_novos p ON p.situacao_id = s.id
            GROUP BY s.id, s.nome, s.cor
            HAVING total > 0
            ORDER BY total DESC
        ");
        return $stmt->fetchAll();
    }

    public function getStatsHoje() {
        $stmt = $this->db->query("
            SELECT COUNT(*) as total
            FROM pedidos_novos
            WHERE DATE(created_at) = CURDATE()
        ");
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function getStatsEstaSemana() {
        $stmt = $this->db->query("
            SELECT COUNT(*) as total
            FROM pedidos_novos
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        ");
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function getStatsEsteMes() {
        $stmt = $this->db->query("
            SELECT COUNT(*) as total
            FROM pedidos_novos
            WHERE MONTH(created_at) = MONTH(CURDATE())
            AND YEAR(created_at) = YEAR(CURDATE())
        ");
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
}
