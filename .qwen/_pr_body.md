## Summary

This PR introduces a major architectural refactoring: all backend implementations are extracted from module.php into separate backend classes under LightDevice/Backends/, each implementing the IBackend interface. This reduces module.php from 365 lines to 210 lines and makes each backend independently testable and maintainable. Additionally, the WLED backend (Prio 3) is implemented as the 11th and final planned backend, bringing total backend support to 11 across 9 protocols.

## Changes

### Architecture — Backend Extraction Pattern

Each backend is now a standalone class implementing IBackend:
- `IBackend.php` — Interface with ValidateConfiguration(), SetPower(), SetBrightness(), FadeTo()
- `DmxBackend.php` — DMX_SetValue/DMX_FadeChannel (value 0)
- `VariableBackend.php` — RequestAction on IPS variables, shared by Shelly (1), Zigbee2MQTT (2), Hue (7), Tasmota (9)
- `KnxBackend.php` — EIB_Switch/EIB_DimValue (value 3)
- `DaliBackend.php` — EIB_Switch/EIB_DimValue via KNX-DALI-Gateway (value 8)
- `HomeMaticBackend.php` — HM_WriteValueBoolean/Float with RAMP_TIME fade (values 4, 5, 6)
- `WledBackend.php` — HTTP POST /json/state with transition support (value 10)

module.php now acts as a thin delegator: CreateBackend() factory instantiates the appropriate backend class based on BackendType, and all public API methods (SetPower, SetBrightness, FadeTo) delegate to the backend instance.

### New Backend — WLED (value 10)

WledBackend controls WLED LED controllers via HTTP REST API:
- POST to http://[ip]/json/state with JSON payloads: {"on":true,"bri":128}
- Native fade support via "transition" field (deci-seconds: seconds * 10)
- Default transition time configurable via WLEDTransitionTime property
- 5-second HTTP timeout for robustness
- Properties: WLEDIPAddress (string), WLEDTransitionTime (float)

### form.json — WLED configuration
- Added WLED option to BackendType select (value 10)
- Added WLED Settings section with IP Address (TextField) and Default Transition Time (NumberSpinner)
- Conditional visibility: BackendType == 10

### docs/REQUIREMENTS.md — Documentation updates
- Marked WLED as implemented in backend overview table
- Added 6 WLED functional requirements (FR-811 through FR-816)
- Extended ApplyChanges validation pseudocode with WLED branch
- Added 5 WLED test cases (T-801 through T-805)
- Updated roadmap: RM-006 WLED marked as implemented
- Updated change history with version 2.0.0 (major refactoring)

### .github/workflows/test.yml — CI updates
- Added BACKEND_WLED constant check
- Added case self::BACKEND_WLED check in ApplyChanges validation
- Added new "Check Backend Files Exist" step verifying all 7 backend class files exist

## Why These Changes Matter

The original module.php contained all backend logic in large switch statements, making it difficult to test individual backends, add new ones, or understand the codebase. By extracting each backend into its own class implementing a common interface, we achieve:

1. **Single Responsibility**: Each backend class handles only its own protocol translation. DmxBackend knows nothing about KNX, HomeMaticBackend knows nothing about HTTP.

2. **Testability**: Backend classes can be unit-tested in isolation by mocking IPS functions. The IBackend interface enables dependency injection patterns.

3. **Extensibility**: Adding a new backend now requires creating one new class file and adding one case to CreateBackend(). No modification to existing backend code is needed (Open/Closed Principle).

4. **Code Reuse**: VariableBackend serves four backends (Shelly, Zigbee2MQTT, Hue, Tasmota) with zero code duplication. HomeMaticBackend serves three variants (IP, Funk, Wired) with shared RAMP_TIME fade logic.

5. **Maintainability**: module.php is reduced from 365 lines to 210 lines. The factory pattern (CreateBackend) centralizes backend instantiation, and each backend file is under 80 lines.

WLED completes the planned backend roadmap. With 11 backends across 9 protocols, this module now covers every major light control system used in the IPS community: professional (KNX, DALI, HomeMatic Wired), consumer (Hue, Shelly), IoT (Tasmota, Zigbee2MQTT), stage lighting (DMX), and DIY LED controllers (WLED).
