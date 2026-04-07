<?php

/**
 * LightDevice — Unified Light Controller
 *
 * Provides a single interface (Power + Brightness) for 10 light control backends.
 * Each backend is implemented in its own class under Backends/ and implements IBackend.
 *
 * Backends:
 *   - DMX (0):          DMX_SetValue() / DMX_FadeChannel() via IPS built-in DMX module
 *   - Shelly (1):       RequestAction() on variables via IPS-Shelly module
 *   - Zigbee2MQTT (2):  RequestAction() on variables via IPS-Zigbee2MQTT module
 *   - KNX (3):          EIB_Switch() / EIB_DimValue() via IPS built-in KNX module
 *   - HomeMatic IP (4): HM_WriteValueBoolean/Float() via IPS built-in HomeMatic module
 *   - HomeMatic Funk (5): Same API as HmIP
 *   - HomeMatic Wired (6): Same API as HmIP
 *   - Philips Hue (7):  RequestAction() on variables via IPS-PhilipsHue-V2 module
 *   - DALI via KNX (8): EIB_Switch() / EIB_DimValue() via KNX-DALI-Gateway
 *   - Tasmota/ESP (9):  RequestAction() on variables via IPS MQTT module
 *   - WLED (10):        HTTP POST /json/state via REST API
 *
 * Public API (callable from scripts):
 *   ULIGHT_SetPower($id, bool $on)
 *   ULIGHT_SetBrightness($id, int $level)   // 0–100
 *   ULIGHT_Toggle($id)
 *   ULIGHT_FadeTo($id, int $targetLevel, float $seconds)
 */

require_once __DIR__ . '/Backends/IBackend.php';
require_once __DIR__ . '/Backends/DmxBackend.php';
require_once __DIR__ . '/Backends/VariableBackend.php';
require_once __DIR__ . '/Backends/KnxBackend.php';
require_once __DIR__ . '/Backends/DaliBackend.php';
require_once __DIR__ . '/Backends/HomeMaticBackend.php';
require_once __DIR__ . '/Backends/WledBackend.php';

class LightDevice extends IPSModuleStrict
{
    const BACKEND_DMX          = 0;
    const BACKEND_SHELLY       = 1;
    const BACKEND_ZIGBEE2MQTT  = 2;
    const BACKEND_KNX          = 3;
    const BACKEND_HMIP         = 4;
    const BACKEND_HMRF         = 5;
    const BACKEND_HMWIRED      = 6;
    const BACKEND_HUE          = 7;
    const BACKEND_DALI         = 8;
    const BACKEND_TASMOTA      = 9;
    const BACKEND_WLED         = 10;

    public function Create(): void
    {
        parent::Create();

        // --- Backend selection ---
        $this->RegisterPropertyInteger('BackendType', self::BACKEND_DMX);

        // --- DMX backend ---
        $this->RegisterPropertyInteger('DMXInstanceID', 0);
        $this->RegisterPropertyInteger('DMXChannel', 1);
        $this->RegisterPropertyFloat('DMXFadeTime', 0.5);

        // --- Variable-based backends (Shelly, Zigbee2MQTT, Hue, Tasmota) ---
        $this->RegisterPropertyInteger('PowerVariableID', 0);
        $this->RegisterPropertyInteger('BrightnessVariableID', 0);

        // --- KNX backend ---
        $this->RegisterPropertyInteger('KNXInstanceID', 0);
        $this->RegisterPropertyString('KNXSwitchAddress', '');
        $this->RegisterPropertyString('KNXDimAddress', '');

        // --- DALI via KNX backend (shares KNXInstanceID) ---
        $this->RegisterPropertyString('DALISwitchAddress', '');
        $this->RegisterPropertyString('DALIDimAddress', '');
        $this->RegisterPropertyInteger('DALIGatewayType', 0);

        // --- HomeMatic backends (IP, Funk, Wired) ---
        $this->RegisterPropertyInteger('HMInstanceID', 0);
        $this->RegisterPropertyInteger('HMDeviceID', 0);
        $this->RegisterPropertyFloat('HMFadeTime', 0.0);

        // --- WLED backend ---
        $this->RegisterPropertyString('WLEDIPAddress', '');
        $this->RegisterPropertyFloat('WLEDTransitionTime', 0.0);

        // --- IPS variables exposed to user ---
        $this->RegisterVariableBoolean('Power', $this->Translate('Power'), '~Switch', 1);
        $this->RegisterVariableInteger('Brightness', $this->Translate('Brightness'), '~Intensity.100', 2);

        $this->EnableAction('Power');
        $this->EnableAction('Brightness');
    }

