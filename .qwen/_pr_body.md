## Summary

This PR implements the Tasmota/ESP backend (Prio 3) using the IPS MQTT module. Tasmota devices are controlled through MQTT variables (POWER boolean, Dimmer integer 0-100) created by the IPS MQTT module, using RequestAction() for both switching and dimming. This approach reuses the existing variable-based backend pattern shared by Shelly, Zigbee2MQTT, and Hue, requiring only the addition of BACKEND_TASMOTA to the existing case blocks. This brings the total number of supported backends to 10 across 8 protocols.

## Changes

### module.php — Tasmota/ESP backend implementation
- Added BACKEND_TASMOTA constant (value 9)
- Extended ApplyChanges() to include Tasmota in the variable-based validation branch (Shelly/Zigbee2MQTT/Hue/Tasmota share the same PowerVariableID + BrightnessVariableID pattern)
- Extended SetPower() with Tasmota case: calls RequestAction(powerVariableID, on)
- Extended SetBrightness() with Tasmota case: calls RequestAction(brightnessVariableID, level) + RequestAction(powerVariableID, level > 0)
- Tasmota uses the exact same code path as Shelly/Zigbee2MQTT/Hue since all four are variable-based backends
- Updated module header docblock to list Tasmota/ESP as a supported backend with MQTT topic reference

### form.json — Tasmota configuration form
- Added Tasmota/ESP option to BackendType select (value 9, caption: "Tasmota/ESP (IPS MQTT)")
- Extended variable-based settings visibility condition to include BackendType == 9
- Updated section caption to "Shelly / Zigbee2MQTT / Hue / Tasmota Settings (variable-based backends)"

### docs/REQUIREMENTS.md — Documentation updates
- Marked Tasmota/ESP as implemented in backend overview table (IPS MQTT Modul)
- Updated Tasmota functional requirements (FR-711 through FR-716) from planned to implemented
- Extended ApplyChanges validation pseudocode to include Tasmota in the variable-based branch
- Added 5 Tasmota test cases (T-701 through T-705)
- Updated roadmap: RM-005 Tasmota/ESP marked as implemented
- Updated change history with version 1.7.0

### .github/workflows/test.yml — CI updates
- Added BACKEND_TASMOTA constant check to structure validation job
- Added case self::BACKEND_TASMOTA check to ApplyChanges validation job

## Why These Changes Matter

Tasmota is the most popular open-source firmware for ESP8266/ESP32-based smart home devices. It is widely used in the IPS community for DIY lighting projects, sonoff switches, and custom ESP-based dimmers. By supporting Tasmota through the IPS MQTT module, this module now covers the full spectrum from professional installations (KNX, DALI, HomeMatic Wired) to consumer systems (Hue, Shelly) to DIY/IoT solutions (Tasmota).

The implementation demonstrates the power of the variable-based abstraction layer: Tasmota required exactly zero new code logic. Adding BACKEND_TASMOTA to the existing Shelly/Zigbee2MQTT/Hue case blocks was sufficient because all four backends communicate through IPS variables managed by their respective modules (IPS-Shelly, IPS-Zigbee2MQTT, IPS-PhilipsHue-V2, IPS MQTT). This means future variable-based backends can be added with a single line of code per method.

The MQTT approach is the standard way Tasmota devices are integrated in IPS: the IPS MQTT module subscribes to stat/tasmota/RESULT for status updates and publishes to cmnd/tasmota/POWER and cmnd/tasmota/Dimmer for control. This provides reliable bidirectional communication with automatic status synchronization, unlike the HTTP API fallback which would require polling.
