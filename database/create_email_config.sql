-- Criar tabela de configuração de email
CREATE TABLE IF NOT EXISTS email_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    smtp_host VARCHAR(255) NOT NULL COMMENT 'Servidor SMTP (ex: smtp.gmail.com)',
    smtp_port INT DEFAULT 587 COMMENT 'Porta SMTP (587 para TLS, 465 para SSL)',
    smtp_user VARCHAR(255) NOT NULL COMMENT 'Email de envio',
    smtp_password VARCHAR(255) NOT NULL COMMENT 'Senha do email',
    smtp_secure ENUM('tls', 'ssl') DEFAULT 'tls' COMMENT 'Tipo de criptografia',
    email_remetente VARCHAR(255) NOT NULL COMMENT 'Email que aparece como remetente',
    nome_remetente VARCHAR(255) DEFAULT 'Sistema de Agendamentos' COMMENT 'Nome do remetente',
    enviar_automatico BOOLEAN DEFAULT TRUE COMMENT 'Enviar email ao criar/editar agendamento',
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Criar tabela de log de emails enviados
CREATE TABLE IF NOT EXISTS email_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    agendamento_id INT,
    destinatarios TEXT COMMENT 'JSON com emails de destino',
    assunto VARCHAR(255),
    status ENUM('enviado', 'erro') DEFAULT 'enviado',
    erro_mensagem TEXT,
    enviado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (agendamento_id) REFERENCES agendamentos(id) ON DELETE SET NULL,
    INDEX idx_agendamento (agendamento_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir configuração padrão (ajuste conforme seu servidor de email)
INSERT INTO email_config (
    smtp_host, smtp_port, smtp_user, smtp_password, smtp_secure,
    email_remetente, nome_remetente, enviar_automatico, ativo
) VALUES (
    'smtp.gmail.com',
    587,
    'seu-email@gmail.com',
    'sua-senha-app',
    'tls',
    'noreply@clinica.com.br',
    'MedAgenda Pro - Sistema de Agendamentos',
    1,
    1
);
