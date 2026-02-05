-- Criar tabela de hospitais com emails
CREATE TABLE IF NOT EXISTS hospitais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL,
    telefone VARCHAR(20),
    endereco TEXT,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nome (nome),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Adicionar campos de fornecedores e email do hospital na tabela agendamentos
ALTER TABLE agendamentos
ADD COLUMN fornecedor1 VARCHAR(255) AFTER fornecedor,
ADD COLUMN fornecedor2 VARCHAR(255) AFTER fornecedor1,
ADD COLUMN email_hospital VARCHAR(255) AFTER hospital;

-- Renomear campo fornecedor para manter compatibilidade (opcional - pode manter os 3)
-- Se quiser, pode fazer: ALTER TABLE agendamentos DROP COLUMN fornecedor;
-- Mas vou manter o campo fornecedor existente e adicionar fornecedor1 e fornecedor2

-- Inserir alguns hospitais exemplo (você pode ajustar conforme necessário)
INSERT INTO hospitais (nome, email, telefone) VALUES
('Hospital São Lucas', 'agendamento@saolucas.com.br', '(27) 3333-4444'),
('Hospital Santa Maria', 'cirurgia@santamaria.com.br', '(27) 3333-5555'),
('Hospital Central', 'procedimentos@central.com.br', '(27) 3333-6666'),
('Clínica Ortopédica', 'contato@ortopedica.com.br', '(27) 3333-7777');
