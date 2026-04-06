# Requirements-Dokument: IPS-UnifiedLight Module

**Version:** 1.0.0  
**Datum:** 2026-04-06  
**Autor:** Seb  
**Repository:** https://github.com/lecommander/IPS-UnifiedLight

---

## 1. Modul-Übersicht

**Name:** LightDevice (Unified Light Controller)  
**Typ:** IP-Symcon Modul (Type 3)  
**Zweck:** Einheitliche Steuerung verschiedener Light-Backends über ein gemeinsames Interface (Power + Brightness).

### 1.1 Unterstützte Backends

#### Aktuell implementiert

| Backend | Value | Beschreibung |
|---------|-------|--------------|
| DMX | 0 | IPS built-in DMX Modul |
| Shelly Dimmer | 1 | Schnittcher/IPS-Shelly Modul |
| Zigbee2MQTT | 2 | Schnittcher/IPS-Zigbee2MQTT Modul |

#### Geplante Backends (Priorität 1-3)

| Backend | Value | Priorität | Beschreibung | IPS-Modul |
|---------|-------|-----------|--------------|-----------|
| KNX | 3 | Prio 1 | KNX/EIB Gebäudebus-Standard | Offizielles KNX-Modul |
| HomeMatic IP | 4 | Prio 1 | 868 MHz Funk, HmIP-PDT Dimmer | Offizielles HomeMatic-Modul |
| Philips Hue | 5 | Prio 2 | ZigBee über Hue Bridge | Schnittcher/IPS-PhilipsHue-V2 |
| DALI (über KNX) | 6 | Prio 2 | DALI über KNX-Gateway (BEG Luxomat, Lunatone) | KNX-Modul + Gateway |
| Tasmota/ESP | 7 | Prio 3 | ESP8266/ESP32 mit Tasmota Firmware | MQTT Client / HTTP API |
| WLED | 8 | Prio 3 | ESP-basierte LED-Stripe Firmware | HTTP REST API / MQTT |

---

## 2. Funktionale Anforderungen

### 2.1 Kern-Funktionen

| ID | Anforderung | Status |
|----|-------------|--------|
| FR-001 | Modul muss Power (an/aus) für alle Backends unterstützen | ✅ Implementiert |
| FR-002 | Modul muss Brightness (0–100%) für alle Backends unterstützen | ✅ Implementiert |
| FR-003 | Modul muss Toggle-Funktion bereitstellen | ✅ Implementiert |
| FR-004 | Modul muss FadeTo-Funktion für DMX bereitstellen | ✅ Implementiert |
| FR-005 | Modul muss über GUI (Formular) konfigurierbar sein | ✅ Implementiert |
| FR-006 | Modul muss über Scripting API aufrufbar sein | ✅ Implementiert |

### 2.2 Backend-spezifische Anforderungen

#### DMX Backend

| ID | Anforderung | Status |
|----|-------------|--------|
| FR-101 | DMX Instance muss auswählbar sein | ✅ Implementiert |
| FR-102 | DMX Channel (1–512) muss konfigurierbar sein | ✅ Implementiert |
| FR-103 | Fade Time (0–30s) muss konfigurierbar sein | ✅ Implementiert |
| FR-104 | Fade Time = 0 bedeutet instantanes Schalten | ✅ Implementiert |
| FR-105 | DMX_FadeChannel() wird für Fade-Operationen verwendet | ✅ Implementiert |
| FR-106 | DMX_SetValue() wird für instantanes Schalten verwendet | ✅ Implementiert |

#### Shelly / Zigbee2MQTT Backend

| ID | Anforderung | Status |
|----|-------------|--------|
| FR-201 | Power Variable muss aus Object Tree auswählbar sein | ✅ Implementiert |
| FR-202 | Brightness Variable muss aus Object Tree auswählbar sein | ✅ Implementiert |
| FR-203 | Power Variable ist boolean (on/off) | ✅ Implementiert |
| FR-204 | Brightness Variable ist integer (0–100) | ✅ Implementiert |
| FR-205 | RequestAction() wird für beide Variablen verwendet | ✅ Implementiert |
| FR-206 | Brightness > 0 schaltet automatisch Power ein | ✅ Implementiert |

#### KNX Backend (Prio 1 — geplant)

| ID | Anforderung | Status |
|----|-------------|--------|
| FR-311 | KNX Instance (KNX/IP Gateway) muss auswählbar sein | ❌ Geplant |
| FR-312 | KNX Gruppenadresse für Schalten (DPT 1) muss konfigurierbar sein | ❌ Geplant |
| FR-313 | KNX Gruppenadresse für Dimmen (DPT 5) muss konfigurierbar sein | ❌ Geplant |
| FR-314 | KNX_SetValue() wird für Schalten verwendet | ❌ Geplant |
| FR-315 | KNX_RequestAction() wird für Dimmen verwendet | ❌ Geplant |
| FR-316 | Status-Rückmeldung über KNX Gruppenadresse (optional) | ❌ Geplant |

