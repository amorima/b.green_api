# b.green API 🌿

API REST desenvolvida em PHP para cálculo de pegada ecológica e emissões de carbono de atividades quotidianas. Inclui um sistema de gestão de API Keys e um painel de administração.

## 🚀 Funcionalidades

- **Cálculo de Emissões:** Suporte para transportes, energia, alimentação, resíduos e dispositivos eletrónicos.
- **Gestão de API Keys:** Sistema de autenticação via chave, com registo por email.
- **Admin Panel:** Interface web para gestão, bloqueio e monitorização de chaves de API.
- **Segurança:** Proteção de ficheiros de dados, validação de inputs e autenticação via Headers.
- **Sem Base de Dados SQL:** Utiliza ficheiros JSON para persistência de dados, facilitando o deploy simples.

## 🛠️ Stack Tecnológica

- **Backend:** PHP 7.4+
- **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
- **Armazenamento:** JSON (`data/api-keys.json`)
- **Servidor:** Apache (com `.htaccess` para routing)

## 📦 Instalação e Configuração

1. **Requisitos:** Servidor Apache com PHP e `mod_rewrite` ativo (ex: XAMPP, MAMP, Laragon).
2. **Setup:**
   - Coloque os ficheiros na pasta pública do servidor (ex: `htdocs` ou `www`).
   - Certifique-se que a pasta `data/` tem permissões de escrita.
3. **Acesso:**
   - Abra o browser em `http://localhost/b.green_api/` (ou o caminho correspondente).

## 📚 Documentação da API

### 1. Solicitar API Key
Gera uma nova chave de acesso associada a um email.

- **Endpoint:** `POST /request-key`
- **Body (JSON):**
  ```json
  {
    "email": "user@example.com"
  }
  ```
- **Resposta:**
  ```json
  {
    "success": true,
    "message": "API Key criada com sucesso",
    "data": {
      "key": "bgk_...",
      "email": "user@example.com"
    }
  }
  ```

### 2. Calcular Emissões
Realiza o cálculo de CO2e com base no tipo de atividade.

- **Endpoint:** `POST /calculate`
- **Headers:**
  - `X-API-Key`: `sua_api_key_aqui`
- **Body (JSON) - Exemplo Genérico:**
  ```json
  {
    "type": "car_gasoline",
    "amount": 100
  }
  ```
- **Body (JSON) - Exemplo Dispositivos:**
  ```json
  {
    "type": "laptop",
    "minutes": 60
  }
  ```
- **Tipos Suportados (`type`):**
  - **Transportes:** `car_gasoline`, `car_diesel`, `car_electric`, `bus`, `train`, `plane_short`, `plane_long`
  - **Energia:** `electricity`, `natural_gas`, `heating_oil`
  - **Alimentação:** `meal_meat`, `meal_vegetarian`, `meal_vegan`
  - **Resíduos:** `waste_general`, `waste_recycled`, `water`
  - **Dispositivos (usar `minutes`):** `laptop`, `desktop`, `television`, `air_conditioner`, `refrigerator`, `washing_machine`, `dishwasher`

### 3. Informações da API
- **Endpoint:** `GET /info`
- **Resposta:** Detalhes sobre a versão e autor.

## 🔐 Painel de Administração

Acesse a `/admin.html` para gerir as chaves.

- **Password Padrão:** `admin123`
- **Funcionalidades:**
  - Visualizar todas as chaves geradas.
  - Ver estatísticas de uso (número de pedidos, último acesso).
  - Bloquear/Desbloquear chaves.
  - Eliminar chaves.

## 📂 Estrutura do Projeto

```
/
├── data/               # Armazenamento de dados (protegido)
│   └── api-keys.json   # Base de dados de chaves
├── public/             # (Opcional) Ficheiros públicos antigos
├── .htaccess           # Regras de reescrita e segurança
├── admin.html          # Frontend do Painel Admin
├── admin-keys.php      # API para gestão de chaves
├── admin-login.php     # API para login de admin
├── calculate.php       # Lógica de cálculo de emissões
├── config.php          # Configurações globais (senhas, caminhos)
├── functions.php       # Funções auxiliares (helpers)
├── index.html          # Landing page e documentação interativa
├── info.php            # Endpoint de informações
├── request-key.php     # Endpoint de registo
└── readme.md           # Documentação do projeto
```

## 🛡️ Segurança

- O acesso direto à pasta `data/` e ficheiros `.json` é bloqueado via `.htaccess`.
- As rotas da API são geridas via `RewriteRule` para URLs limpos.
- Autenticação de Admin feita via Header `X-Admin-Password`.

---
Desenvolvido no âmbito da disciplina de Programação Web I.
