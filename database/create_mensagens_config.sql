-- Criar tabela para configurações de mensagens
CREATE TABLE IF NOT EXISTS mensagens_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL COMMENT 'Nome da configuração',
    dominio VARCHAR(255) NOT NULL COMMENT 'Domínio da API (ex: dev.callon.com.br)',
    canal INT NOT NULL COMMENT 'ID do canal na API',
    texto_mensagem TEXT NOT NULL COMMENT 'Texto da mensagem a ser enviada',
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Adicionar campo whatsapp_enviado na tabela agendamentos
ALTER TABLE agendamentos
ADD COLUMN whatsapp_enviado BOOLEAN DEFAULT FALSE AFTER arquivo_anexo,
ADD COLUMN whatsapp_enviado_em TIMESTAMP NULL AFTER whatsapp_enviado;

-- Inserir configuração padrão
INSERT INTO mensagens_config (nome, dominio, canal, texto_mensagem) VALUES
('Confirmação Padrão', 'dev.callon.com.br', 1, 'Olá! Seu agendamento foi confirmado para o dia {data} às {hora}. Hospital: {hospital}. Em caso de dúvidas, entre em contato.');
