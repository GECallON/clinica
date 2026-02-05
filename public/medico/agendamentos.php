<?php
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/models/Usuario.php';
require_once __DIR__ . '/../../src/models/Agendamento.php';
require_once __DIR__ . '/../../src/models/Situacao.php';

if (!isLoggedIn() || !isMedico()) {
    redirect('../index.php');
}

$agendamentoModel = new Agendamento();
$situacaoModel = new Situacao();

// Capturar filtros - filtro de médico fixo
$filtros = ['medico_id' => $_SESSION['user_id']];
if (!empty($_GET['busca'])) {
    $filtros['busca'] = $_GET['busca'];
}
if (!empty($_GET['situacao_id'])) {
    $filtros['situacao_id'] = $_GET['situacao_id'];
}
if (!empty($_GET['data_inicio'])) {
    $filtros['data_inicio'] = $_GET['data_inicio'];
}
if (!empty($_GET['data_fim'])) {
    $filtros['data_fim'] = $_GET['data_fim'];
}

$agendamentos = $agendamentoModel->getAllWithFilters($filtros);
$situacoes = $situacaoModel->getAtivos();
$stats_situacao = $agendamentoModel->getStatsBySituacaoMedico($_SESSION['user_id']);

$flash = getFlashMessage();
$version = time();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Agendamentos - MedAgenda Pro</title>
    <script src="https://cdn.tailwindcss.com?v=<?= $version ?>"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/theme.css?v=<?= $version ?>">
