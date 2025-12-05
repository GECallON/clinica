<?php
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/models/Usuario.php';
require_once __DIR__ . '/../../src/models/Agendamento.php';
require_once __DIR__ . '/../../src/models/Procedimento.php';
require_once __DIR__ . '/../../src/models/Hospital.php';
require_once __DIR__ . '/../../src/models/Situacao.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$usuarioModel = new Usuario();
$procedimentoModel = new Procedimento();
$hospitalModel = new Hospital();
$situacaoModel = new Situacao();
$agendamentoModel = new Agendamento();

$medicos = $usuarioModel->getMedicos();
$procedimentos = $procedimentoModel->getAtivos();
$hospitais = $hospitalModel->getAtivos();
$situacoes = $situacaoModel->getAtivos();

$isEdit = isset($_GET['id']);
$agendamento = null;

if ($isEdit) {
    $agendamento = $agendamentoModel->getById($_GET['id']);
    if (!$agendamento) {
        setFlashMessage('error', 'Agendamento não encontrado');
        redirect('agendamentos-list.php');
    }
}

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
                                    <i class="fas fa-calendar-<?= $isEdit ? 'edit' : 'plus' ?>"></i>
                                </span>
                                <?= $isEdit ? 'Editar' : 'Novo' ?> agendamento
                            </h1>
                            <p class="page-subtitle mt-1"><?= $isEdit ? 'Atualize' : 'Preencha' ?> as informações do procedimento solicitado.</p>
                        </div>
                    </div>
                </section>

                <section class="glass p-6 max-w-4xl">
                    <form method="POST" action="agendamentos.php" enctype="multipart/form-data" class="space-y-6">
                        <input type="hidden" name="action" value="<?= $isEdit ? 'update' : 'create' ?>">
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id" value="<?= $agendamento['id'] ?>">
                        <?php endif; ?>

                        <div class="space-y-4">
                            <h2 class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Informações do agendamento</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="form-field">
                                    <label for="nome_solicitante">Nome do solicitante *</label>
                                    <input id="nome_solicitante" name="nome_solicitante" type="text" class="w-full"
                                           value="<?= $isEdit ? htmlspecialchars($agendamento['nome_solicitante']) : '' ?>" required>
                                </div>
                                <div class="form-field">
                                    <label for="telefone">Número do Paciente *</label>
                                    <input id="telefone" name="telefone" type="text" class="w-full telefone-mask" placeholder="(00) 00000-0000"
                                           value="<?= $isEdit ? htmlspecialchars($agendamento['telefone']) : '' ?>" required>
                                </div>
                                <div class="form-field">
                                    <label for="nome_paciente">Nome do paciente *</label>
                                    <input id="nome_paciente" name="nome_paciente" type="text" class="w-full"
                                           value="<?= $isEdit ? htmlspecialchars($agendamento['nome_paciente']) : '' ?>" required>
                                </div>
                                <div class="form-field">
                                    <label for="protocolo">Protocolo</label>
                                    <input id="protocolo" name="protocolo" type="text" class="w-full" placeholder="Digite o protocolo"
                                           value="<?= $isEdit ? htmlspecialchars($agendamento['protocolo']) : '' ?>">
                                </div>
                                <div class="form-field">
                                    <label for="convenio">Convênio *</label>
                                    <input id="convenio" name="convenio" type="text" class="w-full"
                                           value="<?= $isEdit ? htmlspecialchars($agendamento['convenio']) : '' ?>" required>
                                </div>
                                <div class="form-field">
                                    <label for="procedimento_id">Procedimento *</label>
                                    <select id="procedimento_id" name="procedimento_id" class="w-full" required>
                                        <option value="">Selecione...</option>
                                        <?php foreach ($procedimentos as $p): ?>
                                            <option value="<?= $p['id'] ?>" <?= $isEdit && $agendamento['procedimento_id'] == $p['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($p['nome']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-field">
                                    <label for="medico_id">Médico solicitante *</label>
                                    <select id="medico_id" name="medico_id" class="w-full" required>
                                        <option value="">Selecione...</option>
                                        <?php foreach ($medicos as $m): ?>
                                            <option value="<?= $m['id'] ?>" <?= $isEdit && $agendamento['medico_id'] == $m['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($m['nome']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-field">
                                    <label for="data_cirurgia">Data do procedimento *</label>
                                    <input id="data_cirurgia" name="data_cirurgia" type="date" class="w-full"
                                           value="<?= $isEdit ? $agendamento['data_cirurgia'] : '' ?>" required>
                                </div>
                                <div class="form-field">
                                    <label for="hora_cirurgia">Hora do procedimento *</label>
                                    <input id="hora_cirurgia" name="hora_cirurgia" type="time" class="w-full"
                                           value="<?= $isEdit ? $agendamento['hora_cirurgia'] : '' ?>" required>
                                </div>
                                <div class="form-field">
                                    <label for="hospital">Hospital *</label>
                                    <input id="hospital" name="hospital" type="text" class="w-full" list="hospitais-list" required autocomplete="off"
                                           value="<?= $isEdit ? htmlspecialchars($agendamento['hospital']) : '' ?>">
                                    <datalist id="hospitais-list">
                                        <?php foreach ($hospitais as $h): ?>
                                            <option value="<?= htmlspecialchars($h['nome']) ?>" data-email="<?= htmlspecialchars($h['email']) ?>">
                                        <?php endforeach; ?>
                                    </datalist>
                                    <p class="mt-2 text-xs text-slate-400">Selecione ou digite o nome do hospital</p>
                                </div>
                                <div class="form-field">
                                    <label for="email_hospital">Email do Hospital *</label>
                                    <input id="email_hospital" name="email_hospital" type="email" class="w-full" placeholder="email@hospital.com.br" required readonly
                                           value="<?= $isEdit ? htmlspecialchars($agendamento['email_hospital']) : '' ?>">
                                    <p class="mt-2 text-xs text-slate-400">Preenchido automaticamente</p>
                                </div>
                                <div class="form-field">
                                    <label for="fornecedor1">Fornecedor 1</label>
                                    <input id="fornecedor1" name="fornecedor1" type="text" class="w-full" placeholder="Nome do primeiro fornecedor"
                                           value="<?= $isEdit ? htmlspecialchars($agendamento['fornecedor1']) : '' ?>">
                                </div>
                                <div class="form-field">
                                    <label for="fornecedor2">Fornecedor 2</label>
                                    <input id="fornecedor2" name="fornecedor2" type="text" class="w-full" placeholder="Nome do segundo fornecedor"
                                           value="<?= $isEdit ? htmlspecialchars($agendamento['fornecedor2']) : '' ?>">
                                </div>
                                <div class="form-field">
                                    <label for="situacao_id">Situação *</label>
                                    <select id="situacao_id" name="situacao_id" class="w-full" required>
                                        <option value="">Selecione...</option>
                                        <?php foreach ($situacoes as $s): ?>
                                            <option value="<?= $s['id'] ?>" <?= $isEdit && $agendamento['situacao_id'] == $s['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($s['nome']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h2 class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Material, observações e anexo</h2>
                            <div class="grid grid-cols-1 gap-5">
                                <div class="form-field">
                                    <label for="material_necessario">Material</label>
                                    <textarea id="material_necessario" name="material_necessario" rows="3" class="w-full" placeholder="Descreva os materiais necessários"><?= $isEdit ? htmlspecialchars($agendamento['material_necessario']) : '' ?></textarea>
                                </div>
                                <div class="form-field">
                                    <label for="observacoes">Observação</label>
                                    <textarea id="observacoes" name="observacoes" rows="3" class="w-full" placeholder="Observações adicionais"><?= $isEdit ? htmlspecialchars($agendamento['observacoes']) : '' ?></textarea>
                                </div>
                                <div class="form-field">
                                    <label for="arquivo">Anexo</label>
                                    <?php if ($isEdit && $agendamento['arquivo_anexo']): ?>
                                        <p class="text-sm text-slate-600 mb-2">
                                            <i class="fas fa-file mr-2"></i>
                                            Arquivo atual: <a href="../../uploads/<?= htmlspecialchars($agendamento['arquivo_anexo']) ?>" target="_blank" class="text-indigo-600 underline"><?= htmlspecialchars($agendamento['arquivo_anexo']) ?></a>
                                        </p>
                                    <?php endif; ?>
                                    <input id="arquivo" name="arquivo" type="file" class="w-full">
                                    <p class="mt-2 text-xs text-slate-400">Formatos aceitos: PDF, JPG, PNG — até 10MB. <?= $isEdit ? 'Deixe vazio para manter o arquivo atual.' : '' ?></p>
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
                                <?= $isEdit ? 'Salvar Alterações' : 'Criar agendamento' ?>
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

        // Autocomplete de email do hospital
        const hospitalInput = document.getElementById('hospital');
        const emailHospitalInput = document.getElementById('email_hospital');
        const hospitaisData = <?= json_encode(array_map(function($h) {
            return ['nome' => $h['nome'], 'email' => $h['email']];
        }, $hospitais)) ?>;

        hospitalInput.addEventListener('input', function(e) {
            const hospitalNome = e.target.value;
            const hospital = hospitaisData.find(h => h.nome === hospitalNome);

            if (hospital) {
                emailHospitalInput.value = hospital.email;
                emailHospitalInput.classList.remove('bg-slate-100');
                emailHospitalInput.classList.add('bg-green-50');
            } else {
                emailHospitalInput.value = '';
                emailHospitalInput.classList.remove('bg-green-50');
                emailHospitalInput.classList.add('bg-slate-100');
            }
        });

        // Permitir edição manual do email
        emailHospitalInput.addEventListener('dblclick', function() {
            this.readOnly = false;
            this.classList.remove('bg-green-50', 'bg-slate-100');
            this.focus();
        });
    </script>
</body>
</html>
