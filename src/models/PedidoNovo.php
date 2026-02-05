<?php
require_once __DIR__ . '/../config.php';

class PedidoNovo {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll($filtros = []) {
        $sql = "
            SELECT p.*,
                   s.nome as situacao_nome,
                   s.cor as situacao_cor,
                   u.nome as medico_nome
            FROM pedidos_novos p
            LEFT JOIN situacoes s ON p.situacao_id = s.id
            LEFT JOIN usuarios u ON p.medico_id = u.id
            WHERE p.deleted_at IS NULL
        ";

        $params = [];

        // Filtro por busca de paciente
        if (!empty($filtros['busca'])) {
            $sql .= " AND p.nome_paciente LIKE ?";
            $params[] = '%' . $filtros['busca'] . '%';
        }

        // Filtro por médico
        if (!empty($filtros['medico_id'])) {
            $sql .= " AND p.medico_id = ?";
            $params[] = $filtros['medico_id'];
        }

        // Filtro por situação
        if (!empty($filtros['situacao_id'])) {
            $sql .= " AND p.situacao_id = ?";
            $params[] = $filtros['situacao_id'];
        }

        // Ordenação
        $ordem = $filtros['ordem'] ?? 'data_desc';
        switch ($ordem) {
            case 'data_asc':
                $sql .= " ORDER BY p.created_at ASC";
                break;
            case 'paciente_asc':
                $sql .= " ORDER BY p.nome_paciente ASC";
                break;
            case 'paciente_desc':
                $sql .= " ORDER BY p.nome_paciente DESC";
                break;
            default: // data_desc
                $sql .= " ORDER BY p.created_at DESC";
        }

        if (empty($params)) {
            $stmt = $this->db->query($sql);
        } else {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
        }

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
            WHERE p.id = ? AND p.deleted_at IS NULL
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        // Buscar ID da situação "Aguardando Autorização" (padrão para novos pedidos)
        $situacao_padrao = 2; // ID da situação "Aguardando Autorização"

        // Tratar campos vazios
        $telefone = !empty($data['telefone']) ? $data['telefone'] : null;
        $medico_id = !empty($data['medico_id']) ? $data['medico_id'] : null;
        $nome_medico = !empty($data['nome_medico']) ? $data['nome_medico'] : null;
        $fornecedor = !empty($data['fornecedor']) ? $data['fornecedor'] : null;
        $procedimento = !empty($data['procedimento']) ? $data['procedimento'] : null;
        $data_recebimento = !empty($data['data_recebimento']) ? $data['data_recebimento'] : null;
        $origem = !empty($data['origem']) ? $data['origem'] : null;
        $valor_material = !empty($data['valor_material']) ? $data['valor_material'] : null;
        $observacao = !empty($data['observacao']) ? $data['observacao'] : null;

