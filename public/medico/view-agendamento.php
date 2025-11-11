<?php
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/models/Agendamento.php';

if (!isLoggedIn() || !isMedico()) {
    die('Acesso negado');
}

$id = $_GET['id'] ?? null;
if (!$id) {
    die('ID inválido');
}

$agendamentoModel = new Agendamento();
$ag = $agendamentoModel->getById($id);

if (!$ag || $ag['medico_id'] != $_SESSION['user_id']) {
    die('Agendamento não encontrado ou sem permissão');
}
?>
<div class="flex justify-end mb-3">
    <button type="button" onclick="closeModal()" class="btn-muted btn-primary--icon">
        <i class="fas fa-xmark text-xs"></i>
    </button>
</div>

<div class="space-y-5">
    <header class="space-y-1">
        <h2 class="text-xl font-semibold text-slate-900">Detalhes do agendamento</h2>
        <p class="text-sm text-slate-500"><?= date('d/m/Y H:i', strtotime($ag['data_cirurgia'] . ' ' . $ag['hora_cirurgia'])) ?> • <?= htmlspecialchars($ag['hospital']) ?></p>
    </header>

    <section class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
        <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-[0.16em] mb-3">
            <i class="fas fa-user-injured text-indigo-500 mr-2"></i>Paciente
        </h3>
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm text-slate-600">
            <div>
                <dt class="text-xs uppercase tracking-[0.14em] text-slate-400">Nome</dt>
                <dd class="font-semibold text-slate-900"><?= htmlspecialchars($ag['nome_paciente']) ?></dd>
            </div>
            <div>
                <dt class="text-xs uppercase tracking-[0.14em] text-slate-400">Telefone</dt>
                <dd class="font-semibold text-slate-900"><?= htmlspecialchars($ag['telefone_paciente'] ?? 'Não informado') ?></dd>
            </div>
            <div>
                <dt class="text-xs uppercase tracking-[0.14em] text-slate-400">Email</dt>
                <dd class="font-semibold text-slate-900"><?= htmlspecialchars($ag['email_paciente'] ?? 'Não informado') ?></dd>
            </div>
            <div>
                <dt class="text-xs uppercase tracking-[0.14em] text-slate-400">Protocolo</dt>
                <dd class="font-semibold text-slate-900"><?= htmlspecialchars($ag['protocolo'] ?? 'Não informado') ?></dd>
            </div>
            <div>
                <dt class="text-xs uppercase tracking-[0.14em] text-slate-400">Convênio</dt>
                <dd class="font-semibold text-slate-900"><?= htmlspecialchars($ag['convenio']) ?></dd>
            </div>
        </dl>
    </section>

    <section class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
        <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-[0.16em] mb-3">
            <i class="fas fa-user text-indigo-500 mr-2"></i>Solicitante
        </h3>
        <dl class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm text-slate-600">
            <div>
                <dt class="text-xs uppercase tracking-[0.14em] text-slate-400">Nome</dt>
                <dd class="font-semibold text-slate-900"><?= htmlspecialchars($ag['nome_solicitante']) ?></dd>
            </div>
            <div>
                <dt class="text-xs uppercase tracking-[0.14em] text-slate-400">Email</dt>
                <dd class="font-semibold text-slate-900"><?= htmlspecialchars($ag['email_solicitante']) ?></dd>
            </div>
            <div>
                <dt class="text-xs uppercase tracking-[0.14em] text-slate-400">Telefone</dt>
                <dd class="font-semibold text-slate-900"><?= htmlspecialchars($ag['telefone_solicitante']) ?></dd>
            </div>
        </dl>
    </section>

    <section class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
        <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-[0.16em] mb-3">
            <i class="fas fa-notes-medical text-indigo-500 mr-2"></i>Procedimento
        </h3>
        <dl class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm text-slate-600">
            <div>
                <dt class="text-xs uppercase tracking-[0.14em] text-slate-400">Procedimento</dt>
                <dd class="font-semibold text-slate-900"><?= htmlspecialchars($ag['procedimento_nome']) ?></dd>
            </div>
            <div>
                <dt class="text-xs uppercase tracking-[0.14em] text-slate-400">Hospital</dt>
                <dd class="font-semibold text-slate-900"><?= htmlspecialchars($ag['hospital']) ?></dd>
            </div>
            <div>
                <dt class="text-xs uppercase tracking-[0.14em] text-slate-400">Situação</dt>
                <dd>
                    <span class="chip chip--accent" style="background: <?= $ag['situacao_cor'] ?>22; color: <?= $ag['situacao_cor'] ?>;">
                        <?= htmlspecialchars($ag['situacao_nome']) ?>
                    </span>
                </dd>
            </div>
        </dl>
    </section>

    <?php if ($ag['material_necessario']): ?>
    <section class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
        <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-[0.16em] mb-2">
            <i class="fas fa-box text-indigo-500 mr-2"></i>Material necessário
        </h3>
        <p class="text-sm text-slate-600 leading-relaxed"><?= nl2br(htmlspecialchars($ag['material_necessario'])) ?></p>
    </section>
    <?php endif; ?>

    <?php if ($ag['observacoes']): ?>
    <section class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
        <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-[0.16em] mb-2">
            <i class="fas fa-comment-dots text-indigo-500 mr-2"></i>Observações
        </h3>
        <p class="text-sm text-slate-600 leading-relaxed"><?= nl2br(htmlspecialchars($ag['observacoes'])) ?></p>
    </section>
    <?php endif; ?>

    <?php if ($ag['arquivo_anexo']): ?>
    <section class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
        <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-[0.16em] mb-2">
            <i class="fas fa-paperclip text-indigo-500 mr-2"></i>Arquivo anexo
        </h3>
        <a href="../../uploads/<?= htmlspecialchars($ag['arquivo_anexo']) ?>" target="_blank" class="btn-muted">
            <i class="fas fa-download text-xs"></i>
            Baixar arquivo
        </a>
    </section>
    <?php endif; ?>
</div>
