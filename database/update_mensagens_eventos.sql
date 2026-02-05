-- Atualizar tabela mensagens_config para suportar eventos e status

-- Adicionar novos campos
ALTER TABLE mensagens_config
ADD COLUMN tipo_evento ENUM('manual', 'mudanca_status', 'lembrete', 'cancelamento') DEFAULT 'manual' AFTER ativo,
ADD COLUMN situacao_id INT NULL AFTER tipo_evento,
ADD COLUMN enviar_automatico BOOLEAN DEFAULT FALSE AFTER situacao_id,
ADD COLUMN dias_antes_lembrete INT NULL COMMENT 'Dias antes para enviar lembrete (se tipo=lembrete)',
ADD INDEX idx_tipo_evento (tipo_evento),
ADD INDEX idx_situacao (situacao_id);

-- Adicionar FK para situação (se aplicável)
ALTER TABLE mensagens_config
ADD CONSTRAINT fk_mensagem_situacao
FOREIGN KEY (situacao_id) REFERENCES situacoes(id) ON DELETE SET NULL;

-- Inserir mensagens padrão por status
INSERT INTO mensagens_config (nome, dominio, canal, texto_mensagem, ativo, tipo_evento, situacao_id, enviar_automatico) VALUES
('Autorizado - Notificação', 'dev.callon.com.br', 1, 'Olá {paciente}! Seu procedimento foi AUTORIZADO ✅. Data: {data} às {hora}. Hospital: {hospital}. Aguardamos você!', 0, 'mudanca_status', 1, 1),
('Aguardando Autorização', 'dev.callon.com.br', 1, 'Olá {paciente}! Seu procedimento está em análise ⏳. Data prevista: {data} às {hora}. Em breve retornaremos com a confirmação.', 0, 'mudanca_status', 2, 1),
('Urgência - Alerta', 'dev.callon.com.br', 1, 'URGENTE ⚠️ {paciente}! Seu procedimento está marcado como URGÊNCIA. Data: {data} às {hora}. Hospital: {hospital}. Compareça com antecedência!', 0, 'mudanca_status', 3, 1),
('Lembrete 1 Dia Antes', 'dev.callon.com.br', 1, 'Lembrete: {paciente}, seu procedimento é AMANHÃ 📅 ({data}) às {hora}. Local: {hospital}. Lembre-se do jejum e documentos!', 0, 'lembrete', NULL, 1);

-- Atualizar a mensagem padrão existente para ser manual
UPDATE mensagens_config SET tipo_evento = 'manual' WHERE id = 1;
