<?php
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/models/Procedimento.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$procedimentoModel = new Procedimento();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        if ($procedimentoModel->create($_POST)) {
            setFlashMessage('success', 'Procedimento criado com sucesso!');
        } else {
            setFlashMessage('error', 'Erro ao criar procedimento');
        }
        redirect('procedimentos.php');
    } elseif ($action === 'update') {
        if ($procedimentoModel->update($_POST['id'], $_POST)) {
            setFlashMessage('success', 'Procedimento atualizado com sucesso!');
        } else {
            setFlashMessage('error', 'Erro ao atualizar procedimento');
        }
        redirect('procedimentos.php');
    } elseif ($action === 'delete') {
        if ($procedimentoModel->delete($_POST['id'])) {
            setFlashMessage('success', 'Procedimento deletado com sucesso!');
        } else {
            setFlashMessage('error', 'Erro ao deletar procedimento');
        }
        redirect('procedimentos.php');
    }
}

$procedimentos = $procedimentoModel->getAll();
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procedimentos - Sistema de Agendamento</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    <nav class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <i class="fas fa-user-shield text-purple-600 text-2xl mr-3"></i>
                    <span class="font-bold text-xl text-gray-800">Painel Administrativo</span>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-700"><?= htmlspecialchars($_SESSION['nome']) ?></span>
                    <a href="../logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-sign-out-alt"></i> Sair
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex">
        <aside class="w-64 bg-white shadow-md min-h-screen">
            <nav class="mt-5">
                <a href="dashboard.php" class="flex items-center px-6 py-3 text-gray-600 hover:bg-gray-50">
                    <i class="fas fa-home mr-3"></i> Dashboard
                </a>
                <a href="usuarios.php" class="flex items-center px-6 py-3 text-gray-600 hover:bg-gray-50">
                    <i class="fas fa-users mr-3"></i> Usuários
                </a>
                <a href="agendamentos.php" class="flex items-center px-6 py-3 text-gray-600 hover:bg-gray-50">
                    <i class="fas fa-calendar-alt mr-3"></i> Agendamentos
                </a>
                <a href="procedimentos.php" class="flex items-center px-6 py-3 text-gray-700 bg-purple-50 border-l-4 border-purple-600">
                    <i class="fas fa-procedures mr-3"></i> Procedimentos
                </a>
                <a href="situacoes.php" class="flex items-center px-6 py-3 text-gray-600 hover:bg-gray-50">
                    <i class="fas fa-tags mr-3"></i> Situações
                </a>
            </nav>
        </aside>

        <main class="flex-1 p-8">
            <?php if ($flash): ?>
            <div class="mb-6 bg-<?= $flash['type'] === 'success' ? 'green' : 'red' ?>-50 border-l-4 border-<?= $flash['type'] === 'success' ? 'green' : 'red' ?>-500 p-4 rounded">
                <p class="text-<?= $flash['type'] === 'success' ? 'green' : 'red' ?>-700"><?= htmlspecialchars($flash['message']) ?></p>
            </div>
            <?php endif; ?>

            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Procedimentos</h1>
                    <p class="text-gray-600">Gerencie os procedimentos cirúrgicos</p>
                </div>
                <button onclick="openModal()" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-medium">
                    <i class="fas fa-plus mr-2"></i> Novo Procedimento
                </button>
            </div>

            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descrição</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duração (min)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($procedimentos as $p): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900"><?= $p['id'] ?></td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900"><?= htmlspecialchars($p['nome']) ?></td>
                            <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars(substr($p['descricao'] ?? '', 0, 50)) ?><?= strlen($p['descricao'] ?? '') > 50 ? '...' : '' ?></td>
                            <td class="px-6 py-4 text-sm text-gray-900"><?= $p['duracao_estimada'] ?></td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 text-xs font-semibold rounded-full <?= $p['ativo'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                    <?= $p['ativo'] ? 'Ativo' : 'Inativo' ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <button onclick='editProcedimento(<?= json_encode($p) ?>)' class="text-blue-600 hover:text-blue-900 mr-3">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteProcedimento(<?= $p['id'] ?>, '<?= htmlspecialchars($p['nome']) ?>')" class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Modal -->
    <div id="modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl max-w-md w-full p-6">
            <h2 id="modalTitle" class="text-2xl font-bold text-gray-800 mb-4">Novo Procedimento</h2>
            <form id="procedimentoForm" method="POST">
                <input type="hidden" name="action" id="action" value="create">
                <input type="hidden" name="id" id="procedimentoId">

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                        <input type="text" name="nome" id="nome" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                        <textarea name="descricao" id="descricao" rows="3" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-600"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Duração Estimada (minutos)</label>
                        <input type="number" name="duracao_estimada" id="duracao_estimada" value="60" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-600">
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="ativo" id="ativo" value="1" checked class="mr-2">
                            <span class="text-sm text-gray-700">Ativo</span>
                        </label>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <form id="deleteForm" method="POST" class="hidden">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" id="deleteId">
    </form>

    <script>
        function openModal() {
            document.getElementById('modal').classList.remove('hidden');
            document.getElementById('modalTitle').textContent = 'Novo Procedimento';
            document.getElementById('action').value = 'create';
            document.getElementById('procedimentoForm').reset();
        }

        function editProcedimento(proc) {
            document.getElementById('modal').classList.remove('hidden');
            document.getElementById('modalTitle').textContent = 'Editar Procedimento';
            document.getElementById('action').value = 'update';
            document.getElementById('procedimentoId').value = proc.id;
            document.getElementById('nome').value = proc.nome;
            document.getElementById('descricao').value = proc.descricao || '';
            document.getElementById('duracao_estimada').value = proc.duracao_estimada;
            document.getElementById('ativo').checked = proc.ativo == 1;
        }

        function deleteProcedimento(id, nome) {
            if (confirm(`Tem certeza que deseja deletar o procedimento "${nome}"?`)) {
                document.getElementById('deleteId').value = id;
                document.getElementById('deleteForm').submit();
            }
        }

        function closeModal() {
            document.getElementById('modal').classList.add('hidden');
        }
    </script>
</body>
</html>
