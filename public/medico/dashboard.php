<?php
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/models/Usuario.php';
require_once __DIR__ . '/../../src/models/Agendamento.php';

if (!isLoggedIn() || !isMedico()) {
    redirect('../index.php');
}

$agendamentoModel = new Agendamento();

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

$agendamentos = $agendamentoModel->getAllWithFilters($filtros);

// Gerar eventos do calendário a partir dos agendamentos filtrados OU todos do médico
$eventos_calendario = [];
$agendamentos_calendario = empty($filtros['busca']) && empty($filtros['situacao_id'])
    ? $agendamentoModel->getAll($_SESSION['user_id'])
    : $agendamentos;

foreach ($agendamentos_calendario as $ag) {
    $eventos_calendario[] = [
        'id' => $ag['id'],
        'title' => $ag['nome_paciente'] . ' - ' . $ag['procedimento_nome'],
        'start' => $ag['data_cirurgia'] . 'T' . $ag['hora_cirurgia'],
        'backgroundColor' => $ag['situacao_cor'],
        'borderColor' => $ag['situacao_cor'],
        'extendedProps' => [
            'hospital' => $ag['hospital'],
            'medico' => $ag['medico_nome'],
            'situacao' => $ag['situacao_nome']
        ]
    ];
}

// Carregar situações para o filtro
if (!class_exists('Situacao')) {
    require_once __DIR__ . '/../../src/models/Situacao.php';
}
$situacaoModel = new Situacao();
$situacoes = $situacaoModel->getAtivos();

// Estatísticas
$total_agendamentos = count($agendamentoModel->getAllWithFilters(['medico_id' => $_SESSION['user_id']]));
$hoje = date('Y-m-d');
$agora = date('H:i');

$agendamentos_hoje = array_filter($agendamentos, fn($a) => $a['data_cirurgia'] === $hoje);
$proximos_7_dias = array_filter($agendamentos, function ($a) use ($hoje) {
    return $a['data_cirurgia'] >= $hoje && $a['data_cirurgia'] <= date('Y-m-d', strtotime('+7 days'));
});

// Estatísticas por situação para o médico
$stats_situacao = $agendamentoModel->getStatsBySituacaoMedico($_SESSION['user_id']);

