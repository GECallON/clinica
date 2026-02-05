<?php
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/models/Usuario.php';
require_once __DIR__ . '/../../src/models/Agendamento.php';
require_once __DIR__ . '/../../src/models/Procedimento.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$id = $_GET['id'] ?? null;
if (!$id) {
    redirect('agendamentos-list.php');
}

$agendamentoModel = new Agendamento();
$ag = $agendamentoModel->getById($id);

if (!$ag) {
    redirect('agendamentos-list.php');
}

$version = time();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Agendamento - MedAgenda Pro</title>
    <script src="https://cdn.tailwindcss.com?v=<?= $version ?>"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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
                        <a href="agendamentos-list.php" class="btn-muted btn-primary--icon">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <div>
                            <h1 class="page-title flex items-center gap-2">
                                <span class="icon-chip bg-indigo-100 text-indigo-600">
                                    <i class="fas fa-calendar-day"></i>
                                </span>
                                Detalhes do agendamento
                            </h1>
                            <p class="page-subtitle mt-1">Confira dados do paciente, solicitante e procedimento.</p>
                        </div>
                    </div>
                </section>

                <section class="glass p-6 max-w-4xl space-y-5">
                    <div class="grid gap-4">
                        <article class="p-4 rounded-2xl bg-slate-50">
                            <header class="flex items-center gap-2 text-sm font-semibold text-slate-600 mb-3">
                                <i class="fas fa-user-injured text-indigo-500 text-xs"></i>
                                Paciente
                            </header>
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm text-slate-600">
                                <div>
                                    <dt class="text-xs uppercase tracking-[0.16em] text-slate-400">Nome</dt>
                                    <dd class="font-semibold text-slate-900"><?= htmlspecialchars($ag['nome_paciente']) ?></dd>
                                </div>
                                <div>
                                    <dt class="text-xs uppercase tracking-[0.16em] text-slate-400">Convênio</dt>
                                    <dd class="font-semibold text-slate-900"><?= htmlspecialchars($ag['convenio']) ?></dd>
                                </div>
                            </dl>
                        </article>

                        <article class="p-4 rounded-2xl bg-slate-50">
                            <header class="flex items-center gap-2 text-sm font-semibold text-slate-600 mb-3">
                                <i class="fas fa-user-circle text-indigo-500 text-xs"></i>
                                Solicitante
                            </header>
                            <dl class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm text-slate-600">
                                <div>
                                    <dt class="text-xs uppercase tracking-[0.16em] text-slate-400">Nome</dt>
                                    <dd class="font-semibold text-slate-900"><?= htmlspecialchars($ag['nome_solicitante']) ?></dd>
                                </div>
                                <div>
                                    <dt class="text-xs uppercase tracking-[0.16em] text-slate-400">Email</dt>
                                    <dd class="font-semibold text-slate-900"><?= htmlspecialchars($ag['email_solicitante']) ?></dd>
                                </div>
                                <div>
                                    <dt class="text-xs uppercase tracking-[0.16em] text-slate-400">Telefone</dt>
                                    <dd class="font-semibold text-slate-900"><?= htmlspecialchars($ag['telefone_solicitante']) ?></dd>
                                </div>
                            </dl>
                        </article>

                        <article class="p-4 rounded-2xl bg-slate-50">
                            <header class="flex items-center gap-2 text-sm font-semibold text-slate-600 mb-3">
                                <i class="fas fa-procedures text-indigo-500 text-xs"></i>
                                Procedimento
                            </header>
                            <dl class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm text-slate-600">
                                <div>
                                    <dt class="text-xs uppercase tracking-[0.16em] text-slate-400">Procedimento</dt>
                                    <dd class="font-semibold text-slate-900"><?= htmlspecialchars($ag['procedimento_nome']) ?></dd>
                                </div>
                                <div>
                                    <dt class="text-xs uppercase tracking-[0.16em] text-slate-400">Médico</dt>
                                    <dd class="font-semibold text-slate-900"><?= htmlspecialchars($ag['medico_nome']) ?></dd>
                                </div>
                                <div>
                                    <dt class="text-xs uppercase tracking-[0.16em] text-slate-400">Hospital</dt>
                                    <dd class="font-semibold text-slate-900"><?= htmlspecialchars($ag['hospital']) ?></dd>
                                </div>
                                <div>
                                    <dt class="text-xs uppercase tracking-[0.16em] text-slate-400">Data</dt>
                                    <dd class="font-semibold text-slate-900"><?= date('d/m/Y', strtotime($ag['data_cirurgia'])) ?></dd>
                                </div>
                                <div>
                                    <dt class="text-xs uppercase tracking-[0.16em] text-slate-400">Hora</dt>
                                    <dd class="font-semibold text-slate-900"><?= date('H:i', strtotime($ag['hora_cirurgia'])) ?></dd>
                                </div>
                                <div>
                                    <dt class="text-xs uppercase tracking-[0.16em] text-slate-400">Situação</dt>
                                    <dd>
                                        <span class="chip chip--accent" style="background: <?= $ag['situacao_cor'] ?>22; color: <?= $ag['situacao_cor'] ?>;">
                                            <?= htmlspecialchars($ag['situacao_nome']) ?>
                                        </span>
                                    </dd>
                                </div>
                            </dl>
                        </article>

                        <?php if ($ag['material_necessario']): ?>
                        <article class="p-4 rounded-2xl bg-slate-50">
                            <header class="flex items-center gap-2 text-sm font-semibold text-slate-600 mb-3">
                                <i class="fas fa-box text-indigo-500 text-xs"></i>
                                Material necessário
                            </header>
                            <p class="text-sm text-slate-600 leading-relaxed"><?= nl2br(htmlspecialchars($ag['material_necessario'])) ?></p>
                        </article>
                        <?php endif; ?>

                        <?php if ($ag['observacoes']): ?>
                        <article class="p-4 rounded-2xl bg-slate-50">
                            <header class="flex items-center gap-2 text-sm font-semibold text-slate-600 mb-3">
                                <i class="fas fa-comment-medical text-indigo-500 text-xs"></i>
                                Observações
                            </header>
                            <p class="text-sm text-slate-600 leading-relaxed"><?= nl2br(htmlspecialchars($ag['observacoes'])) ?></p>
                        </article>
                        <?php endif; ?>

                        <?php if ($ag['arquivo_anexo']): ?>
                        <article class="p-4 rounded-2xl bg-slate-50">
                            <header class="flex items-center gap-2 text-sm font-semibold text-slate-600 mb-3">
                                <i class="fas fa-paperclip text-indigo-500 text-xs"></i>
                                Arquivo anexo
                            </header>
                            <a href="../../uploads/<?= htmlspecialchars($ag['arquivo_anexo']) ?>" target="_blank"
                               class="btn-muted">
                                <i class="fas fa-download text-xs"></i>
                                Baixar arquivo
                            </a>
                        </article>
                        <?php endif; ?>
                    </div>

                    <div class="flex justify-end">
                        <a href="agendamentos-list.php" class="btn-muted">
                            <i class="fas fa-arrow-left text-xs"></i>
                            Voltar
                        </a>
                    </div>
                </section>
            </main>
        </div>
    </div>
</body>
</html>
