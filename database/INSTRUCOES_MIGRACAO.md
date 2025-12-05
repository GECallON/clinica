# Instruções para Aplicar as Mudanças no Banco de Dados

## Alterações Realizadas

### 1. Atualização da Tabela Agendamentos
**Arquivo**: `update_agendamentos_fields.sql`

**Mudanças**:
- ✅ Adicionado campo `fornecedor` (VARCHAR 255)
- ✅ Renomeado campo `telefone_solicitante` para `telefone`
- ✅ Campo `situacao_id` agora permite NULL (não é mais obrigatório)
- ❌ Removidos campos: `email_solicitante`, `telefone_paciente`, `email_paciente`

**Campos finais do formulário de agendamento**:
1. Nome do solicitante
2. Número de telefone
3. Nome do paciente
4. Protocolo
5. Convênio
6. Procedimento
7. Médico solicitante
8. Data do procedimento
9. Hora do procedimento
10. Hospital
11. Fornecedor
12. Material
13. Observação
14. Anexo

### 2. Nova Tabela Pedidos Novos
**Arquivo**: `create_pedidos_novos.sql`

**Campos**:
1. Nome do paciente
2. Nome do médico
3. Convênio
4. Fornecedor
5. Observação
6. Situação

## Como Aplicar as Mudanças

### Opção 1: Via phpMyAdmin ou Cliente MySQL GUI
1. Acesse o phpMyAdmin ou seu cliente MySQL
2. Selecione o banco `sistema_agendamento`
3. Execute o conteúdo do arquivo `update_agendamentos_fields.sql`
4. Execute o conteúdo do arquivo `create_pedidos_novos.sql`

### Opção 2: Via Linha de Comando
```bash
# Conectar ao MySQL
mysql -u root -p

# Selecionar o banco
USE sistema_agendamento;

# Executar as migrações
source /var/www/html/sistema-agendamento/database/update_agendamentos_fields.sql;
source /var/www/html/sistema-agendamento/database/create_pedidos_novos.sql;

# Verificar as mudanças
DESCRIBE agendamentos;
DESCRIBE pedidos_novos;
```

### Opção 3: Comando Único
```bash
cd /var/www/html/sistema-agendamento/database
mysql -u root -p sistema_agendamento < update_agendamentos_fields.sql
mysql -u root -p sistema_agendamento < create_pedidos_novos.sql
```

## Verificação Pós-Migração

Após executar os scripts, verifique se tudo está correto:

```sql
-- Verificar estrutura da tabela agendamentos
SHOW COLUMNS FROM agendamentos;

-- Verificar estrutura da tabela pedidos_novos
SHOW COLUMNS FROM pedidos_novos;

-- Verificar se as FKs estão corretas
SHOW CREATE TABLE agendamentos;
SHOW CREATE TABLE pedidos_novos;
```

## Novos Menus Disponíveis

Após aplicar as mudanças no banco e atualizar os arquivos:

1. **Agendamentos** (atualizado):
   - `/admin/agendamento-create.php` - Formulário simplificado
   - `/admin/agendamentos-list.php` - Lista de agendamentos

2. **Pedidos Novos** (novo):
   - `/admin/pedidos-novos-create.php` - Criar novo pedido
   - `/admin/pedidos-novos-list.php` - Lista de pedidos
   - Disponível no menu lateral em "Visão Geral" e "Ações rápidas"

## Arquivos Modificados

### Backend (Models)
- `src/models/Agendamento.php` - Atualizado (removidos campos antigos, adicionado fornecedor)
- `src/models/PedidoNovo.php` - Criado (novo model)

### Frontend (Views)
- `public/admin/agendamento-create.php` - Atualizado (formulário simplificado)
- `public/admin/pedidos-novos-create.php` - Criado
- `public/admin/pedidos-novos-list.php` - Criado
- `public/admin/pedidos-novos.php` - Criado (controller)
- `public/admin/includes/sidebar.php` - Atualizado (novo menu)

### Database
- `database/update_agendamentos_fields.sql` - Script de migração dos agendamentos
- `database/create_pedidos_novos.sql` - Script de criação da tabela pedidos_novos

## Notas Importantes

⚠️ **BACKUP**: Antes de executar qualquer migração, faça backup do banco de dados:
```bash
mysqldump -u root -p sistema_agendamento > backup_$(date +%Y%m%d).sql
```

⚠️ **Dados Existentes**: Os dados existentes em `agendamentos` serão mantidos, mas os campos removidos (`email_solicitante`, `telefone_paciente`, `email_paciente`) perderão suas informações.

⚠️ **Situação ID**: Registros existentes terão `situacao_id` definido como NULL após a migração.
