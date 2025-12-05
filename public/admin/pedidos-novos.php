<?php
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
                'observacao' => $pedido['observacao'],
                'situacao_id' => $situacao_id
            ];

            if ($pedidoModel->update($id, $data)) {
                // Disparar mensagem automática ao mudar status
                require_once __DIR__ . '/../../src/models/MensagemConfig.php';
                $mensagemModel = new MensagemConfig();
                $mensagemModel->dispararAutomaticoPedido($id, $situacao_id);

                setFlashMessage('success', 'Situação alterada e mensagem enviada!');
            } else {
                setFlashMessage('error', 'Erro ao alterar situação');
            }
        }

        redirect('pedidos-novos-list.php');
    }

    if ($action === 'enviar_whatsapp') {
        require_once __DIR__ . '/../../src/models/MensagemConfig.php';
        $pedido_id = $_POST['pedido_id'] ?? 0;

        $mensagemModel = new MensagemConfig();
        $result = $mensagemModel->enviarWhatsAppPedido($pedido_id);

        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }
}

redirect('pedidos-novos-list.php');
