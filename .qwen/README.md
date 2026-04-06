# .qwen Directory

Dieser Ordner enthält Qwen Code Agent-Konfigurationen und Commands für das IPS-UnifiedLight Projekt.

## Struktur

```
.qwen/
├── agent.json              # Agent-Metadaten und verfügbare Commands
├── settings.json           # Projekt-spezifische Einstellungen und Konventionen
└── commands/
    └── ips-dev.md          # IPS Development Workflow Command (4-Phasen)
```

## Verfügbare Commands

### `/ips-dev <aufgabe>`

Startet den strukturierten 4-Phasen Development Workflow:

1. **INVESTIGATE** - Code-Analyse und Validierung
2. **PLAN** - Implementierungsplan erstellen
3. **IMPLEMENT** - Code-Umsetzung mit Todo-Tracking
4. **REVIEW & SUMMARY** - Änderungen auswerten

**Beispiel:**
```
/ips-dev "Füge einen neuen Backend-Typ hinzu: KNX DALI Gateway"
```

## Konventionen

- **Sprache**: PHP
- **Framework**: IP-Symcon Module
- **Backends**: DMX, Shelly, Zigbee2MQTT
- **Logging**: `$this->SendDebug()`
- **Status-Codes**: 102 (active), 201 (not configured)
