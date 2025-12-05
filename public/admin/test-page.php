<?php
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/models/Usuario.php';
require_once __DIR__ . '/../../src/models/PedidoNovo.php';

if (!isLoggedIn() || !isAdmin()) {
    echo "VOCÊ PRECISA ESTAR LOGADO COMO ADMIN";
    exit;
}

echo "<h1>Teste de Modelos</h1>";

try {
    $pedidoModel = new PedidoNovo();
    $pedidos = $pedidoModel->getAll();
    echo "<p>✅ PedidoNovo model funcionando</p>";
    echo "<p>Total de pedidos: " . count($pedidos) . "</p>";
} catch (Exception $e) {
    echo "<p>❌ Erro PedidoNovo: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='pedidos-novos-create.php'>Ir para Criar Pedido</a></p>";
echo "<p><a href='pedidos-novos-list.php'>Ir para Lista de Pedidos</a></p>";
?>
