<?php
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/models/Usuario.php';
require_once __DIR__ . '/../../src/models/PedidoNovo.php';

if (!isLoggedIn() || !isMedico()) {
    redirect('../index.php');
}

$pedidoModel = new PedidoNovo();

// Capturar filtros
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

$pedidos = $pedidoModel->getAllWithFilters($filtros);

// Carregar situações para o filtro
if (!class_exists('Situacao')) {
    require_once __DIR__ . '/../../src/models/Situacao.php';
}
$situacaoModel = new Situacao();
$situacoes = $situacaoModel->getAtivos();

// Estatísticas
$total_pedidos = count($pedidoModel->getAllWithFilters(['medico_id' => $_SESSION['user_id']]));
$hoje = date('Y-m-d');
$agora = date('H:i');

$pedidos_hoje = array_filter($pedidos, function($p) use ($hoje) {
    return isset($p['data_recebimento']) && $p['data_recebimento'] === $hoje;
});
$proximos_7_dias = array_filter($pedidos, function ($p) use ($hoje) {
    return isset($p['data_recebimento']) && $p['data_recebimento'] >= $hoje && $p['data_recebimento'] <= date('Y-m-d', strtotime('+7 days'));
});

// Estatísticas por situação para o médico
$stats_situacao = $pedidoModel->getStatsBySituacaoMedico($_SESSION['user_id']);

$pedidos_proximos = array_filter($pedidos_hoje, function ($p) use ($agora) {
    if (!isset($p['created_at'])) return false;
    $hora_criacao = date('H:i', strtotime($p['created_at']));
    $diff_minutos = (strtotime($agora) - strtotime($hora_criacao)) / 60;
    return $diff_minutos >= 0 && $diff_minutos <= 120;
});

$flash = getFlashMessage();
$version = time();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Médico - MedAgenda Pro</title>
    <script src="https://cdn.tailwindcss.com?v=<?= $version ?>"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/theme.css?v=<?= $version ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/locales/pt-br.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <style>
        .view-toggle button.active {
            background: var(--accent-gradient);
            color: #fff;
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.16);
        }
    </style>
