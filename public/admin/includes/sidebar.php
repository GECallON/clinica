<?php
$current_page = basename($_SERVER['PHP_SELF']);

// Carregar models necessários
if (!class_exists('Usuario')) {
    require_once __DIR__ . '/../../../src/models/Usuario.php';
}
if (!class_exists('Agendamento')) {
    require_once __DIR__ . '/../../../src/models/Agendamento.php';
}
if (!class_exists('Procedimento')) {
    require_once __DIR__ . '/../../../src/models/Procedimento.php';
}
if (!class_exists('PedidoNovo')) {
    require_once __DIR__ . '/../../../src/models/PedidoNovo.php';
}

$usuarioModel = new Usuario();
$agendamentoModel = new Agendamento();
$procedimentoModel = new Procedimento();
$pedidoNovoModel = new PedidoNovo();

$total_usuarios = count($usuarioModel->getAll());
$total_agendamentos = count($agendamentoModel->getAll());
$total_procedimentos = count($procedimentoModel->getAll());
$total_pedidos_novos = count($pedidoNovoModel->getAll());
?>
<aside class="app-sidebar">
    <nav>
        <p class="app-sidebar__title">Visão Geral</p>
        <div class="app-sidebar__menu">
            <a href="dashboard.php" class="app-sidebar__link <?= in_array($current_page, ['dashboard.php', 'home.php'], true) ? 'is-active' : '' ?>">
                <i class="fas fa-house"></i>
                Dashboard
            </a>
            <a href="usuarios-list.php" class="app-sidebar__link <?= strpos($current_page, 'usuario') !== false ? 'is-active' : '' ?>">
                <i class="fas fa-users"></i>
                Usuários
                <span class="app-sidebar__badge"><?= $total_usuarios ?></span>
            </a>
            <a href="agendamentos-list.php" class="app-sidebar__link <?= strpos($current_page, 'agendamento') !== false ? 'is-active' : '' ?>">
                <i class="fas fa-calendar-day"></i>
                Agendamentos
                <span class="app-sidebar__badge"><?= $total_agendamentos ?></span>
            </a>
            <a href="pedidos-novos-list.php" class="app-sidebar__link <?= strpos($current_page, 'pedidos-novos') !== false ? 'is-active' : '' ?>">
                <i class="fas fa-file-medical"></i>
                Pedidos
                <span class="app-sidebar__badge"><?= $total_pedidos_novos ?></span>
            </a>
            <a href="procedimentos-list.php" class="app-sidebar__link <?= strpos($current_page, 'procedimento') !== false ? 'is-active' : '' ?>">
                <i class="fas fa-kit-medical"></i>
                Procedimentos
                <span class="app-sidebar__badge"><?= $total_procedimentos ?></span>
            </a>
            <a href="situacoes-list.php" class="app-sidebar__link <?= strpos($current_page, 'situacao') !== false ? 'is-active' : '' ?>">
                <i class="fas fa-swatchbook"></i>
                Situações
            </a>
            <a href="mensagens-list.php" class="app-sidebar__link <?= strpos($current_page, 'mensagem') !== false ? 'is-active' : '' ?>">
                <i class="fab fa-whatsapp"></i>
                Mensagens WhatsApp
            </a>
            <a href="email-config.php" class="app-sidebar__link <?= strpos($current_page, 'email') !== false ? 'is-active' : '' ?>">
                <i class="fas fa-envelope"></i>
                Configuração Email
            </a>
        </div>
    </nav>

    <div class="mt-10">
        <p class="app-sidebar__title">Ações rápidas</p>
        <div class="app-sidebar__menu">
            <a href="usuario-create.php" class="app-sidebar__link">
                <i class="fas fa-user-plus"></i>
                Novo usuário
            </a>
            <a href="agendamento-create.php" class="app-sidebar__link">
                <i class="fas fa-calendar-plus"></i>
                Novo agendamento
            </a>
            <a href="pedidos-novos-create.php" class="app-sidebar__link">
                <i class="fas fa-file-medical"></i>
                Novo Pedido
            </a>
        </div>
    </div>
</aside>
