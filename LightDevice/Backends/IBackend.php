<?php

/**
 * IBackend — Interface for all light control backends.
 *
 * Each backend implementation handles the translation from unified
 * Power/Brightness commands to its native IPS API.
 */
interface IBackend
{
    /**
     * Validate backend-specific configuration.
     * Returns true if valid, false if configuration is incomplete.
     */
    public function ValidateConfiguration(): bool;

    /**
     * Set power state (on/off).
     */
    public function SetPower(bool $on): void;

    /**
     * Set brightness level (0-100).
     */
    public function SetBrightness(int $level): void;

    /**
     * Fade to target brightness over given seconds.
     * Backends without native fade support fall back to instant SetBrightness.
     */
    public function FadeTo(int $targetLevel, float $seconds): void;
}
