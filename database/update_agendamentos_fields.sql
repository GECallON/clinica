-- Script para atualizar campos da tabela agendamentos
-- Mantendo apenas os campos solicitados

-- 1. Adicionar campo fornecedor
ALTER TABLE agendamentos
ADD COLUMN fornecedor VARCHAR(255) AFTER hospital;

-- 2. Tornar situacao_id NULLABLE (campo não será mais obrigatório)
ALTER TABLE agendamentos
MODIFY COLUMN situacao_id INT NULL;

-- 3. Renomear telefone_solicitante para telefone
ALTER TABLE agendamentos
CHANGE COLUMN telefone_solicitante telefone VARCHAR(20) NOT NULL;

-- 4. Remover campos desnecessários
ALTER TABLE agendamentos
DROP COLUMN email_solicitante,
DROP COLUMN telefone_paciente,
DROP COLUMN email_paciente;

-- 5. Atualizar registros existentes - definir situacao_id padrão como NULL
UPDATE agendamentos SET situacao_id = NULL WHERE situacao_id IS NOT NULL;

-- Nota: A FK de situacao_id ainda existe mas permite NULL agora
