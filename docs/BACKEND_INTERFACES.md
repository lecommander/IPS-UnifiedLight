# Backend Interface Reference

Dokumentation aller IPS-API-Funktionen, die für die Light-Steuerung pro Backend verwendet werden.

---

## 1. DMX (IPS built-in)

| Funktion | Signatur | Verwendung |
|----------|----------|------------|
| `DMX_SetValue` | `DMX_SetValue(int $instanceID, int $channel, int $value)` | Instantanes Schalten. Value: 0–255 |
| `DMX_FadeChannel` | `DMX_FadeChannel(int $instanceID, int $channel, int $value, float $seconds)` | Fade zu Zielwert. Value: 0–255 |

**Eigenschaften:**
- Channel: 1–512
- Value Range: 0–255 (muss von 0–100% gemappt werden: `value = level * 255 / 100`)
- Fade Time: 0 = instant, sonst Sekunden

---

## 2. Shelly / Zigbee2MQTT (Variable-basiert)

| Funktion | Signatur | Verwendung |
|----------|----------|------------|
| `RequestAction` | `RequestAction(int $variableID, mixed $value)` | Power: bool, Brightness: int 0–100 |
| `IPS_VariableExists` | `IPS_VariableExists(int $variableID): bool` | Validierung |

**Eigenschaften:**
- Power Variable: boolean (true/false)
- Brightness Variable: integer (0–100)
- Variablen werden vom User im Object Tree ausgewählt

---

## 3. KNX (Prio 1)

### IPS-API Funktionen

| Funktion | Signatur | DPT | Verwendung |
|----------|----------|-----|------------|
| `EIB_Switch` | `EIB_Switch(string $groupAddress, bool $on)` | DPT 1 | Schalten (on/off) |
| `EIB_DimValue` | `EIB_DimValue(string $groupAddress, int $value)` | DPT 5 | Absolut-Dimmen (0–100%) |
| `EIB_DimControl` | `EIB_DimControl(string $groupAddress, int $direction, int $data)` | DPT 3 | Relativ-Dimmen (step/binary) |
| `EIB_Value` | `EIB_Value(string $groupAddress, float $value)` | DPT 9 | Float-Werte (2-Byte) |
| `KNX_RequestStatus` | `KNX_RequestStatus(int $instanceID)` | — | Status-Synchronisation nach Start |

### Konfiguration

| Property | Typ | Beschreibung |
|----------|-----|--------------|
| `KNXInstanceID` | integer | KNX/IP Gateway Instance ID |
| `KNXSwitchAddress` | string | Gruppenadresse für Schalten (z.B. "1/2/3") |
| `KNXDimAddress` | string | Gruppenadresse für Dimmen (z.B. "1/2/4") |
| `KNXStatusAddress` | string | Gruppenadresse für Status-Rückmeldung (optional) |

### Mapping

- **Power ON**: `EIB_Switch("1/2/3", true)`
- **Power OFF**: `EIB_Switch("1/2/3", false)`
- **Brightness 0–100**: `EIB_DimValue("1/2/4", $level)` (DPT 5, 0–100%)
- **Fade**: KNX unterstützt kein natives Fade — instant setzen

### WLED/ESP API (HTTP)

| Methode | Endpoint | Body | Verwendung |
|---------|----------|------|------------|
| POST | `/json/state` | `{"on":true,"bri":128}` | Schalten + Dimmen |
| POST | `/json/state` | `{"on":false}` | Ausschalten |
| POST | `/json/state` | `{"on":true,"bri":128,"transition":5}` | Fade mit Transition (Sekunden * 10) |

### Konfiguration

| Property | Typ | Beschreibung |
|----------|-----|--------------|
| `WLEDIPAddress` | string | IP-Adresse oder Hostname des WLED-Controllers |
| `WLEDTransitionTime` | float | Default Transition Time in Sekunden (0 = instant) |

### Mapping

- **Power ON + Brightness**: `HTTP_Request("http://$ip/json/state", POST, '{"on":true,"bri":' . $level . '}')`
- **Power OFF**: `HTTP_Request("http://$ip/json/state", POST, '{"on":false}')`
- **Fade**: `"transition": $seconds * 10` im JSON (WLED erwartet Deci-Sekunden)

---

## 6. WLED (HTTP/MQTT) (Prio 3)

Siehe oben — WLED unterstützt sowohl HTTP als auch MQTT.

### MQTT Variante

| Topic | Payload | Verwendung |
|-------|---------|------------|
| `wled/[device]/api` | `{"on":true,"bri":128}` | Schalten + Dimmen |
| `wled/[device]/api` | `{"on":false}` | Ausschalten |

---

## IPS HTTP API (allgemein)

Für Backends ohne native IPS-Funktion (Tasmota HTTP, WLED HTTP):

| Funktion | Signatur | Verwendung |
|----------|----------|------------|
| `IPS_GetURLContent` | `IPS_GetURLContent(string $url, array $options): string` | HTTP GET/POST Requests |
| `HTTPRequest` | Alternative via `curl` in PHP | — |

Empfehlung: `IPS_GetURLContent()` mit POST-Optionen verwenden.

---

## IPS MQTT API (allgemein)

Für Backends ohne native IPS-MQTT-Module:

| Funktion | Signatur | Verwendung |
|----------|----------|------------|
| `MQTT_Publish` | `MQTT_Publish(int $serverInstanceID, string $topic, mixed $payload)` | MQTT Publish auf Server-Instanz |

**Achtung**: IPS hat kein direktes `MQTT_Publish()` als globale Funktion. Publishing erfolgt über `RequestAction()` auf eine MQTT-Client-Variable oder über ein benutzerdefiniertes Script das die MQTT-Client-Instanz nutzt.

Alternative: Eigenes MQTT-Publishing via PHP `php-mqtt/client` oder IPS interne MQTT-Instanz.

---

**Letzte Aktualisierung:** 2026-04-06
**Status:** Basierend auf Symcon-Dokumentation und Community-Recherche. API-Signaturen können je nach IPS-Version variieren.
