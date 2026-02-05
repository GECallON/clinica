-- Script para adicionar novos campos na tabela agendamentos
ALTER TABLE agendamentos
ADD COLUMN telefone_paciente VARCHAR(20) AFTER nome_paciente,
ADD COLUMN email_paciente VARCHAR(100) AFTER telefone_paciente,
ADD COLUMN protocolo VARCHAR(100) AFTER email_paciente;

-- Criar índice para busca por protocolo
CREATE INDEX idx_protocolo ON agendamentos(protocolo);