<?php
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/models/Situacao.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$situacaoModel = new Situacao();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        if ($situacaoModel->create($_POST)) {
            setFlashMessage('success', 'Situação criada com sucesso!');
        } else {
            setFlashMessage('error', 'Erro ao criar situação');
        }
        redirect('situacoes.php');
    } elseif ($action === 'update') {
        if ($situacaoModel->update($_POST['id'], $_POST)) {
            setFlashMessage('success', 'Situação atualizada com sucesso!');
        } else {
            setFlashMessage('error', 'Erro ao atualizar situação');
        }
        redirect('situacoes.php');
    } elseif ($action === 'delete') {
        if ($situacaoModel->delete($_POST['id'])) {
            setFlashMessage('success', 'Situação deletada com sucesso!');
        } else {
            setFlashMessage('error', 'Erro ao deletar situação');
        }
        redirect('situacoes.php');
    }
}

$situacoes = $situacaoModel->getAll();
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Situações - Sistema de Agendamento</title>
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
                <a href="procedimentos.php" class="flex items-center px-6 py-3 text-gray-600 hover:bg-gray-50">
                    <i class="fas fa-procedures mr-3"></i> Procedimentos
                </a>
                <a href="situacoes.php" class="flex items-center px-6 py-3 text-gray-700 bg-purple-50 border-l-4 border-purple-600">
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
                    <h1 class="text-3xl font-bold text-gray-800">Situações</h1>
                    <p class="text-gray-600">Gerencie as situações dos agendamentos</p>
                </div>
                <button onclick="openModal()" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-medium">
                    <i class="fas fa-plus mr-2"></i> Nova Situação
                </button>
            </div>

            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Visualização</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($situacoes as $s): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900"><?= $s['id'] ?></td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900"><?= htmlspecialchars($s['nome']) ?></td>
                            <td class="px-6 py-4 text-sm text-gray-900"><?= htmlspecialchars($s['cor']) ?></td>
                            <td class="px-6 py-4">
                                <span class="px-4 py-2 text-xs font-semibold rounded-full text-white" style="background-color: <?= $s['cor'] ?>">
                                    <?= htmlspecialchars($s['nome']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 text-xs font-semibold rounded-full <?= $s['ativo'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                    <?= $s['ativo'] ? 'Ativo' : 'Inativo' ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <button onclick='editSituacao(<?= json_encode($s) ?>)' class="text-blue-600 hover:text-blue-900 mr-3">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteSituacao(<?= $s['id'] ?>, '<?= htmlspecialchars($s['nome']) ?>')" class="text-red-600 hover:text-red-900">
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
            <h2 id="modalTitle" class="text-2xl font-bold text-gray-800 mb-4">Nova Situação</h2>
            <form id="situacaoForm" method="POST">
                <input type="hidden" name="action" id="action" value="create">
                <input type="hidden" name="id" id="situacaoId">

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                        <input type="text" name="nome" id="nome" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cor *</label>
                        <input type="color" name="cor" id="cor" value="#3B82F6" class="w-full h-12 px-2 py-1 border rounded-lg focus:ring-2 focus:ring-purple-600">
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
            document.getElementById('modalTitle').textContent = 'Nova Situação';
            document.getElementById('action').value = 'create';
            document.getElementById('situacaoForm').reset();
        }

        function editSituacao(sit) {
            document.getElementById('modal').classList.remove('hidden');
            document.getElementById('modalTitle').textContent = 'Editar Situação';
            document.getElementById('action').value = 'update';
            document.getElementById('situacaoId').value = sit.id;
            document.getElementById('nome').value = sit.nome;
            document.getElementById('cor').value = sit.cor;
            document.getElementById('ativo').checked = sit.ativo == 1;
        }

        function deleteSituacao(id, nome) {
            if (confirm(`Tem certeza que deseja deletar a situação "${nome}"?`)) {
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
