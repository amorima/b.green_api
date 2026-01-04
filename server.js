/**
 * b.green API - Carbon Emissions Calculator
 * API REST para cálculo de emissões de CO2
 */

const express = require("express");
const bodyParser = require("body-parser");
const cors = require("cors");
const rateLimit = require("express-rate-limit");
const fs = require("fs").promises;
const path = require("path");
const crypto = require("crypto");

const ADMIN_PASSWORD = "bgreen2026";

const app = express();
const PORT = process.env.PORT || 3000;
const API_KEYS_FILE = path.join(__dirname, "data", "api-keys.json");

app.use(cors());
app.use(bodyParser.json());
app.use(express.static("public"));

const limiter = rateLimit({
  windowMs: 15 * 60 * 1000,
  max: 100,
  standardHeaders: true,
  legacyHeaders: false,
  message: {
    success: false,
    error: "Limite de pedidos excedido (100 req/15min)",
  },
});

const CARBON_FACTORS = {
  car_gasoline: 0.17,
  car_diesel: 0.185,
  car_electric: 0.045,
  bus: 0.089,
  train: 0.041,
  plane_short: 0.255,
  plane_long: 0.195,
  electricity: 0.188,
  natural_gas: 0.203,
  heating_oil: 0.265,
  meal_meat: 7.2,
  meal_vegetarian: 2.0,
  meal_vegan: 1.5,
  waste_general: 0.45,
  waste_recycled: 0.021,
  water: 0.149,
};

const DEVICE_POWER_WATTS = {
  laptop: 50,
  desktop: 200,
  television: 150,
  air_conditioner: 3500,
  refrigerator: 150,
  washing_machine: 500,
  dishwasher: 1800,
};

async function loadApiKeys() {
  try {
    const data = await fs.readFile(API_KEYS_FILE, "utf8");
    return JSON.parse(data);
  } catch {
    return [];
  }
}

async function saveApiKeys(keys) {
  await fs.writeFile(API_KEYS_FILE, JSON.stringify(keys, null, 2));
}

function generateApiKey() {
  return "bgk_" + crypto.randomBytes(24).toString("hex");
}

async function authenticateApiKey(req, res, next) {
  const apiKey = req.headers["x-api-key"];
  if (!apiKey) {
    return res
      .status(401)
      .json({
        success: false,
        error: "API Key obrigatória. Use header X-API-Key",
      });
  }
  const keys = await loadApiKeys();
  const keyData = keys.find((k) => k.key === apiKey);
  if (!keyData) {
    return res.status(401).json({ success: false, error: "API Key inválida" });
  }
  if (keyData.blocked) {
    return res.status(403).json({ success: false, error: "API Key bloqueada" });
  }
  keyData.requests = (keyData.requests || 0) + 1;
  keyData.lastUsed = new Date().toISOString();
  await saveApiKeys(keys);
  next();
}

app.get("/", (req, res) =>
  res.sendFile(path.join(__dirname, "public", "index.html"))
);

app.post("/api/request-key", async (req, res) => {
  const { email } = req.body;
  if (!email || !email.includes("@")) {
    return res
      .status(400)
      .json({ success: false, error: "Email válido obrigatório" });
  }
  const keys = await loadApiKeys();
  const existing = keys.find((k) => k.email === email);
  if (existing) {
    return res.json({
      success: true,
      message: "Key existente retornada",
      data: {
        key: existing.key,
        email: existing.email,
        createdAt: existing.createdAt,
        requests: existing.requests || 0,
      },
    });
  }
  const newKey = {
    key: generateApiKey(),
    email,
    createdAt: new Date().toISOString(),
    requests: 0,
    blocked: false,
    lastUsed: null,
  };
  keys.push(newKey);
  await saveApiKeys(keys);
  res.json({
    success: true,
    message: "API Key criada com sucesso",
    data: { key: newKey.key, email: newKey.email, createdAt: newKey.createdAt },
  });
});

