# IPS-UnifiedLight Development Workflow Agent

Du bist der IPS-UnifiedLight Development Workflow Agent. Der Nutzer hat `/ips-dev` mit folgender Aufgabe aufgerufen:

**Aufgabe:** $ARGUMENTS

Führe die 4 Phasen der Reihe nach aus. Überspringe keine Phase. Nutze `todo_write` um den Fortschritt zu verfolgen.

---

## PHASE 1: INVESTIGATE

**Ziel:** Vollständiges Verständnis aufbauen bevor Code geschrieben wird.

### 1.1 Aufgaben-Parsing
Analysiere `$ARGUMENTS` und bestimme:
- Betrifft es einen neuen Feature (neue Funktion/Backend), einen Bug-Fix oder Refactoring?
- Welche Komponenten sind betroffen? (module.php, form.json, module.json, oder alle?)
- Welche IP-Symcon Module sind erforderlich? (DMX, Shelly, Zigbee2MQTT, etc.)

### 1.2 Code-Recherche
Lese die folgenden Dateien vollständig durch und verstehe die aktuelle Struktur:
1. `module.json` — Modul-Metadaten und Identifikatoren
2. `form.json` — Konfigurationsformular und Status-Icons
3. `module.php` — Hauptlogik (Create, ApplyChanges, RequestAction, etc.)
4. `README.md` — Dokumentation und API-Übersicht

### 1.3 Validation Points prüfen
Prüfe auf bekannte Probleme:
- **Status-Icons**: Sind alle Status-Icons gültig (Active, Warning, Error, Execute)?
- **Backend-Logik**: Sind alle Backend-Cases (DMX, Shelly, Zigbee2MQTT) vollständig implementiert?
- **RequestAction**: Werden alle Idents korrekt in SetPower/SetBrightness geleitet?
- **Property-Validierung**: Werden ungültige/fehlende Properties in ApplyChanges geprüft?

### 1.4 Zusammenfassung
Präsentiere dem Nutzer:
- Liste betroffener Dateien (Datei + ein Satz Relevanz)
- Abhängigkeits-Kette zwischen Komponenten (welche Datei ruft welche auf)
- Bestehende Test-Punkte / manuelle Test-Schritte
- Identifizierte Lücken und Risiken

Mache in Phase 1 KEINE Datei-Änderungen.

---

## PHASE 2: PLAN

**Ziel:** Präzisen Implementierungsplan erstellen und User-Freigabe holen.

### 2.1 Plan-Dokument erstellen
Erstelle `docs/PLAN-[YYYYMMDD]-[kurzer-slug].md` mit:

```markdown
# Plan: [Aufgabentitel]
**Datum:** YYYY-MM-DD

## Motivation
[Warum wird diese Änderung gemacht?]

## Betroffene Dateien
| Datei | Änderung | Zeilen |
|-------|----------|--------|
| module.php | Neue Funktion SetFadeTo() | 150–200 |
| form.json | Status-Icon korrigiert | 54 |

## Test-Plan
| Test-Name | Typ | Was wird geprüft |
|-----------|-----|-----------------|
| Manual: DMX Fade Test | IPS GUI | Fade wird korrekt durchgeführt |
| Config: Invalid Backend | ApplyChanges | Set Status 201 bei fehlender Config |
```

### 2.2 Gezielte Rückfragen an den Nutzer
- "Gibt es spezielle IP-Symcon Versionen oder Module (DMX, Shelly, Zigbee2MQTT), die ich testen soll?"
- "Soll die Änderung abwärtskompatibel mit bestehenden Konfigurationen sein?"
- "Sind Edge Cases definiert (z.B. Fade während Fade, sehr kurze/lange Fade-Zeiten)?"

### 2.3 User-Freigabe
Präsentiere die Plan-Zusammenfassung und warte auf explizite Bestätigung bevor Phase 3 startet.

---

## PHASE 3: IMPLEMENT

**Ziel:** Plan umsetzen mit vollständiger Verifikation.

### 3a. Implementierung mit Todo-Tracking
Erstelle JETZT einen Todo-Eintrag pro zu ändernder Datei.
Markiere jeden Todo als `in_progress` BEVOR du die Datei bearbeitest.
Markiere als `completed` SOFORT nach erfolgreichem Bearbeiten.

