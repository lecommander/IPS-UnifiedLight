<?php

/**
 * WledBackend — Backend for WLED LED controllers via HTTP REST API.
 *
 * WLED devices are controlled via POST requests to /json/state with
 * JSON payloads like {"on":true,"bri":128}. Supports native fade
 * transitions via the "transition" field (in deci-seconds).
 */
class WledBackend implements IBackend
{
    private string $ipAddress;
    private float $transitionTime;

    public function __construct(string $ipAddress, float $transitionTime)
    {
        $this->ipAddress = $ipAddress;
        $this->transitionTime = $transitionTime;
    }

    public function ValidateConfiguration(): bool
    {
        if ($this->ipAddress === '') {
            return false;
        }
        return true;
    }

    public function SetPower(bool $on): void
    {
        $this->sendState(['on' => $on]);
    }

    public function SetBrightness(int $level): void
    {
        $level = max(0, min(100, $level));
        $this->sendState([
            'on' => $level > 0,
            'bri' => $level,
        ]);
    }

    public function FadeTo(int $targetLevel, float $seconds): void
    {
        $targetLevel = max(0, min(100, $targetLevel));
        // WLED expects transition in deci-seconds (tenths of a second)
        $transition = (int) round($seconds * 10);
        $this->sendState([
            'on' => $targetLevel > 0,
            'bri' => $targetLevel,
            'transition' => $transition,
        ]);
    }

    private function sendState(array $state): void
    {
        $url = 'http://' . $this->ipAddress . '/json/state';
        $body = json_encode($state);
        $options = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => $body,
                'timeout' => 5,
            ],
        ];
        $context = stream_context_create($options);
        @file_get_contents($url, false, $context);
    }
}
