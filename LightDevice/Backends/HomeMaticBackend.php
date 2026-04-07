<?php

/**
 * HomeMaticBackend — Backend for HomeMatic IP, Funk, and Wired.
 *
 * All three variants share the same IPS API (HM_WriteValueBoolean/Float).
 * Supports native fade transitions via the RAMP_TIME parameter.
 */
class HomeMaticBackend implements IBackend
{
    private int $instanceID;
    private int $deviceID;
    private float $fadeTime;

    public function __construct(int $instanceID, int $deviceID, float $fadeTime)
    {
        $this->instanceID = $instanceID;
        $this->deviceID = $deviceID;
        $this->fadeTime = $fadeTime;
    }

    public function ValidateConfiguration(): bool
    {
        if ($this->instanceID === 0 || !IPS_InstanceExists($this->instanceID)) {
            return false;
        }
        if ($this->deviceID === 0 || !IPS_InstanceExists($this->deviceID)) {
            return false;
        }
        return true;
    }

    public function SetPower(bool $on): void
    {
        if ($this->deviceID > 0 && IPS_InstanceExists($this->deviceID)) {
            HM_WriteValueBoolean($this->deviceID, 'STATE', $on);
        }
    }

    public function SetBrightness(int $level): void
    {
        $level = max(0, min(100, $level));
        if ($this->deviceID > 0 && IPS_InstanceExists($this->deviceID)) {
            if ($this->fadeTime > 0) {
                HM_WriteValueFloat($this->deviceID, 'RAMP_TIME', $this->fadeTime);
            }
            HM_WriteValueFloat($this->deviceID, 'LEVEL', $level / 100.0);
        }
    }

    public function FadeTo(int $targetLevel, float $seconds): void
    {
        $targetLevel = max(0, min(100, $targetLevel));
        if ($this->deviceID > 0 && IPS_InstanceExists($this->deviceID)) {
            if ($seconds > 0) {
                HM_WriteValueFloat($this->deviceID, 'RAMP_TIME', $seconds);
            }
            HM_WriteValueFloat($this->deviceID, 'LEVEL', $targetLevel / 100.0);
        }
    }
}