</head>
<body>
    <div class="app-shell">
        <div class="app-content">
            <header class="app-header">
                <div class="app-header__brand">
                    <div class="app-header__logo">
                        <i class="fas fa-stethoscope"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold tracking-[0.28em] uppercase text-slate-400">MedAgenda Pro</p>
                        <h1 class="text-xl font-semibold text-slate-900">Meus Agendamentos</h1>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <a href="dashboard.php" class="btn-muted">
                        <i class="fas fa-arrow-left text-xs"></i>
                        Voltar
                    </a>
                    <div class="app-header__user">
                        <div class="app-header__user-avatar">
                            <?= strtoupper(substr($_SESSION['nome'], 0, 1)) ?>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-800"><?= htmlspecialchars($_SESSION['nome']) ?></p>
                            <p class="text-xs text-slate-500">Médico(a)</p>
                        </div>
                    </div>
                    <a href="../logout.php" class="btn-muted">
                        <i class="fas fa-right-from-bracket text-xs"></i>
                        Sair
                    </a>
                </div>
            </header>

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
                                <i class="fas fa-calendar-check"></i>
                            </span>
                            Meus Agendamentos
                        </h1>
                        <p class="page-subtitle mt-1">Visualize todos os seus agendamentos cadastrados.</p>
                    </div>
                </div>

                <!-- Filtros Rápidos por Status -->
                <section class="glass p-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-filter text-indigo-600"></i>
                        Filtros Rápidos por Status
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                        <a href="agendamentos.php" class="group relative overflow-hidden rounded-xl p-4 border-2 transition-all hover:shadow-lg <?= empty($_GET['situacao_id']) ? 'border-indigo-600 bg-indigo-50' : 'border-slate-200 bg-white hover:border-indigo-300' ?>">
                            <div class="flex items-center gap-3">
                                <span class="icon-chip <?= empty($_GET['situacao_id']) ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600' ?>">
                                    <i class="fas fa-calendar-alt"></i>
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">Todos</p>
                                    <p class="text-xs text-slate-500"><?= count($agendamentoModel->getAllWithFilters(['medico_id' => $_SESSION['user_id']])) ?> agendamentos</p>
                                </div>
                            </div>
                        </a>

                        <?php foreach ($situacoes as $situacao):
                            $isActive = isset($_GET['situacao_id']) && $_GET['situacao_id'] == $situacao['id'];
                            $count = 0;
                            foreach ($stats_situacao as $stat) {
                                if ($stat['id'] == $situacao['id']) {
                                    $count = $stat['total'];
                                    break;
                                }
                            }
                            if ($count == 0) continue; // Pular situações sem agendamentos
                        ?>
                        <a href="agendamentos.php?situacao_id=<?= $situacao['id'] ?>" class="group relative overflow-hidden rounded-xl p-4 border-2 transition-all hover:shadow-lg <?= $isActive ? 'bg-opacity-10' : 'border-slate-200 bg-white hover:border-slate-300' ?>" style="<?= $isActive ? 'border-color: ' . $situacao['cor'] . '; background-color: ' . $situacao['cor'] . '15;' : '' ?>">
                            <div class="flex items-center gap-3">
                                <span class="icon-chip <?= $isActive ? 'text-white' : '' ?>" style="<?= $isActive ? 'background: ' . $situacao['cor'] . ';' : 'background: ' . $situacao['cor'] . '22; color: ' . $situacao['cor'] . ';' ?>">
                                    <i class="fas fa-<?php
                                        if (stripos($situacao['nome'], 'autorizado') !== false || stripos($situacao['nome'], 'agendado') !== false) {
                                            echo 'check-circle';
                                        } elseif (stripos($situacao['nome'], 'análise') !== false || stripos($situacao['nome'], 'aguardando') !== false) {
                                            echo 'clock';
                                        } elseif (stripos($situacao['nome'], 'pendente') !== false) {
                                            echo 'hourglass-half';
                                        } elseif (stripos($situacao['nome'], 'arquivado') !== false) {
                                            echo 'archive';
                                        } else {
                                            echo 'tag';
                                        }
                                    ?>"></i>
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-slate-900"><?= htmlspecialchars($situacao['nome']) ?></p>
                                    <p class="text-xs text-slate-500"><?= $count ?> agend.</p>
                                </div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </section>

                <!-- Filtros e Busca -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
                    <form method="GET" class="flex flex-wrap gap-3 items-end">
                        <div class="flex-1 min-w-[180px] max-w-[250px]">
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                                <i class="fas fa-search text-blue-500 mr-1"></i>
                                Buscar Paciente
                            </label>
                            <input type="text" name="busca" value="<?= htmlspecialchars($_GET['busca'] ?? '') ?>" placeholder="Nome do paciente..." class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                        </div>

                        <div class="flex-1 min-w-[160px] max-w-[200px]">
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                                <i class="fas fa-tag text-blue-500 mr-1"></i>
                                Situação
                            </label>
                            <select name="situacao_id" class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                                <option value="">Todas</option>
                                <?php foreach ($situacoes as $situacao): ?>
                                    <option value="<?= $situacao['id'] ?>" <?= (isset($_GET['situacao_id']) && $_GET['situacao_id'] == $situacao['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($situacao['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold">
                                <i class="fas fa-filter mr-1"></i>
                                Filtrar
                            </button>
                            <a href="agendamentos.php" class="px-4 py-2 text-sm bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition font-semibold">
                                <i class="fas fa-times mr-1"></i>
                                Limpar
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Tabela de Agendamentos -->
                <div class="datatable-card overflow-hidden">
                    <table class="min-w-full">
                        <thead class="datatable__head">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Data / Hora</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Paciente</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Procedimento</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Hospital</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Situação</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (count($agendamentos) > 0): ?>
                                <?php foreach ($agendamentos as $agendamento): ?>
                                <tr class="table-row">
                                    <td class="px-6 py-4">
                                        <p class="font-semibold text-slate-900"><?= date('d/m/Y', strtotime($agendamento['data_cirurgia'])) ?></p>
                                        <p class="text-sm text-slate-500"><?= date('H:i', strtotime($agendamento['hora_cirurgia'])) ?></p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-semibold text-slate-900"><?= htmlspecialchars($agendamento['nome_paciente']) ?></p>
                                        <p class="text-xs text-slate-500"><?= htmlspecialchars($agendamento['convenio']) ?></p>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600"><?= htmlspecialchars($agendamento['procedimento_nome']) ?></td>
                                    <td class="px-6 py-4 text-slate-600"><?= htmlspecialchars($agendamento['hospital']) ?></td>
                                    <td class="px-6 py-4">
                                        <span class="chip chip--accent" style="background: <?= $agendamento['situacao_cor'] ?>22; color: <?= $agendamento['situacao_cor'] ?>;">
                                            <?= htmlspecialchars($agendamento['situacao_nome']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex gap-2">
                                            <button onclick="verHistorico(<?= $agendamento['id'] ?>)" class="btn-muted inline-flex items-center gap-2" title="Ver Histórico">
                                                <i class="fas fa-history text-orange-500"></i>
                                            </button>
                                            <button onclick="verDetalhes(<?= $agendamento['id'] ?>)" class="btn-muted inline-flex items-center gap-2" title="Ver Detalhes">
                                                <i class="fas fa-eye text-blue-500"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                        <i class="fas fa-inbox text-4xl mb-3 opacity-30"></i>
                                        <p class="font-medium">Nenhum agendamento encontrado</p>
                                        <p class="text-sm">Você ainda não possui agendamentos cadastrados.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal de Histórico -->
    <div id="modalHistorico" class="hidden fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="glass rounded-3xl max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-2xl">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-slate-900 flex items-center gap-2">
                        <i class="fas fa-history text-orange-500"></i>
                        Histórico de Status
                    </h2>
                    <button onclick="fecharModalHistorico()" class="btn-muted">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div id="historicoContent" class="space-y-4">
                    <div class="text-center py-8">
                        <i class="fas fa-spinner fa-spin text-4xl text-slate-400"></i>
                        <p class="text-slate-500 mt-3">Carregando histórico...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Detalhes -->
    <div id="modalDetalhes" class="hidden fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="glass rounded-3xl max-w-3xl w-full max-h-[90vh] overflow-y-auto shadow-2xl">
            <div class="p-6" id="detalhesContent"></div>
        </div>
    </div>

    <script>
        // Modal de Histórico
        async function verHistorico(agendamentoId) {
            document.getElementById('modalHistorico').classList.remove('hidden');

            try {
                const response = await fetch(`view-agendamento.php?action=get_historico&id=${agendamentoId}`);
                const data = await response.json();

                const content = document.getElementById('historicoContent');

                if (data.success && data.historico.length > 0) {
                    content.innerHTML = `
                        <div class="relative border-l-2 border-slate-200 pl-6 space-y-6">
                            ${data.historico.map((h, index) => `
                                <div class="relative">
                                    <div class="absolute -left-[27px] w-8 h-8 rounded-full flex items-center justify-center" style="background: ${h.situacao_cor}22;">
                                        <div class="w-4 h-4 rounded-full" style="background: ${h.situacao_cor};"></div>
                                    </div>
                                    <div class="glass p-4 rounded-lg">
                                        <div class="flex items-start justify-between mb-2">
                                            <span class="px-3 py-1 text-xs font-semibold rounded-full" style="background: ${h.situacao_cor}22; color: ${h.situacao_cor};">
                                                ${h.situacao_nome}
                                            </span>
                                            <span class="text-xs text-slate-500">${new Date(h.created_at).toLocaleString('pt-BR')}</span>
                                        </div>
                                        <p class="text-sm text-slate-600 mt-2">
                                            <i class="fas fa-user text-slate-400 mr-1"></i>
                                            ${h.usuario_nome}
                                        </p>
                                        ${h.observacao ? `<p class="text-sm text-slate-700 mt-2 italic">"${h.observacao}"</p>` : ''}
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    `;
                } else {
                    content.innerHTML = `
                        <div class="text-center py-8">
                            <i class="fas fa-inbox text-4xl text-slate-300 mb-3"></i>
                            <p class="text-slate-500">Nenhum histórico encontrado</p>
                        </div>
                    `;
                }
            } catch (error) {
                document.getElementById('historicoContent').innerHTML = `
                    <div class="text-center py-8">
                        <i class="fas fa-exclamation-triangle text-4xl text-red-400 mb-3"></i>
                        <p class="text-red-600">Erro ao carregar histórico</p>
                    </div>
                `;
            }
        }

        function fecharModalHistorico() {
            document.getElementById('modalHistorico').classList.add('hidden');
        }

        // Modal de Detalhes
        async function verDetalhes(agendamentoId) {
            document.getElementById('modalDetalhes').classList.remove('hidden');
            document.getElementById('detalhesContent').innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-4xl text-slate-400"></i></div>';

            try {
                const response = await fetch(`view-agendamento.php?id=${agendamentoId}`);
                const html = await response.text();
                document.getElementById('detalhesContent').innerHTML = html;
            } catch (error) {
                document.getElementById('detalhesContent').innerHTML = '<div class="text-center py-8 text-red-600">Erro ao carregar detalhes</div>';
            }
        }

        // Fechar modal ao clicar fora
        document.getElementById('modalHistorico').addEventListener('click', (e) => {
            if (e.target.id === 'modalHistorico') fecharModalHistorico();
        });

        document.getElementById('modalDetalhes').addEventListener('click', (e) => {
            if (e.target.id === 'modalDetalhes') {
                document.getElementById('modalDetalhes').classList.add('hidden');
            }
        });

        // Fechar modal com ESC
        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                fecharModalHistorico();
                document.getElementById('modalDetalhes').classList.add('hidden');
            }
        });
    </script>
</body>
</html>
