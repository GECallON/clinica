<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/tmp/pedidos-novos-error.log');

try {
    require_once __DIR__ . '/../../src/config.php';
    require_once __DIR__ . '/../../src/models/PedidoNovo.php';

    if (!isLoggedIn() || !isAdmin()) {
        redirect('../index.php');
    }

    $pedidoModel = new PedidoNovo();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'create') {
            $data = $_POST;

            if ($pedidoModel->create($data)) {
                setFlashMessage('success', 'Pedido criado com sucesso!');
            } else {
                setFlashMessage('error', 'Erro ao criar pedido');
            }
            redirect('pedidos-novos-list.php');
        }

        if ($action === 'update') {
            $id = $_POST['id'] ?? 0;
            $data = $_POST;

            if ($pedidoModel->update($id, $data)) {
                setFlashMessage('success', 'Pedido atualizado com sucesso!');
            } else {
                setFlashMessage('error', 'Erro ao atualizar pedido');
            }
            redirect('pedidos-novos-list.php');
        }

        if ($action === 'delete') {
            $id = $_POST['id'] ?? 0;

            if ($pedidoModel->delete($id)) {
                setFlashMessage('success', 'Pedido excluído com sucesso!');
            } else {
                setFlashMessage('error', 'Erro ao excluir pedido');
            }
            redirect('pedidos-novos-list.php');
        }

        if ($action === 'update_status') {
            $id = $_POST['id'] ?? 0;
            $situacao_id = $_POST['situacao_id'] ?? null;

            // Buscar pedido atual
            $pedido = $pedidoModel->getById($id);

            if ($pedido) {
                // Atualizar apenas o status
                $data = [
                    'nome_paciente' => $pedido['nome_paciente'],
                    'telefone' => $pedido['telefone'],
                    'medico_id' => $pedido['medico_id'],
                    'nome_medico' => $pedido['nome_medico'],
                    'convenio' => $pedido['convenio'],
                    'fornecedor' => $pedido['fornecedor'],
                    'procedimento' => $pedido['procedimento'],
                    'data_recebimento' => $pedido['data_recebimento'],
                    'origem' => $pedido['origem'],
                    'valor_material' => $pedido['valor_material'],
                    'observacao' => $pedido['observacao'],
                    'situacao_id' => $situacao_id
                ];

                if ($pedidoModel->update($id, $data)) {
                    // Disparar mensagem automática ao mudar status
                    if (file_exists(__DIR__ . '/../../src/models/MensagemConfig.php')) {
                        require_once __DIR__ . '/../../src/models/MensagemConfig.php';
                        $mensagemModel = new MensagemConfig();
                        if (method_exists($mensagemModel, 'dispararAutomaticoPedido')) {
                            $mensagemModel->dispararAutomaticoPedido($id, $situacao_id);
                        }
                    }

                    setFlashMessage('success', 'Situação alterada com sucesso!');
                } else {
                    setFlashMessage('error', 'Erro ao alterar situação');
                }
            }

            redirect('pedidos-novos-list.php');
        }

        if ($action === 'enviar_whatsapp') {
            if (file_exists(__DIR__ . '/../../src/models/MensagemConfig.php')) {
                require_once __DIR__ . '/../../src/models/MensagemConfig.php';
                $pedido_id = $_POST['pedido_id'] ?? 0;

                $mensagemModel = new MensagemConfig();
                $result = $mensagemModel->enviarWhatsAppPedido($pedido_id);

                header('Content-Type: application/json');
                echo json_encode($result);
                exit;
            }
        }

        if ($action === 'criar_agendamento') {
            require_once __DIR__ . '/../../src/models/Agendamento.php';
            require_once __DIR__ . '/../../src/models/Procedimento.php';

            $pedido_id = $_POST['pedido_id'] ?? 0;
            $pedido = $pedidoModel->getById($pedido_id);

            if (!$pedido) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Pedido não encontrado']);
                exit;
            }

            // Buscar ID do procedimento pelo nome
            $procedimentoModel = new Procedimento();
            $procedimento_nome = $_POST['procedimento'] ?? $pedido['procedimento'];
            $procedimentos = $procedimentoModel->getAtivos();
            $procedimento_id = null;

            foreach ($procedimentos as $proc) {
                if (stripos($proc['nome'], $procedimento_nome) !== false) {
                    $procedimento_id = $proc['id'];
                    break;
                }
            }

            // Se não encontrou, usar o primeiro procedimento ativo
            if (!$procedimento_id && count($procedimentos) > 0) {
                $procedimento_id = $procedimentos[0]['id'];
            }

            $agendamentoModel = new Agendamento();
            $agendamento_data = [
                'nome_solicitante' => $_POST['nome_solicitante'],
                'telefone' => $_POST['telefone_solicitante'],
                'nome_paciente' => $_POST['nome_paciente'],
                'protocolo' => $_POST['protocolo'] ?? null,
                'procedimento_id' => $procedimento_id,
                'data_cirurgia' => $_POST['data_cirurgia'],
                'hora_cirurgia' => $_POST['hora_cirurgia'],
                'hospital' => $_POST['hospital'],
                'fornecedor' => $_POST['fornecedor'] ?? null,
                'valor_material' => $_POST['valor_material'] ?? null,
                'medico_id' => $_POST['medico_id'],
                'convenio' => $_POST['convenio'],
                'situacao_id' => 14, // Agendado
                'material_necessario' => $_POST['material_necessario'] ?? null,
                'observacoes' => $_POST['observacao'] ?? null
            ];

            if ($agendamentoModel->create($agendamento_data)) {
                // Atualizar status do pedido para "Agendado"
                $pedido_atualizado = [
                    'nome_paciente' => $pedido['nome_paciente'],
                    'telefone' => $pedido['telefone'],
                    'medico_id' => $pedido['medico_id'],
                    'nome_medico' => $pedido['nome_medico'],
                    'convenio' => $pedido['convenio'],
                    'fornecedor' => $pedido['fornecedor'],
                    'procedimento' => $pedido['procedimento'],
                    'data_recebimento' => $pedido['data_recebimento'],
                    'origem' => $pedido['origem'],
                    'valor_material' => $pedido['valor_material'],
                    'observacao' => $pedido['observacao'],
                    'situacao_id' => 14,
                    'observacao_status' => 'Agendamento criado automaticamente'
                ];

                $pedidoModel->update($pedido_id, $pedido_atualizado);

                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Agendamento criado com sucesso!']);
                exit;
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Erro ao criar agendamento']);
                exit;
            }
        }
    }

    // GET endpoints
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = $_GET['action'] ?? '';

        if ($action === 'get_historico') {
            $id = $_GET['id'] ?? 0;
            $historico = $pedidoModel->getHistorico($id);

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'historico' => $historico]);
            exit;
        }

        if ($action === 'get_pedido') {
            $id = $_GET['id'] ?? 0;
            $pedido = $pedidoModel->getById($id);

            header('Content-Type: application/json');
            echo json_encode(['success' => !!$pedido, 'pedido' => $pedido]);
            exit;
        }
    }

    redirect('pedidos-novos-list.php');

} catch (Exception $e) {
    error_log("ERRO em pedidos-novos.php: " . $e->getMessage() . " em " . $e->getFile() . ":" . $e->getLine());
    die("ERRO: " . $e->getMessage() . "<br>FILE: " . $e->getFile() . "<br>LINE: " . $e->getLine() . "<br>TRACE:<pre>" . $e->getTraceAsString() . "</pre>");
} catch (Error $e) {
    error_log("FATAL ERROR em pedidos-novos.php: " . $e->getMessage() . " em " . $e->getFile() . ":" . $e->getLine());
    die("FATAL ERROR: " . $e->getMessage() . "<br>FILE: " . $e->getFile() . "<br>LINE: " . $e->getLine() . "<br>TRACE:<pre>" . $e->getTraceAsString() . "</pre>");
}
