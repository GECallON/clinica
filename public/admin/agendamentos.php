<?php
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/models/Agendamento.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$agendamentoModel = new Agendamento();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $data = $_POST;

        // Upload de arquivo
        if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['arquivo'];
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $ext;
            $filepath = UPLOAD_DIR . $filename;

            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $data['arquivo_anexo'] = $filename;
            }
        }

        if ($agendamentoModel->create($data)) {
            // Obter ID do agendamento criado
            $agendamento_id = $agendamentoModel->getLastInsertId();

            // Enviar email automaticamente
            require_once __DIR__ . '/../../src/models/EmailConfig.php';
            $emailModel = new EmailConfig();
            $emailResult = $emailModel->enviarEmailAgendamento($agendamento_id);

            if ($emailResult['success']) {
                setFlashMessage('success', 'Agendamento criado e emails enviados com sucesso!');
            } else {
                setFlashMessage('success', 'Agendamento criado! (Email: ' . $emailResult['message'] . ')');
            }
        } else {
            setFlashMessage('error', 'Erro ao criar agendamento');
        }
        redirect('agendamentos-list.php');
    }

    if ($action === 'update') {
        $id = $_POST['id'] ?? 0;
        $data = $_POST;

        // Upload de arquivo (se houver novo)
        if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['arquivo'];
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $ext;
            $filepath = UPLOAD_DIR . $filename;

            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                // Deletar arquivo antigo se houver
                $agendamento_antigo = $agendamentoModel->getById($id);
                if ($agendamento_antigo && $agendamento_antigo['arquivo_anexo']) {
                    $old_file = UPLOAD_DIR . $agendamento_antigo['arquivo_anexo'];
                    if (file_exists($old_file)) {
                        unlink($old_file);
                    }
                }
                $data['arquivo_anexo'] = $filename;
            }
        } else {
            // Manter arquivo existente
            $agendamento_antigo = $agendamentoModel->getById($id);
            if ($agendamento_antigo) {
                $data['arquivo_anexo'] = $agendamento_antigo['arquivo_anexo'];
            }
        }

        if ($agendamentoModel->update($id, $data)) {
            // Enviar email automaticamente
            require_once __DIR__ . '/../../src/models/EmailConfig.php';
            $emailModel = new EmailConfig();
            $emailResult = $emailModel->enviarEmailAgendamento($id);

            if ($emailResult['success']) {
                setFlashMessage('success', 'Agendamento atualizado e emails enviados com sucesso!');
            } else {
                setFlashMessage('success', 'Agendamento atualizado! (Email: ' . $emailResult['message'] . ')');
            }
        } else {
            setFlashMessage('error', 'Erro ao atualizar agendamento');
        }
        redirect('agendamentos-list.php');
    }
}

redirect('agendamentos-list.php');
