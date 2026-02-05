<?php
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/models/Usuario.php';
require_once __DIR__ . '/../../src/models/EmailConfig.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$emailConfigModel = new EmailConfig();
$config = $emailConfigModel->getAtiva();
$logs = $emailConfigModel->getLogsRecentes(20);
$flash = getFlashMessage();
$version = time();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuração de Email - MedAgenda Pro</title>
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
                <?php if ($flash): ?>
                <div class="rounded-2xl border border-<?= $flash['type'] === 'success' ? 'emerald' : 'red' ?>-200 bg-<?= $flash['type'] === 'success' ? 'emerald' : 'red' ?>-50 px-5 py-4 text-sm text-<?= $flash['type'] === 'success' ? 'emerald' : 'red' ?>-700 shadow-sm">
                    <i class="fas fa-<?= $flash['type'] === 'success' ? 'circle-check' : 'triangle-exclamation' ?> mr-2"></i>
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
                <?php endif; ?>

                <div>
                    <h1 class="page-title flex items-center gap-2">
                        <span class="icon-chip bg-red-100 text-red-600">
                            <i class="fas fa-envelope"></i>
                        </span>
                        Configuração de Email
                    </h1>
                    <p class="page-subtitle mt-1">Configure o servidor SMTP para envio automático de emails.</p>
                </div>

                <section class="glass p-6 max-w-4xl">
                    <form method="POST" action="email-save.php" class="space-y-6">
                        <input type="hidden" name="action" value="<?= $config ? 'update' : 'create' ?>">
                        <?php if ($config): ?>
                            <input type="hidden" name="id" value="<?= $config['id'] ?>">
                        <?php endif; ?>

                        <div class="space-y-4">
                            <h2 class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Servidor SMTP</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="form-field">
                                    <label for="smtp_host">Host SMTP *</label>
                                    <input id="smtp_host" name="smtp_host" type="text" class="w-full"
                                           value="<?= $config ? htmlspecialchars($config['smtp_host']) : 'smtp.gmail.com' ?>"
                                           placeholder="smtp.gmail.com" required>
                                </div>

                                <div class="form-field">
                                    <label for="smtp_port">Porta *</label>
                                    <input id="smtp_port" name="smtp_port" type="number" class="w-full"
                                           value="<?= $config ? $config['smtp_port'] : '587' ?>"
                                           placeholder="587" required>
                                    <p class="mt-2 text-xs text-slate-400">587 para TLS, 465 para SSL</p>
                                </div>

                                <div class="form-field">
                                    <label for="smtp_secure">Criptografia *</label>
                                    <select id="smtp_secure" name="smtp_secure" class="w-full" required>
                                        <option value="tls" <?= $config && $config['smtp_secure'] === 'tls' ? 'selected' : '' ?>>TLS (recomendado)</option>
                                        <option value="ssl" <?= $config && $config['smtp_secure'] === 'ssl' ? 'selected' : '' ?>>SSL</option>
                                    </select>
                                </div>

                                <div class="form-field">
                                    <label for="smtp_user">Usuário/Email SMTP *</label>
                                    <input id="smtp_user" name="smtp_user" type="email" class="w-full"
                                           value="<?= $config ? htmlspecialchars($config['smtp_user']) : '' ?>"
                                           placeholder="seu-email@gmail.com" required>
                                </div>

                                <div class="form-field md:col-span-2">
                                    <label for="smtp_password">Senha/Token do Email *</label>
                                    <input id="smtp_password" name="smtp_password" type="password" class="w-full"
                                           value="<?= $config ? htmlspecialchars($config['smtp_password']) : '' ?>"
                                           placeholder="Senha ou App Password" required>
                                    <p class="mt-2 text-xs text-slate-400">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Gmail: use "Senhas de app" em vez da senha normal
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h2 class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Remetente</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="form-field">
                                    <label for="email_remetente">Email Remetente *</label>
                                    <input id="email_remetente" name="email_remetente" type="email" class="w-full"
                                           value="<?= $config ? htmlspecialchars($config['email_remetente']) : '' ?>"
                                           placeholder="noreply@clinica.com.br" required>
                                </div>

                                <div class="form-field">
                                    <label for="nome_remetente">Nome Remetente *</label>
                                    <input id="nome_remetente" name="nome_remetente" type="text" class="w-full"
                                           value="<?= $config ? htmlspecialchars($config['nome_remetente']) : 'MedAgenda Pro' ?>"
                                           placeholder="MedAgenda Pro" required>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h2 class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Opções</h2>
                            <div class="flex items-center gap-3">
                                <input id="enviar_automatico" name="enviar_automatico" type="checkbox"
                                       <?= (!$config || $config['enviar_automatico']) ? 'checked' : '' ?>>
                                <label for="enviar_automatico" class="text-sm font-medium text-slate-700">
                                    Enviar email automaticamente ao criar/editar agendamento
                                </label>
                            </div>
                            <div class="flex items-center gap-3">
                                <input id="ativo" name="ativo" type="checkbox"
                                       <?= (!$config || $config['ativo']) ? 'checked' : '' ?>>
                                <label for="ativo" class="text-sm font-medium text-slate-700">
                                    Ativar envio de emails
                                </label>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3">
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-save"></i>
                                Salvar Configuração
                            </button>
                            <button type="button" onclick="testarEmail()" class="btn-muted">
                                <i class="fas fa-paper-plane"></i>
                                Enviar Email de Teste
                            </button>
                        </div>
                    </form>
                </section>

                <!-- Log de Emails -->
                <section class="glass p-6">
                    <h2 class="text-lg font-semibold text-slate-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-history text-slate-500"></i>
                        Últimos Emails Enviados
                    </h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="datatable__head">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Paciente</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Destinatários</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Data</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php if (count($logs) > 0): ?>
                                    <?php foreach ($logs as $log): ?>
                                    <tr class="table-row">
                                        <td class="px-4 py-3 text-sm"><?= htmlspecialchars($log['nome_paciente'] ?? 'N/A') ?></td>
                                        <td class="px-4 py-3 text-sm text-slate-600">
                                            <?php
                                            $dests = json_decode($log['destinatarios'], true);
                                            if ($dests) {
                                                echo count($dests) . ' email(s)';
                                            }
                                            ?>
                                        </td>
                                        <td class="px-4 py-3">
                                            <?php if ($log['status'] === 'enviado'): ?>
                                                <span class="chip bg-green-100 text-green-700">
                                                    <i class="fas fa-check mr-1"></i>Enviado
                                                </span>
                                            <?php else: ?>
                                                <span class="chip bg-red-100 text-red-700">
                                                    <i class="fas fa-times mr-1"></i>Erro
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-slate-500"><?= date('d/m/Y H:i', strtotime($log['enviado_em'])) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-slate-500">
                                            <i class="fas fa-inbox text-2xl mb-2 opacity-30"></i>
                                            <p>Nenhum email enviado ainda</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Informações -->
                <div class="glass p-6">
                    <h2 class="text-lg font-semibold text-slate-900 mb-3 flex items-center gap-2">
                        <i class="fas fa-info-circle text-blue-500"></i>
                        Informações Importantes
                    </h2>
                    <div class="space-y-3 text-sm text-slate-600">
                        <p><strong>📧 Destinatários:</strong> O email será enviado para:</p>
                        <ul class="list-disc ml-6 space-y-1">
                            <li>Email do Hospital (configurado no agendamento)</li>
                            <li>Fornecedor 1 (se cadastrar tabela de fornecedores com email)</li>
                            <li>Fornecedor 2 (se cadastrar tabela de fornecedores com email)</li>
                        </ul>

                        <p class="mt-4"><strong>📎 Anexo:</strong> O arquivo anexado no agendamento será incluído no email automaticamente.</p>

                        <p class="mt-4"><strong>🔒 Gmail:</strong> Para usar Gmail, você precisa:</p>
                        <ol class="list-decimal ml-6 space-y-1">
                            <li>Ativar verificação em 2 etapas</li>
                            <li>Criar uma "Senha de app" em: <a href="https://myaccount.google.com/apppasswords" target="_blank" class="text-indigo-600 underline">myaccount.google.com/apppasswords</a></li>
                            <li>Usar a senha de app no campo "Senha/Token"</li>
                        </ol>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        function testarEmail() {
            if (confirm('Deseja enviar um email de teste para o email configurado?')) {
                alert('Funcionalidade de teste será implementada em breve!');
            }
        }
    </script>
</body>
</html>
