<?php
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/models/Usuario.php';
require_once __DIR__ . '/../../src/models/Agendamento.php';
require_once __DIR__ . '/../../src/models/Procedimento.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$usuarioModel = new Usuario();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if ($usuarioModel->delete($_POST['id'])) {
        setFlashMessage('success', 'Usuário deletado com sucesso!');
    } else {
        setFlashMessage('error', 'Erro ao deletar usuário');
    }
    redirect('usuarios-list.php');
}

$usuarios = $usuarioModel->getAll();
$flash = getFlashMessage();
$version = time();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuários - MedAgenda Pro</title>
    <script src="https://cdn.tailwindcss.com?v=<?= $version ?>"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/theme.css?v=<?= $version ?>">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body x-data="usuariosApp()" x-init="init()">
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

                <section class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 class="page-title flex items-center gap-2">
                            <span class="icon-chip bg-indigo-100 text-indigo-600">
                                <i class="fas fa-users"></i>
                            </span>
                            Usuários
                        </h1>
                        <p class="page-subtitle mt-1">Centralize cadastros, níveis de acesso e status da equipe.</p>
                    </div>
                    <a href="usuario-create.php" class="btn-primary">
                        <i class="fas fa-user-plus"></i>
                        Novo usuário
                    </a>
                </section>

                <section class="glass rounded-3xl p-6 shadow-2xl">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="md:col-span-2 form-field">
                            <label class="font-semibold text-slate-600">
                                <i class="fas fa-search mr-2 text-indigo-500 text-xs"></i>
                                Pesquisar
                            </label>
                            <input
                                type="text"
                                x-model="search"
                                @input="filterUsers()"
                                placeholder="Nome, email ou telefone..."
                            >
                        </div>

                        <div class="form-field">
                            <label class="font-semibold text-slate-600">
                                <i class="fas fa-user-tag mr-2 text-indigo-500 text-xs"></i>
                                Nível de acesso
                            </label>
                            <select x-model="filterNivel" @change="filterUsers()">
                                <option value="">Todos</option>
                                <option value="admin">Administrador</option>
                                <option value="medico">Médico</option>
                            </select>
                        </div>

                        <div class="form-field">
                            <label class="font-semibold text-slate-600">
                                <i class="fas fa-toggle-on mr-2 text-indigo-500 text-xs"></i>
                                Status
                            </label>
                            <select x-model="filterStatus" @change="filterUsers()">
                                <option value="">Todos</option>
                                <option value="1">Ativos</option>
                                <option value="0">Inativos</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-wrap items-center justify-between gap-4 text-sm text-slate-500">
                        <p>
                            Mostrando
                            <span x-text="filteredUsers.length" class="font-semibold text-indigo-600"></span>
                            de
                            <span x-text="usuarios.length" class="font-semibold text-slate-700"></span>
                            usuários
                        </p>
                        <button @click="clearFilters()" class="btn-muted">
                            <i class="fas fa-xmark mr-2 text-slate-500 text-xs"></i>
                            Limpar filtros
                        </button>
                    </div>
                </section>

                <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    <template x-for="(usuario, index) in filteredUsers" :key="usuario.id">
                        <div class="glass rounded-3xl p-6 shadow-xl transition-all hover:-translate-y-1 hover:shadow-2xl" :style="'animation-delay: ' + (index * 0.04) + 's'">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-4">
                                    <div class="icon-chip bg-indigo-500/10 text-indigo-600">
                                        <span class="text-sm font-semibold" x-text="usuario.nome.charAt(0).toUpperCase()"></span>
                                    </div>
                                    <div>
                                        <h3 class="text-base font-semibold text-slate-900" x-text="usuario.nome"></h3>
                                        <p class="text-xs text-slate-500" x-text="usuario.email"></p>
                                    </div>
                                </div>
                                <span class="chip chip--accent" x-text="usuario.nivel_acesso === 'admin' ? 'Administrador' : 'Médico'"></span>
                            </div>

                            <div class="space-y-3 text-sm text-slate-600">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-phone text-slate-400 text-xs"></i>
                                    <span x-text="usuario.telefone || 'Telefone não informado'"></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-clock text-slate-400 text-xs"></i>
                                    <span>Cadastro: <span class="font-semibold" x-text="new Date(usuario.created_at).toLocaleDateString('pt-BR')"></span></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-circle text-xs leading-none" :class="usuario.ativo == 1 ? 'text-emerald-400' : 'text-red-400'"></i>
                                    <span x-text="usuario.ativo == 1 ? 'Ativo' : 'Inativo'"></span>
                                </div>
                            </div>

                            <div class="mt-4 flex items-center gap-3">
                                <a :href="'usuario-edit.php?id=' + usuario.id" class="btn-muted flex-1 flex items-center justify-center gap-2">
                                    <i class="fas fa-pen-to-square text-slate-500 text-xs"></i>
                                    Editar
                                </a>
                                <button @click="deleteUser(usuario.id, usuario.nome)" class="btn-primary btn-primary--icon">
                                    <i class="fas fa-trash-can"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </section>

                <section x-show="filteredUsers.length === 0" class="empty-state" x-cloak>
                    <i class="fas fa-user-slash text-3xl text-indigo-300"></i>
                    <p class="mt-3 font-semibold text-slate-700">Nenhum usuário encontrado com os filtros atuais.</p>
                    <p class="text-sm text-slate-500 mt-1">Ajuste os parâmetros de busca ou limpe os filtros.</p>
                </section>
            </main>
        </div>
    </div>

    <form id="deleteForm" method="POST" class="hidden">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" id="deleteId">
    </form>

    <script>
        function usuariosApp() {
            return {
                usuarios: <?= json_encode($usuarios) ?>,
                filteredUsers: [],
                search: '',
                filterNivel: '',
                filterStatus: '',

                init() {
                    this.filteredUsers = this.usuarios;
                },

                filterUsers() {
                    this.filteredUsers = this.usuarios.filter(user => {
                        const term = this.search.trim().toLowerCase();
                        const matchSearch = !term ||
                            user.nome.toLowerCase().includes(term) ||
                            user.email.toLowerCase().includes(term) ||
                            (user.telefone && user.telefone.toLowerCase().includes(term));

                        const matchNivel = !this.filterNivel || user.nivel_acesso === this.filterNivel;
                        const matchStatus = this.filterStatus === '' || user.ativo == this.filterStatus;

                        return matchSearch && matchNivel && matchStatus;
                    });
                },

                clearFilters() {
                    this.search = '';
                    this.filterNivel = '';
                    this.filterStatus = '';
                    this.filterUsers();
                },

                deleteUser(id, nome) {
                    if (confirm(`Tem certeza que deseja deletar o usuário "${nome}"?`)) {
                        document.getElementById('deleteId').value = id;
                        document.getElementById('deleteForm').submit();
                    }
                }
            }
        }
    </script>
</body>
</html>
