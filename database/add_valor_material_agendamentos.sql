-- Adicionar campo valor_material na tabela agendamentos
ALTER TABLE agendamentos
ADD COLUMN valor_material DECIMAL(10,2) AFTER fornecedor;