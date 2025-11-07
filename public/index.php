<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/models/Usuario.php';

if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('admin/home.php');
    } else {
        redirect('medico/dashboard.php');
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    $usuario = new Usuario();
    if ($usuario->login($email, $senha)) {
        if (isAdmin()) {
            redirect('admin/home.php');
        } else {
            redirect('medico/dashboard.php');
        }
    } else {
        $error = 'Email ou senha inválidos';
    }
}

$v = time();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Login - MedAgenda Pro</title>
    <script src="https://cdn.tailwindcss.com?v=<?= $v ?>"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/theme.css?v=<?= $v ?>">
</head>
<body class="py-10 px-4">
    <div class="auth-layout">
        <section class="auth-hero">
            <span class="auth-hero__badge">
                <i class="fas fa-sparkles text-yellow-200 text-xs"></i>
                Nova experiência 2024
            </span>
            <h1 class="auth-hero__title">MedAgenda Pro</h1>
            <p class="auth-hero__text">
                Fluxos fluidos para agendar cirurgias, acompanhar equipes médicas e manter o paciente no centro da operação.
            </p>

            <div class="floating-metric">
                <i class="fas fa-calendar-check text-xs"></i>
                +120 procedimentos/mês
            </div>

            <ul class="mt-8 space-y-2 text-sm text-white/80">
                <li>
                    <i class="fas fa-check-circle mr-2 text-emerald-200 text-xs"></i>
                    Painel multidisciplinar com indicadores em tempo real
                </li>
                <li>
                    <i class="fas fa-check-circle mr-2 text-emerald-200 text-xs"></i>
                    Fluxo seguro para laudos, anexos e materiais
                </li>
                <li>
                    <i class="fas fa-check-circle mr-2 text-emerald-200 text-xs"></i>
                    Agenda médica com calendário e alertas inteligentes
                </li>
            </ul>
        </section>

        <section class="auth-card">
            <header class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-xl font-semibold text-slate-900">Acesse sua conta</h2>
                    <p class="text-sm text-slate-500 mt-1">Autenticação segura para sua equipe médica.</p>
                </div>
                <span class="px-3 py-2 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-600">
                    v2.0 Preview
                </span>
            </header>

            <?php if ($error): ?>
            <div class="mb-6 rounded-2xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-600">
                <i class="fas fa-circle-exclamation mr-2"></i><?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-5">
                <div class="form-field">
                    <label for="email"><i class="fas fa-envelope mr-2 text-indigo-500 text-xs"></i>Email</label>
                    <input id="email" type="email" name="email" required placeholder="seu@email.com">
                </div>

                <div class="form-field">
                    <div class="flex items-center justify-between">
                        <label for="senha"><i class="fas fa-lock mr-2 text-indigo-500 text-xs"></i>Senha</label>
                        <a href="#" class="text-xs font-semibold text-indigo-500 hover:text-indigo-600 transition">Preciso de ajuda</a>
                    </div>
                    <input id="senha" type="password" name="senha" required placeholder="Digite sua senha">
                </div>

                <button type="submit" class="btn-primary w-full justify-center">
                    <i class="fas fa-arrow-right-to-bracket"></i>
                    Entrar no sistema
                </button>
            </form>

            <div class="mt-10 rounded-2xl border border-dashed border-slate-200 bg-slate-50/80 p-5 text-sm">
                <p class="font-semibold text-slate-600 mb-2">
                    <i class="fas fa-info-circle mr-2 text-indigo-500"></i>Credenciais de demonstração
                </p>
                <div class="grid gap-3 text-slate-500">
                    <p>
                        <span class="font-semibold text-slate-700">Administrador:</span>
                        admin@sistema.com • admin123
                    </p>
                    <p>
                        <span class="font-semibold text-slate-700">Médico:</span>
                        joao.silva@clinica.com • medico123
                    </p>
                </div>
            </div>
        </section>
    </div>

    <footer class="mt-12 text-center text-sm text-slate-500">
        &copy; <?= date('Y') ?> MedAgenda Pro — Plataforma de agendamento inteligente.
    </footer>
</body>
</html>
