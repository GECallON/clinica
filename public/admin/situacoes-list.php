<?php
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/models/Usuario.php';
require_once __DIR__ . '/../../src/models/Agendamento.php';
require_once __DIR__ . '/../../src/models/Procedimento.php';
require_once __DIR__ . '/../../src/models/Situacao.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$situacaoModel = new Situacao();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if ($situacaoModel->delete($_POST['id'])) {
        setFlashMessage('success', 'Situação removida com sucesso!');
    }
    redirect('situacoes-list.php');
}

$situacoes = $situacaoModel->getAll();
$flash = getFlashMessage();
$version = time();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Situações - MedAgenda Pro</title>
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
                                <i class="fas fa-swatchbook"></i>
                            </span>
                            Situações
                        </h1>
                        <p class="page-subtitle mt-1">Defina os estados utilizados no fluxo dos agendamentos.</p>
                    </div>
                    <a href="situacao-create.php" class="btn-primary">
                        <i class="fas fa-plus"></i>
                        Nova situação
                    </a>
                </section>

                <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    <?php foreach ($situacoes as $s): ?>
                    <article class="glass p-5 flex flex-col justify-between">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="icon-chip text-white" style="background-color: <?= $s['cor'] ?>">
                                <i class="fas fa-tag"></i>
                            </span>
                            <div>
                                <h3 class="text-sm font-semibold text-slate-900"><?= htmlspecialchars($s['nome']) ?></h3>
                                <p class="text-xs text-slate-400 uppercase tracking-[0.16em]"><?= $s['cor'] ?></p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="chip <?= $s['ativo'] ? 'chip--success' : 'chip--danger' ?>">
                                <?= $s['ativo'] ? 'Ativa' : 'Inativa' ?>
                            </span>
                            <div class="flex items-center gap-2">
                                <a href="situacao-edit.php?id=<?= $s['id'] ?>" class="btn-muted btn-primary--icon">
                                    <i class="fas fa-pen text-xs"></i>
                                </a>
                                <form method="POST" onsubmit="return confirm('Deseja remover esta situação?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                    <button class="btn-danger btn-danger--icon">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </section>
            </main>
        </div>
    </div>
</body>
</html>
