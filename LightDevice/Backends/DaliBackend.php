<?php

/**
 * DaliBackend — Backend for DALI via KNX gateway (BEG Luxomat, Lunatone).
 *
 * Shares the KNXInstanceID with the KNX backend but uses separate
 * DALI-specific group addresses for switching and dimming.
 */
class DaliBackend implements IBackend
{
    private int $knxInstanceID;
    private string $switchAddress;
    private string $dimAddress;

    public function __construct(int $knxInstanceID, string $switchAddress, string $dimAddress)
    {
        $this->knxInstanceID = $knxInstanceID;
        $this->switchAddress = $switchAddress;
        $this->dimAddress = $dimAddress;
    }

    public function ValidateConfiguration(): bool
    {
        if ($this->knxInstanceID === 0 || !IPS_InstanceExists($this->knxInstanceID)) {
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
        // DALI via KNX has no native fade support — instant set
        $this->SetBrightness($targetLevel);
    }
}