app.post("/api/calculate", authenticateApiKey, limiter, (req, res) => {
  const { type, amount, minutes, power_watts } = req.body;
  if (!type)
    return res
      .status(400)
      .json({ success: false, error: "Campo type obrigatório" });

  const isDevice = DEVICE_POWER_WATTS.hasOwnProperty(type);
  if (isDevice) {
    if (!minutes || minutes <= 0)
      return res
        .status(400)
        .json({ success: false, error: "Campo minutes obrigatório" });
    const effectivePower =
      power_watts && power_watts > 0 ? power_watts : DEVICE_POWER_WATTS[type];
    const kwh = (effectivePower / 1000) * (minutes / 60);
    const carbonKg = kwh * CARBON_FACTORS.electricity;
    return res.json({
      success: true,
      data: {
        type,
        minutes,
        power_watts: effectivePower,
        is_custom_power: power_watts && power_watts > 0,
        standard_power_watts: DEVICE_POWER_WATTS[type],
        kwh: parseFloat(kwh.toFixed(3)),
        carbon_kg: parseFloat(carbonKg.toFixed(3)),
        unit: "kg CO2e",
      },
    });
  }

  const factor = CARBON_FACTORS[type];
  if (!factor)
    return res
      .status(400)
      .json({ success: false, error: "Tipo não reconhecido" });
  if (!amount || amount <= 0)
    return res
      .status(400)
      .json({ success: false, error: "Campo amount obrigatório" });
  const carbonKg = amount * factor;
  res.json({
    success: true,
    data: {
      type,
      amount,
      factor,
      carbon_kg: parseFloat(carbonKg.toFixed(3)),
      unit: "kg CO2e",
    },
  });
});

app.post("/admin/login", (req, res) => {
  if (req.body.password !== ADMIN_PASSWORD)
    return res
      .status(401)
      .json({ success: false, error: "Password incorreta" });
  res.json({ success: true, message: "Login efetuado" });
});

app.get("/admin/keys", async (req, res) => {
  if (req.headers["x-admin-password"] !== ADMIN_PASSWORD)
    return res.status(401).json({ success: false, error: "Não autenticado" });
  const keys = await loadApiKeys();
  res.json({ success: true, data: keys });
});

app.put("/admin/keys/:key/block", async (req, res) => {
  if (req.headers["x-admin-password"] !== ADMIN_PASSWORD)
    return res.status(401).json({ success: false, error: "Não autenticado" });
  const keys = await loadApiKeys();
  const keyData = keys.find((k) => k.key === req.params.key);
  if (!keyData)
    return res
      .status(404)
      .json({ success: false, error: "Key não encontrada" });
  keyData.blocked = req.body.blocked;
  await saveApiKeys(keys);
  res.json({
    success: true,
    message: req.body.blocked ? "Key bloqueada" : "Key desbloqueada",
    data: keyData,
  });
});

app.delete("/admin/keys/:key", async (req, res) => {
  if (req.headers["x-admin-password"] !== ADMIN_PASSWORD)
    return res.status(401).json({ success: false, error: "Não autenticado" });
  const keys = await loadApiKeys();
  const filtered = keys.filter((k) => k.key !== req.params.key);
  if (filtered.length === keys.length)
    return res
      .status(404)
      .json({ success: false, error: "Key não encontrada" });
  await saveApiKeys(filtered);
  res.json({ success: true, message: "Key eliminada" });
});

app.get("/api/info", (req, res) => {
  res.json({
    success: true,
    data: {
      name: "b.green API",
      version: "1.0.0",
      types: Object.keys(CARBON_FACTORS),
      devices: Object.keys(DEVICE_POWER_WATTS),
      rateLimit: "100 requests / 15 minutes per key",
      documentation: "https://www.antonioamorim.pt/api/",
    },
  });
});

app.listen(PORT, () =>
  console.log(
    ` b.green API iniciada na porta ${PORT}\n https://www.antonioamorim.pt/api`
  )
);
