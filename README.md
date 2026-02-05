# Sistema de Agendamento Médico

Sistema completo de agendamento de cirurgias e procedimentos médicos com controle de acesso por níveis (Administrador e Médico).

## 🚀 Funcionalidades

### Para Administradores
- ✅ Dashboard com visão geral do sistema
- ✅ Gerenciamento completo de usuários (médicos e admins)
- ✅ Gerenciamento de agendamentos/cirurgias
- ✅ Gerenciamento de procedimentos
- ✅ Gerenciamento de situações (status)
- ✅ Upload de arquivos anexos
- ✅ CRUD completo para todas as entidades

### Para Médicos
- ✅ Visualização da agenda pessoal
- ✅ Visualização em calendário (mensal, semanal, diário)
- ✅ Visualização em lista de agendamentos
- ✅ Estatísticas personalizadas
- ✅ Detalhes completos dos agendamentos

### Recursos Gerais
- ✅ Sistema de autenticação seguro
- ✅ Interface moderna e responsiva com Tailwind CSS
- ✅ Calendário interativo com FullCalendar
- ✅ Upload de arquivos
- ✅ Mensagens flash para feedback ao usuário
- ✅ Validação de formulários

## 📋 Requisitos

- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Apache com mod_rewrite habilitado
- Extensões PHP: PDO, PDO_MySQL

## 🔧 Instalação

### 1. Clonar/Copiar os arquivos

Copie todos os arquivos para o diretório do seu servidor web.

### 2. Configurar o banco de dados

Edite o arquivo `src/config.php` e configure as credenciais do banco:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'sistema_agendamento');
define('DB_USER', 'seu_usuario');
define('DB_PASS', 'sua_senha');
```

### 3. Criar o banco de dados

Execute o arquivo SQL localizado em `database/schema.sql`:

```bash
mysql -u seu_usuario -p < database/schema.sql
```

Ou importe via phpMyAdmin.

### 4. Configurar permissões

Dê permissão de escrita na pasta de uploads:

```bash
chmod 755 uploads/
```

### 5. Acessar o sistema

Acesse: `http://seu-dominio/sistema-agendamento/public/`

## 🔐 Credenciais Padrão

### Administrador
- **Email:** admin@sistema.com
- **Senha:** admin123

### Médico (exemplo)
- **Email:** joao.silva@clinica.com
- **Senha:** medico123

**⚠️ IMPORTANTE:** Altere as senhas padrão após o primeiro acesso!

## 📁 Estrutura de Diretórios

```
sistema-agendamento/
├── database/
│   └── schema.sql          # Script de criação do banco
├── public/                 # Pasta pública (DocumentRoot)
│   ├── admin/             # Páginas do painel admin
│   ├── medico/            # Páginas do dashboard médico
│   ├── index.php          # Página de login
│   └── logout.php         # Logout
├── src/
│   ├── config.php         # Configurações e conexão
│   └── models/            # Models (Usuario, Agendamento, etc)
├── uploads/               # Arquivos anexos
└── README.md
```

## 🎨 Tecnologias Utilizadas

- **Backend:** PHP com PDO
- **Frontend:** HTML5, CSS3, JavaScript
- **UI Framework:** Tailwind CSS
- **Calendário:** FullCalendar.js
- **Ícones:** Font Awesome
- **Banco de Dados:** MySQL

## 📝 Campos do Agendamento

Baseado no formulário de referência (orthohead.com.br/marcacao/):

### Obrigatórios:
- Nome do solicitante
- Email do solicitante
- Telefone do solicitante
- Nome completo do paciente
- Procedimento
- Data da cirurgia
- Hora da cirurgia
- Hospital
- Médico
- Convênio
- Situação

### Opcionais:
- Material necessário
- Observações
- Arquivo anexo (até 10MB)

## 🔒 Segurança

- Senhas criptografadas com `password_hash()` (bcrypt)
- Proteção contra SQL Injection usando PDO Prepared Statements
- Validação de sessão em todas as páginas protegidas
- Verificação de nível de acesso
- Sanitização de dados de entrada

## 🐛 Solução de Problemas

### Erro de conexão com banco de dados
Verifique as credenciais em `src/config.php`

### Erro ao fazer upload
Verifique as permissões da pasta `uploads/`

### Página em branco
Habilite a exibição de erros no PHP:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## 📞 Suporte

Para dúvidas ou problemas, consulte a documentação ou entre em contato com o desenvolvedor.

## 📄 Licença

Este sistema foi desenvolvido como solução personalizada.

---

**Desenvolvido com ❤️ usando Claude Code**
