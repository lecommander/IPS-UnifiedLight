## Summary

This PR implements the HomeMatic IP, HomeMatic Funk, and HomeMatic Wired backends (all Prio 1). All three variants share the same IPS API (HM_WriteValueBoolean for STATE, HM_WriteValueFloat for LEVEL and RAMP_TIME), making a unified implementation straightforward. The module now supports 7 backend types across 5 protocols, covering the dominant light control systems in the DACH region.

## Changes

### module.php — HomeMatic backend implementation
- Added three new backend constants: BACKEND_HMIP (4), BACKEND_HMRF (5), BACKEND_HMWIRED (6)
- Added three new properties: HMInstanceID (CCU instance), HMDeviceID (device instance), HMFadeTime (RAMP_TIME in seconds)
- Extended ApplyChanges() with HomeMatic validation: requires valid HM instance and valid device instance
- Extended SetPower() with HomeMatic case: calls HM_WriteValueBoolean(deviceID, "STATE", on)
- Extended SetBrightness() with HomeMatic case: calls HM_WriteValueFloat(deviceID, "LEVEL", level/100.0), optionally sets RAMP_TIME before LEVEL if HMFadeTime > 0
- Extended FadeTo() with native HomeMatic fade support via RAMP_TIME parameter (unlike KNX/Shelly/Zigbee which fall back to instant)
- Updated module header docblock to list HomeMatic with all supported device types (HmIP-PDT, HmIP-FDT, HmIP-BSL, HM-LC-Dim1TPBU-FM, HM-LC-Dim1T-FM, HMW-LC-Dim1L-DR, etc.)

### form.json — HomeMatic configuration form
- Added three options to BackendType select: HomeMatic IP (value 4), HomeMatic Funk (value 5), HomeMatic Wired (value 6)
- Added HomeMatic Settings section with three fields: HomeMatic Instance (SelectInstance for CCU), Device Instance (SelectInstance for dimmer/switch), Fade Time / RAMP_TIME (NumberSpinner, 0-60s, 0.5 steps)
- All HomeMatic fields are only visible when BackendType is 4, 5, or 6 (BackendType >= 4 && BackendType <= 6)

### docs/REQUIREMENTS.md — Documentation updates
- Marked HomeMatic IP, Funk, and Wired as implemented in backend overview table
- Updated all 14 HomeMatic functional requirements (FR-411 through FR-434) from planned to implemented
- Added HomeMatic properties to API specification (HMInstanceID, HMDeviceID, HMFadeTime)
- Added IPS HomeMatic to dependencies table
- Added HM_WriteValueBoolean() and HM_WriteValueFloat() to IPS functions table
- Extended ApplyChanges validation pseudocode with HomeMatic branch
- Added 7 HomeMatic test cases (T-401 through T-407) covering HmIP, Funk, and Wired scenarios
- Updated roadmap: RM-002, RM-002a, RM-002b marked as implemented
- Updated change history with version 1.4.0

### .github/workflows/test.yml — CI updates
- Added BACKEND_HMIP, BACKEND_HMRF, BACKEND_HMWIRED constant checks to structure validation job
- Added case self::BACKEND_HMIP, BACKEND_HMRF, BACKEND_HMWIRED checks to ApplyChanges validation job

## Why These Changes Matter

HomeMatic is the dominant light control protocol in existing IPS installations in the DACH region (Germany, Austria, Switzerland). Unlike KNX which is primarily used in new construction, HomeMatic is the go-to solution for retrofits and existing buildings. By supporting all three HomeMatic variants (IP, Funk, Wired), this module covers the full eQ-3 product range from pluggable dimmers to DIN-rail actuators.

The unified API across all HomeMatic variants (HM_WriteValueBoolean for STATE, HM_WriteValueFloat for LEVEL) means a single implementation serves all three backends. This is more efficient than KNX which requires separate group addresses for switching and dimming.

HomeMatic is the only backend besides DMX that supports native fade transitions via the RAMP_TIME parameter. While KNX, Shelly, and Zigbee2MQTT fall back to instant brightness changes, HomeMatic devices can smoothly transition between brightness levels over a configurable time period. This is a significant UX advantage for residential installations where abrupt light changes are undesirable.

The FadeTo() method now has proper native fade support for HomeMatic, using the per-call seconds parameter to set RAMP_TIME before applying the LEVEL value. This matches the behavior of DMX_FadeChannel and provides a consistent API across backends.
