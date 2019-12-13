<?php

namespace SouthCN\EasyUC\Repositories\Data;

/**
 * @property-read int    id
 * @property-read string name
 * @property-read string email
 * @property-read int    group
 */
class User
{
    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * 判断用户是否开发管理员/超级管理员
     */
    public function super(): bool
    {
        return $this->group <= 1;
    }

    /**
     * 判断用户是否服务区管理员
     */
    public function serviceAreaAdmin(): bool
    {
        return $this->group == 2;
    }

    /**
     * 判断用户是否站点用户组
     */
    public function normalUser(): bool
    {
        return $this->group >= 10;
    }

    public function someKindOfAdmin(): bool
    {
        return $this->group <= 2;
    }

    public function __get($name)
    {
        return $this->data->$name;
    }
}
