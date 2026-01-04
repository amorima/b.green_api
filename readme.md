# 🌍 EcoTracker API

> **Motor de cálculo de pegada ecológica (Backend)**
> Desenvolvido no âmbito da disciplina de PW1.

Esta API RESTful é responsável por processar dados de consumo (energia, transportes, alimentação) e convertê-los em emissões de CO2 equivalente (CO2e), utilizando fatores de conversão baseados em normas internacionais atuais.

---

## 🚀 Funcionalidades

- **Cálculo Híbrido:** Suporta cálculos baseados em **quantidade** (litros, km, kg) e baseados em **tempo de uso** (para equipamentos elétricos).
- **Dados Reais:** Utiliza fatores de emissão oficiais de 2024 (Governo do Reino Unido e Agência Europeia do Ambiente).
- **Segurança:** Implementação de _Rate Limiting_ para prevenção de abusos e CORS configurado.
- **Performance:** Arquitetura leve em Node.js/Express.

---

## 📚 Metodologia e Fatores de Emissão

A precisão dos cálculos baseia-se em fontes científicas verificáveis. Abaixo apresentam-se as tabelas de referência utilizadas pelo algoritmo.

### 1. Fatores de Conversão Direta (Kg CO2e / Unidade)

_Fontes: UK Gov GHG Conversion Factors (2024) & EEA (2023)._

| Chave (API `type`) | Categoria   | Fator (kg CO2e) | Unidade Base | Notas                    |
| :----------------- | :---------- | :-------------- | :----------- | :----------------------- |
| `electricity_pt`   | Energia     | **0.188**       | por kWh      | Mix energético PT (EEA)  |
| `natural_gas`      | Energia     | **0.202**       | por kWh      | Gás de cidade            |
| `lpg`              | Energia     | **1.557**       | por Litro    | Gás de botija            |
| `heating_oil`      | Energia     | **2.758**       | por Litro    | Gasóleo aquecimento      |
| `wood_pellets`     | Energia     | **0.015**       | por kWh      | Biomassa                 |
| `car_gasoline`     | Transporte  | **0.170**       | por km       | Carro médio              |
| `car_diesel`       | Transporte  | **0.171**       | por km       | Carro médio              |
| `car_electric`     | Transporte  | **0.047**       | por km       | Ciclo de vida + Carga    |
| `bus_urban`        | Transporte  | **0.096**       | por km       | Autocarro urbano         |
| `train`            | Transporte  | **0.035**       | por km       | Comboio nacional         |
| `plane_short`      | Transporte  | **0.244**       | por km       | Voos < 3700km            |
| `plane_long`       | Transporte  | **0.193**       | por km       | Voos > 3700km            |
| `beef`             | Alimentação | **60.0**        | por kg       | Média global             |
| `chicken`          | Alimentação | **6.0**         | por kg       | Média global             |
| `pork`             | Alimentação | **7.0**         | por kg       | Média global             |
| `vegetables`       | Alimentação | **0.4**         | por kg       | Média global             |
| `water_supply`     | Água        | **0.149**       | por m3       | Abastecimento            |
| `waste_landfill`   | Resíduos    | **0.467**       | por kg       | Aterro sanitário         |
| `waste_recycle`    | Resíduos    | **0.021**       | por kg       | Processamento reciclagem |

### 2. Estimativa de Potência de Equipamentos

Para cálculos baseados em tempo (`minutes`), a API converte o tempo em energia (kWh) usando a potência média estimada. Quando necessário, é possível especificar uma potência customizada em watts.

| Chave (API `type`) | Equipamento            | Potência Média Padrão (Watts) |
| :----------------- | :--------------------- | :---------------------------- |
| `laptop`           | Portátil               | 50 W                          |
| `desktop`          | Computador Fixo        | 200 W                         |
| `smartphone`       | Carregamento Telemóvel | 5 W                           |
| `tv_led`           | Televisão LED          | 100 W                         |
| `fridge`           | Frigorífico            | 150 W                         |
| `air_conditioner`  | Ar Condicionado        | 1000 W                        |
| `shower_electric`  | Chuveiro Elétrico      | 3500 W                        |

**Nota:** Se desejar usar uma potência diferente da padrão, pode enviar o parâmetro `power_watts` no pedido. A API usará esse valor em vez do padrão.

#### Exemplo com Potência Customizada

```json
{
  "type": "laptop",
  "minutes": 180,
  "power_watts": 75
}
```

Neste exemplo, a API usa 75 W em vez dos 50 W padrão para o laptop, resultando num cálculo mais preciso.

---

## 🛠️ Especificação da API

### Endpoint Principal

`POST /api/calculate`

Recebe os dados de consumo e devolve a pegada de carbono calculada.

#### Parâmetros do Pedido (JSON)

| Campo         | Tipo   | Obrigatório   | Descrição                                                                  |
| :------------ | :----- | :------------ | :------------------------------------------------------------------------- |
| `type`        | String | Sim           | A chave do tipo de consumo (ver tabelas acima).                            |
| `amount`      | Number | Condicional\* | Quantidade (km, litros, kg). _Obrigatório se não for equipamento._         |
| `minutes`     | Number | Condicional\* | Tempo de uso em minutos. _Obrigatório p/ equipamentos._                    |
| `power_watts` | Number | Opcional      | Potência customizada em watts para equipamentos. _Se omitido, usa padrão._ |

#### Exemplo de Pedido (Transporte)

```json
{
  "type": "car_diesel",
  "amount": 150
}
```

Exemplo de Pedido (Equipamento)

```json
{
  "type": "laptop",
  "minutes": 180
}
```

Exemplo de Resposta (Sucesso)

```json
{
  "success": true,
  "data": {
    "type": "car_diesel",
    "input": 150,
    "carbon_kg": 25.65,
    "source": "UK Gov 2024 & EEA",
    "methodology": "Direct Multiplication (Factor * Amount)"
  },
  "timestamp": "2024-05-20T10:00:00.000Z"
}
```

---

## 💻 Instalação e Deploy

### Pré-requisitos

- Node.js (v14+)
- NPM

### Instalação Local

Clonar o repositório ou copiar os ficheiros.

Instalar dependências:

```bash
npm install
```

Iniciar o servidor:

```bash
npm start
```

### Deploy em Servidor (Produção)

Recomenda-se o uso do gestor de processos PM2 para manter a API online 24/7:

```bash
# Instalar PM2 globalmente
sudo npm install pm2 -g

# Iniciar a aplicação
pm2 start server.js --name "eco-api"

# Configurar arranque automático no boot (reinícios do servidor)
pm2 startup
pm2 save
```

---

## 📖 Referências Bibliográficas

Os dados utilizados neste projeto foram recolhidos das seguintes fontes oficiais:

- Department for Energy Security and Net Zero (2024). Greenhouse gas reporting: conversion factors 2024. Disponível em: gov.uk.
- European Environment Agency (2023). Greenhouse gas emission intensity of electricity generation in Europe. Disponível em: eea.europa.eu.
- ADEME / Energy Use Calculator. Estimativas de potência média de eletrodomésticos para conversão em kWh.

Licença: Projeto académico para fins educativos.