    public function Destroy(): void
    {
        parent::Destroy();
    }

    public function ApplyChanges(): void
    {
        parent::ApplyChanges();

        $backend = $this->CreateBackend();
        if ($backend === null || !$backend->ValidateConfiguration()) {
            $this->SetStatus(201);
            return;
        }

        $this->SetStatus(102);
    }

    public function RequestAction(string $Ident, mixed $Value): void
    {
        switch ($Ident) {
            case 'Power':
                $this->SetPower((bool) $Value);
                break;
            case 'Brightness':
                $this->SetBrightness((int) $Value);
                break;
            default:
                throw new Exception('LightDevice: Invalid Ident: ' . $Ident);
        }
    }

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    public function SetPower(bool $on): void
    {
        $backend = $this->CreateBackend();
        if ($backend !== null) {
            $backend->SetPower($on);
        }
        $this->SetValue('Power', $on);
        if (!$on) {
            $this->SetValue('Brightness', 0);
        }
    }

    public function SetBrightness(int $level): void
    {
        $level = max(0, min(100, $level));
        $backend = $this->CreateBackend();
        if ($backend !== null) {
            $backend->SetBrightness($level);
        }
        $this->SetValue('Brightness', $level);
        $this->SetValue('Power', $level > 0);
    }

    public function Toggle(): void
    {
        $this->SetPower(!$this->GetValue('Power'));
    }

    public function FadeTo(int $targetLevel, float $seconds): void
    {
        $backend = $this->CreateBackend();
        if ($backend !== null) {
            $backend->FadeTo($targetLevel, $seconds);
        }
        $targetLevel = max(0, min(100, $targetLevel));
        $this->SetValue('Brightness', $targetLevel);
        $this->SetValue('Power', $targetLevel > 0);
    }

    // -------------------------------------------------------------------------
    // Backend Factory
    // -------------------------------------------------------------------------

    private function CreateBackend(): ?IBackend
    {
        $backendType = $this->ReadPropertyInteger('BackendType');

        switch ($backendType) {
            case self::BACKEND_DMX:
                return new DmxBackend(
                    $this->ReadPropertyInteger('DMXInstanceID'),
                    $this->ReadPropertyInteger('DMXChannel'),
                    $this->ReadPropertyFloat('DMXFadeTime')
                );

            case self::BACKEND_SHELLY:
            case self::BACKEND_ZIGBEE2MQTT:
            case self::BACKEND_HUE:
            case self::BACKEND_TASMOTA:
                return new VariableBackend(
                    $this->ReadPropertyInteger('PowerVariableID'),
                    $this->ReadPropertyInteger('BrightnessVariableID')
                );

            case self::BACKEND_KNX:
                return new KnxBackend(
                    $this->ReadPropertyInteger('KNXInstanceID'),
                    $this->ReadPropertyString('KNXSwitchAddress'),
                    $this->ReadPropertyString('KNXDimAddress')
                );

            case self::BACKEND_DALI:
                return new DaliBackend(
                    $this->ReadPropertyInteger('KNXInstanceID'),
                    $this->ReadPropertyString('DALISwitchAddress'),
                    $this->ReadPropertyString('DALIDimAddress')
                );

            case self::BACKEND_HMIP:
            case self::BACKEND_HMRF:
            case self::BACKEND_HMWIRED:
                return new HomeMaticBackend(
                    $this->ReadPropertyInteger('HMInstanceID'),
                    $this->ReadPropertyInteger('HMDeviceID'),
                    $this->ReadPropertyFloat('HMFadeTime')
                );

            case self::BACKEND_WLED:
                return new WledBackend(
                    $this->ReadPropertyString('WLEDIPAddress'),
                    $this->ReadPropertyFloat('WLEDTransitionTime')
                );

            default:
                return null;
        }
    }
}
