<?php

/**
 * KnxBackend — Backend for IPS built-in KNX module.
 */
class KnxBackend implements IBackend
{
    private int $instanceID;
    private string $switchAddress;
    private string $dimAddress;

    public function __construct(int $instanceID, string $switchAddress, string $dimAddress)
    {
        $this->instanceID = $instanceID;
        $this->switchAddress = $switchAddress;
        $this->dimAddress = $dimAddress;
    }

    public function ValidateConfiguration(): bool
    {
        if ($this->instanceID === 0 || !IPS_InstanceExists($this->instanceID)) {
            return false;
        }
        if ($this->switchAddress === '') {
            return false;
        }
        if ($this->dimAddress === '') {
            return false;
        }
        return true;
    }

    public function SetPower(bool $on): void
    {
        if ($this->switchAddress !== '') {
            EIB_Switch($this->switchAddress, $on);
        }
    }

    public function SetBrightness(int $level): void
    {
        $level = max(0, min(100, $level));
        if ($this->dimAddress !== '') {
            EIB_DimValue($this->dimAddress, $level);
        }
    }

    public function FadeTo(int $targetLevel, float $seconds): void
    {
        // KNX has no native fade support — instant set
        $this->SetBrightness($targetLevel);
    }
}
