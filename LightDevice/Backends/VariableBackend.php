<?php

/**
 * VariableBackend — Backend for IPS variable-based light devices.
 *
 * Used by: Shelly, Zigbee2MQTT, Philips Hue, Tasmota/ESP
 * These backends all use RequestAction() on IPS variables created by
 * their respective IPS modules.
 */
class VariableBackend implements IBackend
{
    private int $powerVariableID;
    private int $brightnessVariableID;

    public function __construct(int $powerVariableID, int $brightnessVariableID)
    {
        $this->powerVariableID = $powerVariableID;
        $this->brightnessVariableID = $brightnessVariableID;
    }

    public function ValidateConfiguration(): bool
    {
        if ($this->powerVariableID === 0 || !IPS_VariableExists($this->powerVariableID)) {
            return false;
        }
        if ($this->brightnessVariableID === 0 || !IPS_VariableExists($this->brightnessVariableID)) {
            return false;
        }
        return true;
    }

    public function SetPower(bool $on): void
    {
        if ($this->powerVariableID > 0 && IPS_VariableExists($this->powerVariableID)) {
            RequestAction($this->powerVariableID, $on);
        }
    }

    public function SetBrightness(int $level): void
    {
        $level = max(0, min(100, $level));
        if ($this->brightnessVariableID > 0 && IPS_VariableExists($this->brightnessVariableID)) {
            RequestAction($this->brightnessVariableID, $level);
        }
        // Turn on/off power variable to match brightness
        if ($this->powerVariableID > 0 && IPS_VariableExists($this->powerVariableID)) {
            RequestAction($this->powerVariableID, $level > 0);
        }
    }

    public function FadeTo(int $targetLevel, float $seconds): void
    {
        // No native fade support — instant set
        $this->SetBrightness($targetLevel);
    }
}
