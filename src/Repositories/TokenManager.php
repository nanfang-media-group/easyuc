<?php

namespace SouthCN\EasyUC\Repositories;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use SouthCN\EasyUC\Exceptions\Exception;

/**
 * @property string $logout
 */
class TokenManager
{
    protected $session;
    protected $logout;

    public function __construct(string $session = '')
    {
        $this->session = empty($session) ? Session::getId() : $session;

        if (empty($this->$session)) {
            throw new Exception('NO SESSION CAN BE USED');
        }
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
        return "uc:$this->session:token:$name";
    }
}