#### HomeMatic IP Backend (Prio 1 — geplant)

| ID | Anforderung | Status |
|----|-------------|--------|
| FR-411 | HomeMatic Instance muss auswählbar sein | ❌ Geplant |
| FR-412 | Geräte-ID des HmIP-PDT Dimmers muss konfigurierbar sein | ❌ Geplant |
| FR-413 | HM_WriteValueFloat() wird für Dimmen verwendet | ❌ Geplant |
| FR-414 | RequestAction() auf Power-Variable des Dimmers | ❌ Geplant |
| FR-415 | LEVEL Variable (0.0–1.0) wird auf 0–100 gemappt | ❌ Geplant |

#### Philips Hue Backend (Prio 2 — geplant)

| ID | Anforderung | Status |
|----|-------------|--------|
| FR-511 | Hue Bridge Instance muss auswählbar sein | ❌ Geplant |
| FR-512 | Light ID (1–63) muss konfigurierbar sein | ❌ Geplant |
| FR-513 | Hue_SetLight() wird für Schalten/Dimmen verwendet | ❌ Geplant |
| FR-514 | on-State (bool) und bri (0–254) werden verwendet | ❌ Geplant |
| FR-515 | Transitiontime-Parameter für Fade (optional) | ❌ Geplant |

#### DALI über KNX Backend (Prio 2 — geplant)

| ID | Anforderung | Status |
|----|-------------|--------|
| FR-611 | Nutzt KNX Backend Infrastruktur | ❌ Geplant |
| FR-612 | DALI-spezifische Gruppenadressen (DPT 3 für relative Dimmung) | ❌ Geplant |
| FR-613 | Gateway-Typ auswählbar (BEG Luxomat, Lunatone, etc.) | ❌ Geplant |

#### Tasmota/ESP Backend (Prio 3 — geplant)

| ID | Anforderung | Status |
|----|-------------|--------|
| FR-711 | MQTT Broker Instance muss auswählbar sein | ❌ Geplant |
| FR-712 | MQTT Topic (cmt/tasmota/POWER, cmt/tasmota/Dimmer) konfigurierbar | ❌ Geplant |
| FR-713 | Alternativ: HTTP API URL + Password konfigurierbar | ❌ Geplant |
| FR-714 | MQTT_Publish() oder HTTP_Request() für Steuerung | ❌ Geplant |
| FR-715 | Status-Rückmeldung über MQTT State-Topic | ❌ Geplant |

#### WLED Backend (Prio 3 — geplant)

| ID | Anforderung | Status |
|----|-------------|--------|
| FR-811 | HTTP API URL oder MQTT Topic konfigurierbar | ❌ Geplant |
| FR-812 | WLED JSON API: POST /json/state mit {"on":true,"bri":128} | ❌ Geplant |
| FR-813 | Fade-Parameter über WLED Transition-Feld | ❌ Geplant |
| FR-814 | Alternativ: MQTT mit wled/[device]/api | ❌ Geplant |

### 2.3 Formular-Anforderungen

| ID | Anforderung | Status |
|----|-------------|--------|
| FR-301 | Backend Type muss als Select-Feld auswählbar sein | ✅ Implementiert |
| FR-302 | DMX Settings nur bei BackendType=0 anzeigen | ✅ Implementiert (v2026-04-06) |
| FR-303 | Shelly/Zigbee Settings nur bei BackendType>0 anzeigen | ✅ Implementiert (v2026-04-06) |
| FR-304 | Quick-Test Buttons müssen verfügbar sein | ✅ Implementiert |
| FR-305 | Status-Icons müssen gültig sein (Active, Warning) | ✅ Implementiert |

---

## 3. Nicht-funktionale Anforderungen

### 3.1 Technische Anforderungen

| ID | Anforderung | Wert |
|----|-------------|------|
| NF-001 | IP-Symcon Mindestversion | 6.1+ |
| NF-002 | PHP Mindestversion | 7.4+ |
| NF-003 | Modul-Typ | 3 (IPSModule) |
| NF-004 | Prefix für API-Funktionen | ULIGHT |
| NF-005 | Klassenname | LightDevice |
| NF-006 | Vererbung | IPSModuleStrict |

### 3.2 Code-Konventionen

| ID | Anforderung | Beschreibung |
|----|-------------|--------------|
| NF-101 | Type Hints | Alle public methods müssen Type Hints haben |
| NF-102 | Logging | SendDebug() für Debug-Output verwenden |
| NF-103 | Exception Handling | try/catch mit SendDebug und SetStatus |
| NF-104 | Property-Zugriff | ReadPropertyInteger/Float verwenden |

