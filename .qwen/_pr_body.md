## Summary

This PR implements the Philips Hue backend (Prio 2) using the Schnittcher/IPS-PhilipsHue-V2 module. Hue devices are accessed through the Power and Brightness variables created by the Hue module, using RequestAction() for both switching and dimming. This brings the total number of supported backends to 8 across 6 protocols.

## Changes

### module.php — Philips Hue backend implementation
- Added BACKEND_HUE constant (value 7)
- Extended ApplyChanges() to include Hue in the variable-based validation branch (Shelly/Zigbee2MQTT/Hue share the same PowerVariableID + BrightnessVariableID pattern)
- Extended SetPower() with Hue case: calls RequestAction(powerVariableID, on)
- Extended SetBrightness() with Hue case: calls RequestAction(brightnessVariableID, level) + RequestAction(powerVariableID, level > 0)
- Hue uses the same code path as Shelly/Zigbee2MQTT since all three are variable-based backends managed by the IPS-PhilipsHue-V2 module
- Updated module header docblock to list Philips Hue as a supported backend

### form.json — Hue configuration form
- Added Philips Hue option to BackendType select (value 7, caption: "Philips Hue (IPS-PhilipsHue-V2)")
- Extended Shelly/Zigbee2MQTT settings visibility condition from "BackendType > 0 && BackendType < 3" to "BackendType == 1 || BackendType == 2 || BackendType == 7" to include Hue in the variable-based settings section
- Updated section caption to "Shelly / Zigbee2MQTT / Hue Settings (variable-based backends)"

### docs/REQUIREMENTS.md — Documentation updates
- Marked Philips Hue as implemented in backend overview table
- Updated Philips Hue functional requirements (FR-511 through FR-516) from planned to implemented
- Added IPS-PhilipsHue-V2 to dependencies table
- Extended ApplyChanges validation pseudocode to include Hue in the variable-based branch
- Added 5 Philips Hue test cases (T-501 through T-505)
- Updated roadmap: RM-003 Philips Hue marked as implemented
- Updated change history with version 1.5.0

### .github/workflows/test.yml — CI updates
- Added BACKEND_HUE constant check to structure validation job
- Added case self::BACKEND_HUE check to ApplyChanges validation job

## Why These Changes Matter

Philips Hue is the most widely used consumer smart lighting system globally and one of the top three light control protocols in the German IPS community (alongside HomeMatic and KNX). By supporting Hue through the IPS-PhilipsHue-V2 module, this module now covers the full spectrum of light control scenarios: professional installations (KNX, HomeMatic Wired), retrofits (HomeMatic Funk, Shelly), consumer systems (Hue), and IoT protocols (Zigbee2MQTT).

The implementation reuses the existing variable-based backend pattern (same as Shelly and Zigbee2MQTT), requiring only three lines of code in each of ApplyChanges, SetPower, and SetBrightness. This demonstrates the value of the unified abstraction layer: new variable-based backends can be added with minimal code changes.

The Hue module handles all the complexity of bridge communication, light discovery, and ZigBee protocol translation internally. Users simply select the On and Brightness variables from the Hue device instance in the object tree, and UnifiedLight handles the rest. This is consistent with the Shelly/Zigbee2MQTT approach and avoids the need for direct API calls to the Hue bridge.
