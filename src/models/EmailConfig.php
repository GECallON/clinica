<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailConfig {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAtiva() {
        $stmt = $this->db->query("
            SELECT * FROM email_config
            WHERE ativo = 1
            LIMIT 1
        ");
        return $stmt->fetch();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM email_config WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO email_config (
                smtp_host, smtp_port, smtp_user, smtp_password, smtp_secure,
                email_remetente, nome_remetente, enviar_automatico, ativo
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            $data['smtp_host'],
            $data['smtp_port'],
            $data['smtp_user'],
            $data['smtp_password'],
            $data['smtp_secure'],
            $data['email_remetente'],
            $data['nome_remetente'],
            isset($data['enviar_automatico']) ? 1 : 0,
            isset($data['ativo']) ? 1 : 0
        ]);
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE email_config SET
                smtp_host = ?,
                smtp_port = ?,
                smtp_user = ?,
                smtp_password = ?,
                smtp_secure = ?,
                email_remetente = ?,
                nome_remetente = ?,
                enviar_automatico = ?,
                ativo = ?
            WHERE id = ?
        ");

        return $stmt->execute([
            $data['smtp_host'],
            $data['smtp_port'],
            $data['smtp_user'],
            $data['smtp_password'],
            $data['smtp_secure'],
            $data['email_remetente'],
            $data['nome_remetente'],
            isset($data['enviar_automatico']) ? 1 : 0,
            isset($data['ativo']) ? 1 : 0,
            $id
        ]);
    }

    public function enviarEmailAgendamento($agendamento_id) {
        $config = $this->getAtiva();
        if (!$config) {
            return ['success' => false, 'message' => 'Nenhuma configuração de email ativa'];
        }

        // Buscar agendamento
        require_once __DIR__ . '/Agendamento.php';
        $agendamentoModel = new Agendamento();
        $agendamento = $agendamentoModel->getById($agendamento_id);

        if (!$agendamento) {
            return ['success' => false, 'message' => 'Agendamento não encontrado'];
        }

        // Preparar destinatários
        $destinatarios = [];
        if ($agendamento['email_hospital']) {
            $destinatarios[] = ['email' => $agendamento['email_hospital'], 'nome' => $agendamento['hospital']];
        }

        // Adicionar emails de fornecedores (se existirem - você pode criar tabela de fornecedores depois)
        // Por enquanto, vou deixar preparado para aceitar emails diretos

        if (empty($destinatarios)) {
            return ['success' => false, 'message' => 'Nenhum destinatário configurado'];
        }

        try {
            $mail = new PHPMailer(true);

            // Configurar SMTP
            $mail->isSMTP();
            $mail->Host = $config['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['smtp_user'];
            $mail->Password = $config['smtp_password'];
            $mail->SMTPSecure = $config['smtp_secure'];
            $mail->Port = $config['smtp_port'];
            $mail->CharSet = 'UTF-8';

            // Remetente
            $mail->setFrom($config['email_remetente'], $config['nome_remetente']);

            // Destinatários
            foreach ($destinatarios as $dest) {
                $mail->addAddress($dest['email'], $dest['nome']);
            }

            // Assunto e corpo
            $assunto = "Agendamento de Cirurgia - " . $agendamento['nome_paciente'];
            $mail->Subject = $assunto;
            $mail->isHTML(true);

            // Corpo do email
            $corpo = $this->gerarCorpoEmail($agendamento);
            $mail->Body = $corpo;
            $mail->AltBody = strip_tags($corpo);

            // Anexo (se houver)
            if ($agendamento['arquivo_anexo']) {
                $arquivo_path = UPLOAD_DIR . $agendamento['arquivo_anexo'];
                if (file_exists($arquivo_path)) {
                    $mail->addAttachment($arquivo_path, $agendamento['arquivo_anexo']);
                }
            }

            // Enviar
            $mail->send();

            // Registrar log
            $this->registrarLog($agendamento_id, $destinatarios, $assunto, 'enviado', null);

            return ['success' => true, 'message' => 'Email enviado com sucesso!'];

        } catch (Exception $e) {
            // Registrar erro
            $this->registrarLog($agendamento_id, $destinatarios, $assunto ?? 'Erro', 'erro', $mail->ErrorInfo);

            return ['success' => false, 'message' => 'Erro ao enviar email: ' . $mail->ErrorInfo];
        }
    }

    private function gerarCorpoEmail($agendamento) {
        $data_formatada = date('d/m/Y', strtotime($agendamento['data_cirurgia']));
        $hora_formatada = date('H:i', strtotime($agendamento['hora_cirurgia']));

        return "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #4f46e5; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; }
        .info-row { margin: 15px 0; padding: 10px; background: white; border-left: 4px solid #4f46e5; }
        .label { font-weight: bold; color: #4f46e5; display: inline-block; min-width: 180px; }
        .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>🏥 Agendamento de Cirurgia</h1>
        </div>
        <div class='content'>
            <h2 style='color: #4f46e5; margin-top: 0;'>Dados do Agendamento</h2>

            <div class='info-row'>
                <span class='label'>📋 Protocolo:</span>
                <span>" . htmlspecialchars($agendamento['protocolo'] ?? 'Não informado') . "</span>
            </div>

            <div class='info-row'>
                <span class='label'>👤 Paciente:</span>
                <span>" . htmlspecialchars($agendamento['nome_paciente']) . "</span>
            </div>

            <div class='info-row'>
                <span class='label'>📞 Telefone:</span>
                <span>" . htmlspecialchars($agendamento['telefone']) . "</span>
            </div>

            <div class='info-row'>
                <span class='label'>💳 Convênio:</span>
                <span>" . htmlspecialchars($agendamento['convenio']) . "</span>
            </div>

            <div class='info-row'>
                <span class='label'>⚕️ Procedimento:</span>
                <span>" . htmlspecialchars($agendamento['procedimento_nome']) . "</span>
            </div>

            <div class='info-row'>
                <span class='label'>👨‍⚕️ Médico Solicitante:</span>
                <span>" . htmlspecialchars($agendamento['medico_nome']) . "</span>
            </div>

            <div class='info-row'>
                <span class='label'>📅 Data:</span>
                <span>" . $data_formatada . "</span>
            </div>

            <div class='info-row'>
                <span class='label'>🕐 Hora:</span>
                <span>" . $hora_formatada . "</span>
            </div>

            <div class='info-row'>
                <span class='label'>🏥 Hospital:</span>
                <span>" . htmlspecialchars($agendamento['hospital']) . "</span>
            </div>

            <div class='info-row'>
                <span class='label'>📧 Email Hospital:</span>
                <span>" . htmlspecialchars($agendamento['email_hospital']) . "</span>
            </div>

            <div class='info-row'>
                <span class='label'>🏢 Fornecedor 1:</span>
                <span>" . htmlspecialchars($agendamento['fornecedor1'] ?? 'Não informado') . "</span>
            </div>

            <div class='info-row'>
                <span class='label'>🏢 Fornecedor 2:</span>
                <span>" . htmlspecialchars($agendamento['fornecedor2'] ?? 'Não informado') . "</span>
            </div>

            <div class='info-row'>
                <span class='label'>🚦 Situação:</span>
                <span>" . htmlspecialchars($agendamento['situacao_nome']) . "</span>
            </div>

            " . ($agendamento['material_necessario'] ? "
            <div class='info-row'>
                <span class='label'>🛠️ Material Necessário:</span><br>
                <span style='margin-left: 20px;'>" . nl2br(htmlspecialchars($agendamento['material_necessario'])) . "</span>
            </div>
            " : "") . "

            " . ($agendamento['observacoes'] ? "
            <div class='info-row'>
                <span class='label'>📝 Observações:</span><br>
                <span style='margin-left: 20px;'>" . nl2br(htmlspecialchars($agendamento['observacoes'])) . "</span>
            </div>
            " : "") . "

            <hr style='margin: 30px 0; border: none; border-top: 1px solid #e5e7eb;'>

            <div style='text-align: center;'>
                <p><strong>Solicitado por:</strong> " . htmlspecialchars($agendamento['nome_solicitante']) . "</p>
            </div>
        </div>
        <div class='footer'>
            <p>Este é um email automático do Sistema MedAgenda Pro</p>
            <p>Por favor, não responda este email</p>
        </div>
    </div>
</body>
</html>
        ";
    }

    private function registrarLog($agendamento_id, $destinatarios, $assunto, $status, $erro = null) {
        $stmt = $this->db->prepare("
            INSERT INTO email_logs (agendamento_id, destinatarios, assunto, status, erro_mensagem)
            VALUES (?, ?, ?, ?, ?)
        ");

        $destinatarios_json = json_encode($destinatarios);

        $stmt->execute([
            $agendamento_id,
            $destinatarios_json,
            $assunto,
            $status,
            $erro
        ]);
    }

    public function getLogsRecentes($limit = 50) {
        $stmt = $this->db->prepare("
            SELECT l.*, a.nome_paciente
            FROM email_logs l
            LEFT JOIN agendamentos a ON l.agendamento_id = a.id
            ORDER BY l.enviado_em DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
