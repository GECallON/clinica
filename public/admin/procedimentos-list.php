<?php
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/models/Usuario.php';
require_once __DIR__ . '/../../src/models/Agendamento.php';
require_once __DIR__ . '/../../src/models/Procedimento.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$procedimentoModel = new Procedimento();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if ($procedimentoModel->delete($_POST['id'])) {
        setFlashMessage('success', 'Procedimento deletado com sucesso!');
    }
    redirect('procedimentos-list.php');
}

$procedimentos = $procedimentoModel->getAll();
$flash = getFlashMessage();
$version = time();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procedimentos - MedAgenda Pro</title>
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
                <?php if ($flash): ?>
                <div class="rounded-2xl border <?= $flash['type'] === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-red-200 bg-red-50 text-red-700' ?> px-4 py-3 text-sm shadow-sm">
                    <i class="fas fa-<?= $flash['type'] === 'success' ? 'circle-check' : 'triangle-exclamation' ?> mr-2"></i>
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
                <?php endif; ?>

                <section class="flex items-center justify-between gap-4">
                    <div>
                        <h1 class="page-title flex items-center gap-2">
                            <span class="icon-chip bg-indigo-100 text-indigo-600">
                                <i class="fas fa-kit-medical"></i>
                            </span>
                            Procedimentos
                        </h1>
                        <p class="page-subtitle mt-1">Gerencie o catálogo de procedimentos cirúrgicos e ambulatoriais.</p>
                    </div>
                    <a href="procedimento-create.php" class="btn-primary">
                        <i class="fas fa-plus"></i>
                        Novo procedimento
                    </a>
                </section>

                <section class="datatable-card overflow-hidden">
                    <table class="min-w-full">
                        <thead class="datatable__head">
                            <tr>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Nome</th>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Descrição</th>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Duração</th>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($procedimentos as $p): ?>
                            <tr class="table-row">
                                <td class="px-5 py-4 font-semibold text-slate-900"><?= htmlspecialchars($p['nome']) ?></td>
                                <td class="px-5 py-4 text-sm text-slate-500">
                                    <?= htmlspecialchars($p['descricao'] ? mb_strimwidth($p['descricao'], 0, 70, '…') : 'Sem descrição') ?>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600"><?= $p['duracao_estimada'] ?> min</td>
                                <td class="px-5 py-4">
                                    <span class="chip <?= $p['ativo'] ? 'chip--success' : 'chip--danger' ?>">
                                        <?= $p['ativo'] ? 'Ativo' : 'Inativo' ?>
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2">
                                        <a href="procedimento-edit.php?id=<?= $p['id'] ?>" class="btn-muted btn-primary--icon">
                                            <i class="fas fa-pen-to-square text-xs"></i>
                                        </a>
                                        <form method="POST" onsubmit="return confirm('Deseja remover este procedimento?')" class="inline">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                            <button class="btn-danger btn-danger--icon">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>
            </main>
        </div>
    </div>
</body>
</html>
