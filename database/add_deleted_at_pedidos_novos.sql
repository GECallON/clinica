-- Adicionar campo deleted_at para soft delete de pedidos
-- Este campo permite ocultar pedidos quando uma situação é deletada

ALTER TABLE pedidos_novos
ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL
AFTER updated_at;

-- Criar índice para melhorar performance de consultas
CREATE INDEX idx_deleted_at ON pedidos_novos(deleted_at);
