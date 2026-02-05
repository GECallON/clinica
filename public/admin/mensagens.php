<?php
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/models/MensagemConfig.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$mensagemModel = new MensagemConfig();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $data = $_POST;

        if ($mensagemModel->create($data)) {
            setFlashMessage('success', 'Configuração criada com sucesso!');
        } else {
            setFlashMessage('error', 'Erro ao criar configuração');
        }
        redirect('mensagens-list.php');
    }

    if ($action === 'update') {
        $id = $_POST['id'] ?? 0;
        $data = $_POST;

        if ($mensagemModel->update($id, $data)) {
            setFlashMessage('success', 'Configuração atualizada com sucesso!');
        } else {
            setFlashMessage('error', 'Erro ao atualizar configuração');
        }
        redirect('mensagens-list.php');
    }

    if ($action === 'delete') {
        $id = $_POST['id'] ?? 0;

        if ($mensagemModel->delete($id)) {
            setFlashMessage('success', 'Configuração excluída com sucesso!');
        } else {
            setFlashMessage('error', 'Erro ao excluir configuração');
        }
        redirect('mensagens-list.php');
    }

    if ($action === 'enviar') {
        $agendamento_id = $_POST['agendamento_id'] ?? 0;

        $result = $mensagemModel->enviarWhatsApp($agendamento_id);

        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }
}

redirect('mensagens-list.php');