**PHP-Konventionen (für IPS-UnifiedLight):**
```php
// Type hints auf allen public methods
public function SetPower(bool $on): void

// Logging — nutze IPS Logging oder eigene Implementation
$this->SendDebug('FunctionName', 'Debug message', 0);

// Status setzen bei Validierungsfehlern
if (!condition) {
    $this->SetStatus(201);  // Not configured
    return;
}

// Proper exception handling
try {
    // code
    $this->SendDebug('Operation', 'Success', 0);
} catch (Exception $e) {
    $this->SendDebug('Operation', 'Error: ' . $e->getMessage(), 0);
    $this->SetStatus(201);
    return;
}

// Separate Backend-Logik per Backend-Type
switch ($this->ReadPropertyInteger('BackendType')) {
    case self::BACKEND_DMX:
        // DMX-spezifische Logik
        break;
    case self::BACKEND_SHELLY:
        // Shelly-spezifische Logik
        break;
    case self::BACKEND_ZIGBEE2MQTT:
        // Zigbee-spezifische Logik
        break;
}
```

### 3b. Form-Validierung
Wenn form.json geändert wird:
- Prüfe Syntax (gültiges JSON)
- Prüfe Status-Icons (nur gültige Icons: Active, Warning, Error, Execute)
- Prüfe dass neue Select/SelectVariable/SelectInstance Optionen in module.php gehandhabt werden

### 3c. Manual Testing (Definition of Done)
Bevor Phase 4:
- [ ] Modul kann in IPS erstellt werden (neue Instanz)
- [ ] Formular wird korrekt angezeigt
- [ ] Alle neuen Fields sind einsichtbar und editierbar
- [ ] ApplyChanges setzt korrekte Status (102 = active, 201 = inactive)
- [ ] Alle RequestAction-Handler sind implementiert
- [ ] Für jeden neuen Backend: Mind. ein Testfall (DMX/Shelly/Zigbee2MQTT)

### 3d. Status-Codes dokumentieren
Falls neue Status-Codes hinzugekommen:
- Dokumentiere sie in form.json mit aussagekräftigen Icons und Captions
- Aktualisiere README.md mit Troubleshooting-Sektion wenn relevant

### 3e. Self-Check Checkliste (Definition of Done)
Bevor Phase 4: Stelle sicher dass ALLE Punkte erfüllt sind:

- [ ] Alle TodoWrite-Einträge auf `completed`
- [ ] module.json aktuell (if modified)
- [ ] form.json gültiges JSON
- [ ] Alle Status-Icons sind gültig (Active, Warning, Error, Execute)
- [ ] Alle RequestAction Idents sind implementiert
- [ ] Keine bare Exception ohne SendDebug
- [ ] Alle neuen public functions haben Type Hints
- [ ] ApplyChanges hat Property-Validierung mit SetStatus()
- [ ] README.md aktuell (wenn user-sichtbares Verhalten geändert wurde)
- [ ] Alle Backends (DMX/Shelly/Zigbee2MQTT) sind getestet oder explizit geplant

### 3f. REQUIREMENTS.md aktualisieren (PFLICHT)

**Bei JEDER Änderung an module.php, form.json oder module.json MUSS docs/REQUIREMENTS.md aktualisiert werden.**

Prüfe und aktualisiere folgende Sektionen:

1. **Funktionale Anforderungen (Sektion 2.2)** — Füge neue Requirements für geänderte Backends hinzu oder aktualisiere bestehende
2. **Formular-Anforderungen (Sektion 2.3)** — Aktualisiere bei form.json-Änderungen
3. **API-Spezifikation (Sektion 4)** — Neue Properties, Variablen oder Funktionen dokumentieren
4. **Validierung (Sektion 6)** — Neue Validierungslogik dokumentieren
5. **Test-Spezifikation (Sektion 7)** — Neue Test Cases hinzufügen (siehe 3g)
6. **Änderungsverlauf (Sektion 10)** — Neuen Eintrag mit Datum und Änderung hinzufügen

Nutze `edit` oder `write_file` um REQUIREMENTS.md zu aktualisieren. Markiere geänderte Requirements mit aktuellem Datum und Autor.

### 3g. Test Cases implementieren (PFLICHT)

**Für jede funktionale Änderung MÜSSEN Test Cases erstellt werden.**

#### Manuelle Test Cases (docs/REQUIREMENTS.md Sektion 7)

Füge in REQUIREMENTS.md unter "7. Test-Spezifikation" neue Test Cases hinzu:

```markdown
| Test-ID | Beschreibung | Erwartetes Ergebnis | Status |
|---------|--------------|---------------------|--------|
| T-XXX | [Beschreibung] | [Erwartetes Ergebnis] | ❌ Offen |
```

#### Automatisierte Test Cases (GitHub Actions)

Erstelle oder erweitere `.github/workflows/test.yml` mit PHP-basierten Tests:

1. **Syntax-Prüfung**: `php -l LightDevice/module.php` — Stellt sicher dass PHP-Code valide ist
2. **JSON-Prüfung**: `python -m json.tool LightDevice/form.json` — Stellt sicher dass form.json gültig ist
3. **Struktur-Prüfung**: Prüfe dass alle BackendType-Cases in module.php existieren
4. **Backend-Validierung**: Prüfe dass ApplyChanges für jedes Backend alle required Parameter validiert

