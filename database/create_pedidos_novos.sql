-- Criação da tabela pedidos_novos
CREATE TABLE IF NOT EXISTS pedidos_novos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_paciente VARCHAR(255) NOT NULL,
    nome_medico VARCHAR(255) NOT NULL,
    convenio VARCHAR(255) NOT NULL,
    fornecedor VARCHAR(255),
    observacao TEXT,
    situacao_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (situacao_id) REFERENCES situacoes(id) ON DELETE SET NULL,
    INDEX idx_situacao (situacao_id),
    INDEX idx_nome_paciente (nome_paciente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
