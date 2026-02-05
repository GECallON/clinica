-- Criação do banco de dados
CREATE DATABASE IF NOT EXISTS sistema_agendamento CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sistema_agendamento;

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    nivel_acesso ENUM('admin', 'medico') NOT NULL,
    telefone VARCHAR(20),
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_nivel_acesso (nivel_acesso)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de procedimentos
CREATE TABLE IF NOT EXISTS procedimentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    duracao_estimada INT DEFAULT 60 COMMENT 'Duração em minutos',
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de situações
CREATE TABLE IF NOT EXISTS situacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cor VARCHAR(7) DEFAULT '#3B82F6' COMMENT 'Cor em hexadecimal',
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de agendamentos/cirurgias
CREATE TABLE IF NOT EXISTS agendamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_solicitante VARCHAR(255) NOT NULL,
    email_solicitante VARCHAR(255) NOT NULL,
    telefone_solicitante VARCHAR(20) NOT NULL,
    nome_paciente VARCHAR(255) NOT NULL,
    procedimento_id INT NOT NULL,
    data_cirurgia DATE NOT NULL,
    hora_cirurgia TIME NOT NULL,
    hospital VARCHAR(255) NOT NULL,
    medico_id INT NOT NULL,
    convenio VARCHAR(255) NOT NULL,
    situacao_id INT NOT NULL,
    material_necessario TEXT,
    observacoes TEXT,
    arquivo_anexo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (procedimento_id) REFERENCES procedimentos(id),
    FOREIGN KEY (medico_id) REFERENCES usuarios(id),
    FOREIGN KEY (situacao_id) REFERENCES situacoes(id),
    INDEX idx_data_cirurgia (data_cirurgia),
    INDEX idx_medico (medico_id),
    INDEX idx_situacao (situacao_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir situações padrão
INSERT INTO situacoes (nome, cor) VALUES
('Autorizado', '#10B981'),
('Aguardando Autorização', '#F59E0B'),
('Urgência', '#EF4444'),
('Outros', '#6B7280');

-- Inserir procedimentos padrão (baseado no site de referência)
INSERT INTO procedimentos (nome, duracao_estimada) VALUES
('Artroscopia de Ombro', 90),
('Artroscopia de Joelho', 90),
('Reconstrução de LCA', 120),
('Prótese Total de Joelho', 120),
('Prótese Total de Quadril', 120),
('Reparo de Manguito Rotador', 120),
('Osteossíntese', 90),
('Correção de Hallux Valgus', 60),
('Liberação de Túnel do Carpo', 45),
('Cirurgia de Coluna', 180);

-- Inserir usuário admin padrão (senha: admin123)
INSERT INTO usuarios (nome, email, senha, nivel_acesso, telefone) VALUES
('Administrador', 'admin@sistema.com', '$2y$10$GoAhs45JWX9In3ZW5NQJlOaEWG/pISGfJKW/DGywllHOmFiLTlVFG', 'admin', '(00) 00000-0000');

-- Inserir médico exemplo (senha: medico123)
INSERT INTO usuarios (nome, email, senha, nivel_acesso, telefone) VALUES
('Dr. João Silva', 'joao.silva@clinica.com', '$2y$10$7UGhZMa6H8RgCmpduRtg7.Gmdi/f0xETMyGc1/34F96SETvt9Xt6C', 'medico', '(00) 91234-5678');
