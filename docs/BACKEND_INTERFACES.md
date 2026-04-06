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

---

## 4. HomeMatic (IP, Funk, Wired) (Prio 1)

Alle drei HomeMatic-Varianten (IP, Funk, Wired) nutzen dieselbe IPS-API. Der Unterschied liegt in der Geräte-Adresse und dem verwendeten Kommunikationsmedium.

### IPS-API Funktionen

| Funktion | Signatur | Verwendung |
|----------|----------|------------|
| `HM_WriteValueFloat` | `HM_WriteValueFloat(int $instanceID, string $parameter, float $value)` | Dimmen: LEVEL (0.0–1.0), RAMP_TIME (Sekunden) |
| `HM_WriteValueBoolean` | `HM_WriteValueBoolean(int $instanceID, string $parameter, bool $value)` | Schalten: STATE (true/false) |
| `HM_WriteValueInteger` | `HM_WriteValueInteger(int $instanceID, string $parameter, int $value)` | On_Time, Level (alternative) |
| `RequestAction` | `RequestAction(int $variableID, mixed $value)` | Alternative über IPS-Variablen |

### Konfiguration

| Property | Typ | Beschreibung |
|----------|-----|--------------|
| `HMInstanceID` | integer | HomeMatic Zentrale/CCU Instance ID |
| `HMDeviceID` | integer | Geräte-Instanz des Dimmers/Schalters |
| `HMDeviceType` | integer | 4=HmIP, 5=Funk, 6=Wired |
| `HMFadeTime` | float | RAMP_TIME in Sekunden (0 = instant) |

### Unterstützte Geräte

#### HomeMatic IP
| Gerätetyp | Typ | Beschreibung |
|-----------|-----|--------------|
| HmIP-PDT | Dimmer | Pluggable Dimmer Trait |
| HmIP-FDT | Dimmer | Flush-Mount Dimmer |
| HmIP-BSL | Dimmer | Brand Switch Lamp (2-Kanal) |
| HmIP-PS | Schalter | Schaltsteckdose |

#### HomeMatic Funk
| Gerätetyp | Typ | Beschreibung |
|-----------|-----|--------------|
| HM-LC-Dim1TPBU-FM | Dimmer | Tasten-Unterputz-Dimmer |
| HM-LC-Dim1T-FM | Dimmer | Unterputz-Dimmer |
| HM-LC-Dim2T-FM | Dimmer | 2-Kanal Unterputz-Dimmer |
| HM-LC-Sw1-Pl-2 | Schalter | Schaltsteckdose |

#### HomeMatic Wired
| Gerätetyp | Typ | Beschreibung |
|-----------|-----|--------------|
| HMW-LC-Dim1L-DR | Dimmer | Hutschienen-Dimmer |
| HMW-IO-12-Sw7-DR | Schalter | 12-Kanal Hutschienen-Schaltaktor |

### Mapping

- **Power ON**: `HM_WriteValueBoolean($deviceID, "STATE", true)`
- **Power OFF**: `HM_WriteValueBoolean($deviceID, "STATE", false)`
- **Brightness 0–100**: `HM_WriteValueFloat($deviceID, "LEVEL", $level / 100.0)` (LEVEL ist 0.0–1.0)
- **Fade**: `HM_WriteValueFloat($deviceID, "RAMP_TIME", $seconds)` vor LEVEL setzen

### Parameter-Referenz

| Parameter | Typ | Beschreibung |
|-----------|-----|--------------|
| `STATE` | boolean | Schaltzustand (true=an, false=aus) |
| `LEVEL` | float | Helligkeit (0.0=aus, 1.0=100%) |
| `RAMP_TIME` | float | Einschaltverzögerung/Fade-Zeit in Sekunden |
| `ON_TIME` | float | Automatische Ausschaltzeit in Sekunden |
| `WORKING` | boolean | Read-only: Gerät arbeitet gerade |

---

## 5. Philips Hue (Prio 2)

### IPS-API Funktionen

