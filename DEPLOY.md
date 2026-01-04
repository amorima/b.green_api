# Deploy b.green API (PHP)

## Requisitos

- Servidor com PHP 7.4 ou superior
- mod_rewrite ativado (Apache)
- Acesso SSH ou cPanel File Manager

## Estrutura de Ficheiros

```
b.green_api/
├── .htaccess           # Rewrite rules
├── api/                # Backend PHP
│   ├── config.php
│   ├── functions.php
│   ├── request-key.php
│   ├── calculate.php
│   ├── admin-login.php
│   ├── admin-keys.php
│   └── info.php
├── data/               # Base de dados JSON
│   └── api-keys.json
└── public/             # Frontend
    ├── index.html
    └── admin.html
```

## Passos para Deploy

### 1. Upload dos Ficheiros

- Aceda ao cPanel → File Manager
- Navegue até `public_html` (ou `www`)
- Crie a pasta `api` se não existir
- Faça upload de todos os ficheiros mantendo a estrutura acima

### 2. Configurar Permissões

```bash
chmod 755 api/
chmod 644 api/*.php
chmod 755 data/
chmod 666 data/api-keys.json
```

Ou via cPanel File Manager:

- Pasta `data/`: 755
- Ficheiro `api-keys.json`: 666 (read/write)
- Ficheiros PHP: 644

### 3. Verificar .htaccess

O ficheiro `.htaccess` deve estar na raiz do projeto com:

```apache
RewriteEngine On
RewriteBase /api/

# API Routes
RewriteRule ^api/request-key/?$ api/request-key.php [L]
RewriteRule ^api/calculate/?$ api/calculate.php [L]
RewriteRule ^api/info/?$ api/info.php [L]

# Admin Routes
RewriteRule ^admin/login/?$ api/admin-login.php [L]
RewriteRule ^admin/keys/?$ api/admin-keys.php [QSA,L]
```

### 4. Configurar Segurança

Edite `api/config.php` e altere:

```php
define('ADMIN_PASSWORD', 'NOVA_PASSWORD_SEGURA');
```

### 5. Testar API

Aceda a:

- `https://www.antonioamorim.pt/api/` → Index.html
- `https://www.antonioamorim.pt/api/info` → Informação da API
- `https://www.antonioamorim.pt/admin.html` → Painel admin

## Resolução de Problemas

### Erro 500

- Verificar permissões dos ficheiros
- Verificar se mod_rewrite está ativado
- Ver logs: cPanel → Errors

### API Keys não guardam

- Verificar permissões da pasta `data/`
- Verificar se `api-keys.json` tem permissão 666

### CORS Errors

Os headers já estão configurados em `config.php`. Se persistir erro:

- Adicionar ao `.htaccess`:

```apache
Header set Access-Control-Allow-Origin "*"
```

## Endpoints da API

- `POST /api/request-key` - Obter API Key
- `POST /api/calculate` - Calcular emissões (requer X-API-Key)
- `GET /api/info` - Informação da API
- `POST /admin/login` - Login admin
- `GET /admin/keys` - Listar keys (admin)
- `PUT /admin/keys/{key}/block` - Bloquear key (admin)
- `DELETE /admin/keys/{key}` - Eliminar key (admin)

## Suporte

António Amorim - https://www.antonioamorim.pt