$agendamentos_proximos = array_filter($agendamentos_hoje, function ($a) use ($agora) {
    $hora_agendamento = date('H:i', strtotime($a['hora_cirurgia']));
    $diff_minutos = (strtotime($hora_agendamento) - strtotime($agora)) / 60;
    return $diff_minutos > 0 && $diff_minutos <= 120;
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
                    <a href="agendamentos.php" class="btn-primary" title="Ver todos os agendamentos">
                        <i class="fas fa-calendar-check"></i>
                        Agendamentos
                    </a>
                    <button id="notificationBtn" class="btn-muted btn-primary--icon relative">
                        <i class="fas fa-bell text-sm"></i>
                        <?php if (count($agendamentos_proximos) > 0): ?>
                            <span class="absolute -top-1 -right-1 h-5 w-5 rounded-full bg-rose-500 text-white text-xs font-semibold flex items-center justify-center">
                                <?= count($agendamentos_proximos) ?>
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

                <?php if (count($agendamentos_proximos) > 0): ?>
                <section class="glass p-6 border border-orange-200">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <span class="icon-chip text-white" style="background: linear-gradient(135deg, #f97316 0%, #ef4444 100%);">
                                <i class="fas fa-exclamation-triangle"></i>
                            </span>
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">Procedimentos nas próximas 2 horas</h3>
                                <p class="text-sm text-slate-500">
                                    Você tem <strong><?= count($agendamentos_proximos) ?></strong> procedimento(s) começando em breve.
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
                                <i class="fas fa-calendar-alt"></i>
                            </span>
                            <p class="metric-card__value"><?= $total_agendamentos ?></p>
                        </div>
                        <p class="metric-card__label">Agendamentos totais</p>
                    </div>
                    <div class="metric-card">
                        <div class="metric-card__header">
                            <span class="metric-card__icon" style="background: rgba(16, 185, 129, 0.18); color: #10b981;">
                                <i class="fas fa-calendar-day"></i>
                            </span>
                            <p class="metric-card__value"><?= count($agendamentos_hoje) ?></p>
                        </div>
                        <p class="metric-card__label">Para hoje</p>
                    </div>
                    <div class="metric-card">
                        <div class="metric-card__header">
                            <span class="metric-card__icon" style="background: rgba(168, 85, 247, 0.18); color: #8b5cf6;">
                                <i class="fas fa-calendar-week"></i>
                            </span>
                            <p class="metric-card__value"><?= count($proximos_7_dias) ?></p>
                        </div>
                        <p class="metric-card__label">Próximos 7 dias</p>
                    </div>
                    <div class="metric-card">
                        <div class="metric-card__header">
                            <span class="metric-card__icon" style="background: rgba(249, 115, 22, 0.18); color: #f97316;">
                                <i class="fas fa-bolt"></i>
                            </span>
                            <p class="metric-card__value"><?= count($agendamentos_proximos) ?></p>
                        </div>
                        <p class="metric-card__label">Próximas 2 horas</p>
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
                                    <p class="text-xs text-slate-500"><?= $total_agendamentos ?> agendamentos</p>
                                </div>
                            </div>
                        </a>

                        <?php
                        foreach ($situacoes as $situacao):
                            $isActive = isset($_GET['situacao_id']) && $_GET['situacao_id'] == $situacao['id'];
                            $count = 0;
                            foreach ($stats_situacao as $stat) {
                                if ($stat['id'] == $situacao['id']) {
                                    $count = $stat['total'];
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

                <!-- Cards de Estatísticas por Situação -->
                <?php if (!empty($stats_situacao)): ?>
                <section>
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Distribuição por Status</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <?php foreach ($stats_situacao as $stat): ?>
                        <div class="glass p-5 border-l-4" style="border-color: <?= $stat['cor'] ?>;">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-semibold text-slate-600"><?= htmlspecialchars($stat['nome']) ?></span>
                                <span class="text-2xl font-bold" style="color: <?= $stat['cor'] ?>;"><?= $stat['total'] ?></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all" style="width: <?= $stat['percentual'] ?>%; background-color: <?= $stat['cor'] ?>;"></div>
                                </div>
                                <span class="text-xs font-semibold" style="color: <?= $stat['cor'] ?>;"><?= $stat['percentual'] ?>%</span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>
                <?php endif; ?>

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
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Data / hora</th>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Paciente</th>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Procedimento</th>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Hospital</th>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Situação</th>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($agendamentos as $ag): ?>
                            <tr class="table-row">
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-900"><?= date('d/m/Y', strtotime($ag['data_cirurgia'])) ?></p>
                                    <p class="text-sm text-slate-500"><?= date('H:i', strtotime($ag['hora_cirurgia'])) ?></p>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-900"><?= htmlspecialchars($ag['nome_paciente']) ?></p>
                                    <p class="text-xs text-slate-500"><?= htmlspecialchars($ag['convenio']) ?></p>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600"><?= htmlspecialchars($ag['procedimento_nome']) ?></td>
                                <td class="px-5 py-4 text-sm text-slate-600"><?= htmlspecialchars($ag['hospital']) ?></td>
                                <td class="px-5 py-4">
                                    <span class="chip chip--accent" style="background: <?= $ag['situacao_cor'] ?>22; color: <?= $ag['situacao_cor'] ?>;">
                                        <?= htmlspecialchars($ag['situacao_nome']) ?>
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <button type="button" onclick="viewDetails(<?= $ag['id'] ?>)" class="btn-muted btn-primary--icon">
                                        <i class="fas fa-eye text-xs"></i>
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
                    foreach ($agendamentos as $ag) {
                        $grouped[$ag['data_cirurgia']][] = $ag;
                    }
                    ksort($grouped);
                    foreach ($grouped as $data => $items):
                    ?>
                    <article class="glass p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="icon-chip bg-indigo-100 text-indigo-600">
                                <i class="fas fa-calendar-day"></i>
                            </span>
                            <div class="flex-1">
                                <h3 class="text-base font-semibold text-slate-900"><?= date('d/m/Y', strtotime($data)) ?></h3>
                                <p class="text-xs text-slate-500"><?= date('l', strtotime($data)) ?></p>
                            </div>
                            <span class="chip chip--accent"><?= count($items) ?> agend.</span>
                        </div>
                        <div class="space-y-3">
                            <?php foreach ($items as $ag): ?>
                            <button type="button" onclick="viewDetails(<?= $ag['id'] ?>)" class="w-full text-left glass p-4 hover:shadow-lg transition-all">
                                <div class="flex flex-wrap items-center gap-4">
                                    <span class="chip chip--accent">
                                        <?= date('H:i', strtotime($ag['hora_cirurgia'])) ?>
                                    </span>
                                    <div class="flex-1">
                                        <p class="font-semibold text-slate-900"><?= htmlspecialchars($ag['nome_paciente']) ?></p>
                                        <p class="text-xs text-slate-500"><?= htmlspecialchars($ag['procedimento_nome']) ?> • <?= htmlspecialchars($ag['hospital']) ?></p>
                                    </div>
                                    <span class="chip chip--accent" style="background: <?= $ag['situacao_cor'] ?>22; color: <?= $ag['situacao_cor'] ?>;">
                                        <?= htmlspecialchars($ag['situacao_nome']) ?>
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

    <div id="detailsModal" class="hidden fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="glass rounded-3xl max-w-3xl w-full max-h-[90vh] overflow-y-auto shadow-2xl">
            <div class="p-6" id="modalContent"></div>
        </div>
    </div>

    <script>
        const proximos = <?= json_encode(array_values($agendamentos_proximos)) ?>;

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
            viewDetails(first.id);
        }

        notificationBtn?.addEventListener('click', () => {
            if (!proximos.length) {
                renderNotification('Sem alertas', 'Você não possui procedimentos iminentes.', 'fa-circle-check', 'emerald');
            } else {
                proximos.forEach(ag => {
                    const horario = `${ag.hora_cirurgia.substring(0,5)}`;
                    renderNotification(
                        `${ag.nome_paciente}`,
                        `${ag.procedimento_nome} • ${horario}`,
                        'fa-clock',
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

        function viewDetails(id) {
            if (!detailsModal || !modalContent) return;
            modalContent.innerHTML = '<div class="p-6 text-center text-sm text-slate-500">Carregando...</div>';
            detailsModal.classList.remove('hidden');

            fetch('view-agendamento.php?id=' + id)
                .then(response => {
                    if (!response.ok) throw new Error('Erro ao carregar detalhes');
                    return response.text();
                })
                .then(html => {
                    modalContent.innerHTML = html;
                })
                .catch(() => {
                    modalContent.innerHTML = '<div class="p-6 text-center text-sm text-red-500">Não foi possível carregar os detalhes.</div>';
                });
        }

        function closeModal() {
            if (!detailsModal || !modalContent) return;
            detailsModal.classList.add('hidden');
            modalContent.innerHTML = '';
        }

        detailsModal?.addEventListener('click', event => {
            if (event.target === detailsModal) {
                closeModal();
            }
        });

        window.addEventListener('keydown', event => {
            if (event.key === 'Escape') {
                closeModal();
            }
        });

        window.closeModal = closeModal;
        window.viewDetails = viewDetails;
    </script>
</body>
</html>
