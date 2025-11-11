<?php
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/models/Usuario.php';
require_once __DIR__ . '/../../src/models/Agendamento.php';
require_once __DIR__ . '/../../src/models/Procedimento.php';
require_once __DIR__ . '/../../src/models/Situacao.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$usuarioModel = new Usuario();
$procedimentoModel = new Procedimento();
$situacaoModel = new Situacao();

$medicos = $usuarioModel->getMedicos();
$procedimentos = $procedimentoModel->getAtivos();
$situacoes = $situacaoModel->getAtivos();
$version = time();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Agendamento - MedAgenda Pro</title>
    <script src="https://cdn.tailwindcss.com?v=<?= $version ?>"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
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
                                    <i class="fas fa-calendar-plus"></i>
                                </span>
                                Novo agendamento
                            </h1>
                            <p class="page-subtitle mt-1">Preencha as informações do procedimento solicitado.</p>
                        </div>
                    </div>
                </section>

                <section class="glass p-6 max-w-4xl">
                    <form method="POST" action="agendamentos.php" enctype="multipart/form-data" class="space-y-6">
                        <input type="hidden" name="action" value="create">

                        <div class="space-y-4">
                            <h2 class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Solicitante</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="form-field md:col-span-2">
                                    <label for="nome_solicitante">Nome solicitante *</label>
                                    <input id="nome_solicitante" name="nome_solicitante" type="text" class="w-full" required>
                                </div>
                                <div class="form-field">
                                    <label for="email_solicitante">Email solicitante *</label>
                                    <input id="email_solicitante" name="email_solicitante" type="email" class="w-full" required>
                                </div>
                                <div class="form-field">
                                    <label for="telefone_solicitante">Telefone solicitante *</label>
                                    <input id="telefone_solicitante" name="telefone_solicitante" type="text" class="w-full" required>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h2 class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Paciente</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="form-field md:col-span-2">
                                    <label for="nome_paciente">Nome do paciente *</label>
                                    <input id="nome_paciente" name="nome_paciente" type="text" class="w-full" required>
                                </div>
                                <div class="form-field">
                                    <label for="telefone_paciente">Telefone do paciente</label>
                                    <input id="telefone_paciente" name="telefone_paciente" type="text" class="w-full telefone-mask" placeholder="(00) 00000-0000">
                                </div>
                                <div class="form-field">
                                    <label for="email_paciente">Email do paciente</label>
                                    <input id="email_paciente" name="email_paciente" type="email" class="w-full" placeholder="paciente@email.com">
                                </div>
                                <div class="form-field">
                                    <label for="protocolo">Protocolo</label>
                                    <input id="protocolo" name="protocolo" type="text" class="w-full" placeholder="Cole o protocolo existente aqui">
                                </div>
                                <div class="form-field">
                                    <label for="convenio">Convênio *</label>
                                    <input id="convenio" name="convenio" type="text" class="w-full" required>
                                </div>
                                <div class="form-field">
                                    <label for="procedimento_id">Procedimento *</label>
                                    <select id="procedimento_id" name="procedimento_id" class="w-full" required>
                                        <option value="">Selecione...</option>
                                        <?php foreach ($procedimentos as $p): ?>
                                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nome']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-field">
                                    <label for="medico_id">Médico responsável *</label>
                                    <select id="medico_id" name="medico_id" class="w-full" required>
                                        <option value="">Selecione...</option>
                                        <?php foreach ($medicos as $m): ?>
                                            <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nome']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-field">
                                    <label for="data_cirurgia">Data da cirurgia *</label>
                                    <input id="data_cirurgia" name="data_cirurgia" type="date" class="w-full" required>
                                </div>
                                <div class="form-field">
                                    <label for="hora_cirurgia">Hora da cirurgia *</label>
                                    <input id="hora_cirurgia" name="hora_cirurgia" type="time" class="w-full" required>
                                </div>
                                <div class="form-field">
                                    <label for="hospital">Hospital *</label>
                                    <input id="hospital" name="hospital" type="text" class="w-full" required>
                                </div>
                                <div class="form-field">
                                    <label for="situacao_id">Situação *</label>
                                    <select id="situacao_id" name="situacao_id" class="w-full" required>
                                        <option value="">Selecione...</option>
                                        <?php foreach ($situacoes as $s): ?>
                                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nome']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h2 class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Materiais & observações</h2>
                            <div class="grid grid-cols-1 gap-5">
                                <div class="form-field">
                                    <label for="material_necessario">Material necessário</label>
                                    <textarea id="material_necessario" name="material_necessario" rows="3" class="w-full"></textarea>
                                </div>
                                <div class="form-field">
                                    <label for="observacoes">Observações adicionais</label>
                                    <textarea id="observacoes" name="observacoes" rows="3" class="w-full"></textarea>
                                </div>
                                <div class="form-field">
                                    <label for="arquivo">Anexar arquivo</label>
                                    <input id="arquivo" name="arquivo" type="file" class="w-full">
                                    <p class="mt-2 text-xs text-slate-400">Formatos aceitos: PDF, JPG, PNG — até 10MB.</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3">
                            <a href="agendamentos-list.php" class="btn-muted">
                                <i class="fas fa-xmark text-xs"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-check"></i>
                                Criar agendamento
                            </button>
                        </div>
                    </form>
                </section>
            </main>
        </div>
    </div>

    <script>
        // Máscara para telefone
        document.querySelectorAll('.telefone-mask').forEach(input => {
            input.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 11) value = value.slice(0, 11);

                if (value.length > 6) {
                    value = `(${value.slice(0,2)}) ${value.slice(2,7)}-${value.slice(7)}`;
                } else if (value.length > 2) {
                    value = `(${value.slice(0,2)}) ${value.slice(2)}`;
                } else if (value.length > 0) {
                    value = `(${value}`;
                }

                e.target.value = value;
            });
        });

        // Máscara para telefone do solicitante também
        document.getElementById('telefone_solicitante').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) value = value.slice(0, 11);

            if (value.length > 6) {
                value = `(${value.slice(0,2)}) ${value.slice(2,7)}-${value.slice(7)}`;
            } else if (value.length > 2) {
                value = `(${value.slice(0,2)}) ${value.slice(2)}`;
            } else if (value.length > 0) {
                value = `(${value}`;
            }

            e.target.value = value;
        });
    </script>
</body>
</html>
