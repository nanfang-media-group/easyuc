<?php

namespace SouthCN\EasyUC\Repositories;

use Illuminate\Support\Facades\Cache;

class LogoutSignal
{
    protected $key;

    public function __construct(?string $token)
    {
        $this->key = "uc:logout:$token";
    }

    public function set(): void
    {
        Cache::forever($this->key, true);
    }

    public function check(): bool
    {
        return Cache::get($this->key, false);
    }

    public function clear(): bool
    {
        return Cache::forget($this->key);
    }
}
