<?php
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/models/Usuario.php';
require_once __DIR__ . '/../../src/models/Agendamento.php';
require_once __DIR__ . '/../../src/models/Procedimento.php';
require_once __DIR__ . '/../../src/models/Situacao.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$usuarioModel = new Usuario();
$agendamentoModel = new Agendamento();
$procedimentoModel = new Procedimento();
$situacaoModel = new Situacao();

$total_usuarios = count($usuarioModel->getAll());
$total_medicos = count($usuarioModel->getMedicos());
$total_agendamentos = count($agendamentoModel->getAll());
$total_procedimentos = count($procedimentoModel->getAll());

$agendamentos_recentes = array_slice($agendamentoModel->getAll(), 0, 10);

$hoje = date('Y-m-d');
$agendamentos_hoje = array_filter($agendamentoModel->getAll(), fn($a) => $a['data_cirurgia'] === $hoje);
$proximos_7_dias = array_filter(
    $agendamentoModel->getAll(),
    fn($a) => $a['data_cirurgia'] >= $hoje && $a['data_cirurgia'] <= date('Y-m-d', strtotime('+7 days'))
);

$flash = getFlashMessage();
$version = time();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Admin - MedAgenda Pro</title>
    <script src="https://cdn.tailwindcss.com?v=<?= $version ?>"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
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
                <div class="rounded-2xl border <?= $flash['type'] === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-red-200 bg-red-50 text-red-700' ?> px-5 py-4 text-sm shadow-sm">
                    <i class="fas fa-<?= $flash['type'] === 'success' ? 'circle-check' : 'triangle-exclamation' ?> mr-3"></i>
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
                <?php endif; ?>

                <section class="space-y-2">
                    <h1 class="page-title flex items-center gap-2">
                        <span class="icon-chip bg-indigo-100 text-indigo-600">
                            <i class="fas fa-stethoscope"></i>
                        </span>
                        Bem-vindo, <?= htmlspecialchars(explode(' ', $_SESSION['nome'])[0]) ?>!
                    </h1>
                    <p class="page-subtitle">
                        <?= date('d/m/Y') ?> • <?= date('H:i') ?> • Painel panorâmico do centro cirúrgico.
                    </p>
                </section>

                <section class="metrics-group">
                    <div class="metric-card">
                        <div class="metric-card__header">
                            <span class="metric-card__icon" style="background: rgba(79, 70, 229, 0.15); color: #4f46e5;">
                                <i class="fas fa-users"></i>
                            </span>
                            <p class="metric-card__value"><?= $total_usuarios ?></p>
                        </div>
                        <p class="metric-card__label">Usuários cadastrados</p>
                        <a href="usuarios-list.php" class="mt-3 inline-flex items-center text-xs font-semibold text-indigo-500 hover:text-indigo-600 transition">
                            Ver equipe completa
                            <i class="fas fa-arrow-right-long ml-2"></i>
                        </a>
                    </div>

                    <div class="metric-card">
                        <div class="metric-card__header">
                            <span class="metric-card__icon" style="background: rgba(14, 165, 233, 0.18); color: #0ea5e9;">
                                <i class="fas fa-user-md"></i>
                            </span>
                            <p class="metric-card__value"><?= $total_medicos ?></p>
                        </div>
                        <p class="metric-card__label">Médicos ativos</p>
                        <span class="chip chip--accent mt-3 inline-block">
                            <i class="fas fa-chart-line mr-1"></i>
                            <?= count($agendamentos_hoje) ?> procedimentos hoje
                        </span>
                    </div>

                    <div class="metric-card">
                        <div class="metric-card__header">
                            <span class="metric-card__icon" style="background: rgba(236, 72, 153, 0.15); color: #db2777;">
                                <i class="fas fa-calendar-check"></i>
                            </span>
                            <p class="metric-card__value"><?= $total_agendamentos ?></p>
                        </div>
                        <p class="metric-card__label">Agendamentos registrados</p>
                        <a href="agendamentos-list.php" class="mt-3 inline-flex items-center text-xs font-semibold text-rose-500 hover:text-rose-600 transition">
                            Organizar agenda
                            <i class="fas fa-arrow-right-long ml-2"></i>
                        </a>
                    </div>

                    <div class="metric-card">
                        <div class="metric-card__header">
                            <span class="metric-card__icon" style="background: rgba(16, 185, 129, 0.18); color: #10b981;">
                                <i class="fas fa-syringe"></i>
                            </span>
                            <p class="metric-card__value"><?= $total_procedimentos ?></p>
                        </div>
                        <p class="metric-card__label">Procedimentos configurados</p>
                        <a href="procedimentos-list.php" class="mt-3 inline-flex items-center text-xs font-semibold text-emerald-500 hover:text-emerald-600 transition">
                            Editar protocolos
                            <i class="fas fa-arrow-right-long ml-2"></i>
                        </a>
                    </div>
                </section>

                <section class="metrics-group">
                    <div class="metric-card">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="metric-card__label uppercase tracking-wide text-xs text-slate-500">Hoje</p>
                                <p class="metric-card__value"><?= count($agendamentos_hoje) ?></p>
                            </div>
                            <span class="chip chip--accent">
                                <i class="fas fa-clock mr-2"></i><?= date('d/m/Y') ?>
                            </span>
                        </div>
                        <p class="mt-4 text-sm text-slate-500">Cirurgias previstas para execução nas próximas horas.</p>
                    </div>

                    <div class="metric-card">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="metric-card__label uppercase tracking-wide text-xs text-slate-500">Próximos 7 dias</p>
                                <p class="metric-card__value"><?= count($proximos_7_dias) ?></p>
                            </div>
                            <span class="chip chip--accent">
                                <i class="fas fa-calendar-week mr-2"></i>Agenda dinâmica
                            </span>
                        </div>
                        <p class="mt-4 text-sm text-slate-500">Verifique materiais, convênios e disponibilidade de equipes.</p>
                    </div>
                </section>

                <section class="datatable-card">
                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5 bg-white/70">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">Agendamentos recentes</h2>
                            <p class="text-sm text-slate-500">Últimas cirurgias cadastradas no sistema</p>
                        </div>
                        <a href="agendamentos-list.php" class="btn-muted flex items-center gap-2">
                            <i class="fas fa-list-check text-slate-500"></i>
                            Gerenciar agenda
                        </a>
                    </div>

                    <?php if (!empty($agendamentos_recentes)): ?>
                    <table class="min-w-full">
                        <thead class="datatable__head">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Data/Hora</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Paciente</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Médico</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Procedimento</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Situação</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($agendamentos_recentes as $ag): ?>
                            <tr class="table-row">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="metric-card__icon !mb-0" style="background: rgba(37, 99, 235, 0.12); color: #2563eb;">
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
                                <td class="px-6 py-4">
                                    <span class="chip chip--accent" style="background: <?= $ag['situacao_cor'] ?>22; color: <?= $ag['situacao_cor'] ?>;">
                                        <?= htmlspecialchars($ag['situacao_nome']) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox text-3xl text-indigo-300"></i>
                        <p class="mt-3 font-semibold text-slate-700">Ainda sem registros recentes</p>
                        <p class="text-sm text-slate-500 mt-1">
                            Cadastre um agendamento para visualizar a timeline de cirurgias.
                        </p>
                    </div>
                    <?php endif; ?>
                </section>
            </main>
        </div>
    </div>
</body>
</html>
