<?php
namespace App\DTOs;

class UssdInput
{
    public string $sessionId;
    public string $phone;
    public array $inputArray;

    public function __construct(string $sessionId, string $phone, ?string $text)
    {
        $this->sessionId = $sessionId;
        $this->phone = $phone;
        $text = $text ?? '';
        $this->inputArray = $text === '' ? [] : explode('*', $text);
    }

    public function lastInput(): string
    {
        return end($this->inputArray) ?? '';
    }

    public function level(): int
    {
        return count($this->inputArray);
    }
}
