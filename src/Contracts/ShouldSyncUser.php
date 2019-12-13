<?php

namespace SouthCN\EasyUC\Contracts;

use Illuminate\Foundation\Auth\User;
use SouthCN\EasyUC\Repositories\Data\User as UserData;

interface ShouldSyncUser
{
    /**
     * 主动或被动地，从用户中心同步用户信息
     */
    public function syncUser(UserData $userData): User;

    /**
     * 被动地，从用户中心同步删除不再存在的用户
     */
    public function removeUsers(array $existingUUIDs): void;
}