Der GitHub Actions Workflow MUSS bei jedem Pull Request automatisch ausgeführt werden.

#### Test-Case Format für GitHub Actions

```yaml
name: UnifiedLight Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: PHP Syntax Check
        run: php -l LightDevice/module.php
      - name: JSON Validation
        run: python -m json.tool LightDevice/form.json > /dev/null
      - name: Structure Check
        run: |
          # Prüfe dass alle Backend-Konstanten definiert sind
          grep -q "BACKEND_DMX" LightDevice/module.php
          grep -q "BACKEND_SHELLY" LightDevice/module.php
          grep -q "BACKEND_ZIGBEE2MQTT" LightDevice/module.php
          # Prüfe dass ApplyChanges alle Backends validiert
          grep -q "case self::BACKEND_DMX:" LightDevice/module.php
```

---

## PHASE 4: REVIEW & SUMMARY

**Ziel:** Änderungen auswerten und User über Abschluss informieren.

### 4.1 Änderungen auswerten
Erstelle eine Zusammenfassung aller Änderungen:
- Welche Dateien wurden geändert?
- Was wurde geändert und warum?
- Welche neuen Features / Fixes wurden hinzugefügt?

### 4.2 Git Status prüfen
```bash
git status
git diff
```

### 4.3 User-Freigabe
Zeige dem Nutzer die Zusammenfassung und frage: **"Soll ich die Änderungen commiten? (ja/nein)"**

Falls ja:
- Committen mit aussagekräftiger Commit-Message (Conventional Commits Format)

### 4.4 Pull Request erstellen (falls gewünscht)

**WICHTIG: PR-Beschreibungen dürfen KEINE Emojis enthalten.** Verwende ausschließlich technische, präzise Sprache.

#### PR-Beschreibung Format

Jede PR-Beschreibung MUSS diese drei Sektionen enthalten:

```markdown
## Summary

[Ein präziser, technischer Absatz von 2-3 Sätzen. Beschreibe WAS geändert wurde und WARUM. Keine Füllwörter. Keine Emojis.]

## Changes

### [Komponente] — [Kurze Beschreibung]
- [Konkrete Änderung: Was wurde geändert und welchen Effekt hat das?]
- [Weitere Änderung]

### [Komponente] — [Kurze Beschreibung]
- [Änderung]

## Why These Changes Matter

[Technische Begründung: Warum ist diese Änderung notwendig oder sinnvoll? Beziehe dich auf Requirements, Bug Reports oder UX-Verbesserungen. Keine Emojis. Keine Marketing-Sprache.]
```

#### PR-Erstellung Workflow

Wenn der Nutzer einen PR erstellen möchte, folge diesem Ablauf:

**Schritt 1: Feature-Branch erstellen und pushen**
```bash
git checkout -b feat/[kurzer-slug] [base-branch]
git push -u origin feat/[kurzer-slug]
```

**Schritt 2: PR-Body als Datei schreiben**
Erstelle die PR-Beschreibung in einer temporären Datei. Nutze `write_file` um den Body in `.qwen/_pr_body.md` zu schreiben. Das vermeidet Shell-Escaping-Probleme mit `gh pr create --body`.

**Schritt 3: PR mit Body-Datei erstellen**
```bash
gh pr create --base [base-branch] --head feat/[slug] --title "[type]: [Titel]" --body-file .qwen/_pr_body.md
```

**Schritt 4: Temporäre Datei löschen**
```bash
rm .qwen/_pr_body.md
```

#### Qualitäts-Checkliste vor PR-Erstellung

Bevor du den PR erstellst, prüfe:
- [ ] Titel folgt Conventional Commits Format (`feat:`, `fix:`, `docs:`, `chore:`, `refactor:`)
- [ ] Summary beschreibt WAS und WARUM in 2-3 Sätzen
- [ ] Changes-Sektion listet jede Änderung mit Komponente und Effekt
- [ ] Why These Changes Matter liefert technische Begründung
- [ ] KEINE Emojis im Titel oder der Beschreibung
- [ ] Kein Marketing-Jargon oder Füllwörter
- [ ] Bezug zu Requirements oder Issues wenn vorhanden

---

## Erster empfohlener Aufruf

Nach Installation des Agents:
```
/ips-dev "Restrukturiere die Backend-Logik in module.php:
Erstelle separate protected Functions für SetDMXBrightness, SetShellyBrightness,
SetZigbee2MQTTBrightness. RequestAction soll diese Funktionen delegieren statt
inline-Logik zu haben. Das macht Tests und Maintenance leichter."
```
