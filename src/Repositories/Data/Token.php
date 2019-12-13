<?php

namespace SouthCN\EasyUC\Repositories\Data;

class Token
{
    public $access;
    public $logout;

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }
}
