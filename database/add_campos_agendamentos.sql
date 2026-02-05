-- Adicionar novos campos na tabela agendamentos
ALTER TABLE agendamentos
ADD COLUMN fornecedor VARCHAR(255) AFTER hospital,
ADD COLUMN valor_material DECIMAL(10,2) AFTER fornecedor,
ADD COLUMN telefone VARCHAR(20) AFTER email_solicitante,
ADD COLUMN protocolo VARCHAR(100) AFTER nome_paciente;