        $stmt = $this->db->prepare("
            INSERT INTO pedidos_novos (
                nome_paciente, telefone, medico_id, nome_medico, convenio,
                fornecedor, procedimento, data_recebimento, origem,
                valor_material, observacao, situacao_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $result = $stmt->execute([
            $data['nome_paciente'],
            $telefone,
            $medico_id,
            $nome_medico,
            $data['convenio'],
            $fornecedor,
            $procedimento,
            $data_recebimento,
            $origem,
            $valor_material,
            $observacao,
            $situacao_padrao // Sempre "Aguardando Autorização" ao criar
        ]);

        if ($result) {
            $pedido_id = $this->db->lastInsertId();

            // Gravar histórico inicial do status
            $this->adicionarHistorico($pedido_id, $situacao_padrao, 'Pedido criado');

            // Disparar mensagem automática ao criar - verificar se existe
            if (file_exists(__DIR__ . '/MensagemConfig.php')) {
                if (!class_exists('MensagemConfig')) {
                    require_once __DIR__ . '/MensagemConfig.php';
                }
                $mensagemModel = new MensagemConfig();
                if (method_exists($mensagemModel, 'dispararAutomatico')) {
                    $mensagemModel->dispararAutomatico($pedido_id, $situacao_padrao);
                }
            }
        }

        return $result;
    }

    public function update($id, $data) {
        // Buscar status atual antes de atualizar
        $pedido_atual = $this->getById($id);
        $status_anterior = $pedido_atual['situacao_id'] ?? null;

        // Tratar campos vazios
        $telefone = !empty($data['telefone']) ? $data['telefone'] : null;
        $medico_id = !empty($data['medico_id']) ? $data['medico_id'] : null;
        $nome_medico = !empty($data['nome_medico']) ? $data['nome_medico'] : null;
        $fornecedor = !empty($data['fornecedor']) ? $data['fornecedor'] : null;
        $procedimento = !empty($data['procedimento']) ? $data['procedimento'] : null;
        $data_recebimento = !empty($data['data_recebimento']) ? $data['data_recebimento'] : null;
        $origem = !empty($data['origem']) ? $data['origem'] : null;
        $valor_material = !empty($data['valor_material']) ? $data['valor_material'] : null;
        $observacao = !empty($data['observacao']) ? $data['observacao'] : null;
        $situacao_id = !empty($data['situacao_id']) ? $data['situacao_id'] : null;

        $stmt = $this->db->prepare("
            UPDATE pedidos_novos SET
                nome_paciente = ?,
                telefone = ?,
                medico_id = ?,
                nome_medico = ?,
                convenio = ?,
                fornecedor = ?,
                procedimento = ?,
                data_recebimento = ?,
                origem = ?,
                valor_material = ?,
                observacao = ?,
                situacao_id = ?
            WHERE id = ?
        ");

        $result = $stmt->execute([
            $data['nome_paciente'],
            $telefone,
            $medico_id,
            $nome_medico,
            $data['convenio'],
            $fornecedor,
            $procedimento,
            $data_recebimento,
            $origem,
            $valor_material,
            $observacao,
            $situacao_id,
            $id
        ]);

        // Se o status mudou, gravar no histórico
        if ($result && $situacao_id && $status_anterior && $situacao_id != $status_anterior) {
            $obs_historico = $data['observacao_status'] ?? 'Status alterado';
            $this->adicionarHistorico($id, $situacao_id, $obs_historico);
        }

        return $result;
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
                ROUND((COUNT(p.id) / NULLIF((SELECT COUNT(*) FROM pedidos_novos WHERE deleted_at IS NULL), 0) * 100), 1) as percentual
            FROM situacoes s
            LEFT JOIN pedidos_novos p ON p.situacao_id = s.id AND p.deleted_at IS NULL
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
            AND deleted_at IS NULL
        ");
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function getStatsEstaSemana() {
        $stmt = $this->db->query("
            SELECT COUNT(*) as total
            FROM pedidos_novos
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            AND deleted_at IS NULL
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
            AND deleted_at IS NULL
        ");
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Adiciona um registro no histórico de status do pedido
     */
    public function adicionarHistorico($pedido_id, $situacao_id, $observacao = null) {
        $usuario_id = $_SESSION['user_id'] ?? null;

        if (!$usuario_id) {
            return false;
        }

        $stmt = $this->db->prepare("
            INSERT INTO pedidos_status_historico (pedido_id, situacao_id, usuario_id, observacao)
            VALUES (?, ?, ?, ?)
        ");

        return $stmt->execute([$pedido_id, $situacao_id, $usuario_id, $observacao]);
    }

    /**
     * Retorna o histórico completo de status de um pedido
     */
    public function getHistorico($pedido_id) {
        $stmt = $this->db->prepare("
            SELECT
                h.*,
                s.nome as situacao_nome,
                s.cor as situacao_cor,
                u.nome as usuario_nome
            FROM pedidos_status_historico h
            JOIN situacoes s ON h.situacao_id = s.id
            JOIN usuarios u ON h.usuario_id = u.id
            WHERE h.pedido_id = ?
            ORDER BY h.created_at DESC
        ");
        $stmt->execute([$pedido_id]);
        return $stmt->fetchAll();
    }
}
