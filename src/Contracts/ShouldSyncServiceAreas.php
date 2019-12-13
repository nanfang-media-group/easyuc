<?php

namespace SouthCN\EasyUC\Contracts;

use SouthCN\EasyUC\Repositories\Data\ServiceAreaList;

interface ShouldSyncServiceAreas
{
    /**
     * 主动或被动地，从用户中心同步服务区列表
     */
    public function syncServiceAreas(ServiceAreaList $serviceAreaList): void;
}
