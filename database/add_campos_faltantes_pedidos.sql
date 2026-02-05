-- Adicionar apenas campos faltantes na tabela pedidos_novos
ALTER TABLE pedidos_novos
ADD COLUMN procedimento VARCHAR(255) AFTER convenio,
ADD COLUMN data_recebimento DATE AFTER procedimento,
ADD COLUMN origem VARCHAR(255) AFTER data_recebimento,
ADD COLUMN valor_material DECIMAL(10,2) AFTER origem;