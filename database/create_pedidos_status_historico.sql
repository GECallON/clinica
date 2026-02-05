-- Tabela para armazenar histórico de alterações de status dos pedidos
CREATE TABLE IF NOT EXISTS pedidos_status_historico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    situacao_id INT NOT NULL,
    usuario_id INT NOT NULL COMMENT 'Usuário que fez a alteração',
    observacao TEXT COMMENT 'Observação sobre a mudança de status',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pedido_id) REFERENCES pedidos_novos(id) ON DELETE CASCADE,
    FOREIGN KEY (situacao_id) REFERENCES situacoes(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    INDEX idx_pedido_id (pedido_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela para armazenar histórico de alterações de status dos agendamentos
CREATE TABLE IF NOT EXISTS agendamentos_status_historico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    agendamento_id INT NOT NULL,
    situacao_id INT NOT NULL,
    usuario_id INT NOT NULL COMMENT 'Usuário que fez a alteração',
    observacao TEXT COMMENT 'Observação sobre a mudança de status',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (agendamento_id) REFERENCES agendamentos(id) ON DELETE CASCADE,
    FOREIGN KEY (situacao_id) REFERENCES situacoes(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    INDEX idx_agendamento_id (agendamento_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
