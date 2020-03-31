<?php

namespace SouthCN\EasyUC\Contracts;

interface ShouldSyncSites
{
    /**
     * 主动或被动地，从用户中心同步站点列表
     */
    public function syncSites(array $siteList): void;
}