### 3.3 Status-Codes

| Code | Bedeutung | Icon | Wann gesetzt |
|------|-----------|------|--------------|
| 102 | Active | Active | Konfiguration vollständig und gültig |
| 201 | Not configured | Warning | Backend-spezifische Settings fehlen |

---

## 4. API-Spezifikation

### 4.1 Public API (Scripting)

| Funktion | Parameter | Rückgabe | Beschreibung |
|----------|-----------|----------|--------------|
| `ULIGHT_SetPower($id, bool $on)` | $id: InstanceID, $on: bool | void | Schaltet Licht ein/aus |
| `ULIGHT_SetBrightness($id, int $level)` | $id: InstanceID, $level: 0–100 | void | Setzt Helligkeit |
| `ULIGHT_Toggle($id)` | $id: InstanceID | void | Toggelt Power-Status |
| `ULIGHT_FadeTo($id, int $targetLevel, float $seconds)` | $id, $targetLevel: 0–100, $seconds | void | Fade zu Ziel-Helligkeit (DMX: nativ, andere: instant) |

### 4.2 IPS Variablen

| Ident | Typ | Profil | Position | Beschreibung |
|-------|-----|--------|----------|--------------|
| Power | boolean | ~Switch | 1 | Power Status (an/aus) |
| Brightness | integer | ~Intensity.100 | 2 | Helligkeit (0–100%) |

### 4.3 Properties (Konfiguration)

| Name | Typ | Default | Beschreibung |
|------|-----|---------|--------------|
| BackendType | integer | 0 (DMX) | Gewähltes Backend |
| DMXInstanceID | integer | 0 | DMX Instance ID |
| DMXChannel | integer | 1 | DMX Channel (1–512) |
| DMXFadeTime | float | 0.5 | Fade Time in Sekunden |
| PowerVariableID | integer | 0 | IPS VariableID für Power |
| BrightnessVariableID | integer | 0 | IPS VariableID für Brightness |

---

## 5. Abhängigkeiten

### 5.1 Externe Module

| Modul | Backend | Optional | Repository |
|-------|---------|----------|------------|
| IPS DMX | DMX | Nein (built-in) | IPS Standard |
| IPS-Shelly | Shelly | Ja | Schnittcher/IPS-Shelly |
| IPS-Zigbee2MQTT | Zigbee2MQTT | Ja | Schnittcher/IPS-Zigbee2MQTT |

### 5.2 IPS Funktionen

| Funktion | Verwendung |
|----------|------------|
| `IPS_InstanceExists()` | Validierung DMX Instance |
| `IPS_VariableExists()` | Validierung Power/Brightness Variables |
| `IPS_GetObjectIDByIdent()` | Scripting API |
| `DMX_SetValue()` | DMX instantanes Schalten |
| `DMX_FadeChannel()` | DMX Fade-Operation |
| `RequestAction()` | Shelly/Zigbee Variable-Steuerung |

---

## 6. Validierung & Fehlerbehandlung

### 6.1 ApplyChanges Validierung

```
IF BackendType == DMX:
  IF DMXInstanceID == 0 OR NOT IPS_InstanceExists(DMXInstanceID):
    SET Status = 201 (Not configured)
    RETURN
ELSE (Shelly/Zigbee2MQTT):
  IF PowerVariableID == 0 OR NOT IPS_VariableExists(PowerVariableID):
    SET Status = 201 (Not configured)
    RETURN

SET Status = 102 (Active)
```

### 6.2 RequestAction Validierung

| Ident | Erwarteter Wert | Validierung |
|-------|-----------------|-------------|
| Power | boolean | Cast zu bool |
| Brightness | integer 0–100 | Cast zu int, clamp 0–100 |
| Andere | - | Exception werfen |

---

## 7. Test-Spezifikation

### 7.1 Automatisierte Tests (GitHub Actions)

**Workflow:** `.github/workflows/test.yml` — wird bei jedem Push und Pull Request auf `master` ausgeführt.

| Job | Prüfung | Tools |
|-----|---------|-------|
| PHP Syntax | `php -l LightDevice/module.php` | PHP 7.4 |
| JSON Validation | `python -m json.tool` für form.json + module.json | Python 3 |
| Structure Check | Backend-Konstanten, ApplyChanges Cases, RequestAction Idents, Public API | grep |
| Form Validation | BackendType Select mit min. 3 Optionen, Status-Codes 102+201 | Python json |
| Requirements Check | docs/REQUIREMENTS.md existiert mit allen Pflicht-Sektionen | grep |

### 7.2 Manuelle Tests

