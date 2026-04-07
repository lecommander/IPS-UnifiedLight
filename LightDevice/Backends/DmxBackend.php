<?php

/**
 * DmxBackend — Backend for IPS built-in DMX module.
 */
class DmxBackend implements IBackend
{
    private int $instanceID;
    private int $channel;
    private float $fadeTime;

    public function __construct(int $instanceID, int $channel, float $fadeTime)
    {
        $this->instanceID = $instanceID;
        $this->channel = $channel;
        $this->fadeTime = $fadeTime;
    }

    public function ValidateConfiguration(): bool
    {
        if ($this->instanceID === 0 || !IPS_InstanceExists($this->instanceID)) {
            return false;
        }
        if ($this->channel < 1 || $this->channel > 512) {
            return false;
        }
        return true;
    }

    public function SetPower(bool $on): void
    {
        $value = $on ? 255 : 0;
        if ($this->fadeTime > 0) {
            DMX_FadeChannel($this->instanceID, $this->channel, $value, $this->fadeTime);
        } else {
            DMX_SetValue($this->instanceID, $this->channel, $value);
        }
    }

    public function SetBrightness(int $level): void
    {
        $level = max(0, min(100, $level));
        $dmxValue = (int) round($level * 255 / 100);
        if ($this->fadeTime > 0) {
            DMX_FadeChannel($this->instanceID, $this->channel, $dmxValue, $this->fadeTime);
        } else {
            DMX_SetValue($this->instanceID, $this->channel, $dmxValue);
        }
    }

    public function FadeTo(int $targetLevel, float $seconds): void
    {
        $targetLevel = max(0, min(100, $targetLevel));
        $dmxValue = (int) round($targetLevel * 255 / 100);
        DMX_FadeChannel($this->instanceID, $this->channel, $dmxValue, $seconds);
    }
}
