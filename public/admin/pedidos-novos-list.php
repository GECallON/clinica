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
$usuarioModel = new Usuario();

// Capturar filtros
$filtros = [];
if (!empty($_GET['medico_id'])) {
    $filtros['medico_id'] = $_GET['medico_id'];
}
if (!empty($_GET['situacao_id'])) {
    $filtros['situacao_id'] = $_GET['situacao_id'];
}
if (!empty($_GET['busca'])) {
    $filtros['busca'] = $_GET['busca'];
}
if (!empty($_GET['ordem'])) {
    $filtros['ordem'] = $_GET['ordem'];
}

$pedidos = $pedidoModel->getAll($filtros);
$situacoes = $situacaoModel->getAtivos();
$medicos = $usuarioModel->getMedicos();
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

                        <div class="flex-1 min-w-[180px] max-w-[220px]">
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                                <i class="fas fa-user-md text-blue-500 mr-1"></i>
                                Médico
                            </label>
                            <select name="medico_id" class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                                <option value="">Todos</option>
                                <?php foreach ($medicos as $medico): ?>
                                    <option value="<?= $medico['id'] ?>" <?= (isset($_GET['medico_id']) && $_GET['medico_id'] == $medico['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($medico['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
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

                        <div class="flex-1 min-w-[160px] max-w-[200px]">
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                                <i class="fas fa-sort text-blue-500 mr-1"></i>
                                Ordenar
                            </label>
                            <select name="ordem" class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                                <option value="data_desc" <?= (($_GET['ordem'] ?? 'data_desc') == 'data_desc') ? 'selected' : '' ?>>Mais recentes</option>
                                <option value="data_asc" <?= (($_GET['ordem'] ?? '') == 'data_asc') ? 'selected' : '' ?>>Mais antigas</option>
                                <option value="paciente_asc" <?= (($_GET['ordem'] ?? '') == 'paciente_asc') ? 'selected' : '' ?>>Paciente A-Z</option>
                                <option value="paciente_desc" <?= (($_GET['ordem'] ?? '') == 'paciente_desc') ? 'selected' : '' ?>>Paciente Z-A</option>
                            </select>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold">
                                <i class="fas fa-filter mr-1"></i>
                                Filtrar
                            </button>
                            <a href="pedidos-novos-list.php" class="px-4 py-2 text-sm bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition font-semibold">
                                <i class="fas fa-times mr-1"></i>
                                Limpar
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Resumo por Médico -->
                <?php if (!empty($_GET['medico_id'])):
                    $medico_selecionado = array_filter($medicos, fn($m) => $m['id'] == $_GET['medico_id']);
                    $medico_selecionado = reset($medico_selecionado);
                    $total_pacientes = count($pedidos);
                    $total_valor = array_sum(array_column($pedidos, 'valor_material'));
                ?>
                <div class="glass p-6 border-l-4 border-blue-500">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-slate-900 mb-2">
                                <i class="fas fa-user-md text-blue-600 mr-2"></i>
                                Relatório: Dr(a). <?= htmlspecialchars($medico_selecionado['nome']) ?>
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
                                <div class="bg-white p-4 rounded-lg border border-slate-200">
                                    <p class="text-sm text-slate-500 font-semibold">Total de Pacientes</p>
                                    <p class="text-3xl font-bold text-blue-600"><?= $total_pacientes ?></p>
                                </div>
                                <div class="bg-white p-4 rounded-lg border border-slate-200">
                                    <p class="text-sm text-slate-500 font-semibold">Valor Total de Materiais</p>
                                    <p class="text-2xl font-bold text-green-600">R$ <?= number_format($total_valor, 2, ',', '.') ?></p>
                                </div>
                                <div class="bg-white p-4 rounded-lg border border-slate-200">
                                    <p class="text-sm text-slate-500 font-semibold">Média por Paciente</p>
                                    <p class="text-2xl font-bold text-purple-600">R$ <?= $total_pacientes > 0 ? number_format($total_valor / $total_pacientes, 2, ',', '.') : '0,00' ?></p>
                                </div>
                                <div class="bg-white p-4 rounded-lg border border-slate-200">
                                    <p class="text-sm text-slate-500 font-semibold">Situações Diferentes</p>
                                    <p class="text-3xl font-bold text-orange-600"><?= count(array_unique(array_column($pedidos, 'situacao_id'))) ?></p>
                                </div>
                            </div>

                            <!-- Lista de Pacientes do Médico -->
                            <div class="mt-6">
                                <h4 class="text-md font-semibold text-slate-700 mb-3">Lista de Pacientes:</h4>
                                <div class="bg-white rounded-lg border border-slate-200 max-h-60 overflow-y-auto">
                                    <ul class="divide-y divide-slate-100">
                                        <?php
                                        $pacientes_unicos = [];
                                        foreach ($pedidos as $p) {
                                            $pacientes_unicos[$p['nome_paciente']][] = $p;
                                        }
                                        foreach ($pacientes_unicos as $nome_paciente => $pedidos_paciente):
                                        ?>
                                        <li class="px-4 py-3 hover:bg-slate-50 transition">
                                            <div class="flex items-center justify-between">
                                                <div class="flex-1">
                                                    <p class="font-semibold text-slate-900"><?= htmlspecialchars($nome_paciente) ?></p>
                                                    <p class="text-sm text-slate-500">
                                                        <?= count($pedidos_paciente) ?> pedido(s) •
                                                        <?php
                                                        $telefone = $pedidos_paciente[0]['telefone'];
                                                        echo $telefone ? htmlspecialchars($telefone) : 'Sem telefone';
                                                        ?>
                                                    </p>
                                                </div>
                                                <div class="flex gap-2">
                                                    <?php foreach (array_slice($pedidos_paciente, 0, 3) as $pp): ?>
                                                    <span class="px-2 py-1 text-xs rounded-full" style="background: <?= $pp['situacao_cor'] ?>22; color: <?= $pp['situacao_cor'] ?>;">
                                                        <?= htmlspecialchars($pp['situacao_nome']) ?>
                                                    </span>
                                                    <?php endforeach; ?>
                                                    <?php if (count($pedidos_paciente) > 3): ?>
                                                    <span class="px-2 py-1 text-xs rounded-full bg-slate-100 text-slate-600">
                                                        +<?= count($pedidos_paciente) - 3 ?>
                                                    </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>

                            <div class="mt-4 flex gap-3">
                                <button onclick="window.print()" class="btn-primary">
                                    <i class="fas fa-print"></i>
                                    Imprimir Relatório
                                </button>
                                <button onclick="exportarExcel()" class="btn-muted">
                                    <i class="fas fa-file-excel text-green-600"></i>
                                    Exportar Excel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Cards de Estatísticas -->
                <?php
                $stats_situacao = $pedidoModel->getStatsBySituacao();
                if (count($stats_situacao) > 0):
                ?>
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
                <?php endif; ?>

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
                                            <button onclick="verHistorico(<?= $pedido['id'] ?>)" class="btn-muted inline-flex items-center gap-2" title="Ver Histórico">
                                                <i class="fas fa-history text-orange-500"></i>
                                            </button>
                                            <a href="pedido-print.php?id=<?= $pedido['id'] ?>" target="_blank" class="btn-muted inline-flex items-center gap-2" title="Imprimir/PDF">
                                                <i class="fas fa-print text-purple-500"></i>
                                            </a>
                                            <a href="pedidos-novos-create.php?id=<?= $pedido['id'] ?>" class="btn-muted inline-flex items-center gap-2" title="Editar">
                                                <i class="fas fa-edit text-blue-500"></i>
                                            </a>
                                            <button onclick="deletePedido(<?= $pedido['id'] ?>)" class="btn-muted inline-flex items-center gap-2 text-red-600 hover:bg-red-50" title="Excluir">
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

    <!-- Modal de Agendamento -->
    <div id="modalAgendamento" class="hidden fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="glass rounded-3xl max-w-4xl w-full max-h-[90vh] overflow-y-auto shadow-2xl">
            <form id="formAgendamento" class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-slate-900 flex items-center gap-2">
                        <i class="fas fa-calendar-check text-green-500"></i>
                        Criar Agendamento
                    </h2>
                    <button type="button" onclick="fecharModalAgendamento()" class="btn-muted">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <input type="hidden" id="pedido_id_agendamento" name="pedido_id">
                <input type="hidden" id="situacao_id_agendamento" name="situacao_id" value="14">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Nome do Solicitante *</label>
                        <input type="text" name="nome_solicitante" required class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Número de Telefone *</label>
                        <input type="tel" name="telefone_solicitante" required class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Protocolo</label>
                        <input type="text" name="protocolo" class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Data do Procedimento *</label>
                        <input type="date" name="data_cirurgia" required class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Hora do Procedimento *</label>
                        <input type="time" name="hora_cirurgia" required class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Hospital *</label>
                        <input type="text" name="hospital" required class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Material</label>
                        <textarea name="material_necessario" rows="3" class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-green-500 focus:border-transparent"></textarea>
                    </div>
                </div>

                <div class="bg-slate-50 rounded-xl p-4 mb-6">
                    <p class="text-sm font-semibold text-slate-700 mb-3">Dados do Pedido (preenchidos automaticamente):</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                        <div>
                            <span class="text-slate-500">Paciente:</span>
                            <span id="info_paciente" class="font-semibold text-slate-900 ml-2">-</span>
                        </div>
                        <div>
                            <span class="text-slate-500">Médico:</span>
                            <span id="info_medico" class="font-semibold text-slate-900 ml-2">-</span>
                        </div>
                        <div>
                            <span class="text-slate-500">Convênio:</span>
                            <span id="info_convenio" class="font-semibold text-slate-900 ml-2">-</span>
                        </div>
                        <div>
                            <span class="text-slate-500">Procedimento:</span>
                            <span id="info_procedimento" class="font-semibold text-slate-900 ml-2">-</span>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="btn-primary flex-1">
                        <i class="fas fa-check"></i>
                        Criar Agendamento
                    </button>
                    <button type="button" onclick="fecharModalAgendamento()" class="btn-muted">
                        Cancelar
                    </button>
                </div>
            </form>
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

            // Se mudar para "Agendado" (ID 14), abrir modal
            if (situacaoIdNova == 14) {
                abrirModalAgendamento(pedidoId);
                return;
            }

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

        // Modal de Histórico
        async function verHistorico(pedidoId) {
            document.getElementById('modalHistorico').classList.remove('hidden');

            try {
                const response = await fetch(`pedidos-novos.php?action=get_historico&id=${pedidoId}`);
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

        // Modal de Agendamento
        let pedidoDados = {};

        async function abrirModalAgendamento(pedidoId) {
            try {
                const response = await fetch(`pedidos-novos.php?action=get_pedido&id=${pedidoId}`);
                const data = await response.json();

                if (data.success) {
                    pedidoDados = data.pedido;

                    // Preencher dados do pedido
                    document.getElementById('pedido_id_agendamento').value = pedidoId;
                    document.getElementById('info_paciente').textContent = pedidoDados.nome_paciente;
                    document.getElementById('info_medico').textContent = pedidoDados.medico_nome || '-';
                    document.getElementById('info_convenio').textContent = pedidoDados.convenio;
                    document.getElementById('info_procedimento').textContent = pedidoDados.procedimento || '-';

                    document.getElementById('modalAgendamento').classList.remove('hidden');
                }
            } catch (error) {
                alert('Erro ao carregar dados do pedido');
                location.reload();
            }
        }

        function fecharModalAgendamento() {
            document.getElementById('modalAgendamento').classList.add('hidden');
            document.getElementById('formAgendamento').reset();
            location.reload();
        }

        // Submit do form de agendamento
        document.getElementById('formAgendamento').addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(e.target);
            formData.append('action', 'criar_agendamento');

            // Adicionar dados do pedido
            formData.append('nome_paciente', pedidoDados.nome_paciente);
            formData.append('medico_id', pedidoDados.medico_id);
            formData.append('convenio', pedidoDados.convenio);
            formData.append('procedimento', pedidoDados.procedimento);
            formData.append('fornecedor', pedidoDados.fornecedor || '');
            formData.append('valor_material', pedidoDados.valor_material || '');
            formData.append('observacao', pedidoDados.observacao || '');

            try {
                const response = await fetch('pedidos-novos.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    alert('✅ Agendamento criado com sucesso!');
                    location.reload();
                } else {
                    alert('❌ ' + (result.message || 'Erro ao criar agendamento'));
                }
            } catch (error) {
                alert('❌ Erro ao criar agendamento');
                console.error(error);
            }
        });

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
        function exportarExcel() {
            // Capturar dados da tabela
            const medicoId = new URLSearchParams(window.location.search).get('medico_id');
            if (!medicoId) {
                alert('Selecione um médico para exportar');
                return;
            }

            // Redirecionar para página de exportação
            window.location.href = 'pedidos-export-excel.php?medico_id=' + medicoId + '&' + window.location.search.substring(1);
        }
    </script>
</body>
</html>
