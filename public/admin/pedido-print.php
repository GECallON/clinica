<?php
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/models/PedidoNovo.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$id = $_GET['id'] ?? null;
if (!$id) {
    redirect('pedidos-novos-list.php');
}

$pedidoModel = new PedidoNovo();
$pedido = $pedidoModel->getById($id);

if (!$pedido) {
    redirect('pedidos-novos-list.php');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido #<?= $pedido['id'] ?> - <?= htmlspecialchars($pedido['nome_paciente']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none; }
            body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
        }
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-4xl mx-auto bg-white shadow-lg rounded-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-8">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-bold mb-2">MedAgenda Pro</h1>
                    <p class="text-blue-100">Detalhes do Pedido</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-blue-100">Pedido Nº</p>
                    <p class="text-3xl font-bold">#<?= str_pad($pedido['id'], 5, '0', STR_PAD_LEFT) ?></p>
                </div>
            </div>
        </div>

        <!-- Conteúdo -->
        <div class="p-8">
            <!-- Informações do Paciente -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-4 border-b-2 border-blue-500 pb-2">
                    Informações do Paciente
                </h2>
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm text-gray-500 font-semibold">Nome do Paciente</p>
                        <p class="text-lg text-gray-900"><?= htmlspecialchars($pedido['nome_paciente']) ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-semibold">Telefone</p>
                        <p class="text-lg text-gray-900"><?= htmlspecialchars($pedido['telefone'] ?? '-') ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-semibold">Convênio</p>
                        <p class="text-lg text-gray-900"><?= htmlspecialchars($pedido['convenio']) ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-semibold">Data de Recebimento</p>
                        <p class="text-lg text-gray-900"><?= $pedido['data_recebimento'] ? date('d/m/Y', strtotime($pedido['data_recebimento'])) : '-' ?></p>
                    </div>
                </div>
            </div>

            <!-- Informações Médicas -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-4 border-b-2 border-blue-500 pb-2">
                    Informações Médicas
                </h2>
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm text-gray-500 font-semibold">Médico Responsável</p>
                        <p class="text-lg text-gray-900"><?= htmlspecialchars($pedido['medico_nome'] ?? $pedido['nome_medico'] ?? '-') ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-semibold">Procedimento</p>
                        <p class="text-lg text-gray-900"><?= htmlspecialchars($pedido['procedimento'] ?? '-') ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-semibold">Fornecedor</p>
                        <p class="text-lg text-gray-900"><?= htmlspecialchars($pedido['fornecedor'] ?? '-') ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-semibold">Valor do Material</p>
                        <p class="text-lg text-gray-900 font-bold">
                            <?= $pedido['valor_material'] ? 'R$ ' . number_format($pedido['valor_material'], 2, ',', '.') : '-' ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Status -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-4 border-b-2 border-blue-500 pb-2">
                    Status do Pedido
                </h2>
                <div class="flex items-center gap-3">
                    <div class="px-6 py-3 rounded-lg font-semibold text-white" style="background-color: <?= $pedido['situacao_cor'] ?? '#3b82f6' ?>;">
                        <?= htmlspecialchars($pedido['situacao_nome'] ?? 'Sem situação') ?>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Origem</p>
                        <p class="text-gray-900"><?= htmlspecialchars($pedido['origem'] ?? '-') ?></p>
                    </div>
                </div>
            </div>

            <!-- Observações -->
            <?php if ($pedido['observacao']): ?>
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-4 border-b-2 border-blue-500 pb-2">
                    Observações
                </h2>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-gray-700 whitespace-pre-wrap"><?= htmlspecialchars($pedido['observacao']) ?></p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Informações do Sistema -->
            <div class="border-t pt-6 mt-8">
                <div class="grid grid-cols-2 gap-4 text-sm text-gray-500">
                    <div>
                        <p class="font-semibold">Data de Cadastro</p>
                        <p><?= date('d/m/Y H:i', strtotime($pedido['created_at'])) ?></p>
                    </div>
                    <div>
                        <p class="font-semibold">Última Atualização</p>
                        <p><?= date('d/m/Y H:i', strtotime($pedido['updated_at'])) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-100 p-6 text-center text-sm text-gray-600">
            <p>Documento gerado em <?= date('d/m/Y H:i:s') ?></p>
            <p class="mt-2">MedAgenda Pro - Sistema de Gestão de Agendamentos</p>
        </div>
    </div>

    <!-- Botões de Ação -->
    <div class="max-w-4xl mx-auto mt-6 flex gap-4 justify-center no-print">
        <button onclick="window.print()" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            Imprimir / Salvar PDF
        </button>
        <button onclick="window.close()" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-semibold">
            Fechar
        </button>
    </div>
</body>
</html>
