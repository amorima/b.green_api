# b.green API

API REST para cálculo de emissões de carbono de atividades quotidianas.

## Stack Tecnológica

- **Backend:** PHP 7.4+
- **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
- **Base de Dados:** JSON (ficheiro local)
- **Servidor:** Apache com mod_rewrite

## Características

- Cálculo de emissões para 16 tipos de atividades
- Sistema de API Keys por email (uma key por email)
- Rate limiting (100 requests/15min por key)
- Painel de administração para gestão de keys
- Documentação interativa completa
- Tracking de uso por API Key
- Bloqueio/desbloqueio de keys via admin

## Tipos de Atividades Suportados

### Transportes

- Carro (gasolina, diesel, elétrico), autocarro, comboio, avião

### Energia

- Eletricidade, gás natural, gasóleo de aquecimento

### Alimentação

- Refeições (carne, vegetariana, vegan)

### Resíduos

- Lixo geral, reciclagem, consumo de água

### Dispositivos (por tempo de uso)

- Portátil, desktop, TV, ar condicionado, frigorífico, máquinas de lavar

## Instalação Local (PHP)

```bash
# Clonar repositório
git clone https://github.com/amorima/b.green_api.git
cd b.green_api

# Iniciar servidor PHP local
php -S localhost:8000
```

A API estará disponível em `http://localhost:8000`

## Documentação

Aceda a `/` ou `/public/index.html` para ver:

- Guia de obtenção de API Key
- Teste interativo da API
- Documentação completa de todos os endpoints
- Tabelas com fatores de emissão
- Exemplos de código (JavaScript, cURL, Python)
- Códigos de resposta HTTP

## Endpoints da API

### Públicos

- `POST /api/request-key` - Criar ou recuperar API Key por email
- `GET /api/info` - Informação sobre tipos e dispositivos disponíveis

### Autenticados (X-API-Key)

- `POST /api/calculate` - Calcular emissões de carbono

### Admin (X-Admin-Password)

- `POST /admin/login` - Autenticação admin
- `GET /admin/keys` - Listar todas as API Keys
- `PUT /admin/keys/{key}/block` - Bloquear/desbloquear key
- `DELETE /admin/keys/{key}` - Eliminar key

## Exemplo de Uso

### Obter API Key

```bash
curl -X POST https://www.antonioamorim.pt/api/request-key \
  -H "Content-Type: application/json" \
  -d '{"email":"seu@email.com"}'
```

### Calcular Emissões

```bash
curl -X POST https://www.antonioamorim.pt/api/calculate \
  -H "Content-Type: application/json" \
  -H "X-API-Key: bgk_sua_key" \
  -d '{"type":"car_gasoline","amount":100}'
```

## Painel Admin

Aceda a `/admin.html` para:

- Ver todas as API Keys registadas
- Visualizar estatísticas (total keys, keys ativas, total pedidos)
- Bloquear/desbloquear keys
- Eliminar keys
- Ver último uso e contagem de pedidos por key

**Password padrão:** `bgreen2026` (alterar em produção no ficheiro `api/config.php`)

## Deploy em Servidor PHP

Ver [DEPLOY.md](DEPLOY.md) para instruções detalhadas de:

- Upload de ficheiros via cPanel
- Configuração de permissões
- Configuração do .htaccess
- Alteração da password de admin
- Resolução de problemas comuns

## Segurança

- API Keys únicas por email
- Autenticação obrigatória para cálculos
- Admin protegido por password
- Rate limiting por key
- Logs de uso (requests, lastUsed)
- Possibilidade de bloqueio de keys

## Estrutura do Projeto

```
b.green_api/
├── .htaccess           # Apache rewrite rules
├── api/                # Backend PHP
│   ├── config.php      # Configuração e constantes
│   ├── functions.php   # Funções auxiliares
│   ├── request-key.php # Endpoint: criar/obter key
│   ├── calculate.php   # Endpoint: calcular emissões
│   ├── admin-login.php # Endpoint: login admin
│   ├── admin-keys.php  # Endpoint: gestão de keys
│   └── info.php        # Endpoint: informação API
├── data/               # Base de dados JSON
│   └── api-keys.json   # Armazenamento de keys
├── public/             # Frontend
│   ├── index.html      # Página principal + docs
│   └── admin.html      # Painel de administração
├── DEPLOY.md           # Guia de deploy
└── readme.md           # Este ficheiro
```

## Autor

António Amorim

- Website: https://www.antonioamorim.pt
- GitHub: https://github.com/amorima
