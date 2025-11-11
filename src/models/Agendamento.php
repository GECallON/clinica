<?php
require_once __DIR__ . '/../config.php';

class Agendamento {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll($medico_id = null) {
        $sql = "
            SELECT a.*,
                   u.nome as medico_nome,
                   p.nome as procedimento_nome,
                   s.nome as situacao_nome,
                   s.cor as situacao_cor
            FROM agendamentos a
            JOIN usuarios u ON a.medico_id = u.id
            JOIN procedimentos p ON a.procedimento_id = p.id
            JOIN situacoes s ON a.situacao_id = s.id
        ";

        if ($medico_id) {
            $sql .= " WHERE a.medico_id = ? ORDER BY a.data_cirurgia DESC, a.hora_cirurgia DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$medico_id]);
        } else {
            $sql .= " ORDER BY a.data_cirurgia DESC, a.hora_cirurgia DESC";
            $stmt = $this->db->query($sql);
        }

        return $stmt->fetchAll();
    }

    public function getByMedicoAndDate($medico_id, $start_date, $end_date) {
        $stmt = $this->db->prepare("
            SELECT a.*,
                   u.nome as medico_nome,
                   p.nome as procedimento_nome,
                   s.nome as situacao_nome,
                   s.cor as situacao_cor
            FROM agendamentos a
            JOIN usuarios u ON a.medico_id = u.id
            JOIN procedimentos p ON a.procedimento_id = p.id
            JOIN situacoes s ON a.situacao_id = s.id
            WHERE a.medico_id = ? AND a.data_cirurgia BETWEEN ? AND ?
            ORDER BY a.data_cirurgia, a.hora_cirurgia
        ");
        $stmt->execute([$medico_id, $start_date, $end_date]);
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT a.*,
                   u.nome as medico_nome,
                   p.nome as procedimento_nome,
                   s.nome as situacao_nome,
                   s.cor as situacao_cor
            FROM agendamentos a
            JOIN usuarios u ON a.medico_id = u.id
            JOIN procedimentos p ON a.procedimento_id = p.id
            JOIN situacoes s ON a.situacao_id = s.id
            WHERE a.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO agendamentos (
                nome_solicitante, email_solicitante, telefone_solicitante,
                nome_paciente, telefone_paciente, email_paciente, protocolo,
                procedimento_id, data_cirurgia, hora_cirurgia,
                hospital, medico_id, convenio, situacao_id,
                material_necessario, observacoes, arquivo_anexo
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            $data['nome_solicitante'],
            $data['email_solicitante'],
            $data['telefone_solicitante'],
            $data['nome_paciente'],
            $data['telefone_paciente'] ?? null,
            $data['email_paciente'] ?? null,
            $data['protocolo'] ?? null,
            $data['procedimento_id'],
            $data['data_cirurgia'],
            $data['hora_cirurgia'],
            $data['hospital'],
            $data['medico_id'],
            $data['convenio'],
            $data['situacao_id'],
            $data['material_necessario'] ?? null,
            $data['observacoes'] ?? null,
            $data['arquivo_anexo'] ?? null
        ]);
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE agendamentos SET
                nome_solicitante = ?,
                email_solicitante = ?,
                telefone_solicitante = ?,
                nome_paciente = ?,
                telefone_paciente = ?,
                email_paciente = ?,
                protocolo = ?,
                procedimento_id = ?,
                data_cirurgia = ?,
                hora_cirurgia = ?,
                hospital = ?,
                medico_id = ?,
                convenio = ?,
                situacao_id = ?,
                material_necessario = ?,
                observacoes = ?,
                arquivo_anexo = ?
            WHERE id = ?
        ");

        return $stmt->execute([
            $data['nome_solicitante'],
            $data['email_solicitante'],
            $data['telefone_solicitante'],
            $data['nome_paciente'],
            $data['telefone_paciente'] ?? null,
            $data['email_paciente'] ?? null,
            $data['protocolo'] ?? null,
            $data['procedimento_id'],
            $data['data_cirurgia'],
            $data['hora_cirurgia'],
            $data['hospital'],
            $data['medico_id'],
            $data['convenio'],
            $data['situacao_id'],
            $data['material_necessario'] ?? null,
            $data['observacoes'] ?? null,
            $data['arquivo_anexo'] ?? null,
            $id
        ]);
    }

    public function delete($id) {
        // Primeiro, buscar o arquivo anexo para deletar
        $agendamento = $this->getById($id);
        if ($agendamento && $agendamento['arquivo_anexo']) {
            $file_path = UPLOAD_DIR . $agendamento['arquivo_anexo'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }

        $stmt = $this->db->prepare("DELETE FROM agendamentos WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getCalendarEvents($medico_id = null) {
        $agendamentos = $this->getAll($medico_id);
        $events = [];

        foreach ($agendamentos as $ag) {
            $events[] = [
                'id' => $ag['id'],
                'title' => $ag['nome_paciente'] . ' - ' . $ag['procedimento_nome'],
                'start' => $ag['data_cirurgia'] . 'T' . $ag['hora_cirurgia'],
                'backgroundColor' => $ag['situacao_cor'],
                'borderColor' => $ag['situacao_cor'],
                'extendedProps' => [
                    'hospital' => $ag['hospital'],
                    'medico' => $ag['medico_nome'],
                    'situacao' => $ag['situacao_nome']
                ]
            ];
        }

        return $events;
    }
}
