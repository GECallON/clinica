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

// Carregar model de Pedidos
if (!class_exists('PedidoNovo')) {
    require_once __DIR__ . '/../../src/models/PedidoNovo.php';
}
$pedidoModel = new PedidoNovo();

$total_usuarios = count($usuarioModel->getAll());
$total_medicos = count($usuarioModel->getMedicos());
$total_agendamentos = count($agendamentoModel->getAll());
$total_pedidos = count($pedidoModel->getAll());
$total_procedimentos = count($procedimentoModel->getAll());

$agendamentos_recentes = array_slice($agendamentoModel->getAll(), 0, 10);

$hoje = date('Y-m-d');
$agendamentos_hoje = array_filter($agendamentoModel->getAll(), fn($a) => $a['data_cirurgia'] === $hoje);
$proximos_7_dias = array_filter(
    $agendamentoModel->getAll(),
    fn($a) => $a['data_cirurgia'] >= $hoje && $a['data_cirurgia'] <= date('Y-m-d', strtotime('+7 days'))
);

// Estatísticas de Agendamentos
$stats_situacao_agendamento = $agendamentoModel->getStatsDetalhadasPorStatus();
$stats_medico = $agendamentoModel->getStatsByMedico(5);
$stats_hospital = $agendamentoModel->getStatsByHospital(5);
$stats_monthly = $agendamentoModel->getMonthlyStats(6);
$proximos_30_dias = $agendamentoModel->getUpcomingStats(30);
$este_mes_agendamento = $agendamentoModel->getStatsThisMonth();
$mes_passado_agendamento = $agendamentoModel->getStatsLastMonth();

// Estatísticas de Pedidos
$stats_situacao_pedidos = $pedidoModel->getStatsBySituacao();
$pedidos_hoje = $pedidoModel->getStatsHoje();
$pedidos_esta_semana = $pedidoModel->getStatsEstaSemana();
$pedidos_este_mes = $pedidoModel->getStatsEsteMes();

