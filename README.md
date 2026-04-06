# IPS-UnifiedLight

Unified light controller module for IP-Symcon. Provides a single `Power` + `Brightness` interface for DMX, Shelly Dimmer, and Zigbee2MQTT devices.

## Installation

In IP-Symcon → Module Store → Add module URL:

```
https://github.com/lecommander/IPS-UnifiedLight
```

## Usage

Create a **LightDevice** instance per light. Select the backend type and configure the device-specific settings.

### Backend: DMX

1. Select `DMX (IPS built-in)` as Backend Type
2. Select the DMX instance from the object tree
3. Set the DMX channel (1–512)
4. Optionally set a fade time in seconds

### Backend: Shelly Dimmer

Requires [Schnittcher/IPS-Shelly](https://github.com/Schnittcher/IPS-Shelly) to be installed and the Shelly device configured.

1. Select `Shelly Dimmer (IPS-Shelly)` as Backend Type
2. Select the **Power** variable from the Shelly instance (boolean, on/off)
3. Select the **Brightness** variable from the Shelly instance (integer, 0–100)

### Backend: Zigbee2MQTT

Requires [Schnittcher/IPS-Zigbee2MQTT](https://github.com/Schnittcher/IPS-Zigbee2MQTT) to be installed.

1. Select `Zigbee2MQTT (IPS-Zigbee2MQTT)` as Backend Type
2. Select the **Power** variable from the Zigbee2MQTT device instance
3. Select the **Brightness** variable from the Zigbee2MQTT device instance

## Scripting API

```php
// Standard IPS — works with all backends
RequestAction(IPS_GetObjectIDByIdent('Power', $instanceID), true);
RequestAction(IPS_GetObjectIDByIdent('Brightness', $instanceID), 75);

// Module functions
ULIGHT_SetPower($instanceID, true);
ULIGHT_SetPower($instanceID, false);
ULIGHT_SetBrightness($instanceID, 75);    // 0–100
ULIGHT_Toggle($instanceID);
ULIGHT_FadeTo($instanceID, 50, 3.0);     // target level, fade seconds (DMX only)
```

## Roadmap

- [ ] RGBW / Color support (ColorTemp, RGB)
- [ ] LightGroup: control multiple LightDevice instances as one scene
- [ ] HomeMatic Dimmer backend
- [ ] Transition time support for Shelly/Zigbee2MQTT

## Requirements

- IP-Symcon 6.1+
- PHP 7.4+
