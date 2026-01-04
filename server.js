/**
 * b-green EcoTracker API - Motor de Cálculo de Emissões de Carbono (CO2e)
 * Projeto: PW1 Eco Tracker
 * * Descrição:
 * API RESTful desenvolvida em Node.js/Express. Responsável por receber dados
 * de consumo (transportes, energia, resíduos) e convertê-los em impacto ambiental
 * utilizando fatores de emissão padronizados internacionalmente.
 * * Funcionalidades:
 * - Acesso público (CORS Open) com proteção contra abuso (Rate Limiting).
 * - Base de dados de fatores baseada no UK Gov (2024) e EEA (Agência Europeia do Ambiente).
 * - Cálculo híbrido: por quantidade (litros/km) e por tempo de uso (dispositivos elétricos).
 */

const express = require("express");
const bodyParser = require("body-parser");
const cors = require("cors");
const rateLimit = require("express-rate-limit");

const app = express();
const PORT = process.env.PORT || 3000;

// =================================================================
// 1. CONFIGURAÇÃO DE SEGURANÇA E ACESSO
// =================================================================

// CORS: Permite que qualquer origem aceda à API (Open API).
// Essencial para que o frontend no GitHub Pages comunique com este servidor.
app.use(cors());

// Rate Limiter: Medida de segurança para APIs públicas.
// Limita cada IP a 100 pedidos a cada 15 minutos para prevenir ataques DoS.
const limiter = rateLimit({
  windowMs: 15 * 60 * 1000,
  max: 100,
  message: {
    error: "Limite de pedidos excedido. Tente novamente em 15 minutos.",
  },
  standardHeaders: true,
  legacyHeaders: false,
});
app.use(limiter);

// Body Parser: Processa o JSON recebido no corpo do pedido.
app.use(bodyParser.json());

// =================================================================
// 2. BASE DE DADOS DE FATORES DE EMISSÃO
// =================================================================

/**
 * Fatores de Conversão (kg CO2e por Unidade).
 * Fontes:
 * - Department for Energy Security and Net Zero (UK Gov 2024)
 * - European Environment Agency (EEA) - Intensidade elétrica PT
 */
const CARBON_FACTORS = {
  // --- Energia Doméstica ---
  electricity_pt: 0.188, // kg/kWh (Mix Portugal - média recente)
  natural_gas: 0.202, // kg/kWh
  lpg: 1.557, // kg/litro (Gás de botija)
  heating_oil: 2.758, // kg/litro (Gasóleo aquecimento)
  wood_pellets: 0.015, // kg/kWh (Biomassa)

  // --- Transportes (kg/km) ---
  car_gasoline: 0.17, // Carro médio gasolina
  car_diesel: 0.171, // Carro médio diesel
  car_electric: 0.047, // Carro elétrico (ciclo vida)
  bus_urban: 0.096, // Autocarro urbano
  train: 0.035, // Comboio
  plane_short: 0.244, // Voo < 3700km (inc. forçamento radiativo)
  plane_long: 0.193, // Voo > 3700km

  // --- Resíduos e Água ---
  water_supply: 0.149, // kg/m3
  waste_landfill: 0.467, // kg/kg (Aterro)
  waste_recycle: 0.021, // kg/kg (Reciclagem)

  // --- Alimentação (kg/kg - Média Global OurWorldInData) ---
  beef: 60.0,
  chicken: 6.0,
  pork: 7.0,
  vegetables: 0.4,
};

/**
 * Potência Média de Dispositivos (Watts).
 * Utilizado para converter tempo de uso (minutos) em energia (kWh).
 */
const DEVICE_POWER_WATTS = {
  laptop: 50,
  desktop: 200,
  smartphone: 5,
  tv_led: 100,
  fridge: 150,
  air_conditioner: 1000,
  shower_electric: 3500,
};

// =================================================================
// 3. ENDPOINT DE CÁLCULO
// =================================================================

app.post("/api/calculate", (req, res) => {
  const { type, amount, minutes, power_watts } = req.body;

  // Validação inicial
  if (!type) {
    return res.status(400).json({ error: "O campo 'type' é obrigatório." });
  }

  let carbonFootprint = 0;
  let details = {};

  // CENÁRIO A: Cálculo Direto (Combustíveis, Km, Kg)
  // Fórmula: Quantidade * Fator
  if (CARBON_FACTORS[type] !== undefined) {
    if (amount === undefined || amount < 0) {
      return res
        .status(400)
        .json({ error: "É necessário um 'amount' válido." });
    }

    carbonFootprint = amount * CARBON_FACTORS[type];
    details = { methodology: "Direct Multiplication (Factor * Amount)" };

    // CENÁRIO B: Cálculo de Dispositivos (Tempo -> Energia -> CO2)
    // Fórmula: (Watts / 1000) * Horas * Fator Eletricidade PT
  } else if (DEVICE_POWER_WATTS[type] !== undefined) {
    if (minutes === undefined || minutes < 0) {
      return res
        .status(400)
        .json({ error: "É necessário 'minutes' válido para equipamentos." });
    }

    // Se power_watts for fornecido, usa esse valor. Caso contrário, usa o valor padrão.
    const effectivePower =
      power_watts !== undefined && power_watts > 0
        ? power_watts
        : DEVICE_POWER_WATTS[type];

    const hours = minutes / 60;
    const kwh = (effectivePower / 1000) * hours;
    carbonFootprint = kwh * CARBON_FACTORS["electricity_pt"];

    details = {
      methodology: "Time-based Energy Estimation",
      power_watts: effectivePower,
      is_custom_power: power_watts !== undefined && power_watts > 0,
      standard_power_watts: DEVICE_POWER_WATTS[type],
      estimated_kwh: parseFloat(kwh.toFixed(4)),
    };
  } else {
    return res.status(404).json({
      error: "Tipo de consumo não encontrado.",
      available_types: [
        ...Object.keys(CARBON_FACTORS),
        ...Object.keys(DEVICE_POWER_WATTS),
      ],
    });
  }

  // Resposta final formatada
  return res.json({
    success: true,
    data: {
      type: type,
      input: amount || minutes,
      carbon_kg: parseFloat(carbonFootprint.toFixed(3)),
      source: "UK Gov 2024 & EEA",
      ...details,
    },
    timestamp: new Date().toISOString(),
  });
});

// Inicialização do Servidor
app.listen(PORT, () => {
  console.log(`\n--- EcoTracker API ---`);
  console.log(`Status: Online`);
  console.log(`Porta: ${PORT}`);
  console.log(
    `Fatores carregados: ${
      Object.keys(CARBON_FACTORS).length +
      Object.keys(DEVICE_POWER_WATTS).length
    }`
  );
});
