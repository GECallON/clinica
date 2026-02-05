<?php
require_once __DIR__ . '/../config.php';

class MensagemConfig {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll() {
        $stmt = $this->db->query("
            SELECT * FROM mensagens_config
            ORDER BY created_at DESC
        ");
        return $stmt->fetchAll();
    }

    public function getAtiva() {
        $stmt = $this->db->query("
            SELECT * FROM mensagens_config
            WHERE ativo = 1
            LIMIT 1
        ");
        return $stmt->fetch();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM mensagens_config WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO mensagens_config (
                nome, dominio, canal, texto_mensagem, ativo,
                tipo_evento, situacao_id, enviar_automatico, dias_antes_lembrete
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            $data['nome'],
            $data['dominio'],
            $data['canal'],
            $data['texto_mensagem'],
            isset($data['ativo']) ? 1 : 0,
            $data['tipo_evento'] ?? 'manual',
            $data['situacao_id'] ?? null,
            isset($data['enviar_automatico']) ? 1 : 0,
            $data['dias_antes_lembrete'] ?? null
        ]);
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE mensagens_config SET
                nome = ?,
                dominio = ?,
                canal = ?,
                texto_mensagem = ?,
                ativo = ?,
                tipo_evento = ?,
                situacao_id = ?,
                enviar_automatico = ?,
                dias_antes_lembrete = ?
            WHERE id = ?
        ");

        return $stmt->execute([
            $data['nome'],
            $data['dominio'],
            $data['canal'],
            $data['texto_mensagem'],
            isset($data['ativo']) ? 1 : 0,
            $data['tipo_evento'] ?? 'manual',
            $data['situacao_id'] ?? null,
            isset($data['enviar_automatico']) ? 1 : 0,
            $data['dias_antes_lembrete'] ?? null,
            $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM mensagens_config WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function enviarWhatsApp($agendamento_id) {
        // Buscar configuração ativa
        $config = $this->getAtiva();
        if (!$config) {
            return ['success' => false, 'message' => 'Nenhuma configuração ativa encontrada'];
        }

        // Buscar agendamento
        require_once __DIR__ . '/Agendamento.php';
        $agendamentoModel = new Agendamento();
        $agendamento = $agendamentoModel->getById($agendamento_id);

        if (!$agendamento) {
            return ['success' => false, 'message' => 'Agendamento não encontrado'];
        }

        if (!$agendamento['telefone']) {
            return ['success' => false, 'message' => 'Telefone não cadastrado'];
        }

        // Preparar mensagem substituindo variáveis
        $texto = $config['texto_mensagem'];
        $texto = str_replace('{data}', date('d/m/Y', strtotime($agendamento['data_cirurgia'])), $texto);
        $texto = str_replace('{hora}', date('H:i', strtotime($agendamento['hora_cirurgia'])), $texto);
        $texto = str_replace('{hospital}', $agendamento['hospital'], $texto);
        $texto = str_replace('{paciente}', $agendamento['nome_paciente'], $texto);
        $texto = str_replace('{medico}', $agendamento['medico_nome'], $texto);
        $texto = str_replace('{procedimento}', $agendamento['procedimento_nome'], $texto);

        // Limpar telefone (remover caracteres especiais)
        $telefone = preg_replace('/[^0-9]/', '', $agendamento['telefone']);

        // Montar URL da API
        $url = "http://{$config['dominio']}/api/mvtobe/{$config['canal']}";
        $url .= "?numero={$telefone}";
        $url .= "&texto=" . urlencode($texto);
        $url .= "&mensage_ativa=1";

        // Enviar requisição
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Marcar como enviado no banco
        if ($httpCode === 200) {
            $stmt = $this->db->prepare("
                UPDATE agendamentos
                SET whatsapp_enviado = 1, whatsapp_enviado_em = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$agendamento_id]);

            return ['success' => true, 'message' => 'Mensagem enviada com sucesso!'];
        } else {
            return ['success' => false, 'message' => 'Erro ao enviar mensagem. Código: ' . $httpCode];
        }
    }

    public function getMensagemPorStatus($situacao_id) {
        $stmt = $this->db->prepare("
            SELECT * FROM mensagens_config
            WHERE situacao_id = ? AND tipo_evento = 'mudanca_status' AND enviar_automatico = 1 AND ativo = 1
            LIMIT 1
        ");
        $stmt->execute([$situacao_id]);
        return $stmt->fetch();
    }

    public function dispararAutomatico($agendamento_id, $situacao_id_nova) {
        // Buscar mensagem configurada para este status
        $mensagem = $this->getMensagemPorStatus($situacao_id_nova);

        if (!$mensagem) {
            return ['success' => false, 'message' => 'Nenhuma mensagem automática configurada para este status'];
        }

        // Buscar agendamento
        require_once __DIR__ . '/Agendamento.php';
        $agendamentoModel = new Agendamento();
        $agendamento = $agendamentoModel->getById($agendamento_id);

        if (!$agendamento || !$agendamento['telefone']) {
            return ['success' => false, 'message' => 'Agendamento sem telefone'];
        }

        // Preparar mensagem
        $texto = $mensagem['texto_mensagem'];
        $texto = str_replace('{data}', date('d/m/Y', strtotime($agendamento['data_cirurgia'])), $texto);
        $texto = str_replace('{hora}', date('H:i', strtotime($agendamento['hora_cirurgia'])), $texto);
        $texto = str_replace('{hospital}', $agendamento['hospital'], $texto);
        $texto = str_replace('{paciente}', $agendamento['nome_paciente'], $texto);
        $texto = str_replace('{medico}', $agendamento['medico_nome'], $texto);
        $texto = str_replace('{procedimento}', $agendamento['procedimento_nome'], $texto);

        // Limpar telefone
        $telefone = preg_replace('/[^0-9]/', '', $agendamento['telefone']);

        // Montar URL
        $url = "http://{$mensagem['dominio']}/api/mvtobe/{$mensagem['canal']}";
        $url .= "?numero={$telefone}";
        $url .= "&texto=" . urlencode($texto);
        $url .= "&mensage_ativa=1";

        // Enviar
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            // Registrar no log (opcional - pode criar tabela de log depois)
            return ['success' => true, 'message' => 'Mensagem automática enviada!'];
        }

        return ['success' => false, 'message' => 'Erro ao enviar'];
    }

    public function getByTipoEvento($tipo) {
        $stmt = $this->db->prepare("
            SELECT * FROM mensagens_config
            WHERE tipo_evento = ? AND ativo = 1
            ORDER BY created_at DESC
        ");
        $stmt->execute([$tipo]);
        return $stmt->fetchAll();
    }

    public function enviarWhatsAppPedido($pedido_id) {
        // Buscar configuração ativa
        $config = $this->getAtiva();
        if (!$config) {
            return ['success' => false, 'message' => 'Nenhuma configuração ativa encontrada'];
        }

        // Buscar pedido
        require_once __DIR__ . '/PedidoNovo.php';
        $pedidoModel = new PedidoNovo();
        $pedido = $pedidoModel->getById($pedido_id);

        if (!$pedido) {
            return ['success' => false, 'message' => 'Pedido não encontrado'];
        }

        if (!$pedido['telefone']) {
            return ['success' => false, 'message' => 'Telefone não cadastrado'];
        }

        // Preparar mensagem
        $texto = $config['texto_mensagem'];
        $texto = str_replace('{paciente}', $pedido['nome_paciente'], $texto);
        $texto = str_replace('{medico}', $pedido['medico_nome'] ?? 'não informado', $texto);
        $texto = str_replace('{convenio}', $pedido['convenio'], $texto);
        $texto = str_replace('{fornecedor}', $pedido['fornecedor'] ?? 'não informado', $texto);

        // Limpar telefone
        $telefone = preg_replace('/[^0-9]/', '', $pedido['telefone']);

        // Montar URL
        $url = "http://{$config['dominio']}/api/mvtobe/{$config['canal']}";
        $url .= "?numero={$telefone}";
        $url .= "&texto=" . urlencode($texto);
        $url .= "&mensage_ativa=1";

        // Enviar
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $stmt = $this->db->prepare("
                UPDATE pedidos_novos
                SET whatsapp_enviado = 1, whatsapp_enviado_em = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$pedido_id]);

            return ['success' => true, 'message' => 'Mensagem enviada com sucesso!'];
        } else {
            return ['success' => false, 'message' => 'Erro ao enviar mensagem. Código: ' . $httpCode];
        }
    }

    public function dispararAutomaticoPedido($pedido_id, $situacao_id_nova) {
        // Buscar mensagem configurada para este status
        $mensagem = $this->getMensagemPorStatus($situacao_id_nova);

        if (!$mensagem) {
            return ['success' => false, 'message' => 'Nenhuma mensagem automática configurada para este status'];
        }

        // Buscar pedido
        require_once __DIR__ . '/PedidoNovo.php';
        $pedidoModel = new PedidoNovo();
        $pedido = $pedidoModel->getById($pedido_id);

        if (!$pedido || !$pedido['telefone']) {
            return ['success' => false, 'message' => 'Pedido sem telefone'];
        }

        // Preparar mensagem
        $texto = $mensagem['texto_mensagem'];
        $texto = str_replace('{paciente}', $pedido['nome_paciente'], $texto);
        $texto = str_replace('{medico}', $pedido['medico_nome'] ?? 'não informado', $texto);
        $texto = str_replace('{convenio}', $pedido['convenio'], $texto);
        $texto = str_replace('{fornecedor}', $pedido['fornecedor'] ?? 'não informado', $texto);

        // Limpar telefone
        $telefone = preg_replace('/[^0-9]/', '', $pedido['telefone']);

        // Montar URL
        $url = "http://{$mensagem['dominio']}/api/mvtobe/{$mensagem['canal']}";
        $url .= "?numero={$telefone}";
        $url .= "&texto=" . urlencode($texto);
        $url .= "&mensage_ativa=1";

        // Enviar
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            return ['success' => true, 'message' => 'Mensagem automática enviada!'];
        }

        return ['success' => false, 'message' => 'Erro ao enviar'];
    }
}
