-- Adicionar campo telefone na tabela pedidos_novos
ALTER TABLE pedidos_novos
ADD COLUMN telefone VARCHAR(20) AFTER nome_paciente,
ADD COLUMN whatsapp_enviado BOOLEAN DEFAULT FALSE AFTER observacao,
ADD COLUMN whatsapp_enviado_em TIMESTAMP NULL AFTER whatsapp_enviado;

-- Criar índice para telefone
CREATE INDEX idx_telefone ON pedidos_novos(telefone);