</head>
<body>
    <div id="notificationContainer" class="fixed top-24 right-6 z-40 space-y-4 max-w-sm"></div>

    <div class="app-shell">
        <div class="app-content">
            <header class="app-header">
                <div class="app-header__brand">
                    <div class="app-header__logo">
                        <i class="fas fa-stethoscope"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold tracking-[0.28em] uppercase text-slate-400">MedAgenda Pro</p>
                        <h1 class="text-xl font-semibold text-slate-900">Agenda do Médico</h1>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <button id="notificationBtn" class="btn-muted btn-primary--icon relative">
                        <i class="fas fa-bell text-sm"></i>
                        <?php if (count($pedidos_proximos) > 0): ?>
                            <span class="absolute -top-1 -right-1 h-5 w-5 rounded-full bg-rose-500 text-white text-xs font-semibold flex items-center justify-center">
                                <?= count($pedidos_proximos) ?>
                            </span>
                        <?php endif; ?>
                    </button>
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
                <div class="rounded-2xl border <?= $flash['type'] === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-red-200 bg-red-50 text-red-700' ?> px-4 py-3 text-sm shadow-sm">
                    <i class="fas fa-<?= $flash['type'] === 'success' ? 'circle-check' : 'triangle-exclamation' ?> mr-2"></i>
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
                <?php endif; ?>

                <?php if (count($pedidos_proximos) > 0): ?>
                <section class="glass p-6 border border-orange-200">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <span class="icon-chip text-white" style="background: linear-gradient(135deg, #f97316 0%, #ef4444 100%);">
                                <i class="fas fa-exclamation-triangle"></i>
                            </span>
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">Pedidos recentes (últimas 2 horas)</h3>
                                <p class="text-sm text-slate-500">
                                    Você tem <strong><?= count($pedidos_proximos) ?></strong> pedido(s) recebido(s) recentemente.
                                </p>
                            </div>
                        </div>
                        <button type="button" onclick="viewUpcoming()" class="btn-primary">
                            <i class="fas fa-arrow-right"></i>
                            Detalhes
                        </button>
                    </div>
                </section>
                <?php endif; ?>

                <section class="metrics-group">
                    <div class="metric-card">
                        <div class="metric-card__header">
                            <span class="metric-card__icon">
                                <i class="fas fa-file-medical"></i>
                            </span>
                            <p class="metric-card__value"><?= $total_pedidos ?></p>
                        </div>
                        <p class="metric-card__label">Pedidos totais</p>
                    </div>
                    <div class="metric-card">
                        <div class="metric-card__header">
                            <span class="metric-card__icon" style="background: rgba(16, 185, 129, 0.18); color: #10b981;">
                                <i class="fas fa-calendar-day"></i>
                            </span>
                            <p class="metric-card__value"><?= count($pedidos_hoje) ?></p>
                        </div>
                        <p class="metric-card__label">Recebidos hoje</p>
                    </div>
                    <div class="metric-card">
                        <div class="metric-card__header">
                            <span class="metric-card__icon" style="background: rgba(168, 85, 247, 0.18); color: #8b5cf6;">
                                <i class="fas fa-calendar-week"></i>
                            </span>
                            <p class="metric-card__value"><?= count($proximos_7_dias) ?></p>
                        </div>
                        <p class="metric-card__label">Últimos 7 dias</p>
                    </div>
                    <div class="metric-card">
                        <div class="metric-card__header">
                            <span class="metric-card__icon" style="background: rgba(249, 115, 22, 0.18); color: #f97316;">
                                <i class="fas fa-bolt"></i>
                            </span>
                            <p class="metric-card__value"><?= count($pedidos_proximos) ?></p>
                        </div>
                        <p class="metric-card__label">Últimas 2 horas</p>
                    </div>
                </section>

                <!-- Filtros Rápidos por Status -->
                <section class="glass p-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-filter text-indigo-600"></i>
                        Filtros Rápidos por Status
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                        <a href="dashboard.php" class="group relative overflow-hidden rounded-xl p-4 border-2 transition-all hover:shadow-lg <?= empty($_GET['situacao_id']) ? 'border-indigo-600 bg-indigo-50' : 'border-slate-200 bg-white hover:border-indigo-300' ?>">
                            <div class="flex items-center gap-3">
                                <span class="icon-chip <?= empty($_GET['situacao_id']) ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600' ?>">
                                    <i class="fas fa-calendar-alt"></i>
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">Todos</p>
                                    <p class="text-xs text-slate-500"><?= $total_pedidos ?> pedidos</p>
                                </div>
                            </div>
                        </a>

                        <?php
                        foreach ($situacoes as $situacao):
                            $isActive = isset($_GET['situacao_id']) && $_GET['situacao_id'] == $situacao['id'];
                            $count = 0;
                            $percentual = 0;
                            foreach ($stats_situacao as $stat) {
                                if ($stat['id'] == $situacao['id']) {
                                    $count = $stat['total'];
                                    $percentual = $stat['percentual'];
                                    break;
                                }
                            }
                        ?>
                        <a href="dashboard.php?situacao_id=<?= $situacao['id'] ?>" class="group relative overflow-hidden rounded-xl p-4 border-2 transition-all hover:shadow-lg <?= $isActive ? 'bg-opacity-10' : 'border-slate-200 bg-white hover:border-slate-300' ?>" style="<?= $isActive ? 'border-color: ' . $situacao['cor'] . '; background-color: ' . $situacao['cor'] . '15;' : '' ?>">
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
                                        } elseif (stripos($situacao['nome'], 'cotando') !== false) {
                                            echo 'calculator';
                                        } else {
                                            echo 'tag';
                                        }
                                    ?>"></i>
                                </span>
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-slate-900"><?= htmlspecialchars($situacao['nome']) ?></p>
                                    <p class="text-xs text-slate-500"><?= $count ?> pedidos • <?= $percentual ?>%</p>
                                </div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </section>

                <!-- Filtros e Busca -->
                <section class="glass p-4">
                    <form method="GET" class="flex flex-wrap gap-3 items-end">
                        <div class="flex-1 min-w-[200px] max-w-[300px]">
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                                <i class="fas fa-search text-indigo-500 mr-1"></i>
                                Buscar Paciente
                            </label>
                            <input type="text" name="busca" value="<?= htmlspecialchars($_GET['busca'] ?? '') ?>" placeholder="Nome do paciente..." class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                        </div>

                        <div class="flex-1 min-w-[180px] max-w-[250px]">
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                                <i class="fas fa-tag text-indigo-500 mr-1"></i>
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
                            <button type="submit" class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition font-semibold">
                                <i class="fas fa-filter mr-1"></i>
                                Filtrar
                            </button>
                            <a href="dashboard.php" class="px-4 py-2 text-sm bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition font-semibold">
                                <i class="fas fa-times mr-1"></i>
                                Limpar
                            </a>
                        </div>
                    </form>
                </section>

                <section class="glass p-2 inline-flex view-toggle">
                    <button id="timelineBtn" class="btn-muted btn-primary--icon active" onclick="switchView('timeline')">
                        <i class="fas fa-stream"></i>
                        Timeline
                    </button>
                    <button id="listBtn" class="btn-muted btn-primary--icon" onclick="switchView('list')">
                        <i class="fas fa-list"></i>
                        Lista
                    </button>
                </section>

                <section id="calendarView" class="glass p-6 hidden">
                    <div id="calendar"></div>
                </section>

                <section id="listView" class="glass p-0 overflow-hidden hidden">
                    <table class="min-w-full">
                        <thead class="datatable__head">
                            <tr>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Recebido</th>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Paciente</th>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Procedimento</th>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Fornecedor</th>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Situação</th>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($pedidos as $ped): ?>
                            <tr class="table-row">
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-900"><?= $ped['data_recebimento'] ? date('d/m/Y', strtotime($ped['data_recebimento'])) : '-' ?></p>
                                    <p class="text-sm text-slate-500"><?= date('H:i', strtotime($ped['created_at'])) ?></p>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-900"><?= htmlspecialchars($ped['nome_paciente']) ?></p>
                                    <p class="text-xs text-slate-500"><?= htmlspecialchars($ped['convenio']) ?></p>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600"><?= htmlspecialchars($ped['procedimento'] ?: '-') ?></td>
                                <td class="px-5 py-4 text-sm text-slate-600"><?= htmlspecialchars($ped['fornecedor'] ?: '-') ?></td>
                                <td class="px-5 py-4">
                                    <span class="chip chip--accent" style="background: <?= $ped['situacao_cor'] ?>22; color: <?= $ped['situacao_cor'] ?>;">
                                        <?= htmlspecialchars($ped['situacao_nome']) ?>
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <button type="button" onclick="verHistorico(<?= $ped['id'] ?>)" class="btn-muted btn-primary--icon" title="Ver Histórico">
                                        <i class="fas fa-history text-xs text-orange-500"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>

                <section id="timelineView" class="space-y-4">
                    <?php
                    $grouped = [];
                    foreach ($pedidos as $ped) {
                        $data_grupo = $ped['data_recebimento'] ?: date('Y-m-d', strtotime($ped['created_at']));
                        $grouped[$data_grupo][] = $ped;
                    }
                    krsort($grouped); // Ordem decrescente (mais recente primeiro)
                    foreach ($grouped as $data => $items):
                    ?>
                    <article class="glass p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="icon-chip bg-indigo-100 text-indigo-600">
                                <i class="fas fa-calendar-day"></i>
                            </span>
                            <div class="flex-1">
                                <h3 class="text-base font-semibold text-slate-900"><?= date('d/m/Y', strtotime($data)) ?></h3>
                                <p class="text-xs text-slate-500"><?= strftime('%A', strtotime($data)) ?></p>
                            </div>
                            <span class="chip chip--accent"><?= count($items) ?> pedidos</span>
                        </div>
                        <div class="space-y-3">
                            <?php foreach ($items as $ped): ?>
                            <button type="button" onclick="verHistorico(<?= $ped['id'] ?>)" class="w-full text-left glass p-4 hover:shadow-lg transition-all">
                                <div class="flex flex-wrap items-center gap-4">
                                    <span class="chip chip--accent bg-indigo-100 text-indigo-700">
                                        <i class="fas fa-file-medical"></i>
                                    </span>
                                    <div class="flex-1">
                                        <p class="font-semibold text-slate-900"><?= htmlspecialchars($ped['nome_paciente']) ?></p>
                                        <p class="text-xs text-slate-500">
                                            <?= htmlspecialchars($ped['procedimento'] ?: 'Sem procedimento') ?>
                                            <?php if ($ped['fornecedor']): ?>
                                                • <?= htmlspecialchars($ped['fornecedor']) ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <span class="chip chip--accent" style="background: <?= $ped['situacao_cor'] ?>22; color: <?= $ped['situacao_cor'] ?>;">
                                        <?= htmlspecialchars($ped['situacao_nome']) ?>
                                    </span>
                                </div>
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </section>
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

    <script>
        const proximos = <?= json_encode(array_values($pedidos_proximos)) ?>;

        const listView = document.getElementById('listView');
        const timelineView = document.getElementById('timelineView');

        const listBtn = document.getElementById('listBtn');
        const timelineBtn = document.getElementById('timelineBtn');

        const notificationBtn = document.getElementById('notificationBtn');
        const notificationContainer = document.getElementById('notificationContainer');
        const detailsModal = document.getElementById('detailsModal');
        const modalContent = document.getElementById('modalContent');

        function switchView(view) {
            listBtn.classList.remove('active');
            timelineBtn.classList.remove('active');

            listView.classList.add('hidden');
            timelineView.classList.add('hidden');

            if (view === 'list') {
                listBtn.classList.add('active');
                listView.classList.remove('hidden');
            } else {
                timelineBtn.classList.add('active');
                timelineView.classList.remove('hidden');
            }
        }

        function viewUpcoming() {
            if (!proximos.length) return;
            const first = proximos[0];
            verHistorico(first.id);
        }

        notificationBtn?.addEventListener('click', () => {
            if (!proximos.length) {
                renderNotification('Sem alertas', 'Você não possui pedidos recentes.', 'fa-circle-check', 'emerald');
            } else {
                proximos.forEach(ped => {
                    renderNotification(
                        `${ped.nome_paciente}`,
                        `${ped.procedimento || 'Sem procedimento'} • ${ped.convenio}`,
                        'fa-file-medical',
                        'orange'
                    );
                });
            }
        });

        function renderNotification(title, message, icon, color) {
            const wrapper = document.createElement('div');
            wrapper.className = 'glass p-4 rounded-2xl shadow-lg notification-toast';
            wrapper.innerHTML = `
                <div class="flex items-start gap-3">
                    <span class="icon-chip bg-${color}-100 text-${color}-600">
                        <i class="fas ${icon}"></i>
                    </span>
                    <div>
                        <h4 class="font-semibold text-slate-900">${title}</h4>
                        <p class="text-sm text-slate-500">${message}</p>
                    </div>
                    <button class="btn-muted btn-primary--icon" onclick="this.closest('.notification-toast').remove()">
                        <i class="fas fa-xmark text-xs"></i>
                    </button>
                </div>
            `;
            notificationContainer.appendChild(wrapper);
            setTimeout(() => wrapper.remove(), 6000);
        }

        // Modal de Histórico
        async function verHistorico(pedidoId) {
            document.getElementById('modalHistorico').classList.remove('hidden');

            try {
                const response = await fetch(`../admin/pedidos-novos.php?action=get_historico&id=${pedidoId}`);
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

        // Fechar modal ao clicar fora
        document.getElementById('modalHistorico')?.addEventListener('click', (e) => {
            if (e.target.id === 'modalHistorico') fecharModalHistorico();
        });

        // Fechar modal com ESC
        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                fecharModalHistorico();
            }
        });

        window.verHistorico = verHistorico;
    </script>
</body>
</html>
