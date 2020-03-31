<?php

namespace SouthCN\EasyUC\Contracts;

use Illuminate\Foundation\Auth\User;
use SouthCN\EasyUC\Repositories\Data\User as UserData;

interface ShouldSyncUser
{
    /**
     * 主动或被动地，从用户中心同步用户信息
     */
    public function syncUser(UserData $userData, int $operation): ?User;
}
