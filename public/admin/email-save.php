<?php
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/models/EmailConfig.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$emailConfigModel = new EmailConfig();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create' || $action === 'update') {
        $data = $_POST;

        if ($action === 'create') {
            if ($emailConfigModel->create($data)) {
                setFlashMessage('success', 'Configuração de email criada com sucesso!');
            } else {
                setFlashMessage('error', 'Erro ao criar configuração');
            }
        } else {
            $id = $_POST['id'] ?? 0;
            if ($emailConfigModel->update($id, $data)) {
                setFlashMessage('success', 'Configuração de email atualizada com sucesso!');
            } else {
                setFlashMessage('error', 'Erro ao atualizar configuração');
            }
        }
    }
}

redirect('email-config.php');