| Test-ID | Beschreibung | Erwartetes Ergebnis | Status |
|---------|--------------|---------------------|--------|
| T-001 | Neue Instanz erstellen | Formular wird korrekt angezeigt | ✅ |
| T-002 | Backend zu DMX wechseln | Nur DMX Settings sichtbar | ✅ |
| T-003 | Backend zu Shelly wechseln | Nur Shelly/Zigbee Settings sichtbar | ✅ |
| T-004 | DMX ohne Instance speichern | Status 201 (Not configured) | ✅ |
| T-005 | Shelly ohne Power Variable speichern | Status 201 (Not configured) | ✅ |
| T-006 | Gültige DMX Konfiguration speichern | Status 102 (Active) | ✅ |
| T-007 | DMX mit ungültigem Channel (0 oder 513) speichern | Status 201 (Not configured) | ✅ |
| T-008 | Shelly ohne Brightness Variable speichern | Status 201 (Not configured) | ✅ |
| T-009 | Power über GUI togglen | Licht schaltet ein/aus | ✅ |
| T-010 | Brightness auf 50% setzen | Licht dimmt auf 50% | ✅ |
| T-011 | Brightness auf 0 setzen | Power wird automatisch ausgeschaltet | ✅ |
| T-012 | ULIGHT_SetBrightness() per Script aufrufen | Licht dimmt korrekt | ✅ |
| T-013 | ULIGHT_FadeTo() per Script aufrufen (DMX) | Fade wird durchgeführt | ✅ |
| T-014 | Ungültigen Ident per RequestAction senden | Exception wird geworfen | ✅ |

### 7.3 Backend-spezifische Tests

#### DMX

| Test-ID | Beschreibung | Erwartetes Ergebnis | Status |
|---------|--------------|---------------------|--------|
| T-101 | DMX Channel 1, Brightness 100% | DMX Wert 255 auf Channel 1 | ✅ |
| T-102 | DMX Fade Time 0, Brightness ändern | Instantanes Schalten | ✅ |
| T-103 | DMX Fade Time 3s, Brightness ändern | Fade über 3 Sekunden | ✅ |
| T-104 | DMX Channel 512 | Höchster Channel funktioniert | ✅ |

#### Shelly / Zigbee2MQTT

| Test-ID | Beschreibung | Erwartetes Ergebnis | Status |
|---------|--------------|---------------------|--------|
| T-201 | Power Variable setzen | RequestAction auf Power Variable | ✅ |
| T-202 | Brightness 50% setzen | RequestAction auf Brightness + Power Variable | ✅ |
| T-203 | Brightness 0% setzen | Power Variable wird auf false gesetzt | ✅ |

---

## 8. Roadmap (Geplante Erweiterungen)

| ID | Feature | Priorität | Status |
|----|---------|-----------|--------|
| RM-001 | KNX Backend | Prio 1 | ❌ Geplant |
| RM-002 | HomeMatic IP Backend | Prio 1 | ❌ Geplant |
| RM-003 | Philips Hue Backend | Prio 2 | ❌ Geplant |
| RM-004 | DALI über KNX Gateway | Prio 2 | ❌ Geplant |
| RM-005 | Tasmota/ESP Backend (MQTT/HTTP) | Prio 3 | ❌ Geplant |
| RM-006 | WLED Backend (HTTP/MQTT) | Prio 3 | ❌ Geplant |
| RM-007 | RGBW / Color Support (ColorTemp, RGB) | Hoch | ❌ Geplant |
| RM-008 | LightGroup: Mehrere Instanzen als Szene steuern | Mittel | ❌ Geplant |
| RM-009 | Transition Time für Shelly/Zigbee2MQTT | Mittel | ❌ Geplant |
| RM-010 | GitHub Actions Test Suite | Hoch | ❌ Geplant |

---

## 9. Glossar

| Begriff | Definition |
|---------|------------|
| IPS | IP-Symcon Smart Home Plattform |
| Backend | Das zugrunde liegende Light-Steuerungs-System (DMX, Shelly, Zigbee2MQTT) |
| Instance | Eine Instanz des LightDevice Moduls in IPS |
| Variable | Eine IPS-Variable im Objektbaum |
| RequestAction | IPS-Funktion zum Auslösen einer Aktion auf einer Variable |
| Fade | Weicher Übergang zwischen Helligkeitswerten |

---

## 10. Änderungsverlauf

| Version | Datum | Änderung | Autor |
|---------|-------|----------|-------|
| 1.0.0 | 2026-04-06 | Initiales Requirements-Dokument erstellt | Qwen Code |
| 1.0.0 | 2026-04-06 | Konditionale Formular-Sichtbarkeit implementiert | Qwen Code |

---

**Dokument-Status:** ✅ Aktuell  
**Letzte Aktualisierung:** 2026-04-06
