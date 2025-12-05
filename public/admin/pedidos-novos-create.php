<?php
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/models/Usuario.php';
require_once __DIR__ . '/../../src/models/PedidoNovo.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$usuarioModel = new Usuario();
$pedidoModel = new PedidoNovo();
require_once __DIR__ . '/../../src/models/Situacao.php';
$situacaoModel = new Situacao();

$medicos = $usuarioModel->getMedicos();
$situacoes = $situacaoModel->getAtivos();

$isEdit = isset($_GET['id']);
$pedido = null;

if ($isEdit) {
    $pedido = $pedidoModel->getById($_GET['id']);
    if (!$pedido) {
        setFlashMessage('error', 'Pedido não encontrado');
        redirect('pedidos-novos-list.php');
    }
}

$version = time();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Pedido - MedAgenda Pro</title>
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
                        <a href="pedidos-novos-list.php" class="btn-muted btn-primary--icon">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <div>
                            <h1 class="page-title flex items-center gap-2">
                                <span class="icon-chip bg-blue-100 text-blue-600">
                                    <i class="fas fa-<?= $isEdit ? 'edit' : 'file-medical' ?>"></i>
                                </span>
                                <?= $isEdit ? 'Editar' : 'Novo' ?> Pedido
                            </h1>
                            <p class="page-subtitle mt-1"><?= $isEdit ? 'Atualize' : 'Preencha' ?> as informações do pedido.</p>
                        </div>
                    </div>
                </section>

                <section class="glass p-6 max-w-4xl">
                    <form method="POST" action="pedidos-novos.php" class="space-y-6">
                        <input type="hidden" name="action" value="<?= $isEdit ? 'update' : 'create' ?>">
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id" value="<?= $pedido['id'] ?>">
                        <?php endif; ?>

                        <div class="space-y-4">
                            <h2 class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Informações do pedido</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="form-field">
                                    <label for="nome_paciente">Nome do paciente *</label>
                                    <input id="nome_paciente" name="nome_paciente" type="text" class="w-full"
                                           value="<?= $isEdit ? htmlspecialchars($pedido['nome_paciente']) : '' ?>" required>
                                </div>
                                <div class="form-field">
                                    <label for="telefone">Número do Paciente *</label>
                                    <input id="telefone" name="telefone" type="text" class="w-full telefone-mask" placeholder="(00) 00000-0000"
                                           value="<?= $isEdit ? htmlspecialchars($pedido['telefone']) : '' ?>" required>
                                </div>
                                <div class="form-field">
                                    <label for="medico_id">Médico *</label>
                                    <select id="medico_id" name="medico_id" class="w-full" required>
                                        <option value="">Selecione o médico...</option>
                                        <?php foreach ($medicos as $m): ?>
                                            <option value="<?= $m['id'] ?>" <?= $isEdit && $pedido['medico_id'] == $m['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($m['nome']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-field">
                                    <label for="convenio">Convênio *</label>
                                    <input id="convenio" name="convenio" type="text" class="w-full"
                                           value="<?= $isEdit ? htmlspecialchars($pedido['convenio']) : '' ?>" required>
                                </div>
                                <div class="form-field">
                                    <label for="fornecedor">Fornecedor</label>
                                    <input id="fornecedor" name="fornecedor" type="text" class="w-full" placeholder="Nome do fornecedor"
                                           value="<?= $isEdit ? htmlspecialchars($pedido['fornecedor']) : '' ?>">
                                </div>
                                <div class="form-field md:col-span-2">
                                    <label for="observacao">Observação</label>
                                    <textarea id="observacao" name="observacao" rows="3" class="w-full" placeholder="Observações adicionais"><?= $isEdit ? htmlspecialchars($pedido['observacao']) : '' ?></textarea>
                                </div>
                                <?php if ($isEdit): ?>
                                <div class="form-field">
                                    <label for="situacao_id">Situação *</label>
                                    <select id="situacao_id" name="situacao_id" class="w-full" required>
                                        <option value="">Selecione...</option>
                                        <?php foreach ($situacoes as $s): ?>
                                            <option value="<?= $s['id'] ?>" <?= $pedido['situacao_id'] == $s['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($s['nome']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Status padrão: Aguardando Autorização (somente ao criar) -->
                            <?php if (!$isEdit): ?>
                            <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg">
                                <p class="text-sm text-amber-800">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    <strong>Status inicial:</strong> Aguardando Autorização (você pode alterar depois na lista de pedidos)
                                </p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="flex justify-end gap-3">
                            <a href="pedidos-novos-list.php" class="btn-muted">
                                <i class="fas fa-xmark text-xs"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-check"></i>
                                <?= $isEdit ? 'Salvar Alterações' : 'Criar pedido' ?>
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
    </script>
</body>
</html>
