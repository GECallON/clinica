-- Atualizar tabela pedidos_novos para usar medico_id

-- Adicionar campo medico_id com FK
ALTER TABLE pedidos_novos
ADD COLUMN medico_id INT AFTER nome_paciente,
ADD CONSTRAINT fk_pedido_medico
FOREIGN KEY (medico_id) REFERENCES usuarios(id) ON DELETE SET NULL,
ADD INDEX idx_medico (medico_id);

-- Remover campo nome_medico (se preferir usar só medico_id)
-- ALTER TABLE pedidos_novos DROP COLUMN nome_medico;

-- OU manter ambos (recomendado para compatibilidade)
-- nome_medico pode ser usado como fallback se não houver médico cadastrado
