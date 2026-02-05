# 🌐 Acesso ao Sistema

## URL de Acesso

O sistema está configurado e pode ser acessado através do domínio:

**http://clinica.callon.com.br**

---

## ✅ Configuração Completa

### VirtualHost Apache
- ✅ Criado: `/etc/apache2/sites-available/clinica.callon.com.br.conf`
- ✅ Habilitado no Apache
- ✅ DocumentRoot: `/var/www/html/sistema-agendamento/public`
- ✅ Mod_rewrite habilitado

### DNS/Hosts
- ✅ Entrada no `/etc/hosts`: `127.0.0.1 clinica.callon.com.br`

### Configuração da Aplicação
- ✅ BASE_URL atualizada para: `http://clinica.callon.com.br`

---

## 🔐 Credenciais de Acesso

### Administrador
- **URL:** http://clinica.callon.com.br
- **Email:** admin@sistema.com
- **Senha:** admin123

### Médico (exemplo)
- **URL:** http://clinica.callon.com.br
- **Email:** joao.silva@clinica.com
- **Senha:** medico123

---

## 📋 Próximos Passos

### 1. Instalar o Banco de Dados (se ainda não instalou)

Acesse o instalador:
```
http://clinica.callon.com.br/../install.php
```

Ou importe manualmente:
```bash
mysql -u root -p < /var/www/html/sistema-agendamento/database/schema.sql
```

### 2. Configurar Permissões

```bash
sudo chmod 755 /var/www/html/sistema-agendamento/uploads/
sudo chown -R www-data:www-data /var/www/html/sistema-agendamento/uploads/
```

### 3. Deletar o Instalador (após instalação)

```bash
rm /var/www/html/sistema-agendamento/install.php
```

### 4. Alterar Senhas Padrão

Após o primeiro acesso, altere as senhas através do painel administrativo em:
**Usuários → Editar → Nova Senha**

---

## 🔒 Configurar SSL/HTTPS (Recomendado)

Para habilitar HTTPS com certificado SSL:

### Opção 1: Let's Encrypt (Gratuito)

```bash
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d clinica.callon.com.br
```

### Opção 2: Configuração Manual

Descomente a seção SSL no arquivo:
```
/etc/apache2/sites-available/clinica.callon.com.br.conf
```

E adicione seus certificados SSL.

Depois, habilite o módulo SSL e recarregue:
```bash
sudo a2enmod ssl
sudo systemctl reload apache2
```

Atualize também o `BASE_URL` em `src/config.php`:
```php
define('BASE_URL', 'https://clinica.callon.com.br');
```

---

## 🌍 Acesso Externo

### Para acesso na rede local:
1. Substitua `127.0.0.1` pelo IP do servidor no `/etc/hosts` das máquinas clientes
2. Configure o firewall se necessário:
   ```bash
   sudo ufw allow 80/tcp
   sudo ufw allow 443/tcp
   ```

### Para acesso público na internet:
1. Configure o DNS do domínio `clinica.callon.com.br` para apontar para o IP do servidor
2. Configure o firewall/roteador para encaminhar as portas 80 e 443
3. Configure SSL/HTTPS (obrigatório para produção)

---

## 📞 Logs e Troubleshooting

### Logs do Apache:
```bash
# Error log
tail -f /var/log/apache2/clinica.callon.com.br-error.log

# Access log
tail -f /var/log/apache2/clinica.callon.com.br-access.log
```

### Testar configuração:
```bash
sudo apache2ctl configtest
sudo apache2ctl -S | grep clinica
```

### Recarregar Apache:
```bash
sudo systemctl reload apache2
```

---

## ✨ Pronto!

Acesse agora: **http://clinica.callon.com.br** 🎉
