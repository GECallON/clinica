<?php
/**
 * Script de instalação do Sistema de Agendamento
 * Execute este arquivo apenas UMA VEZ após a instalação
 */

// Configurações do banco de dados
$db_host = 'localhost';
$db_user = 'root';
$db_pass = 'Aswf1010*';

$success = true;
$messages = [];

try {
    // Conectar ao MySQL (sem selecionar banco)
    $pdo = new PDO("mysql:host=$db_host", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $messages[] = "✓ Conectado ao MySQL com sucesso";

    // Ler o arquivo SQL
    $sql_file = __DIR__ . '/database/schema.sql';
    if (!file_exists($sql_file)) {
        throw new Exception("Arquivo schema.sql não encontrado!");
    }

    $sql = file_get_contents($sql_file);
    $messages[] = "✓ Arquivo SQL carregado";

    // Dividir e executar os comandos SQL
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }

    $messages[] = "✓ Banco de dados criado com sucesso";
    $messages[] = "✓ Tabelas criadas com sucesso";
    $messages[] = "✓ Dados iniciais inseridos";

    // Verificar se a pasta uploads existe e tem permissões
    $upload_dir = __DIR__ . '/uploads';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
        $messages[] = "✓ Pasta uploads criada";
    }

    if (is_writable($upload_dir)) {
        $messages[] = "✓ Pasta uploads tem permissão de escrita";
    } else {
        $messages[] = "⚠ AVISO: Pasta uploads não tem permissão de escrita. Execute: chmod 755 uploads/";
    }

} catch (Exception $e) {
    $success = false;
    $messages[] = "✗ ERRO: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalação - Sistema de Agendamento</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-purple-600 to-blue-600 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full p-8">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-purple-600 to-blue-600 rounded-full mb-4">
                <i class="fas fa-cog text-white text-2xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Instalação do Sistema</h1>
            <p class="text-gray-600">Sistema de Agendamento Médico</p>
        </div>

        <?php if ($success): ?>
        <div class="bg-green-50 border-l-4 border-green-500 p-6 rounded-lg mb-6">
            <div class="flex items-center mb-4">
                <i class="fas fa-check-circle text-green-500 text-3xl mr-3"></i>
                <h2 class="text-xl font-bold text-green-800">Instalação Concluída com Sucesso!</h2>
            </div>

            <div class="space-y-2 mb-6">
                <?php foreach ($messages as $msg): ?>
                <p class="text-green-700 text-sm"><?= htmlspecialchars($msg) ?></p>
                <?php endforeach; ?>
            </div>

            <div class="bg-white p-4 rounded-lg border border-green-200 mb-4">
                <h3 class="font-bold text-gray-800 mb-3">Credenciais de Acesso:</h3>
                <div class="space-y-2 text-sm">
                    <div class="bg-gray-50 p-3 rounded">
                        <p class="font-semibold text-purple-700">Administrador:</p>
                        <p>Email: <code class="bg-gray-200 px-2 py-1 rounded">admin@sistema.com</code></p>
                        <p>Senha: <code class="bg-gray-200 px-2 py-1 rounded">admin123</code></p>
                    </div>
                    <div class="bg-gray-50 p-3 rounded">
                        <p class="font-semibold text-blue-700">Médico (exemplo):</p>
                        <p>Email: <code class="bg-gray-200 px-2 py-1 rounded">joao.silva@clinica.com</code></p>
                        <p>Senha: <code class="bg-gray-200 px-2 py-1 rounded">medico123</code></p>
                    </div>
                </div>
            </div>

            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                    <p class="text-yellow-800 text-sm font-medium">
                        <strong>IMPORTANTE:</strong> Por segurança, DELETE este arquivo (install.php) após a instalação!
                    </p>
                </div>
            </div>

            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                <div class="flex items-center">
                    <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                    <p class="text-blue-800 text-sm">
                        Altere as senhas padrão após o primeiro acesso ao sistema.
                    </p>
                </div>
            </div>

            <div class="text-center mt-6">
                <a href="public/index.php" class="inline-block bg-gradient-to-r from-purple-600 to-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:shadow-lg transform hover:scale-[1.02] transition-all">
                    <i class="fas fa-sign-in-alt mr-2"></i> Acessar o Sistema
                </a>
            </div>
        </div>
        <?php else: ?>
        <div class="bg-red-50 border-l-4 border-red-500 p-6 rounded-lg">
            <div class="flex items-center mb-4">
                <i class="fas fa-times-circle text-red-500 text-3xl mr-3"></i>
                <h2 class="text-xl font-bold text-red-800">Erro na Instalação</h2>
            </div>

            <div class="space-y-2 mb-4">
                <?php foreach ($messages as $msg): ?>
                <p class="text-red-700 text-sm"><?= htmlspecialchars($msg) ?></p>
                <?php endforeach; ?>
            </div>

            <div class="bg-white p-4 rounded-lg border border-red-200">
                <h3 class="font-bold text-gray-800 mb-2">Possíveis soluções:</h3>
                <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
                    <li>Verifique as credenciais do banco de dados no topo deste arquivo</li>
                    <li>Certifique-se de que o MySQL está rodando</li>
                    <li>Verifique se o arquivo database/schema.sql existe</li>
                    <li>Confira as permissões do usuário do banco de dados</li>
                </ul>
            </div>

            <div class="text-center mt-6">
                <button onclick="location.reload()" class="bg-red-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-red-700 transition-colors">
                    <i class="fas fa-redo mr-2"></i> Tentar Novamente
                </button>
            </div>
        </div>
        <?php endif; ?>

        <div class="text-center mt-8 pt-6 border-t border-gray-200 text-sm text-gray-600">
            <p>Sistema de Agendamento Médico v1.0</p>
            <p class="mt-1">Desenvolvido com <i class="fas fa-heart text-red-500"></i> usando Claude Code</p>
        </div>
    </div>
</body>
</html>
