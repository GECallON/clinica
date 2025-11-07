<?php
// Limpar todas as sessões
session_start();
session_destroy();

// Headers para limpar cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Atualizar Sistema - MedAgenda Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .spinner {
            animation: spin 2s linear infinite;
        }
    </style>
</head>
<body class="flex items-center justify-center p-4">
    <div class="glass rounded-3xl p-10 shadow-2xl max-w-2xl text-center">
        <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center mx-auto mb-6 shadow-xl spinner">
            <i class="fas fa-sync-alt text-white text-3xl"></i>
        </div>

        <h1 class="text-3xl font-black text-gray-800 mb-4">
            🔄 Atualizando Sistema...
        </h1>

        <p class="text-gray-600 mb-8">
            Limpando cache e carregando versão nova do MedAgenda Pro
        </p>

        <div class="bg-blue-50 rounded-xl p-6 mb-8 text-left">
            <h2 class="font-bold text-sm text-gray-800 mb-3">✨ Novidades na versão atualizada:</h2>
            <ul class="space-y-2 text-sm text-gray-700">
                <li><i class="fas fa-check text-green-600 mr-2"></i>Design refinado e elegante</li>
                <li><i class="fas fa-check text-green-600 mr-2"></i>Fontes proporcionais e harmoniosas</li>
                <li><i class="fas fa-check text-green-600 mr-2"></i>Alpine.js com filtros em tempo real</li>
                <li><i class="fas fa-check text-green-600 mr-2"></i>CRUD completo para tudo</li>
                <li><i class="fas fa-check text-green-600 mr-2"></i>Páginas separadas (sem popups)</li>
                <li><i class="fas fa-check text-green-600 mr-2"></i>Sistema de notificações para médicos</li>
            </ul>
        </div>

        <p class="text-sm text-gray-600 mb-6">
            Você será redirecionado em <span id="countdown" class="font-bold text-blue-600">3</span> segundos...
        </p>

        <a href="index.php" class="inline-block px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl font-bold hover:shadow-xl transition-all">
            <i class="fas fa-arrow-right mr-2"></i>Ir Agora para Login
        </a>
    </div>

    <script>
        // Limpar storage
        localStorage.clear();
        sessionStorage.clear();

        // Countdown
        let count = 3;
        const countdown = setInterval(() => {
            count--;
            document.getElementById('countdown').textContent = count;
            if (count <= 0) {
                clearInterval(countdown);
                window.location.href = 'index.php?v=' + Date.now();
            }
        }, 1000);
    </script>
</body>
</html>
