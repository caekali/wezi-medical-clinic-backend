<?php
namespace App\Services\ussd;

use Illuminate\Support\Facades\Cache;

class UssdSessionService
{
    public function getStack(string $sessionId): array
    {
        return Cache::get("ussd_stack_{$sessionId}", []);
    }

    public function push(string $sessionId, array $stack): void
    {
        Cache::put("ussd_stack_{$sessionId}", $stack, 300);
    }

    public function pop(string $sessionId): array
    {
        $stack = $this->getStack($sessionId);
        array_pop($stack);
        $this->push($sessionId, $stack);
        return $stack;
    }

    public function forget(string $sessionId, array $keys = []): void
    {
        foreach ($keys as $key) {
            Cache::forget("{$key}_{$sessionId}");
        }
    }

    public function set(string $sessionId, string $key, $value): void
    {
        Cache::put("{$key}_{$sessionId}", $value, 300);
    }

    public function get(string $sessionId, string $key, $default = null)
    {
        return Cache::get("{$key}_{$sessionId}", $default);
    }
}
