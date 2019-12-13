<?php

namespace SouthCN\EasyUC\Contracts;

interface ShouldSyncOrgs
{
    /**
     * 主动或被动地，从用户中心同步单位列表
     */
    public function syncOrgs(array $orgList): void;
}
