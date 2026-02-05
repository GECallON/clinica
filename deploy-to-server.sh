#!/bin/bash

# Script de Deploy para Servidor Remoto
# Execute este script NO SERVIDOR 134.209.72.178

echo "🚀 Iniciando deploy do sistema de agendamento..."

# 1. Ir para o diretório do projeto
cd /var/www/html/sistema-agendamento || exit 1

# 2. Fazer backup do banco de dados
echo "📦 Fazendo backup do banco de dados..."
mysqldump -u root -p sistema_agendamento > backup_$(date +%Y%m%d_%H%M%S).sql

# 3. Fazer pull do repositório
echo "⬇️ Baixando atualizações do GitHub..."
git pull origin master

# 4. Instalar dependências do Composer
echo "📚 Instalando PHPMailer..."
composer install --no-dev --optimize-autoloader

# 5. Executar migrações do banco de dados
echo "🗄️ Executando migrações do banco de dados..."
mysql -u root -p sistema_agendamento < database/update_agendamentos_fields.sql
mysql -u root -p sistema_agendamento < database/create_pedidos_novos.sql
mysql -u root -p sistema_agendamento < database/add_hospital_fornecedores.sql
mysql -u root -p sistema_agendamento < database/add_telefone_pedidos.sql
mysql -u root -p sistema_agendamento < database/create_mensagens_config.sql
mysql -u root -p sistema_agendamento < database/update_mensagens_eventos.sql
mysql -u root -p sistema_agendamento < database/update_pedidos_novos_medico.sql
mysql -u root -p sistema_agendamento < database/create_email_config.sql

# 6. Ajustar permissões
echo "🔒 Ajustando permissões..."
chown -R www-data:www-data /var/www/html/sistema-agendamento
chmod -R 755 /var/www/html/sistema-agendamento
chmod -R 775 /var/www/html/sistema-agendamento/uploads

# 7. Recarregar Apache
echo "🔄 Recarregando Apache..."
systemctl reload apache2

echo "✅ Deploy concluído com sucesso!"
echo ""
echo "📋 Próximos passos:"
echo "1. Acesse: http://SEU_DOMINIO/admin/email-config.php"
echo "2. Configure o SMTP (Gmail ou outro)"
echo "3. Acesse: http://SEU_DOMINIO/admin/mensagens-list.php"
echo "4. Configure as mensagens WhatsApp por status"
echo "5. Teste criando um agendamento ou pedido!"