| Funktion | Signatur | Verwendung |
|----------|----------|------------|
| `RequestAction` | `RequestAction(int $variableID, mixed $value)` | Über IPS-Hue-Modul Variablen |
| `Hue_SetLight` | `Hue_SetLight(int $bridgeInstanceID, int $lightID, array $state)` | Direkter API-Aufruf |

### Konfiguration

| Property | Typ | Beschreibung |
|----------|-----|--------------|
| `HueBridgeInstanceID` | integer | Hue Bridge Instance ID |
| `HueLightID` | integer | Light ID (1–63) |

### Mapping

- **Power ON**: `Hue_SetLight($bridgeID, $lightID, ["on" => true])`
- **Power OFF**: `Hue_SetLight($bridgeID, $lightID, ["on" => false])`
- **Brightness 0–100**: `Hue_SetLight($bridgeID, $lightID, ["bri" => $level * 254 / 100])` (bri ist 0–254)
- **Fade**: `Hue_SetLight($bridgeID, $lightID, ["bri" => $val, "transitiontime" => $seconds * 10])` (transitiontime in 100ms-Einheiten)

---

## 6. Tasmota/ESP (MQTT/HTTP) (Prio 3)

### MQTT Variante

| Topic | Payload | Verwendung |
|-------|---------|------------|
| `cmnd/tasmota/POWER` | `ON` / `OFF` | Schalten |
| `cmnd/tasmota/Dimmer` | `0–100` | Dimmen |
| `stat/tasmota/RESULT` | `{"POWER":"ON","Dimmer":50}` | Status-Rückmeldung |

### HTTP API Variante

| Methode | Endpoint | Body | Verwendung |
|---------|----------|------|------------|
| GET | `/cm?cmnd=Power%20ON` | — | Schalten |
| GET | `/cm?cmnd=Dimmer%2050` | — | Dimmen |
| GET | `/cm?cmnd=Power%20OFF` | — | Ausschalten |

### Konfiguration

| Property | Typ | Beschreibung |
|----------|-----|--------------|
| `TasmotaIPAddress` | string | IP-Adresse des Tasmota-Geräts |
| `TasmotaMQTTTopic` | string | MQTT Topic Präfix (z.B. "tasmota") |
| `TasmotaMethod` | integer | 1=HTTP, 2=MQTT |

### Mapping

- **Power ON (HTTP)**: `IPS_GetURLContent("http://$ip/cm?cmnd=Power%20ON")`
- **Power ON (MQTT)**: `RequestAction($mqttVariableID, "ON")`
- **Brightness (HTTP)**: `IPS_GetURLContent("http://$ip/cm?cmnd=Dimmer%20$level")`

---

## 7. WLED (HTTP/MQTT) (Prio 3)

### HTTP API

| Methode | Endpoint | Body | Verwendung |
|---------|----------|------|------------|
| POST | `/json/state` | `{"on":true,"bri":128}` | Schalten + Dimmen |
| POST | `/json/state` | `{"on":false}` | Ausschalten |
| POST | `/json/state` | `{"on":true,"bri":128,"transition":5}` | Fade mit Transition (Sekunden * 10) |

### MQTT Variante

| Topic | Payload | Verwendung |
|-------|---------|------------|
| `wled/[device]/api` | `{"on":true,"bri":128}` | Schalten + Dimmen |
| `wled/[device]/api` | `{"on":false}` | Ausschalten |

### Konfiguration

| Property | Typ | Beschreibung |
|----------|-----|--------------|
| `WLEDIPAddress` | string | IP-Adresse oder Hostname des WLED-Controllers |
| `WLEDTransitionTime` | float | Default Transition Time in Sekunden (0 = instant) |

### Mapping

- **Power ON + Brightness**: `IPS_GetURLContent("http://$ip/json/state", ["method" => "POST", "body" => '{"on":true,"bri":' . $level . '}'])`
- **Power OFF**: `IPS_GetURLContent("http://$ip/json/state", ["method" => "POST", "body" => '{"on":false}'])`
- **Fade**: `"transition": $seconds * 10` im JSON (WLED erwartet Deci-Sekunden)

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
