<?php
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/models/Usuario.php';
require_once __DIR__ . '/../../src/models/Agendamento.php';
require_once __DIR__ . '/../../src/models/Procedimento.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$usuarioModel = new Usuario();
$error = '';
$version = time();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($usuarioModel->create($_POST)) {
        setFlashMessage('success', 'Usuário criado com sucesso!');
        redirect('usuarios-list.php');
    } else {
        $error = 'Erro ao criar usuário. Verifique se o email já está em uso.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Usuário - MedAgenda Pro</title>
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
                <section class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <a href="usuarios-list.php" class="btn-muted btn-primary--icon">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <div>
                            <h1 class="page-title flex items-center gap-2">
                                <span class="icon-chip bg-indigo-100 text-indigo-600">
                                    <i class="fas fa-user-plus"></i>
                                </span>
                                Novo Usuário
                            </h1>
                            <p class="page-subtitle mt-1">Cadastre um novo membro para a equipe.</p>
                        </div>
                    </div>
                </section>

                <?php if ($error): ?>
                <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
                    <i class="fas fa-triangle-exclamation mr-2"></i><?= $error ?>
                </div>
                <?php endif; ?>

                <section class="glass p-6 max-w-3xl">
                    <form method="POST" class="space-y-6" x-data="{ showPassword: false }">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="form-field md:col-span-2">
                                <label for="nome"><i class="fas fa-user mr-2 text-indigo-500 text-xs"></i>Nome completo *</label>
                                <input id="nome" name="nome" type="text" class="w-full" required placeholder="Dr. João Silva">
                            </div>

                            <div class="form-field">
                                <label for="email"><i class="fas fa-envelope mr-2 text-indigo-500 text-xs"></i>Email *</label>
                                <input id="email" name="email" type="email" class="w-full" required placeholder="joao@clinica.com">
                            </div>

                            <div class="form-field">
                                <label for="telefone"><i class="fas fa-phone mr-2 text-indigo-500 text-xs"></i>Telefone</label>
                                <input id="telefone" name="telefone" type="text" class="w-full" placeholder="(00) 00000-0000">
                            </div>

                            <div class="form-field relative">
                                <label for="senha"><i class="fas fa-lock mr-2 text-indigo-500 text-xs"></i>Senha *</label>
                                <input :type="showPassword ? 'text' : 'password'" id="senha" name="senha" class="w-full pr-10" required placeholder="********">
                                <button type="button" @click="showPassword = !showPassword"
                                        class="absolute right-3 top-[38px] text-slate-400 hover:text-slate-600">
                                    <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                </button>
                            </div>

                            <div class="form-field">
                                <label for="nivel_acesso"><i class="fas fa-user-tag mr-2 text-indigo-500 text-xs"></i>Nível de acesso *</label>
                                <select id="nivel_acesso" name="nivel_acesso" class="w-full" required>
                                    <option value="medico">Médico</option>
                                    <option value="admin">Administrador</option>
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <label class="flex items-center gap-2 text-sm font-medium text-slate-600">
                                    <input type="checkbox" name="ativo" value="1" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-400" checked>
                                    Usuário ativo
                                </label>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3">
                            <a href="usuarios-list.php" class="btn-muted">
                                <i class="fas fa-xmark text-xs"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-check"></i>
                                Criar usuário
                            </button>
                        </div>
                    </form>
                </section>
            </main>
        </div>
    </div>
</body>
</html>
