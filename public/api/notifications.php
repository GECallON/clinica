<?php
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/models/Agendamento.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Não autenticado']);
    exit;
}

$agendamentoModel = new Agendamento();
$hoje = date('Y-m-d');
$agora = date('Y-m-d H:i:s');

$notifications = [];

if (isMedico()) {
    // Buscar agendamentos do médico
    $agendamentos = $agendamentoModel->getAll($_SESSION['user_id']);

    // Agendamentos de hoje
    $agendamentos_hoje = array_filter($agendamentos, function($a) use ($hoje) {
        return $a['data_cirurgia'] === $hoje;
    });

    // Agendamentos próximos (próximas 2 horas)
    $proximas_2h = array_filter($agendamentos, function($a) use ($agora) {
        $data_hora = $a['data_cirurgia'] . ' ' . $a['hora_cirurgia'];
        $diff = (strtotime($data_hora) - strtotime($agora)) / 3600; // diferença em horas
        return $diff > 0 && $diff <= 2;
    });

    // Agendamentos amanhã
    $amanha = date('Y-m-d', strtotime('+1 day'));
    $agendamentos_amanha = array_filter($agendamentos, function($a) use ($amanha) {
        return $a['data_cirurgia'] === $amanha;
    });

    // Criar notificações
    if (count($agendamentos_hoje) > 0) {
        $notifications[] = [
            'id' => 'hoje_' . time(),
            'type' => 'info',
            'icon' => 'fa-calendar-day',
            'title' => 'Agendamentos Hoje',
            'message' => 'Você tem ' . count($agendamentos_hoje) . ' agendamento(s) para hoje',
            'time' => 'Agora',
            'priority' => 'medium',
            'link' => null
        ];
    }

    foreach ($proximas_2h as $ag) {
        $data_hora = $ag['data_cirurgia'] . ' ' . $ag['hora_cirurgia'];
        $minutos = round((strtotime($data_hora) - strtotime($agora)) / 60);

        $notifications[] = [
            'id' => 'prox_' . $ag['id'],
            'type' => 'warning',
            'icon' => 'fa-bell',
            'title' => 'Procedimento em breve',
            'message' => $ag['procedimento_nome'] . ' - ' . $ag['nome_paciente'] . ' em ' . $minutos . ' min',
            'time' => 'Em ' . $minutos . ' min',
            'priority' => 'high',
            'link' => 'view-agendamento.php?id=' . $ag['id']
        ];
    }

    if (count($agendamentos_amanha) > 0) {
        $notifications[] = [
            'id' => 'amanha_' . time(),
            'type' => 'success',
            'icon' => 'fa-calendar-alt',
            'title' => 'Amanhã',
            'message' => 'Você tem ' . count($agendamentos_amanha) . ' agendamento(s) para amanhã',
            'time' => 'Amanhã',
            'priority' => 'low',
            'link' => null
        ];
    }

    // Total de agendamentos
    $total = count($agendamentos);

    echo json_encode([
        'success' => true,
        'notifications' => $notifications,
        'counts' => [
            'total' => $total,
            'hoje' => count($agendamentos_hoje),
            'proximos_7_dias' => count(array_filter($agendamentos, function($a) use ($hoje) {
                return $a['data_cirurgia'] >= $hoje && $a['data_cirurgia'] <= date('Y-m-d', strtotime('+7 days'));
            }))
        ],
        'last_update' => date('Y-m-d H:i:s')
    ]);
} else {
    // Admin
    echo json_encode([
        'success' => true,
        'notifications' => [],
        'counts' => [],
        'last_update' => date('Y-m-d H:i:s')
    ]);
}
