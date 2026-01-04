# 📋 Guia de Deploy - b.green API

## Pré-requisitos

- Node.js 18+ instalado
- Acesso SSH ao servidor cPanel
- Domínio configurado: https://www.antonioamorim.pt/api

## Passo 1: Upload dos Ficheiros

1. Aceda ao File Manager do cPanel
2. Navegue para `public_html/api/`
3. Faça upload dos ficheiros:
   - server.js
   - package.json
   - pasta `public/` (index.html, admin.html)
   - pasta `data/` (api-keys.json)
   - pasta `admin/` (ficheiros admin)

## Passo 2: Instalar Dependências

Via terminal SSH:

```bash
cd ~/public_html/api
npm install
```

## Passo 3: Configurar Node.js Application (cPanel)

1. Aceda a "Setup Node.js App" no cPanel
2. Configurações:

   - **Nome:** bgreen-api
   - **Versão Node.js:** 18.x ou superior
   - **Modo:** Production
   - **Application root:** public_html/api
   - **Application URL:** api (ou deixar vazio)
   - **Application startup file:** server.js
   - **Porta:** (gerada automaticamente, ex: 3000)

3. Clique em "Create"

## Passo 4: Iniciar Aplicação

```bash
cd ~/public_html/api
pm2 start server.js --name "bgreen-api"
pm2 save
pm2 startup
```

## Passo 5: Testar

Aceda a:

- **API:** https://www.antonioamorim.pt/api/api/calculate
- **Interface:** https://www.antonioamorim.pt/api/
- **Admin:** https://www.antonioamorim.pt/api/admin.html

## Comandos Úteis

```bash
# Ver logs
pm2 logs bgreen-api

# Reiniciar
pm2 restart bgreen-api

# Parar
pm2 stop bgreen-api

# Status
pm2 status
```

## Estrutura de Ficheiros

```
public_html/api/
├── server.js           # Backend principal
├── package.json        # Dependências
├── public/             # Frontend
│   ├── index.html      # Interface principal
│   └── admin.html      # Painel admin
└── data/               # Base de dados
    └── api-keys.json   # Keys + emails + requests
```

## Segurança

- API Keys são obrigatórias para todos os pedidos
- Pedidos limitados a 100/15min por key
- Keys bloqueadas não podem fazer pedidos
- Admin password: definir em `server.js` (linha 15)

## Suporte

Email: b.green@gmail.com
