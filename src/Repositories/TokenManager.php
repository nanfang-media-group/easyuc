<?php

namespace SouthCN\EasyUC\Repositories;

use Illuminate\Support\Facades\Cache;

/**
 * @property string $logout
 */
class TokenManager
{
    protected $uid;
    protected $logout;

    public function __construct(string $uid)
    {
        $this->uid = $uid;
    }

    public function __set(string $name, string $value): void
    {
        Cache::forever($this->key($name), $value);
    }

    public function __get(string $name): ?string
    {
        return Cache::get($this->key($name));
    }

    public function __unset(string $name): void
    {
        Cache::forget($this->key($name));
    }

    protected function key(string $name): string
    {
        return "uc:{$this->uid}:token:$name";
    }
}
