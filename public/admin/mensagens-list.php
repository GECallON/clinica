<?php
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/models/Usuario.php';
require_once __DIR__ . '/../../src/models/MensagemConfig.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$mensagemModel = new MensagemConfig();
$mensagens = $mensagemModel->getAll();
$flash = getFlashMessage();
$version = time();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensagens de Confirmação - MedAgenda Pro</title>
    <script src="https://cdn.tailwindcss.com?v=<?= $version ?>"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="../assets/css/theme.css?v=<?= $version ?>">
</head>
<body>
    <div class="app-shell">
        <?php include 'includes/sidebar.php'; ?>

        <div class="app-content">
            <?php include 'includes/header.php'; ?>

            <main class="space-y-6">
                <?php if ($flash): ?>
                <div class="rounded-2xl border border-<?= $flash['type'] === 'success' ? 'emerald' : 'red' ?>-200 bg-<?= $flash['type'] === 'success' ? 'emerald' : 'red' ?>-50 px-5 py-4 text-sm text-<?= $flash['type'] === 'success' ? 'emerald' : 'red' ?>-700 shadow-sm">
                    <i class="fas fa-<?= $flash['type'] === 'success' ? 'circle-check' : 'triangle-exclamation' ?> mr-2"></i>
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
                <?php endif; ?>

                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 class="page-title flex items-center gap-2">
                            <span class="icon-chip bg-green-100 text-green-600">
                                <i class="fab fa-whatsapp"></i>
                            </span>
                            Mensagens de Confirmação
                        </h1>
                        <p class="page-subtitle mt-1">Configure as mensagens automáticas enviadas via WhatsApp.</p>
                    </div>
                    <a href="mensagem-create.php" class="btn-primary">
                        <i class="fas fa-plus"></i>
                        Nova configuração
                    </a>
                </div>

                <div class="datatable-card overflow-hidden">
                    <table class="min-w-full">
                        <thead class="datatable__head">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Nome</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Domínio</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Canal</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (count($mensagens) > 0): ?>
                                <?php foreach ($mensagens as $msg): ?>
                                <tr class="table-row">
                                    <td class="px-6 py-4">
                                        <p class="font-semibold text-slate-900"><?= htmlspecialchars($msg['nome']) ?></p>
                                        <p class="text-sm text-slate-500 mt-1 truncate max-w-xs"><?= htmlspecialchars(substr($msg['texto_mensagem'], 0, 80)) ?>...</p>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600"><?= htmlspecialchars($msg['dominio']) ?></td>
                                    <td class="px-6 py-4">
                                        <span class="chip chip--accent"><?= $msg['canal'] ?></span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if ($msg['ativo']): ?>
                                            <span class="chip bg-green-100 text-green-700">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                Ativa
                                            </span>
                                        <?php else: ?>
                                            <span class="chip bg-gray-100 text-gray-700">
                                                <i class="fas fa-times-circle mr-1"></i>
                                                Inativa
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex gap-2">
                                            <a href="mensagem-edit.php?id=<?= $msg['id'] ?>" class="btn-muted inline-flex items-center gap-2">
                                                <i class="fas fa-edit text-slate-500"></i>
                                            </a>
                                            <button onclick="deleteMensagem(<?= $msg['id'] ?>)" class="btn-muted inline-flex items-center gap-2 text-red-600 hover:bg-red-50">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                        <i class="fas fa-inbox text-4xl mb-3 opacity-30"></i>
                                        <p class="font-medium">Nenhuma configuração cadastrada</p>
                                        <p class="text-sm">Clique em "Nova configuração" para criar a primeira.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Informações sobre variáveis -->
                <div class="glass p-6">
                    <h2 class="text-lg font-semibold text-slate-900 mb-3 flex items-center gap-2">
                        <i class="fas fa-info-circle text-blue-500"></i>
                        Variáveis disponíveis
                    </h2>
                    <p class="text-sm text-slate-600 mb-4">Use estas variáveis no texto da mensagem para personalizar automaticamente:</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        <div class="p-3 bg-slate-50 rounded-lg border border-slate-200">
                            <code class="text-sm font-mono text-indigo-600">{data}</code>
                            <p class="text-xs text-slate-500 mt-1">Data do procedimento</p>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-lg border border-slate-200">
                            <code class="text-sm font-mono text-indigo-600">{hora}</code>
                            <p class="text-xs text-slate-500 mt-1">Hora do procedimento</p>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-lg border border-slate-200">
                            <code class="text-sm font-mono text-indigo-600">{hospital}</code>
                            <p class="text-xs text-slate-500 mt-1">Nome do hospital</p>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-lg border border-slate-200">
                            <code class="text-sm font-mono text-indigo-600">{paciente}</code>
                            <p class="text-xs text-slate-500 mt-1">Nome do paciente</p>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-lg border border-slate-200">
                            <code class="text-sm font-mono text-indigo-600">{medico}</code>
                            <p class="text-xs text-slate-500 mt-1">Nome do médico</p>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-lg border border-slate-200">
                            <code class="text-sm font-mono text-indigo-600">{procedimento}</code>
                            <p class="text-xs text-slate-500 mt-1">Nome do procedimento</p>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        function deleteMensagem(id) {
            if (confirm('Tem certeza que deseja excluir esta configuração?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'mensagens.php';

                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete';

                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id';
                idInput.value = id;

                form.appendChild(actionInput);
                form.appendChild(idInput);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