// Calcular tendência Agendamentos
$tendencia_agendamento = 0;
$tendencia_texto_agendamento = 'estável';
if ($mes_passado_agendamento > 0) {
    $tendencia_agendamento = (($este_mes_agendamento - $mes_passado_agendamento) / $mes_passado_agendamento) * 100;
    if ($tendencia_agendamento > 0) {
        $tendencia_texto_agendamento = 'crescimento';
    } elseif ($tendencia_agendamento < 0) {
        $tendencia_texto_agendamento = 'queda';
    }
}

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
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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

                <!-- SEÇÃO: PEDIDOS -->
                <section>
                    <h2 class="text-xl font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-file-medical text-blue-600"></i>
                        Pedidos
                    </h2>
                    <div class="metrics-group">
                        <div class="metric-card">
                            <div class="metric-card__header">
                                <span class="metric-card__icon" style="background: rgba(59, 130, 246, 0.15); color: #3b82f6;">
                                    <i class="fas fa-file-medical"></i>
                                </span>
                                <p class="metric-card__value"><?= $total_pedidos ?></p>
                            </div>
                            <p class="metric-card__label">Total de Pedidos</p>
                            <a href="pedidos-novos-list.php" class="mt-3 inline-flex items-center text-xs font-semibold text-blue-500 hover:text-blue-600 transition">
                                Ver todos
                                <i class="fas fa-arrow-right-long ml-2"></i>
                            </a>
                        </div>

                        <div class="metric-card">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="metric-card__label uppercase tracking-wide text-xs text-slate-500">Hoje</p>
                                    <p class="metric-card__value"><?= $pedidos_hoje ?></p>
                                </div>
                                <span class="chip chip--accent">
                                    <i class="fas fa-clock mr-2"></i><?= date('d/m/Y') ?>
                                </span>
                            </div>
                            <p class="mt-4 text-sm text-slate-500">Novos pedidos criados hoje.</p>
                        </div>

                        <div class="metric-card">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="metric-card__label uppercase tracking-wide text-xs text-slate-500">Últimos 7 dias</p>
                                    <p class="metric-card__value"><?= $pedidos_esta_semana ?></p>
                                </div>
                                <span class="chip chip--accent">
                                    <i class="fas fa-calendar-week mr-2"></i>Semana
                                </span>
                            </div>
                            <p class="mt-4 text-sm text-slate-500">Pedidos desta semana.</p>
                        </div>

                        <div class="metric-card">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="metric-card__label uppercase tracking-wide text-xs text-slate-500">Este Mês</p>
                                    <p class="metric-card__value"><?= $pedidos_este_mes ?></p>
                                </div>
                                <span class="chip chip--accent">
                                    <i class="fas fa-calendar-alt mr-2"></i>Mensal
                                </span>
                            </div>
                            <p class="mt-4 text-sm text-slate-500">Pedidos do mês atual.</p>
                        </div>
                    </div>

                    <!-- Distribuição de Pedidos por Status -->
                    <?php if (count($stats_situacao_pedidos) > 0): ?>
                    <div class="glass p-6 mt-4">
                        <h3 class="text-sm font-semibold text-slate-700 mb-3 uppercase tracking-wide">Distribuição por Status</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <?php foreach ($stats_situacao_pedidos as $s): ?>
                            <div class="p-3 rounded-lg text-center" style="background: <?= $s['cor'] ?>15; border: 2px solid <?= $s['cor'] ?>40;">
                                <p class="text-2xl font-bold" style="color: <?= $s['cor'] ?>;"><?= $s['total'] ?></p>
                                <p class="text-xs text-slate-600 mt-1"><?= htmlspecialchars($s['nome']) ?></p>
                                <p class="text-xs font-semibold mt-1" style="color: <?= $s['cor'] ?>;"><?= $s['percentual'] ?>%</p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </section>

                <!-- SEÇÃO: AGENDAMENTOS -->
                <section>
                    <h2 class="text-xl font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-calendar-alt text-indigo-600"></i>
                        Agendamentos (Cirurgias)
                    </h2>
                    <div class="metrics-group">
                        <div class="metric-card">
                            <div class="metric-card__header">
                                <span class="metric-card__icon" style="background: rgba(236, 72, 153, 0.15); color: #db2777;">
                                    <i class="fas fa-calendar-check"></i>
                                </span>
                                <p class="metric-card__value"><?= $total_agendamentos ?></p>
                            </div>
                            <p class="metric-card__label">Total de Agendamentos</p>
                            <a href="agendamentos-list.php" class="mt-3 inline-flex items-center text-xs font-semibold text-rose-500 hover:text-rose-600 transition">
                                Ver agenda
                                <i class="fas fa-arrow-right-long ml-2"></i>
                            </a>
                        </div>

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
                            <p class="mt-4 text-sm text-slate-500">Cirurgias previstas para execução.</p>
                        </div>

                        <div class="metric-card">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="metric-card__label uppercase tracking-wide text-xs text-slate-500">Próximos 7 dias</p>
                                    <p class="metric-card__value"><?= count($proximos_7_dias) ?></p>
                                </div>
                                <span class="chip chip--accent">
                                    <i class="fas fa-calendar-week mr-2"></i>Agenda
                                </span>
                            </div>
                            <p class="mt-4 text-sm text-slate-500">Cirurgias nos próximos 7 dias.</p>
                        </div>

                        <div class="metric-card">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <p class="metric-card__label uppercase tracking-wide text-xs text-slate-500">Este Mês</p>
                                    <p class="metric-card__value"><?= $este_mes_agendamento ?></p>
                                </div>
                                <span class="chip <?= $tendencia_agendamento > 0 ? 'bg-green-100 text-green-700' : ($tendencia_agendamento < 0 ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700') ?>">
                                    <i class="fas fa-<?= $tendencia_agendamento > 0 ? 'arrow-up' : ($tendencia_agendamento < 0 ? 'arrow-down' : 'minus') ?> mr-1"></i>
                                    <?= $tendencia_agendamento > 0 ? '+' : '' ?><?= number_format(abs($tendencia_agendamento), 1) ?>%
                                </span>
                            </div>
                            <p class="text-sm text-slate-500">
                                <?= $tendencia_texto_agendamento === 'crescimento' ? '📈' : ($tendencia_texto_agendamento === 'queda' ? '📉' : '➡️') ?>
                                <span class="font-medium"><?= abs($este_mes_agendamento - $mes_passado_agendamento) ?></span> vs mês anterior
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Estatísticas Detalhadas por Status (Agendamentos) -->
                <section class="glass p-6">
                    <h2 class="text-lg font-semibold text-slate-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-chart-pie text-indigo-500"></i>
                        Agendamentos - Detalhamento por Status
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($stats_situacao_agendamento as $situacao): ?>
                        <div class="p-5 rounded-xl border-2" style="border-color: <?= $situacao['cor'] ?>40; background: <?= $situacao['cor'] ?>05;">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <p class="text-3xl font-bold" style="color: <?= $situacao['cor'] ?>;"><?= $situacao['total'] ?></p>
                                    <p class="text-sm font-semibold text-slate-700 mt-1"><?= htmlspecialchars($situacao['nome']) ?></p>
                                </div>
                                <div class="text-right">
                                    <span class="text-2xl font-bold" style="color: <?= $situacao['cor'] ?>;"><?= $situacao['percentual'] ?>%</span>
                                    <p class="text-xs text-slate-500">do total</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3 pt-4 border-t" style="border-color: <?= $situacao['cor'] ?>20;">
                                <div class="text-center p-3 rounded-lg" style="background: <?= $situacao['cor'] ?>15;">
                                    <p class="text-2xl font-bold" style="color: <?= $situacao['cor'] ?>;"><?= $situacao['hoje'] ?></p>
                                    <p class="text-xs text-slate-600 font-medium">Hoje</p>
                                </div>
                                <div class="text-center p-3 rounded-lg" style="background: <?= $situacao['cor'] ?>15;">
                                    <p class="text-2xl font-bold" style="color: <?= $situacao['cor'] ?>;"><?= $situacao['proximos_7_dias'] ?></p>
                                    <p class="text-xs text-slate-600 font-medium">Próximos 7 dias</p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <!-- Rankings -->
                <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Top Médicos -->
                    <div class="glass p-6">
                        <h2 class="text-lg font-semibold text-slate-900 mb-4 flex items-center gap-2">
                            <i class="fas fa-trophy text-amber-500"></i>
                            Top Médicos
                        </h2>
                        <div class="space-y-3">
                            <?php foreach ($stats_medico as $idx => $medico): ?>
                            <div class="flex items-center justify-between p-3 rounded-lg <?= $idx === 0 ? 'bg-amber-50 border border-amber-200' : 'bg-slate-50' ?>">
                                <div class="flex items-center gap-3">
                                    <span class="w-8 h-8 rounded-full <?= $idx === 0 ? 'bg-amber-500' : 'bg-slate-300' ?> text-white flex items-center justify-center text-sm font-bold">
                                        <?= $idx + 1 ?>
                                    </span>
                                    <span class="font-medium text-slate-900"><?= htmlspecialchars($medico['medico_nome']) ?></span>
                                </div>
                                <span class="chip chip--accent"><?= $medico['total'] ?> agendamentos</span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Top Hospitais -->
                    <div class="glass p-6">
                        <h2 class="text-lg font-semibold text-slate-900 mb-4 flex items-center gap-2">
                            <i class="fas fa-hospital text-blue-500"></i>
                            Top Hospitais
                        </h2>
                        <div class="space-y-3">
                            <?php foreach ($stats_hospital as $idx => $hospital): ?>
                            <div class="flex items-center justify-between p-3 rounded-lg <?= $idx === 0 ? 'bg-blue-50 border border-blue-200' : 'bg-slate-50' ?>">
                                <div class="flex items-center gap-3">
                                    <span class="w-8 h-8 rounded-full <?= $idx === 0 ? 'bg-blue-500' : 'bg-slate-300' ?> text-white flex items-center justify-center text-sm font-bold">
                                        <?= $idx + 1 ?>
                                    </span>
                                    <span class="font-medium text-slate-900"><?= htmlspecialchars($hospital['hospital']) ?></span>
                                </div>
                                <span class="chip chip--accent"><?= $hospital['total'] ?> procedimentos</span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>

                <!-- Gráfico de Evolução -->
                <section class="glass p-6">
                    <h2 class="text-lg font-semibold text-slate-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-chart-line text-indigo-500"></i>
                        Evolução dos Agendamentos (Últimos 6 Meses)
                    </h2>
                    <canvas id="chartAgendamentos" height="80"></canvas>
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

    <script>
        // Dados do gráfico de evolução mensal
        const monthlyData = <?= json_encode($stats_monthly) ?>;

        // Preparar labels e dados
        const labels = monthlyData.map(item => {
            const [ano, mes] = item.mes.split('-');
            const meses = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
            return meses[parseInt(mes) - 1] + '/' + ano.substring(2);
        });
        const data = monthlyData.map(item => parseInt(item.total));

        // Criar gráfico
        const ctx = document.getElementById('chartAgendamentos');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Agendamentos',
                        data: data,
                        borderColor: '#4f46e5',
                        backgroundColor: 'rgba(79, 70, 229, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        pointBackgroundColor: '#4f46e5',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.95)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            padding: 12,
                            borderColor: 'rgba(255, 255, 255, 0.1)',
                            borderWidth: 1,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y + ' agendamentos';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                color: '#64748b'
                            },
                            grid: {
                                color: 'rgba(226, 232, 240, 0.5)',
                                drawBorder: false
                            }
                        },
                        x: {
                            ticks: {
                                color: '#64748b'
                            },
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }
    </script>
</body>
</html>
