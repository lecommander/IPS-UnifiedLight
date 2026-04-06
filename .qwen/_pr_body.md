## Summary

This PR implements the KNX backend (Prio 1) as the fourth supported light control protocol. KNX is the European standard for building automation, widely used in professional installations and new construction projects. The implementation uses IPS built-in KNX functions EIB_Switch() for on/off control (DPT 1) and EIB_DimValue() for brightness control (DPT 5, 0-100%).

## Changes

### module.php — KNX backend implementation
- Added BACKEND_KNX constant (value 3)
- Added three new properties: KNXInstanceID (integer), KNXSwitchAddress (string), KNXDimAddress (string)
- Extended ApplyChanges() with KNX validation: requires valid KNX instance, non-empty switch address, and non-empty dim address
- Extended SetPower() with KNX case: calls EIB_Switch(switchAddress, on)
- Extended SetBrightness() with KNX case: calls EIB_DimValue(dimAddress, level)
- Updated module header docblock to list KNX as a supported backend

### form.json — KNX configuration form
- Added KNX option to BackendType select (value 3)
- Added KNX Settings section with three fields: KNX Instance (SelectInstance), Switch Group Address (TextField), Dim Group Address (TextField)
- KNX fields are only visible when BackendType == 3, keeping the form clean for other backends
- Adjusted Shelly/Zigbee2MQTT visibility condition from "BackendType > 0" to "BackendType > 0 && BackendType < 3" to exclude KNX

### docs/REQUIREMENTS.md — Documentation updates
- Marked KNX as implemented in backend overview table
- Updated KNX functional requirements (FR-311 through FR-316) from planned to implemented
- Added KNX properties to API specification (KNXInstanceID, KNXSwitchAddress, KNXDimAddress)
- Added IPS KNX to dependencies table
- Added EIB_Switch() and EIB_DimValue() to IPS functions table
- Extended ApplyChanges validation pseudocode with KNX branch
- Added KNX test cases (T-301 through T-304)
- Updated roadmap: RM-001 KNX Backend marked as implemented
- Updated change history with version 1.2.0

### .github/workflows/test.yml — CI updates
- Added BACKEND_KNX constant check to structure validation job
- Added case self::BACKEND_KNX check to ApplyChanges validation job

### docs/BACKEND_INTERFACES.md — New reference document
- Created comprehensive API reference for all supported backends
- Documents IPS function signatures, DPT types, configuration properties, and value mappings for DMX, Shelly/Zigbee2MQTT, KNX, and planned backends (Hue, Tasmota, WLED)

## Why These Changes Matter

KNX is the most requested backend for professional installations. Unlike consumer protocols (Shelly, Hue), KNX is used in commercial buildings, hotels, hospitals, and high-end residential projects where reliability and standardization are critical. The KNX implementation follows the same pattern as existing backends: group addresses for switching and dimming are configured by the user, and the module translates unified Power/Brightness commands to native KNX telegrams.

The EIB_Switch/EIB_DimValue functions are part of IPS built-in KNX support, requiring no third-party modules. This makes KNX the second built-in backend after DMX, reducing deployment complexity for professional integrators who already use KNX for their building infrastructure.

KNX does not support native fade transitions (no DPT for gradual dimming with time parameter), so FadeTo() falls back to instant SetBrightness() for KNX devices, consistent with how Shelly/Zigbee2MQTT handle fading.
