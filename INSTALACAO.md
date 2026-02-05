# 🚀 Guia Rápido de Instalação

## Método 1: Instalação Automática (Recomendado)

1. **Acesse o instalador no navegador:**
   ```
   http://seu-dominio/sistema-agendamento/install.php
   ```

2. **Configure as credenciais do banco** (se necessário):
   - Abra `install.php`
   - Edite as linhas 8-10 com suas credenciais MySQL

3. **Execute o instalador** e siga as instruções na tela

4. **DELETE o arquivo install.php** após a instalação

5. **Acesse o sistema:**
   ```
   http://seu-dominio/sistema-agendamento/public/
   ```

## Método 2: Instalação Manual

1. **Configure o banco de dados:**

   Edite `src/config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'sistema_agendamento');
   define('DB_USER', 'seu_usuario');
   define('DB_PASS', 'sua_senha');
   ```

2. **Importe o banco de dados:**
   ```bash
   mysql -u root -p < database/schema.sql
   ```

   Ou use o phpMyAdmin para importar `database/schema.sql`

3. **Configure permissões:**
   ```bash
   chmod 755 uploads/
   ```

4. **Acesse o sistema:**
   ```
   http://seu-dominio/sistema-agendamento/public/
   ```

## 🔐 Credenciais Padrão

### Administrador
- Email: `admin@sistema.com`
- Senha: `admin123`

### Médico (exemplo)
- Email: `joao.silva@clinica.com`
- Senha: `medico123`

**⚠️ ALTERE AS SENHAS APÓS O PRIMEIRO ACESSO!**

## 📋 Checklist Pós-Instalação

- [ ] Banco de dados criado e populado
- [ ] Pasta uploads com permissão de escrita
- [ ] Arquivo install.php deletado
- [ ] Senhas padrão alteradas
- [ ] Config.php configurado com credenciais corretas
- [ ] Sistema acessível via navegador

## 🐛 Problemas Comuns

### Erro de conexão com banco
- Verifique as credenciais em `src/config.php`
- Confirme que o MySQL está rodando
- Verifique se o banco foi criado

### Página em branco
- Habilite exibição de erros no PHP
- Verifique os logs do Apache/PHP
- Confirme que todas as extensões PHP estão instaladas

### Erro ao fazer upload
- Verifique permissões: `chmod 755 uploads/`
- Confira o limite de upload no php.ini

## 📞 Suporte

Consulte o arquivo `README.md` para documentação completa.

---

**Pronto para começar?** Acesse o instalador agora! 🎉
