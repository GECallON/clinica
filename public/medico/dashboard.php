<?php
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/models/Usuario.php';
require_once __DIR__ . '/../../src/models/Agendamento.php';

if (!isLoggedIn() || !isMedico()) {
    redirect('../index.php');
}

$agendamentoModel = new Agendamento();
$agendamentos = $agendamentoModel->getAll($_SESSION['user_id']);
$events = $agendamentoModel->getCalendarEvents($_SESSION['user_id']);

$total_agendamentos = count($agendamentos);
$hoje = date('Y-m-d');
$agora = date('H:i');

$agendamentos_hoje = array_filter($agendamentos, fn($a) => $a['data_cirurgia'] === $hoje);
$proximos_7_dias = array_filter($agendamentos, function ($a) use ($hoje) {
    return $a['data_cirurgia'] >= $hoje && $a['data_cirurgia'] <= date('Y-m-d', strtotime('+7 days'));
});

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

                <section class="glass p-2 inline-flex view-toggle">
                    <button id="calendarBtn" class="btn-muted btn-primary--icon active" onclick="switchView('calendar')">
                        <i class="fas fa-calendar"></i>
                        Agenda
                    </button>
                    <button id="listBtn" class="btn-muted btn-primary--icon" onclick="switchView('list')">
                        <i class="fas fa-list"></i>
                        Lista
                    </button>
                    <button id="timelineBtn" class="btn-muted btn-primary--icon" onclick="switchView('timeline')">
                        <i class="fas fa-stream"></i>
                        Timeline
                    </button>
                </section>

                <section id="calendarView" class="glass p-6">
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

                <section id="timelineView" class="hidden space-y-4">
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
        const events = <?= json_encode($events) ?>;
        const proximos = <?= json_encode(array_values($agendamentos_proximos)) ?>;

        const calendarEl = document.getElementById('calendar');
        const calendarView = document.getElementById('calendarView');
        const listView = document.getElementById('listView');
        const timelineView = document.getElementById('timelineView');

        const calendarBtn = document.getElementById('calendarBtn');
        const listBtn = document.getElementById('listBtn');
        const timelineBtn = document.getElementById('timelineBtn');

        const notificationBtn = document.getElementById('notificationBtn');
        const notificationContainer = document.getElementById('notificationContainer');
        const detailsModal = document.getElementById('detailsModal');
        const modalContent = document.getElementById('modalContent');

        let calendar;

        document.addEventListener('DOMContentLoaded', () => {
            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'pt-br',
                height: 'auto',
                headerToolbar: {
                    left: 'title',
                    center: '',
                    right: 'prev,next today'
                },
                events,
                eventClick: info => {
                    viewDetails(info.event.id);
                }
            });
            calendar.render();
        });

        function switchView(view) {
            calendarBtn.classList.remove('active');
            listBtn.classList.remove('active');
            timelineBtn.classList.remove('active');

            calendarView.classList.add('hidden');
            listView.classList.add('hidden');
            timelineView.classList.add('hidden');

            if (view === 'calendar') {
                calendarBtn.classList.add('active');
                calendarView.classList.remove('hidden');
                calendar.updateSize();
            } else if (view === 'list') {
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
