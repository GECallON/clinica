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
                nome_solicitante, telefone,
                nome_paciente, protocolo,
                procedimento_id, data_cirurgia, hora_cirurgia,
                hospital, email_hospital, fornecedor, fornecedor1, fornecedor2,
                medico_id, convenio, situacao_id,
                material_necessario, observacoes, arquivo_anexo
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $result = $stmt->execute([
            $data['nome_solicitante'],
            $data['telefone'],
            $data['nome_paciente'],
            $data['protocolo'] ?? null,
            $data['procedimento_id'],
            $data['data_cirurgia'],
            $data['hora_cirurgia'],
            $data['hospital'],
            $data['email_hospital'] ?? null,
            $data['fornecedor'] ?? null,
            $data['fornecedor1'] ?? null,
            $data['fornecedor2'] ?? null,
            $data['medico_id'],
            $data['convenio'],
            $data['situacao_id'] ?? null,
            $data['material_necessario'] ?? null,
            $data['observacoes'] ?? null,
            $data['arquivo_anexo'] ?? null
        ]);

        return $result;
    }

    public function getLastInsertId() {
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE agendamentos SET
                nome_solicitante = ?,
                telefone = ?,
                nome_paciente = ?,
                protocolo = ?,
                procedimento_id = ?,
                data_cirurgia = ?,
                hora_cirurgia = ?,
                hospital = ?,
                email_hospital = ?,
                fornecedor = ?,
                fornecedor1 = ?,
                fornecedor2 = ?,
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
            $data['telefone'],
            $data['nome_paciente'],
            $data['protocolo'] ?? null,
            $data['procedimento_id'],
            $data['data_cirurgia'],
            $data['hora_cirurgia'],
            $data['hospital'],
            $data['email_hospital'] ?? null,
            $data['fornecedor'] ?? null,
            $data['fornecedor1'] ?? null,
            $data['fornecedor2'] ?? null,
            $data['medico_id'],
            $data['convenio'],
            $data['situacao_id'] ?? null,
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

    // Relatórios e Estatísticas
    public function getStatsBySituacao() {
        $stmt = $this->db->query("
            SELECT s.nome, s.cor, COUNT(a.id) as total
            FROM situacoes s
            LEFT JOIN agendamentos a ON a.situacao_id = s.id
            GROUP BY s.id, s.nome, s.cor
            ORDER BY total DESC
        ");
        return $stmt->fetchAll();
    }

    public function getStatsByHospital($limit = 10) {
        $stmt = $this->db->prepare("
            SELECT hospital, COUNT(*) as total
            FROM agendamentos
            GROUP BY hospital
            ORDER BY total DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function getStatsByMedico($limit = 10) {
        $stmt = $this->db->prepare("
            SELECT u.nome as medico_nome, COUNT(a.id) as total
            FROM agendamentos a
            JOIN usuarios u ON a.medico_id = u.id
            GROUP BY u.id, u.nome
            ORDER BY total DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function getStatsByProcedimento($limit = 10) {
        $stmt = $this->db->prepare("
            SELECT p.nome as procedimento_nome, COUNT(a.id) as total
            FROM agendamentos a
            JOIN procedimentos p ON a.procedimento_id = p.id
            GROUP BY p.id, p.nome
            ORDER BY total DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function getMonthlyStats($months = 6) {
        $stmt = $this->db->prepare("
            SELECT
                DATE_FORMAT(data_cirurgia, '%Y-%m') as mes,
                COUNT(*) as total
            FROM agendamentos
            WHERE data_cirurgia >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
            GROUP BY mes
            ORDER BY mes ASC
        ");
        $stmt->execute([$months]);
        return $stmt->fetchAll();
    }

    public function getUpcomingStats($days = 30) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total
            FROM agendamentos
            WHERE data_cirurgia BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
        ");
        $stmt->execute([$days]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function getStatsThisMonth() {
        $stmt = $this->db->query("
            SELECT COUNT(*) as total
            FROM agendamentos
            WHERE MONTH(data_cirurgia) = MONTH(CURDATE())
            AND YEAR(data_cirurgia) = YEAR(CURDATE())
        ");
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function getStatsLastMonth() {
        $stmt = $this->db->query("
            SELECT COUNT(*) as total
            FROM agendamentos
            WHERE MONTH(data_cirurgia) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
            AND YEAR(data_cirurgia) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
        ");
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function getStatsDetalhadasPorStatus() {
        $stmt = $this->db->query("
            SELECT
                s.id,
                s.nome,
                s.cor,
                COUNT(a.id) as total,
                COUNT(CASE WHEN a.data_cirurgia = CURDATE() THEN 1 END) as hoje,
                COUNT(CASE WHEN a.data_cirurgia BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 1 END) as proximos_7_dias,
                ROUND((COUNT(a.id) / NULLIF((SELECT COUNT(*) FROM agendamentos), 0) * 100), 1) as percentual
            FROM situacoes s
            LEFT JOIN agendamentos a ON a.situacao_id = s.id
            GROUP BY s.id, s.nome, s.cor
            ORDER BY total DESC
        ");
        return $stmt->fetchAll();
    }

    public function getVolumeUltimos30Dias() {
        $stmt = $this->db->query("
            SELECT
                s.nome,
                s.cor,
                DATE(a.data_cirurgia) as data,
                COUNT(*) as total
            FROM agendamentos a
            JOIN situacoes s ON a.situacao_id = s.id
            WHERE a.data_cirurgia >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY s.id, s.nome, s.cor, DATE(a.data_cirurgia)
            ORDER BY data DESC, total DESC
        ");
        return $stmt->fetchAll();
    }
}
