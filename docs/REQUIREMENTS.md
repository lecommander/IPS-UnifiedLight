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

| Backend | Value | Beschreibung |
|---------|-------|--------------|
| DMX | 0 | IPS built-in DMX Modul |
| Shelly Dimmer | 1 | Schnittcher/IPS-Shelly Modul |
| Zigbee2MQTT | 2 | Schnittcher/IPS-Zigbee2MQTT Modul |

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

### 7.1 Manuelle Tests

| Test-ID | Beschreibung | Erwartetes Ergebnis |
|---------|--------------|---------------------|
| T-001 | Neue Instanz erstellen | Formular wird korrekt angezeigt |
| T-002 | Backend zu DMX wechseln | Nur DMX Settings sichtbar |
| T-003 | Backend zu Shelly wechseln | Nur Shelly/Zigbee Settings sichtbar |
| T-004 | DMX ohne Instance speichern | Status 201 (Not configured) |
| T-005 | Shelly ohne Power Variable speichern | Status 201 (Not configured) |
| T-006 | Gültige DMX Konfiguration speichern | Status 102 (Active) |
| T-007 | Power über GUI togglen | Licht schaltet ein/aus |
| T-008 | Brightness auf 50% setzen | Licht dimmt auf 50% |
| T-009 | Brightness auf 0 setzen | Power wird automatisch ausgeschaltet |
| T-010 | ULIGHT_SetBrightness() per Script aufrufen | Licht dimmt korrekt |
| T-011 | ULIGHT_FadeTo() per Script aufrufen (DMX) | Fade wird durchgeführt |
| T-012 | Ungültigen Ident per RequestAction senden | Exception wird geworfen |

### 7.2 Backend-spezifische Tests

#### DMX

| Test-ID | Beschreibung | Erwartetes Ergebnis |
|---------|--------------|---------------------|
| T-101 | DMX Channel 1, Brightness 100% | DMX Wert 255 auf Channel 1 |
| T-102 | DMX Fade Time 0, Brightness ändern | Instantanes Schalten |
| T-103 | DMX Fade Time 3s, Brightness ändern | Fade über 3 Sekunden |
| T-104 | DMX Channel 512 | Höchster Channel funktioniert |

#### Shelly / Zigbee2MQTT

| Test-ID | Beschreibung | Erwartetes Ergebnis |
|---------|--------------|---------------------|
| T-201 | Power Variable setzen | RequestAction auf Power Variable |
| T-202 | Brightness 50% setzen | RequestAction auf Brightness + Power Variable |
| T-203 | Brightness 0% setzen | Power Variable wird auf false gesetzt |

---

## 8. Roadmap (Geplante Erweiterungen)

| ID | Feature | Priorität | Status |
|----|---------|-----------|--------|
| RM-001 | RGBW / Color Support (ColorTemp, RGB) | Hoch | ❌ Geplant |
| RM-002 | LightGroup: Mehrere Instanzen als Szene steuern | Mittel | ❌ Geplant |
| RM-003 | HomeMatic Dimmer Backend | Niedrig | ❌ Geplant |
| RM-004 | Transition Time für Shelly/Zigbee2MQTT | Mittel | ❌ Geplant |
| RM-005 | Unit Tests / Automatisierte Tests | Hoch | ❌ Geplant |
| RM-006 | Dokumentation auf Deutsch | Mittel | ❌ Geplant |

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
