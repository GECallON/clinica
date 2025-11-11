<?php
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/models/Agendamento.php';

if (!isLoggedIn() || !isAdmin()) {
    die('Acesso negado');
}

$id = $_GET['id'] ?? null;
if (!$id) {
    die('ID inválido');
}

$agendamentoModel = new Agendamento();
$ag = $agendamentoModel->getById($id);

if (!$ag) {
    die('Agendamento não encontrado');
}
?>

<div class="relative">
    <button onclick="closeModal()" class="absolute top-0 right-0 text-gray-400 hover:text-gray-600">
        <i class="fas fa-times text-2xl"></i>
    </button>

    <h2 class="text-2xl font-bold text-gray-800 mb-6">Detalhes do Agendamento</h2>

    <div class="space-y-4">
        <!-- Informações do Paciente -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="font-semibold text-lg text-gray-800 mb-3 flex items-center">
                <i class="fas fa-user-injured text-blue-600 mr-2"></i> Informações do Paciente
            </h3>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <p class="text-gray-600">Nome do Paciente:</p>
                    <p class="font-medium text-gray-800"><?= htmlspecialchars($ag['nome_paciente']) ?></p>
                </div>
                <div>
                    <p class="text-gray-600">Telefone do Paciente:</p>
                    <p class="font-medium text-gray-800"><?= htmlspecialchars($ag['telefone_paciente'] ?? 'Não informado') ?></p>
                </div>
                <div>
                    <p class="text-gray-600">Email do Paciente:</p>
                    <p class="font-medium text-gray-800"><?= htmlspecialchars($ag['email_paciente'] ?? 'Não informado') ?></p>
                </div>
                <div>
                    <p class="text-gray-600">Protocolo:</p>
                    <p class="font-medium text-gray-800"><?= htmlspecialchars($ag['protocolo'] ?? 'Não informado') ?></p>
                </div>
                <div>
                    <p class="text-gray-600">Convênio:</p>
                    <p class="font-medium text-gray-800"><?= htmlspecialchars($ag['convenio']) ?></p>
                </div>
            </div>
        </div>

        <!-- Informações do Solicitante -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="font-semibold text-lg text-gray-800 mb-3 flex items-center">
                <i class="fas fa-user text-purple-600 mr-2"></i> Solicitante
            </h3>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <p class="text-gray-600">Nome:</p>
                    <p class="font-medium text-gray-800"><?= htmlspecialchars($ag['nome_solicitante']) ?></p>
                </div>
                <div>
                    <p class="text-gray-600">Email:</p>
                    <p class="font-medium text-gray-800"><?= htmlspecialchars($ag['email_solicitante']) ?></p>
                </div>
                <div>
                    <p class="text-gray-600">Telefone:</p>
                    <p class="font-medium text-gray-800"><?= htmlspecialchars($ag['telefone_solicitante']) ?></p>
                </div>
            </div>
        </div>

        <!-- Informações da Cirurgia -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="font-semibold text-lg text-gray-800 mb-3 flex items-center">
                <i class="fas fa-procedures text-green-600 mr-2"></i> Cirurgia/Procedimento
            </h3>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <p class="text-gray-600">Procedimento:</p>
                    <p class="font-medium text-gray-800"><?= htmlspecialchars($ag['procedimento_nome']) ?></p>
                </div>
                <div>
                    <p class="text-gray-600">Médico:</p>
                    <p class="font-medium text-gray-800"><?= htmlspecialchars($ag['medico_nome']) ?></p>
                </div>
                <div>
                    <p class="text-gray-600">Hospital:</p>
                    <p class="font-medium text-gray-800"><?= htmlspecialchars($ag['hospital']) ?></p>
                </div>
                <div>
                    <p class="text-gray-600">Data:</p>
                    <p class="font-medium text-gray-800"><?= date('d/m/Y', strtotime($ag['data_cirurgia'])) ?></p>
                </div>
                <div>
                    <p class="text-gray-600">Hora:</p>
                    <p class="font-medium text-gray-800"><?= date('H:i', strtotime($ag['hora_cirurgia'])) ?></p>
                </div>
                <div>
                    <p class="text-gray-600">Situação:</p>
                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full text-white" style="background-color: <?= $ag['situacao_cor'] ?>">
                        <?= htmlspecialchars($ag['situacao_nome']) ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Material Necessário -->
        <?php if ($ag['material_necessario']): ?>
        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="font-semibold text-lg text-gray-800 mb-2 flex items-center">
                <i class="fas fa-box text-orange-600 mr-2"></i> Material Necessário
            </h3>
            <p class="text-sm text-gray-700"><?= nl2br(htmlspecialchars($ag['material_necessario'])) ?></p>
        </div>
        <?php endif; ?>

        <!-- Observações -->
        <?php if ($ag['observacoes']): ?>
        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="font-semibold text-lg text-gray-800 mb-2 flex items-center">
                <i class="fas fa-comment-medical text-red-600 mr-2"></i> Observações
            </h3>
            <p class="text-sm text-gray-700"><?= nl2br(htmlspecialchars($ag['observacoes'])) ?></p>
        </div>
        <?php endif; ?>

        <!-- Arquivo Anexo -->
        <?php if ($ag['arquivo_anexo']): ?>
        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="font-semibold text-lg text-gray-800 mb-2 flex items-center">
                <i class="fas fa-paperclip text-indigo-600 mr-2"></i> Arquivo Anexo
            </h3>
            <a href="../../uploads/<?= htmlspecialchars($ag['arquivo_anexo']) ?>" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm">
                <i class="fas fa-download mr-1"></i> Baixar arquivo
            </a>
        </div>
        <?php endif; ?>
    </div>

    <div class="mt-6 flex justify-end">
        <button onclick="closeModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg">
            Fechar
        </button>
    </div>
</div>
