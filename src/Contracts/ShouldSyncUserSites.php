<?php

namespace SouthCN\EasyUC\Contracts;

use Illuminate\Foundation\Auth\User;
use SouthCN\EasyUC\Repository;

interface ShouldSyncUserSites
{
    /**
     * 主动或被动地，从用户中心同步「超级管理员」的所有权限
     */
    public function syncUserAppSites(User $user): void;

    /**
     * 主动或被动地，从用户中心同步「服务区管理员」的服务区权限
     */
    public function syncUserServiceAreas(User $user, Repository $repository): void;

    /**
     * 主动或被动地，从用户中心同步「普通用户」的站点权限
     */
    public function syncUserSites(User $user, Repository $repository): void;
}
