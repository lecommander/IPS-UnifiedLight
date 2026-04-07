<?php

/**
 * LightDevice — Unified Light Controller
 *
 * Provides a single interface (Power + Brightness) for DMX, Shelly Dimmer,
 * Zigbee2MQTT, KNX, HomeMatic, and Philips Hue light devices. Translates generic
 * commands to each backend's native IPS API.
 *
 * Backends:
 *   - DMX:          DMX_SetValue() / DMX_FadeChannel() via IPS built-in DMX module
 *   - Shelly:       RequestAction() on variables created by Schnittcher/IPS-Shelly
 *   - Zigbee2MQTT:  RequestAction() on variables created by Schnittcher/IPS-Zigbee2MQTT
 *   - KNX:          EIB_Switch() / EIB_DimValue() via IPS built-in KNX module
 *   - HomeMatic:    HM_WriteValueBoolean() / HM_WriteValueFloat() via IPS built-in HomeMatic module
 *     - HmIP (4):       HmIP-PDT, HmIP-FDT, HmIP-BSL
 *     - Funk (5):       HM-LC-Dim1TPBU-FM, HM-LC-Dim1T-FM, HM-LC-Sw1-Pl-2
 *     - Wired (6):      HMW-LC-Dim1L-DR, HMW-IO-12-Sw7-DR
 *   - Philips Hue:  RequestAction() on variables created by Schnittcher/IPS-PhilipsHue-V2 (7)
 *   - DALI via KNX: EIB_Switch() / EIB_DimValue() via KNX-DALI-Gateway (BEG Luxomat, Lunatone) (8)
 *
 * Public API (callable from scripts):
 *   ULIGHT_SetPower($id, bool $on)
 *   ULIGHT_SetBrightness($id, int $level)   // 0–100
 *   ULIGHT_Toggle($id)
 *   ULIGHT_FadeTo($id, int $targetLevel, float $seconds)
 */

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

    public function Create(): void
    {
        parent::Create();

        // --- Backend selection ---
        $this->RegisterPropertyInteger('BackendType', self::BACKEND_DMX);

        // --- DMX backend ---
        $this->RegisterPropertyInteger('DMXInstanceID', 0);
        $this->RegisterPropertyInteger('DMXChannel', 1);
        $this->RegisterPropertyFloat('DMXFadeTime', 0.5);  // seconds, 0 = instant

        // --- Shelly / Zigbee2MQTT backends ---
        // User selects the exact IPS variables directly (avoids ident guessing)
        $this->RegisterPropertyInteger('PowerVariableID', 0);
        $this->RegisterPropertyInteger('BrightnessVariableID', 0);

        // --- KNX backend ---
        $this->RegisterPropertyInteger('KNXInstanceID', 0);
        $this->RegisterPropertyString('KNXSwitchAddress', '');
        $this->RegisterPropertyString('KNXDimAddress', '');

        // --- DALI via KNX backend (shares KNXInstanceID) ---
        $this->RegisterPropertyString('DALISwitchAddress', '');
        $this->RegisterPropertyString('DALIDimAddress', '');
        $this->RegisterPropertyInteger('DALIGatewayType', 0);  // 0=Auto, 1=BEG Luxomat, 2=Lunatone

        // --- HomeMatic backends (IP, Funk, Wired — shared API) ---
        $this->RegisterPropertyInteger('HMInstanceID', 0);
        $this->RegisterPropertyInteger('HMDeviceID', 0);
        $this->RegisterPropertyFloat('HMFadeTime', 0.0);  // RAMP_TIME in seconds, 0 = instant

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

        // Validate configuration — each backend validates only its own required parameters
        $backendType = $this->ReadPropertyInteger('BackendType');

        switch ($backendType) {
            case self::BACKEND_DMX:
                $dmxID = $this->ReadPropertyInteger('DMXInstanceID');
                if ($dmxID === 0 || !IPS_InstanceExists($dmxID)) {
                    $this->SetStatus(201);
                    return;
                }
                $channel = $this->ReadPropertyInteger('DMXChannel');
                if ($channel < 1 || $channel > 512) {
                    $this->SetStatus(201);
                    return;
                }
                break;

            case self::BACKEND_SHELLY:
            case self::BACKEND_ZIGBEE2MQTT:
            case self::BACKEND_HUE:
                $powerVar = $this->ReadPropertyInteger('PowerVariableID');
                if ($powerVar === 0 || !IPS_VariableExists($powerVar)) {
                    $this->SetStatus(201);
                    return;
                }
                $brightnessVar = $this->ReadPropertyInteger('BrightnessVariableID');
                if ($brightnessVar === 0 || !IPS_VariableExists($brightnessVar)) {
                    $this->SetStatus(201);
                    return;
                }
                break;

            case self::BACKEND_KNX:
                $knxID = $this->ReadPropertyInteger('KNXInstanceID');
                if ($knxID === 0 || !IPS_InstanceExists($knxID)) {
                    $this->SetStatus(201);
                    return;
                }
                $switchAddr = $this->ReadPropertyString('KNXSwitchAddress');
                if ($switchAddr === '') {
                    $this->SetStatus(201);
                    return;
                }
                $dimAddr = $this->ReadPropertyString('KNXDimAddress');
                if ($dimAddr === '') {
                    $this->SetStatus(201);
                    return;
                }
                break;

            case self::BACKEND_DALI:
                $daliKnXID = $this->ReadPropertyInteger('KNXInstanceID');
                if ($daliKnXID === 0 || !IPS_InstanceExists($daliKnXID)) {
                    $this->SetStatus(201);
                    return;
                }
                $daliSwitchAddr = $this->ReadPropertyString('DALISwitchAddress');
                if ($daliSwitchAddr === '') {
                    $this->SetStatus(201);
                    return;
                }
                $daliDimAddr = $this->ReadPropertyString('DALIDimAddress');
                if ($daliDimAddr === '') {
                    $this->SetStatus(201);
                    return;
                }
                break;

            case self::BACKEND_HMIP:
            case self::BACKEND_HMRF:
            case self::BACKEND_HMWIRED:
                $hmInstanceID = $this->ReadPropertyInteger('HMInstanceID');
                if ($hmInstanceID === 0 || !IPS_InstanceExists($hmInstanceID)) {
                    $this->SetStatus(201);
                    return;
                }
                $hmDeviceID = $this->ReadPropertyInteger('HMDeviceID');
                if ($hmDeviceID === 0 || !IPS_InstanceExists($hmDeviceID)) {
                    $this->SetStatus(201);
                    return;
                }
                break;
        }

        $this->SetStatus(102);  // Active
    }

    // -------------------------------------------------------------------------
    // RequestAction — called when variable is changed via GUI or RequestAction()
    // -------------------------------------------------------------------------

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
        $backendType = $this->ReadPropertyInteger('BackendType');

        switch ($backendType) {
            case self::BACKEND_DMX:
                $dmxID   = $this->ReadPropertyInteger('DMXInstanceID');
                $channel = $this->ReadPropertyInteger('DMXChannel');
                $fade    = $this->ReadPropertyFloat('DMXFadeTime');
                $value   = $on ? 255 : 0;
                if ($fade > 0) {
                    DMX_FadeChannel($dmxID, $channel, $value, $fade);
                } else {
                    DMX_SetValue($dmxID, $channel, $value);
                }
                break;

            case self::BACKEND_SHELLY:
            case self::BACKEND_ZIGBEE2MQTT:
            case self::BACKEND_HUE:
                $powerVarID = $this->ReadPropertyInteger('PowerVariableID');
                if ($powerVarID > 0 && IPS_VariableExists($powerVarID)) {
                    RequestAction($powerVarID, $on);
                }
                break;

            case self::BACKEND_KNX:
                $switchAddr = $this->ReadPropertyString('KNXSwitchAddress');
                if ($switchAddr !== '') {
                    EIB_Switch($switchAddr, $on);
                }
                break;

            case self::BACKEND_DALI:
                $daliSwitchAddr = $this->ReadPropertyString('DALISwitchAddress');
                if ($daliSwitchAddr !== '') {
                    EIB_Switch($daliSwitchAddr, $on);
                }
                break;

            case self::BACKEND_HMIP:
            case self::BACKEND_HMRF:
            case self::BACKEND_HMWIRED:
                $hmDeviceID = $this->ReadPropertyInteger('HMDeviceID');
                if ($hmDeviceID > 0 && IPS_InstanceExists($hmDeviceID)) {
                    HM_WriteValueBoolean($hmDeviceID, 'STATE', $on);
                }
                break;
        }

        $this->SetValue('Power', $on);
        if (!$on) {
            $this->SetValue('Brightness', 0);
        }
    }

    public function SetBrightness(int $level): void
    {
        $level       = max(0, min(100, $level));
        $backendType = $this->ReadPropertyInteger('BackendType');

        switch ($backendType) {
            case self::BACKEND_DMX:
                $dmxID    = $this->ReadPropertyInteger('DMXInstanceID');
                $channel  = $this->ReadPropertyInteger('DMXChannel');
                $fade     = $this->ReadPropertyFloat('DMXFadeTime');
                $dmxValue = (int) round($level * 255 / 100);
                if ($fade > 0) {
                    DMX_FadeChannel($dmxID, $channel, $dmxValue, $fade);
                } else {
                    DMX_SetValue($dmxID, $channel, $dmxValue);
                }
                break;

            case self::BACKEND_SHELLY:
            case self::BACKEND_ZIGBEE2MQTT:
            case self::BACKEND_HUE:
                $brightnessVarID = $this->ReadPropertyInteger('BrightnessVariableID');
                if ($brightnessVarID > 0 && IPS_VariableExists($brightnessVarID)) {
                    RequestAction($brightnessVarID, $level);
                }
                // Turn on/off power variable to match brightness
                $powerVarID = $this->ReadPropertyInteger('PowerVariableID');
                if ($powerVarID > 0 && IPS_VariableExists($powerVarID)) {
                    RequestAction($powerVarID, $level > 0);
                }
                break;

            case self::BACKEND_KNX:
                $dimAddr = $this->ReadPropertyString('KNXDimAddress');
                if ($dimAddr !== '') {
                    EIB_DimValue($dimAddr, $level);
                }
                break;

            case self::BACKEND_DALI:
                $daliDimAddr = $this->ReadPropertyString('DALIDimAddress');
                if ($daliDimAddr !== '') {
                    EIB_DimValue($daliDimAddr, $level);
                }
                break;

            case self::BACKEND_HMIP:
            case self::BACKEND_HMRF:
            case self::BACKEND_HMWIRED:
                $hmDeviceID = $this->ReadPropertyInteger('HMDeviceID');
                if ($hmDeviceID > 0 && IPS_InstanceExists($hmDeviceID)) {
                    $fadeTime = $this->ReadPropertyFloat('HMFadeTime');
                    if ($fadeTime > 0) {
                        HM_WriteValueFloat($hmDeviceID, 'RAMP_TIME', $fadeTime);
                    }
                    HM_WriteValueFloat($hmDeviceID, 'LEVEL', $level / 100.0);
                }
                break;
        }

        $this->SetValue('Brightness', $level);
        $this->SetValue('Power', $level > 0);
    }

    public function Toggle(): void
    {
        $this->SetPower(!$this->GetValue('Power'));
    }

    /**
     * Fade to target brightness over given seconds.
     * For DMX: uses native DMX_FadeChannel (ignores configured fade time).
     * For HomeMatic: uses RAMP_TIME parameter.
     * For Shelly/Zigbee2MQTT/KNX: instant set (no native fade available).
     */
    public function FadeTo(int $targetLevel, float $seconds): void
    {
        $targetLevel = max(0, min(100, $targetLevel));
        $backendType = $this->ReadPropertyInteger('BackendType');

        if ($backendType === self::BACKEND_DMX) {
            $dmxID    = $this->ReadPropertyInteger('DMXInstanceID');
            $channel  = $this->ReadPropertyInteger('DMXChannel');
            $dmxValue = (int) round($targetLevel * 255 / 100);
            DMX_FadeChannel($dmxID, $channel, $dmxValue, $seconds);
            $this->SetValue('Brightness', $targetLevel);
            $this->SetValue('Power', $targetLevel > 0);
        } elseif ($backendType === self::BACKEND_HMIP
               || $backendType === self::BACKEND_HMRF
               || $backendType === self::BACKEND_HMWIRED) {
            $hmDeviceID = $this->ReadPropertyInteger('HMDeviceID');
            if ($hmDeviceID > 0 && IPS_InstanceExists($hmDeviceID)) {
                if ($seconds > 0) {
                    HM_WriteValueFloat($hmDeviceID, 'RAMP_TIME', $seconds);
                }
                HM_WriteValueFloat($hmDeviceID, 'LEVEL', $targetLevel / 100.0);
            }
            $this->SetValue('Brightness', $targetLevel);
            $this->SetValue('Power', $targetLevel > 0);
        } else {
            $this->SetBrightness($targetLevel);
        }
    }
}
