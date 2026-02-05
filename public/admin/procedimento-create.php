<?php
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/models/Usuario.php';
require_once __DIR__ . '/../../src/models/Agendamento.php';
require_once __DIR__ . '/../../src/models/Procedimento.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$procedimentoModel = new Procedimento();
$error = '';
$version = time();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($procedimentoModel->create($_POST)) {
        setFlashMessage('success', 'Procedimento criado com sucesso!');
        redirect('procedimentos-list.php');
    } else {
        $error = 'Erro ao criar procedimento';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Procedimento - MedAgenda Pro</title>
    <script src="https://cdn.tailwindcss.com?v=<?= $version ?>"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/theme.css?v=<?= $version ?>">
</head>
<body>
    <div class="app-shell">
        <?php include 'includes/sidebar.php'; ?>

        <div class="app-content">
            <?php include 'includes/header.php'; ?>

            <main class="space-y-6">
                <section class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <a href="procedimentos-list.php" class="btn-muted btn-primary--icon">
                                <i class="fas fa-arrow-left"></i>
                            </a>
                            <div>
                                <h1 class="page-title flex items-center gap-2">
                                    <span class="icon-chip bg-indigo-100 text-indigo-600">
                                        <i class="fas fa-kit-medical"></i>
                                    </span>
                                    Novo procedimento
                                </h1>
                                <p class="page-subtitle mt-1">Cadastre um procedimento para o catálogo da clínica.</p>
                            </div>
                        </div>
                </section>

                <?php if ($error): ?>
                <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
                    <i class="fas fa-triangle-exclamation mr-2"></i><?= $error ?>
                </div>
                <?php endif; ?>

                <section class="glass p-6 max-w-2xl">
                    <form method="POST" class="space-y-5">
                        <div class="form-field">
                            <label for="nome"><i class="fas fa-tag mr-2 text-indigo-500 text-xs"></i>Nome do procedimento *</label>
                            <input id="nome" name="nome" type="text" class="w-full" required placeholder="Ex.: Artroscopia de Joelho">
                        </div>

                        <div class="form-field">
                            <label for="descricao"><i class="fas fa-align-left mr-2 text-indigo-500 text-xs"></i>Descrição</label>
                            <textarea id="descricao" name="descricao" rows="3" class="w-full" placeholder="Resumo do procedimento e observações importantes."></textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-field">
                                <label for="duracao_estimada"><i class="fas fa-clock mr-2 text-indigo-500 text-xs"></i>Duração estimada (min)</label>
                                <input id="duracao_estimada" name="duracao_estimada" type="number" min="1" value="60" class="w-full">
                            </div>
                            <div class="form-field">
                                <label class="flex items-center gap-2 text-sm font-medium text-slate-600">
                                    <input type="checkbox" name="ativo" value="1" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-400" checked>
                                    Procedimento ativo
                                </label>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3">
                            <a href="procedimentos-list.php" class="btn-muted">
                                <i class="fas fa-xmark text-xs"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-check"></i>
                                Criar procedimento
                            </button>
                        </div>
                    </form>
                </section>
            </main>
        </div>
    </div>
</body>
</html>
