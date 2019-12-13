<?php

namespace SouthCN\EasyUC\Contracts;

use Illuminate\Foundation\Auth\User;
use SouthCN\EasyUC\Repositories\Data\ServiceAreaList;
use SouthCN\EasyUC\Repositories\Data\SiteList;

interface ShouldSyncUserSites
{
    /**
     * 主动或被动地，从用户中心同步「超级管理员」的所有权限
     */
    public function syncUserAppSites(User $user): void;

    /**
     * 主动或被动地，从用户中心同步「服务区管理员」的服务区权限
     */
    public function syncUserServiceAreas(User $user, ServiceAreaList $serviceAreaList): void;

    /**
     * 主动或被动地，从用户中心同步「普通用户」的站点权限
     */
    public function syncUserSites(User $user, SiteList $siteList): void;
}
