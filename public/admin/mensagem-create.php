<?php
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/models/Usuario.php';
require_once __DIR__ . '/../../src/models/MensagemConfig.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$mensagemModel = new MensagemConfig();
$isEdit = isset($_GET['id']);
$mensagem = null;

if ($isEdit) {
    $mensagem = $mensagemModel->getById($_GET['id']);
    if (!$mensagem) {
        setFlashMessage('error', 'Configuração não encontrada');
        redirect('mensagens-list.php');
    }
}

$version = time();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Editar' : 'Nova' ?> Configuração - MedAgenda Pro</title>
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
                        <a href="mensagens-list.php" class="btn-muted btn-primary--icon">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <div>
                            <h1 class="page-title flex items-center gap-2">
                                <span class="icon-chip bg-green-100 text-green-600">
                                    <i class="fab fa-whatsapp"></i>
                                </span>
                                <?= $isEdit ? 'Editar' : 'Nova' ?> Configuração de Mensagem
                            </h1>
                            <p class="page-subtitle mt-1">Configure a mensagem automática enviada via WhatsApp.</p>
                        </div>
                    </div>
                </section>

                <section class="glass p-6 max-w-4xl">
                    <form method="POST" action="mensagens.php" class="space-y-6">
                        <input type="hidden" name="action" value="<?= $isEdit ? 'update' : 'create' ?>">
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id" value="<?= $mensagem['id'] ?>">
                        <?php endif; ?>

                        <div class="space-y-4">
                            <h2 class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Configurações Básicas</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="form-field md:col-span-2">
                                    <label for="nome">Nome da Configuração *</label>
                                    <input id="nome" name="nome" type="text" class="w-full"
                                           value="<?= $isEdit ? htmlspecialchars($mensagem['nome']) : '' ?>"
                                           placeholder="Ex: Confirmação Padrão" required>
                                </div>

                                <div class="form-field">
                                    <label for="dominio">Domínio da API *</label>
                                    <input id="dominio" name="dominio" type="text" class="w-full"
                                           value="<?= $isEdit ? htmlspecialchars($mensagem['dominio']) : 'dev.callon.com.br' ?>"
                                           placeholder="dev.callon.com.br" required>
                                    <p class="mt-2 text-xs text-slate-400">Sem http:// ou https://</p>
                                </div>

                                <div class="form-field">
                                    <label for="canal">Canal *</label>
                                    <input id="canal" name="canal" type="number" class="w-full"
                                           value="<?= $isEdit ? $mensagem['canal'] : '1' ?>"
                                           placeholder="1" min="1" required>
                                    <p class="mt-2 text-xs text-slate-400">ID do canal na API</p>
                                </div>

                                <div class="form-field md:col-span-2">
                                    <div class="flex items-center gap-2 mb-2">
                                        <input id="ativo" name="ativo" type="checkbox" class="rounded"
                                               <?= ($isEdit && $mensagem['ativo']) || !$isEdit ? 'checked' : '' ?>>
                                        <label for="ativo" class="text-sm font-medium text-slate-700">Ativar esta configuração</label>
                                    </div>
                                    <p class="text-xs text-slate-400">Apenas uma configuração pode estar ativa por vez</p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h2 class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Mensagem</h2>
                            <div class="form-field">
                                <label for="texto_mensagem">Texto da Mensagem *</label>
                                <textarea id="texto_mensagem" name="texto_mensagem" rows="6" class="w-full"
                                          placeholder="Digite a mensagem aqui..." required><?= $isEdit ? htmlspecialchars($mensagem['texto_mensagem']) : 'Olá! Seu agendamento foi confirmado para o dia {data} às {hora}. Hospital: {hospital}. Em caso de dúvidas, entre em contato.' ?></textarea>
                                <p class="mt-2 text-xs text-slate-400">Use as variáveis abaixo para personalizar</p>
                            </div>

                            <!-- Variáveis disponíveis -->
                            <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                <p class="text-sm font-semibold text-blue-900 mb-2">
                                    <i class="fas fa-lightbulb mr-2"></i>
                                    Variáveis disponíveis:
                                </p>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-xs">
                                    <code class="bg-white px-2 py-1 rounded text-indigo-600">{data}</code>
                                    <code class="bg-white px-2 py-1 rounded text-indigo-600">{hora}</code>
                                    <code class="bg-white px-2 py-1 rounded text-indigo-600">{hospital}</code>
                                    <code class="bg-white px-2 py-1 rounded text-indigo-600">{paciente}</code>
                                    <code class="bg-white px-2 py-1 rounded text-indigo-600">{medico}</code>
                                    <code class="bg-white px-2 py-1 rounded text-indigo-600">{procedimento}</code>
                                </div>
                            </div>
                        </div>

                        <!-- Exemplo de URL gerada -->
                        <div class="p-4 bg-slate-50 border border-slate-200 rounded-lg">
                            <p class="text-sm font-semibold text-slate-700 mb-2">
                                <i class="fas fa-link mr-2"></i>
                                Exemplo de URL gerada:
                            </p>
                            <code class="text-xs text-slate-600 break-all">
                                http://<span id="preview-dominio"><?= $isEdit ? htmlspecialchars($mensagem['dominio']) : 'dev.callon.com.br' ?></span>/api/mvtobe/<span id="preview-canal"><?= $isEdit ? $mensagem['canal'] : '1' ?></span>?numero=5527995289878&texto=Olá...&mensage_ativa=1
                            </code>
                        </div>

                        <div class="flex justify-end gap-3">
                            <a href="mensagens-list.php" class="btn-muted">
                                <i class="fas fa-xmark text-xs"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-check"></i>
                                <?= $isEdit ? 'Salvar Alterações' : 'Criar Configuração' ?>
                            </button>
                        </div>
                    </form>
                </section>
            </main>
        </div>
    </div>

    <script>
        // Preview dinâmico da URL
        const dominioInput = document.getElementById('dominio');
        const canalInput = document.getElementById('canal');
        const previewDominio = document.getElementById('preview-dominio');
        const previewCanal = document.getElementById('preview-canal');

        dominioInput.addEventListener('input', (e) => {
            previewDominio.textContent = e.target.value || 'dev.callon.com.br';
        });

        canalInput.addEventListener('input', (e) => {
            previewCanal.textContent = e.target.value || '1';
        });
    </script>
</body>
</html>
