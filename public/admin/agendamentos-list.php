<?php
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/models/Usuario.php';
require_once __DIR__ . '/../../src/models/Agendamento.php';
require_once __DIR__ . '/../../src/models/Procedimento.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$agendamentoModel = new Agendamento();
$agendamentos = $agendamentoModel->getAll();
$flash = getFlashMessage();
$version = time();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendamentos - MedAgenda Pro</title>
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
                            <span class="icon-chip bg-indigo-100 text-indigo-600">
                                <i class="fas fa-calendar-alt"></i>
                            </span>
                            Agendamentos
                        </h1>
                        <p class="page-subtitle mt-1">Visualize, filtre e acompanhe todas as cirurgias em andamento.</p>
                    </div>
                    <a href="agendamento-create.php" class="btn-primary">
                        <i class="fas fa-plus"></i>
                        Novo agendamento
                    </a>
                </div>

                <div class="datatable-card overflow-hidden">
                    <table class="min-w-full">
                        <thead class="datatable__head">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Data/Hora</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Paciente</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Médico</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Procedimento</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Hospital</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Situação</th>
                                <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">WhatsApp</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($agendamentos as $ag): ?>
                            <tr class="table-row">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="metric-card__icon !mb-0">
                                            <i class="fas fa-calendar-day"></i>
                                        </span>
                                        <div>
                                            <p class="font-semibold text-slate-900"><?= date('d/m/Y', strtotime($ag['data_cirurgia'])) ?></p>
                                            <p class="text-sm text-slate-500"><?= date('H:i', strtotime($ag['hora_cirurgia'])) ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-slate-900"><?= htmlspecialchars($ag['nome_paciente']) ?></p>
                                    <p class="text-sm text-slate-500"><?= htmlspecialchars($ag['convenio']) ?></p>
                                </td>
                                <td class="px-6 py-4 text-slate-600"><?= htmlspecialchars($ag['medico_nome']) ?></td>
                                <td class="px-6 py-4 text-slate-600"><?= htmlspecialchars($ag['procedimento_nome']) ?></td>
                                <td class="px-6 py-4 text-slate-600"><?= htmlspecialchars($ag['hospital']) ?></td>
                                <td class="px-6 py-4">
                                    <span class="chip chip--accent" style="background: <?= $ag['situacao_cor'] ?>22; color: <?= $ag['situacao_cor'] ?>;">
                                        <?= htmlspecialchars($ag['situacao_nome']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button
                                        onclick="enviarWhatsApp(<?= $ag['id'] ?>)"
                                        id="whatsapp-btn-<?= $ag['id'] ?>"
                                        class="w-10 h-10 rounded-full flex items-center justify-center transition <?= $ag['whatsapp_enviado'] ? 'bg-green-500 hover:bg-green-600' : 'bg-gray-200 hover:bg-gray-300' ?>"
                                        title="<?= $ag['whatsapp_enviado'] ? 'Mensagem enviada em ' . date('d/m/Y H:i', strtotime($ag['whatsapp_enviado_em'])) : 'Enviar mensagem via WhatsApp' ?>">
                                        <i class="fab fa-whatsapp text-xl <?= $ag['whatsapp_enviado'] ? 'text-white' : 'text-gray-600' ?>"></i>
                                    </button>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex gap-2">
                                        <a href="agendamento-create.php?id=<?= $ag['id'] ?>" class="btn-muted inline-flex items-center gap-2">
                                            <i class="fas fa-edit text-blue-500"></i>
                                            Editar
                                        </a>
                                        <button onclick="viewDetails(<?= $ag['id'] ?>)" class="btn-muted inline-flex items-center gap-2">
                                            <i class="fas fa-eye text-slate-500"></i>
                                            Detalhes
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <script>
        function viewDetails(id) {
            window.location.href = 'agendamento-view.php?id=' + id;
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
                formData.append('action', 'enviar');
                formData.append('agendamento_id', id);

                const response = await fetch('mensagens.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    // Sucesso - botão verde
                    btn.className = 'w-10 h-10 rounded-full flex items-center justify-center transition bg-green-500 hover:bg-green-600';
                    icon.className = 'fab fa-whatsapp text-xl text-white';
                    btn.title = 'Mensagem enviada com sucesso!';

                    // Mostrar notificação
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
