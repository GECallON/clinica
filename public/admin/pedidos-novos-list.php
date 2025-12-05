<?php
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/models/Usuario.php';
require_once __DIR__ . '/../../src/models/PedidoNovo.php';
require_once __DIR__ . '/../../src/models/Situacao.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$pedidoModel = new PedidoNovo();
$situacaoModel = new Situacao();
$pedidos = $pedidoModel->getAll();
$situacoes = $situacaoModel->getAtivos();
$flash = getFlashMessage();
$version = time();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos Novos - MedAgenda Pro</title>
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
                            <span class="icon-chip bg-blue-100 text-blue-600">
                                <i class="fas fa-file-medical"></i>
                            </span>
                            Pedidos
                        </h1>
                        <p class="page-subtitle mt-1">Visualize e gerencie todos os pedidos registrados.</p>
                    </div>
                    <a href="pedidos-novos-create.php" class="btn-primary">
                        <i class="fas fa-plus"></i>
                        Novo pedido
                    </a>
                </div>

                <div class="datatable-card overflow-hidden">
                    <table class="min-w-full">
                        <thead class="datatable__head">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Paciente</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Médico</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Convênio</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Fornecedor</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Alterar Situação</th>
                                <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">WhatsApp</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Data</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (count($pedidos) > 0): ?>
                                <?php foreach ($pedidos as $pedido): ?>
                                <tr class="table-row">
                                    <td class="px-6 py-4">
                                        <p class="font-semibold text-slate-900"><?= htmlspecialchars($pedido['nome_paciente']) ?></p>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600"><?= htmlspecialchars($pedido['medico_nome'] ?? '-') ?></td>
                                    <td class="px-6 py-4 text-slate-600"><?= htmlspecialchars($pedido['convenio']) ?></td>
                                    <td class="px-6 py-4 text-slate-600"><?= htmlspecialchars($pedido['fornecedor'] ?? '-') ?></td>
                                    <td class="px-6 py-4">
                                        <select onchange="alterarSituacao(<?= $pedido['id'] ?>, this.value, <?= $pedido['situacao_id'] ?>)" class="text-sm border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500" style="border-color: <?= $pedido['situacao_cor'] ?? '#ccc' ?>; background: <?= $pedido['situacao_cor'] ?? '#f5f5f5' ?>22;">
                                            <?php foreach ($situacoes as $s): ?>
                                                <option value="<?= $s['id'] ?>" <?= $pedido['situacao_id'] == $s['id'] ? 'selected' : '' ?> style="background: <?= $s['cor'] ?>22; color: <?= $s['cor'] ?>;">
                                                    <?= htmlspecialchars($s['nome']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <button
                                            onclick="enviarWhatsApp(<?= $pedido['id'] ?>)"
                                            id="whatsapp-btn-<?= $pedido['id'] ?>"
                                            class="w-10 h-10 rounded-full flex items-center justify-center transition <?= $pedido['whatsapp_enviado'] ? 'bg-green-500 hover:bg-green-600' : 'bg-gray-200 hover:bg-gray-300' ?>"
                                            title="<?= $pedido['whatsapp_enviado'] ? 'Mensagem enviada em ' . date('d/m/Y H:i', strtotime($pedido['whatsapp_enviado_em'])) : 'Enviar mensagem via WhatsApp' ?>">
                                            <i class="fab fa-whatsapp text-xl <?= $pedido['whatsapp_enviado'] ? 'text-white' : 'text-gray-600' ?>"></i>
                                        </button>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-500"><?= date('d/m/Y H:i', strtotime($pedido['created_at'])) ?></td>
                                    <td class="px-6 py-4">
                                        <div class="flex gap-2">
                                            <a href="pedidos-novos-create.php?id=<?= $pedido['id'] ?>" class="btn-muted inline-flex items-center gap-2">
                                                <i class="fas fa-edit text-blue-500"></i>
                                            </a>
                                            <button onclick="deletePedido(<?= $pedido['id'] ?>)" class="btn-muted inline-flex items-center gap-2 text-red-600 hover:bg-red-50">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                        <i class="fas fa-inbox text-4xl mb-3 opacity-30"></i>
                                        <p class="font-medium">Nenhum pedido cadastrado</p>
                                        <p class="text-sm">Clique em "Novo pedido" para criar o primeiro registro.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <script>
        function deletePedido(id) {
            if (confirm('Tem certeza que deseja excluir este pedido?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'pedidos-novos.php';

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

        async function alterarSituacao(pedidoId, situacaoIdNova, situacaoIdAntiga) {
            if (situacaoIdNova == situacaoIdAntiga) return;

            if (confirm('Confirma a alteração da situação? Uma mensagem será enviada automaticamente se configurada.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'pedidos-novos.php';

                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'update_status';

                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id';
                idInput.value = pedidoId;

                const situacaoInput = document.createElement('input');
                situacaoInput.type = 'hidden';
                situacaoInput.name = 'situacao_id';
                situacaoInput.value = situacaoIdNova;

                form.appendChild(actionInput);
                form.appendChild(idInput);
                form.appendChild(situacaoInput);
                document.body.appendChild(form);
                form.submit();
            } else {
                // Recarregar para reverter o select
                location.reload();
            }
        }

        async function enviarWhatsApp(id) {
            const btn = document.getElementById('whatsapp-btn-' + id);
            const icon = btn.querySelector('i');

            // Desabilitar botão
            btn.disabled = true;
            icon.className = 'fas fa-spinner fa-spin text-xl text-white';
            btn.className = 'w-10 h-10 rounded-full flex items-center justify-center transition bg-blue-500';

            try {
                const formData = new FormData();
                formData.append('action', 'enviar_whatsapp');
                formData.append('pedido_id', id);

                const response = await fetch('pedidos-novos.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    // Sucesso - botão verde
                    btn.className = 'w-10 h-10 rounded-full flex items-center justify-center transition bg-green-500 hover:bg-green-600';
                    icon.className = 'fab fa-whatsapp text-xl text-white';
                    btn.title = 'Mensagem enviada com sucesso!';

                    alert('✅ ' + result.message);
                } else {
                    // Erro - voltar ao estado anterior
                    btn.className = 'w-10 h-10 rounded-full flex items-center justify-center transition bg-gray-200 hover:bg-gray-300';
                    icon.className = 'fab fa-whatsapp text-xl text-gray-600';

                    alert('❌ ' + result.message);
                }
            } catch (error) {
                // Erro de rede
                btn.className = 'w-10 h-10 rounded-full flex items-center justify-center transition bg-gray-200 hover:bg-gray-300';
                icon.className = 'fab fa-whatsapp text-xl text-gray-600';

                alert('❌ Erro ao enviar mensagem. Tente novamente.');
                console.error(error);
            } finally {
                btn.disabled = false;
            }
        }
    </script>
</body>
</html>
