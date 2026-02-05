-- Adicionar campo deleted_at para soft delete de agendamentos
-- Este campo permite ocultar agendamentos quando uma situação é deletada

ALTER TABLE agendamentos
ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL
AFTER updated_at;

-- Criar índice para melhorar performance de consultas
CREATE INDEX idx_deleted_at ON agendamentos(deleted_at);
