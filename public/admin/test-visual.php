<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TESTE VISUAL - Sistema Novo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .glass { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px); }
    </style>
</head>
<body class="flex items-center justify-center p-8">
    <div class="glass rounded-3xl p-12 shadow-2xl max-w-2xl text-center">
        <div class="w-24 h-24 bg-gradient-to-br from-green-500 to-green-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-xl">
            <i class="fas fa-check text-white text-5xl"></i>
        </div>

        <h1 class="text-4xl font-black text-gray-800 mb-4">
            ✨ Sistema NOVO Funcionando!
        </h1>

        <div class="space-y-4 text-left bg-gray-50 rounded-2xl p-6 mb-6">
            <p class="text-sm text-gray-700"><i class="fas fa-check-circle text-green-600 mr-2"></i><strong>Design refinado</strong> - Fontes menores e proporcionais</p>
            <p class="text-sm text-gray-700"><i class="fas fa-check-circle text-green-600 mr-2"></i><strong>Alpine.js</strong> - Framework reativo integrado</p>
            <p class="text-sm text-gray-700"><i class="fas fa-check-circle text-green-600 mr-2"></i><strong>CRUD completo</strong> - Procedimentos e Situações</p>
            <p class="text-sm text-gray-700"><i class="fas fa-check-circle text-green-600 mr-2"></i><strong>Páginas separadas</strong> - Sem popups</p>
            <p class="text-sm text-gray-700"><i class="fas fa-check-circle text-green-600 mr-2"></i><strong>Visual elegante</strong> - Glassmorphism e gradientes</p>
        </div>

        <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-4 mb-6">
            <p class="text-sm font-bold text-blue-900 mb-2">IMPORTANTE:</p>
            <p class="text-sm text-blue-800">Para ver o sistema novo, você PRECISA limpar o cache do navegador!</p>
        </div>

        <a href="home.php" class="inline-block px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl font-bold hover:shadow-xl transition-all">
            <i class="fas fa-arrow-right mr-2"></i>Ir para Dashboard NOVO
        </a>

        <div class="mt-6 pt-6 border-t border-gray-200">
            <p class="text-xs text-gray-600">Gerado em: <?= date('d/m/Y H:i:s') ?></p>
        </div>
    </div>
</body>
</html>
