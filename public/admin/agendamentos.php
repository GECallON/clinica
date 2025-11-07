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
            setFlashMessage('success', 'Agendamento criado com sucesso!');
        } else {
            setFlashMessage('error', 'Erro ao criar agendamento');
        }
        redirect('agendamentos-list.php');
    }
}

redirect('agendamentos-list.php');
